<?php

/**
 * Core Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Core Methods trait
*/
trait Core_Methods
{
    /**
     * The original post
     *
     * @var array
     */
    public $orig_post;

    /**
     * The original get
     *
     * @var array
     */
    public $orig_get;

    /**
     * The permalink status
     *
     * @var boolean
     */
    public $permalink;

    /**
     * The taxonomy IDs
     *
     * @var array
     */
    public $taxonomy_ids;

    /**
     * The taxonomies
     *
     * @var array
     */
    public $taxonomies;

    /**
     * WordPress `init` action handler.
     */
    public function init()
    {
        // $this->cache = new WishListMemberCache($this->plugin_slug, $this->get_option('custom_cache_folder'));
        // Process ping from HQ.
        if (isset(wlm_get_data()['_wlping_']) && isset(wlm_get_data()['_hash_'])) {
            $this->process_wlping(wlm_get_data()['_wlping_'], wlm_get_data()['_hash_']);
        }

        if ('wp-login.php' === $GLOBALS['pagenow'] && ! isset($_COOKIE['wlm_login_cookie'])) {
            $_COOKIE['wlm_login_cookie'] = 'WLM Login check';
        }

        // Check for access levels.
        // Do not allow wlm to run it's own access_protection.
        // Let's control it via another plugin. That is much cleane.
        global $wpdb;
        if (defined('WLM_ERROR_REPORTING')) {
            set_error_handler([&$this, 'error_handler'], WLM_ERROR_REPORTING);
        }

        $this->MigrateLevelData();

        // Migrate data pertaining to each content's membership level.
        // This prepares us for user level content.
        $this->MigrateContentLevelData();

        /*
         * Short Codes
         */
        $this->wlmshortcode = new \WishListMember\Shortcodes();

        /*
         * Generate Transient Hash Session
         * and Javascript Code
         */
        if (isset(wlm_get_data()['wlm_th'])) {
            list($field, $name) = explode(':', wlm_get_data()['wlm_th']);
            header('Content-type:text/javascript');
            $ckname = md5('wlm_transient_hash');
            $hash   = md5(wlm_server_data()['REMOTE_ADDR'] . microtime());
            wlm_setcookie("{$ckname}[{$hash}]", $hash, 0, '/');
            echo "<!-- \n\n";
            if ('field' === $field && ! empty($name)) {
                echo 'document.write("<input type=\'hidden\' name=\'' . esc_js($name) . '\' value=\'' . esc_js($hash) . '\' />");';
                echo 'document.write("<input type=\'hidden\' name=\'bn\' value=\'WishListProducts_SP\' />");';
            } else {
                echo 'var wlm_cookie_hash="' . esc_attr($hash) . '";';
            }
            echo "\n\n// -->";
            exit;
        }

        $wpm_levels = (array) $this->get_option('wpm_levels');

        /*
         * WP Cron Hooks
         */

        // Sync Membership.
        if (! wp_next_scheduled('wishlistmember_syncmembership_count')) {
            wp_schedule_event(time(), 'daily', 'wishlistmember_syncmembership_count');
        }

        // Send Queued Email.
        if (! wp_next_scheduled('wishlistmember_email_queue')) {
            wp_schedule_event(time(), 'wlm_minute', 'wishlistmember_email_queue');
        }

        // Process Queued Import.
        if (! wp_next_scheduled('wishlistmember_import_queue')) {
            wp_schedule_event(time(), 'hourly', 'wishlistmember_import_queue');
        }

        // Process Queued Import.
        if (! wp_next_scheduled('wishlistmember_backup_queue')) {
            wp_schedule_event(time(), 'wlm_minute', 'wishlistmember_backup_queue');
        }

        // Process api queue.
        if (! wp_next_scheduled('wishlistmember_api_queue')) {
            wp_schedule_event(time(), 'hourly', 'wishlistmember_api_queue');
        }

        // Unsubscribe Expired Members.
        if (! wp_next_scheduled('wishlistmember_unsubscribe_expired')) {
            wp_schedule_event(time(), 'hourly', 'wishlistmember_unsubscribe_expired');
        }

        // Schedule the cron to run the cancelling of memberships. Glen Barnhardt 4-16-2010.
        if (! wp_next_scheduled('wishlistmember_check_scheduled_cancelations')) {
            wp_schedule_event(time(), 'hourly', 'wishlistmember_check_scheduled_cancelations');
        }

        // Schedule the cron to run the cancelling of waiting level cancellations. Glen Barnhardt 10-27-2010.
        if (! wp_next_scheduled('wishlistmember_check_level_cancelations')) {
            wp_schedule_event(time(), 'hourly', 'wishlistmember_check_level_cancelations');
        }

        // Schedule the cron to run the notification of members with incomplete registration. Fel Jun 10-27-2010.
        if (! wp_next_scheduled('wishlistmember_registration_notification')) {
            wp_schedule_event(time(), 'hourly', 'wishlistmember_registration_notification');
        }

        // Schedule the cron to run the notification of members with incomplete registration. Fel Jun 10-27-2010.
        if (! wp_next_scheduled('wishlistmember_email_confirmation_reminders')) {
            wp_schedule_event(time(), 'hourly', 'wishlistmember_email_confirmation_reminders');
        }

        // Schedule the cron to run the notification for expiring members. Peter 02-20-2013.
        if (! wp_next_scheduled('wishlistmember_expring_members_notification')) {
            wp_schedule_event(time(), 'daily', 'wishlistmember_expring_members_notification');
        }

        // Schedule the cron to run User Level modifications.
        if (! wp_next_scheduled('wishlistmember_run_scheduled_user_levels')) {
            // Schedule the event daily.
            wp_schedule_event(time(), 'hourly', 'wishlistmember_run_scheduled_user_levels');
        }

        // Schedule the cron to run User Level Actions.
        if (! wp_next_scheduled('wishlistmember_run_user_level_actions')) {
            // Schedule the event daily.
            wp_schedule_event(time(), 'hourly', 'wishlistmember_run_user_level_actions');
        }

        // Schedule the cron to run file protection migration.
        if (! wp_next_scheduled('wishlistmember_migrate_file_protection')) {
            // Schedule the event twice daily.
            wp_schedule_event(time(), 'twicedaily', 'wishlistmember_migrate_file_protection');
        }

        if (wlm_get_data()['wlmfile']) {
            $this->file_protect_load_attachments();
            $this->file_protect(wlm_get_data()['wlmfile']);
        }
        if (wlm_get_data()['wlmfolder']) {
            if (1 === (int) $this->get_option('folder_protection')) {
                $this->folder_protect(wlm_get_data()['wlmfolder'], wlm_get_data()['restoffolder']);
            }
        }

        $wpm_current_user = wp_get_current_user();

        if (( isset(wlm_get_data()['wlmfolderinfo']) ) && ( $wpm_current_user->caps['administrator'] )) {
            wlm_print_style(get_bloginfo('wpurl') . '/wp-admin/css/wp-admin.css');

            /*
             * Security check. we dont want display list of all files on the  server right?
             * We make it limited only to folder protection folder even for admin.
             */
            $needle = $this->get_option('rootOfFolders');
            $haystack = wlm_get_data()['wlmfolderinfo'];
            $pos = strpos($haystack, $needle);

            if (false === $pos) {
                die();
            }

            $handle = opendir(wlm_get_data()['wlmfolderinfo']);
            if ($handle) {
                ?>
                <div style="padding-top:5px;padding-left:20px;">
                    <table>
                        <tr>
                            <th> URL</th>
                        </tr>
                        <?php
                        while (false !== ( $file = readdir($handle) )) {
                            // Do something with the file.
                            // Note that '.' and '..' is returned even.
                            if (! ( ( '.' === $file ) || ( '..' === $file ) || ( '.htaccess' === $file ) )) {
                                ?>
                                <tr>

                                    <td> <?php echo esc_html(wlm_get_data()['wlmfolderLinkinfo'] . '/' . $file); ?></td>

                                </tr>

                                <?php
                            }
                        }
                        ?>
                    </table>
                </div>
                <?php
                closedir($handle);
            }

            die();
        }

        if (wlm_get_data()['clearRecentPosts']) {
            if (is_admin()) {
                $this->delete_option('RecentPosts');
            }
        }

        // Email confirmation.
        if (wlm_get_data()['wlmconfirm']) {
            list($uid, $hash) = explode('/', wlm_get_data()['wlmconfirm'], 2);
            $user             = new \WishListMember\User($uid, true);
            $level_id         = $user->ConfirmByHash($hash);
            if ($level_id) {
                // Send welcome email.
                $userinfo = $user->user_info->data;

                delete_user_meta($userinfo->ID, 'wlm_email_confirmation_reminder');

                if ($this->get_option('auto_login_after_confirm')) {
                    $this->wpm_auto_login($uid);

                    // Redirect user to the after login page if "Auto Login Member After Clicking Confirmation Link" is enabled.
                    if ($this->get_option('enable_login_redirect_override')) {
                        wp_safe_redirect($this->get_after_login_redirect($level_id, $user));
                        exit;
                    }
                }
                wp_safe_redirect($this->get_after_reg_redirect($level_id, null, 'after_registration'));
                exit;
            }
        }

        // We just save the original post and get data just in case we need them later.
        $this->orig_post = wlm_post_data(true);
        $this->orig_get  = wlm_get_data(true);
        // Remove unsecure information.
        unset($this->orig_post['password']);
        unset($this->orig_get['password']);
        unset($this->orig_post['password1']);
        unset($this->orig_get['password1']);
        unset($this->orig_post['password2']);
        unset($this->orig_get['password2']);

        // Load extensions.
        foreach ((array) $this->extensions as $extension) {
            include_once $extension;
            $this->register_extension($WLMExtension['Name'], $WLMExtension['URL'], $WLMExtension['Version'], $WLMExtension['Description'], $WLMExtension['Author'], $WLMExtension['AuthorURL'], $WLMExtension['File']);
        }

        if (false !== strpos(urldecode(wlm_server_data()['REQUEST_URI']), '/wlmapi/2.0/')) {
            if (file_exists($this->plugin_dir . '/core/API2.php')) {
                require_once WLM_PLUGIN_DIR . '/legacy/core/API2.php';
                preg_match('/\/wlmapi\/2\.0\/(xml|json|php)?\//i', urldecode(wlm_server_data()['REQUEST_URI']), $return_type);
                $return_type = $return_type[1];
                $wlmapi      = new \WLMAPI2('EXTERNAL');
                switch ($wlmapi->return_type) {
                    case 'XML':
                        header('Content-type: text/xml');
                        break;
                    case 'JSON':
                        header('Content-type: application/json');
                        break;
                    default:
                        header('Content-type: text/plain');
                        break;
                }

                // API responses must not be cached.
                header('Cache-Control: no-cache');

                // Clean output buffering to make sure nothing gets sent over with our API response.
                @ob_end_clean();
                fwrite(WLM_STDOUT, $wlmapi->result);

                // Record API used.
                $api_used = $this->get_option('WLMAPIUsed');
                $date     = wlm_date('Y-m-d');
                if ($api_used) {
                    $api_used = (array) wlm_maybe_unserialize($api_used);
                    if (isset($api_used['api2']) && $api_used['api2']['date'] === $date) {
                        $request                     = (int) $api_used['api2']['request'];
                        $api_used['api2']['request'] = $request + 1;
                    } else {
                        $arr              = [
                            'request' => 1,
                            'date'    => $date,
                        ];
                        $api_used['api2'] = $arr;
                    }
                } else {
                    $arr              = [
                        'request' => 1,
                        'date'    => $date,
                    ];
                    $api_used['api2'] = $arr;
                }
                $this->save_option('WLMAPIUsed', wlm_maybe_serialize((array) $api_used));

                exit;
            }
        }

        if (! defined('WLMCANSPAM')) {
            define(
                'WLMCANSPAM',
                sprintf(
                    // Translators: 1 - Unsubscribe link, 2 - Link to WP Profile.
                    __("If you no longer wish to receive communication from us:\n%1\$s\n\nTo update your contact information:\n%2\$s", 'wishlist-member'),
                    get_bloginfo('url') . '/?wlmunsub=%s',
                    get_bloginfo('wpurl') . '/wp-admin/profile.php'
                )
            );
        }

        $this->permalink = (bool) get_option('permalink_structure'); // We get permalink status.

        if (wlm_post_data()['cookiehash']) {
            wlm_inject_cookie('wishlist_reg_cookie', stripslashes(wlm_post_data()['cookiehash']), 0, '/');
        }

        if (wlm_get_data()['wlmunsub']) {
            list($uid, $key) = explode('/', wlm_get_data()['wlmunsub']);
            $mykey           = substr(md5($uid . AUTH_SALT), 0, 10);
            $user            = $this->get_user_data($uid);
            if ($user->ID && $mykey === $key) {
                // Check if the user is already unsubscribe so we don't resend the unsubscribe notification.
                // Whenever the unsubscribe url is visited.
                // Value of 1 means the user is already unsubscribe, value of 0 means user's email broad status is still active.
                $is_user_unsubscribe = $this->Get_UserMeta($user->ID, 'wlm_unsubscribe');
                if (0 === (int) $is_user_unsubscribe) {
                    $this->Update_UserMeta($user->ID, 'wlm_unsubscribe', 1);
                    if (1 === (int) $this->get_option('unsub_notification')) {
                        $recipient_email = '' === wlm_trim($this->get_option('unsubscribe_notice_email_recipient')) ? get_bloginfo('admin_email') : $this->get_option('unsubscribe_notice_email_recipient');
                        $this->send_email_template('admin_unsubscribe_notice', $user->ID, [], $recipient_email);
                    }
                    $this->send_unsubscribe_notification_to_user($user);
                }

                $url = $this->unsubscribe_url();
                if ($url) {
                    header('Location:' . $url);
                    exit;
                } else {
                    add_action('wp_head', [&$this, 'unsub_javascript']);
                }
            }
        }

        if (wlm_get_data()['wlmresub']) {
            list($uid, $key) = explode('/', wlm_get_data()['wlmresub']);
            $mykey           = substr(md5($uid . AUTH_SALT), 0, 10);
            $user            = $this->get_user_data($uid);
            if ($user->ID && $mykey = $key) {
                $this->Delete_UserMeta($user->ID, 'wlm_unsubscribe');
            }
            $url = $this->resubscribe_url();
            if ($url) {
                header('Location:' . $url);
                exit;
            } else {
                add_action('wp_head', [&$this, 'resub_javascript']);
            }
        }

        if (wlm_get_data()['loginlimit']) {
            add_filter(
                'wp_login_errors',
                function ($errors) {
                    $errors->add('wlm_loginlimit', $this->get_option('login_limit_error'));
                    return $errors;
                }
            );
        }

        // Process registration URL...
        $scuri = $this->registration_url();

        if (1 === (int) wlm_get_data()['wpm_download_sample_csv']) {
            $this->sample_import_csv();
        }

        if ($scuri) {
            // Strip out trailing .php.
            $scuri = preg_replace('/\.php$/i', '', $scuri);

            // Match the URL with an SC Method.
            $scuris = array_keys((array) $this->sc_integration_uris);
            foreach ((array) $scuris as $x) {
                if ($this->get_option($x) === $scuri) {
                    $scuri = $x;
                    break;
                }
            }

            // Get the method name to call for the shoppingcart.
            if (isset($this->sc_integration_uris[ $scuri ])) {
                $scmethod = $this->sc_integration_uris[ $scuri ];
                wlm_post_data()['WishListMemberAction'] = 'WPMRegister';
            } else {
                do_action('wishlistmember_paymentprovider_handler', $scuri);
                // Not a valid SC Integration URI - we terminate.
                $this->cart_integration_terminate($scuri);
            }
        }

        $wlm_action = wlm_post_data()['WishListMemberAction'];
        $require_nonce_and_capability_check = true;

        if('WPMRegister' === $wlm_action && !wlm_admin_in_admin()){
            // No nonce and capability check needed for WPMRegister if not in admin area.
            $require_nonce_and_capability_check = false;
        }

        if ($require_nonce_and_capability_check) {
            if (!current_user_can('manage_options')) {
                return;
            }
            verify_wlm_nonces();
            if (!WLM_POST_NONCED) {
                return;
            }
        }
        switch ($wlm_action) {
            case 'WPMRegister':
                // Added by Admin.
                if (true === wlm_admin_in_admin()) {
                    $wpm_errmsg = '';
                    $registered = $this->wpm_register(wlm_post_data(true), $wpm_errmsg);
                    if ($registered) {
                        $_POST = ['msg' => __('<b>New Member Added.</b>', 'wishlist-member')];
                    } else {
                        wlm_post_data()['notice'] = $wpm_errmsg;
                    }
                } elseif (wlm_post_data(true)) {
                    $docart = true;

                    /*
                     * this is an attempt to prevent duplicate payment provider registration posts
                     * from being processed it will definitely have its side effects but let's
                     * give it a try and see if people will complain
                     */

                    if ($this->get_option('PreventDuplicatePosts') && $scmethod) {
                        // Do not check for duplicate posts for PayPalPS short URL.
                        if (( 'WLM_INTEGRATION_PAYPAL' === $scmethod['class'] && ! empty(wlm_get_data()['pid']) )) {
                            null;
                        } elseif (( 'WLM_INTEGRATION_PAYPALEC' === $scmethod['class'] && in_array(wlm_get_data()['action'], ['purchase-express'], true) )) {
                            null;
                        } elseif (in_array(wlm_get_data()['stripe_action'], ['sync', 'invoice', 'invoices', 'update_payment', 'cancel', ''], true)) {
                            // Do not check for duplicate posts on Stripe's action=sync, invoices, invoice, update_payment and cancel.
                            null;
                        } elseif (( 'WLM_INTEGRATION_1SHOPPINGCART' === $scmethod['class'] && (  !in_array(wlm_post_data()['status'], ['accepted', 'approved', 'authorized', 'pending'], true) ) )) {
                            // Do not check if the status is not for registration.
                            // This should allow processing of cancellation via 1SC IPN even with Prevent Duplicate settings enabled.
                            null;
                        } else {
                            $now         = time();
                            $recentposts = (array) $this->get_option('RecentPosts');

                            /*
                             * we now compute posthash from both $_GET and $_POST and not
                             * just from $_POST because some integrations don't send $_POST
                             * data but $_GET.
                             */
                            $posthash = md5(serialize(wlm_get_data(true)) . serialize(wlm_post_data(true)));

                            asort($recentposts);
                            foreach ((array) array_keys((array) $recentposts) as $k) {
                                if ($recentposts[ $k ] < $now) {
                                    unset($recentposts[ $k ]);
                                }
                            }
                            if ($recentposts[ $posthash ]) {
                                $docart = false;
                                $url    = $this->duplicate_post_url();
                                if ($url === $this->request_url()) {
                                    $url = get_bloginfo('url');
                                }
                                header("Location: {$url}");
                                exit;
                            } else {
                                $recentposts[ $posthash ] = $now + WLM_DUPLICATE_POST_TIMEOUT;
                            }
                            $this->save_option('RecentPosts', $recentposts);
                        }
                    }
                    if ($docart) {
                        // We save original $_POST to see if it will change.
                        $op = serialize(wlm_post_data(true));
                        if (! class_exists($scmethod['class'])) {
                            include_once $this->plugin_dir . '/lib/' . $scmethod['file'];
                        }
                        $this->RegisterClass($scmethod['class']);

                        /**
                         * Triggered to call payment provider methods as registered via ::RegisterClass()
                         *
                         * @param callable $method_function Method or function to call.
                         * @param array    $scmethod {
                         *   Associative array containing payment provider filename, class and method.
                         *   @type  string $file   Path to file.
                         *   @type  string $class  Class name.
                         *   @type  string $method Method name.
                         * }
                         * @param string   $scuri Payment provider slug.
                         */
                        do_action('wishlistmember_call_payment_provider_method', [$this, $scmethod['method']], $scmethod, $scuri);

                        // Record payment provider used.
                        $shoppingcart_used = $this->get_option('WLMShoppinCartUsed');
                        $date              = wlm_date('Y-m-d H:i:s');
                        if ($shoppingcart_used) {
                            $shoppingcart_used                        = (array) wlm_maybe_unserialize($shoppingcart_used);
                            $shoppingcart_used[ $scmethod['method'] ] = $date;
                        } else {
                            $shoppingcart_used[ $scmethod['method'] ] = $date;
                        }
                        $this->save_option('WLMShoppinCartUsed', wlm_maybe_serialize((array) $shoppingcart_used));
                    }
                    $this->cart_integration_terminate();
                }
                break;
            case 'ResetPrivacyEmailTemplates':
                $this->reset_privacy_template();
                break;
            case 'SaveCustomRegForm':
                $this->save_custom_reg_form();
                break;
            case 'CloneCustomRegForm':
                $this->clone_custom_reg_form(wlm_post_data()['form_id']);
                break;
            case 'DeleteCustomRegForm':
                $this->delete_custom_reg_form(wlm_post_data()['form_id']);
                break;
            case 'SaveMembershipLevels':
                $this->save_membership_levels();
                break;
            case 'SaveMembershipContent':
                $this->save_membership_content();
                break;
            case 'SaveMembershipContentPayPerPost':
                $this->save_membership_content_pay_per_post();
                break;
            case 'EasyFolderProtection':
                $this->easy_folder_protection();
                break;
            case 'FolderProtectionParentFolder':
                $this->folder_protection_parent_folder();
                break;
            case 'SaveMembersData':
                $this->save_members_data();
                break;
            case 'MoveMembership':
                $this->move_membership();
                break;
            case 'ImportMembers':
                $this->queue_import_members();
                break;
            case 'ExportMembersChunked':
                $this->export_members_chunked();
                break;
            case 'ExportSettingsToFile':
                $this->export_settings_to_file();
                break;
            // Start - backup stuff.
            case 'BackupSettings':
                $this->backup_generate();
                break;
            case 'RestoreSettings':
                $this->backup_restore(wlm_post_data()['SettingsName'], false);
                break;
            case 'ImportSettings':
                $this->backup_import(1 === (int) wlm_post_data()['backup_first']);
                break;
            case 'ExportSettings':
                $this->backup_download(wlm_post_data()['SettingsName']);
                break;
            case 'DeleteSettings':
                $this->backup_delete(wlm_post_data()['SettingsName']);
                break;
            case 'ResetSettings':
                $this->reset_settings();
                break;
            // End - backup stuff.
            case 'SaveSequential':
                $this->save_sequential_upgrade_configuration();
                break;
            case 'EmailBroadcast':
                // Email broadcast.
                $this->email_broadcast();
                break;
            case 'DoMarketPlaceActions':
                // Marketplace actions.
                $this->do_market_place_actions();
                break;
        }

        // Check that each level has a reg URL specified.
        $changed = false;
        foreach ((array) array_keys((array) $wpm_levels) as $k) {
            if (! $wpm_levels[ $k ]['url']) {
                $wpm_levels[ $k ]['url'] = $this->pass_gen(6);
                $changed                 = true;
            }
        }
        if (
            $changed
        ) {
            $this->save_option('wpm_levels', $wpm_levels);
        }

        // Check if all levels have expirations specified.
        $unspecifiedexpiration = [];
        foreach ((array) $wpm_levels as $level) {
            if (! wlm_arrval($level, 'expire') && ! wlm_arrval($level, 'noexpire') && wlm_arrval($level, 'name')) {
                $unspecifiedexpiration[] = $level['name'];
            }
        }
        if (count($unspecifiedexpiration)) {
            $GLOBALS['unspecifiedexpiration'] = $unspecifiedexpiration;
        }

        $wpm_current_user = wp_get_current_user();
        // No profile editing for members.
        if ($wpm_current_user->ID && 'wp-admin' === basename(dirname(wlm_server_data()['PHP_SELF'])) && 'profile.php' === basename(wlm_server_data()['PHP_SELF']) && ! $this->get_option('members_can_update_info') && ! $wpm_current_user->caps['administrator'] && ! $this->get_option('members_can_update_info') && ! current_user_can('manage_options')) {
            header('Location:' . get_bloginfo('url'));
            exit;
        }

        // Do not allow access to Dashboard for non-admins.
        if ($wpm_current_user->ID && 'wp-admin/index.php' === basename(dirname(wlm_server_data()['PHP_SELF'])) . '/' . basename(wlm_server_data()['PHP_SELF']) && ! current_user_can('edit_posts') && ! current_user_can('manage_options')) {
            header('Location:profile.php');
            exit;
        }

        if ($wpm_current_user->ID) {
            if (empty(wlm_getcookie('wlm_user_sequential'))) {
                $this->do_sequential($wpm_current_user->ID);
                $this->process_scheduled_level_actions($wpm_current_user->ID);
                wlm_setcookie('wlm_user_sequential', 1, time() + 3600, home_url('/', 'relative'));
                wlm_setcookie('wlm_user_sequential', 1, time() + 3600, site_url('/', 'relative'));
            }
        }

        // Spawn cron job if requested.
        if (1 === (int) wlm_get_data()['wlmcron']) {
            spawn_cron();
            exit;
        }

        // Send registration notification by force without waiting for the cron.
        if (1 === (int) wlm_get_data()['regnotification']) {
            $this->notify_registration();
            exit;
        }

        // Send registration notification by force without waiting for the cron.
        if (1 === (int) wlm_get_data()['emailconfirmationreminders']) {
            $this->email_confirmation_reminders();
            exit;
        }

        // Send expiring members notification by force without waiting for the cron.
        if (1 === (int) wlm_get_data()['expnotification']) {
            $this->expiring_members_notification();
            exit;
        }

        if (wlm_get_data()['wlmprocessapiqueues'] > 0) {
            do_action('wishlistmember_api_queue', wlm_get_data()['wlmprocessapiqueues']);
            exit;
        }

        if (wlm_get_data()['wlmprocessbroadcast'] > 0) {
            $x = $this->send_queued_mail();
            exit;
        }

        if (wlm_get_data()['wlmprocessimport'] > 0) {
            $x = $this->process_import_members();
            exit;
        }

        if (wlm_get_data()['wlmprocessbackup'] > 0) {
            $x = $this->process_backup_queue();
            exit;
        }

        if (wlm_get_data()['syncmembership'] > 0) {
            $wpm_current_user = wp_get_current_user();
            if ($wpm_current_user->caps['administrator']) {
                $this->sync_membership_count();
                echo 'Done!';
                exit;
            }
        }

        // Temporary fix for wpm_useraddress.
        $this->fix_user_address(1);

        // Get term_ids for OnlyShowContentForLevel.
        $this->taxonomy_ids = [];

        $this->taxonomies = get_taxonomies(
            [
                '_builtin'     => false,
                'hierarchical' => true,
            ],
            'names'
        ) ?? [];
        array_unshift($this->taxonomies, 'category');
        foreach ($this->taxonomies as $taxonomy) {
            add_action($taxonomy . '_edit_form_fields', [&$this, 'category_form']);
            add_action($taxonomy . '_add_form_fields', [&$this, 'category_form']);
            add_action('create_' . $taxonomy, [&$this, 'save_category']);
            add_action('edit_' . $taxonomy, [&$this, 'save_category']);
        }
        $this->taxonomy_ids = get_terms(
            [
                'taxonomy' => $this->taxonomies,
                'fields'  => 'ids',
                'get'     => 'all',
                'orderby' => 'none',
            ]
        );
        // Cateogry Protection.
    }
    /**
     * Activation
     */
    public function activate()
    {
        global $wpdb;

        $this->CoreActivate();

        // Create WishList Member DB Tables.
        $this->CreateWLMDBTables();

        // This is where you place code that runs on plugin activation.
        // Load all initial values.
        require $this->plugin_dir . '/core/InitialValues.php';
        if (isset($wishlist_member_initial_data) && is_array($wishlist_member_initial_data)) {
            foreach ($wishlist_member_initial_data as $key => $value) {
                $this->add_option($key, $value);
            }
        }

        include_once $this->plugin_dir . '/core/OldValues.php';
        if (isset($wishlist_member_old_initial_values) && is_array($wishlist_member_old_initial_values)) {
            foreach ($wishlist_member_old_initial_values as $key => $values) {
                foreach ((array) $values as $value) {
                    if (strtolower(preg_replace('/\s/', '', $this->get_option($key))) === strtolower(preg_replace('/\s/', '', $value))) {
                        $this->save_option($key, $wishlist_member_initial_data[ $key ]);
                    }
                }
            }
        }

        // Update lostinfo email subject.
        if (! $this->get_option('lostinfo_email_subject_spam_fix_re') && 'RE: Your membership login info' === $this->get_option('lostinfo_email_subject')) {
            $this->save_option('lostinfo_email_subject', $wishlist_member_initial_data['lostinfo_email_subject']);
            $this->save_option('lostinfo_email_subject_spam_fix_re', 1);
        }

        $apikey = $this->get_option('genericsecret');
        if (empty($apikey)) {
            $apikey = wlm_generate_password(50, false);
        }

        $this->add_option('WLMAPIKey', $apikey);

        $user = new \WP_User(1);
        if ($user) {
            $name = wlm_trim($user->first_name . ' ' . $user->last_name);
            if (! $name) {
                $name = $user->display_name;
            }
            if (! $name) {
                $name = $user->user_nicename;
            }
            if (! $name) {
                $name = $user->user_login;
            }
            $this->add_option('email_sender_name', $name);
            $this->add_option('email_sender_address', $user->user_email);
            $this->add_option('newmembernotice_email_recipient', $user->user_email);
        }

        // Add file protection htaccess.
        if (method_exists($this, 'file_protect_htaccess')) {
            $this->file_protect_htaccess(! ( 1 === (int) $this->get_option('file_protection') ));
        }

        $wpm_levels = $this->get_option('wpm_levels');
        // Membership levels cleanup.
        if (is_array($wpm_levels) && count($wpm_levels)) {
            foreach ($wpm_levels as $key => $level) {
                // Add slugs to membership levels that don't have slugs.
                if (empty($level['slug'])) {
                    $level['slug'] = $this->sanitize_string($level['name']);
                }

                /*
                 * turn off sequential upgrade for levels that match any of the ff:
                 * - no upgrade method specified
                 * - no upgrade to specified and method is not remove
                 * - have 0-day moves
                 */
                if (
                    // No upgrade method at all.
                    empty($level['upgradeMethod'])
                    // No upgrade destination and method is not REMOVE.
                    || ( empty($level['upgradeTo']) && 'REMOVE' !== $level['upgradeMethod'] )
                    // 0-Day Moves.
                    || ( 'MOVE' === $level['upgradeMethod'] && ! ( (int) $level['upgradeAfter'] ) && empty($level['upgradeSchedule']) )
                ) {
                    $level['upgradeMethod'] = '0';
                    $level['upgradeTo']     = '0';
                    $level['upgradeAfter']  = '0';
                }

                // Migrate Add To Feature to Level Actions.
                if (( isset($level['addToLevel']) && is_array($level['addToLevel']) && count($level['addToLevel']) > 0 )) {
                    $data = [
                        'level_action_event'  => 'added',
                        'level_action_method' => 'add',
                        'action_levels'       => array_keys($level['addToLevel']),
                        'inheritparent'       => isset($level['inheritparent']) ? $level['inheritparent'] : 0,
                        'sched_toggle'        => 'after',
                        'sched_after_term'    => '0',
                        'sched_after_period'  => 'days',
                    ];
                    $this->level_options->save_option($key, 'scheduled_action', $data);
                    $this->save_option('addto_feature_moved', 1);
                }
                if (( isset($level['removeFromLevel']) && is_array($level['removeFromLevel']) && count($level['removeFromLevel']) > 0 )) {
                    $data = [
                        'level_action_event'  => 'added',
                        'level_action_method' => 'remove',
                        'action_levels'       => array_keys($level['removeFromLevel']),
                        'sched_toggle'        => 'after',
                        'sched_after_term'    => '0',
                        'sched_after_period'  => 'days',
                    ];
                    $this->level_options->save_option($key, 'scheduled_action', $data);
                    $this->save_option('addto_feature_moved', 1);
                }
                // Lets remove Add To Level feature data.
                unset($level['addToLevel']);
                unset($level['removeFromLevel']);

                $wpm_levels[ $key ] = $level;
            }
        } else {
            $wpm_levels = [];
        }
        $this->save_option('wpm_levels', $wpm_levels);

        // Default login limit error.
        if ('' === wlm_trim($this->get_option('login_limit_error'))) {
            $this->save_option('login_limit_error', $wishlist_member_initial_data['login_limit_error']);
        }

        // Default minimum password length.
        if ('' === wlm_trim($this->get_option('min_passlength'))) {
            $this->save_option('min_passlength', $wishlist_member_initial_data['min_passlength']);
        }

        // Sync Membership Content.
        $this->sync_content();

        // Migrate old cydec (qpp) stuff to new cydec. qpp is now a separate deal.
        if (1 !== (int) $this->get_option('cydec_migrated')) {
            if ($this->add_option('cydecthankyou', $this->get_option('qppthankyou'))) {
                $this->delete_option('qppthankyou');
            }

            if ($this->add_option('cydecsecret', $this->get_option('qppsecret'))) {
                $this->delete_option('qppsecret');
            }

            if ('qpp' === $this->get_option('lastcartviewed')) {
                $this->save_option('lastcartviewed', 'cydec');
            }

            $wpdb->query('UPDATE `' . esc_sql($this->table_names->userlevel_options) . '` SET `option_value`=REPLACE(`option_value`,"QPP","CYDEC") WHERE `option_name`="transaction_id" AND `option_value` LIKE "QPP\_%"');

            $this->save_option('cydec_migrated', 1);
        }

        $this->remove_cron_hooks();
        if (! empty($GLOBALS['wp_rewrite'])) {
            if (function_exists('apache_get_modules')) {
                $GLOBALS['wp_rewrite']->flush_rules();
            }
        }

        // Migrate file protection settings to table.
        if (method_exists($this, 'migrate_file_protection')) {
            $this->migrate_file_protection();

            // Migrate folder protection settings.
            $this->folder_protection_migrate(); // Really old to old migration.
            $this->migrate_folder_protection(); // Old to new migration.
        }

        // Migrate old widget if active to new one that uses Class.
        $this->migrate_widget();

        // Migrate data for scheduled add, move and remove to new format.
        $this->MigrateScheduledLevelsMeta();

        /*
         * we clear xxxssapxxx% entries in the database
         * removed in WLM 2.8 to prevent security issues
         */
        $wpdb->query('DELETE FROM `' . esc_sql($this->table_names->options) . '` WHERE `option_name` LIKE "xxxssapxxx%"');
    }

