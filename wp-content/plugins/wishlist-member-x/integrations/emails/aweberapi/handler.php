<?php

namespace WishListMember\Autoresponders;

if (! class_exists('\AWeberAPI')) {
    include_once wishlistmember_instance()->plugin_dir . '/extlib/aweber_api/aweber_api.php';
}

class AweberAPI
{
    public static function subscribe($email, $level_id)
    {
        self::process($email, $level_id);
    }

    public static function unsubscribe($email, $level_id)
    {
        self::process($email, $level_id, true);
    }

    public static function process($email, $level_id, $unsub = false)
    {
        $ar = ( new \WishListMember\Autoresponder('aweberapi') )->settings;

        $autounsub = 'yes' === $ar['autounsub'][ $level_id ] ? 'unsubscribe' : $ar['autounsub'][ $level_id ];
        $list_id   = $ar['connections'][ $level_id ];
        $auth_key  = isset($ar['auth_key']) ? $ar['auth_key'] : '';

        if (empty($list_id) || empty($auth_key)) {
            return; // Exit if we don't have anything to sub/unsub to.
        }

        $user = get_user_by('email', trim(wishlistmember_instance()->ar_sender['email']));
        if (empty($user)) {
            return;
        }

        /*
         * ad tracking
         * - process wlm shortcodes
         * - truncate to 20 characters
         */
        if (isset($ar['ad_tracking'][ $level_id ])) {
            $ad_tracking = substr(trim(wishlistmember_instance()->wlmshortcode->manual_process($user, $ar['ad_tracking'][ $level_id ])), 0, 20);
        } else {
            $ad_tracking = '';
        }

        $level_tag = [];
        $params    = [];

        if (false === $unsub) {
            if (isset($ar['level_tag'][ $level_id ]['added'])) {
                $level_tag           = $ar['level_tag'][ $level_id ]['added'];
                $level_tag['apply']  = isset($level_tag['apply']) ? wlm_trim($level_tag['apply']) : '';
                $level_tag['remove'] = isset($level_tag['remove']) ? wlm_trim($level_tag['remove']) : '';
            }

            $params = [
                'action'          => 'subscribe',
                'list_id'         => $list_id,
                'update_existing' => 0,
                'email'           => wishlistmember_instance()->ar_sender['email'],
                'name'            => wishlistmember_instance()->ar_sender['name'],
                'ip_address'      => wlm_server_data()['REMOTE_ADDR'],
                'level_tag'       => $level_tag,
                'ad_tracking'     => $ad_tracking,
                'on_unsub'        => '',
                'user_id'         => $user->ID,
            ];

            $aweber_uid = get_user_meta($user->ID, "aweberapi_{$list_id}_id", true);
            if (! $aweber_uid) {
                $sub = self::_interface()->find_subscriber($list_id, wishlistmember_instance()->ar_sender['email']); // If no id, lets check if subcriber.
                if ($sub) {
                    $aweber_uid = isset($sub['id']) ? $sub['id'] : false;
                    if ($aweber_uid) {
                        add_user_meta($user->ID, "aweberapi_{$list_id}_id", $aweber_uid);
                    }
                }
            }
        } else {
            if ('delete' === $autounsub) {
                $aweber_uid = get_user_meta($user->ID, "aweberapi_{$list_id}_id", true);
                if (! $aweber_uid) {
                    $sub = self::_interface()->find_subscriber($list_id, wishlistmember_instance()->ar_sender['email']); // If no id, lets check if subcriber.
                    if ($sub) {
                        $aweber_uid = isset($sub['id']) ? $sub['id'] : false;
                        if ($aweber_uid) {
                                add_user_meta($user->ID, "aweberapi_{$list_id}_id", $aweber_uid);
                        }
                    }
                }
                // We only unsubscribe people with records in aweber list.
                if ($aweber_uid) {
                    $params = [
                        'action'          => 'unsubscribe',
                        'list_id'         => $list_id,
                        'update_existing' => 0,
                        'email'           => wishlistmember_instance()->ar_sender['email'],
                        'name'            => wishlistmember_instance()->ar_sender['name'],
                        'ip_address'      => wlm_server_data()['REMOTE_ADDR'],
                        'level_tag'       => $level_tag,
                        'ad_tracking'     => $ad_tracking,
                        'on_unsub'        => 'delete',
                        'user_id'         => $user->ID,
                    ];
                } else {
                    return;
                }
            } else {
                // Get membership levels.
                $user_levels     = wishlistmember_instance()->get_membership_levels($user->ID, false, false, true, true);
                $is_cancelled    = in_array($level_id, $user_levels); // If user still have the level, its cancelled else its removed.
                $level_tag_index = $is_cancelled ? 'cancelled' : 'removed';

                if (isset($ar['level_tag'][ $level_id ][ $level_tag_index ])) {
                    $level_tag           = $ar['level_tag'][ $level_id ][ $level_tag_index ];
                    $level_tag['apply']  = isset($level_tag['apply']) ? wlm_trim($level_tag['apply']) : '';
                    $level_tag['remove'] = isset($level_tag['remove']) ? wlm_trim($level_tag['remove']) : '';
                }
                // If we dont need to apply or remove a tag, lets end.
                if (empty($level_tag['apply']) && empty($level_tag['remove']) && 'unsubscribe' !== $autounsub) {
                    return;
                }

                $aweber_uid = get_user_meta($user->ID, "aweberapi_{$list_id}_id", true);
                if (! $aweber_uid) {
                    $sub = self::_interface()->find_subscriber($list_id, wishlistmember_instance()->ar_sender['email']); // If no id, lets check if subcriber.
                    if ($sub) {
                        $aweber_uid = isset($sub['id']) ? $sub['id'] : false;
                        if ($aweber_uid) {
                                add_user_meta($user->ID, "aweberapi_{$list_id}_id", $aweber_uid);
                        }
                    }
                }

                if ($aweber_uid) {
                    $params = [
                        'action'          => 'subscribe',
                        'list_id'         => $list_id,
                        'update_existing' => 1,
                        'email'           => wishlistmember_instance()->ar_sender['email'],
                        'name'            => wishlistmember_instance()->ar_sender['name'],
                        'ip_address'      => wlm_server_data()['REMOTE_ADDR'],
                        'level_tag'       => $level_tag,
                        'ad_tracking'     => $ad_tracking,
                        'on_unsub'        => $autounsub,
                        'user_id'         => $user->ID,
                    ];
                } else {
                    return;
                }
            }
        }

        if (! empty($params)) {
            // Add  to queue.
            $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
            $qname                    = 'aweberapi_' . time();
            $params                   = wlm_maybe_serialize($params);
            $WishlistAPIQueueInstance->add_queue($qname, $params, 'For Queueing');
            self::process_queue();
        }
    }

