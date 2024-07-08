<?php

/**
 * Registration Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Registration Methods trait
*/
trait Registration_Methods
{
    use Registration_Methods_Deprecated;

    /**
     * Force Registration Form Scripts and Styles
     *
     * @var boolean
     */
    public $force_registrationform_scripts_and_styles = false;

    /**
     * Registration Page Form Data
     *
     * @var array
     */
    public $reg_page_form_data = [];

    /**
     * ReCaptcha Response
     *
     * @return boolean
     */
    public function recaptcha_response()
    {
        // Recaptcha.
        $recaptcha = true;
        if (isset(wlm_post_data()['g-recaptcha-response'])) {
            $recaptcha_public  = $this->get_option('recaptcha_public_key');
            $recaptcha_private = $this->get_option('recaptcha_private_key');
            if ($recaptcha_public && $recaptcha_private) {
                if (! function_exists('recaptcha_verify')) {
                    require_once $this->plugin_dir . '/extlib/recaptchalib.php';
                }
                $recaptcha = recaptcha_verify($recaptcha_private, wlm_server_data()['REMOTE_ADDR'], wlm_post_data()['g-recaptcha-response']);

                if ($recaptcha->is_valid) {
                    $recaptcha = true;
                } else {
                    $recaptcha = false;
                }
            }
        }
        return $recaptcha;
        // End recaptcha.
    }

    /**
     * Generate Recaptcha HTML for level
     *
     * @param  integer $level_id Level ID.
     * @return string
     */
    public function generate_recaptcha_html($level_id)
    {
        $wpm_levels = $this->get_option('wpm_levels');
        if ($this->is_ppp_level($level_id)) {
            $this->inject_ppp_settings($wpm_levels, $level_id);
        }
        $captcha_html = '';
        if (! empty($wpm_levels[ $level_id ]['requirecaptcha'])) {
            $recaptcha_public  = $this->get_option('recaptcha_public_key');
            $recaptcha_private = $this->get_option('recaptcha_private_key');
            if ($recaptcha_public && $recaptcha_private) {
                if (! function_exists('recaptcha_get_html')) {
                    require_once $this->plugin_dir . '/extlib/recaptchalib.php';
                }
                $error        = $error ?? '';
                $captcha_html = recaptcha_get_html($recaptcha_public, $error, is_ssl());
            }
        }
        return $captcha_html;
    }