    /**
     * Plugin deactivation hook.
     */
    public function deactivate()
    {
        // $this->backup_generate();
        // We delete magic page.
        wp_delete_post($this->magic_page(false), true);
        // Remove file protection htaccess.
        $this->file_protect_htaccess(true);
        // Remove the cron schedule. Glen Barnhardt 4/16/2010.
        $this->remove_cron_hooks();
    }

    /**
     * Add our custom cron schedule.
     *
     * @param  array $schedules The array of custom cron schedules as passed by `cron_schedules` filter.
     * @return array
     */
    public function wlm_cron_schedules($schedules)
    {
        $schedules['wlm_minute']    = [
            'interval' => 60,
            'display'  => __('Every Minute (added by WishList Member)', 'wishlist-member'),
        ];
        $schedules['wlm_5minutes'] = [
            'interval' => 300,
            'display'  => __('Every 5 Minutes (added by WishList Member)', 'wishlist-member'),
        ];
        $schedules['wlm_10minutes'] = [
            'interval' => 600,
            'display'  => __('Every 10 Minutes (added by WishList Member)', 'wishlist-member'),
        ];
        $schedules['wlm_15minutes'] = [
            'interval' => 900,
            'display'  => __('Every 15 Minutes (added by WishList Member)', 'wishlist-member'),
        ];
        // Add other intervals here.
        return $schedules;
    }