    public static function process_queue()
    {
        self::_interface()->AweberProcessQueue();
    }

    public static function _interface()
    {
        static $interface;
        if (! $interface) {
            $interface = new AweberAPI_Interface(( new \WishListMember\Autoresponder('aweberapi') )->settings['auth_key']);
        }
        return $interface;
    }
}




class AweberAPI_Interface
{
    private $app_id     = '2d8307c8';
    private $api_ver    = '1.0';
    private $api_key    = '';
    private $api_secret = '';
    private $auth_key   = '';
    private $debug      = false;

    /**
     * $access_tokens list containing access_token & access_token_secret
     */
    private $access_tokens = '';

    public function __construct($auth_key)
    {
        $this->set_auth_key($auth_key);
    }

    public function set_auth_key($auth_key)
    {
        list( $api_key, $api_secret, $request_token, $token_secret, $auth_verifier, $new_token, $new_secret ) = explode('|', wlm_trim($auth_key));

        if (! empty($new_token) && ! empty($new_secret)) {
            $options                               = wishlistmember_instance()->get_option('Autoresponders');
            $access_tokens                         = [$new_token, $new_secret];
            $options['aweberapi']['access_tokens'] = [$new_token, $new_secret];
            $auth_key                              = sprintf('%s|%s|%s|%s|%s|', $api_key, $api_secret, $request_token, $token_secret, $auth_verifier);
            $options['aweberapi']['auth_key']      = $auth_key;
            wishlistmember_instance()->save_option('Autoresponders', $options);
        }

        $this->auth_key = $auth_key;
    }

    public function get_auth_key()
    {
        return $this->auth_key;
    }

    public function get_authkey_url()
    {
        return sprintf('https://auth.aweber.com/%s/oauth/authorize_app/%s', $this->api_ver, $this->app_id);
    }

