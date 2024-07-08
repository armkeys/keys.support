<?php

/*
 * Zapier Autoresponder Integration Functions
 *
 * @package WishListMember
 */

if (! class_exists('WLM_OTHER_INTEGRATION_ZAPIER')) {
    /**
     * WLM_OTHER_INTEGRATION_ZAPIER class
     */
    class WLM_OTHER_INTEGRATION_ZAPIER
    {
        /**
         * Takes care of authentication checking and parsing requests
         */
        public static function Zapier()
        {
            // No need to do anything if the our auth is not present.
            if (! isset(wlm_server_data()['HTTP_X_WLMZAPIERAUTH'])) {
                return;
            }
            $qstring = urldecode(wlm_server_data()['QUERY_STRING']);
            if (false !== strpos($qstring, '/zapier/')) {
                $url_parts = explode('/zapier/', $qstring);
                $url_parts = explode('/', $url_parts[1]);
                if (method_exists(__CLASS__, $url_parts[0]) && '_' !== substr($url_parts[0], 0, 1)) {
                    if (! self::_auth()) {
                        http_response_code(401);
                        wp_send_json(['message' => 'Invalid WishList Member Zapier Key']);
                    }
                    $data = [
                        'payload'   => file_get_contents('php://input'),
                        'url_parts' => $url_parts,
                    ];
                    header('Content-type: application/json');
                    list($data, $response_code) = call_user_func([__CLASS__, $url_parts[0]], $data);
                    http_response_code($response_code ? $response_code : 404);
                    if (empty($data) && is_scalar($data)) {
                        $data = new \stdClass();
                    }
                    if (is_string($data)) {
                        $data = json_decode($data);
                    }
                    wp_send_json($data);
                    exit;
                } else {
                    http_response_code(404);
                    wp_send_json(['message' => 'Invalid request made to the WishList Member Zapier Integration']);
                }
            }
        }

        /**
         * Checks if we request is authenticated
         *
         * @return boolean
         */
        public static function _auth()
        {
            $zapier_settings = (array) wishlistmember_instance()->get_option('zapier_settings');
            return wlm_server_data()['HTTP_X_WLMZAPIERAUTH'] === $zapier_settings['key'];
        }

        /**
         * Gets webhook URLs
         *
         * @param  string $event Event.
         * @return array
         */
        public static function _get_zap_urls($event = '')
        {
            $zapier_urls = wishlistmember_instance()->get_option('zapier_urls');

            if (empty($zapier_urls)) {
                return [];
            }
            if (empty($event)) {
                return $zapier_urls;
            }
            if (empty($zapier_urls[ $event ])) {
                return [];
            }
            return $zapier_urls[ $event ];
        }

        /**
         * Adds a webhook URL
         *
         * @param  string $url   Zap URL.
         * @param  string $event Event.
         * @return boolean
         */
        public static function _add_zap_url($url, $event)
        {
            $urls = self::_get_zap_urls();
            if (empty($urls)) {
                $urls = [];
            }

            if (empty($urls[ $event ])) {
                $urls[ $event ] = [];
            }

            $u     = $urls[ $event ];
            $count = count($u);
            $u[]   = $url;
            $u     = array_unique($u);
            if (count($u) > $count) {
                $urls[ $event ] = $u;
                wishlistmember_instance()->save_option('zapier_urls', $urls);
                return true;
            }
            return false;
        }

        /**
         * Deletes a webhook URL
         *
         * @param string $url Zap URL.
         */
        public static function _delete_zap_url($url)
        {
            $urls = self::_get_zap_urls();

            foreach ($urls as $event => $u) {
                $u = array_diff($u, [$url]);
                if (empty($u)) {
                    unset($urls[ $event ]);
                } else {
                    $urls[ $event ] = $u;
                }
            }
            wishlistmember_instance()->save_option('zapier_urls', $urls);
        }

        /**
         * Sends data to webhook URL
         *
         * @param string $data  Data.
         * @param string $event Event.
         */
        public static function _zap($data, $event)
        {
            static $_zaps = [];

            $urls = self::_get_zap_urls($event);
            if (empty($urls)) {
                return;
            }

            $data = wp_json_encode($data);

            foreach ($urls as $url) {
                // Prevent duplicate zaps from being sent.
                $_zap_hash = md5($url . $data);
                if (in_array($_zap_hash, $_zaps, true)) {
                    continue;
                }
                $_zaps[] = $_zap_hash;

                $result = wp_remote_post(
                    $url,
                    [
                        'method'  => 'POST',
                        'headers' => ['Content-type' => 'application/json'],
                        'body'    => $data,
                    ]
                );
                if (! is_wp_error($result) && 410 === (int) $result['response']['code']) {
                    self::_delete_zap_url($url);
                }
            }
        }

        /**
         * Handles subscribe requests
         *
         * @param  array $data Data.
         * @return array
         */
        public static function subscribe($data)
        {
            extract($data, EXTR_SKIP);
            $payload = json_decode($payload);
            if (empty($payload->target_url)) {
                return [['message' => __('Target subscription URL not specified by Zapier subscribe request', 'wishlist-member')], 409];
            }
            $event = $payload->event;
            if (isset($payload->level_id)) {
                $event .= '|' . $payload->level_id;
            }
            if (! self::_add_zap_url($payload->target_url, $event)) {
                return [['message' => __('Cannot process Zapier subscription request', 'wishlist-member')], 409];
            }
            return [[$user], 201];
        }

        /**
         * Handles unsubscribe requests
         *
         * @param array $data Data.
         */
        public static function unsubscribe($data)
        {
            extract($data, EXTR_SKIP);
            $payload = json_decode($payload);
            self::_delete_zap_url($payload->target_url);
        }

        /**
         * Handles poll requests for testing connectivity
         * Note: This returns data from a randomly chosen
         *
         * @param  array $data Data.
         * @return array
         */
        public static function user_poll_test($data)
        {
            global $wpdb;

            $poll_id = $data['url_parts'][1];
            $payload = json_decode($data['payload']);

            $log_keys = [
                'member_added_to_level'         => 'added',
                'member_removed_from_level'     => 'removed',
                'member_cancelled_from_level'   => 'cancelled',
                'member_uncancelled_from_level' => 'uncancelled',
                'remove_member'                 => 'deleted',
            ];
            $returned = wlm_or(get_transient('zapier_test_poll_' . $poll_id . '_' . $payload->_zap_static_hook_code), [0]);

            switch ($poll_id) {
                case 'member_added_to_level':
                case 'member_uncancelled_from_level':
                case 'member_cancelled_from_level':
                    $rand_id = $wpdb->get_var(
                        $wpdb->prepare(
                            'select user_id
							from %0s wlml join %0s wpu on wlml.user_id = wpu.ID
							where log_group = "level"
							and log_key = %s
							and (
								log_value like %s
								or log_value like %s
							)
							and user_id %0s in ( select distinct user_id
								from %0s
								where level_id = %d
								and ID in ( select userlevel_id
									from %0s
									where option_name = "cancelled"
									and option_value = 1 ) )
							and user_id not in (' . implode(', ', array_fill(0, count($returned), '%d')) . ') 
							order by date_added desc limit 1',
                            wishlistmember_instance()->table_names->logs,
                            $wpdb->users,
                            $log_keys[ $poll_id ],
                            sprintf('%%;s:%d:"%d";%%', strlen($payload->level_id), $payload->level_id),
                            sprintf('%%;i:%d;%%', $payload->level_id),
                            'member_cancelled_from_level' === $poll_id ? '' : 'not',
                            wishlistmember_instance()->table_names->userlevels,
                            $payload->level_id,
                            wishlistmember_instance()->table_names->userlevel_options,
                            ...array_values($returned)
                        )
                    );
                    break;
                case 'member_removed_from_level':
                    $rand_id = $wpdb->get_var(
                        $wpdb->prepare(
                            'select user_id
							from %0s wlml join %0s wpu on wlml.user_id = wpu.ID
							where log_group = "level"
							and log_key = %s
							and (
								log_value like %s
								or log_value like %s
							)
							and user_id not in ( select distinct user_id
								from %0s
								where level_id = %d )
							and user_id not in (' . implode(', ', array_fill(0, count($returned), '%d')) . ') 
							order by date_added desc limit 1',
                            wishlistmember_instance()->table_names->logs,
                            $wpdb->users,
                            $log_keys[ $poll_id ],
                            sprintf('%%;s:%d:"%d";%%', strlen($payload->level_id), $payload->level_id),
                            sprintf('%%;i:%d;%%', $payload->level_id),
                            wishlistmember_instance()->table_names->userlevels,
                            $payload->level_id,
                            ...array_values($returned)
                        )
                    );
                    break;
                case 'remove_member':
                    $rand_user = $wpdb->get_var(
                        $wpdb->prepare(
                            'select log_value
							from %0s
							where log_group = "user"
							and log_key = %s
							and user_id not in (' . implode(', ', array_fill(0, count($returned), '%d')) . ') 
							order by date_added desc limit 1',
                            wishlistmember_instance()->table_names->logs,
                            $log_keys[ $poll_id ],
                            ...array_values($returned)
                        )
                    );
                    $rand_user = unserialize($rand_user);
                    $rand_id   = $rand_user ? $rand_user['ID'] : null;
                    break;
                default:
                    $rand_id = $wpdb->get_var(
                        $wpdb->prepare(
                            'select ID
							from ' . $wpdb->users . '
							where ID not in (' . implode(', ', array_fill(0, count($returned), '%d')) . ') 
							order by user_registered desc limit 1',
                            ...array_values($returned)
                        )
                    );
            }

            if (empty($rand_id)) {
                return [[], 200];
            } else {
                $returned[] = $rand_id;
                set_transient('zapier_test_poll_' . $poll_id . '_' . $payload->_zap_static_hook_code, array_unique($returned), 60 * 15);
            }

            $user = self::_get_user(empty($rand_user) ? $rand_id : $rand_user);
            $data = ['id' => $poll_id . '-' . $rand_id] + $user;

            if (
                in_array(
                    $poll_id,
                    [
                        'member_added_to_level',
                        'member_removed_from_level',
                        'member_cancelled_from_level',
                        'member_uncancelled_from_level',
                    ],
                    true
                )
            ) {
                $data['levels'] = array_values(( new \WishListMember\User($rand_id, false) )->Levels);
                if ($payload->level_id) {
                    $data['level_id']   = $payload->level_id;
                    $data['level_name'] = ( new \WishListMember\Level($payload->level_id) )->name;
                } else {
                    $data['level_id']   = '';
                    $data['level_name'] = '';
                }
            }
            return [[$data], 200];
        }

        /**
         * Poll URL for ping requests from Zapier
         *
         * @param  array $data Data.
         * @return array
         */
        public static function ping($data)
        {
            return [
                [
                    'message' => 'pinged',
                    'date'    => gmdate('r'),
                    'site'    => get_bloginfo('url'),
                    'version' => WLM_PLUGIN_VERSION,
                ],
                200,
            ];
        }

        /**
         * Poll URL for levels requests from Zapier
         *
         * @param  array $data Data.
         * @return array
         */
        public static function levels($data)
        {
            $wpm_levels = self::_get_levels();
            $levels     = [];
            foreach ($wpm_levels as $level_id => $level) {
                $levels[] = [
                    'level_id'   => $level_id,
                    'level_name' => $level['name'],
                ];
            }
            return [$levels, 200];
        }

        /**
         * Zapier Action : Add Member
         *
         * @param  array $data Data.
         * @return array
         */
        public static function add_member($data)
        {
            extract($data, EXTR_SKIP);
            $payload = json_decode($payload);

            if (! filter_var($payload->email, FILTER_VALIDATE_EMAIL)) {
                return [['message' => __('Invalid email address', 'wishlist-member')], 400];
            }

            // Prepare user data for adding.
            $user_data = [
                'user_login' => $payload->login,
                'user_email' => $payload->email,
                'user_pass'  => empty($payload->password) ? wishlistmember_instance()->pass_gen(12) : $payload->password,
            ];

            if (! empty($payload->full_name)) {
                $full                    = explode(' ', trim(preg_replace('/[\s]+/', ' ', $payload->full_name)), 2);
                $user_data['first_name'] = $full[0];
                if (isset($full[1])) {
                    $user_data['last_name'] = $full[1];
                }
            }
            // Add first name and last name to user data if provided.
            if (! empty($payload->first_name)) {
                $user_data['first_name'] = $payload->first_name;
            }
            if (! empty($payload->last_name)) {
                $user_data['last_name'] = $payload->last_name;
            }

            // Insert user.
            $user_data['Sequential'] = 1;

            if (! empty($payload->level_id)) {
                $user_data['Levels'] = [[$payload->level_id, empty($payload->transaction_id) ? '' : $payload->transaction_id]];
            }
            if (! empty($payload->send_email)) {
                $user_data['SendMailPerLevel'] = 1;
            }

            $update_id = false;
            if (! empty($payload->update_user_if_existing)) {
                $update_id = email_exists($payload->email);
            }

            foreach (['company', 'address1', 'address2', 'city', 'state', 'zip', 'country'] as $address_field) {
                if (! empty($payload->$address_field)) {
                    $user_data[ $address_field ] = $payload->$address_field;
                }
            }

            foreach (['phone_home', 'phone_work', 'phone_mobile'] as $address_field) {
                if (! empty($payload->$address_field)) {
                    $user_data[ 'custom_' . $address_field ] = $payload->$address_field;
                }
            }

            if ($update_id) {
                $user_data2 = $user_data;
                unset($user_data2['user_login']);
                unset($user_data2['user_email']);
                unset($user_data2['user_pass']);
                $result = wlmapi_update_member($update_id, $user_data2);
            } else {
                $result = wlmapi_add_member($user_data);
            }

            if (! $result['success']) {
                return [['message' => $result['ERROR']], 409];
            }

            // Return user info.
            $user = self::_get_user($result['member'][0]['ID']);

            // Include levels to be returned.
            $user['levels'] = array_values(( new \WishListMember\User($user['user_id'], false) )->Levels);

            return [[$user], 200];
        }

        /**
         * Zapier Action : Add Member To Level
         *
         * @uses   self::_level_management
         * @param  array $data Data.
         * @return array
         */
        public static function add_member_to_level($data)
        {
            return self::_level_management($data, __FUNCTION__);
        }

        /**
         * Zapier Action : Remove Member From Level
         *
         * @uses   self::_level_management
         * @param  array $data Data.
         * @return array
         */
        public static function remove_member_from_level($data)
        {
            return self::_level_management($data, __FUNCTION__);
        }

        /**
         * Zapier Action : Cancel Member From Level
         *
         * @uses   self::_level_management
         * @param  array $data Data.
         * @return array
         */
        public static function cancel_member_from_level($data)
        {
            return self::_level_management($data, __FUNCTION__);
        }

        /**
         * Zapier Action : UnCancel Member From Level
         *
         * @uses   self::_level_management
         * @param  array $data Data.
         * @return array
         */
        public static function uncancel_member_from_level($data)
        {
            return self::_level_management($data, __FUNCTION__);
        }

        /**
         * Helper Function for the following Zapier Actions:
         * - Add Member To Level
         * - Remove Member From Level
         * - Cancel Member From Level
         * - UnCancel Member From Level
         *
         * @param  array  $data   Data.
         * @param  string $action Action.
         * @return array
         */
        public static function _level_management($data, $action)
        {
            extract($data, EXTR_SKIP);
            $payload = json_decode($payload);

            // Grab user info from email.
            $user = self::_get_user($payload->email, 'email');
            if (empty($user)) {
                // Return if email does not match a user.
                return [['message' => __('Email address not found in WishList Member', 'wishlist-member')], 404];
            }
            $user_id = $user['user_id'];

            // Do requested action.
            switch ($action) {
                case 'add_member_to_level':
                    $data = [
                        'Users' => $user_id,
                        'TxnID' => empty($payload->transaction_id) ? '' : $payload->transaction_id,
                    ];
                    wlmapi_add_member_to_level($payload->level_id, $data);
                    break;
                case 'remove_member_from_level':
                    wlmapi_remove_member_from_level($payload->level_id, $user_id);
                    break;
                case 'cancel_member_from_level':
                    wlmapi_update_level_member_data($payload->level_id, $user_id, ['Cancelled' => '1']);
                    break;
                case 'uncancel_member_from_level':
                    wlmapi_update_level_member_data($payload->level_id, $user_id, ['Cancelled' => '0']);
                    break;
            }

            // Include levels to be returned.
            $user['levels'] = array_values(( new \WishListMember\User($user_id, false) )->Levels);

            return [[$user], 200];
        }

        /**
         * Check if email is a temporary email
         *
         * @param  string $email Email address.
         * @return boolean
         */
        public static function _is_temp_email($email)
        {
            return preg_match('/^temp_[0-9a-f]{32}$/', $email);
        }

        /**
         * Get user and return info that we need
         *
         * @uses   get_user_by
         * @param  integer $user_id User ID.
         * @param  string  $field   id, login or email.
         * @return array
         */
        public static function _get_user($user_id, $field = 'id')
        {
            $user = is_scalar($user_id) ? get_user_by($field, $user_id) : (object) $user_id;
            if (! $user) {
                return [];
            }

            $data = [
                'user_id'    => $user->ID,
                'login'      => $user->user_login,
                'email'      => $user->user_email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
            ];
            return $data;
        }


        /**
         * Action: wishlistmember_user_registered
         *
         * Action called when a new member is registered to WishList Member
         *
         * @param integer $user_id    User ID.
         * @param array   $data       Data.
         * @param mixed   $merge_with Merge width ID.
         */
        public static function _new_wishlist_member($user_id, $data, $merge_with = '')
        {
            if (! $merge_with) {
                self::_new_member($user_id);
                self::_wlmhook_add_levels($user_id, [$data['wpm_id']]);
            }
        }

        /**
         * Action called when a new user is registered to WordPress
         *
         * @param integer $user_id User ID.
         */
        public static function _new_member($user_id)
        {
            $event = 'new_member';
            $data  = self::_get_user($user_id);
            if (self::_is_temp_email($data['email'])) {
                return;
            }
            $data['id'] = sprintf('%s-%d', $event, $user_id);
            self::_zap([$data], $event);
        }

        /**
         * Action called when a user is deleted from WordPress
         *
         * @param integer $user_id User ID.
         */
        public static function _remove_member($user_id)
        {
            $event = 'remove_member';
            $data  = self::_get_user($user_id);
            if (self::_is_temp_email($data['email'])) {
                return;
            }
            $data['id'] = sprintf('%s-%d', $event, $user_id);
            self::_zap([$data], $event);
        }

        /**
         * Return membership levels
         *
         * @return array
         */
        public static function _get_levels()
        {
            static $levels = null;

            if (is_null($levels)) {
                $levels = wishlistmember_instance()->get_option('wpm_levels');
                if (! $levels) {
                    $levels = [];
                }
            }

            return $levels;
        }

        /**
         * Action called when a user is added to one or more membership levels in WishList Member
         *
         * @param integer $user_id User ID.
         * @param array   $levels  Array of membership levels.
         */
        public static function _wlmhook_add_levels($user_id, $levels)
        {
            $event = 'member_added_to_level';
            $data  = self::_get_user($user_id);
            if (self::_is_temp_email($data['email'])) {
                return;
            }
            $data['id'] = sprintf('%s-%d', $event, $user_id);
            $wpm_levels = self::_get_levels();
            foreach ($levels as $level) {
                $data['level_id']   = $level;
                $data['level_name'] = $wpm_levels[ $level ]['name'];
                $data['levels']     = array_values(( new \WishListMember\User($user_id, false) )->Levels);
                self::_zap([$data], $event);
                self::_zap([$data], $event . '|' . $level);
            }
        }

        /**
         * Action called when a user is removed from one or more membership levels in WishList Member
         *
         * @param integer $user_id User ID.
         * @param array   $levels  array of membership levels.
         */
        public static function _wlmhook_remove_levels($user_id, $levels)
        {
            $event = 'member_removed_from_level';
            $data  = self::_get_user($user_id);
            if (self::_is_temp_email($data['email'])) {
                return;
            }
            $data['id'] = sprintf('%s-%d', $event, $user_id);
            $wpm_levels = self::_get_levels();
            foreach ($levels as $level) {
                $data['level_id']   = $level;
                $data['level_name'] = $wpm_levels[ $level ]['name'];
                $data['levels']     = array_values(( new \WishListMember\User($user_id, false) )->Levels);
                self::_zap([$data], $event);
                self::_zap([$data], $event . '|' . $level);
            }
        }

        /**
         * Action called when a user is cancelled from one or more membership levels in WishList Member
         *
         * @param integer $user_id User ID.
         * @param array   $levels  Array of membership levels.
         */
        public static function _wlmhook_cancel_levels($user_id, $levels)
        {
            $event = 'member_cancelled_from_level';
            $data  = self::_get_user($user_id);
            if (self::_is_temp_email($data['email'])) {
                return;
            }
            $data['id'] = sprintf('%s-%d', $event, $user_id);
            $wpm_levels = self::_get_levels();
            foreach ($levels as $level) {
                $data['level_id']   = $level;
                $data['level_name'] = $wpm_levels[ $level ]['name'];
                $data['levels']     = array_values(( new \WishListMember\User($user_id, false) )->Levels);
                self::_zap([$data], $event);
                self::_zap([$data], $event . '|' . $level);
            }
        }

        /**
         * Action called when a user is uncancelled from one or more membership levels in WishList Member
         *
         * @param integer $user_id User ID.
         * @param array   $levels  Array of membership levels.
         */
        public static function _wlmhook_uncancel_levels($user_id, $levels)
        {
            $event = 'member_uncancelled_from_level';
            $data  = self::_get_user($user_id);
            if (self::_is_temp_email($data['email'])) {
                return;
            }
            $data['id'] = sprintf('%s-%d', $event, $user_id);
            $wpm_levels = self::_get_levels();
            foreach ($levels as $level) {
                $data['level_id']   = $level;
                $data['level_name'] = $wpm_levels[ $level ]['name'];
                $data['levels']     = array_values(( new \WishListMember\User($user_id, false) )->Levels);
                self::_zap([$data], $event);
                self::_zap([$data], $event . '|' . $level);
            }
        }

        /**
         * Function to remove hooks that's been set.
         */
        public static function remove_hooks()
        {
            // Hook to take care requests from zapier.
            remove_action('plugins_loaded', ['WLM_OTHER_INTEGRATION_ZAPIER', 'Zapier']);

            // Hooks when a user is registered or deleted.
            remove_action('wishlistmember_user_registered', ['WLM_OTHER_INTEGRATION_ZAPIER', '_new_wishlist_member'], 10, 3);
            remove_action('user_register', ['WLM_OTHER_INTEGRATION_ZAPIER', '_new_member'], 10);
            remove_action('delete_user', ['WLM_OTHER_INTEGRATION_ZAPIER', '_remove_member'], 10);

            // Hooks when a user is added to or removed from membership levels.
            remove_action('wishlistmember_user_registered', ['WLM_OTHER_INTEGRATION_ZAPIER', '_new_wishlist_member'], 10);
            remove_action('wishlistmember_add_user_levels_shutdown', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_add_levels'], 10);
            remove_action('wishlistmember_remove_user_levels', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_remove_levels'], 10);

            // Hooks when a user is cancelled or uncancelled from membership levels.
            remove_action('wishlistmember_cancel_user_levels', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_cancel_levels'], 10);
            remove_action('wishlistmember_uncancel_user_levels', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_uncancel_levels'], 10);
        }

        /**
         * Function to set hooks.
         */
        public static function set_hooks()
        {
            // Hook to take care requests from zapier.
            add_action('plugins_loaded', ['WLM_OTHER_INTEGRATION_ZAPIER', 'Zapier']);

            // Hooks when a user is registered or deleted.
            add_action('wishlistmember_user_registered', ['WLM_OTHER_INTEGRATION_ZAPIER', '_new_wishlist_member'], 10, 3);
            add_action('user_register', ['WLM_OTHER_INTEGRATION_ZAPIER', '_new_member'], 10, 1);
            add_action('delete_user', ['WLM_OTHER_INTEGRATION_ZAPIER', '_remove_member'], 10, 1);

            // Hooks when a user is added to or removed from membership levels.
            add_action('wishlistmember_add_user_levels_shutdown', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_add_levels'], 10, 2);
            add_action('wishlistmember_remove_user_levels', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_remove_levels'], 10, 2);

            // Hooks when a user is cancelled or uncancelled from membership levels.
            add_action('wishlistmember_cancel_user_levels', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_cancel_levels'], 10, 2);
            add_action('wishlistmember_uncancel_user_levels', ['WLM_OTHER_INTEGRATION_ZAPIER', '_wlmhook_uncancel_levels'], 10, 2);

            add_action('wishlistmember_suppress_other_integrations', ['WLM_OTHER_INTEGRATION_ZAPIER', 'remove_hooks']);
        }
    }

    WLM_OTHER_INTEGRATION_ZAPIER::set_hooks();
}
