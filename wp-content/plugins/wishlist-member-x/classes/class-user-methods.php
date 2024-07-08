<?php

/**
 * User Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* User Methods trait
*/
trait User_Methods
{
    // -----------------------------------------
    // Delete user Hook.
    public function delete_user($id)
    {
        $levels = $this->get_membership_levels($id);
        $usr    = $this->get_user_data($id);
        if ($usr->ID) {
            foreach ((array) $levels as $level) {
                $this->ar_unsubscribe($usr->first_name, $usr->last_name, $usr->user_email, $level);
            }
        }
    }

    public function deleted_user()
    {
        if ($this->nodelete_user_hook) {
            return;
        }
        $this->schedule_sync_membership(true);
    }

    // -----------------------------------------
    // Update profile Hook.
    public function profile_update()
    {
        if (null === wlm_post_data()['wlm_updating_profile']) {
            return;
        }
        $wpm_current_user = wp_get_current_user();

        $post_data = wlm_post_data(true);

        if (wlm_arrval($post_data, 'wlm_unsubscribe')) {
            $this->Delete_UserMeta($post_data['user_id'], 'wlm_unsubscribe');
        } else {
            $this->Update_UserMeta($post_data['user_id'], 'wlm_unsubscribe', 1);
            $this->send_unsubscribe_notification_to_user($post_data['user_id']);
        }

        if ($wpm_current_user->caps['administrator']) {
            if (wlm_arrval($post_data, 'wlm_reset_limit_counter')) {
                $this->Delete_UserMeta($post_data['user_id'], 'wpm_login_counter');
            }
            if (wlm_arrval($post_data, 'wpm_delete_member')) {
                if (wlm_arrval($post_data, 'user_id') > 1) {
                    wp_delete_user(wlm_arrval($post_data, 'user_id'));
                }
                $msg = sprintf('<strong>%s</strong>', __('User DELETED.', 'wishlist-member'));
                $this->delete_user(wlm_arrval($post_data, 'user_id'));
            } elseif (wlm_arrval($post_data, 'wpm_send_reset_email')) {
                $msg = sprintf('<strong>%s</strong>', __('Reset Password Link Sent to User.', 'wishlist-member'));
                do_action('retrieve_password/wlminternal', $post_data['user_login']);
            } else {
                $this->set_membership_levels($post_data['user_id'], $post_data['wpm_levels']);
                // Txn ids & timestamps.
                foreach ((array) $post_data['wpm_levels'] as $k) {
                    if (preg_match('#.+[-/,:]#', $post_data['lvltime'][ $k ])) {
                        $gmt = get_option('gmt_offset');
                        if ($gmt >= 0) {
                            $gmt = '+' . $gmt;
                        }
                        $gmt = ' ' . $gmt . ' GMT';
                    } else {
                        $gmt = '';
                    }
                    $this->set_membership_level_txn_id($post_data['user_id'], $k, $post_data['txnid'][ $k ]);
                    $this->user_level_timestamp($post_data['user_id'], $k, strtotime($post_data['lvltime'][ $k ] . $gmt), true);
                }
                $this->Update_UserMeta($post_data['user_id'], 'wpm_login_limit', $post_data['wpm_login_limit']);
                $msg = __('Member Profile Updated.', 'wishlist-member');
            }
        }
        // Address.
        foreach ((array) $post_data['wpm_useraddress'] as $k => $v) {
            $post_data['wpm_useraddress'][ $k ] = stripslashes($v);
        }
        $this->Update_UserMeta($post_data['user_id'], 'wpm_useraddress', $post_data['wpm_useraddress']);

        // Custom fields.
        $custom_fields = explode(',', $post_data['wlm_custom_fields_profile']);
        if (! empty($custom_fields)) {
            foreach ($custom_fields as $field) {
                $this->Update_UserMeta($post_data['user_id'], 'custom_' . $field, $post_data[ $field ]);
            }
        }

        // Custom hidden fields.
        $custom_fields = explode(',', $post_data['wlm_custom_fields_profile_hidden']);
        if (! empty($custom_fields)) {
            foreach ($custom_fields as $field) {
                $this->Update_UserMeta($post_data['user_id'], 'custom_' . $field, $post_data[ $field ]);
            }
        }

        // Password hint.
        if ($this->get_option('password_hinting')) {
            $this->Update_UserMeta($post_data['user_id'], 'wlm_password_hint', wlm_trim($post_data['passwordhint']));
        }

        // Consent to market.
        if ($this->get_option('privacy_enable_consent_to_market')) {
            $this->Update_UserMeta($post_data['user_id'], 'wlm_consent_to_market', wlm_arrval($post_data, 'wlm_consent_to_market') + 0);
        }

        // Tos accepted.
        if ($this->get_option('privacy_require_tos_on_registration')) {
            $this->Update_UserMeta($post_data['user_id'], 'wlm_tos_accepted', wlm_arrval($post_data, 'wlm_tos_accepted') + 0);
        }

        if (in_array(wlm_request_data()['wp_http_referer'], ['wlm', 'http://wlm'], true)) {
            $link = $this->GetMenu('members');
            header('Location:admin.php' . $link->URL . '&msg=' . rawurlencode($msg));
            exit;
        }
    }