    /**
     * Removes all scheduled cron hooks related to WishList Member.
     */
    public function remove_cron_hooks()
    {
        $hooks  = apply_filters(
            'wishlistmember_remove_cron_hooks',
            [
                'wishlistmember_eway_sync',
                'wishlistmember_1shoppingcart_check_orders_status',
                'wishlistmember_1shoppingcart_get_new_orders_detail',
                'wishlistmember_1shoppingcart_process_orders',
                'wishlistmember_1shoppingcart_update_orders_id',
                'wishlistmember_api_queue',
                'wishlistmember_arb_sync',
                'wishlistmember_attachments_load',
                'wishlistmember_check_level_cancelations',
                'wishlistmember_check_scheduled_cancelations',
                'wishlistmember_email_queue',
                'wishlistmember_import_queue',
                'wishlistmember_backup_queue',
                'wishlistmember_expring_members_notification',
                'wishlistmember_ifs_sync',
                'wishlistmember_registration_notification',
                'wishlistmember_email_confirmation_reminders',
                'wishlistmember_run_scheduled_user_levels',
                'wishlistmember_run_user_level_actions',
                'wishlistmember_syncmembership_count',
                'wishlistmember_unsubscribe_expired',
                'wishlistmember_migrate_file_protection',
            ]
        );
        $scheds = (array) get_option('cron');
        foreach ($scheds as $sched) {
            if (is_array($sched)) {
                foreach (array_keys($sched) as $hook) {
                    if ('wishlistmember_' === substr($hook, 0, 15)) {
                        $hooks[] = $hook;
                    }
                }
            }
        }
        $hooks = array_unique($hooks);

        foreach ($hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Error handler.
     *
     * @param  integer $errno   Error number.
     * @param  string  $errmsg  Error message.
     * @param  string  $errfile Error file.
     * @param  integer $errline Error line number.
     * @return false
     */
    public function error_handler($errno, $errmsg, $errfile, $errline)
    {
        static $errcodes;

        if (! isset($errcodes)) {
            $errcodes = [
                E_ERROR             => 'Fatal run-time error',
                E_WARNING           => 'Run-time warning',
                E_PARSE             => 'Compile-time parse error',
                E_NOTICE            => 'Run-time notice',
                E_CORE_ERROR        => 'Fatal initial startup error',
                E_CORE_WARNING      => 'Initial startup warning',
                E_COMPILE_ERROR     => 'Fatal compile-time error',
                E_COMPILE_WARNING   => 'Compile-time warnings',
                E_USER_ERROR        => 'User-generated error',
                E_USER_WARNING      => 'User-generated warning',
                E_USER_NOTICE       => 'User-generated notice',
                E_STRICT            => 'E_STRICT error',
                E_RECOVERABLE_ERROR => 'Catchable fatal error',
                E_DEPRECATED        => 'E_DEPRECATED error',
                E_USER_DEPRECATED   => 'E_USER_DEPRECATED error',
            ];
        }

        $display = defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY;
        $log     = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;

        if (substr($errfile, 0, strlen($this->plugin_dir)) === $this->plugin_dir) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $code   = $errcodes[ $errno ];
                $errmsg = sprintf('WishList Member Debug. [This is a notification for developers who are working in WordPress debug mode.] %s: %s in %s on line %s', $code, $errmsg, $errfile, $errline);
                if ($log) {
                    error_log($errmsg);
                }
                if ($display) {
                    echo esc_html($errmsg);
                }
            }
        }
        return false;
    }