    /**
     * Registers new users to WordPress and
     * assigns the correct membership level
     *
     * @param  array   $data                User data array.
     * @param  string  $wpm_errmsg          Passed by reference, we save the error message here.
     * @param  boolean $send_welcome_email  True to send registration email or not.
     * @param  boolean $notify_admin        True to notify admin via email of this registration.
     * @param  integer $min_password_length Minimum password length. Defaults to user specified length in settings section.
     * @param  string  $pending_status      Pending status text.
     * @return integer|boolean             User ID on success or false on error
     */
    public function wpm_register($data, &$wpm_errmsg, $send_welcome_email = true, $notify_admin = true, $min_password_length = null, $pending_status = null)
    {
        global $wpdb;

        // Include the required WordPress functions.
        require_once ABSPATH . WPINC . '/pluggable.php';

        do_action_deprecated('wishlistmember3_pre_register', [$data], '3.10', 'wishlistmember_pre_register');
        do_action_ref_array('wishlistmember_pre_register', [&$data]);

        if (! isset($data['password1']) && ! isset($data['password2']) && ! empty($data['password'])) {
            $data['password1'] = $data['password'];
            $data['password2'] = $data['password'];
        }

        $is_multisite = is_multisite();
        $blog_id      = get_current_blog_id();

        $registered_by_admin = true === wlm_admin_in_admin();

        if ($registered_by_admin && $this->get_option('privacy_enable_consent_to_market')) {
            $data['consent_to_market'] = 1;
        }

        // Fix for the issue where WordPress MU doesn't allow Uppercase Usernames.
        remove_filter('sanitize_user', 'strtolower');

        $custom_fields = [];
        if (! empty(wlm_post_data()['custom_fields'])) {
            $custom_fields = explode(',', wlm_post_data()['custom_fields']);
            foreach ($custom_fields as $key => $custom_field) {
                $custom_fields[$key] = preg_replace('/ +/', '_', wlm_trim($custom_field));
            }
        }

        $required_fields = [];
        if (! empty(wlm_post_data()['required_fields'])) {
            $required_fields = explode(',', wlm_post_data()['required_fields']);
            foreach ($required_fields as $key => $required_field) {
                $required_fields[$key] = preg_replace('/ +/', '_', wlm_trim($required_field));
            }
        }

        $custom_form = isset(wlm_post_data()['custom_fields']) && isset(wlm_post_data()['required_fields']);

        $required_error  = false;
        $required_fields = array_intersect($required_fields, $custom_fields);
        foreach ($required_fields as $required_field) {
            if (empty(wlm_post_data()[ $required_field ])) {
                $required_error = true;
                break;
            }

            foreach ((array) wlm_post_data()[ $required_field ] as $req_fld) {
                $value = trim($req_fld);

                if (( empty($value) ) && ( '0' !== (string) $value )) {
                    $required_error = true;
                    break 2;
                }
            }
        }

        // Remove fields that go into the wp profile.
        $custom_fields = array_diff($custom_fields, ['website', 'aim', 'yim', 'jabber', 'biography', 'nickname', 'firstname', 'lastname']);
        // Remove fields that go into wpm_useraddress.
        $custom_fields = array_diff($custom_fields, ['company', 'address1', 'address2', 'city', 'state', 'zip', 'country']);

        // Determine the minimum password length.
        if (is_null($min_password_length)) {
            $min_password_length = $this->get_option('min_passlength');
        }
        $min_password_length += 0;
        if (! $min_password_length) {
            $min_password_length = 8;
        }

        /*
         * are we merging? if so, load $mergewith with
         * data of user to merge with. $mergewith is used
         * to merge temp accounts generated by shopping
         * cart registrations to the user info provided
         * by the user when he completes the registration
         */
        $mergewith = [];
        if (isset($data['mergewith']) && $data['mergewith']) {
            $mergewith = $this->get_user_data($data['mergewith']);
        }

        // Is this a temp account?
        $tempacct = 'temp_' . md5($data['orig_email']) === $data['email'];

        // Load membership levels.
        $wpm_levels = apply_filters('wishlistmember_registration_levels', $this->get_option('wpm_levels'));

        // Load blacklist data.
        $blacklist = $this->check_blacklist($data['email']);

        // Check if for approval registration.
        $is_forapproval = $this->is_for_approval_registration($data['wpm_id']);
        if ($is_forapproval) {
            $pending_status = 'Registered For Approval';
            $wpm_newid      = time();
            sleep(1);
            if ('PinPayments' === $is_forapproval['name']) {
                $data['sctxnid'] = 'SP-' . $data['wpm_id'] . '-' . $wpm_newid;
                $pending_status  = 'Pin Payments Confirmation';
            }
            $data['wpm_id']      = $is_forapproval['level'];
            $registered_by_admin = false;
        }

        // Blacklist checking.
        if ($blacklist) {
            switch ($blacklist) {
                case 1:
                    $wpm_errmsg = $this->get_option('blacklist_email_message');
                    break;
                case 2:
                    $wpm_errmsg = $this->get_option('blacklist_ip_message');
                    break;
                case 3:
                    $wpm_errmsg = $this->get_option('blacklist_email_ip_message');
                    break;
            }
            return false;
        }
        // Validate username.
        if (! wlm_trim($data['username']) || ! validate_username($data['username'])) {
            $wpm_errmsg = __('Please enter a username', 'wishlist-member');
            return false;
        }
        // Check username length - cannot be more than 50 characters.
        if (strlen($data['username']) > 50) {
            $wpm_errmsg = __('Username cannot be more than 50 characters in length. Please enter a shorter username.', 'wishlist-member');
            return false;
        }
        // Check if username already exists.
        $xid = username_exists($data['username']);
        if ($xid) {
            if (! is_multisite() || is_user_member_of_blog($xid)) {
                $wpm_errmsg = __('The username you chose already exists.  Please try another one.', 'wishlist-member');
                if (wlm_get_data()['reg'] && empty($wpm_levels[ wlm_get_data()['reg'] ]['disableexistinglink'])) {
                    switch ($this->get_option('FormVersion')) {
                        case 'improved':
                            $wpm_errmsg .= '<br /><br />' . esc_html__('If you are already a member and are upgrading your membership access, please select the "I have an existing account" option below.', 'wishlist-member');
                            break;
                        case 'themestyled':
                            $wpm_errmsg .= '<br /><br />' . esc_html__('If you are already a member and are upgrading your membership access, please select "Existing Account" above.', 'wishlist-member');
                            break;
                        default:
                            $wpm_errmsg .= '<br /><br />' . esc_html__('If you are already a member and are upgrading your membership access, please click the "Existing Members" link below.', 'wishlist-member');
                    }
                }
                return false;
            }
        }
        // Check for firstname and lastname.
        if (! ( wlm_trim($data['firstname']) && wlm_trim($data['lastname']) ) && ! $custom_form && ! $registered_by_admin) {
            $wpm_errmsg = __('Please enter your first name and your last name.', 'wishlist-member');
            return false;
        }
        // Validate email.
        if (! wlm_is_email(wlm_trim($data['email'])) && ! ( wlm_is_email($data['orig_email']) && $tempacct )) {
            $wpm_errmsg = __('Please enter a valid email address.', 'wishlist-member');
            return false;
        }
        // Check if email already exists.
        $xid = email_exists($data['email']);
        if ($xid && wlm_arrval($mergewith, 'user_email') !== $data['email']) {
            if (! is_multisite() || is_user_member_of_blog($xid)) {
                $wpm_errmsg = __('The email you entered is already in our database.', 'wishlist-member');
                return false;
            }
        }
        // Check email length - cannot be more than 100 characters.
        if (strlen($data['email']) > 100) {
            $wpm_errmsg = __('Email address cannot be more than 100 characters in length. Please enter a shorter email address.', 'wishlist-member');
            return false;
        }
        // Validate password length.
        if (strlen(wlm_trim($data['password1'])) < $min_password_length) {
            // Translators: 1: minimum password length.
            $wpm_errmsg = sprintf(__('Password has to be at least %1$d characters long and must not contain spaces.', 'wishlist-member'), $min_password_length);
            return false;
        }
        // Make sure password does not contain \ and spaces.
        $chars = preg_quote(' \\', '/');
        if (preg_match('/[' . $chars . ']/', $data['password1'])) {
            $wpm_errmsg = sprintf(__('Password must not contain backslash(\) and spaces.', 'wishlist-member'), $min_password_length);
            return false;
        }
        // Validate password strength if enabled.
        if ($this->get_option('strongpassword') && ! wlm_check_password_strength($data['password1']) && ! $tempacct) {
            $wpm_errmsg = __('Please provide a strong password. Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.', 'wishlist-member');
            return false;
        }
        // Check if password1 and password2 matches.
        if ($data['password1'] !== $data['password2']) {
            $wpm_errmsg = __('The passwords you entered do not match.', 'wishlist-member');
            return false;
        }

        if (! $tempacct) {
            // TOS required (data privacy).
            if ($this->get_option('privacy_require_tos_on_registration') && empty($data['tos_required']) && ! $registered_by_admin) {
                $wpm_errmsg = wlm_trim($this->get_option('privacy_require_tos_error_message'));
                return false;
            }
        }

        // Validate reCaptcha.
        if (! $this->recaptcha_response()) {
            if (3 === (int) wlm_post_data()['recaptcha-version']) {
                $wpm_errmsg = __('Are you human? Go back and try it again', 'wishlist-member');
            } else {
                $wpm_errmsg = __('The reCAPTCHA wasn\'t entered correctly. Go back and try it again', 'wishlist-member');
            }
            return false;
        }

        if ($required_error) {
            $wpm_errmsg = __('All required fields must be filled-in.', 'wishlist-member');
            return false;
        }

        // This is an extra filter users can use if they want to add extra validation.
        $custom_filter_validation = apply_filters('wishlistmember_process_registration_filter', false);

        if ($custom_filter_validation) {
            // If true then set the returned data on $custom_filter_validation as the error message.
            $wpm_errmsg = $custom_filter_validation;
            return false;
        }

        // Sanitize the lastname, firstname and email.
        $data['firstname'] = $this->CleanInput($data['firstname'] ?? '');
        $data['lastname']  = $this->CleanInput($data['lastname'] ?? '');
        $data['email']     = $this->CleanInput($data['email'] ?? '');
        $data['reg_date']  = $this->CleanInput($data['reg_date'] ?? '');

        $nickname = trim(empty($data['nickname']) ? $data['firstname'] : $data['nickname']);

        // Generate userdata.
        $userdata = [
            'user_pass'       => wlm_trim($data['password1'] ?? ''),
            'user_login'      => wlm_trim($data['username'] ?? ''),
            'user_email'      => wlm_trim($data['email'] ?? ''),
            'user_registered' => wlm_trim($data['reg_date'] ?? ''),
            'nickname'        => $nickname,
            'first_name'      => wlm_trim($data['firstname'] ?? ''),
            'last_name'       => wlm_trim($data['lastname'] ?? ''),
            'display_name'    => wlm_trim($data['firstname'] ?? '') . ' ' . wlm_trim($data['lastname'] ?? ''),
            'user_url'        => wlm_trim($data['website'] ?? ''),
            'aim'             => wlm_trim($data['aim'] ?? ''),
            'yim'             => wlm_trim($data['yim'] ?? ''),
            'jabber'          => wlm_trim($data['jabber'] ?? ''),
            'description'     => wlm_trim($data['biography'] ?? ''),
        ];

        // Wpm_useraddress.
        $wpm_useraddress = array_intersect_key($data, array_flip(['company', 'address1', 'address2', 'city', 'state', 'zip', 'country']));

        // Set role for user.
        if ($wpm_levels[ $data['wpm_id'] ]['role']) {
            $userdata['role'] = $wpm_levels[ $data['wpm_id'] ]['role'];
        }

        /*
         * create the user
         * if wlm_arrval( $mergewith, 'ID' ) is set then we are merging with
         * a temp account generated by one of the payment provider
         * registrations. we merge the info passed by the user
         * with the temp account using wp_update_user
         *
         * if we're not merging then we create the user using wlm_insert_user
         */
        if (wlm_arrval($mergewith, 'ID')) {
            // Added to prevent WP from sending the password/email change email.
            add_filter('send_password_change_email', '__return_false');
            add_filter('send_email_change_email', '__return_false');

            $userdata['ID']            = wlm_arrval($mergewith, 'ID');
            $userdata['user_nicename'] = '';
            $id                        = wp_update_user($userdata);

            // Data privacy : marketing consent.
            if ($this->get_option('privacy_enable_consent_to_market')) {
                $consent_to_market = wlm_arrval($data, 'consent_to_market') + 0;
                $this->Update_UserMeta($id, 'wlm_consent_to_market', $consent_to_market);
                if (! $consent_to_market) {
                    $this->Update_UserMeta($id, 'wlm_unsubscribe', 1);
                } else {
                    $this->Update_UserMeta($id, 'wlm_unsubscribe', 0);
                }
            }
        } else {
            $id = wlm_insert_user($userdata);
            if (! $id || is_wp_error($id)) {
                $wp_error = is_wp_error($id);
                if ($wp_error && isset($id->errors['existing_user_login'])) {
                    if (is_multisite()) {
                        $wpm_errmsg = __('The username is already in our database.', 'wishlist-member');
                    } else {
                        $wpm_errmsg = __('The user is already in our database.', 'wishlist-member');
                    }
                } else {
                    $wpm_errmsg = __('There was an error registering the user.', 'wishlist-member');
                }
                return false;
            }

            // If password hinting is enabled, add the password hint to members user options table.
            if ($this->get_option('password_hinting')) {
                $this->Update_UserMeta($id, 'wlm_password_hint', trim(wlm_post_data()['passwordhint']));
            }

            // Data privacy : marketing consent.
            if ($this->get_option('privacy_enable_consent_to_market')) {
                $consent_to_market = wlm_arrval($data, 'consent_to_market') + 0;
                $this->Update_UserMeta($id, 'wlm_consent_to_market', $consent_to_market);
                if (! $consent_to_market) {
                    $this->Update_UserMeta($id, 'wlm_unsubscribe', 1);
                }
            }

            // Data privacy : tos accepted.
            if ($this->get_option('privacy_require_tos_on_registration') && ! $registered_by_admin) {
                $this->Update_UserMeta($id, 'wlm_tos_accepted', 1);
            }
        }

        // No more id=0 check. We simply bail at this point if WP failed.
        if (! $id || is_wp_error($id)) {
            $wpm_errmsg = __('There was an error with the registration and an account could not be created.  Please try again or contact the site administrator for more information.', 'wishlist-member');
            return false;
        }

        /*
         * we repeat the update to make sure we have the password
         * updated because for some reason, wp_update_user does
         * not correctly save the password for new users...
         *
         * I'm no longer sure if this is still needed but no harm
         * done if we just re-update the user with the same info
         * anyway.
         *
         * The story for this goes a long way back to the time when
         * we first added the functionality of allowing users to
         * assign their own usernames and passwords when they go
         * through one of our payment provider integrations
         */

        add_filter('send_password_change_email', '__return_false'); // Added to prevent WP from sending the password change email (since WP 4.3).
        $userdata['ID'] = $id;
        $id             = wp_update_user($userdata);

        // Do fixes if we're doing a merge.
        if (wlm_arrval($mergewith, 'ID')) {
            /*
             * fix the username because temp account's username
             * is in the form of temp_(md5 hash here)
             */
            $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->users}` SET `user_login`=%s WHERE `ID`=%d", $userdata['user_login'], $id));
            wp_update_user($userdata); // Another update to refresh things.
        }

        /*
         * we save registration post and get data if
         * we are not doing a merge
         */
        if (! wlm_arrval($mergewith, 'ID')) {
            // Save registration post.
            $this->Update_UserMeta($id, 'wlm_reg_post', $this->WLMEncrypt($this->orig_post));
            // Save registration get.
            $this->Update_UserMeta($id, 'wlm_reg_get', $this->WLMEncrypt($this->orig_get));
        }

        /*
         * we save additional_levels if it's set
         */
        if (wlm_post_data()['additional_levels']) {
            $this->Update_UserMeta($id, 'additional_levels', wlm_post_data()['additional_levels']);
        }

        /*
         * save custom registration fields
         */
        foreach ($custom_fields as $custom_field) {
            $name  = 'custom_' . $custom_field;
            $value = wlm_post_data()[ $custom_field ];
            $this->Update_UserMeta($id, $name, $value);
        }

        if ($id < 1) {
            $wpm_errmsg = __('An unknown error occured.  Please try again.', 'wishlist-member');
            return false;
        }

        // Save orig_email to usermeta (payment provider stuff).
        if ($data['orig_email']) {
            $this->Update_UserMeta($id, 'wlm_origemail', $data['orig_email']);
        }
        // If its a temporary account, set notification count which also servers as marker for incomplete registrants.
        if ($tempacct && ! wlm_arrval($mergewith, 'ID')) {
            // Initialize data.
            $wlm_incregnotification = [
                'count'    => 0,
                'lastsend' => time(),
                'level'    => $data['wpm_id'],
            ];
            add_user_meta($id, 'wlm_incregnotification', $wlm_incregnotification);
        }
        // If its not a temporary account and is merging delete notification count we set for templorary users.
        if (! $tempacct && wlm_arrval($mergewith, 'ID')) {
            delete_user_meta($id, 'wlm_incregnotification');
        }
        // Save registration IP.
        $this->Update_UserMeta(
            $id,
            'wpm_registration_ip',
            $this->ip_tracking_enabled($id) ? wlm_get_client_ip() : ''
        );

        // Prepare stuff for email merge-codes.
        $payperpost = preg_match('/^payperpost-(\d+)$/', $data['wpm_id'], $match);
        if ($payperpost) {
            $payperpost = get_post($match[1]);
        }
        $macros = [
            '[memberlevel]' => $payperpost ? $payperpost->post_title : wlm_trim($wpm_levels[ $data['wpm_id'] ]['name']),
            '[password]'    => wlm_trim($data['password1']),
            '[confirmurl]'  => get_bloginfo('url') . '/index.php?wlmconfirm=' . $id . '/' . md5(wlm_trim($data['email']) . '__' . wlm_trim($data['username']) . '__' . $data['wpm_id'] . '__' . $this->GetAPIKey()),
        ];

        /*
         *  we check if there's a "need for admin approval" or "email confirmation"
         *  in the level settings, if yes, then add a flag that will delay member from being added to AR
         *  until both all flags are cleared
         */
        $pendingautoresponder = [];

        $isshoppingcartpending = $this->is_pending_shopping_cart_approval($data['wpm_id'], $id);
        if ($isshoppingcartpending && ! $is_forapproval) { // Make sure this is not SC Integration that use approval status.
            $pending_status = $isshoppingcartpending;
        }

        /*
         * check if we need to set admin approval
         */
        $require_admin_approval              = $wpm_levels[ $data['wpm_id'] ]['requireadminapproval'] ?? 0;
        $require_admin_approval_integrations = $wpm_levels[ $data['wpm_id'] ]['requireadminapproval_integrations'] ?? 0;

        $level_for_approval = [
            (bool) ( $require_admin_approval && ! wlm_arrval($mergewith, 'ID') && ! $tempacct && ! $is_forapproval ),
            (bool) ( $require_admin_approval_integrations && ! wlm_arrval($mergewith, 'ID') && $tempacct ),
            (bool) ( ! is_null($pending_status) && ! $is_forapproval ),
        ];

        // Admin approval for pending shoppingcart transactions (Autoresponder Pending).
        if (in_array(true, array_map('boolval', $level_for_approval), true) && ! $registered_by_admin) {
            $pendingautoresponder[] = 'autoresponder_add_pending_admin_approval';
        }

        $require_email_confirmation = $wpm_levels[ $data['wpm_id'] ]['requireemailconfirmation'] ?? 0;

        // Require email confirmation (Autoresponder Pending).
        if (( ( $require_email_confirmation && ! $registered_by_admin ) ) || ( ( $require_email_confirmation && $tempacct ) )) {
            $pendingautoresponder[] = 'autoresponder_add_pending_email_confirmation';
        }

        // We're now using the levels first assigned in the temp account.
        // Merging? Remove user from all levels first.
        if (wlm_arrval($mergewith, 'ID')) {
            if ($this->is_ppp_level($data['wpm_id'])) {
                $data['sctxnid'] = $this->Get_ContentLevelMeta('U-' . $id, substr($data['wpm_id'], 11), 'transaction_id');
            } else {
                $data['sctxnid'] = $this->get_membership_levels_txn_id($id, $data['wpm_id']);
            }

            // Adding this here cause when merging, ar_subscribe aren't.
            // Be called anymore as we are just using the membership.
            // Levels first assigned in the temp account.
            if (empty($pendingautoresponder)) {
                $this->ar_subscribe(wlm_trim($data['firstname']), wlm_trim($data['lastname']), wlm_trim($data['email']), $data['wpm_id']);
            }

            // Also adding this here, same reason as with ar_subscribe above.
            $this->webinar_subscribe(wlm_trim($data['firstname']), wlm_trim($data['lastname']), wlm_trim($data['email']), $data['wpm_id']);
        }

        // Add new member to right level.
        $this->set_membership_levels(
            $id,
            $data['wpm_id'],
            [
                'process_autoresponders' => ! $tempacct,
                'process_webinars'       => ! $tempacct,
                'pending_autoresponders' => $pendingautoresponder,
                'keep_existing_levels'   => true,
            ]
        );

        // Turn on user's sequential upgrade.
        $this->is_sequential($id, true);

        // Save sctxnid.
        if (isset($data['sctxnid']) && $data['sctxnid']) {
            if ($this->is_ppp_level($data['wpm_id'])) {
                $this->add_user_post_transaction_id($id, substr($data['wpm_id'], 11), $data['sctxnid']);
            } else {
                $this->set_membership_level_txn_id($id, $data['wpm_id'], $data['sctxnid']);
            }
        }

        // Let's also save the user's wpm_useraddress if it's specified.
        if (! empty(wlm_post_data()['wpm_useraddress']) || ! empty($wpm_useraddress)) { // We only save the address if it's specified.
            $wpm_useraddress = array_merge((array) wlm_post_data()['wpm_useraddress'], (array) $wpm_useraddress);
            $this->Update_UserMeta($id, 'wpm_useraddress', $wpm_useraddress);
        }

        // Update level count.
        if ($wpm_levels[ $data['wpm_id'] ]) {
            ++$wpm_levels[ $data['wpm_id'] ]['count'];
        }
        $this->save_option('wpm_levels', $wpm_levels);

        // Set for approval and send approval email (if so configured).
        if (in_array(true, array_map('boolval', $level_for_approval), true) && ! $registered_by_admin) {
            $this->level_for_approval($data['wpm_id'], $id, $pending_status ? $pending_status : true);

            if (! $tempacct) {
                // Send require admin approval email.
                if (empty($data['sctxnid'])) { // Free registration.
                    $this->send_email_template('require_admin_approval', $id, $macros); // Send to user.
                    $this->send_email_template('require_admin_approval_admin', $id, $macros, $this->get_option('email_sender_address')); // Send to admin.
                } else { // We assume it's a paid registration if transaction ID is specified.
                    $this->send_email_template('require_admin_approval_paid', $id, $macros); // Send to user.
                    $this->send_email_template('require_admin_approval_paid_admin', $id, $macros, $this->get_option('email_sender_address')); // Send to admin.
                }
                $send_welcome_email = false;
            }
        } elseif ($is_forapproval) { // For payment providers that uses for approval status eg. PinPayments, thats the purpose of $is_forapproval.
            $this->level_for_approval($data['wpm_id'], $id, $pending_status ? $pending_status : true);
        }

        // Set for email confirmation and send confirmation email (if so configured).
        $require_email_confirmation = apply_filters(
            'wishlistmember3_wpmregister_send_email_confirmation',
            $require_email_confirmation && ! $registered_by_admin,
            $require_email_confirmation,
            $registered_by_admin
        );

        if ($require_email_confirmation) {
            $this->level_unconfirmed($data['wpm_id'], $id, true);

            if (! $tempacct) {
                $this->send_email_template('email_confirmation', $id, $macros);
                $send_welcome_email = false;

                $email_confirmation_reminder = [
                    'count'    => 0,
                    'lastsend' => time(),
                    'wpm_id'   => $data['wpm_id'],
                ];
                add_user_meta($id, 'wlm_email_confirmation_reminder', $email_confirmation_reminder);
            }
        }

        // Send the welcome email.
        if ($send_welcome_email && ! $tempacct) {
            $this->send_email_template('registration', $id, $macros);
        }

        // Notify the admin via e-mail.
        if ($notify_admin) {
                $admin_macros = $macros;
            if ('0' !== $this->get_option('mask_passwords_in_emails')) {
                $admin_macros['[password]'] = '********';
            }
                $this->send_email_template('admin_new_member_notice', $id, $admin_macros, $this->get_option('email_sender_address'));
        }

        // Delete the registration page security cookie.
        $this->registration_cookie('x', $dummy);

        /*
         * auto login
         */
        if (false === wlm_admin_in_admin() && ! $tempacct) {
            $this->wpm_auto_login($id);
        }

        /*
         * delete the wpmu cookie
         * mu means "Merge User"
         */
        if (! headers_sent()) {
            wlm_setcookie('wpmu', '', time() - 3600, '/');
        }

        /**
         * Is Transient IP specified?
         */
        if (isset(wlm_post_data()['transient_hash'])) {
            $this->set_transient_hash(wlm_post_data()['transient_hash'], $data['orig_email']);
        }

        $this->schedule_sync_membership();
        $this->sync_content('posts');

        // Hook triggere when new user is added.
        do_action('wishlistmember_user_registered', $id, $data);
        // Finally, now we can return the new user's ID.
        return $id;
    }

    /**
     * Registers existing user to a membership level
     *
     * @param  array   $data               User data array.
     * @param  string  $wpm_errmsg         Passed by reference, we save the error message here.
     * @param  boolean $send_welcome_email True to send registration email or not, if "sendlevel", use level settings.
     * @param  boolean $notify_admin       True to notify admin via email of this registration.
     * @param  boolean $bypass_user_auth   Bypass user authentication.
     * @return integer|boolean            User ID on success or false on error
     */
    public function wpm_register_existing($data, &$wpm_errmsg, $send_welcome_email = true, $notify_admin = true, $bypass_user_auth = false)
    {
        // Include the required WordPress functions.
        require_once ABSPATH . 'wp-admin/includes/user.php';

        do_action_deprecated('wishlistmember3_pre_register_existing', [$data], '3.10', 'wishlistmember_pre_register_existing');
        do_action('wishlistmember_pre_register_existing', $data);

        // Load the membership levels.
        $registration_level = new \WishListMember\Level(
            $data['wpm_id'],
            apply_filters('wishlistmember_registration_levels', $this->get_option('wpm_levels'))
        );

        // Set blacklist to zero.
        $blacklist = 0;

        $resetexpired        = false;
        $expired             = false;
        $resetactive         = false;
        $levelexpires        = false;
        $levelexpires_cal    = false;
        $levelexpire_regdate = false;

        // Check if for approval registration.
        $is_forapproval = $this->is_for_approval_registration($data['wpm_id']);
        if ($is_forapproval) {
            $wpm_newid = time();
            sleep(1);
            $pending_status = 'Registered For Approval';
            if ('PinPayments' === $is_forapproval['name']) {
                $data['sctxnid'] = 'SP-' . $data['wpm_id'] . '-' . $wpm_newid;
                $pending_status  = 'Pin Payments Confirmation';
            }
            $data['wpm_id']      = $is_forapproval['level'];
            $registered_by_admin = false;
        }

        // Check if the user is valid.
        if (true === wlm_admin_in_admin() || true === $bypass_user_auth) {
            $validuser = username_exists($data['username']);

            if (! $validuser) {
                $validuser        = email_exists($data['email']);
                $user_info        = get_userdata($validuser);
                $data['username'] = $user_info->user_login;
            }

            $data['password'] = __('Already assigned', 'wishlist-member');
        } else {
            $validuser = wp_authenticate($data['username'], $data['password']);
            $validuser = ! is_wp_error($validuser);
        }
        if ($validuser) {
            $user = $this->get_user_data(0, $data['username']);
            // Check for blacklist status.
            $blacklist = $this->check_blacklist($user->user_email);

            // Load user's Membership Levels.
            $levels = $this->get_membership_levels($user->ID);

            // Check if the member is already registered to the level.
            $inlevel = in_array((string)$data['wpm_id'], array_map('strval', $levels), true);

            /*
             * if member is already in level, check if he's expired and if so,
             * check if level is configured to reset registration for expired
             * level re-registration
             */
            if ($inlevel) {
                $expired      = $this->level_expired($data['wpm_id'], $user->ID);
                $resetexpired = 1 === (int) $registration_level->registrationdatereset;
                // If autoreg is enabled OR expired and level allows re-registration then set inlevel to false.
                if ($expired && $resetexpired) {
                    $inlevel = false;
                } else {
                    // If levels has expiration and allow reregistration for active members.
                    $levelexpires       = 1 === (int) $registration_level->expire_option;
                    $levelexpire_length = $registration_level->expire;
                    $levelexpires_cal   = $registration_level->calendar;
                    $resetactive        = 1 === (int) $registration_level->registrationdateresetactive;
                    if (! $expired && $levelexpires && $resetactive) {
                        $inlevel = false;
                        // Get the registration date before it gets updated because we will use it later.
                        $levelexpire_regdate = $this->Get_UserLevelMeta($user->ID, $data['wpm_id'], 'registration_date');
                    }
                }

                $cancelled      = $this->level_cancelled($data['wpm_id'], $user->ID);
                $resetcancelled = 1 === (int) $registration_level->uncancelonregistration;
                // If expired and level allows re-registration then set inlevel to false.
                if ($cancelled && $resetcancelled) {
                    $inlevel = false;
                }

                $repeat_registration = false;
                if (defined('WLM_ALLOW_REPEAT_REGISTRATION')) {
                    $inlevel             = false;
                    $repeat_registration = true;
                }
            }
        }

        // Validate if not blacklisted.
        if ($blacklist) {
            switch ($blacklist) {
                case 1:
                    $wpm_errmsg = $this->get_option('blacklist_email_message');
                    break;
                case 2:
                    $wpm_errmsg = $this->get_option('blacklist_ip_message');
                    break;
                case 3:
                    $wpm_errmsg = $this->get_option('blacklist_email_ip_message');
                    break;
            }
            return false;
        }
        // Validate if a valid user.
        if (! $validuser) {
            $wpm_errmsg = __('Invalid username and/or password.', 'wishlist-member');
            return false;
        }
        // Validate if not in level.
        if ($inlevel) {
            $wpm_errmsg = __('You are already registered to this level.', 'wishlist-member');
            return false;
        }
        // Validate if reCaptcha is OK.
        if (! $this->recaptcha_response()) {
            if (3 === (int) wlm_post_data()['recaptcha-version']) {
                $wpm_errmsg = __('Are you human? Go back and try it again', 'wishlist-member');
            } else {
                $wpm_errmsg = __('The reCAPTCHA wasn\'t entered correctly. Go back and try it again', 'wishlist-member');
            }
            return false;
        }

        /*
         *  we check if there's a "need for admin approval" or "email confirmation"
         *  in the level settings, if yes, then add a flag that will delay member from being added to AR
         *  until all these flags are cleared
         */
        $pendingautoresponder = [];
        if ($registration_level->requireadminapproval && ! $registered_by_admin && ! $is_forapproval) {
            $pendingautoresponder[] = 'autoresponder_add_pending_admin_approval';
        }

        if ($registration_level->requireemailconfirmation && ! $registered_by_admin) {
            $pendingautoresponder[] = 'autoresponder_add_pending_email_confirmation';
        }

        // Set membership levels.
        $levels[] = $data['wpm_id'];
        $this->set_membership_levels(
            $user->ID,
            $levels,
            [
                'pending_autoresponders' => $pendingautoresponder,
                'keep_existing_levels'   => true,
            ]
        );

        // Attach transaction_id to user and delete mergewith temporary user.
        if ($data['mergewith']) {
            $mw = $this->get_user_data($data['mergewith']);
            if ($mw->data->additional_levels) {
                $this->Update_UserMeta($user->ID, 'additional_levels', $mw->data->additional_levels);
            }
            if ($this->is_ppp_level($data['wpm_id'])) {
                $clcntnt = substr($data['wpm_id'], 11);
                $clmeta  = $this->Get_AllContentLevelMeta('U-' . $mw->ID, substr($data['wpm_id'], 11));
                if ($clmeta) {
                    foreach ($clmeta as $k => $v) {
                        if (! $this->Add_ContentLevelMeta('U-' . $user->ID, $content_id, $k, $v)) {
                            $this->Update_ContentLevelMeta('U-' . $user->ID, $content_id, $k, $v);
                        }
                    }
                }
            } else {
                foreach ((array) $this->get_membership_levels_txn_ids($mw->ID) as $key => $val) {
                    $this->set_membership_level_txn_id($user->ID, $key, $val);
                }
                $this->level_cancelled($data['wpm_id'], $user->ID, false);
            }

            // Fix for issue where WLM can't delete temp accounts on WP MU.
            // Should also fix issue where incomplete reg emails still being.
            // Sent even with users already completing it.
            if (is_multisite()) {
                require_once ABSPATH . 'wp-admin/includes/ms.php';
                wpmu_delete_user($data['mergewith']);
            } else {
                wp_delete_user($data['mergewith']);
            }
        } else {
            if ($this->is_ppp_level($data['wpm_id'])) {
                $this->add_user_post_transaction_id($user->ID, substr($data['wpm_id'], 11), $data['sctxnid']);
            } else {
                if (! $repeat_registration) {
                    $this->set_membership_level_txn_id($user->ID, $data['wpm_id'], $data['sctxnid']);
                }
            }
        }

        // If expired and level allows re-registration, then reset timestamp.
        if ($expired && $resetexpired) {
            $this->user_level_timestamp($user->ID, $data['wpm_id'], time());
        } else {
            if (! $expired && $levelexpires && $resetactive) {
                // Make sure its valid.
                $levelexpires_cal = in_array($levelexpires_cal, ['Days', 'Weeks', 'Months', 'Years'], true) ? $levelexpires_cal : false;
                if ($levelexpires_cal && $levelexpire_regdate) {
                    list( $xdate, $xfraction )                                 = explode('#', $levelexpire_regdate);
                    list( $xyear, $xmonth, $xday, $xhour, $xminute, $xsecond ) = preg_split('/[- :]/', $xdate);
                    if ('Days' === $levelexpires_cal) {
                        $xday = $levelexpire_length + $xday;
                    }
                    if ('Weeks' === $levelexpires_cal) {
                        $xday = ( $levelexpire_length * 7 ) + $xday;
                    }
                    if ('Months' === $levelexpires_cal) {
                        $xmonth = $levelexpire_length + $xmonth;
                    }
                    if ('Years' === $levelexpires_cal) {
                        $xyear = $levelexpire_length + $xyear;
                    }
                    $this->user_level_timestamp($user->ID, $data['wpm_id'], mktime($xhour, $xminute, $xsecond, $xmonth, $xday, $xyear));
                }
            }
        }

        // If cancelled and level is set to uncancel on re-registration, then uncancel.
        if ($cancelled && $resetcancelled) {
            $txnid = $this->get_membership_levels_txn_id($user->ID, $data['wpm_id']);
            foreach ((array) $this->get_membership_levels_txn_ids($user->ID, $txnid) as $level => $txnid) {
                $this->level_cancelled($level, $user->ID, false);
            }
            // If the additional level setting Registration Date Reset for Active Level is enabled, reset the level registration date.
            if ($registration_level->registrationdateresetactive) {
                $this->user_level_timestamp($user->ID, $data['wpm_id'], time());
            }
        }

        // Prepare email mergecodes.
        $macros = [
            '[memberlevel]' => wlm_trim($registration_level->name),
            '[password]'    => $data['password'],
            '[confirmurl]'  => get_bloginfo('url') . '/index.php?wlmconfirm=' . $user->ID . '/' . md5($user->user_email . '__' . $user->user_login . '__' . $data['wpm_id'] . '__' . $this->GetAPIKey()),
        ];

        /*
         * doing a manual registration so we also
         * set the level's For Approval status if
         * the level is configured as such
         */

        $level_for_approval = [
            (bool) ( $registration_level->requireadminapproval && ! $registered_by_admin && ! $data['mergewith'] && ! $is_forapproval ),
            (bool) ( $registration_level->requireadminapproval_integrations && $data['mergewith'] ),
        ];

        // Set for approval and send approval email (if so configured).
        if (in_array(true, array_map('boolval', $level_for_approval), true) && ! $registered_by_admin) {
            $this->level_for_approval($data['wpm_id'], $user->ID, $pending_status ? $pending_status : true);

            // Send require admin approval email.
            if (empty($data['sctxnid'])) { // Free registration.
                $this->send_email_template('require_admin_approval', $user->ID, $macros); // Send to user.
                $this->send_email_template('require_admin_approval_admin', $user->ID, $macros, $this->get_option('email_sender_address')); // Send to admin.
            } else { // We assume it's a paid registration if txnid is specified.
                $this->send_email_template('require_admin_approval_paid', $user->ID, $macros); // Send to user.
                $this->send_email_template('require_admin_approval_paid_admin', $user->ID, $macros, $this->get_option('email_sender_address')); // Send to admin.
            }
            $send_welcome_email = false;
        } elseif ($is_forapproval) { // For payment providers that uses for approval status eg. PinPayments, thats the purpose of $is_forapproval.
            $this->level_for_approval($data['wpm_id'], $user->ID, $pending_status ? $pending_status : true);
        }
        if (wlm_getcookie('wishlist_reg_cookie_manual')) {
            // Send confirmation email (if so configured).
            if ($registration_level->requireemailconfirmation) {
                $this->level_unconfirmed($data['wpm_id'], $user->ID, true);

                $this->send_email_template('email_confirmation', $user->ID, $macros);
                $send_welcome_email = false;

                $email_confirmation_reminder = [
                    'count'    => 0,
                    'lastsend' => time(),
                    'wpm_id'   => $data['wpm_id'],
                ];
                add_user_meta($id, 'wlm_email_confirmation_reminder', $email_confirmation_reminder);
            }
        }

        // Add password.
        $macros['[password]'] = $data['password'];

        // If we want to user per level settings.
        if ('sendlevel' === $send_welcome_email) {
            $this->email_template_level = $data['wpm_id'];
        }

        // And send the mail.
        if ($send_welcome_email) {
            if (2 === (int) $registration_level->newuser_notification_user) {
                add_filter('wishlistmember_per_level_template_setting_newuser_notification_user_' . $data['wpm_id'], '__return_false', 11);
            }

            $this->send_email_template('registration', $user->ID, $macros);
        }
        if ($notify_admin) {
            if (2 === (int) $registration_level->newuser_notification_admin) {
                add_filter('wishlistmember_per_level_template_setting_newuser_notification_admin_' . $data['wpm_id'], '__return_false', 11);
            }

                $admin_macros = $macros;
            if ('0' !== $this->get_option('mask_passwords_in_emails')) {
                $admin_macros['[password]'] = '********';
            }
                $this->send_email_template('admin_new_member_notice', $user->ID, $admin_macros, $this->get_option('email_sender_address'));
        }

        // Delete the registration page security cookie.
        $this->registration_cookie('x', $dummy);

        // WP multisite stuff.
        if (function_exists('add_user_to_blog')) {
            if (! is_user_member_of_blog($user->ID)) {
                add_user_to_blog($GLOBALS['blog_id'], $user->ID, $registration_level->role);
            }
        }

        if (false === wlm_admin_in_admin()) {
            $this->wpm_auto_login($user->ID);
        }
        // We're done.
        do_action('wishlistmember_user_registered', $user->ID, $data, $mw);
        return $user->ID;
    }

    /**
     * Sets Registration Security Cookie
     *
     * @param  boolean|string $set   Boolean or "manual".
     * @param  string         $hash  Passed by reference, cookie hash.
     * @param  string         $level Level ID.
     * @return boolean
     */
    public function registration_cookie($set = null, &$hash = null, $level = null)
    {
        if (is_null($set)) {
            $set = true;
        }
        if ('manual' === $set) {
            $set    = true;
            $manual = true;
        } else {
            $manual = false;
        }
        $level = is_null($level) ? '' : ( '_' . $level );

        if (true === $set) {
            $x    = time();
            $x    = serialize([md5(AUTH_SALT . '_' . $x . $level), $x]);
            $hash = $x;
            if (! headers_sent()) {
                wlm_setcookie('wishlist_reg_cookie', $x, 0, '/');

                if ($manual) {
                    wlm_setcookie('wishlist_reg_cookie_manual', 1, 0, '/');
                } else {
                    wlm_setcookie('wishlist_reg_cookie_manual', '', time() - 3600, '/');
                }
            }
            $return = true;
        } elseif (false === $set) {
            $x = wlm_maybe_unserialize(stripslashes(wlm_getcookie('wishlist_reg_cookie')));
            if (empty($x) || ! is_array($x)) {
                return false;
            }
            $timeout = $x[1] + $this->registration_cookie_timeout;
            $return  = ( md5(AUTH_SALT . '_' . $x[1] . $level) === $x[0] && time() < $timeout );
        } else {
            if (! headers_sent()) {
                // Non-boolean parameter deletes the cookie.
                wlm_setcookie('wishlist_reg_cookie', '', time() - 3600, '/');
                wlm_setcookie('wishlist_reg_cookie_manual', '', time() - 3600, '/');
            }
            $return = false;
        }
        return $return;
    }

    /**
     * Redirects to the correct Level Registration URL
     *
     * @return string Payment Provider Reg URL?
     */
    public function registration_url()
    {
        $levels = apply_filters('wishlistmember_registration_levels', $this->get_option('wpm_levels'));
        $reg    = explode('/register/', rawurldecode(wlm_server_data()['REQUEST_URI']));
        $reg    = preg_split('/[\?&\/]/', (string) wlm_arrval($reg, 1));

        $post_id = wlm_arrval($reg, 1);
        $reg     = wlm_arrval($reg, 0);

        $fallback = false;
        if ('fallback' === $reg) {
            $url = $this->get_registration_url(wlm_get_data()['h'] . '/fallback', true);
            header('Location:' . $url);
            exit;
        }

        // > Payment Provider Generic API.
        $continue = false;
        if ('continue' === $reg) {
            $continue = true;
            $reg      = '';
            // Get the secret key.
            $secret = $this->get_option('genericsecret');

            // This is a short url version.
            if (isset(wlm_get_data()['to'])) {
                $longurl = $this->get_continue_registration_url_from_short(wlm_get_data()['to'], false);
                wp_redirect(WLM_REGISTRATION_URL . $longurl);
                die();
            }

            // Generate the hash.
            $h  = urlencode(md5(wlm_get_data()['e'] . '__' . $secret));
            $h2 = urlencode(md5(wlm_get_data()['e'] . '__' . $this->GetAPIKey()));

            if (wlm_get_data()['h'] === $h || wlm_get_data()['h'] === $h2) {
                $counter = 0;
                do {
                    if ($counter > 0) {
                        sleep(2);
                    }
                    $user = $this->get_user_data(0, $e = 'temp_' . md5(wlm_get_data()['e']));
                } while (! $user && $counter++ < 5);

                if ($user->ID) {
                    $level = $this->get_membership_levels($user->ID);
                    if (count($level) > 1) { // If levels is more than one.
                        foreach ($level as $k => $lvl) { // Let's remove child levels.
                            if ($this->level_parent($lvl, $user->ID)) {
                                unset($level[ $k ]);
                            }
                        }
                    }
                    $level     = array_values($level); // Reset level index so its start with 0 again.
                    $userlevel = 'U-' . $user->ID;
                    $level     = array_diff($level, [$userlevel]);
                    $level = apply_filters('wishlistmember_continue_incomplete_registration_levels', $level, $user->ID);
                    if (! count($level)) {
                        // No valid levels found, try pay per posts.
                        $protected_cpts = (array) $this->get_option('protected_custom_post_types');
                        $post_id        = 0;
                        foreach (array_merge(['posts', 'pages'], $protected_cpts) as $ptype) {
                            $post_id = $this->get_membership_content($ptype, $userlevel);
                            if ($post_id) {
                                break;
                            }
                        }
                        if (! empty($post_id)) {
                            list( $post_id ) = $post_id;
                            $reg             = 'payperpost-' . $post_id;
                        }
                    } else {
                        list($level) = $level;
                        $reg         = $levels[ $level ]['url'];
                    }
                    if ($reg && ! headers_sent()) {
                        wlm_setcookie('wpmu', $e, 0, '/');
                    }
                } else {
                    $xuser = get_user_by('email', wlm_get_data()['e']);
                    if ($xuser) {
                        $xlevels = $this->get_membership_levels($xuser->ID, false, true, true, true, false);
                        if ($xlevels) {
                            wp_redirect($this->get_after_reg_redirect(array_pop($xlevels))) && exit;
                        }
                    }
                }
            } else {
                $reg = '';
            }
        }

        // Payment Provider Generic API.
        if (is_array($post_id)) {
            list($post_id) = $post_id;
        }

        $post_id = (int) ( $post_id );
        if ($post_id && get_post($post_id)) {
            if ($continue || $this->free_pay_per_post($post_id)) {
                $this->registration_cookie('manual', $dummy, 'payperpost-' . $post_id);
                $redir = $this->get_registration_url('payperpost-' . $post_id, false, $dummy);
                header('Location:' . $redir);
                exit;
            } else {
                header('Location:' . get_bloginfo('url'));
                exit;
            }
        }

        foreach ((array) $levels as $id => $level) {
            if ($reg === $level['url'] && '' !== $level['url'] && ( $level['allow_free_reg'] || $continue )) {
                $this->registration_cookie('manual', $dummy, $id);
                $redir = $this->get_registration_url($id, false, $dummy);
                header('Location:' . $redir);
                exit;
            }
        }

        // Check for approval registrations.
        $for_approval_registration = $this->get_option('wlm_for_approval_registration');
        if ($for_approval_registration) {
            $for_approval_registration = unserialize($for_approval_registration);

            foreach ((array) $for_approval_registration as $id => $title) {
                if ($id === $reg && '' !== $title) {
                    $this->registration_cookie('manual', $dummy, $id);
                    $redir = $this->get_registration_url($id, false, $dummy);
                    header('Location:' . $redir);
                    exit;
                }
            }
        }

        // Not one of our registration URLs.  Possible payment provider thank you URL.
        return $reg;
    }

    /**
     * Get Registration URL
     *
     * @param  integer $level_id   Level ID.
     * @param  boolean $set_cookie Optional, true to set cookie.
     * @param  string  $hash       Passed by reference, cookie hash.
     * @return string             Registration URL
     */
    public function get_registration_url($level_id, $set_cookie = false, &$hash = null)
    {
        if ($set_cookie) {
            $this->registration_cookie(true, $hash, $level_id);
        }
        $redir  = $this->magic_page();
        $qe     = false === strpos($redir, '?') ? '?' : '&';
        $redir .= $qe . 'reg=' . $level_id;

        if (1 === (int) wlm_get_data()['existing']) {
            $redir .= '&existing=1';
        }
        if (isset(wlm_get_data()['wlm_reg_msg'])) {
            $redir .= '&wlm_reg_msg=' . urlencode(wlm_get_data()['wlm_reg_msg']);
        }
        $getdata = array_diff(wlm_get_data(true), ['']);
        unset($getdata['existing']);
        unset($getdata['wlm_reg_msg']);
        if ($getdata) {
            $getdata = base64_encode(http_build_query($getdata));
            $redir  .= '&wlm_rgd=' . $getdata;
        }
        return $redir;
    }

    /**
     * Registration Form Content
     *
     * @param  integer $level_id    Optional level ID.
     * @param  boolean $return_form Default false. True to return form markup, false to just display it.
     * @return string
     */
    public function reg_content($level_id = null, $return_form = null)
    {
        $reg_id = is_null($level_id) ? wlm_get_data()['reg'] : $level_id;

        if (is_null($return_form)) {
            $return_form = false;
        }

        remove_filter('the_content', 'wptexturize');
        remove_filter('the_content', 'wpautop');

        $wpm_levels   = apply_filters('wishlistmember_registration_levels', $this->get_option('wpm_levels'));
        $wpm_level_id = $reg_id;

        $form_version = $this->get_option('FormVersion');

        if ($this->is_ppp_level($wpm_level_id)) {
            $this->inject_ppp_settings($wpm_levels, $wpm_level_id);
        }

        $is_forapproval = $this->is_for_approval_registration($wpm_level_id);
        if ($is_forapproval) {
            $this->inject_for_approval_settings($wpm_levels, $wpm_level_id);
        }

         /**
         * Filters the registration level data.
         *
         * @param array  $wpm_level Membership level data.
         * @param string $level_id  Level ID.
         */
        $wpm_level   = apply_filters('wishlistmember_registration_level_data', $wpm_levels[ $wpm_level_id ], $wpm_level_id);
        $form_action = str_replace('&existing=1', '', htmlentities($this->get_registration_url($wpm_level_id, true, $hash)));

        $hash         = htmlentities($hash, ENT_QUOTES);
        $captcha_html = $this->generate_recaptcha_html($wpm_level_id);

        $wpm_errmsg = '';
        $mergewith  = '';

        $username  = '';
        $firstname = '';
        $lastname  = '';
        $email     = '';

        // This used to pass this variable in the query string which is why i added this line.
        wlm_get_data()['u'] = wlm_or(wlm_post_data()['mergewith'], 'wlm_getcookie', 'wpmu');
        if (wlm_get_data()['u']) {
            $the_u = $this->get_user_data(wlm_get_data()['u']);
            if ($the_u->ID) {
                $firstname = $the_u->first_name;
                $lastname  = $the_u->last_name;
                $email     = $the_u->wlm_origemail;
                $mergewith = $the_u->ID;
            }
        }

        /*
         * don't process registration if we just
         * want to return the registration form
         *
         * doing so prevents multiple registration attempts
         * as well as sending multiple email notifications
         * when fulfilling an incomplete registration
         */
        $registered = false;
        if (! $return_form) {
            $registration_called = false;
            if ('wpm_register' === wlm_post_data()['action']) {
                $registered          = $this->wpm_register(wlm_post_data(true), $wpm_errmsg);
                $registration_called = true;
            } elseif ('wpm_register_existing' === wlm_post_data()['action']) {
                $registered          = $this->wpm_register_existing(wlm_post_data(true), $wpm_errmsg);
                $registration_called = true;
            }

            if ($registration_called && $registered) {
                do_action('wishlistmember_registration_success', $registered, wlm_post_data(true));
            }

            if ($registration_called && ! $registered) {
                $username  = wlm_post_data()['username'];
                $firstname = wlm_post_data()['firstname'];
                $lastname  = wlm_post_data()['lastname'];
                $email     = wlm_post_data()['email'];
                $reg_page  = trim(wlm_post_data()['reg_page']);
                if ($reg_page) {
                    $data = wlm_post_data(true);
                    $data = array_slice($data, array_search('reg_page', array_keys($data), true) + 1);
                    $data = array_slice($data, 0, array_search('custom_fields', array_keys($data), true) + 1);
                    if ('wpm_register_existing' === wlm_post_data()['action']) {
                        $data['existing'] = 1;
                    }

                    if (! session_id()) {
                        session_start();
                    }

                    $_SESSION['wlm_reg_data'] = $data;
                    $data['wpm_errmsg'] = rawurlencode($wpm_errmsg);
                    unset($data['password'], $data['password1'], $data['password2'], $data['username'], $data['firstname'], $data['lastname'], $data['email'], $data['passwordhint'], $data['custom_fields']);
                    wp_safe_redirect(add_query_arg($data, $reg_page));
                    exit;
                }
            }
        }

        if (empty($wpm_errmsg)) {
            $wpm_errmsg = wlm_trim(wlm_get_data()['wpm_errmsg']);
        }

        $afterreg = $this->get_after_reg_redirect($wpm_level_id, $wpm_levels);

        if ('' === get_option('permalink_structure')) {
            $existinglink = $this->magic_page(true) . '&reg=' . $reg_id . '&existing=1';
            $newlink      = $this->magic_page(true) . '&reg=' . $reg_id;
        } else {
            $existinglink = $this->magic_page(true) . '?reg=' . $reg_id . '&existing=1';
            $newlink      = $this->magic_page(true) . '?reg=' . $reg_id;
        };

        $reglevel = $wpm_level['name'] ?? '';

        $reg_before = wlm_or($this->get_option('regpage_before'), []);
        $reg_after  = wlm_or($this->get_option('regpage_after'), []);

        if ($is_forapproval) {
            $reg_before = $reg_before[ $is_forapproval['level'] ];
            $reg_after  = $reg_after[ $is_forapproval['level'] ];
        } else {
            $reg_before = wlm_arrval($reg_before, $this->is_ppp_level($wpm_level_id) ? 'payperpost' : $wpm_level_id);
            $reg_after  = wlm_arrval($reg_after, $this->is_ppp_level($wpm_level_id) ? 'payperpost' : $wpm_level_id);

            $reg_before = apply_filters('wishlistmember_before_registration_form', $reg_before, $wpm_level_id);
            $reg_after  = apply_filters('wishlistmember_after_registration_form', $reg_after, $wpm_level_id);
        }

        $marketing_consent_checkbox_text = $this->get_option('privacy_enable_consent_to_market') ? $this->get_option('privacy_consent_to_market_text') : '';

        $tos_required_checkbox_text = $this->get_option('privacy_require_tos_on_registration') ? $this->get_option('privacy_require_tos_checkbox_text') : '';

        if (wlm_get_data()['existing']) {
            $registration_instructions = str_replace(['[level]', '[newlink]', '[existinglink]'], [$reglevel, $newlink, $existinglink], $this->get_option('reg_instructions_existing'));
            $registration_header       = __('Existing Member Login', 'wishlist-member');

            $form_body = $this->get_level_existing_registration_form($wpm_level_id, $form_action, $hash, $mergewith, $captcha_html);
        } else {
            $registration_instructions_no_existing  = str_replace(['[level]', '[newlink]', '[existinglink]'], [$reglevel, $newlink, $existinglink], $this->get_option('reg_instructions_new_noexisting'));
            $registration_instructions_has_existing = str_replace(['[level]', '[newlink]', '[existinglink]'], [$reglevel, $newlink, $existinglink], $this->get_option('reg_instructions_new'));
            $registration_header                    = __('New Member Registration', 'wishlist-member');

            if (isset($wpm_level['disableexistinglink']) && $wpm_level['disableexistinglink']) {
                $registration_instructions = $registration_instructions_no_existing;
            } else {
                $registration_instructions = $registration_instructions_has_existing;
            }

            $form_body = $this->get_level_registration_form($wpm_level_id, $form_action, $hash, $username, $firstname, $lastname, $email, $mergewith, $captcha_html, $marketing_consent_checkbox_text, $tos_required_checkbox_text);
        }

        if (wlm_trim($wpm_errmsg)) {
            $form_error = sprintf('<p class="wpm_err">%s</p>', $wpm_errmsg);
        } else {
            $form_error = '';
        }

        if (wlm_trim(wlm_get_data()['wlm_reg_msg'])) {
            $wlm_reg_msg_external = sprintf('<p class="wlm_reg_msg_external">%s</p>', wlm_trim(wlm_get_data()['wlm_reg_msg']));
        } else {
            $wlm_reg_msg_external = '';
        }

        $form_instructions = sprintf('<div id="wlmreginstructions">%1$s</div><h3 style="margin:0">%2$s</h3><br />', $registration_instructions, $registration_header);

        switch ($form_version) {
            case 'improved':
                if (wlm_get_data()['existing']) {
                    $form_body = $this->get_level_registration_form($wpm_level_id, $form_action, $hash, $username, $firstname, $lastname, $email, $mergewith, $captcha_html) . $form_body;
                } else {
                    $form_body .= $this->get_level_existing_registration_form($wpm_level_id, $form_action, $hash, $mergewith, $captcha_html);
                }
                $checked_existing              = wlm_get_data()['existing'] ? ' checked="checked"' : '';
                $checked_new                   = ! wlm_get_data()['existing'] ? ' checked="checked"' : '';
                $existing_account_option_label = __('I have an existing account', 'wishlist-member');
                $new_account_option_label      = __('I am a new user', 'wishlist-member');
                $form_toggle = '';
                if (! $wpm_level['disableexistinglink'] || wlm_get_data()['existing']) {
                    $form_toggle = sprintf(
                        '<form onsubmit="return false" class="wlm_regform_toggle">' .
                        '<label><input type="radio" name="regtype" value="wlm_show_existing_regform"%1$s> %2$s</label>' .
                        '<label><input type="radio" name="regtype" value="wlm_show_new_regform"%3$s> %4$s</label>' .
                        '</form>',
                        $checked_existing,
                        $existing_account_option_label,
                        $checked_new,
                        $new_account_option_label
                    );
                }

                $regform_show_class = ( wlm_get_data()['existing'] || 2 === (int) $wpm_level['disableexistinglink'] ) ? 'wlm_show_existing_regform' : 'wlm_show_new_regform';
                $form_body          = sprintf('<div class="wlm_regform_improved %s">%s%s%s%s</div>', $regform_show_class, $form_error, $wlm_reg_msg_external, $form_toggle, $form_body);

                $form_before = $reg_before;
                $form_after  = $reg_after;
                break;
            case 'themestyled':
                if (wlm_get_data()['existing']) {
                    $form_body = $this->get_level_registration_form($wpm_level_id, $form_action, $hash, $username, $firstname, $lastname, $email, $mergewith, $captcha_html) . $form_body;
                } else {
                    $form_body .= $this->get_level_existing_registration_form($wpm_level_id, $form_action, $hash, $mergewith, $captcha_html);
                }

                if (wlm_get_data()['existing'] || 2 === (int) wlm_arrval($wpm_level, 'disableexistinglink')) {
                    $regform_show_class  = 'wlm3-show-existing-regform';
                    $regform_error_class = 'wlm3-existing-account-error';
                } else {
                    $regform_show_class  = 'wlm3-show-new-regform';
                    $regform_error_class = 'wlm3-new-account-error';
                }

                if (! isset($wpm_level['disableexistinglink']) || ! $wpm_level['disableexistinglink']) {
                    $form_toggle = sprintf('<ul class="wlm3-theme-styled-reg-form-toggle"><li class="wlm3-new-account"><a href="#" onclick="this.parentElement.parentElement.parentElement.className=\'wlm3-theme-styled-reg-form wlm3-show-new-regform\'; return false">%s</a></li><li class="wlm3-existing-account"><a href="#" onclick="this.parentElement.parentElement.parentElement.className=\'wlm3-theme-styled-reg-form wlm3-show-existing-regform\'; return false">%s</a></li></ul>', __('Create Account', 'wishlist-member'), __('Existing Account', 'wishlist-member'));
                } else {
                    $form_toggle = '';
                }

                $form_body = sprintf('<div class="wlm3-theme-styled-reg-form %s">%s<div class="%s">%s</div>%s</div>', $regform_show_class, $form_toggle, $regform_error_class, $form_error, $form_body);

                $form_before = $reg_before;
                $form_after  = $reg_after;
                break;
            default:
                $form_before = $form_error . $form_instructions . $wlm_reg_msg_external;
                if (! isset(wlm_get_data()['existing'])) {
                    $form_before = $reg_before . $form_before;
                    $form_after  = $form_after . $reg_after;
                }
        }

        $form_body = str_replace(["\r", "\n", "\t"], '', $form_body);

        $redirectcount            = 3;
        $registration_please_wait = __('Please wait while we process your submission and kindly do not click your browser\'s back or refresh button.', 'wishlist-member');
        // Translators: 1: link to after registration page, 2: number of seconds before redirect.
        $click_to_redirect = sprintf(__('<a href="%1$s">Click here</a> if you are not redirected in %2$d seconds.', 'wishlist-member'), $afterreg, $redirectcount);

        if ($registered || wlm_get_data()['registered'] || wlm_post_data()['WLMRegHookIDs']) {
            $welcome = sprintf(
                '<meta http-equiv="refresh" content="{$redirectcount};url=%1$s">' .
                '<script type="text/javascript">' .
                '	function wlmredirect(){' .
                '		document.location="%1$s";' .
                '	}' .
                '	window.setTimeout(wlmredirect,%2$d000)' .
                '</script>' .
                '<div class="wlm3-please-wait-msg"><p>%3$s</p>' .
                '<p>%4$s</p></div>',
                $afterreg,
                $redirectcount,
                $registration_please_wait,
                $click_to_redirect
            );

            $text = apply_filters('wishlistmember_after_registration_page', $welcome, $this); // We no longer pass $this by reference. might break PHP4 setups.
            do_action('wishlistmember_after_registration', $this);

            if (! session_id()) {
                session_start();
            }

            unset($_SESSION['wlm_reg_data']);

            if ($text !== $welcome) {
                // Our text was filtered so we set the registration cookie again.
                $this->registration_cookie(true, $hash, $wpm_level_id);
            } else {
                // No more hooks playing around so we delete our cookie.
                $this->registration_cookie('DELETE', $dummy);
            }
        } else {
            $text = $form_before . $form_body . $form_after;
        }

        if ($return_form) {
            return $form_body;
        } else {
            $text = apply_filters('wishlistmember_registration_page', $text, $this);
            return $text;
        }
    }

    /**
     * Return the appropriate existing members registration form
     *
     * @param  string         $level_id     Level ID.
     * @param  string         $form_action  Form action.
     * @param  string         $hash         Cookie Hash.
     * @param  string|integer $mergewith    Username or ID to merge the account with.
     * @param  string         $captcha_code Captcha markup code.
     * @return string
     */
    public function get_level_existing_registration_form($level_id, $form_action, $hash, $mergewith = '', $captcha_code = '')
    {
        switch ($this->get_option('FormVersion')) {
            case 'improved':
                $form_body = $this->get_improved_existing_registration_form($captcha_code);
                break;
            case 'themestyled':
                $form_body = $this->get_themestyled_existing_registration_form($captcha_code);
                break;
            default:
                $form_body = $this->get_legacy_existing_registration_form($captcha_code);
        }

        $form_action    = str_replace(['&existing=1', '&amp;existing=1'], '', $form_action);
        $mergewithinput = empty($mergewith) ? '' : "<input type='hidden' name='mergewith' value='{$mergewith}' />";

        $additional_levels_form = '';
        if (! empty($mergewith)) {
            $mw = $this->get_user_data($mergewith);
            if ($mw->data->additional_levels) {
                $additional_levels_form = $this->reg_form_additional_levels_list($mw->data->additional_levels);
            }
        }

        $form = sprintf(
            '%1$s' .
            '<form method="post" action="%2$s&existing=1">' .
            '	<input type="hidden" name="action" value="wpm_register_existing" />' .
            '	<input type="hidden" name="cookiehash" value="%3$s" />' .
            '	<input type="hidden" name="wpm_id" value="%4$s" />' .
            '	<input type="hidden" name="reg_page" value="%7$s" />' .
            '	%5$s' .
            '	<div class="wlm_regform_container wlm_regform_existing_user">' .
            '		%6$s' .
            '	</div>' .
            '</form>',
            $additional_levels_form,
            $form_action,
            $hash,
            $level_id,
            $mergewithinput,
            $form_body,
            get_permalink()
        );

        return str_replace(["\n", "\r", "\t"], '', $form);
    }

    /**
     * Get markup for "theme styled" existing members registration form
     *
     * @param  string $captcha_code Captcha markup.
     * @return string
     */
    public function get_themestyled_existing_registration_form($captcha_code = '')
    {
        $markup  = '';
        $markup .= wlm_form_field(
            [
                'name'  => 'username',
                'label' => __(
                    'Username',
                    'wishlist-member'
                ),
            ]
        );
        $markup .= wlm_form_field(
            [
                'name'  => 'password',
                'label' => __('Password', 'wishlist-member'),
                'type'  => 'password',
            ]
        );

        $captcha_code = wlm_trim($captcha_code);
        if ($captcha_code) {
            $$markup .= wlm_form_field(
                [
                    'type'  => 'rawhtml',
                    'value' => $captcha_code,
                ]
            );
        }

        $markup .= wlm_form_field(
            [
                'type'  => 'rawhtml',
                'value' => sprintf(
                    '<a href="%s" style="float: right">%s</a>',
                    esc_url(wp_lostpassword_url()),
                    __('Forgot Password?', 'wishlist-member')
                ),
            ]
        );
        $markup .= wlm_form_field(
            [
                'type'  => 'submit',
                'value' => __(
                    'Login',
                    'wishlist-member'
                ),
            ]
        );

        return sprintf('<div class="wlm3-form wlm3-existing-account-form">%s</div>', $markup);
    }

    /**
     * Get markup for "improved" existing members registration form
     *
     * @param  string $captcha_code Captcha markup.
     * @return string
     */
    public function get_improved_existing_registration_form($captcha_code = '')
    {
        $txt_username        = __('Username', 'wishlist-member');
        $txt_password        = __('Password', 'wishlist-member');
        $txt_login           = __('Login', 'wishlist-member');
        $url_forgot_password = esc_url(wp_lostpassword_url());
        $txt_forgot_password = __('Forgot Password?', 'wishlist-member');

        $captcha_code = wlm_trim($captcha_code);
        if ($captcha_code) {
            $captcha_code = '<div class="wlm_form_group captcha_html">' . $captcha_code . '</div>';
        }

        $form_body = sprintf(
            '<div class="wlm_regform_div wlm_registration wlm_regform_2col wlm_regform_improved">' .
            '	<div class="wlm_form_group">' .
            '		<label for="wlm_exist_username_field" class="wlm_form_label wlm_required_field" id="wlm_username_label">' .
            '			<span class="wlm_label_text" id="wlm_exist_username_text">%1$s:</span>' .
            '		</label>' .
            '		<input class="fld wlm_input_text" id="wlm_exist_username_field" name="username" type="text">' .
            '		<p class="wlm_field_description"></p>' .
            '	</div>' .
            '	<div class="wlm_form_group">' .
            '		<label for="wlm_exist_password_field" class="wlm_form_label wlm_required_field" id="wlm_password_label">' .
            '			<span class="wlm_label_text" id="wlm_exist_password_text">%2$s:</span>' .
            '		</label>' .
            '		<input class="fld wlm_input_text" id="wlm_exist_password_field" name="password" type="password">' .
            '	</div>' .
            '	%3$s' .
            '	<p class="forgotpassword">' .
            '		<a href="%4$s" target="_blank">%5$s</a>' .
            '	</p>' .
            '	<p class="submit">' .
            '		<input class="submit" id="wlm_exist_submit_button" type="submit" value="%6$s" />' .
            '	</p>' .
            '</div>',
            $txt_username,
            $txt_password,
            $captcha_code,
            $url_forgot_password,
            $txt_forgot_password,
            $txt_login
        );
        return $form_body;
    }

    /**
     * Get markup for "legacy" existing members registration form
     *
     * @param  string $captcha_code Captcha markup.
     * @return string
     */
    public function get_legacy_existing_registration_form($captcha_code = '')
    {

        $txt_username        = __('Username', 'wishlist-member');
        $txt_password        = __('Password', 'wishlist-member');
        $txt_login           = __('Login', 'wishlist-member');
        $url_forgot_password = esc_url(wp_lostpassword_url());
        $txt_forgot_password = __('Forgot Password?', 'wishlist-member');

        $captcha_code = wlm_trim($captcha_code);
        if ($captcha_code) {
            $captcha_code = '<tr class="li_fld captcha_html"><td class="label">&nbsp;</td><td class="fld_div">' . $captcha_code . '</td></tr>';
        }

        $form_body = sprintf(
            '<table class="wpm_existing wpm_regform_table">' .
            '	<tr valign="top" class="li_fld">' .
            '		<td class="label"><b>%1$s:</b>&nbsp;</td>' .
            '		<td class="fld_div"><input type="text" name="username" class="fld" value="%2$s" size="25" /></td>' .
            '	</tr>' .
            '	<tr valign="top" class="li_fld">' .
            '		<td class="label"><b>%3$s:</b>&nbsp;</td>' .
            '		<td class="fld_div"><input type="password" name="password" class="fld" size="25" /></td>' .
            '	</tr>' .
            '	%4$s' .
            '	<tr valign="top" class="li_submit">' .
            '		<td></td>' .
            '		<td class="fld_div"><input type="submit" class="button" value="%5$s" /></td>' .
            '	</tr>' .
            '	<tr>' .
            '		<td></td>' .
            '		<td class="forgotpassword">' .
            '			<a href="%6$s" target="_blank">%7$s</a>' .
            '		</td>' .
            '	</tr>' .
            '</table>',
            $txt_username,
            $username,
            $txt_password,
            $captcha_code,
            $txt_login,
            $url_forgot_password,
            $txt_forgot_password
        );
        return $form_body;
    }

    /**
     * Get the registration form for the membership level
     *
     * @param  string         $level_id                        Level ID.
     * @param  string         $form_action                     Value to put in the form's "action" attribute.
     * @param  string         $hash                            Security hash.
     * @param  string         $username                        Username to pre-fill.
     * @param  string         $firstname                       First name to pre-fill.
     * @param  string         $lastname                        Last name to pre-fill.
     * @param  string         $email                           Email to pre-fill.
     * @param  string|integer $mergewith                       User to ID to mergewith.
     * @param  string         $captcha_code                    Captcha HTML code.
     * @param  string         $marketing_consent_checkbox_text Text for marketing consent checkbox.
     * @param  string         $tos_required_checkbox_text      Text for TOS checkbox.
     * @return string HTML code for the registration form
     */
    public function get_level_registration_form($level_id, $form_action, $hash, $username = '', $firstname = '', $lastname = '', $email = '', $mergewith = '', $captcha_code = '', $marketing_consent_checkbox_text = '', $tos_required_checkbox_text = '')
    {

        wlm_xss_sanitize($username);
        wlm_xss_sanitize($firstname);
        wlm_xss_sanitize($lastname);
        wlm_xss_sanitize($email);

        $form_action = str_replace(['&existing=1', '&amp;existing=1'], '', $form_action);

        $mergewithinput = empty($mergewith) ? '' : "<input type='hidden' name='mergewith' value='{$mergewith}' />";

        $wpm_levels      = $this->get_option('wpm_levels');
        $regpage_form_id = '';
        $is_forapproval  = $this->is_for_approval_registration($level_id);

        if (! empty($level_id) && ( isset($wpm_levels[ $level_id ]) || $this->is_ppp_level($level_id) || $is_forapproval )) {
            $regpage_form_id = $this->get_option('regpage_form');
            $regpage_form_id = is_array($regpage_form_id) ? $regpage_form_id : [];
            if ($this->is_ppp_level($level_id)) {
                $regpage_form_id = wlm_trim($regpage_form_id['payperpost'] ?? '');
            } elseif ($is_forapproval) {
                $regpage_form_id = wlm_trim($regpage_form_id[ $is_forapproval['level'] ] ?? '');
            } elseif (isset($wpm_levels[ $level_id ]['custom_reg_form']) && wlm_arrval($wpm_levels[ $level_id ], 'enable_custom_reg_form')) {
                $regpage_form_id = $wpm_levels[ $level_id ]['custom_reg_form'];
            } else {
                $regpage_form_id = '';
            }
        }

        $reg_post        = [];
        $wpm_useraddress = [];
        if ($mergewith) {
            $reg_post        = $this->WLMDecrypt($this->Get_UserMeta($mergewith + 0, 'wlm_reg_post'));
            $wpm_useraddress = $this->Get_UserMeta($mergewith + 0, 'wpm_useraddress');
        }
        $function_passed_data = [
            'username'  => $username,
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
        ];

        $getdata = [];
        if (wlm_get_data()['wlm_rgd']) {
            parse_str(base64_decode(wlm_get_data()['wlm_rgd']), $getdata);
        }
        if (! session_id()) {
            session_start();
        }

        $reg_data = wlm_get_data(true);
        if (isset($_SESSION['wlm_reg_data'])) {
            $reg_data = $_SESSION['wlm_reg_data'];
        }

        $this->reg_page_form_data = array_merge($function_passed_data, (array) $reg_post, (array) $wpm_useraddress, (array) $getdata, $reg_data, wlm_post_data(true));

        switch ($this->get_option('FormVersion')) {
            case 'improved':
                $regpage_form = $this->get_improved_registration_form(
                    $regpage_form_id,
                    $captcha_code,
                    '',
                    wlm_arrval($wpm_levels[ $level_id ] ?? [], 'disableprefilledinfo'),
                    $wpm_levels[ $level_id ] ?? [],
                    $marketing_consent_checkbox_text,
                    $tos_required_checkbox_text
                );
                break;
            case 'themestyled':
                $regpage_form = $this->get_themestyled_registration_form(
                    $regpage_form_id,
                    $captcha_code,
                    '',
                    wlm_arrval($wpm_levels[ $level_id ] ?? [], 'disableprefilledinfo'),
                    $wpm_levels[ $level_id ] ?? [],
                    $marketing_consent_checkbox_text,
                    $tos_required_checkbox_text
                );
                break;
            default:
                $regpage_form = $this->get_legacy_registration_form($regpage_form_id, $captcha_code, '', $wpm_levels[ $level_id ]['disableprefilledinfo'], $marketing_consent_checkbox_text, $tos_required_checkbox_text);
        }

        $additional_levels_form = '';
        if (! empty($mergewith)) {
            $mw = $this->get_user_data($mergewith);
            if (wlm_arrval($mw->data, 'additional_levels')) {
                $additional_levels_form = $this->reg_form_additional_levels_list($mw->data->additional_levels);
            }
        }

        if (empty($regpage_form_id)) {
            $regpage_form_id = 'DEFAULT-' . $level_id;
        }
        $form_body = sprintf(
            '%1$s' .
            '<form method="post" id="frm_new_user_reg" action="%2$s">' .
            '	<input type="hidden" name="wlm_form_id" value="%3$s" />' .
            '	<input type="hidden" name="action" value="wpm_register" />' .
            '	<input type="hidden" name="wpm_id" value="%4$s" />%5$s' .
            '	<input type="hidden" name="cookiehash" value="%6$s" />' .
            '	<input type="hidden" name="orig_firstname" value="%7$s" />' .
            '	<input type="hidden" name="orig_lastname" value="%8$s" />' .
            '	<input type="hidden" name="orig_email" value="%9$s" />' .
            '	<input type="hidden" name="reg_page" value="%11$s" />' .
            '		%10$s' .
            '</form>',
            $additional_levels_form,
            $form_action,
            $regpage_form_id,
            $level_id,
            $mergewithinput,
            $hash,
            $firstname,
            $lastname,
            $email,
            $regpage_form,
            get_permalink()
        );
        return '<div class="wlm_regform_container wlm_regform_new_user">' . $form_body . '</div>';
    }

    /**
     * Get theme-styled registration form
     *
     * @param  string  $form_id                         Form ID.
     * @param  string  $captcha_code                    Captcha markup.
     * @param  boolean $foredit                         True if editing the form. Default false.
     * @param  boolean $disable_prefilled               True to disable prefilling of fields fields. Default false.
     * @param  string  $level_info                      Level info.
     * @param  string  $marketing_consent_checkbox_text Marketin consent checkbox text.
     * @param  string  $tos_required_checkbox_text      TOS required checkbox text.
     * @return string
     */
    public function get_themestyled_registration_form($form_id, $captcha_code = '', $foredit = false, $disable_prefilled = false, $level_info = null, $marketing_consent_checkbox_text = '', $tos_required_checkbox_text = '')
    {

        $form_id   = 'CUSTOMREGFORM-' . substr($form_id, 14);
        $form_data = $this->get_option($form_id);

        $tos_required_checkbox_text = wlm_trim($tos_required_checkbox_text);
        $tos_required_checkbox_html = '';
        if ($tos_required_checkbox_text) {
            $tos_required_checkbox_html = wlm_form_field(
                [
                    'type'    => 'checkbox',
                    'options' => ['1' => $tos_required_checkbox_text],
                    'name'    => 'tos_required',
                ]
            );
        }

        $marketing_consent_checkbox_text = wlm_trim($marketing_consent_checkbox_text);
        $marketing_consent_checkbox_html = '';
        if ($marketing_consent_checkbox_text) {
            $marketing_consent_checkbox_html = wlm_form_field(
                [
                    'type'    => 'checkbox',
                    'options' => ['1' => $marketing_consent_checkbox_text],
                    'name'    => 'consent_to_market',
                ]
            );
        }

        $captcha_code = wlm_trim($captcha_code);
        $captcha_html = '';
        if ($captcha_code) {
            $captcha_html = wlm_form_field(
                [
                    'type'  => 'rawhtml',
                    'value' => $captcha_code,
                ]
            );
        }

        if (! wlm_arrval($form_data, 'form')) {
            $custom_fields   = [];
            $required_fields = [];

            $markup  = wlm_post_data()['wpm_errmsg'];
            $markup .= wlm_form_field(
                [
                    'label' => __('Username', 'wishlist-member'),
                    'name'  => 'username',
                    'value' => '',
                ]
            );
            $markup .= wlm_form_field(
                [
                    'label' => __('First Name', 'wishlist-member'),
                    'name'  => 'firstname',
                    'value' => '',
                ]
            );
            $markup .= wlm_form_field(
                [
                    'label' => __('Last Name', 'wishlist-member'),
                    'name'  => 'lastname',
                    'value' => '',
                ]
            );
            $markup .= wlm_form_field(
                [
                    'label' => __('Email', 'wishlist-member'),
                    'name'  => 'email',
                    'value' => '',
                ]
            );
            $markup .= wlm_form_field(
                [
                    'label' => __('Password', 'wishlist-member'),
                    'type'  => 'password_metered',
                    'name'  => 'password',
                    'value' => '',
                ]
            );

            if ($this->get_option('password_hinting')) {
                $markup           .= wlm_form_field(
                    [
                        'label' => __('Password Hint', 'wishlist-member'),
                        'name'  => 'passwordhint',
                        'value' => '',
                    ]
                );
                $required_fields[] = 'passwordhint';
            }

            if (is_array($level_info) && wlm_arrval($level_info, 'enable_tos')) {
                $markup           .= wlm_form_field(
                    [
                        'type'        => 'tos',
                        'value'       => 1,
                        'text'        => __('I agree to the Terms and Conditions', 'wishlist-member'),
                        'name'        => 'terms_of_service',
                        'description' => wlm_arrval(
                            $level_info,
                            'tos'
                        ),
                    ]
                );
                $required_fields[] = 'terms_of_service';
                $custom_fields[]   = 'terms_of_service';
            }

            $markup .= $tos_required_checkbox_html . $marketing_consent_checkbox_html . $captcha_html;

            $markup .= sprintf('<input type="hidden" name="custom_fields" value="%s">', htmlentities(implode(',', $custom_fields)));
            $markup .= sprintf('<input type="hidden" name="required_fields" value="%s">', htmlentities(implode(',', $required_fields)));

            $markup .= wlm_form_field(
                [
                    'type'  => 'submit',
                    'value' => __(
                        'Submit Registration',
                        'wishlist-member'
                    ),
                    'id'    => 'wlm_new_user_submit_button',
                ]
            );
        } else {
            $form_ = $form_data['form'];
            if (! $foredit) {
                if (! $form_data['form_dissected']) {
                    $form_data['form_dissected'] = wlm_dissect_custom_registration_form($form_data);
                    $this->save_option($form_id, $form_data);
                }
            }
            $dissected = $form_data['form_dissected'];

            $markup = '';

            foreach ($dissected['fields'] as $field) {
                $attributes         = isset($field['attributes']) ? $field['attributes'] : [];
                $attributes['type'] = $field['type'];

                if (! empty($field['label'])) {
                    $attributes['label'] = $field['label'];
                }
                if (! empty($field['description'])) {
                    $attributes['description'] = $field['description'];
                }
                if (! empty($field['text'])) {
                    $attributes['text'] = $field['text'];
                }

                if ('tos' === $field['type']) {
                    if (! empty($field['lightbox'])) {
                        $attributes['lightbox'] = $field['lightbox'];
                    }
                    if (false === strpos($attributes['name'], $form_data['required'])) {
                        $form_data['required'] = implode(',', array_merge([$attributes['name']], explode(',', $form_data['required'])));
                    }
                }

                if (is_array(wlm_arrval($field, 'options'))) {
                    $options = [];
                    foreach ($field['options'] as $o) {
                        $options[ $o['value'] ] = $o['text'];
                        if (wlm_arrval($o, 'selected')) {
                            $attributes['value'] = $o['value'];
                        }
                    }
                    $attributes['options'] = $options;
                }

                if (isset($attributes['name']) && 'password1' === $attributes['name']) {
                    $attributes['type']        = 'password_metered';
                    $attributes['name']        = 'password';
                    $attributes['label']       = str_replace(' (twice)', '', $attributes['label']);
                    $attributes['description'] = str_replace('Enter your desired password twice. Must be at least [wlm_min_passlength] characters long.', '', $attributes['description']);
                }

                if ('input' === $attributes['type']) {
                    $attributes['type'] = 'text';
                }
                $markup .= wlm_form_field($attributes);
            }

            $markup .= $tos_required_checkbox_html . $marketing_consent_checkbox_html . $captcha_html;

            $markup .= wlm_form_field(
                [
                    'type'  => 'submit',
                    'value' => $dissected['submit'] ? $dissected['submit'] : __(
                        'Submit Registration',
                        'wishlist-member'
                    ),
                    'id'    => 'wlm_new_user_submit_button',
                ]
            );

            $markup .= sprintf('<input type="hidden" name="custom_fields" value="%s">', htmlentities($form_data['fields']));
            $markup .= sprintf('<input type="hidden" name="required_fields" value="%s">', htmlentities($form_data['required']));
        }

        // Disable Submit button after clicking once.
        $markup .= '<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#wlm_new_user_submit_button").on("click",function(e) {
					jQuery("#wlm_new_user_submit_button").attr("disabled", "disabled");
					jQuery("#frm_new_user_reg").submit();

				});
			 });
		</script>';


        $disable_prefilled_js = '<script type="text/javascript">jQuery(function() {wlm3_register_disable_prefill();});</script>';
        $disable_prefill_info = ( $disable_prefilled ) ? $disable_prefilled_js : '';

        return sprintf('<div class="wlm3-form wlm3-new-account-form">%s%s</div>', $markup, $disable_prefill_info);
    }

    /**
     * Get improved registration form
     *
     * @param  string  $form_id                         Form ID.
     * @param  string  $captcha_code                    Captcha markup.
     * @param  boolean $foredit                         True if editing the form. Default false.
     * @param  boolean $disable_prefilled               True to disable prefilling of fields fields. Default false.
     * @param  string  $level_info                      Level info.
     * @param  string  $marketing_consent_checkbox_text Marketin consent checkbox text.
     * @param  string  $tos_required_checkbox_text      TOS required checkbox text.
     * @return string
     */
    public function get_improved_registration_form($form_id, $captcha_code = '', $foredit = false, $disable_prefilled = false, $level_info = null, $marketing_consent_checkbox_text = '', $tos_required_checkbox_text = '')
    {

        $form_id      = 'CUSTOMREGFORM-' . substr($form_id, 14);
        $form_data    = $this->get_option($form_id);
        $captcha_code = wlm_trim($captcha_code);

        $marketing_consent_checkbox_text = wlm_trim($marketing_consent_checkbox_text);
        $tos_required_checkbox_text      = wlm_trim($tos_required_checkbox_text);

        $disable_prefilled_js = '
			<script type="text/javascript">
				jQuery(document).ready(function() {

					if(jQuery(\'input[name="mergewith"]\').val() == null) {
						return;
					}

					if (jQuery(\'input[name="orig_firstname"]\').val() != "") {
						jQuery(\'input[name="firstname"]\').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery(\'input[name="orig_lastname"]\').val() != "") {
					 jQuery(\'input[name="lastname"]\').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery(\'input[name="orig_email"]\').val() != "") {
					 jQuery(\'input[name="email"]\').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}
				 });
			</script>
		';
        $disable_prefill_info = ( $disable_prefilled ) ? $disable_prefilled_js : '';

        if ($captcha_code) {
            $captcha_code = '<div class="wlm_form_group captcha_html">' . $captcha_code . '</div>';
        }

        $marketing_consent_html = '';
        if ($marketing_consent_checkbox_text) {
            $marketing_consent_html = sprintf(
                '<div class="wlm_form_group wlm_required_field marketing_consent">' .
                '<label><input type="checkbox" name="consent_to_market" value="1">%1$s</label>' .
                '</div>',
                $marketing_consent_checkbox_text
            );
        }

        $tos_required_html = '';
        if ($tos_required_checkbox_text) {
            $tos_required_html = sprintf(
                '<div class="wlm_form_group wlm_required_field tos_required">' .
                '<label><input type="checkbox" name="tos_required" value="1">%1$s</label>' .
                '</div>',
                $tos_required_checkbox_text
            );
        }

        if (! $form_data['form']) {
            $txt_username            = __('Username', 'wishlist-member');
            $txt_firstname           = __('First Name', 'wishlist-member');
            $txt_lastname            = __('Last Name', 'wishlist-member');
            $txt_email               = __('Email', 'wishlist-member');
            $txt_password            = __('Password (twice)', 'wishlist-member');
            $txt_password_desc       = __('Enter your desired password twice. Must be at least [wlm_min_passlength] characters long.', 'wishlist-member');
            $txt_password_hint_label = __('Password Hint', 'wishlist-member');
            $txt_password_hint_desc  = __('Enter a password hint that will remind you of your password in case you forget it.', 'wishlist-member');
            $txt_submit              = __('Submit Registration', 'wishlist-member');

            $custom_fields   = [];
            $required_fields = [];

            $password_hint = '';
            if ($this->get_option('password_hinting')) {
                $password_hint     = sprintf(
                    '<div class="wlm_form_group wlm_required_field">' .
                    '<label for="wlm_password_field1" class="wlm_form_label" id="wlm_password_label">' .
                    '<span class="wlm_label_text" id="wlm_password_text">%1$s:</span>' .
                    '</label>' .
                    '<input type="text" class="fld wlm_input_text" id="wlm_passwordhint" name="passwordhint">' .
                    '<p class="wlm_field_description">%2$s</p>' .
                    '</div>',
                    $txt_password_hint_label,
                    $txt_password_hint_desc
                );
                $required_fields[] = 'wlm_passwordhint';
            }

            $tos_code = '';
            if (is_array($level_info) && wlm_arrval($level_info, 'enable_tos')) {
                $tos_label         = __('I agree to the Terms and Conditions', 'wishlist-member');
                $tos               = wlm_arrval($level_info, 'tos');
                $tos_code          = sprintf(
                    '<div class="wlm_form_group wlm_required_field wlm_form_tos">' .
                    '<label class="wlm_form_label"></label>' .
                    '<div class="wlm_option_group">' .
                    '<label><input name="terms_of_service" type="checkbox" value="1"> %1$s</label>' .
                    '</div>' .
                    '<div class="wlm_field_tos_content" id="tos_data_terms_of_service">%2$s</div>' .
                    '</div>',
                    $tos_label,
                    $tos
                );
                $required_fields[] = 'terms_of_service';
                $custom_fields[]   = 'terms_of_service';
            }

            $custom_fields   = '<input type="hidden" name="custom_fields" value="' . implode(', ', $custom_fields) . '" />';
            $required_fields = '<input type="hidden" name="required_fields" value="' . implode(', ', $required_fields) . '" />';

            // Add auto_complete_delay_ajax on the form script if autocreate_account_enable_delay is enabled for that level.
            $auto_complete_delay_ajax = '';
            if (is_array($level_info) && wlm_arrval($level_info, 'autocreate_account_enable') && wlm_arrval($level_info, 'autocreate_account_enable_delay')) {
                // This will send an ajax call every 30 seconds as long as the registration form is open.
                $auto_complete_delay_ajax = sprintf(
                    'var ajaxurl = "' . admin_url('admin-ajax.php') . '";' .
                    'var reg_user_id = jQuery(\'input[name="mergewith"]\').val();' .
                    'setInterval(function(){' .
                    'jQuery.post(
					    ajaxurl,
					    {
					        \'action\':\'wishlistmember_delay_autocomplete_registration\',
					        \'' . get_wlm_nonce_field_name() . '\':\'' . get_wlm_nonce() . '\',
					        \'reg_user_id\':reg_user_id,
					        \'reg_level_id\':' . $level_info[ 'id' ] . '
					    }
					);
					}, 30000);'
                );
            }

            $form = sprintf(
                '<script type="text/javascript">' .
                'jQuery(document).ready(function() {' .
                'jQuery("#wlm_new_user_submit_button").on("click",function(e) {' .
                'jQuery("#wlm_new_user_submit_button").attr("disabled", "disabled");' .
                'jQuery("#frm_new_user_reg").submit();' .
                '});' .
                '});' .
                '%16$s' .
                '</script>' .
                '%1$s' .
                '<div class="wlm_regform_div wlm_registration wlm_regform_2col">' .
                '<div class="wlm_form_group wlm_required_field">' .
                '<label for="wlm_firstname_field" class="wlm_form_label" id="wlm_firstname_label">' .
                '<span class="wlm_label_text" id="wlm_firstname_text">%2$s:</span>' .
                '</label>' .
                '<input class="fld wlm_input_text" id="wlm_firstname_field" name="firstname" type="text">' .
                '<p class="wlm_field_description"></p>' .
                '</div>' .
                '<div class="wlm_form_group wlm_required_field">' .
                '<label for="wlm_lastname_field" class="wlm_form_label" id="wlm_lastname_label">' .
                '<span class="wlm_label_text" id="wlm_lastname_text">%3$s:</span>' .
                '</label>' .
                '<input class="fld wlm_input_text" id="wlm_lastname_field" name="lastname" type="text">' .
                '<p class="wlm_field_description"></p>' .
                '</div>' .
                '<div class="wlm_form_group wlm_required_field">' .
                '<label for="wlm_email_field" class="wlm_form_label" id="wlm_email_label">' .
                '<span class="wlm_label_text" id="wlm_email_text">%4$s:</span>' .
                '</label>' .
                '<input class="fld wlm_input_text" id="wlm_email_field" name="email" type="email">' .
                '<p class="wlm_field_description"></p>' .
                '</div>' .
                '<div class="wlm_form_group wlm_required_field">' .
                '<label for="wlm_username_field" class="wlm_form_label" id="wlm_username_label">' .
                '<span class="wlm_label_text" id="wlm_username_text">%5$s:</span>' .
                '</label>' .
                '<input class="fld wlm_input_text" id="wlm_username_field" name="username" type="text">' .
                '<p class="wlm_field_description"></p>' .
                '</div>' .
                '<div class="wlm_form_group wlm_required_field">' .
                '<label for="wlm_password_field1" class="wlm_form_label" id="wlm_password_label">' .
                '<span class="wlm_label_text" id="wlm_password_text">%6$s:</span>' .
                '</label>' .
                '<input class="fld wlm_input_text" id="wlm_password_field1" name="password1" type="password">' .
                '<input class="fld wlm_input_text wlm_password_field2" id="wlm_password_field2" name="password2" type="password">' .
                '<p class="wlm_field_description">%7$s</p>' .
                '</div>' .
                '%8$s' .
                '%9$s' .
                '%10$s' .
                '%11$s' .
                '%12$s' .
                '%13$s' .
                '%14$s' .
                '<p class="submit">' .
                '<input class="submit" id="wlm_new_user_submit_button" type="submit" value="%15$s" />' .
                '</p>' .
                '</div>',
                $disable_prefill_info,
                $txt_firstname,
                $txt_lastname,
                $txt_email,
                $txt_username,
                $txt_password,
                $txt_password_desc,
                $password_hint,
                $tos_code,
                $tos_required_html,
                $marketing_consent_html,
                $captcha_code,
                $custom_fields,
                $required_fields,
                $txt_submit,
                $auto_complete_delay_ajax
            );
        } else {
            $form = $form_data['form'];
            if (! $foredit) {
                if (! $form_data['form_dissected']) {
                    $form_data['form_dissected'] = wlm_dissect_custom_registration_form($form_data);
                    $this->save_option($form_id, $form_data);
                }
                $dissected = $form_data['form_dissected'];

                if (! is_array($dissected['fields'])) {
                    return $form;
                }

                $hiddens = '';
                $form    = '<div class="wlm_regform_div wlm_registration wlm_regform_2col">';
                foreach ($dissected['fields'] as $entry) {
                    $required   = wlm_arrval($entry, 'required')  ? ' wlm_required_field' : '';
                    $attributes = '';

                    if (is_array(wlm_arrval($entry, 'attributes'))) {
                        foreach ($entry['attributes'] as $key => $val) {
                            $attributes .= ' ' . $key . '="' . $val . '"';
                        }
                    }

                    switch ($entry['type']) {
                        case 'input':
                            if (1 === (int) wlm_arrval($entry, 'system_field')  && 'password' === $entry['attributes']['type']) {
                                $form .= sprintf(
                                    '<div class="wlm_form_group wlm_required_field">' .
                                    '<label for="wlm_password_field1" class="wlm_form_label" id="wlm_password_label">' .
                                    '<span class="wlm_label_text" id="wlm_password_text">%1$s</span>' .
                                    '</label>' .
                                    '<input class="fld wlm_input_text" id="wlm_password_field1" name="password1" type="password">' .
                                    '<input class="fld wlm_input_text wlm_password_field2" id="wlm_password_field2" name="password2" type="password">' .
                                    '<p class="wlm_field_description">%2$s</p>' .
                                    '</div>',
                                    $entry['label'],
                                    $entry['description']
                                );
                            } else {
                                $form .= sprintf(
                                    '<div class="wlm_form_group %1$s">' .
                                    '<label for="wlm_%2$s_field" class="wlm_form_label" id="wlm_%3$s_label">' .
                                    '<span class="wlm_label_text" id="wlm_%4$s_text">%5$s</span>' .
                                    '</label>' .
                                    '<input class="fld wlm_input_text" id="wlm_%6$s_field" %7$s>' .
                                    '<p class="wlm_field_description">%8$s</p>' .
                                    '</div>',
                                    $required,
                                    $entry['attributes']['name'],
                                    $entry['attributes']['name'],
                                    $entry['attributes']['name'],
                                    $entry['label'],
                                    $entry['attributes']['name'],
                                    $attributes,
                                    $entry['description']
                                );
                            }
                            break;
                        case 'textarea':
                            $value = $entry['attributes']['value'];
                            $form .= sprintf(
                                '<div class="wlm_form_group %1$s">' .
                                '<label for="wlm_%2$s_field" class="wlm_form_label" id="wlm_%3$s_label">' .
                                '<span class="wlm_label_text" id="wlm_%4$s_text">%5$s</span>' .
                                '</label>' .
                                '<textarea class="fld wlm_input_text" id="wlm_%6$s_field" %7$s>%8$s</textarea>' .
                                '<p class="wlm_field_description">%9$s</p>' .
                                '</div>',
                                $required,
                                $entry['attributes']['name'],
                                $entry['attributes']['name'],
                                $entry['attributes']['name'],
                                $entry['label'],
                                $entry['attributes']['name'],
                                $attributes,
                                $entry['attributes']['value'],
                                $entry['description']
                            );
                            break;
                        case 'paragraph':
                            $form .= '<div class="wlm_form_group wlm_form_paragraph">' . $entry['text'] . '</div>';
                            break;
                        case 'header':
                            $form .= '<div class="wlm_form_group wlm_form_section_header">' . $entry['text'] . '</div>';
                            break;
                        case 'select':
                            $options = '';
                            foreach ($entry['options'] as $option) {
                                $options .= sprintf('<option value="%s"%s>%s</option>', $option['value'], $option['selected'] ? ' selected="selected"' : '', $option['text']);
                            }
                            $form .= sprintf(
                                '<div class="wlm_form_group %1$s">' .
                                '<label for="wlm_%2$s_field" class="wlm_form_label" id="wlm_%3$s_label">' .
                                '<span class="wlm_label_text" id="wlm_%4$s_text">%5$s</span>' .
                                '</label>' .
                                '<select class="fld" %6$s>%7$s</select>' .
                                '<p class="wlm_field_description">%8$s</p>' .
                                '</div>',
                                $required,
                                $entry['attributes']['name'],
                                $entry['attributes']['name'],
                                $entry['attributes']['name'],
                                $entry['label'],
                                $attributes,
                                $options,
                                $entry['description']
                            );
                            break;
                        case 'checkbox':
                        case 'radio':
                            $options = '';
                            foreach ($entry['options'] as $option) {
                                // Retain Radio Box status when there's an error in registration.
                                $option_checked = 0;
                                if (isset(wlm_post_data()[ $entry['attributes']['name'] ]) && 'radio' === $entry['attributes']['type']) {
                                    if (wlm_post_data()[ $entry['attributes']['name'] ] === $option['value']) {
                                        $option_checked = 1;
                                    }
                                }

                                $options .= sprintf('<label><input %s value="%s"%s>%s</label>', $attributes, $option['value'], $option_checked ? ' checked="v"' : '', $option['text']);
                            }
                            $form .= sprintf(
                                '<div class="wlm_form_group %1$s">' .
                                '<label for="wlm_%2$s_field" class="wlm_form_label" id="wlm_%3$s_label">' .
                                '<span class="wlm_label_text" id="wlm_%4$s_text">%5$s</span>' .
                                '</label>' .
                                '<div class="wlm_option_group">%6$s</div>' .
                                '<p class="wlm_field_description">%7$s</p>' .
                                '</div>',
                                $required,
                                $entry['attributes']['name'],
                                $entry['attributes']['name'],
                                $entry['attributes']['name'],
                                $entry['label'],
                                $options,
                                $entry['description']
                            );
                            break;
                        case 'hidden':
                            $hiddens .= '<input ' . $attributes . '>';
                            break;
                        case 'tos':
                            $x        = print_r($entry, true);
                            $lightbox = $entry['lightbox'] ? ' wlm_tos_lightbox' : '';
                            $tos      = $entry['description'];

                            // Retain TOS Checkbox status when there's an error in registration.
                            if (isset(wlm_post_data()[ $entry['attributes']['name'] ]) && 'tos' === $entry['type']) {
                                if ('on' === wlm_post_data()[ $entry['attributes']['name'] ]) {
                                    $attributes .= ' checked="v"';
                                }
                            }

                            $text = $entry['text'];
                            if ($lightbox) {
                                wp_enqueue_script('wlm-jquery-fancybox');
                                wp_enqueue_style('wlm-jquery-fancybox');

                                wp_enqueue_script('wlm-popup-regform');
                                wp_enqueue_style('wlm-popup-regform-style');

                                $text = '<a id="go-regform-" class="go-regform" href="#tosform-">' . $text . '</a>';
                            }

                            $form .= sprintf(
                                '<div id="tosform-" class="regform" style="display:none;">' .
                                '<div class="regform-container">' .
                                '<div class="regform-header">' .
                                '<a class="regform-close" href="javascript:void(0)">x</a>' .
                                '</div>' .
                                '<div class="regform-description">' .
                                '<div class="wlm_field_tos_content %1$s" id="tos_data_terms_of_service">%2$s</div>' .
                                '</div>' .
                                '<div class="btn-fld">' .
                                '</div>' .
                                '</div>' .
                                '</div>' .
                                '<div class="wlm_form_group %3$s wlm_form_tos">' .
                                '<label class="wlm_form_label"></label>' .
                                '<div class="wlm_option_group">' .
                                '<label><input%4$s> %5$s</label>' .
                                '</div>' .
                                '<div class="wlm_field_tos_content %1$s" id="tos_data_terms_of_service">%2$s</div>' .
                                '</div>',
                                $lightbox,
                                $tos,
                                $required,
                                $attributes,
                                $text
                            );
                            break;
                    }
                }
                $form .= $tos_required_html;
                $form .= $marketing_consent_html;
                $form .= $captcha_code;
                $form .= '<p class="submit"><input class="submit" id="wlm_submit_button" type="submit" value="' . $dissected['submit'] . '" /></p>';
                $form .= $hiddens;
                $form .= '<input type="hidden" name="custom_fields" value="' . $form_data['fields'] . '" />';
                $form .= '<input type="hidden" name="required_fields" value="' . $form_data['required'] . '" />';
                $form .= '</div>';
                $form .= $disable_prefill_info;
            }
        }

        return str_replace(["\n", "\r", "\t"], '', $form);
    }

    /**
     * Retrieve legacy registration form
     *
     * @param  string  $form_id                         Form ID.
     * @param  string  $captcha_code                    Captcha markup.
     * @param  boolean $foredit                         True if editing the form. Default false.
     * @param  boolean $disable_prefilled               True to disable prefilling of fields fields. Default false.
     * @param  string  $marketing_consent_checkbox_text Marketin consent checkbox text.
     * @param  string  $tos_required_checkbox_text      TOS required checkbox text.
     * @return string
     */
    public function get_legacy_registration_form($form_id, $captcha_code = '', $foredit = false, $disable_prefilled = false, $marketing_consent_checkbox_text = '', $tos_required_checkbox_text = '')
    {
        $form_id      = 'CUSTOMREGFORM-' . substr((string) $form_id, 14);
        $form_data    = $this->get_option($form_id);
        $captcha_code = wlm_trim($captcha_code);

        $marketing_consent_checkbox_text = wlm_trim($marketing_consent_checkbox_text);
        $tos_required_checkbox_text      = wlm_trim($tos_required_checkbox_text);

        $disable_prefilled_js = '
			<script type="text/javascript">
				jQuery(document).ready(function() {

					if(jQuery(\'input[name="mergewith"]\').val() == null) {
						return;
					}

					if (jQuery(\'input[name="orig_firstname"]\').val() != "") {
						jQuery(\'input[name="firstname"]\').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery(\'input[name="orig_lastname"]\').val() != "") {
					 jQuery(\'input[name="lastname"]\').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery(\'input[name="orig_email"]\').val() != "") {
					 jQuery(\'input[name="email"]\').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}
				 });
			</script>
		';
        $disable_prefill_info = ( $disable_prefilled ) ? $disable_prefilled_js : '';

        if ($captcha_code) {
            $captcha_code = sprintf(
                '<tr class="li_fld captcha_html">' .
                '<td class="label">&nbsp;</td>' .
                '<td class="fld_div">%1$s</td>' .
                '</tr>',
                $captcha_code
            );
        }

        $marketing_consent_html = '';
        if ($marketing_consent_checkbox_text) {
            $marketing_consent_html = sprintf(
                '<tr class="li_fld required marketing_consent">' .
                '<td class="label">&nbsp;</td>' .
                '<td class="fld_div">' .
                '<label><input type="checkbox" name="consent_to_market" value="1">%1$s</label>' .
                '</td>' .
                '</tr>',
                $marketing_consent_checkbox_text
            );
        }

        $tos_required_html = '';
        if ($tos_required_checkbox_text) {
            $tos_required_html = sprintf(
                '<tr class="li_fld required tos_required">' .
                '<td class="label">&nbsp;</td>' .
                '<td class="fld_div">' .
                '<label><input type="checkbox" name="tos_required" value="1">%1$s</label>' .
                '</td>' .
                '</tr>',
                $tos_required_checkbox_text
            );
        }

        if (! wlm_arrval($form_data, 'form')) {
            $txt_username            = __('Username', 'wishlist-member');
            $txt_firstname           = __('First Name', 'wishlist-member');
            $txt_lastname            = __('Last Name', 'wishlist-member');
            $txt_email               = __('Email', 'wishlist-member');
            $txt_password            = __('Password (twice)', 'wishlist-member');
            $txt_password_desc       = __('Enter your desired password twice. Must be at least [wlm_min_passlength] characters long.', 'wishlist-member');
            $txt_password_hint_label = __('Password Hint', 'wishlist-member');
            $txt_password_hint_desc  = __('Enter a password hint that will remind you of your password in case you forget it.', 'wishlist-member');
            $txt_submit              = __('Submit Registration', 'wishlist-member');

            $password_hint = sprintf(
                '<tr class="li_fld systemFld">' .
                '<td class="label">%1$s:</td>' .
                '<td class="fld_div">' .
                '<input type="text" class="fld" name="passwordhint" size="12" />' .
                '<div class="desc">%2$s</div>' .
                '</td>' .
                '</tr>',
                $txt_password_hint_label,
                $txt_password_hint_desc
            );

            $password_hint = $this->get_option('password_hinting') ? $password_hint : '';

            $form = sprintf(
                '%1$s
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery("#wlm_new_user_submit_button").on("click",function(e) {
							jQuery("#wlm_new_user_submit_button").attr("disabled", "disabled");
							jQuery("#frm_new_user_reg").submit();

						});
					 });
				</script>
				<table class="wpm_regform_table wpm_registration" cellpadding="0" cellspacing="0">
					<tr class="li_fld systemFld">
						<td class="label">%2$s:</td>
						<td class="fld_div">
							<input type="text" class="fld" name="username" size="25" value="" />
							<div class="desc"></div>
						</td>
					</tr>
					<tr class="li_fld required wp_field">
						<td class="label">%3$s:</td>
						<td class="fld_div">
							<input type="text" class="fld" name="firstname" size="25" value="" />
							<div class="desc"></div>
						</td>
					</tr>
					<tr class="li_fld required wp_field">
						<td class="label">%4$s:</td>
						<td class="fld_div">
							<input type="text" class="fld" name="lastname" size="25" value="" />
							<div class="desc"></div>
						</td>
					</tr>
					<tr class="li_fld systemFld">
						<td class="label">%5$s:</td>
						<td class="fld_div">
							<input type="email" class="fld" name="email" size="25" value="" />
							<div class="desc"></div>
						</td>
					</tr>
					<tr class="li_fld systemFld">
						<td class="label">%6$s:</td>
						<td class="fld_div">
							<input type="password" class="fld" name="password1" size="25" />
							<br />
							<input type="password" class="fld" name="password2" size="25" />
							<div class="desc">
								%7$s
							</div>
						</td>
					</tr>
					%8$s
					%9$s
					%10$s
					%11$s
					<tr class="li_submit">
						<td class="label">&nbsp;</td>
						<td class="fld_div form_button">
							<input type="submit" id="wlm_new_user_submit_button" class="fld button" value="%12$s" />
						</td>
					</tr>
				</table>',
                $disable_prefill_info,
                $txt_username,
                $txt_firstname,
                $txt_lastname,
                $txt_email,
                $txt_password,
                $txt_password_desc,
                $password_hint,
                $tos_required_html,
                $marketing_consent_html,
                $captcha_code,
                $txt_submit
            );
        } else {
            // Extract this so we can get the value of $fields and $required.
            extract((array) $this->get_option($form_id));
            $before_submit = $tos_required_html . $marketing_consent_html . $captcha_code;
            $form          = $form_data['form'];
            if (! $foredit) {
                $form .= $disable_prefill_info;
                $form .= '<input type="hidden" name="custom_fields" value="' . esc_attr($fields) . '" />';
                $form .= '<input type="hidden" name="required_fields" value="' . esc_attr($required) . '" />';
            }
        }

        if (isset($before_submit)) {
            $form = str_replace('<tr class="li_submit">', $before_submit . '<tr class="li_submit">', $form);
        }
        return str_replace(["\n", "\r", "\t"], '', $form);
    }

    /**
     * Get markup for additional levels list
     *
     * @param  array|string $add_levels Array or comma-separated string of level IDs.
     * @return string
     */
    public function reg_form_additional_levels_list($add_levels)
    {
        $wpm_levels             = $this->get_option('wpm_levels');
        $level_list             = '';
        $level_caption          = '';
        $additional_levels_form = '';

        if (! is_array($add_levels)) { // We assume $add_levels is in simple CSV format if it's not an array.
            $add_levels = explode(',', $add_levels);
            array_walk(
                $add_levels,
                function (&$var) {
                    $var = wlm_trim($var);
                }
            );
        }

        if (1 === count($add_levels)) {
            $add_level     = wlm_trim($add_levels[0]);
            $level_list    = "&nbsp;<span class='additional-level'>{$wpm_levels[$add_level]["name"]}</span>";
            $level_caption = 'level';
        } else {
            foreach ($add_levels as $add_level) {
                $add_level = wlm_trim($add_level);
                if (isset($wpm_levels[ $add_level ])) {
                    $level_list .= "<li class='additional-level'>{$wpm_levels[$add_level]["name"]}</li>\n";
                }
            }
            if ('' !== $level_list) {
                $level_list = "<br /><ul class='additional-levels-list'>{$level_list}</ul>";
            }
            $level_caption = 'levels';
        }

        if ('' !== $level_list) {
            // Translators: 1: Level caption.
            $additional_levels_form  = '<span>' . sprintf(__('You are also registering for the following %1$s:', 'wishlist-member'), $level_caption) . "</span>\n";
            $additional_levels_form .= $level_list;
        }
        return $additional_levels_form;
    }

    /**
     * Checks if a id is a valid For Approval Registration
     *
     * @param  string $registration_id Registration ID.
     * @return mixed                  FALSE on Error, Level ID on success
     */
    public function is_for_approval_registration($registration_id)
    {
        $wpm_levels                = $this->get_option('wpm_levels');
        $for_approval_registration = $this->get_option('wlm_for_approval_registration');
        if ($for_approval_registration) {
            $for_approval_registration = unserialize($for_approval_registration);
            if (array_key_exists($registration_id, $for_approval_registration) && $wpm_levels[ $for_approval_registration[ $registration_id ]['level'] ]) {
                return $for_approval_registration[ $registration_id ];
            }
        }
        return false;
    }

    /**
     * Injects For Approval Registration settings to $wpm_levels
     *
     * @param array  $wpm_levels Passed by reference, $wpm_levels.
     * @param string $level_id   Level ID.
     */
    public function inject_for_approval_settings(&$wpm_levels, $level_id)
    {

        $for_approval_registration = $this->get_option('wlm_for_approval_registration');

        if (! $for_approval_registration) {
            return false;
        }

        $for_approval_registration = unserialize($for_approval_registration);
        if (! isset($for_approval_registration[ $level_id ])) {
            return false;
        }

        if ('PinPayments' === $for_approval_registration[ $level_id ]['name']) {
            $spreedlythankyou = $this->get_option('spreedlythankyou');
            if ($spreedlythankyou) {
                $url                     = $this->make_thankyou_url($spreedlythankyou) . '?reg_id=' . $level_id;
                $fapproval_settings      = [
                    'afterregredirect'         => $url,
                    'requireemailconfirmation' => 0,
                ];
                $wpm_levels[ $level_id ] = array_merge((array) $wpm_levels[ $for_approval_registration[ $level_id ]['level'] ], $fapproval_settings);
            }
        } else {
            $wpm_levels[ $level_id ] = (array) $wpm_levels[ $for_approval_registration[ $level_id ]['level'] ];
        }
    }

    /**
     * Get custom registration form fields
     *
     * @param array   $levels         Array of Level IDs.
     * @param boolean $include_hidden True to include hidden fields. Default false.
     */
    public function get_custom_reg_fields($levels = null, $include_hidden = false)
    {

        // Get custom reg forms of the membership levels.
        $wpm_levels  = $this->get_option('wpm_levels');
        $levels_form = [];
        foreach ($wpm_levels as $key => $level) {
            $levels_form[ $key ] = wlm_arrval($level, 'custom_reg_form');
        }

        if (is_array($levels) && ! empty($levels)) {
            $levels      = array_flip($levels);
            $levels_form = array_intersect_key($levels_form, $levels);
        }
        $forms  = $this->get_custom_reg_forms();
        $fields = [];
        foreach ($forms as $form) {
            if (! in_array($form->option_name, (array) $levels_form, true)) {
                continue;
            }
            $form = $form->option_value['form'];
            $form = preg_replace('~>\s+<~', '><', $form);
            preg_match_all('#<tr.*?class=".*?li_fld .*?".*?>.*?</tr>#i', $form, $matches);

            $matches = $matches[0];
            foreach ($matches as $k => $match) {
                $system_fld = preg_match('#<tr.*?class=".*?systemFld.*?".*?>.*?</tr>#i', $match);
                $wp_field   = preg_match('#<tr.*?class=".*?wp_field.*?".*?>.*?</tr>#i', $match);
                $tos        = preg_match('#<tr.*?class=".*?field_tos.*?".*?>.*?</tr>#i', $match);
                $fname      = preg_match('#<tr.*?name=".*?firstname.*?".*?>.*?</tr>#i', $match);
                $lname      = preg_match('#<tr.*?name=".*?lastname.*?".*?>.*?</tr>#i', $match);
                $hidden     = $include_hidden ? false : preg_match('#<tr.*?class=".*?field_hidden.*?".*?>.*?</tr>#i', $match);
                if (! $system_fld && ! $wp_field && ! $tos && ! $hidden && ! $fname && ! $lname) {
                    if (preg_match('/<(input|select|textarea) .*name="(.*?)".*?>/i', $match, $field_name)) {
                        $field_name            = preg_replace('/\[\]$/', '', $field_name[2] ?? '');
                        $fields[ $field_name ] = $match;
                    }
                }
            }
        }
        return $fields;
    }

    /**
     * Check if the current Registration URL
     * is a Fallback URL
     *
     * A fallback Registration URL allows the user
     * to enter the email address he used for payment
     * to proceed with his incomplete registration
     *
     * @param  string $reg Value of wlm_get_data()['reg'].
     * @return boolean
     */
    public function is_fallback_url($reg)
    {
        $reg      = explode('/', (string) $reg, 3);
        $hash     = (string) wlm_arrval($reg, 0);
        $time     = (string) wlm_arrval($reg, 1);
        $fallback = (string) wlm_arrval($reg, 2);
        if ('fallback' === $fallback) {
            $expire = $time + 3600;
            if ($expire > time()) {
                if (md5(wlm_server_data()['REMOTE_ADDR'] . '__' . $time . '__' . $this->GetAPIKey()) === $hash) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get markup for registration fallback
     *
     * @return string
     */
    public function reg_fallback_content()
    {
        global $wlm_fallback_error;

        $error = $wlm_fallback_error ? '<p class="wpm_err">' . esc_html__('Email not found', 'wishlist-member') . '</p>' : '';

        $content = sprintf(
            '<form method="post" class="wlm_fallback">%1$s<p>%2$s</p><input class="wlm_fallback_email" type="email" name="email" value="" size="40" /><input class="wlm_fallback_submit" type="submit" value="%3$s" /></form>',
            $error,
            __('Please enter the email address used to purchase and click the "Continue" button below.', 'wishlist-member'),
            __('Continue', 'wishlist-member')
        );
        return $content;
    }

    /**
     * Get after registration redirect URL
     *
     * @param string $level_id   Level ID.
     * @param array  $wpm_levels Optional wpm_levels.
     * @param string $type       Optional. Can be either after_registration or membership
     *                           forconfirmation. Will check for level settings if not provided.
     */
    public function get_after_reg_redirect($level_id, $wpm_levels = null, $type = null)
    {
        if (empty($wpm_levels) || ! isset($wpm_levels[ $level_id ])) {
            $wpm_levels = $this->get_option('wpm_levels');
            if ($this->is_ppp_level($level_id)) {
                $this->inject_ppp_settings($wpm_levels, $level_id);
            }
        }

        $wpm_level       = $wpm_levels[ $level_id ];
        $wpm_level['id'] = $level_id;

        $afterreg = '';

        if (! in_array($type, ['membership_forconfirmation', 'after_registration'], true)) {
            $type = null;
        }

        $prefix   = $type ? $type : ( wlm_arrval($wpm_level, 'requireemailconfirmation') ? 'membership_forconfirmation' : 'after_registration' );
        $type     = $this->get_option($prefix . '_type');
        $internal = $this->get_option($prefix . '_internal');
        switch ($type) {
            case 'internal':
                $afterreg = $internal ? get_permalink($internal) : home_url();
                break;
            case 'url':
                $afterreg = $this->get_option($prefix);
                break;
            case 'text':
                $afterreg = add_query_arg('sp', $prefix, $this->magic_page());
                break;
            default:
                $afterreg = $internal ? get_permalink($internal) : add_query_arg('sp', $prefix, $this->magic_page());
                break;
        }

        // If no after registration url specified then set it to homepage.
        if (! wlm_trim($afterreg)) {
            $afterreg = get_bloginfo('url');
        }

        // If membership_forconfirmation or requireemailconfirmation then we don't need to check for the individual level's after registration redirect.
        if (in_array($prefix, ['membership_forconfirmation', 'requireemailconfirmation'], true)) {
            return $afterreg;
        }

        $afterreg = apply_filters('wlm_after_registration_redirect', $afterreg, $level_id);
        return $afterreg;
    }

    /**
     * Get after Login redirect URL
     *
     * @param  string $level_id Level ID.
     * @param  array  $user     User object.
     * @return string $url After Login URL
     */
    public function get_after_login_redirect($level_id, $user)
    {

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

        // If no after login url specified then set it to homepage.
        if (! $url) {
            $url = get_bloginfo('url');
        }

        $url = apply_filters('wlm_after_login_redirect', $url, $level_id, $user);
        return $url;
    }

    /**
     * Auto Login a User
     *
     * @param integer $user_id User ID.
     */
    public function wpm_auto_login($user_id)
    {
        // Pull user info.
        wp_set_auth_cookie($user_id);

        // Save login IP.
        $this->Update_UserMeta($user_id, 'wpm_login_ip', $this->ip_tracking_enabled($user_id) ? wlm_get_client_ip() : '');
        $this->Update_UserMeta($user_id, 'wpm_login_date', time());
    }

    /**
     * Check $email address and current IP address again blacklist
     *
     * @param  string $email Email address.
     * @return integer          Bitmask.
     *                      - 0 Email and IP not in blacklist.
     *                      - Bit 1: Email blacklisted.
     *                      - Bit 2: IP blacklisted.
     */
    public function check_blacklist($email)
    {
        if (true === wlm_admin_in_admin()) {
            return 0;
        }
        $emails = wlm_trim($this->get_option('blacklist_email'));
        $ips    = wlm_trim($this->get_option('blacklist_ip'));
        $return = 0;
        if ($emails) {
            $emails = explode("\n", $emails);
            foreach ((array) $emails as $p) {
                $p = '/^' . str_replace('\*', '.*?', preg_quote(wlm_trim($p), '/')) . '$/i';
                if (preg_match($p, $email)) {
                    $return = $return | 1;
                    break;
                }
            }
        }
        if ($ips) {
            $ips = explode("\n", $ips);
            foreach ((array) $ips as $p) {
                $p = '/^' . str_replace('\*', '.*?', preg_quote(wlm_trim($p), '/')) . '$/i';
                if (preg_match($p, wlm_server_data()['REMOTE_ADDR'])) {
                    $return = $return | 2;
                    break;
                }
            }
        }
        return $return;
    }

    /**
     * Registration Hook IDs
     * Used by Extensions that wish to integrate with after registration process
     * Deprecated.
     *
     * @param  integer $hook_id Hook ID.
     * @return string      Hidden input field HTML markup.
     */
    public function after_reg_hook_id($hook_id)
    {
        $return    = '';
        $post_data = wlm_post_data(true);
        if (is_array(wlm_arrval($post_data, 'WLMRegHookIDs'))) {
            foreach ((array) $post_data['WLMRegHookIDs'] as $rhi) {
                $return .= '<input type="hidden" name="WLMRegHookIDs[]" value="' . esc_attr($rhi) . '" />';
            }
        }
        $return .= '<input type="hidden" name="WLMRegHookIDs[]" value="' . esc_attr($hook_id) . '" />';
        return $return;
    }

    /**
     * Checks if a Registration URL suffix is already in use
     *
     * @param  string $suffix                 Registration URL suffix.
     * @param  array  $exclude_levels         Array of Membership Level IDs to exclude.
     * @param  array  $exclude_shopping_carts Array of Payment Provider Thank you URL option names to exclude.
     * @return boolean
     */
    public function reg_url_exists($suffix, $exclude_levels = null, $exclude_shopping_carts = null)
    {
        $suffix   = wlm_trim($suffix);
        $suffixes = [];
        if (! isset($this->sc_integration_uris)) {
            $this->sc_integration_uris = [];
        }
        // Stuff that we remove from our check.
        $exclude_levels         = (array) $exclude_levels;
        $exclude_shopping_carts = (array) $exclude_shopping_carts;

        $keys = array_keys((array) $this->sc_integration_uris);
        foreach ((array) $keys as $key) {
            if (! in_array($key, $exclude_shopping_carts, true)) {
                $suffixes[] = wlm_trim($this->get_option($key));
            }
        }

        $wpm_levels = $this->get_option('wpm_levels');
        foreach ((array) $wpm_levels as $key => $level) {
            if (! in_array((string)$key, array_map('strval', $exclude_levels), true)) {
                $suffixes[] = wlm_trim($level['url']);
            }
        }

        // Remove empty entries and the 2nd function parameter.
        $suffixes = array_diff($suffixes, ['']);

        return in_array($suffix, $suffixes, true);
    }

    /**
     * Generates a Registration / Thank You URL Suffix
     *
     * @param  integer $length Length of the suffix to return. Default 6.
     * @return string         Registration/Thank You URL Suffix
     */
    public function make_reg_url($length = 6)
    {
        $array = array_flip(array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9)));
        do {
            $url = implode('', array_rand($array, $length));
        } while ($this->reg_url_exists($url));
        return $url;
    }

    /**
     * Generate and return the Continue Registration URL for incomplete / temp accounts
     *
     * @param  string $email Email address.
     * @return string URL
     */
    public function get_continue_registration_url($email)
    {
        // If wlm_get_data contains values, include it to the long URL.
        $orig_url_get_data = array_diff(wlm_get_data(true), ['']);
        $get_params_url    = '';
        if (! empty($orig_url_get_data)) {
            $get_params     = http_build_query($orig_url_get_data);
            $get_params_url = '&' . $get_params;
        }

        $longurl = '/continue&e=' . rawurlencode($email) . '&h=' . rawurlencode(md5($email . '__' . $this->GetAPIKey())) . $get_params_url;
        if (1 !== (int) $this->get_option('enable_short_registration_links')) {
            return WLM_REGISTRATION_URL . $longurl;
        }

        $shorturl = base_convert(microtime(), 10, 35);
        $key      = sprintf('tinylink_%s', sha1($longurl));
        $value    = $shorturl . '||' . $longurl . '||' . $email;

        if (! $this->get_continue_registration_url_from_short($shorturl, false)) {
            $this->save_option($key, $value);
        }
        return WLM_REGISTRATION_URL . '/continue&to=' . $shorturl;
    }

    /**
     * Get the continue registration URL from short link
     *
     * @param  string $short Short link.
     * @return string
     */
    public function get_continue_registration_url_from_short($short)
    {
        global $wpdb;
        $short   = esc_sql($short);
        $results = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT `option_name`,`option_value` FROM `' . esc_sql($this->table_names->options) . '` WHERE `option_value` LIKE %s',
                "{$short}||%"
            )
        );
        if (empty($results)) {
            return false;
        } else {
            $value   = $results[0]->option_value;
            $longurl = explode('||', $value, 3);
            return $longurl[1];
        }
    }

    /**
     * Get fallback registration URL.
     *
     * @return string
     */
    public function get_fallback_registration_url()
    {
        $time = time();
        return WLM_REGISTRATION_URL . '/fallback&h=' . md5(wlm_server_data()['REMOTE_ADDR'] . '__' . $time . '__' . $this->GetAPIKey()) . '/' . $time;
    }

    /**
     * Retrieves all Custom Registration Forms from Database
     *
     * @return array
     */
    public function get_custom_reg_forms()
    {
        global $wpdb;
        $forms     = $wpdb->get_results('SELECT option_name, option_value, ID FROM `' . esc_sql($this->table_names->options) . "` WHERE `option_name` LIKE 'CUSTOMREGFORM-%' ORDER BY `option_name` ASC", OBJECT_K);
        $form_sort = [];
        foreach ($forms as $i => $form) {
            $form                      = wlm_maybe_unserialize($form->option_value);
            $forms[ $i ]->option_value = $form;
            $form_sort[ $i ]           = $form['form_name'];
        }
        $form_sort2 = $form_sort;
        array_multisort($form_sort, SORT_ASC, $form_sort2, SORT_DESC, $forms);
        return $forms;
    }