    // -----------------------------------------
    // Login Hook.
    public function login($user_login, $user)
    {

        // We want run seq upgrade once at login time to make sure user will be assigned to all levels.
        $sequential_individual_call_name = 'wlm_is_doing_sequential_for_' . $user->ID;
        delete_transient($sequential_individual_call_name);

        if ($this->login_counter($user)) {
            // Do not check if redirects if login was done via Ajax.
            if (wp_doing_ajax()) {
                return;
            }

            // Make sure we do logs once every minute.
            // Sometimes this is triggered multiple times in 1sec, esp. in admin when session ends.
            $client_ip      = wlm_get_client_ip();
            $transient_name = sprintf('done_login_logs_%d_%s', $user->ID, md5($client_ip));
            if (false == get_transient($transient_name)) {
                // \WishListMember\Logs::add( $user->ID, 'login', 'login', array( 'ip' => $client_ip ) );
                set_transient($transient_name, 1, MINUTE_IN_SECONDS);
            }

            // Save IP.
            $this->Update_UserMeta($user->ID, 'wpm_login_ip', wishlistmember_instance()->ip_tracking_enabled($user->ID) ? $client_ip : '');
            // Save login date.
            $this->Update_UserMeta($user->ID, 'wpm_login_date', time());

            if (apply_filters('wishlistmember_login_redirect_override', false)) {
                return;
            }

            do_action('wishlistmember_after_login');

            // If admin doesn't want WLM to handle login redirect then just return it so WP will handle the redirect instead.
            if (! $this->get_option('enable_login_redirect_override')) {
                return;
            }

            // Redirect user to default WP login URL if cookies are disabled on client's browser.
            if (isset(wlm_post_data()['wlm_redirect_to']) && empty($_COOKIE)) {
                wp_safe_redirect(wp_login_url());
                exit();
            }

            if (empty($_COOKIE) && empty(wlm_get_data()['wlmconfirm'])) {
                return;
            }

            // Admin wants to go to wp-admin?
            // WordPress always sets the redirect_to to admin url when it's empty.
            if (substr(wlm_post_data()['redirect_to'], 0, strlen(admin_url())) == admin_url()) {
                if ($user->caps['administrator']) {
                    /*
                        header('Location:'.wlm_post_data()['redirect_to']);
                        exit();
                    */
                    // Instead of redirecting ourselves, we just let WP handle redirects for admins.
                    return;
                }
                // Now let's force a wishlist-member redirect.
                wlm_post_data()['redirect_to'] = 'wishlistmember';
            }

            if (! empty(wlm_post_data()['wlm_redirect_to'])) {
                if ('wishlistmember' === wlm_post_data()['wlm_redirect_to']) {
                    wlm_post_data()['redirect_to'] = 'wishlistmember';
                } else {
                    header('Location:' . wlm_post_data()['wlm_redirect_to']);
                    exit;
                }
            }

            if ('wishlistmember' === wlm_post_data()['redirect_to'] || ! $user->caps['administrator']) {
                // If redirect_to is not wishlistmember, then we let WP handle things for us.
                if ('wishlistmember' !== wlm_post_data()['redirect_to'] && ! $this->get_option('enable_login_redirect_override')) {
                    return;
                }
                // Get levels.
                $levels = (array) array_flip($this->get_membership_levels($user->ID));

                // Fetch all levels.
                $wpm_levels = $this->get_option('wpm_levels');

                // Inject pay per post settings.
                $this->inject_ppp_settings($wpm_levels, 'U-' . $user->ID);

                // ** USERS WITH NO LEVEL SHOULD USE THE GLOBAL REDIRECT ** //
                // No levels? redirect to homepage.
                // If (!count($levels))
                // Header("Location:" . get_bloginfo('url'));
                // If no level, use the global.
                $url = '---';
                if (count($levels)) {
                    // Sort levels by level order and subscription timestamp.
                    $ts = $this->user_level_timestamps($user->ID);
                    foreach ((array) array_keys((array) $levels) as $level) {
                        if (empty($wpm_levels[ $level ]['levelOrder'])) {
                            $level_order = sprintf('%04d', 0); // This make 0 digit like  string 0000!
                        } else {
                            $level_order = sprintf('%04d', $wpm_levels[ $level ]['levelOrder']);
                        }
                        $levels[ $level ] = $level_order . ',' . $ts[ $level ] . ',' . $level;
                    }
                    asort($levels);

                    // Remove user level and make it the first entry to assure that it is the last option.
                    $ulevel = ['U-' . $user->ID => $levels[ 'U-' . $user->ID ]];
                    unset($levels[ 'U-' . $user->ID ]);
                    $levels = $ulevel + $levels;

                    // Fetch the last level in the array.
                    $levels = array_keys((array) $levels);
                    $level  = array_pop($levels);

                    // $url = $wpm_levels[$level]['custom_login_redirect'] ? '' : '---';
                }

                // Now let's get that after login page.
                if ('---' === $url) {
                    // Get default after login page.
                    $type = $this->get_option('after_login_type');
                    if (false === $type) {
                        $url = $this->get_option('after_login_internal');
                        $url = $url ? get_permalink($url) : $this->get_option('after_login');
                    } else {
                        if ('text' === $type) {
                            $url = add_query_arg('sp', 'after_login', $this->magic_page());
                        } elseif ('internal' === $type) {
                            $url = $this->get_option('after_login_internal');
                            $url = get_permalink($url);
                        } else {
                            $url = $this->get_option('after_login');
                        }
                        if (! $url) {
                            $url = add_query_arg('sp', 'after_login', $this->magic_page());
                        }
                    }
                } elseif (empty($url)) {
                    // Per level login reg is homepage.
                    $url = get_bloginfo('url');
                } else {
                    // Get permalink of per level after login page.
                    $url = get_permalink($url);
                }

                // If no after login url specified then set it to homepage.
                if (! $url) {
                    $url = get_bloginfo('url');
                }

                $url = apply_filters('wlm_after_login_redirect', $url, $level, $user);

                // Redirect.
                header('Location:' . $url);
                exit;
            }
        }
    }

    // Gets the user ID of the current user before the wp_logout function is called.
    // WordPress added wp_set_current_user( 0 ); on build 46265 (Oct-12-2019) which broke the after logout redirect because.
    // The global $current_user is now cleared when the function Logout() is triggered.
    public function get_user_id_before_logout()
    {
        global $current_user;
        $this->wlm_current_user = $current_user->ID;
    }