    /**
     * WP Admin header hook.
     */
    public function admin_head()
    {
        if (! ( current_user_can('manage_posts') )) {
            echo "<style type=\"text/css\">\n\n/* WishList Member */\ndivul#dashmenu{ display:none; }\n#wphead{ border-top-width:2px; }\n#screen-meta a.show-settings{display:none;}\n</style>\n";
        }
    }

    /**
     * Generates the plugin update notice.
     *
     * @param  object $transient The plugin info transient object.
     * @return object The modified plugin info transient object.
     */
    public function plugin_update_notice($transient)
    {
        static $our_transient_response;

        if (empty($transient)) {
            $transient = new \stdClass();
        }

        $version = current_user_can('update_plugins') ? wlm_arrval($_REQUEST, 'wlm3_rollback') : '';

        if ($this->plugin_is_latest() && ! $version) {
            return $transient;
        }

        if (! $our_transient_response) {
            $package = $this->plugin_download_url();
            if (false === $package) {
                return $transient;
            }

            $file = $this->plugin_file;

            $our_transient_response = [
                $file => (object) [
                    'id'           => 'wishlist-member-' . time(),
                    'slug'         => $this->plugin_slug,
                    'plugin'       => $file,
                    'new_version'  => $version ? $version : $this->plugin_latest_version(),
                    'url'          => 'https://wishlistmember.com/',
                    'package'      => $package,
                    'requires_php' => WLM_MIN_PHP_VERSION,
                    'icons'        => [
                        'svg' => plugins_url('ui/images/WishListMember-logomark-16px-wp.svg', WLM_PLUGIN_FILE),
                    ],
                ],
            ];
        }
        if (! isset($transient->response)) {
            $transient->response = [];
        }
        $transient->response = array_merge((array) $transient->response, (array) $our_transient_response);

        return $transient;
    }