    /**
     * Saves Custom Registration Form
     *
     * @param boolean $redirect True to redirect after saving. Default true.
     * @param array   $data     Form data to save.
     */
    public function save_custom_reg_form($redirect = true, $data = null)
    {
        if (empty($data)) {
            $data = wlm_post_data(true);
        }
        $fname    = stripslashes($data['form_name']);
        $fields   = $data['form_fields'];
        $required = $data['form_required'];
        $fid      = substr($data['form_id'], 14);

        if (empty($fid)) {
            $fid = $this->sanitize_string(microtime());
        }
        $fdata = stripslashes((string) wlm_arrval($data, 'rfdata'));
        $fid   = 'CUSTOMREGFORM-' . $fid;

        $data = [
            'form_name' => $fname,
            'fields'    => $fields,
            'required'  => $required,
            'form'      => $fdata,
        ];

        $data['form_dissected'] = wlm_dissect_custom_registration_form($data);

        $this->save_option($fid, $data);
        $query_string = $this->QueryString('form_id') . '&form_id=' . $fid . '&msg=' . __('<b>Custom Registration Form Saved.</b>', 'wishlist-member');
        if (true === $redirect) {
            header("Location:?{$query_string}");
            exit;
        }
    }

    /**
     * Delete Custom Registration Form
     *
     * @param string $form_id Unique Form ID.
     */
    public function delete_custom_reg_form($form_id)
    {
        $form_id = 'CUSTOMREGFORM-' . $this->sanitize_string(substr($form_id, 14));
        $this->delete_option($form_id);
        $this->msg = __('Form deleted.', 'wishlist-member');
    }