    // -----------------------------------------
    // Logout Hook.
    public function logout()
    {
        global $current_user;

        $current_user_id = ( $current_user->ID ) ? $current_user->ID : $this->wlm_current_user;

        // Skip processing the logout event if WLM doesn't have permission.
        if (! $this->get_option('enable_logout_redirect_override')) {
            return;
        }

        // $GLOBALS['wp_rewrite'] required.
        if (is_null($GLOBALS['wp_rewrite'])) {
            return;
        }

        if (apply_filters('wishlistmember_logout_redirect_override', false)) {
            return;
        }

        do_action('wishlistmember_after_logout');

        if (
            ( '' == wlm_arrval($_REQUEST, 'redirect_to') && true !== $this->no_logout_redirect )
            ||
            ( $this->get_option('enable_logout_redirect_override') )
        ) { // We only do the logout redirect if this is not TRUE.
            // Get levels.
            $levels = array_flip($this->get_membership_levels($current_user_id));

            // Now let's get that after logout page.
            //
            // No levels? redirect to homepage.
            if (! count($levels)) {
                $url = site_url('wp-login.php', 'login');
            } else {
                $url = '---'; // Todo,  if we want add logout redirect to each level.
            }

            if (count($levels)) {
                // Fetch all levels.
                $wpm_levels = $this->get_option('wpm_levels');

                // Sort levels by level order and subscription timestamp.
                $ts = $this->user_level_timestamps($current_user->ID);
                foreach ((array) array_keys((array) $levels) as $level) {
                    if (empty($wpm_levels[ $level ]['levelOrder'])) {
                        $level_order = sprintf('%04d', 0); // This make 0 digit like  string 0000!
                    } else {
                        $level_order = sprintf('%04d', $wpm_levels[ $level ]['levelOrder']);
                    }
                    $levels[ $level ] = $level_order . ',' . ( $ts[ $level ] ?? '' ) . ',' . $level;
                }
                asort($levels);

                // Remove user level and make it the first entry to assure that it is the last option.
                $ulevel = ['U-' . $current_user->ID => $levels[ 'U-' . $current_user->ID ] ?? ''];
                unset($levels[ 'U-' . $current_user->ID ]);
                $levels = $ulevel + $levels;

                // Fetch the last level in the array.
                $levels = array_keys((array) $levels);
                $level  = array_pop($levels);
            }

            if ('---' === $url) {
                // Get default after logout page.
                $type = $this->get_option('after_logout_type');
                if (false === $type) {
                    $url = $this->get_option('after_logout_internal');
                    $url = $url ? get_permalink($url) : $this->get_option('after_logout');
                } else {
                    if ('text' === $type) {
                        $url = add_query_arg('sp', 'after_logout', $this->magic_page());
                    } elseif ('internal' === $type) {
                        $url = $this->get_option('after_logout_internal');
                        $url = get_permalink($url);
                    } else {
                        $url = $this->get_option('after_logout');
                    }
                    if (! $url) {
                        $url = add_query_arg('sp', 'after_logout', $this->magic_page());
                    }
                }
            } elseif (empty($url)) {
                // Per level logout reg is homepage.
                $url = get_bloginfo('url');
            } else {
                // Get permalink of per level after logout page.
                $url = get_permalink($url);
            }

            // If no after logout url specified then set it to homepage.
            if (! $url) {
                $url = get_bloginfo('url');
            }


            $url = apply_filters('wlm_after_logout_redirect', $url, $level);

            // Redirect.
            header('Location:' . $url);
            exit;
        }
    }

    /**
     * DEPRECATED: Send password reset email
     *
     * @param string $user_login
     */
    public function retrieve_password($user_login)
    {
        error_log(__('This function is deprecated and will be removed in the future.', 'wishlist-member'));
        do_action('retrieve_password/wlminternal', $user_login);
    }