    /**
     * Retrieves information about the plugin.
     *
     * @param  mixed  $res    The original response.
     * @param  string $action The action being performed.
     * @param  object $args   The arguments for the action.
     * @return mixed The modified response.
     */
    public function plugin_info_hook($res, $action, $args)
    {
        if (false === $res && 'plugin_information' === $action && $args->slug === $this->plugin_slug) {
            $res                  = new \stdClass();
            $res->name            = 'WishList Member&trade;';
            $res->slug            = $this->plugin_slug;
            $res->version         = $this->plugin_latest_version();
            $res->author          = WLM_PLUGIN_AUTHOR;
            $res->author_profile  = WLM_PLUGIN_AUTHORURI;
            $res->homepage        = WLM_PLUGIN_URI;
            $res->active_installs = wlm_arrval((array) wp_remote_get('http://wishlistactivation.com/counter.php'), 'body') + 0;
            $res->requires        = WLM_MIN_WP_VERSION;
            $res->requires_php    = WLM_MIN_PHP_VERSION;
            $res->banners         = [
                'high' => 'https://wishlist-member-images.s3.amazonaws.com/wp-update-banner-2x.png',
                'low'  => 'https://wishlist-member-images.s3.amazonaws.com/wp-update-banner.png',
            ];
            $res->sections        = [
                'description' => '<p><strong>WishList Member&trade;</strong> is a powerful, yet easy to use membership software solution that can turn any WordPress site into a full-blown membership site.</p>'
                . '<p>Simply install the plugin, and within minutes you’ll have your own membership site up and running… complete with protected, members-only content, integrated payments, member management, and so much more!</p>',

                'changelog'   => '<p>WishList Member&trade; Changelog can be viewed <a href="https://customers.wishlistproducts.com/changelogs/" target="_blank">HERE</a>.</p>',

                'support'     => '<p>WishList Member&trade; offers support using the following options:</p>'
                . '<ul>'
                . '<li><a href="https://wishlistmember.com/doc-categories/video-tutorials/" target="_blank" title="Video Tutorials">Tutorials</a></li>'
                . '<li><a href="https://wishlistmember.com/docs/" target="_blank" title="Help">Help Docs</a></li>'
                . '<li><a href="http://codex.wishlistproducts.com/" target="_blank" title="API Documents">API Docs</a></li>'
                . '<li><a href="https://my.wishlistmember.com/support/" target="_blank" title="Support">Support</a></li>'
                . '</ul>',
            ];
        }
        return $res;
    }

