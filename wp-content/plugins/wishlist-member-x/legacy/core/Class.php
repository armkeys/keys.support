<?php

/**
 * Core Class for WishList Member
 *
 * @package wishlistmember
 */

if (! defined('ABSPATH')) {
    die();
}
if (! class_exists('WishListMemberCore')) {

    /**
     * Core WishList Member Class
     *
     * @package    wishlistmember
     * @subpackage classes
     */
    class WishListMemberCore
    {
        const ACTIVATION_URLS        = 'wishlistactivation.com';
        const ACTIVATION_MAX_RETRIES = 5;

        /**
         * Integration shortcodes
         *
         * @var array
         */
        public $integration_shortcodes = [];
        /**
         * Short codes
         *
         * @var array
         */
        public $short_codes = [];
        /**
         * Merge codes
         *
         * @var array
         */
        public $merge_codes = [];
        /**
         * Plugin option name
         *
         * @var string
         */
        public $plugin_option_name;
        /**
         * Table prefix
         *
         * @var string
         */
        public $table_prefix;
        /**
         * Product SKU
         *
         * @var string
         */
        public $product_sku;
        /**
         * Menu ID
         *
         * @var integer
         */
        public $menu_id;
        /**
         * Title
         *
         * @var string
         */
        public $title;
        /**
         * Link
         *
         * @var string
         */
        public $link;
        /**
         * Version
         *
         * @var float
         */
        public $version;
        /**
         * Plugin directory
         *
         * @var string
         */
        public $plugin_dir;
        /**
         * Plugin URL
         *
         * @var string
         */
        public $plugin_url;
        /**
         * XHR
         *
         * @var \WishListXhr
         */
        public $xhr;
        /**
         * Email Broadcast
         *
         * @var \WishListMember\Email_Broadcast
         */
        public $email_broadcast;
        /**
         * Level Options
         *
         * @var \WishListMember\Level_Options
         */
        public $level_options;
        /**
         * Menus
         *
         * @var array
         */
        public $menus;
        /**
         * Marketplace Check URL
         *
         * @var string
         */
        public $marketplace_check_url;
        /**
         * marketplace
         *
         * @var boolean
         */
        public $marketplace;
        /**
         * Form Options
         *
         * @var array
         */
        public $form_options;
        /**
         * Form Option
         *
         * @var string
         */
        public $form_option;
        /**
         * Data Migrated
         *
         * @var integer
         */
        public $data_migrated;

        /**
         * Beta Tester
         *
         * @var boolean
         */
        public $is_beta_tester;

        /**
         * Licensing response
         *
         * @var string
         */
        public $wpwl_check_response = '';

        // -----------------------------------------
        // Constructor.
        public function Constructor($pluginfile, $sku, $menuid, $title, $link)
        {
            // Constructor.
            global $wpdb;
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            $this->plugin_option_name = 'WishListMemberOptions';
            $this->table_prefix      = $wpdb->prefix . 'wlm_';
            $this->options_table    = $this->table_prefix . 'options';

            // Run this before we include pluggable.php so our wp_password_change_notification gets regognize first.
            if ($this->get_option('disable_password_change_email_for_admin')) {
                if (! function_exists('wp_password_change_notification')) {
                    function wp_password_change_notification()
                    {
                    }
                }
                if (! is_multisite()) {
                    include_once ABSPATH . 'wp-includes/pluggable.php';
                }
            }

            // Character encoding.
            $this->blog_charset = get_option('blog_charset');

            $this->product_sku = $sku;
            $this->menu_id     = $menuid;
            $this->title      = $title;
            $this->link       = $link;

            $this->version   = WLM_PLUGIN_VERSION;
            $this->plugin_dir = WLM_PLUGIN_DIR . '/legacy';
            $this->plugin_url = plugins_url('/legacy', WLM_PLUGIN_FILE);

            $this->xhr                 = new WishListXhr($this);
            $this->email_broadcast      = new \WishListMember\Email_Broadcast();
            $this->level_options        = new \WishListMember\Level_Options($this->table_prefix);
            $this->menus               = [];
            $this->marketplace_check_url = 'http://wishlist-marketplace.s3.amazonaws.com/trigger.txt';
            // $market_place = get_transient('wlm_marketplace_check_url_value');
            // If ($market_place === false) {
            // $market_place = $this->ReadURL($this->marketplace_check_url, 10, true, true);
            // Set_transient('wlm_marketplace_check_url_value', $market_place, 86400);
            // }
            $this->marketplace = false;

            $this->ClearOptions();
            $this->DataMigration();

            if ('Save' === wlm_post_data()['WishListMemberAction']) {
                $this->SaveOptions();
            }

            add_action('admin_notices', [&$this, 'ActivationWarning']);
            add_action('init', [&$this, 'WPWLKeyProcess']);

            $this->LoadTables();
        }

        /*
         * Our own hook run everytime an option is updated
         * couldn't find a way to implement this via WordPress hooks
         */

        public function OptionSaveHook($option_name, $option_value)
        {
            if ('mask_passwords_in_emails' === $option_name) {
                $this->save_option('track-mask_passwords_in_emails', [time(), $option_value]);
            }
        }

        /**
         * Load WishList Member Tables
         */
        public function LoadTables($force_reload = false)
        {
            global $wpdb;

            $this->table_names = get_transient('wlm_tables');
            if ($force_reload || ! $this->table_names || $this->version != $this->get_option('CurrentVersion')) {
                // Prepare table names.
                $tables = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $this->table_prefix . '%'));

                $keys              = preg_replace('/^' . preg_quote($this->table_prefix) . '/i', '', $tables);
                $this->table_names = (object) array_combine($keys, $tables);
                set_transient('wlm_tables', $this->table_names, 60 * 60 * 24);
            }
        }

        /**
         * Core Activation Routine
         */
        public function CoreActivate()
        {
            $this->CreateCoreTables();
        }

        /**
         * Displays Beta Tester Message
         */
        public function BetaTester($return)
        {
            $url = 'https://wishlistmember.com/';
            $aff = $this->get_option('affiliate_id');
            if ($aff && ! empty($aff)) {
                if (wp_http_validate_url($aff)) {
                    $url = esc_url($aff);
                } else {
                    $url = 'https://wishlistmember.com/wlp.php?af=' . $aff;
                }
            }

            $message = "This is a <strong><a href='{$url}'>WishList Member</a></strong> Beta Test Site.";
            if (is_admin()) {
                echo '<div class="error fade"><p>';
                echo wp_kses_data($message);
                echo '</p></div>';
            } else {
                echo '<div style="background:#FFEBE8; border:1px solid #CC0000; border-radius:3px; padding:0.2em 0.6em;">';
                echo wp_kses_data($message);
                echo '</div>';
            }
            return $return;
        }

        /**
         * Adds an admin menu
         *
         * @param string $key  Menu Key
         * @param string $name Menu Name
         * @param string $file Menu File
         */
        public function AddMenu($key, $name, $file, $hasSubMenu = false)
        {
            $this->menus[ $key ] = [
                'Name'       => $name,
                'File'       => $file,
                'HasSubMenu' => (bool) $hasSubMenu,
            ];
        }

        /**
         * Retrieves a menu object.  Also displays an HTML version of the menu if the $html parameter is set to true
         *
         * @param  string  $key  The index/key of the menu to retrieve
         * @param  boolean $html If true, it echoes the url in as an HTML link
         * @return object|false Returns the menu object if successful or false on failure
         */
        public function GetMenu($key, $html = false)
        {
            $obj = wlm_arrval($this->menus, $key);
            if ($obj) {
                $obj       = (object) $obj;
                $obj->URL  = '?page=' . $this->menu_id . '&wl=' . $key;
                $obj->HTML = '<a href="' . $obj->URL . '">' . $obj->Name . '</a>';
                if ($html) {
                    echo wp_kses_post($obj->HTML);
                }
                return $obj;
            } else {
                return false;
            }
        }

        public function get_admin_page_to_include(&$include = null, &$wl = null)
        {
            $wl      = wlm_trim(wlm_get_data()['wl']);
            $include = '';
            if (isset($this->menus[ $wl ])) {
                $menu    = $this->menus[ $wl ];
                $include = $this->plugin_dir . '/admin/' . $menu['File'];
            }
            if (! $include || ! file_exists($include) || ! is_file($include)) {
                $include = $this->plugin_dir . '/admin/dashboard.php';
                $wl      = '';
            }
        }

        /**
         * Includes the correct admin interface baesd on the query variable "wl"
         */
        public function AdminPage()
        {
            $this->get_admin_page_to_include($include, $wl);
            echo '<div class="wrap wishlist_member_admin">';
            include $include;
            if (WP_DEBUG) {
                echo '<p>' . esc_html(get_num_queries()) . ' queries in ';
                timer_stop(1);
                echo 'seconds.</p>';
            }
            echo '</div>';
            if (! empty($wl)) {
                ?>
                <script>
                    jQuery(function($){
                        $('#adminmenu #toplevel_page_WishListMember .wp-submenu li').removeClass('current');
                        $('#adminmenu #toplevel_page_WishListMember .wp-submenu a[href$=wl\\=<?php echo esc_attr($wl); ?>]').parent().addClass('current');
                    });
                </script>
                <?php
            }
        }

        /**
         * Displays the content for the "Other" Tab
         */
        public function OtherTab()
        {
            if (! @readfile('https://updates.wishlistproducts.com/legacy/list.html')) {
                printf(
                    '<div class="wrap wishlist_member_admin"><h2>%s</h2><p>%s</p></div>',
                    esc_html__('Other WishList Products Plugins', 'wishlist-member'),
                    wp_kses_data(
                        sprintf(
                            // Translators: %s <a> tag to WishList Products Blog.
                            __('For more WordPress tools and resources please visit the %s', 'wishlist-member'),
                            sprintf(
                                '<a href="http://wishlistproducts.com/blog" target="_blank">%s</a>',
                                esc_html__('WishList Products Blog', 'wishlist-member')
                            )
                        )
                    )
                );
            }
        }

        /**
         * Displays the interface where the customer can enter the license information
         */
        public function WPWLKey()
        {
            ?>
            <div class="wrap wishlist_member_admin">
                <h2>WishList Products License Information</h2>
                <p><?php esc_html_e('Please enter your WishList Products Key below to activate this plugin', 'wishlist-member'); ?></p>
                <form method="post">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row" style="border:none;white-space:nowrap;" class="WLRequired"><?php esc_html_e('WishList Products Key', 'wishlist-member'); ?></th>
                            <td style="border:none" width="1">
                                <input type="text" name="<?php $this->Option('LicenseKey', true); ?>" placeholder="WishList Products Key" value="<?php $this->OptionValue(); ?>" size="48" />
                            </td>
                            <td style="border:none">
                                <?php esc_html_e('This was sent to the email you used during your purchase', 'wishlist-member'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type="hidden" value="0" name="<?php $this->Option('LicenseLastCheck'); ?>" />
                                <?php
                                $this->Options();
                                $this->RequiredOptions();
                                ?>
                                <input type="hidden" value="<strong>License Information Saved</strong>" name="WLSaveMessage" />
                                <input type="hidden" value="Save" name="WishListMemberAction" />
                                <input type="submit" value="Save WishList Products License Key" name="Submit" class="button-primary" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
        }

        public function ActivationWarning()
        {
            if (! current_user_can('manage_options')) {
                return;
            }
            $rets = $this->get_option('LicenseRets', true, true);
            if (is_admin() && $rets > 0 && $rets < self::ACTIVATION_MAX_RETRIES) {
                $msg = get_transient('wlm_serverunavailable');
                if (! empty($msg)) {
                    echo wp_kses_post($msg);
                }
            }
        }

        /**
         * Checks whether to bypass the license form or not.
         *
         * @return boolean
         */
        public function bypass_licensing()
        {
            /**
             * Default result for this function
             *
             * @var boolean
             */
            $bypass_licensing = false;

            /**
             * Host name of the WP Site url.
             *
             * @var string
             */
            $site_url_host = trim(wp_parse_url(strtolower(get_bloginfo('url')), PHP_URL_HOST));
            /**
             * Regex URL patterns.
             *
             * @var string[]
             */
            $patterns = [
                // Developer-type URLs.
                '/^[^\.]+$/',
                '/^.+\.loc$/',
                '/^.+\.local$/',
                '/^.+?\.lndo\.site$/', // Lando local.
                // Staging URLs.
                '/^.+?-liquidwebsites\.com$/', // liquidWeb staging.
                '/^.+?\.wpengine\.com$/', // WPEngine cnames.
                '/^staging[0-9]*\.[^\.]+\..+$/', // staging subdomains.
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $site_url_host)) {
                    $bypass_licensing = true;
                }
            }

            $x = 'wishlistmember_bypass_licensing';
            /**
             * Filters the end result on whether to bypass the WishList Member license for or not.
             *
             * @param boolean $bypass_licensing Whether the license form is to be bypassed.
             */
            return apply_filters($x, $bypass_licensing);
        }

        /**
         * Processes the license information
         */
        public function WPWLKeyProcess()
        {

            // No processing if sku is empty.
            if (empty($this->product_sku)) {
                return;
            }

            if (! function_exists('current_user_can')) {
                return;
            }
            if (isset(wlm_request_data()['wordpress_wishlist_deactivate']) && ! current_user_can('administrator')) {
                return;
            }

            $WPWLKey   = $this->get_option('LicenseKey');
            $WPWLEmail = $this->get_option('LicenseEmail');

            // Bypass activation for.
            if ('****************' === $WPWLKey || $this->bypass_licensing()) {
                $this->delete_option('LicenseRets', 'LicenseSubscription', 'LicenseExpiration');
                $WPWLKey = $this->save_option('LicenseKey', '');
                $this->save_option('LicenseLastCheck', time());
                $this->save_option('LicenseStatus', 1);
                return;
            }

            if (empty($WPWLEmail)) {
                $WPWLEmail = 'Aliens!!!'; // Set dummy value, just bear with me for now.
            }

            $LicenseStatus = $this->get_option('LicenseStatus');
            $Retries       = $this->get_option('LicenseRets', true, true) + 0;

            $this->is_beta_tester = 'beta@wishlistproducts.com' === $WPWLEmail;
            if ($this->is_beta_tester) {
                add_action('admin_notices', [&$this, 'BetaTester']);
                add_action('the_content', [&$this, 'BetaTester']);
            }
            $WPWLLast      = $this->get_option('LicenseLastCheck');
            $WPWLPID       = $this->product_sku;
            $WPWLURL       = strtolower(get_bloginfo('url'));
            $WPWLCheck     = md5("{$WPWLKey}_{$WPWLPID}_{$WPWLURL}");
            $WPWLKeyAction = wlm_post_data()['wordpress_wishlist_deactivate'] == $WPWLPID ? 'deactivate' : 'activate';
            $WPWLTime      = time();
            $Month         = 60 * 60 * 24 * 30;

            if (empty($WPWLKey) && empty($WPWLEmail) && 'deactivate' !== $WPWLKeyAction) {
                $this->delete_option('LicenseKey', 'LicenseStatus');
                return;
            }
            if ($WPWLTime - $Month > $WPWLLast || 'deactivate' === $WPWLKeyAction) {
                $urls    = explode(',', self::ACTIVATION_URLS);
                $urlargs = [
                    '',
                    '',
                    urlencode($WPWLKey),
                    defined('WLM_PRODUCT_SLUG') ? WLM_PRODUCT_SLUG : urlencode($WPWLPID),
                    urlencode($WPWLCheck),
                    urlencode($WPWLEmail),
                    urlencode($WPWLURL),
                    urlencode($WPWLKeyAction),
                    urlencode($this->version),
                ];
                foreach ($urls as &$url) {
                    $urlargs[0] = 'http://%s/activ8.php?key=%s&pid=%s&check=%s&email=%s&url=%s&%s=1&ver=%s&json=1';
                    $urlargs[1] = $url;
                    $url        = call_user_func_array('sprintf', $urlargs);
                }

                $WPWLStatus        = 0;
                $WPWLCheckResponse = 0;
                if ('deactivate' === $WPWLKeyAction || ( ! empty($WPWLKey) && ! empty($WPWLEmail) && '' != wlm_trim($WPWLKey) && '' != wlm_trim($WPWLEmail) )) {
                    $WPWLStatus = $this->ReadURL($urls, 10);
                    if (false !== $WPWLStatus) {
                        $WPWLResult        = json_decode($WPWLStatus);
                        $WPWLCheckResponse = $WPWLResult->activated;
                        $WPWLStatus        = $WPWLCheckResponse;
                        if ($WPWLStatus) {
                            $this->save_option('LicenseEmail', ( $WPWLResult->email ?? '' )); // Save email returned from activation.
                            $this->save_option('LicenseSubscription', ! empty($WPWLResult->subscription));
                            $this->save_option('LicenseExpiration', $WPWLResult->renewal_date);
                            /**
                             * This action is ran when the license key is validated.
                             */
                            do_action('wishlistmember_license_key_validated', $WPWLKey);
                        } else {
                            $WPWLCheckResponse = $WPWLResult->msg;
                            $WPWLStatus        = $WPWLCheckResponse;
                            /**
                             * This action is ran when the license key is not validated.
                             *
                             * @param string $error_message Reason why the key was not validated.
                             */
                            do_action('wishlistmember_license_key_not_validated', $WPWLResult->msg);
                        }
                    }
                }

                if (false === $WPWLStatus) {
                    if ($Retries >= self::ACTIVATION_MAX_RETRIES || 1 !== (int) $LicenseStatus) {
                        $WPWLCheckResponse = 'Unable to contact License Activation Server. <a href="http://wlplink.com/go/activation" target="_blank">Click here for more info.</a>';
                        $WPWLStatus        = $WPWLCheckResponse;
                    } else {
                        $this->save_option('LicenseRets', $Retries + 1, true);
                        $WPWLStatus = $this->get_option('LicenseStatus');

                        // Set the message as a transient.
                        $msg  = '<div class="error fade"><p>';
                        $msg .= __('Warning: Unable to contact License Activation Server. We will keep on trying. <a href="http://wlplink.com/go/activation" target="_blank">Click here for more info.</a>', 'wishlist-member');
                        $msg .= '</p></div>';
                        set_transient('wlm_serverunavailable', $msg, 60 * 60 * 12);
                    }

                    // Staggered rechecks.
                    // If there is an error with wlm servers, check after an hour.
                    // So that we won't keep making requests.
                    $Month      = 60 * 60 * 24 * 30;
                    $checkafter = 60 * 60 * 24 * 7;
                    // For testing check after a minute.
                    // $checkafter = 60;
                    $this->save_option('LicenseLastCheck', $WPWLTime - $Month + $checkafter);
                } else {
                    $this->save_option('LicenseRets', 0, true);
                    $this->save_option('LicenseLastCheck', $WPWLTime);
                    $this->delete_option('activation_problem_notice_sent');
                }

                $WPWLStatus = wlm_trim($WPWLStatus);
                $this->save_option('LicenseStatus', $WPWLStatus);

                if ('deactivate' === $WPWLKeyAction) {
                    $this->delete_option('LicenseRets', 'LicenseStatus', 'LicenseSubscription', 'LicenseExpiration', 'LicenseKey', 'LicenseLastCheck');
                }
            }

            $this->wpwl_check_response = ( isset($WPWLCheckResponse) ? $WPWLCheckResponse : '' );
            if ('1' != $this->get_option('LicenseStatus')) {
                $this->msg = '';
                add_action('admin_notices', [&$this, 'WPWLKeyResponse'], 1);
            }
        }

        /**
         * Displays the license processing status
         */
        public function WPWLKeyResponse()
        {
            if (! current_user_can('manage_options')) {
                return;
            }
            if (strlen($this->wpwl_check_response) > 1) {
                echo '<div class="notice notice-error" id="message"><p><strong>' . wp_kses_post($this->wpwl_check_response) . '</strong></p></div>';
            }
        }

        /**
         * Send an email notification to the admin if license activation server cannot be reached
         */
        public function SendActivationErrorNotice()
        {
            if (! get_transient('last_activation_email_notice') && $this->get_option('activation_problem_notice_sent') < 3 && $this->get_option('send_activation_problem_notice')) {
                $this->send_plaintext_mail(
                    $this->get_option('email_sender_address'),
                    __('WishList Member Cannot Reach The Activation Server', 'wishlist-member'),
                    sprintf(
                        // Translators: %s: blog home url.
                        esc_html__(
                            'This message is to inform you that your installation of WishList Member on %s is currently unable to reach the WishList Products License Activation Server.  This could be caused by an adjustment made to the site URL, firewall issues, network issues, etc.

There is a 30 day grace period in place.  This means that WishList Member is still functioning but it will be unable to function on the URL once 30 days pass if WishList Member continues to be unable to reach the WishList Products License Activation Server.

Note that the system will send this email message once every 48 hours for a maximum of 3 times or until the issue is resolved.

Please visit the link below for more information on how to resolve this connection issue.

http://wlplink.com/go/activation

Thank you.',
                            'wishlist-member'
                        ),
                        home_url()
                    ),
                    []
                );
                $this->save_option('activation_problem_notice_sent', $this->get_option('activation_problem_notice_sent') + 1);
                set_transient('last_activation_email_notice', time(), 60 * 60 * 48); // Do not send in the next 48 hours.
            }
        }

        /**
         * Returns the Query String. Pass a GET variable and that gets removed.
         */
        public function QueryString()
        {
            $args   = func_get_args();
            $args[] = 'msg';
            $args[] = 'err';
            $get    = [];
            parse_str(wlm_server_data()['QUERY_STRING'], $querystring);
            foreach ((array) $querystring as $key => $value) {
                $get[ $key ] = "{$key}={$value}";
            }
            foreach ((array) array_keys((array) $get) as $key) {
                if (in_array($key, $args)) {
                    unset($get[ $key ]);
                }
            }
            return implode('&', $get);
        }

        /**
         * Sets up an array of form options
         *
         * @param string  $name     of the option
         * @param boolean $required Specifies if the option is a required option
         */
        public function Option($name = '', $required = false)
        {
            if ($name) {
                $this->form_option           = $name;
                $this->form_options[ $name ] = (bool) $required;
                echo esc_attr($name);
            } else {
                echo esc_attr($this->form_option);
            }
        }

        /**
         * Retrieves the value of the form option that was previously set with Option method
         *
         * @param  boolean $return  Specifies whether to return the value or just output it to the browser
         * @param  string  $default Default value to display
         * @return string The value of the option
         */
        public function OptionValue($return = false, $default = '')
        {
            if (wlm_arrval($this, 'err')) {
                $x = wlm_post_data()[ $this->form_option ];
            } else {
                $x = $this->get_option($this->form_option);
            }
            if (! strlen($x)) {
                $x = $default;
            }
            if ($return) {
                return $x;
            }
            echo esc_attr($x);
        }

        /**
         * Outputs selected="true" to the browser if $value is equal to the value of the option that was previously set
         *
         * @param string $value
         */
        public function OptionSelected($value)
        {
            $x = $this->OptionValue(true);
            if ($x == $value) {
                echo ' selected="true"';
            }
        }

        /**
         * Outputs checked="true" to the browser if $value is equal to the value of the option that was previously set
         *
         * @param string $value
         */
        public function OptionChecked($value)
        {
            $x = $this->OptionValue(true);
            if ($x == $value) {
                echo ' checked="true"';
            }
        }

        /**
         * Echoes form options that were set as a comma delimited string
         *
         * @param boolean $html echoes form options as the value of a hidden input field with the name "WLOptions"
         */
        public function Options($html = true)
        {
            $value = implode(',', array_keys((array) $this->form_options));
            if ($html) {
                echo '<input type="hidden" name="WLOptions" value="' . esc_attr($value) . '" />';
            } else {
                echo esc_attr($value);
            }
        }

        /**
         * Echoes REQUIRED form options that were set as a comma delimited string
         *
         * @param boolean $html echoes form options as the value of a hidden input field with the name "WLRequiredOptions"
         */
        public function RequiredOptions($html = true)
        {
            $value = implode(',', array_keys((array) $this->form_options, true));
            if ($html) {
                echo '<input type="hidden" name="WLRequiredOptions" value="' . esc_attr($value) . '" />';
            } else {
                echo esc_attr($value);
            }
        }

        /**
         * Clears the form options array
         */
        public function ClearOptions()
        {
            $this->form_options = [];
        }

        // -----------------------------------------
        // Saves Options.
        /**
         * Saves the form options passed by POST
         *
         * @param  boolean $showmsg whether to display the "Settings Saved" message or not
         * @return boolean Returns false if a required field is not set
         */
        public function SaveOptions($showmsg = true)
        {
            foreach ((array) wlm_post_data(true) as $k => $v) {
                if (! is_array($v)) {
                    wlm_post_data()[ $k ] = trim(stripslashes($v));
                }
            }
            $required = explode(',', wlm_post_data()['WLRequiredOptions']);
            foreach ((array) $required as $req) {
                if ($req && ! wlm_post_data()[ $req ]) {
                    $this->err = __('Fields marked with an asterisk (*) are required', 'wishlist-member');
                    return false;
                }
            }
            $options = explode(',', wlm_post_data()['WLOptions']);
            foreach ((array) $options as $option) {
                $this->save_option($option, wlm_post_data()[ $option ]);
            }
            if ($showmsg) {
                $this->msg = wlm_post_data()['WLSaveMessage'] ? wlm_post_data()['WLSaveMessage'] : __('Settings Saved', 'wishlist-member');
            }
        }

        /**
         * Reads the content of a URL using WordPress WP_Http class if possible
         *
         * @param  string|array $url                        The URL to read. If array, then each entry is checked if the previous entry fails
         * @param  integer      $timeout                    (optional) Optional timeout. defaults to 5
         * @param  boolean      $file_get_contents_fallback (optional) true to fallback to using file_get_contents if WP_Http fails. defaults to false
         * @return mixed FALSE on Error or the Content of the URL that was read
         */
        public function ReadURL($url, $timeout = null, $file_get_contents_fallback = null, $wget_fallback = null)
        {
            $urls = (array) $url;
            if (is_null($timeout)) {
                $timeout = 30;
            }
            if (is_null($file_get_contents_fallback)) {
                $file_get_contents_fallback = false;
            }
            if (is_null($wget_fallback)) {
                $wget_fallback = false;
            }

            $x = false;
            foreach ($urls as $url) {
                if (class_exists('WP_Http')) {
                    $http = new WP_Http();
                    $req  = $http->request($url, ['timeout' => $timeout]);
                    $x    = ( is_wp_error($req) || is_null($req) || false === $req ) ? false : ( 200 === (int) $req['response']['code'] ? $req['body'] . '' : false );
                } else {
                    $file_get_contents_fallback = true;
                }

                // Andy - fix for can not load WishList member page error.
                // $old_settings = ini_get('allow_url_fopen');
                // @ini_set('allow_url_fopen',1);
                if (false === $x && ini_get('allow_url_fopen') && $file_get_contents_fallback) {
                    $x = file_get_contents($url);
                }
                // @ini_set('allow_url_fopen',$old_settings);
                if (false === $x && $wget_fallback) {
                    exec('wget -T ' . $timeout . ' -q -O - "' . $url . '"', $output, $error);
                    if ($error) {
                        $x = false;
                    } else {
                        $x = trim(implode("\n", $output));
                    }
                }

                if (false !== $x) {
                    return $x;
                }
            }
            return $x;
        }

        /**
         * Just return False
         *
         * @return boolean Always False
         */
        public function ReturnFalse()
        {
            return false;
        }

        /**
         * Register an external class and its methods for overloading
         *
         * @param string $classname Name of Class to Register
         */
        public function RegisterClass($classname)
        {
            if (! isset($this->imported)) {
                $this->imported = [];
            }
            if (! isset($this->imported_functions)) {
                $this->import_functions = [];
            }

            $import = new $classname();
            // $import_name = get_class($import);
            $import_functions = get_class_methods($import);

            array_push($this->imported, [$classname, $import]);
            foreach ((array) $import_functions as $key => $fxn_name) {
                $this->imported_functions[ $fxn_name ] = &$import;
            }
        }

        /**
         * Simple obfuscation to garble some text
         *
         * @param  string $string String to obfuscate
         * @return string Obfucated string
         */
        public function WLMEncrypt($string)
        {
            $string = serialize($string);
            $hash   = md5($string);
            $string = base64_encode($string);
            for ($i = 0; $i < strlen($string); $i++) {
                $c            = $string[ $i ];
                $o            = ord($c);
                $o            = $o << 1;
                $string[ $i ] = chr($o);
            }
            return str_rot13(base64_encode($string)) . $hash;
        }

        /**
         * Simple un-obfuscation to restore garbled text
         *
         * @param  string $string String to un-obfuscate
         * @return string Un-obfucated string
         */
        public function WLMDecrypt($string)
        {
            // If $string is not a string then return $string, get it?
            if (! is_string($string)) {
                return $string;
            }

            $orig = $string;
            $hash = trim(substr($string, -32));

            // No possible hash in the end, not encrypted.
            if (! preg_match('/^[a-f0-9]{32}$/', $hash)) {
                return $string;
            }

            $string = str_rot13(substr($string, 0, -32));
            $string = base64_decode($string);
            for ($i = 0; $i < strlen($string); $i++) {
                $c            = $string[ $i ];
                $o            = ord($c);
                $o            = $o >> 1;
                $string[ $i ] = chr($o);
            }
            $string = base64_decode($string);

            if (md5($string) == $hash) {
                // Call Decrypt again until it can no longer be decrypted.
                return $this->WLMDecrypt(unserialize($string));
            } else {
                return $orig;
            }
        }

        /**
         * Retrieves the API Key
         *
         * @return string API Key
         */
        public function GetAPIKey()
        {
            $secret = $this->get_option('WLMAPIKey');
            if (! $secret) {
                $secret = $this->get_option('genericsecret');
            }
            return $secret;
        }

        /**
         * Retrieves the tooltip id
         *
         * @return string Tooltip
         */
        public function Tooltip($tooltpid)
        {
            $thisTooltip = '<a class="wishlist-tooltip help" rel="#' . $tooltpid . '" href="help"><span>&nbsp;<i class="icon-question-sign"></i> </span></a>';
            return $thisTooltip;
        }

        /**
         * Remove bad char from string
         *
         * @param  string $string String to be cleaned
         * @return Cleaned string
         */
        public function CleanInput($string)
        {
            $string = str_replace(['<', '>', '"'], '', is_null($string) ? '' : $string);
            return $string;
        }

        /**
         * Migrate data to table
         */
        public function DataMigration()
        {
            global $wpdb;
            $wlm_migrated_name = $this->plugin_option_name . '_Migrated';
            $wlm_migrated      = get_option($wlm_migrated_name) + 0;

            if (1 !== (int) $wlm_migrated) {
                ignore_user_abort(true);
                $wlm_migrated = 1;

                $this->CreateCoreTables();
                $this->PluginOptions = $this->WLMDecrypt(get_option($this->plugin_option_name));
                if (is_array($this->PluginOptions)) {
                    foreach ($this->PluginOptions as $name => $value) {
                        if (is_string($value) && ( strlen($value) > 64 || 'xxx' === substr($name, 0, 3) )) {
                            $autoload = 'no';
                        } else {
                            $autoload = 'yes';
                        }
                        $data = [
                            'option_name'  => $name,
                            'option_value' => wlm_maybe_serialize($value),
                            'autoload'     => $autoload,
                        ];
                        $x    = $wpdb->insert($this->options_table, $data);
                        if (false === $x) {
                            $wlm_migrated = 0;
                        }
                    }
                }
                update_option($wlm_migrated_name, $wlm_migrated);
            }
            $this->data_migrated = $wlm_migrated;
            return $this->data_migrated;
        }

        /**
         * Create options table
         */
        public function CreateCoreTables()
        {
            global $wpdb;

            /*
             * Important: This now makes use of dbDelta function
             *
             * Please refer to the following URL for instructions:
             * http://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
             *
             * VIOLATORS OF dbDelta RULES WILL BE PROSECUTED :D
             */

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            $charset_collate = $wpdb->get_charset_collate();

            $table     = $this->options_table;
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  option_name varchar(64) NOT NULL,
			  option_value longtext NOT NULL,
			  autoload varchar(20) NOT NULL DEFAULT 'yes',
			  PRIMARY KEY  (ID),
			  UNIQUE KEY option_name (option_name),
			  KEY autoload (autoload)
			) {$charset_collate};";
            dbDelta($structure);
            // Reload table names.
            $this->LoadTables(true);
        }
    }

}
?>