    /**
     * Clone an existing Custom Registration Form
     *
     * @param string $form_id Unique Form ID.
     */
    public function clone_custom_reg_form($form_id)
    {
        $form_id = 'CUSTOMREGFORM-' . $this->sanitize_string(substr($form_id, 14));
        $form    = $this->get_option($form_id);
        if ($form) {
            $form['form_name'] = 'Copy of ' . $form['form_name'];
            $form_id           = $this->sanitize_string('CUSTOMREGFORM-' . microtime(), false);
            $this->add_option($form_id, $form);
            // Translators: 1: Form name.
            $this->msg = sprintf(__('Form <b>%1$s</b> cloned to <b>Copy of %1$s</b>.', 'wishlist-member'), $form['form_name']);
        }
    }

    /**
     * Get the WishList Member requested URL
     */
    public function request_url()
    {
        list($wpm_request_url) = explode('/', strtolower(wlm_server_data()['SERVER_PROTOCOL']), 2);
        $wpm_request_url      .= '://' . wlm_server_data()['HTTP_HOST'] . wlm_server_data()['REQUEST_URI'];
        if (wlm_server_data()['QUERY_STRING']) {
            $wpm_request_url .= '?' . wlm_server_data()['QUERY_STRING'];
        }
        return $wpm_request_url;
    }