    /**
     * Pre-upgrade function.
     *
     * @param  mixed $return The return value.
     * @param  array $plugin The plugin being upgraded.
     * @return mixed The return value.
     */
    public function pre_upgrade($return, $plugin)
    {
        $plugin = ( isset($plugin['plugin']) ) ? $plugin['plugin'] : '';
        if ($plugin === $this->plugin_file) {
            $dir = sys_get_temp_dir() . '/' . sanitize_title('wishlist-member-upgrade-' . get_bloginfo('url'));

            $this->recursive_delete($dir);

            $this->recursive_copy($this->plugin_dir . '/extensions', $dir . '/extensions');
            $this->recursive_copy(WLM_PLUGIN_DIR . '/lang', $dir . '/lang');
        }
        return $return;
    }

    /**
     * Post plugin upgrade.
     *
     * @param  mixed $return The return value.
     * @param  array $plugin The plugin being upgraded.
     * @return mixed The return value.
     */
    public function post_upgrade($return, $plugin)
    {
        $plugin = ( isset($plugin['plugin']) ) ? $plugin['plugin'] : '';
        if ($plugin === $this->plugin_file) {
            $dir = sys_get_temp_dir() . '/' . sanitize_title('wishlist-member-upgrade-' . get_bloginfo('url'));

            $this->recursive_copy($this->plugin_dir . '/extensions', $dir . '/extensions');
            $this->recursive_copy(WLM_PLUGIN_DIR . '/lang', $dir . '/lang');

            $this->recursive_copy($dir . '/extensions', $this->plugin_dir . '/extensions');
            $this->recursive_copy($dir . '/lang', WLM_PLUGIN_DIR . '/lang');

            $this->recursive_delete($dir);
        }
        return $return;
    }