    public function profile_page($profileuser)
    {
        global $current_user;

        if (! $profileuser) {
            // $profileuser not passed, attempt to get pro0file from $GLOBALS.
            $profileuser = wlm_arrval($GLOBALS, 'profileuser');
        }

        // No profile user, do nothing.
        if (empty($profileuser)) {
            return;
        }

        $profileuser = $this->get_user_data($profileuser->ID);
        $mlevels     = $this->get_membership_levels($profileuser->ID);

        if ($this->access_control->current_user_can('wishlistmember3_members/manage')) {
            $custom_fields_form = $this->get_custom_reg_fields();
        } else {
            $custom_fields_form = $this->get_custom_reg_fields($mlevels);
        }

        // Let's remove the WishList Member address fields in the $custom_fields_form to prevent duplicates.
        foreach ($custom_fields_form as $key => $c_fields_form) {
            $address_fields = ['company', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'website', 'nickname'];
            if (in_array($key, $address_fields)) {
                unset($custom_fields_form[ $key ]);
            }
        }

        $custom_fields = implode('', $custom_fields_form);
        $custom_fields = str_replace(['<td class="label">', '</td><td'], ['<th scope="row">', '</th><td'], $custom_fields);

        // If password hinting is enabled, display the password hint for the member.
        if ($this->get_option('password_hinting')) {
            $custom_fields .= '<tr class="li_fld field_text required">
									<th scope="row">Password Hint:</th>
									<td class="fld_div">
										<input class="fld" type="text" name="passwordhint" size="20" value="' . $profileuser->wlm_password_hint . '">
										<div class="desc"></div>
									</td>
								</tr>';
        }

        // Consent to market.
        $consent_to_market = '';
        if ($this->get_option('privacy_enable_consent_to_market')) {
            $txt01             = __('Consent to Market', 'wishlist-member');
            $checked           = $this->Get_UserMeta($profileuser->ID, 'wlm_consent_to_market') ? 'checked' : '';
            $consent_to_market = <<<STRING
			<tr valign="top">
				<th scope="row"></th>
				<td><label><input type="checkbox" name="wlm_consent_to_market" value="1" {$checked} /> {$txt01}</label></td>
			</tr>
STRING;
        }

        // Require tos on registration.
        $tos_on_registration = '';
        if ($this->get_option('privacy_require_tos_on_registration')) {
            $txt01               = __('Terms of Service Accepted', 'wishlist-member');
            $checked             = $this->Get_UserMeta($profileuser->ID, 'wlm_tos_accepted') ? 'checked' : '';
            $readonly            = $checked ? 'onclick="return false;" readonly' : '';
            $tos_on_registration = <<<STRING
			<tr valign="top">
				<th scope="row"></th>
				<td><label><input type="checkbox" name="wlm_tos_accepted" value="1" {$checked} {$readonly} /> {$txt01}</label></td>
			</tr>
STRING;
        }

        // Data privacy section.
        $data_privacy = '';
        if ($consent_to_market || $tos_on_registration) {
            $data_privacy = sprintf('<h3>%s</h3><table class="form-table">%s%s</table>', __('Data Privacy', 'wishlist-member'), $consent_to_market, $tos_on_registration);
        }

        $user_custom_fields = $this->get_user_custom_fields($profileuser->ID);
        $postdata           = array_intersect_key($user_custom_fields, $custom_fields_form);

        $user_custom_fields = array_diff_key($user_custom_fields, $custom_fields_form);
        $hastos             = isset($user_custom_fields['terms_of_service']);

        if (( $this->access_control->current_user_can('wishlistmember3_edit_custom_fields') || $this->access_control->current_user_can('wishlistmember3_members/manage') ) && $user_custom_fields) {
            foreach ($user_custom_fields as $custom_name => $custom_value) {
                // Let's remove the WishList Member address fields as well as the firstname and lastname to avoid duplicates.
                $address_fields = ['company', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'website', 'firstname', 'lastname'];
                if (in_array($custom_name, $address_fields)) {
                    continue;
                }

                if ('terms_of_service' !== $custom_name) {
                    $custom_fields .= '<tr><th scope="row"><span style="color:gray">' . $custom_name . '</span></th><td>';
                    $custom_fields .= '<input type="text" name="' . esc_attr($custom_name) . '" value="' . htmlentities(stripslashes(implode(' ', (array) $custom_value)), ENT_QUOTES) . '" />';
                    $custom_fields .= '</td></tr>';
                }
            }
        }
        if ($hastos) {
            $custom_fields .= '<tr><th scope="row">' . esc_html__('Terms of Service', 'wishlist-member') . '</th><td>';
            if ($user_custom_fields['terms_of_service']) {
                $custom_fields .= 'Accepted';
            } else {
                $custom_fields .= '&nbsp;';
            }
            $custom_fields .= '</td></tr>';
        }

        $custom_fields_heading = $custom_fields ? __('<h3>Additional Registration Information</h3>', 'wishlist-member') : '';
        $custom_fields         = $custom_fields ? $custom_fields_heading . '<table class="form-table wpm_regform_table WishListMemberCustomFields">' . $custom_fields . '</table>' : '';
        if ($custom_fields) {
            $custom_fields .= '<input type="hidden" name="wlm_custom_fields_profile" value="' . implode(',', array_keys($custom_fields_form)) . '" />';
            if (( $this->access_control->current_user_can('wishlistmember3_edit_custom_fields') || $this->access_control->current_user_can('wishlistmember3_members/manage') ) && $user_custom_fields) {
                $custom_fields .= '<input type="hidden" name="wlm_custom_fields_profile_hidden" value="' . implode(',', array_keys($user_custom_fields)) . '" />';
            }

            $postdata = (array) $postdata;
            array_walk_recursive($postdata, 'wlm_xss_sanitize');
            $postdata = array_diff($postdata, ['']);
            ?>
            <script type="text/javascript">
            var wlm_regform_values = <?php echo wp_json_encode($postdata, JSON_UNESCAPED_UNICODE); ?>;
            </script>
            <?php
        }
        wlm_print_script($this->plugin_url . '/js/regform_prefill.js');

        $mailcheck    = 1 == $profileuser->wlm_unsubscribe ? '' : 'checked="true"';
        $txt01        = __('Subscribed to Email Broadcast Mailing List', 'wishlist-member');
        $mailinglist  = <<<STRING
		<tr valign="top">
			<th scope="row"></th>
			<td><label><input type="checkbox" name="wlm_unsubscribe" value="1" {$mailcheck} /> {$txt01}</label></td>
		</tr>
STRING;
        $txt01        = __('WishList Member Feed URL', 'wishlist-member');
        $wlm_feed_url = <<<STRING
		<tr valign="top">
			<th scope="row">{$txt01}</th>
			<td><a href="{$profileuser->wlm_feed_url}">{$profileuser->wlm_feed_url}</a></td>
		</tr>
STRING;
        // Retrieve address.
        $wpm_useraddress = $profileuser->wpm_useraddress;
        $countries       = '<select name="wpm_useraddress[country]">';
        foreach ((array) $this->countries() as $country) {
            if (isset($profileuser->wpm_useraddress['country'])) {
                $selected = $country == $profileuser->wpm_useraddress['country'] ? ' selected="true" ' : '';
            }
                $selected   = isset($selected) ? $selected : '';
                $countries .= '<option' . $selected . '>' . $country . '</option>';
        }

        $wpm_useraddress_company  = isset($wpm_useraddress['company']) ? stripslashes($wpm_useraddress['company']) : '';
        $wpm_useraddress_address1 = isset($wpm_useraddress['address1']) ? stripslashes($wpm_useraddress['address1']) : '';
        $wpm_useraddress_address2 = isset($wpm_useraddress['address2']) ? stripslashes($wpm_useraddress['address2']) : '';
        $wpm_useraddress_city     = isset($wpm_useraddress['city']) ? stripslashes($wpm_useraddress['city']) : '';
        $wpm_useraddress_state    = isset($wpm_useraddress['state']) ? stripslashes($wpm_useraddress['state']) : '';
        $wpm_useraddress_zip      = isset($wpm_useraddress['zip']) ? stripslashes($wpm_useraddress['zip']) : '';

        $txtaddress     = __('Address', 'wishlist-member');
        $txtcompany     = __('Company', 'wishlist-member');
        $txtcity        = __('City', 'wishlist-member');
        $txtstate       = __('State', 'wishlist-member');
        $txtzip         = __('Zip', 'wishlist-member');
        $txtcountry     = __('Country', 'wishlist-member');
        $addresssection = <<<STRING
				 <h3>{$txtaddress}</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">{$txtcompany}</th>
					<td><input type="text" name="wpm_useraddress[company]" value="{$wpm_useraddress_company}" size="30" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">{$txtaddress}</th>
					<td><input type="text" name="wpm_useraddress[address1]" value="{$wpm_useraddress_address1}" size="40" /><br /><input type="text" name="wpm_useraddress[address2]" value="{$wpm_useraddress_address2}" size="40" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">{$txtcity}</th>
					<td><input type="text" name="wpm_useraddress[city]" value="{$wpm_useraddress_city}" size="30" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">{$txtstate}</th>
					<td><input type="text" name="wpm_useraddress[state]" value="{$wpm_useraddress_state}" size="30" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">{$txtzip}</th>
					<td><input type="text" name="wpm_useraddress[zip]" value="{$wpm_useraddress_zip}" size="10" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">{$txtcountry}</th>
					<td>{$countries}</td>
				</tr>
			</table>
STRING;

        if ($this->access_control->current_user_can('wishlistmember3_members/manage')) {
            $wpm_levels = $this->get_option('wpm_levels');
            $options    = [];
            foreach ((array) $wpm_levels as $id => $level) {
                $checked = in_array($id, $mlevels) ? 'checked="true"' : '';
                if ($checked) {
                    $txnid      = '<input type="text" name="txnid[' . esc_attr($id) . ']" value="' . esc_attr($this->get_membership_levels_txn_id($profileuser->ID, $id)) . '" size="20" style="text-align:center" />';
                    $lvltime    = '<input type="text" name="lvltime[' . esc_attr($id) . ']" value="' . esc_attr(gmdate('F d, Y h:i:sa', $this->user_level_timestamp($profileuser->ID, $id) + $this->gmt)) . '" size="25" style="text-align:center" />';
                    $lvl_parent = $this->level_parent($id, $profileuser->ID);
                    $lvl_parent = $lvl_parent && isset($wpm_levels[ $lvl_parent ]) ? $wpm_levels[ $lvl_parent ]['name'] : '';
                } else {
                    $txnid      = '';
                    $lvltime    = '';
                    $lvl_parent = '';
                }
                $strike    = isset($strike) ? $strike : '';
                $strike2   = isset($strike2) ? $strike2 : '';
                $options[] = '<tr><td style="padding:0;margin:0"><label><input type="checkbox" name="wpm_levels[]" value="' . esc_attr($id) . '" ' . $checked . ' /> ' . $strike . esc_html($level['name']) . $strike2 . '</label></td><td style="padding:0 5px;margin:0">' . $txnid . '</td><td style="padding:0 5px;margin:0">' . $lvltime . '</td><td style="padding:0 5px;margin:0;text-align:center">' . esc_html($lvl_parent) . '</td></tr>';
            }
            $options = '<table cellpadding="2" cellspacing="4"><tr><td style="padding:0;margin:0;font-size:1em"><strong>' . esc_html__('Level', 'wishlist-member') . '</strong></td><td style="padding:0 5px;margin:0;font-size:1em;text-align:center"><strong>' . esc_html__('Transaction ID', 'wishlist-member') . '</strong></td><td style="padding:0 5px;margin:0;font-size:1em;text-align:center"><strong>' . esc_html__('Date Added to Level', 'wishlist-member') . '</strong></td><td style="padding:0 5px;margin:0;font-size:1em;text-align:center"><strong>' . esc_html__('Parent Level', 'wishlist-member') . '</strong></td></tr>' . implode('', $options) . '</table>';

            $registered = wlm_date($this->get_date_time_format(), $this->user_registered($profileuser, false));
            $regip      = $profileuser->wpm_registration_ip;
            $loginip    = $profileuser->wpm_login_ip;

            // Fix issue when no login record shows date in 1970/1969.
            if (! empty($profileuser->wpm_login_date)) {
                $lastlogin = wlm_date($this->get_date_time_format(), (int) $profileuser->wpm_login_date);
            } else {
                $lastlogin = 'No login record yet.';
            }

            if ($this->access_control->current_user_can('wishlistmember3_members/manage')) {
                $txt01            = __('Login Limit', 'wishlist-member');
                $txt01b           = __('IPs Logged in Today', 'wishlist-member');
                $txt02            = __('Special Values:', 'wishlist-member');
                $txt03            = __('<strong>0</strong> or Blank: Use default settings', 'wishlist-member');
                $txt04            = __('<strong>-1</strong>: No limit for this user', 'wishlist-member');
                $loginlimit       = <<<STRING
				<tr valign="top">
					<th scope="row">{$txt01}</th>
					<td>
						<input type="text" name="wpm_login_limit" value="{$profileuser->wpm_login_limit}" size="3" style="width:50px" /> IPs per day<br />
						{$txt02}<br />
							&raquo; {$txt03}<br />
							&raquo; {$txt04}
					</td>
				</tr>
STRING;
                $current_loggedin = (array) $profileuser->wpm_login_counter;
                $today            = wlm_date('Ymd');
                foreach ((array) $current_loggedin as $k => $v) {
                    if (
                        $v != $today
                    ) {
                        unset($current_loggedin[ $k ]);
                    }
                }
                if (count($current_loggedin)) {
                    $reset_limit_counter  = __('Reset Limit Counter', 'wishlist-member');
                    $reset_limit_counter2 = '<div><label><input type="checkbox" name="wlm_reset_limit_counter" value="1" /> ' . $reset_limit_counter . '</label></div>';
                    $current_loggedin     = implode('<br />', array_keys((array) $current_loggedin));
                } else {
                    $current_loggedin = __('This user has not yet logged in for the day', 'wishlist-member');
                }

                $reset_limit_counter2 = isset($reset_limit_counter2) ? $reset_limit_counter2 : '';
                $current_loggedin     = <<<STRING
				<tr valign="top">
					<th scope="row">{$txt01b}</th>
					<td>
						{$current_loggedin}
						{$reset_limit_counter2}
					</td>
STRING;
            }

            $delete = '';
            if ($current_user->ID != $profileuser->ID && $profileuser->ID > 1) {
                $txt01  = __('Update Member Profile', 'wishlist-member');
                $txt02  = __('Delete This Member', 'wishlist-member');
                $txt03  = __('Warning!\\n\\nAre you sure you want to delete this user?', 'wishlist-member');
                $txt04  = __('Last Warning!\\n\\nAre you really sure that you want to delete this user?\\nNote that this action cannot be undone.', 'wishlist-member');
                $txt05  = __('Send Reset Password Link to User', 'wishlist-member');
                $delete = <<<STRING
				<tr valign="top">
					<th scope="row"></th>
					<td>
						<input type="hidden" name="user_login" value="{$profileuser->user_login}">
						<input class="button-primary" type="submit" value="{$txt01}" />
						<input class="button-secondary" type="submit" name="wpm_send_reset_email" value="{$txt05}" />
						&nbsp;&nbsp;
						<input class="button-secondary" type="submit" name="wpm_delete_member" value="{$txt02}" onclick="if(confirm('{$txt03}') && confirm('{$txt04}')){this.form.pass1.value='';return true;}else{return false;}" />
					</td>
				</tr>
STRING;
            }

            $txt01 = __('Membership Level', 'wishlist-member');
            $txt02 = __('Registered', 'wishlist-member');
            $txt03 = __('Email', 'wishlist-member');
            $txt05 = __('Date', 'wishlist-member');
            $txt06 = __('Last Login', 'wishlist-member');

            $wpmstuff = <<<STRING
			<h3>WishList Member</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">{$txt01}</th>
					<td>{$options}</td>
				</tr>
				{$mailinglist}
				{$wlm_feed_url}
				<tr valign="top">
					<th scope="row">{$txt02}</th>
					<td>{$txt03}: {$profileuser->user_email} &nbsp; <br />{$txt05}: {$registered}<br />IP: {$regip} &nbsp; </td>
				</tr>
				<tr valign="top">
					<th scope="row">{$txt06}</th>
					<td>{$txt05}: {$lastlogin}<br />IP: {$loginip} &nbsp; </td>
				</tr>
				{$loginlimit}
				{$current_loggedin}
				{$delete}
			</table>

			{$addresssection}

			{$data_privacy}

			{$custom_fields}

STRING;
        } else {
            $wpmstuff = "<table class='form-table'>{$consent_to_market}{$mailinglist}{$wlm_feed_url}</table>{$addresssection}{$custom_fields}";
        }
        ?>
        <div id="WishListMemberUserProfile">
            <?php
                $base_kses = [
                    'class' => true,
                    'id'    => true,
                    'style' => true,
                ];
                echo wp_kses(
                    $wpmstuff,
                    [
                        'strong' => $base_kses,
                        'br'     => $base_kses,
                        'div'    => $base_kses,
                        'h3'     => $base_kses,
                        'span'   => $base_kses,
                        'td'     => $base_kses,
                        'label'  => $base_kses,
                        'a'      => $base_kses + ['href' => true],
                        'input'  => $base_kses + [
                            'checked'  => true,
                            'name'     => true,
                            'onclick'  => true,
                            'readonly' => true,
                            'size'     => true,
                            'style'    => true,
                            'type'     => true,
                            'value'    => true,
                            'width'    => true,
                        ],
                        'option' => $base_kses + [
                            'value'    => true,
                            'selected' => true,
                        ],
                        'select' => $base_kses + ['name' => true],
                        'table'  => $base_kses + [
                            'cellpadding' => true,
                            'cellspacing' => true,
                        ],
                        'th'     => $base_kses + ['scope' => true],
                        'tr'     => $base_kses + ['valign' => true],
                    ]
                )
            ?>
        <input type="hidden" name="wlm_updating_profile" value="1" />
        </div>
        <?php
        $nodeIndex = $this->access_control->current_user_can('wishlistmember3_members/manage') ? 0 : 3;
        ?>
        <script type="text/javascript">
            function MoveWLMember(){
                try{
                    var x=document.getElementById('WishListMemberUserProfile');
                    var p=x.parentNode;
                    var s=p.getElementsByTagName('h3');
                    p.insertBefore(x,s[<?php echo (int) $nodeIndex; ?>]);
                }catch(e){}
            }
            MoveWLMember();
        </script>
        <?php
    }
    public function _privacy_user_request_mergecodes($data)
    {
        $user = $this->get_user_data($data['email']);
        if (! $user) {
            $user = $data['message_recipient'];
        }
        if (! $user) {
            return false;
        }

        $merge_codes = [
            '[firstname]'  => $user->first_name,
            '[lastname]'   => $user->last_name,
            '[username]'   => $user->user_login,
            '[email]'      => $data['email'] ? $data['email'] : $user->user_email,
            '[request]'    => $data['description'] ? $data['description'] : '###DESCRIPTION###',
            '[expiration]' => $data['expiration'] ? $data['expiration'] : '###EXPIRATION###',
            '[link]'       => $data['link'] ? $data['link'] : '###LINK###',
        ];

        foreach ($data as $key => $value) {
            if (! is_object($value)) {
                $merge_codes[ '[' . $key . ']' ] = sprintf('%s', $value);
            }
        }
        return $merge_codes;
    }