    public function parse_authkey($key)
    {
        if (empty($key)) {
            return [];
        }
        list( $api_key, $api_secret, $request_token, $token_secret, $auth_verifier ) = explode('|', $key);
        $parsed = [
            'api_key'       => $api_key,
            'api_secret'    => $api_secret,
            'request_token' => $request_token,
            'token_secret'  => $token_secret,
            'auth_verifier' => $auth_verifier,
        ];
        return $parsed;
    }

    public function get_access_tokens()
    {
        $auth_key = $this->auth_key;

        if (empty($auth_key)) {
            return false;
        }

        $options       = wishlistmember_instance()->get_option('Autoresponders');
        $access_tokens = $options['aweberapi']['access_tokens'];
        if (empty($access_tokens)) {
            return false;
        }

        // Test our access token.
        $auth = $this->parse_authkey($auth_key);

        $api                     = new \AWeberAPI($auth['api_key'], $auth['api_secret']);
        $api->adapter->debug     = $this->debug;
        $api->user->tokenSecret  = $auth['token_secret'];
        $api->user->requestToken = $auth['request_token'];
        $api->user->verifier     = $auth['auth_verifier'];

        list( $access_token, $access_token_secret ) = $access_tokens;
        try {
            $account = $api->getAccount($access_token, $access_token_secret);
            return $account ? $access_tokens : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function renew_access_tokens()
    {
        $key = $this->auth_key;
        if (empty($key)) {
            return false;
        }
        $auth                    = $this->parse_authkey($key);
        $api                     = new \AWeberAPI($auth['api_key'], $auth['api_secret']);
        $api->adapter->debug     = $this->debug;
        $api->user->tokenSecret  = $auth['token_secret'];
        $api->user->requestToken = $auth['request_token'];
        $api->user->verifier     = $auth['auth_verifier'];
        try {
            $access_tokens = $api->getAccessToken();
            return $access_tokens;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function AweberProcessQueue($recnum = 5, $tries = 5)
    {
        $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
        $last_process             = get_option('WLM_AweberAPI_LastProcess');
        $current_time             = time();
        $tries                    = $tries > 1 ? (int) $tries : 5;
        $recnum                   = $recnum > 1 ? (int) $recnum : 5;
        $error                    = false;

        $options  = wishlistmember_instance()->get_option('Autoresponders');
        $auth_key = isset($options['aweberapi']['auth_key']) ? wlm_trim($options['aweberapi']['auth_key']) : '';
        if (empty($auth_key)) {
            return false;
        }
        $this->set_auth_key($auth_key);

        // Lets process every 30 seconds.
        if (! $last_process || ( $current_time - $last_process ) > 30) {
            $queues = $WishlistAPIQueueInstance->get_queue('aweberapi', $recnum, $tries, 'tries,name');

            foreach ($queues as $queue) {
                $data = wlm_maybe_unserialize($queue->value);

                if (empty($data['email'])) { // Invalid email queue data, no email, we delete it.
                    $WishlistAPIQueueInstance->delete_queue($queue->ID);
                    continue;
                }

                if ('subscribe' === $data['action']) { // New and unsubcribing.
                    $res = $this->subscribe($data);
                } elseif ('unsubscribe' === $data['action']) {// delete
                    $res = $this->unsubscribe($data);
                }

                if (isset($res['error'])) {
                    $res['error'] = strip_tags($res['error']);
                    $res['error'] = str_replace(["\n", "\t", "\r"], '', $res['error']);
                    $d            = [
                        'notes' => $res['error'],
                        'tries' => $queue->tries + 1,
                    ];
                    $WishlistAPIQueueInstance->update_queue($queue->ID, $d);
                    $error = true;
                } else {
                    $WishlistAPIQueueInstance->delete_queue($queue->ID);
                    $error = false;
                }
            }
            // Save the last processing time when error has occured on last transaction.
            if ($error) {
                $current_time = time();
                if ($last_process) {
                    update_option('WLM_AweberAPI_LastProcess', $current_time);
                } else {
                    add_option('WLM_AweberAPI_LastProcess', $current_time);
                }
            }
        }
    }

    public function get_lists()
    {
        $access_tokens = $this->get_access_tokens();
        if (empty($access_tokens)) {
            throw new \Exception('Auth keys have already expired');
        }

        list($access_token, $access_token_secret) = $access_tokens;
        $key                                      = $this->auth_key;
        $auth                                     = $this->parse_authkey($key);
        $api                                      = new \AWeberAPI($auth['api_key'], $auth['api_secret']);
        $api->adapter->debug                      = $this->debug;
        $api->user->tokenSecret                   = $auth['token_secret'];
        $api->user->requestToken                  = $auth['request_token'];
        $api->user->verifier                      = $auth['auth_verifier'];

        try {
            $account = $api->getAccount($access_token, $access_token_secret);
            $lists   = [];
            foreach ($account->lists as $l) {
                $lists[] = $l->attrs();
            }
            return $lists;
        } catch (\Exception $e) {
            error_log('An error occured while getting list: ' . $e->getMessage());
            return false;
        }
    }

    public function unsubscribe($params)
    {
        $key  = $this->auth_key;
        $auth = $this->parse_authkey($key);

        if (empty($auth)) {
            error_log('WishList Member Aweber API Error: Invalid Auth');
            return ['error' => 'WishList Member Aweber API Error: Invalid Auth'];
        }

        $access_tokens = $this->get_access_tokens();
        if (empty($access_tokens)) {
            error_log('WishList Member Aweber API Error: Auth keys have already expired');
            return ['error' => 'WishList Member Aweber API Error: Auth keys have already expired'];
        }

        list( $access_token, $access_token_secret ) = $access_tokens;
        $api                                        = new \AWeberAPI($auth['api_key'], $auth['api_secret']);
        $api->adapter->debug                        = $this->debug;
        $api->user->tokenSecret                     = $auth['token_secret'];
        $api->user->requestToken                    = $auth['request_token'];
        $api->user->verifier                        = $auth['auth_verifier'];

        if (! isset($params['list_id']) || ! isset($params['user_id'])) {
            return false;
        }
        $list_id = $params['list_id'];
        $user_id = $params['user_id'];

        $aweber_uid = get_user_meta($user_id, "aweberapi_{$list_id}_id", true);
        if (! $aweber_uid) {
            return false;
        }

        try {
            $account     = $api->getAccount($access_token, $access_token_secret);
            $list        = $account->lists->getById($list_id);
            $subs        = $list->subscribers;
            $sub         = $subs->getById($aweber_uid);
            $sub_details = $sub ? $sub->data : false;
            if ($sub_details && 'unconfirmed' !== $sub_details['status']) { // We cannot delete unconfirmed.
                $res = $sub->delete();
            }
            delete_user_meta($user_id, "aweberapi_{$list_id}_id");
            return true;
        } catch (\Exception $e) {
            error_log('An error occured while deleting: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function subscribe($params)
    {
        $key  = $this->auth_key;
        $auth = $this->parse_authkey($key);

        if (empty($auth)) {
            error_log('WishList Member Aweber API Error: Invalid Auth');
            return ['error' => 'WishList Member Aweber API Error: Invalid Auth'];
        }

        $access_tokens = $this->get_access_tokens();
        if (empty($access_tokens)) {
            error_log('WishList Member Aweber API Error: Auth keys have already expired');
            return ['error' => 'WishList Member Aweber API Error: Auth keys have already expired'];
        }

        list( $access_token, $access_token_secret ) = $access_tokens;
        $api                                        = new \AWeberAPI($auth['api_key'], $auth['api_secret']);
        $api->adapter->debug                        = $this->debug;
        $api->user->tokenSecret                     = $auth['token_secret'];
        $api->user->requestToken                    = $auth['request_token'];
        $api->user->verifier                        = $auth['auth_verifier'];

        if (! isset($params['list_id']) || ! isset($params['user_id'])) {
            return false;
        }
        $list_id = $params['list_id'];
        $user_id = $params['user_id'];
        try {
            $account = $api->getAccount($access_token, $access_token_secret);
            $list    = $account->lists->getById($list_id);
            $subs    = $list->subscribers;

            $subscriber  = false;
            $sub_details = false;
            $aweber_uid  = get_user_meta($user_id, "aweberapi_{$list_id}_id", true);
            if ($aweber_uid) {
                $subscriber  = $subs->getById($aweber_uid);
                $sub_details = $subscriber ? $subscriber->data : false;
            }

            // We can only update subscribed existing users.
            if ($sub_details && 'unconfirmed' === $sub_details['status']) {
                return true;
            }

            $a_uid = false;
            if ($sub_details) { // Update.
                $subscriber->name = $params['name'];
                if (isset($params['level_tag']['apply']) && ! empty($params['level_tag']['apply'])) {
                    $params['level_tag']['apply'] = strtolower($params['level_tag']['apply']);
                    $subscriber->tags['add']      = array_map('trim', explode(',', $params['level_tag']['apply']));
                }
                if (isset($params['level_tag']['remove']) && ! empty($params['level_tag']['remove'])) {
                    $params['level_tag']['remove'] = strtolower($params['level_tag']['remove']);
                    $subscriber->tags['remove']    = array_map('trim', explode(',', $params['level_tag']['remove']));
                }
                if (isset($params['on_unsub']) && 'unsubscribe' === $params['on_unsub']) {
                    $subscriber->status = 'unsubscribed';
                }
                $subscriber->save();
            } else { // Add.
                $ad_tracking = $params['ad_tracking'] ? substr($params['ad_tracking'], 0, 20) : false;
                $data        = [
                    'email'      => $params['email'],
                    'name'       => $params['name'],
                    'ip_address' => $params['ip_address'],
                ];
                if ($ad_tracking) {
                    $data['ad_tracking'] = $ad_tracking;
                }

                if (isset($params['level_tag']['apply']) && ! empty($params['level_tag']['apply'])) {
                    $params['level_tag']['apply'] = strtolower($params['level_tag']['apply']);
                    $data['tags']                 = array_map('trim', explode(',', $params['level_tag']['apply']));
                }
                $sub  = $subs->create($data);
                $attr = $sub->attrs();
                if (! empty($attr) && is_array($attr) && isset($attr['id'])) {
                    $a_uid = $attr['id'];
                }
            }

            if ($a_uid) {
                update_user_meta($params['user_id'], $aweber_uid, $a_uid);
            }
            return true;
        } catch (\Exception $e) {
            error_log('An error occured while subscribing: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function get_subscriber($list_id, $id)
    {
        $key  = $this->auth_key;
        $auth = $this->parse_authkey($key);

        if (empty($auth)) {
            // Throw new \Exception("Invalid Auth");
            error_log('WishList Member Aweber API Error: Invalid Auth');
            return false;
        }

        $access_tokens = $this->get_access_tokens();
        if (empty($access_tokens)) {
            // Throw new \Exception("Auth keys have already expired");
            error_log('WishList Member Aweber API Error: Auth keys have already expired');
            return false;
        }

        list( $access_token, $access_token_secret ) = $access_tokens;
        $api                                        = new \AWeberAPI($auth['api_key'], $auth['api_secret']);
        $api->adapter->debug                        = $this->debug;
        $api->user->tokenSecret                     = $auth['token_secret'];
        $api->user->requestToken                    = $auth['request_token'];
        $api->user->verifier                        = $auth['auth_verifier'];

        try {
            $account = $api->getAccount($access_token, $access_token_secret);
            $list    = $account->lists->getById($list_id);
            $subs    = $list->subscribers;
            // Now create a new subscriber.
            $sub = $subs->getById($id);
            if ($sub) {
                return $sub->data;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            error_log('An error occured while getting subscriber: ' . $e->getMessage());
            return false;
        }
    }

    public function find_subscriber($list_id, $email)
    {
        $key  = $this->auth_key;
        $auth = $this->parse_authkey($key);

        if (empty($auth)) {
            // Throw new \Exception("Invalid Auth");
            error_log('WishList Member Aweber API Error: Invalid Auth');
            return false;
        }

        $access_tokens = $this->get_access_tokens();
        if (empty($access_tokens)) {
            // Throw new \Exception("Auth keys have already expired");
            error_log('WishList Member Aweber API Error: Auth keys have already expired');
            return false;
        }

        list( $access_token, $access_token_secret ) = $access_tokens;
        $api                                        = new \AWeberAPI($auth['api_key'], $auth['api_secret']);
        $api->adapter->debug                        = $this->debug;
        $api->user->tokenSecret                     = $auth['token_secret'];
        $api->user->requestToken                    = $auth['request_token'];
        $api->user->verifier                        = $auth['auth_verifier'];

        try {
            $account = $api->getAccount($access_token, $access_token_secret);
            $list    = $account->lists->getById($list_id);
            $subs    = $list->subscribers;
            // Now create a new subscriber.
            $sub = $subs->find(['email' => $email]);
            if ($sub) {
                return $sub->data['entries'][0];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            error_log('An error occured while getting subscriber: ' . $e->getMessage());
            return false;
        }
    }
}