    /**
     * Displays an update notice if the plugin is not the latest version.
     */
    public function update_nag()
    {
        $current_screen = get_current_screen();
        if (preg_match('/^update/', $current_screen->id)) {
            return;
        }
        if (! $this->plugin_is_latest()) {
            $latest_wpm_ver = $this->plugin_latest_version();
            if (! $latest_wpm_ver) {
                $latest_wpm_ver = $this->version;
            }

            global $current_user;
            $user_id                      = $current_user->ID;
                            $dismiss_meta = 'dismiss_wlm_update_notice_' . $latest_wpm_ver;
            if (! get_user_meta($user_id, $dismiss_meta) && current_user_can('update_plugins')) {
                echo "<div class='update-nag'>";
                // Translators: 1: Latest WLM Version.
                printf(esc_html__('The most current version of WishList Member is v%s.', 'wishlist-member'), esc_html($latest_wpm_ver));
                echo ' ';
                echo "<a href='" . esc_url($this->plugin_update_url()) . "'>";
                esc_html_e('Please update now. ', 'wishlist-member');
                echo '</a> | ';
                echo '<a href="' . esc_url(add_query_arg('dismiss_notice', '0')) . '"> Dismiss </a>';
                echo '</div>';
            }
        }
    }

    /**
     * Dismisses the WishList Member update notice.
     *
     * This function checks if the current version of the plugin is the latest version.
     * If it is not, it retrieves the latest version and adds a user meta to dismiss the update notice.
     */
    public function dismiss_wlm_update_notice()
    {

        global $current_user;
        $user_id = $current_user->ID;

        // If user clicks to ignore the notice, add that to their user meta.
        if (! $this->plugin_is_latest()) {
            $latest_wpm_ver = $this->plugin_latest_version();
            if (! $latest_wpm_ver) {
                        $latest_wpm_ver = $this->version;
            }

            $dismiss_meta = 'dismiss_wlm_update_notice_' . $latest_wpm_ver;
            if (isset(wlm_get_data()['dismiss_notice']) && '0' === (string) wlm_get_data()['dismiss_notice']) {
                add_user_meta($user_id, $dismiss_meta, 'true', true);
            }
        }
    }



    /**
     * Accepts HQ announcements if the user is allowed to update plugins.
     */
    public function accept_hq_announcement()
    {

            global $current_user;
            $user_id      = $current_user->ID;
            $dismiss_meta = 'dismiss_hq_notice';
            $announcement = $this->get_announcement();
        if (! empty($announcement) && ! get_user_meta($user_id, $dismiss_meta) && current_user_can('update_plugins')) {
            echo "<br/><div class='update-nag'>";
            echo wp_kses_post($announcement);
            echo ' ';
            echo '<a href="' . esc_url(add_query_arg('dismiss_hq_notice', '0')) . '"> Dismiss </a>';
            echo '</div>';
        }
    }

    /**
     * Dismiss HQ announcement.
     */
    public function dismiss_hq_announcement()
    {

        global $current_user;
        $user_id = $current_user->ID;

        // If user clicks to ignore the notice, add that to their user meta.
        if (isset(wlm_get_data()['dismiss_hq_notice']) && '0' === (string) wlm_get_data()['dismiss_hq_notice']) {
                $dismiss_meta = 'dismiss_hq_notice';
                add_user_meta($user_id, $dismiss_meta, 'true', true);
        }
    }

    /**
     * Dismiss WishList Member nag
     */
    public function dismiss_wlm_nag()
    {
        if (! empty(wlm_post_data()['nag_name'])) {
            $this->add_option(wlm_post_data()['nag_name'], time());
        }
    }

    /**
     * Pre-upgrade checking
     */
    public function upgrade_check()
    {
        if (! empty(wlm_get_data()['wlm3_rollback'])) {
            return;
        }
        if ('update.php' === basename(wlm_server_data()['SCRIPT_NAME']) && 'upgrade-plugin' === wlm_get_data()['action'] && wlm_get_data()['plugin'] === $this->plugin_file) {
            $check_result = wlm_trim($this->ReadURL(add_query_arg('check', '1', $this->plugin_download_url()), 10, true, true));
            if ('allowed' !== $check_result) {
                header('Location: ' . $check_result);
                exit;
            }
        }
    }