    /**
     * Update magic page content with WishList Member Registration Page if needed
     *
     * @param  array $content Array of posts as passed by `the_posts` filter.
     * @return array
     */
    public function registration_page($content)
    {
        static $return_value;

        // Short circuit magic page processing if `wishlistmember_using_magic_page` filter returns true.
        if (apply_filters('wishlistmember_using_magic_page', false)) {
            return $content;
        }

        if (isset(wlm_get_data()['sp'])) {
            return $content; // For wlm3 custom_error_page, see wlm3 hooks.
        }

        $postid = ''; // Run-time Notice fix.
        if (isset($post)) {
            $postid = isset($post->ID) ? $post->ID : '';
        }

        if (! is_null($return_value) && ! is_admin() && (int) $postid === (int) $this->magic_page(false)) {
            return $return_value;
        }

        $wpm_levels = apply_filters('wishlistmember_registration_levels', $this->get_option('wpm_levels'));

        $posts = $content;
        if (is_page() && count($posts)) {
            $post = &$posts[0];
            if ((int) $post->ID === (int) $this->magic_page(false)) {
                $reg         = wlm_get_data()['reg'];
                $payperpost  = $this->is_ppp_level($reg);
                $fallback    = $this->is_fallback_url($reg);
                $forapproval = $this->is_for_approval_registration($reg);
                if ($fallback && array_key_exists('email', wlm_post_data(true))) {
                    $user = $this->get_user_data(0, 'temp_' . md5(wlm_post_data()['email']));
                    if (! $user) {
                        $GLOBALS['wlm_fallback_error'] = 1;
                    } else {
                        $redirect = $this->get_continue_registration_url(wlm_post_data()['email']);
                        header('Location:' . $redirect);
                        exit;
                    }
                }

                if (( ! ( $wpm_levels[ $reg ] ?? false ) && ! $payperpost && ! $fallback && ! $forapproval ) || ! $this->registration_cookie(false, $hash, $reg)) {
                    header('Location:' . get_bloginfo('url'));
                    exit;
                }
                $this->registration_cookie(null, $hash, $reg);
                add_filter('body_class', [$this, 'add_wlm_registration_body_class']);
                $post->post_content = $this->reg_content();
                if ($payperpost) {
                    // Translators: 1: pay per post name.
                    $post->post_title = sprintf(__('Register for %1$s Pay Per Post', 'wishlist-member'), $payperpost->post_title);
                } elseif ($forapproval) {
                    if (false !== strrpos($forapproval['level'], 'payperpost')) {
                        // Translators: 1: pay per post name.
                        $post->post_title = sprintf(__('Register %1$s Pay Per Post', 'wishlist-member'), $forapproval['level_settings']['name']);
                    } else {
                        // Translators: 1: level name.
                        $post->post_title = sprintf(__('Register for %1$s', 'wishlist-member'), $forapproval['level_settings']['name']);
                    }
                } elseif ($fallback) {
                    $post->post_title   = sprintf(__('Enter Your Email to Continue', 'wishlist-member'), $wpm_levels[ $reg ]['name']);
                    $post->post_content = $this->reg_fallback_content();
                } else {
                    // Translators: 1: level name.
                    $post->post_title = sprintf(__('Register for %1$s', 'wishlist-member'), $wpm_levels[ $reg ]['name']);
                }
            }
        }

        unset($post); // <- very important so the loop below does not overwrite the value of the first entry in $posts.

        $hasreg = false;
        foreach ($posts as $post) {
            if (preg_match('/\[(wlm_|wlm)*register.+]/i', $post->post_content)) {
                $hasreg = true;
                break;
            }
        }

        if ($hasreg) {
            $this->force_registrationform_scripts_and_styles = true;
        }

        $return_value = $posts;

        return $posts;
    }