    public function _privacy_process_email_template($content, $template, $data)
    {
        $merge_codes = $this->_privacy_user_request_mergecodes($data);
        if (! $merge_codes) {
            return $content;
        }

        $content            = str_replace(array_keys($merge_codes), array_values($merge_codes), $template);
        $this->sending_mail = true;
        return $content;
    }

    public function privacy_user_request_email_subject($content, $blogname, $data)
    {
        $template = wlm_trim($this->get_option('privacy_email_template_request_subject'));
        if (! $template) {
            return $content;
        }
        return $this->_privacy_process_email_template($content, $template, $data);
    }
    public function privacy_user_request_email($content, $data)
    {
        $template = wlm_trim($this->get_option('privacy_email_template_request'));
        if (! $template) {
            return $content;
        }
        return $this->_privacy_process_email_template($content, $template, $data);
    }

    public function privacy_user_delete_email($content, $data)
    {
        $template = wlm_trim($this->get_option('privacy_email_template_delete'));
        if (! $template) {
            return $content;
        }
        $this->mail_subject = $this->_privacy_process_email_template('', wlm_trim($this->get_option('privacy_email_template_delete_subject')), $data);
        return $this->_privacy_process_email_template($content, $template, $data);
    }

    public function privacy_personal_data_email($content, $request_id)
    {
        $template = wlm_trim($this->get_option('privacy_email_template_download'));
        if (! $template) {
            return $content;
        }
        $request = wp_get_user_request_data($request_id);
        if (! $request) {
            return $content;
        }

        $data               = [
            'email' => $request->email,
        ];
        $this->mail_subject = $this->_privacy_process_email_template('', wlm_trim($this->get_option('privacy_email_template_download_subject')), $data);
        return $this->_privacy_process_email_template($content, $template, $data);
    }