    /**
     * Register frontend custom scripts and styles via `wp_enqueue_scripts` hook.
     */
    public function frontend_scripts_and_styles()
    {
        $magicpage = is_page($this->magic_page(false));
        $fallback  = $magicpage | $this->is_fallback_url(wlm_get_data()['reg']);

        if (true === wlm_arrval($this, 'force_registrationform_scripts_and_styles') || $magicpage || $fallback) {
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('wishlist_member_regform_prefill', $this->plugin_url . '/js/regform_prefill.js', [], $this->version);
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            wp_enqueue_script('tb_images', $this->plugin_url . '/js/thickbox_images.js', [], $this->version);

            switch ($this->get_option('FormVersion')) {
                case 'improved':
                    wp_enqueue_script('wishlist_member_improved_registration_js', $this->plugin_url . '/js/improved_registration_form_frontend.js', 'jquery-ui', $this->version);
                    wp_enqueue_style('wishlist_member_improved_registration_css', $this->plugin_url . '/css/improved_registration_form_frontend.css', 'jquery-ui', $this->version);
                    break;
                case 'themestyled':
                    // Scripts are enqueued as needed by wlm_form_field().
                    break;
                default:
                    wp_enqueue_style('wishlist_member_custom_reg_form_css', $this->plugin_url . '/css/registration_form_frontend.css', [], $this->version);
            }
            wp_enqueue_style('wishlistmember-frontend-styles-reg_form');
            wp_enqueue_style('wishlistmember-frontend-styles-combined');
            add_action('wp_print_footer_scripts', [$this, 'regpage_form_data']);
        }
    }

    /**
     * WP Footer hook.
     */
    public function footer()
    {
        // Terms of service & privacy policy.
        $privacy = [];
        if ($this->get_option('privacy_display_tos_on_footer') && $this->get_option('privacy_tos_page')) {
            $page      = get_page($this->get_option('privacy_tos_page'));
            $privacy[] = sprintf('<a href="%s" target="_blank">%s</a>', esc_url(get_permalink($page->ID)), esc_html($page->post_title));
        }
        if ($this->get_option('privacy_display_pp_on_footer') && $this->get_option('privacy_pp_page')) {
            $page      = get_page($this->get_option('privacy_pp_page'));
            $privacy[] = sprintf('<a href="%s" target="_blank">%s</a>', esc_url(get_permalink($page->ID)), esc_html($page->post_title));
        }
        if ($privacy) {
            printf('<p align="center">%s</p>', wp_kses_post(implode(' | ', $privacy)));
        }

        // Show affiliate link.
        if ($this->get_option('show_linkback')) {
            $url = 'https://wishlistmember.com/';
            $aff = $this->get_option('affiliate_id');
            if ($aff && ! empty($aff)) {
                if (wp_http_validate_url($aff)) {
                    $url = esc_url($aff);
                } else {
                    $url = 'https://wishlistmember.com/wlp.php?af=' . $aff;
                }
            }
            // Translators: 1: affiliate url.
            echo '<p align="center">' . wp_kses_post(sprintf(__('Powered by WishList Member - <a href="%1$s" target="_blank" title="Membership Software">Membership Software</a>', 'wishlist-member'), esc_url($url))) . '</p>';
        }
    }

    /**
     * WP Head hook.
     */
    public function wp_head()
    {
        global $post;
        echo '<!-- Running WishList Member X v' . esc_html($this->version) . " -->\n";
        $p_id = isset($post->ID) ? $post->ID : '';

        $wpmpage = $this->magic_page(false);
        if ((int) $p_id === (int) $wpmpage) {
            echo '<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW" />';
            echo "\n";
            echo '<META NAME="GOOGLEBOT" CONTENT="NOARCHIVE"/ >';
            echo "\n";
            echo '<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE"/ >';
            echo "\n";
            echo '<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE"/ >';
            echo "\n";
            echo '<META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 02 Aug 1999 01:02:03 GMT">';
            echo "\n";
        }

        $wlm_css = $this->get_option('wlm_css'); // WLM3.
        if (false === $wlm_css) {
            wp_register_style('wishlistmember-frontend-styles-reg_form', false, [], WLM_PLUGIN_VERSION);
            wp_add_inline_style('wishlistmember-frontend-styles-reg_form', $this->get_option('reg_form_css'));
            wp_register_style('wishlistmember-frontend-styles-sidebar_widget', false, [], WLM_PLUGIN_VERSION);
            wp_add_inline_style('wishlistmember-frontend-styles-sidebar_widget', $this->get_option('sidebar_widget_css'));
            wp_register_style('wishlistmember-frontend-styles-login_mergecode', false, [], WLM_PLUGIN_VERSION);
            wp_add_inline_style('wishlistmember-frontend-styles-login_mergecode', $this->get_option('login_mergecode_css'));
        } else {
            wp_register_style('wishlistmember-frontend-styles-combined', false, [], WLM_PLUGIN_VERSION);
            wp_add_inline_style('wishlistmember-frontend-styles-combined', $wlm_css);
        }
    }

    /**
     * Retrieve WishList Member new for dashboard feed and output it as JSON.
     */
    public function dashboard_feeds()
    {
        $maxitems = 2;
        $defaults = [
            'url'     => 'http://feeds.feedburner.com/wishlistmembernews',
            'age'     => 7,
            'dismiss' => 'dashboard_feed_dismissed',
        ];

        $args = wp_parse_args(wlm_post_data(true), $defaults);
        $rss  = fetch_feed($args['url']);
        if (! is_wp_error($rss)) {
            $maxitems  = $rss->get_item_quantity(1);
            $rss_items = $rss->get_items(0, $maxitems);
        }

        $dismiss_timestamp = $this->get_option($args['dismiss']) + 0;

        $date_now    = strtotime('now');
        $rss_content = '';
        $results     = [];
        if ($maxitems > 0) {
            // Loop through each feed item and display each item as a hyperlink.
            foreach ($rss_items as $item) {
                $timestamp = $item->get_date('U');
                $item_date = wlm_date(get_option('date_format'), $timestamp);
                $date_diff = $date_now - $timestamp;
                $date_diff = $date_diff / 86400;
                // Only show feeds less than 7 days old.
                if ($date_diff >= $args['age']) {
                    continue;
                }
                if ($timestamp <= $dismiss_timestamp) {
                    continue;
                }

                $results[] = [
                    'title'       => $item->get_title(),
                    'content'     => $item->get_content(),
                    'description' => $item->get_description(),
                    'permalink'   => $item->get_permalink(),
                ];
            }
        }
        wp_send_json($results);
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('admin_head', [$wlm, 'admin_head'], 1);
        add_action('admin_init', [$wlm, 'dismiss_hq_announcement']);
        add_action('admin_init', [$wlm, 'dismiss_wlm_update_notice']);
        add_action('admin_init', [$wlm, 'upgrade_check']);
        add_action('init', [$wlm, 'init']);
        add_action('wp_ajax_wlm_dismiss_nag', [$wlm, 'dismiss_wlm_nag']);
        add_action('wp_ajax_wlm_feeds', [$wlm, 'dashboard_feeds']);
        add_action('wp_enqueue_scripts', [$wlm, 'frontend_scripts_and_styles'], 9999999999);
        add_action('wp_footer', [$wlm, 'footer']);
        add_action('wp_head', [$wlm, 'wp_head']);
        add_filter('cron_schedules', [$wlm, 'wlm_cron_schedules']);
        add_filter('plugins_api', [$wlm, 'plugin_info_hook'], 10, 3);
        add_filter('site_transient_update_plugins', [$wlm, 'plugin_update_notice']);
        add_filter('upgrader_post_install', [$wlm, 'post_upgrade'], 10, 2);
        add_filter('upgrader_pre_install', [$wlm, 'pre_upgrade'], 10, 2);
        register_deactivation_hook(WLM_PLUGIN_FILE, [$wlm, 'deactivate']);
    }
);