    /**
     * Add class to body for registration forms.
     *
     * @param  array $classes Array of class names as passed by `body_class` filter.
     * @return array
     */
    public function add_wlm_registration_body_class($classes)
    {
        $classes[] = 'wishlistmember-registration-form';
        return $classes;
    }

    /**
     * User registration cleanup.
     *
     * @param integer $uid  User ID.
     * @param array   $data Registration data.
     */
    public function user_registered_cleanup($uid, $data)
    {
        global $wpdb;
        if (1 === (int) $this->get_option('enable_short_registration_links')) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT ID, `option_name`,`option_value` FROM `' . esc_sql($this->table_names->options) . '` WHERE `option_value` LIKE %s',
                    "%||{$data['email']}"
                )
            );
            foreach ($results as $r) {
                $wpdb->delete($this->table_names->options, ['ID' => $r->ID]);
            }
        }
        $this->schedule_sync_membership();
    }

    /**
     * Delay auto complete registration.
     */
    public function delay_auto_complete_registration_handler()
    {
        $post_data = wlm_post_data(true);
        verify_wlm_nonces();
        if (WLM_POST_NONCED) {
            set_transient('wlm_delay_autocomplete_' . $post_data['reg_user_id'], $post_data, 60);
        }
        wp_die();
    }

    /**
     * TempEmailSanitize
     * is a filter that hooks to sanitize_email
     * and makes sure that our temp email address
     * which we use for payment provider integrations
     * go through.
     *
     * @param  string $email Email address.
     * @return string
     */
    public function temp_email_sanitize($email)
    {
        if (
                wlm_post_data()['orig_email'] && ( wlm_post_data()['email'] ) === wlm_post_data()['username'] && 'temp_' . md5(wlm_post_data()['orig_email']) === wlm_post_data()['email']
        ) {
            return wlm_post_data()['email'];
        }
        return $email;
    }
    /**
     * Outputs registration page form data
     */
    public function regpage_form_data()
    {

        $this->reg_page_form_data = isset($this->reg_page_form_data) ? (array) $this->reg_page_form_data : [];
        foreach ($this->reg_page_form_data as $k => $v) {
            if (! empty($v)) {
                $this->reg_page_form_data[ $k ] = stripslashes((string) $v);
            }
        }
        $data = array_diff($this->reg_page_form_data, ['']);

        // Do not prefill temporary email.
        foreach ($data as $k => $v) {
            if (false !== stripos($v, '@temp.mail')) {
                unset($data[ $k ]);
            }
        }
        array_walk_recursive($data, 'wlm_xss_sanitize');
        if (! empty($data)) {
            printf("<script type='text/javascript'>\nvar wlm_regform_values = %s;\n</script>", wp_json_encode($data));
        }
    }

    /**
     * Notify Users with Incomplete Registration
     * Called by WP-Cron
     */
    public function notify_registration()
    {
        // Let's call 3.0's function hook installed.
        return $this->incomplete_registration_notification();
    }

    /**
     * Handle permalink-type registration page URLs
     * Called by 'rewrite_rules_array' hook
     *
     * @param  array $rules Rewrite Rules.
     * @return array
     */
    public function rewrite_rules($rules = null)
    {
        $rules['register/(.+?)'] = 'index.php';
        return $rules;
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_registration_notification', [$wlm, 'notify_registration']);
        add_action('wishlistmember_user_registered', [$wlm, 'user_registered_cleanup'], 10, 3);
        add_action('wp_ajax_nopriv_wishlistmember_delay_autocomplete_registration', [$wlm, 'delay_auto_complete_registration_handler']);
        add_action('wp_ajax_wishlistmember_delay_autocomplete_registration', [$wlm, 'delay_auto_complete_registration_handler']);
        add_filter('sanitize_email', [$wlm, 'temp_email_sanitize'], 1234567890);
        add_filter('the_posts', [$wlm, 'registration_page']);
        add_filter('rewrite_rules_array', [$wlm, 'rewrite_rules']);
    }
);