    public function register_privacy_personal_data_eraser($erasers)
    {
        $erasers['wishlist-member-user'] = [
            'eraser_friendly_name' => __('WishList Member', 'wishlist-member'),
            'callback'             => [$this, 'privacy_personal_data_eraser'],
        ];

        return $erasers;
    }

    public function privacy_personal_data_eraser($email_address)
    {
        $user = get_user_by('email', $email_address);

        if (! $user) {
            return new \WP_Error('wlm-personal-data-eraser_invalid-email', __('Invalid email address', 'wishlist-member'));
        }

        $messages      = [];
        $items_removed = 0;

        // Delete last login date.
        $this->Delete_UserMeta($user->ID, 'wpm_login_date');
        // $messages[] = __( 'Last login date erased', 'wishlist-member' );
        ++$items_removed;
        // Delete last login IP.
        $this->Delete_UserMeta($user->ID, 'wpm_login_ip');
        // $messages[] = __( 'Last login IP erased', 'wishlist-member' );
        ++$items_removed;
        // Delete registration IP.
        $this->Delete_UserMeta($user->ID, 'wpm_registration_ip');
        // $messages[] = __( 'Registration IP erased', 'wishlist-member' );
        ++$items_removed;
        // Delete login counter.
        $this->Delete_UserMeta($user->ID, 'wpm_login_counter');
        // $messages[] = __( 'Login counter erased', 'wishlist-member' );
        ++$items_removed;

        return [
            'items_removed'  => $items_removed,
            'items_retained' => 0,
            'messages'       => $messages,
            'done'           => true,
        ];
    }

    public function register_privacy_personal_data_exporter($exporters)
    {
        $exporters['wishlist-member-user'] = [
            'exporter_friendly_name' => __('WishList Member Data', 'wishlist-member'),
            'callback'               => [$this, 'privacy_personal_data_exporter'],
        ];

        return $exporters;
    }

    public function privacy_personal_data_exporter($email_address)
    {
        $data_to_export        = [];
        $user_data_to_export   = [];
        $user_levels_to_export = [];
        $user_ppps_to_export   = [];

        $user = new \WishListMember\User($email_address, true);
        unset($user->WL);
        if (! $user) {
            return [
                'data' => $user,
                'done' => true,
            ];
        }

        $user = json_decode(json_encode($user), true);

        // User Data.
        $names = [
            'wlm_feed_url'              => __('Private RSS Feed', 'wishlist-member'),
            'wpm_registration_ip'       => __('Registration IP Address', 'wishlist-member'),
            'custom_terms_of_service'   => __('Terms of Service', 'wishlist-member'),
            'wpm_login_date'            => __('Last Login Date', 'wishlist-member'),
            'wpm_login_ip'              => __('Last Login IP', 'wishlist-member'),
            'wpm_useraddress'           => __('Address', 'wishlist-member'),
            'sequential'                => __('Sequential Upgrade', 'wishlist-member'),
            'custom_stripe_cust_id'     => __('Stripe ID', 'wishlist-member'),
            'stripe_cust_id'            => __('Stripe ID', 'wishlist-member'),
            'eway_cust_id'              => __('eWay ID', 'wishlist-member'),
            'wlminfusionsoft_contactid' => __('Infusionsoft ID', 'wishlist-member'),
            'wlm_password_hint'         => __('Password Hint', 'wishlist-member'),
        ];

        foreach ($names as $key => $name) {
            $value = $user['user_info']['data'][ $key ];
            switch ($key) {
                case 'wpm_login_date':
                    $value = $value > 1 ? wlm_date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'wpm_useraddress':
                    if (! is_array($value)) {
                        $value = [];
                    }
                    foreach ($value as &$v) {
                        $v = wlm_trim($v);
                    }
                    unset($v);
                    if ('Select Country' == $value['country']) {
                        $value['country'] = '';
                    }
                    $value = trim(preg_replace('/\n+/', '<br>', implode("\n", $value)));
                    break;
                case 'custom_terms_of_service':
                    $value = $value ? __('Accepted', 'wishlist-member') : '';
                    break;
                default:
                    $value = wlm_trim($value);
            }
            if ($value) {
                $user_data_to_export[] = [
                    'name'  => $name,
                    'value' => $value,
                ];
            }
        }

        $fields = array_diff_key($user['user_info']['data']['wldata'], $names);
        if ($fields) {
            foreach ($fields as $name => $value) {
                if (! preg_match('/^custom_/', $name)) {
                    continue;
                }
                if (is_array($value)) {
                    $value = implode('<br>', $value);
                }

                if (! is_scalar($value)) {
                    continue;
                }

                if ($value) {
                    $user_data_to_export[] = [
                        'name'  => ucwords(strtolower(preg_replace('/[^A-Za-z0-9]+/', ' ', preg_replace('/^custom_/', '', $name)))),
                        'value' => $value,
                    ];
                }
            }
        }

        $data_to_export[] = [
            'group_id'    => 'wishlist-member-user',
            'group_label' => __('WishList Member User Data', 'wishlist-member'),
            'item_id'     => "wlm-user-{$user->ID}",
            'data'        => $user_data_to_export,
        ];

        // Membership Levels.
        if ($user['Levels']) {
            foreach ($user['Levels'] as $level_id => $level) {
                if ($level['Active']) {
                    $value = sprintf('%s : %s<br>%s : %s', __('Transaction ID', 'wishlist-member'), $level['TxnID'], __('Registration Date', 'wishlist-member'), wlm_date('Y-m-d H:i:s', $level['Timestamp']));
                    if ($level['ExpiryDate']) {
                        $value .= sprintf('<br>%s : %s', __('Expiration Date', 'wishlist-member'), wlm_date('Y-m-d H:i:s', $level['ExpiryDate']));
                    }
                    $user_levels_to_export[] = [
                        'name'  => $level['Name'],
                        'value' => $value,
                    ];
                }
            }

            if ($user_levels_to_export) {
                $data_to_export[] = [
                    'group_id'    => 'wishlist-member-user-levels',
                    'group_label' => __('WishList Member Levels', 'wishlist-member'),
                    'item_id'     => "wlm-userlevels-{$user->ID}",
                    'data'        => $user_levels_to_export,
                ];
            }
        }

        // Pay Per Posts.
        if ($user['pay_per_posts']) {
            foreach ($user['pay_per_posts']['_all_'] as $post_id) {
                $user_ppps_to_export[] = [
                    'name'  => __('URL', 'wishlist-member'),
                    'value' => get_permalink($post_id),
                ];
            }
            if ($user_ppps_to_export) {
                $data_to_export[] = [
                    'group_id'    => 'wishlist-member-user-ppps',
                    'group_label' => __('WishList Member Pay Per Posts', 'wishlist-member'),
                    'item_id'     => "wlm-userppps-{$user->ID}",
                    'data'        => $user_ppps_to_export,
                ];
            }
        }

        return [
            'data' => $data_to_export,
            'done' => true,
        ];
    }

    public function reset_privacy_template()
    {
        require 'core/InitialValues.php';

        $target        = wlm_post_data()['target'];
        $valid_targets = [
            'privacy_email_template_request',
            'privacy_email_template_download',
            'privacy_email_template_delete',
            'member_unsub_notification_body',
        ];

        $template_names = [
            __('User Request Email Template', 'wishlist-member'),
            __('Download Fulfilled Email Template', 'wishlist-member'),
            __('Erasure Fulfilled Email Template', 'wishlist-member'),
            __('Unsubscribe Notification Email Template', 'wishlist-member'),
        ];
        $template_names = array_combine($valid_targets, $template_names);

        if (! in_array($target, $valid_targets)) {
            return;
        }

        $subject = preg_replace('/_body$/', '', $target) . '_subject';

        $this->save_option($target, $wishlist_member_initial_data[ $target ]);
        $this->save_option($subject, $wishlist_member_initial_data[ $subject ]);

        $_POST = ['msg' => sprintf('%s %s', $template_names[ $target ], __('was reset.', 'wishlist-member'))];
    }

    public function password_hinting($error)
    {
        $user = get_user_by('login', wlm_post_data()['log']);
        if (! $user) {
            return $error;
        }

        $passwordhint = $this->Get_UserMeta($user->ID, 'wlm_password_hint');
        $match_text   = __('The password you entered for the username', 'wishlist-member');
        if (( '' != wlm_trim($passwordhint) )) {
            if (preg_match('/' . $match_text . '/i', $error)) {
                $error .= '<br/ > <strong> ' . __('Password Hint:', 'wishlist-member') . ' </strong>' . $passwordhint;
            }
        }
        return $error;
    }
    public function password_hinting_email()
    {
        echo '<script>
			jQuery(document).ready(function() {

				 // Resize the login form.
				 jQuery("#login").css("width", "340px");
				 // Remove p tag wrap on the get new password button.
				 jQuery("#wp-submit").unwrap();

				jQuery("#wlpasswordhintsubmit").click(function() {
					jQuery("#wlpasswordhintsubmit").attr("disabled", true).val("' . esc_js(__('Sending Pass Hint....', 'wishlist-member')) . '");

					ajaxurl = "' . esc_url(admin_url('admin-ajax.php')) . '";

					jQuery.post(
						ajaxurl,
						{
							action: "PasswordHintSubmit",
							user_login: jQuery("#user_login").val()
						},
						function(data,status){
							if(status!="success"){
								message = "' . esc_js(__('Connection problem. Please check that you are connected to the internet.', 'wishlist-member')) . '";
							} else if(data.error!="ok") {
								alert(data.error);
								jQuery("#wlpasswordhintsubmit").attr("disabled", false).val("' . esc_js(__('Send Password Hint', 'wishlist-member')) . '");
							} else {
								alert(data.message);
								jQuery("#wlpasswordhintsubmit").fadeOut();
							}
						},
						"json"
					);
					return false;
				});
			});

		</script>';
        echo '<input type="submit"  name="wlpasswordhintsubmit" id="wlpasswordhintsubmit" class="button button-large" value="' . esc_attr__('Send Password Hint', 'wishlist-member') . '" />';
    }

    public function password_hint_submit()
    {

        header('Content-Type: application/json');
        if (strpos(wlm_post_data()['user_login'], '@')) {
                $user_data = get_user_by('email', trim(wlm_post_data()['user_login']));
            if (empty($user_data)) {
                $error = __('There is no user registered with that email address.', 'wishlist-member');
            }
        } else {
                $login = trim(wlm_post_data()['user_login']);
            $user_data = get_user_by('login', $login);
        }

        if (! $user_data) {
            $error = 'Invalid username or e-mail.';
        } else {
                $macros = [
                    '[passwordhint]' => wlm_trim($this->Get_UserMeta($user_data->data->ID, 'wlm_password_hint')),
                ];

                if ($macros['passwordhint']) {
                    $error = __('The Username/Email you entered does not have a Password Hint.', 'wishlist-member');
                } else {
                    $this->send_email_template('password_hint', $user_data->data->ID, $macros);
                    $message = __('Successfully submitted password hint, please check your email.', 'wishlist-member');
                    $error   = __('ok', 'wishlist-member');
                }
        }

        wp_send_json(
            [
                'error'   => $error,
                'message' => $message,
            ]
        );
    }

    /**
     * Log user data when user is deleted.
     *
     * @param integer $user_id  User ID.
     * @param integer $reassign User ID to reassign posts and links to.
     * @param object  $user     User object.
     */
    public function log_user_delete($user_id, $reassign = null, $user = null)
    {
        if (empty($user)) {
            return;
        }
        $user->first_name = $user->first_name;
        $user->last_name = $user->last_name;
        $user = json_decode(json_encode($user), true)['data'];
        unset($user['user_pass']);
        \WishListMember\Logs::add($user_id, 'user', 'deleted', $user);
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('clear_auth_cookie', [$wlm, 'get_user_id_before_logout']);
        add_action('delete_user', [$wlm, 'delete_user']);
        add_action('deleted_user', [$wlm, 'deleted_user']);
        add_action('edit_user_profile', [$wlm, 'profile_page']);
        add_action('edit_user_profile_update', [$wlm, 'profile_update'], 1);
        add_action('show_user_profile', [$wlm, 'profile_page']);
        add_action('wp_ajax_nopriv_PasswordHintSubmit', [$wlm, 'password_hint_submit']);
        add_action('wp_login', [$wlm, 'login'], 10, 2);
        add_action('wp_logout', [$wlm, 'logout']);
        add_filter('login_errors', [$wlm, 'password_hinting']);
        $wlm->get_option('password_hinting') && add_filter('lostpassword_form', [$wlm, 'password_hinting_email']);
        add_filter('user_confirmed_action_email_content', [$wlm, 'privacy_user_delete_email'], 10, 2);
        add_filter('user_request_action_email_content', [$wlm, 'privacy_user_request_email'], 10, 2);
        add_filter('user_request_action_email_subject', [$wlm, 'privacy_user_request_email_subject'], 10, 3);
        add_filter('wp_privacy_personal_data_email_content', [$wlm, 'privacy_personal_data_email'], 10, 2);
        add_filter('wp_privacy_personal_data_erasers', [$wlm, 'register_privacy_personal_data_eraser']);
        add_filter('wp_privacy_personal_data_exporters', [$wlm, 'register_privacy_personal_data_exporter']);
        add_action('delete_user', [$wlm, 'log_user_delete'], 10, 3);
    }
);
