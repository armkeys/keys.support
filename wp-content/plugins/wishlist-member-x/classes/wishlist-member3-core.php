<?php

/**
 * Core Class for WishList Member 3.0
 *
 * @package wishlistmember
 */

if (! defined('ABSPATH')) {
    die();
}
if (! class_exists('WishListMember3_Core')) {
    /**
     * Core Class for WishList Member 3.0
     *
     * @package    wishlistmember3
     * @subpackage classes
     */
    class WishListMember3_Core extends WishListMemberDBMethods
    {
        /**
         * Overloaded instance methods from other objects
         *
         * @var array
         */
        private $instance_methods = [];

        /**
         * WP blog character set
         *
         * @var string
         */
        public $blog_charset;
        /**
         * Scripts
         *
         * @var array
         */
        public $scripts;
        /**
         * Styles
         *
         * @var array
         */
        public $styles;
        /**
         * WP version
         *
         * @var float
         */
        public $wp_version;
        /**
         * Plugin path
         *
         * @var string
         */
        public $plugin_path;
        /**
         * Plugin directory
         *
         * @var string
         */
        public $plugin_dir3;
        /**
         * Plugin file
         *
         * @var string
         */
        public $plugin_file;
        /**
         * Plugin slug
         *
         * @var string
         */
        public $plugin_slug;
        /**
         * plugin basename
         *
         * @var string
         */
        public $plugin_basename;
        /**
         * Plugin URL
         *
         * @var string
         */
        public $plugin_url3;
        /**
         * Legacy directory
         *
         * @var string
         */
        public $legacy_wlm_dir ;
        /**
         * Legacy URL
         *
         * @var string
         */
        public $legacy_wlm_url;
        /**
         * WLM Shortcodes
         *
         * @var array
         */
        public $wlmshortcode;
        /**
         * URL label
         *
         * @var string
         */
        public $_url_label_markup;
        /**
         * Copy command
         *
         * @var string
         */
        public $copy_command;
        /**
         * Page templates
         *
         * @var array
         */
        public $page_templates;
        /**
         * Level email defaults
         *
         * @var array
         */
        public $level_email_defaults;
        /**
         * Level defaults
         *
         * @var array
         */
        public $level_defaults;
        /**
         * PayPerPost email defaults
         *
         * @var array
         */
        public $ppp_email_defaults;
        /**
         * PayPerPost defaults
         *
         * @var array
         */
        public $ppp_defaults;
        /**
         * Js date format
         *
         * @var string
         */
        public $js_date_format;
        /**
         * Js time format
         *
         * @var string
         */
        public $js_time_format;
        /**
         * Js datetime format
         *
         * @var string
         */
        public $js_datetime_format;

        /**
         * Pagination items
         *
         * @var array
         */
        public $pagination_items = [];

        /**
         * Ajax URL
         *
         * @var string
         */
        public $ajaxurl;

        /**
         * Table names
         *
         * @var array
         */
        public $table_names;

        /**
         * Original WP MediaElement
         *
         * @var array
         */
        public $orig_wp_mediaelement;

        /**
         * Flag to indicate if API is running
         *
         * @var boolean
         */
        public $api2_running = false;

        /**
         * Overloaded Constructor of Class.php
         * Initialize main plugin variables
         */
        public function constructor3($pluginfile, $sku, $menuid, $title, $link)
        {
            global $wpdb;
            global $wp;

            // Todo remove this in 3.1.
            if (isset(wlm_get_data()['page']) && 'WishListMember3' === wlm_get_data()['page']) {
                header('Location: ' . add_query_arg('page', $menuid));
                exit;
            }

            require_once ABSPATH . '/wp-admin/includes/plugin.php';

            $this->scripts = [];
            $this->styles  = [];

            $this->product_sku = $sku;
            $this->menu_id     = $menuid;
            $this->title      = $title;
            $this->link       = $link;
            $this->menus      = [];

            $this->blog_charset     = get_option('blog_charset');
            $this->table_prefix      = $wpdb->prefix . 'wlm_';
            $this->options_table    = $this->table_prefix . 'options';
            $this->plugin_option_name = 'WishListMemberOptions';

            $this->version   = WLM_PLUGIN_VERSION;
            $this->wp_version = $GLOBALS['wp_version'];

            $this->plugin_path     = $pluginfile;
            $this->plugin_dir3     = dirname($this->plugin_path);
            $this->plugin_file     = basename(dirname($pluginfile)) . '/' . basename($pluginfile);
            $this->plugin_slug     = sanitize_title_with_dashes(WLM_PLUGIN_NAME);
            $this->plugin_basename = plugin_basename($this->plugin_path);
            $this->plugin_url3     = plugins_url('', '/') . basename($this->plugin_dir3);

            // Path to Legacy WLM.
            $this->legacy_wlm_dir = $this->plugin_dir3 . '/legacy';
            $this->legacy_wlm_url = $this->plugin_url3 . '/legacy';

            // $this->pagination_items  = array(10,25,50,100,250,500);
            $this->pagination_items = [10, 25, 50, 100, 200, 500, 1000, 'Show All'];

            $this->xhr            = new WishListXhr($this);
            $this->email_broadcast = new \WishListMember\Email_Broadcast();
            $this->wlmshortcode   = new \WishListMember\Shortcodes();

            $this->_url_label_markup = '%1$s &nbsp; <a href="" class="wlm-popover copy-url clipboard">Copy URL</a>';

            // Translators: %s: Command / Ctrl.
            $this->copy_command = sprintf(__('Press %s-C to copy', 'wishlist-member'), ( strpos(wlm_server_data()['HTTP_USER_AGENT'], 'Mac OS X') ? 'Command' : 'Ctrl' ));

            $this->page_templates = [];
            $page_templates       = glob($this->plugin_dir . '/resources/page_templates/*.php');
            if ($page_templates) {
                foreach ($page_templates as $page_template) {
                    $template = preg_replace('/\.php$/', '', basename($page_template));
                    include $page_template;
                    $this->page_templates[ $template ] = wlm_trim($content);
                }
            }

            include $this->plugin_dir3 . '/helpers/level-email-defaults.php';
            $this->level_email_defaults = $level_email_defaults;

            include $this->plugin_dir3 . '/helpers/level-defaults.php';
            $this->level_defaults = array_merge($level_defaults, $level_email_defaults);

            include $this->plugin_dir3 . '/helpers/ppp-email-defaults.php';
            $this->ppp_email_defaults = $ppp_email_defaults;

            include $this->plugin_dir3 . '/helpers/ppp-defaults.php';
            $this->ppp_defaults = $ppp_defaults;

            ob_start();

            $this->LoadTables();

            // Load preload files for all integrations.
            foreach (['payments', 'emails', 'others'] as $integration_type) {
                $dir          = $this->plugin_dir3 . '/integrations/' . $integration_type . '/*';
                $integrations = glob($dir . '*', GLOB_ONLYDIR | GLOB_MARK);
                foreach ($integrations as $integration) {
                    $preload = $integration . 'preload.php';
                    if (file_exists($preload)) {
                        include_once $preload;
                    }
                }
            }

            $this->js_date_format     = $this->php2js_date_format(get_option('date_format'));
            $this->js_time_format     = $this->php2js_date_format(get_option('time_format'));
            $this->js_datetime_format = $this->js_date_format . ' ' . $this->js_time_format;

            // We want to make sure that we have the necessary default data for the current version.
            $cver = $this->get_option('CurrentVersion');

            // If CurrentVersion is empty, then we assume it's the first time WLM is installed.
            if (empty($cver)) {
                $this->first_install();
            }

            // This block runs when the version number changes.
            if ($cver != $this->version) {
                $this->version_changed($cver, $this->version);
            }

            // Migrate / update pay per post settings.
            $ppp_settings = $this->get_option('payperpost');
            if (! is_array($ppp_settings)) {
                $ppp_settings = [];
            }

            $old_afterregredirect = count($ppp_settings) && ! isset($ppp_settings['afterreg_redirect_type']); // 2.9 doesn't have this property.
            $old_loginredirect    = count($ppp_settings) && ! isset($ppp_settings['login_redirect_type']); // 2.9 doesn't have this property.

            $ppp_settings = array_merge($this->ppp_defaults, $ppp_settings);

            // Migrate settings for per level after reg redirect page.
            if ($old_afterregredirect && wlm_arrval($ppp_settings, 'afterregredirect') && '---' !== $ppp_settings['afterregredirect'] && is_null($ppp_settings['custom_afterreg_redirect'])) {
                $ppp_settings['custom_afterreg_redirect'] = 1;
                $ppp_settings['afterreg_redirect_type']   = 'page';
                $ppp_settings['afterreg_page']            = $ppp_settings['afterregredirect'];
            }

            // Migrate settings for per level after reg redirect page.
            if ($old_loginredirect && wlm_arrval($ppp_settings, 'loginredirect') && '---' !== $ppp_settings['loginredirect'] && is_null($ppp_settings['custom_login_redirect'])) {
                $ppp_settings['custom_login_redirect'] = 1;
                $ppp_settings['login_redirect_type']   = 'page';
                $ppp_settings['login_page']            = $ppp_settings['loginredirect'];
            }
            $this->save_option('payperpost', $ppp_settings);

            $pd = basename(WLM_PLUGIN_DIR) . '/lang';
            load_plugin_textdomain('wishlist-member', false, $pd);
        }


        /**
         * This method gets called on the first install of WishList Member
         * Note: That this is not called on upgrade
         */
        public function first_install()
        {
            // Set form version to improved.
            $this->save_option('FormVersion', 'themestyled');

            // Paypal smart payment buttons.
            $spb           = $this->get_option('paypalec_spb');
            $spb['enable'] = 1;
            $this->save_option('paypalec_spb', $spb);

            // Activate Stripe payment integration by default on fresh installs.
            $this->save_option('ActiveShoppingCarts', ['integration.shoppingcart.stripe.php']);
            $this->first_install = true;
        }


        /**
         * Called when version has changed
         * i.e. when WLM is upgraded or downgraded
         */
        public function version_changed($old_version, $new_version)
        {

            // Force license recheck on new version.
            $this->delete_option('LicenseLastCheck');

            // Create rollback if previous version is less than current version.
            $this->create_rollback_version($old_version);

            // Save the current version.
            $this->save_option('CurrentVersion', $new_version);

            // Run activation code.
            $this->activate();

            $this->save_option('prevent_ppp_deletion', '1');

            $this->update_level_data();

            $this->version_has_changed = [$old_version, $new_version];
        }


        /**
         * Update level data to 3.0 standards
         */
        public function update_level_data()
        {
            // Make sure all levels have default values.
            $wpm_levels   = $this->get_option('wpm_levels');
            $to_per_level = [
                'require_email_confirmation_start'        => $this->get_option('email_conf_send_after'),
                'require_email_confirmation_send_every'   => $this->get_option('email_conf_send_every'),
                'require_email_confirmation_howmany'      => $this->get_option('email_conf_how_many'),
                'require_email_confirmation_sender_name'  => $this->get_option('email_sender_name'),
                'require_email_confirmation_sender_email' => $this->get_option('email_sender_address'),
                'require_email_confirmation_subject'      => $this->get_option('confirm_email_subject'),
                'require_email_confirmation_message'      => $this->get_option('confirm_email_message'),
                'require_admin_approval_free_user1_sender_name' => $this->get_option('email_sender_name'),
                'require_admin_approval_free_user1_sender_email' => $this->get_option('email_sender_address'),
                'require_admin_approval_free_user1_subject' => $this->get_option('requireadminapproval_email_subject'),
                'require_admin_approval_free_user1_message' => $this->get_option('requireadminapproval_email_message'),
                'require_admin_approval_free_user2_sender_name' => $this->get_option('email_sender_name'),
                'require_admin_approval_free_user2_sender_email' => $this->get_option('email_sender_address'),
                'require_admin_approval_free_user2_subject' => $this->get_option('registrationadminapproval_email_subject'),
                'require_admin_approval_free_user2_message' => $this->get_option('registrationadminapproval_email_message'),
                'require_admin_approval_paid_user1_sender_name' => $this->get_option('email_sender_name'),
                'require_admin_approval_paid_user1_sender_email' => $this->get_option('email_sender_address'),
                'require_admin_approval_paid_user1_subject' => $this->get_option('requireadminapproval_email_subject'),
                'require_admin_approval_paid_user1_message' => $this->get_option('requireadminapproval_email_message'),
                'require_admin_approval_paid_user2_sender_name' => $this->get_option('email_sender_name'),
                'require_admin_approval_paid_user2_sender_email' => $this->get_option('email_sender_address'),
                'require_admin_approval_paid_user2_subject' => $this->get_option('registrationadminapproval_email_subject'),
                'require_admin_approval_paid_user2_message' => $this->get_option('registrationadminapproval_email_message'),
                'incomplete_notification'                 => $this->get_option('incomplete_notification'),
                'incomplete_start'                        => $this->get_option('incomplete_notification_first'),
                'incomplete_send_every'                   => $this->get_option('incomplete_notification_add_every'),
                'incomplete_howmany'                      => $this->get_option('incomplete_notification_add'),
                'incomplete_sender_name'                  => $this->get_option('email_sender_name'),
                'incomplete_sender_email'                 => $this->get_option('email_sender_address'),
                'incomplete_subject'                      => $this->get_option('incnotification_email_subject'),
                'incomplete_message'                      => $this->get_option('incnotification_email_message'),
                'newuser_notification_admin'              => $this->get_option('notify_admin_of_newuser'),
                'newuser_admin_subject'                   => $this->get_option('newmembernotice_email_subject'),
                'newuser_admin_message'                   => $this->get_option('newmembernotice_email_message'),
                'newuser_user_sender_name'                => $this->get_option('email_sender_name'),
                'newuser_user_sender_email'               => $this->get_option('email_sender_address'),
                'newuser_user_subject'                    => $this->get_option('register_email_subject'),
                'newuser_user_message'                    => $this->get_option('register_email_body'),
                'expiring_admin_send'                     => $this->get_option('expiring_notification_days'),
                'expiring_notification_user'              => $this->get_option('expiring_notification'),
                'expiring_user_send'                      => $this->get_option('expiring_notification_days'),
                'expiring_user_sender_name'               => $this->get_option('email_sender_name'),
                'expiring_user_sender_email'              => $this->get_option('email_sender_address'),
                'expiring_user_subject'                   => $this->get_option('expiringnotification_email_subject'),
                'expiring_user_message'                   => $this->get_option('expiringnotification_email_message'),
                'cancel_sender_name'                      => $this->get_option('email_sender_name'),
                'cancel_sender_email'                     => $this->get_option('email_sender_address'),
                'cancel_subject'                          => $this->get_option('cancel_email_subject'),
                'cancel_message'                          => $this->get_option('cancel_email_message'),
                'uncancel_sender_name'                    => $this->get_option('email_sender_name'),
                'uncancel_sender_email'                   => $this->get_option('email_sender_address'),
                'uncancel_subject'                        => $this->get_option('uncancel_email_subject'),
                'uncancel_message'                        => $this->get_option('uncancel_email_message'),
            ];

            // Migrate / fix / auto-correct certain level settings.
            $reg_forms   = $this->get_option('regpage_form');
            $reg_befores = $this->get_option('regpage_before');
            $reg_afters  = $this->get_option('regpage_after');

            if (! is_array($reg_forms)) {
                $reg_forms = [];
            }
            foreach ($wpm_levels as $level_id => &$level) {
                $old_afterregredirect = ! isset($level['afterreg_redirect_type']); // 2.9 doesn't have this property.
                $old_loginredirect    = ! isset($level['login_redirect_type']); // 2.9 doesn't have this property.

                $level = array_merge($to_per_level, $level);
                $level = array_merge($this->level_defaults, $level);

                // Migrate settings for per level after reg redirect page.
                if ($old_afterregredirect && '---' !== $level['afterregredirect'] && is_null($level['custom_afterreg_redirect'])) {
                    $level['custom_afterreg_redirect'] = 1;
                    $level['afterreg_redirect_type']   = 'page';
                    $level['afterreg_page']            = $level['afterregredirect'];
                }

                // Migrate settings for per level after login redirect page.
                if ($old_loginredirect && '---' !== $level['loginredirect'] && is_null($level['custom_login_redirect'])) {
                    $level['custom_login_redirect'] = 1;
                    $level['login_redirect_type']   = 'page';
                    $level['login_page']            = $level['loginredirect'];
                }

                // Migrate expiration options.
                if (is_null($level['expire_option'])) {
                    $level['expire_option'] = (int) empty($level['noexpire']);
                } elseif (empty($level['noexpire']) && empty($level['expire_option'])) {
                    // Fix the value as noexpire and expire_option should never be both empty.
                    $level['expire_option'] = 1;
                } elseif (! empty($level['noexpire']) && ! empty($level['expire_option'])) {
                    // Fix the value as noexpire and expire_option should never be both set.
                    $level['expire_option'] = 0;
                }

                $level['noexpire'] = (int) ! empty($level['noexpire']); // Set noexpire to integer value.

                // Make sure that expiration makes sense.
                if (1 == $level['expire_option'] && empty($level['expire'])) {
                    $level['expire_option'] = 0;
                    $level['noexpire']      = 1;
                }

                // Custom registration forms.
                if (is_null($level['enable_custom_reg_form'])) {
                    if (! empty($reg_forms[ $level_id ])) {
                        $level['enable_custom_reg_form'] = 1;
                        $level['custom_reg_form']        = $reg_forms[ $level_id ];
                    } else {
                        $level['enable_custom_reg_form'] = 0;
                    }
                }
                // Html before reg form.
                if (is_null($level['regform_before'])) {
                    $level['regform_before'] = (string) wlm_arrval($reg_befores, $level_id);
                }
                // Html after reg form.
                if (is_null($level['regform_after'])) {
                    $level['regform_after'] = (string) wlm_arrval($reg_afters, $level_id);
                }
                if (is_null($level['enable_header_footer'])) {
                    $level['enable_header_footer'] = (int) (bool) wlm_trim($level['regform_before'] . $level['regform_after']);
                }
            }
            unset($level);
            $this->save_option('wpm_levels', $wpm_levels);
        }

        /**
         * Parses a menu array and "normalizes" its keys and titles
         * Note: This function calls itself to process submenus
         *
         * @param  array $items  menu items
         * @param  array $parent parent menu
         * @return array          parsed menu items
         */
        public function parse_menu($items, $parent = [])
        {
            static $first = true;
            if ($first) {
                $first = false;
                $items = apply_filters('wishlist_member_menu', $items);
            }
            $hide_legacy_features = ! $this->get_option('show_legacy_features');
            foreach ($items as $key => &$item) {
                $item['title'] = __(wlm_arrval($item, 'title'), 'wishlist-member');
                $item['name']  = __(wlm_arrval($item, 'name'), 'wishlist-member');
                if (is_array($parent) && $parent) {
                    $item['key']   = sprintf('%s%s', trailingslashit($parent['key']), $item['key']);
                    $item['title'] = sprintf('%s | %s', $parent['title'], $item['title']);
                }

                $item['legacy'] = (bool) $hide_legacy_features && (bool) apply_filters('wishlist_member_legacy_menu', ! empty($item['legacy']), $item['key']);
                if ($item['legacy']) {
                    unset($items[ $key ]);
                    continue;
                }

                if ('dashboard' !== $item['key']) { // Always allow dashboard.
                    // Remove menu item if user does not have proper capabilities.
                    if (! $this->access_control->current_user_can('wishlistmember3_' . $item['key']) || ( isset($item['wp_capability']) && ! $this->access_control->current_user_can($item['wp_capability']) )) {
                        unset($items[ $key ]);
                        continue;
                    }
                }

                if (! isset($item['sub']) || ! is_array($item['sub'])) {
                    $item['sub'] = [];
                }
                $item['sub'] = apply_filters('wishlist_member_submenu', $item['sub'], $item['key']);

                if (is_array($item['sub']) && $item['sub']) {
                    $item['sub']   = $this->parse_menu($item['sub'], $item);
                    $item['key']   = $item['sub'][0]['key'];
                    $item['title'] = $item['sub'][0]['title'];
                }
            }
            unset($item);
            return array_values($items);
        }

        /**
         * Gets menu items at the specified menu $level from ui/menu.json
         *
         * @uses WishListMember3_Core::parse_menu to parse menu items
         *
         * @param  integer $level menu level
         * @return array          menu items for the level requested
         */
        public function get_menus($level)
        {
            static $menus;
            $key = wlm_get_data()['wl'];
            if (empty($key)) {
                $key = $this->get_default_menu();
            }

            $level = $this->is_show_wizard() ? 2 : $level;

            if (empty($menus)) {
                $menus = json_decode(file_get_contents($this->plugin_dir3 . '/ui/menu.json'), true);
                $menus = $this->parse_menu($menus);
            }

            $menu = $menus;
            if (! $level) {
                return $menu;
            }

            $parts = array_pad(array_slice(explode('/', wlm_trim($key)), 0, $level), $level, '');

            $x = 0;
            while (is_string($part = array_shift($parts))) {
                foreach ($menu as $m) {
                    $key = explode('/', $m['key'])[ $x ];
                    if ($key == $part) {
                        $menu = $m['sub'];
                        break;
                    }
                }
                ++$x;
            }
            return $menu;
        }

        public function get_current_menu_item()
        {
            $wl = array_diff(explode('/', (string) wlm_get_data()['wl']), ['']);
            $wl = array_slice($wl, 0, 3);
            if (! empty($wl)) {
                $menus = $this->get_menus(count($wl) - 1);
                $key   = preg_quote('/' . array_pop($wl), '/');
            } else {
                $menus = $this->get_menus(0);
                $key   = preg_quote($this->get_default_menu(), '/');
            }
            $return = [];
            foreach ($menus as $menu) {
                $mkey = '/' == substr($menu['key'], 0, 1) ? $menu['key'] : '/' . $menu['key'];
                if (preg_match('/' . $key . '$/', $mkey)) {
                    return $menu;
                }
            }

            $menus               = $this->get_menus(0);
            $return              = wlm_arrval($menus, 0);
            wlm_get_data()['wl'] = wlm_arrval($return, 'key');
            return $return;
        }

        /**
         * Generates and returns the menu link for the specified $key and menu $level
         */
        public function get_menu_link($key, $level)
        {
            $wl = $key;

            $url = wlm_arrval($this, 'ajaxurl');
            if (empty($url)) {
                $url = false;
            }

            $remove_args = [];
            if ($url) {
                parse_str(parse_url($url, PHP_URL_QUERY), $remove_args);
            } else {
                $remove_args = wlm_get_data(true);
            }

            if (! $level && 'dashboard' === $key) {
                $return = remove_query_arg('wl', $url);
            } else {
                $return = add_query_arg('wl', $wl, $url);
            }

            unset($remove_args['wl']);
            unset($remove_args['page']);
            if ($remove_args) {
                $remove_args = array_keys($remove_args);
            }
            $remove_args[] = 'dummy';
            $return        = remove_query_arg($remove_args, $return);

            $return = explode('#', $return);
            return $return[0];
        }

        /**
         * Checks if the specified $key active for the specified menu $level
         */
        public function is_menu_active($link, $level = null)
        {
            $current = wlm_get_data()['wl'];
            parse_str($link, $new);
            $new = wlm_arrval($new, 'wl');
            if (is_int($level)) {
                $current = implode('/', array_slice(explode('/', (string) $current), 0, $level + 1));
                $new     = implode('/', array_slice(explode('/', (string) $new), 0, $level + 1));
            }
            return $current == $new;
        }

        /**
         * Generates the admin page including the sidebar and tertiary
         * level menu items.
         */
        public function admin_page()
        {
            $this->user_interface();
            include_once $this->plugin_dir3 . '/helpers/loading-screen.php';
            include_once $this->plugin_dir3 . '/helpers/toaster.php';
        }
        public function user_interface()
        {
            $ui_path = $this->plugin_dir3 . '/ui/includes';
            $ui_url  = $this->plugin_url3 . '/ui/';

            include $ui_path . '/header.php';

            echo '<div class="app-container" id="wlm3-app-container" style="position:relative">';

            // Sidebar.
            $sidebar = include $ui_path . '/sidebar.php';

            // Main Content.
            if ($sidebar) {
                echo '<div id="the-content" class="app-content main-content">';
            } else {
                echo '<div id="the-content" class="app-content">';
            }

            $this->show_admin_page();

            echo '</div>'; // The-content.

            // WordPress Footer.
            printf("<footer class='container-fluid text-right text-muted'><em><a class='small' href='%s' target='_blank'>WordPress</a> <span class='small'>(%s)</span></em></footer>", 'https://wordpress.org/', wp_kses_data(apply_filters('update_footer', '')));

            echo '</div>'; // App-container.

            include $ui_path . '/footer.php';
        }

        public function get_default_menu()
        {
            return $this->is_show_wizard() ? 'setup/getting-started' : 'dashboard';
        }

        public function is_show_wizard()
        {
            $show_wizard = false;
            if (1 != $this->get_option('LicenseStatus')) {
                $show_wizard = true;
            } else {
                $wpm_levels = $this->get_option('wpm_levels');
                $wizard_ran = $this->get_option('wizard_ran');
                if (count($wpm_levels) <= 0 && ! $wizard_ran) {
                    $show_wizard = true;
                } else {
                    if (! $wizard_ran) {
                        $this->save_option('wizard_ran', 1);
                    }
                }
            }
            return $show_wizard;
        }

        public function format_title($title)
        {
            return $this->title . ' | ' . $title;
        }

        public function show_admin_page()
        {
            // Message holder for js display_message.
            // Echo '<div class="row"><div class="col-md-12 wlm-message-holder"></div></div>';
            // Echo '<div class="alert alert-success"><i class="wlm-icons md-24">check_circle</i> Congrats!! You have successfully read this message!</div>';
            echo '<div class="alert wlm-message-holder toaster"></div>';

            include_once $this->plugin_dir3 . '/helpers/license-nag.php';

            // Third (& fourth) level menu.
            $menus = $this->get_menus(2);
            if ($menus) {
                echo "<div style='position: relative'>";
                echo '<ul id="wlm3-tabbar" class="nav nav-tabs responsive-tabs header-tab">';
                foreach ($menus as $menu) {
                    if ($menu['legacy']) {
                        continue;
                    }
                    $link   = $this->get_menu_link($menu['key'], 2);
                    $active = $this->is_menu_active($link, 2) ? ' active' : '';
                    if (count($menu['sub'])) {
                        printf('<li role="presentation" class="dropdown nav-item"><a data-toggle="dropdown" class="%s menu4 dropdown-toggle" data-title="%s" href="%s" target="_parent">%s<span class="caret"></span></a><ul class="dropdown-menu">', esc_attr($active), esc_attr($this->format_title($menu['title'])), esc_attr($link), esc_html($menu['name']));
                        foreach ($menu['sub'] as $sub) {
                            if ($menu['legacy']) {
                                continue;
                            }
                            printf('<li><a data-title="%s" href="%s#%s">%s</a></li>', esc_attr($this->format_title($sub['title'])), esc_attr($link), esc_attr($sub['key']), esc_html($sub['name']));
                        }
                        echo '</li></ul>';
                    } else {
                        printf('<li role="presentation" class="nav-item"><a class="%s nav-link" data-title="%s" href="%s" target="_parent">%s</a></li>', esc_attr($active), esc_attr($this->format_title($menu['title'])), esc_attr($link), esc_html($menu['name']));
                    }
                }
                echo '</ul>';
                echo "<ul style='position: absolute; top: 0; right: 0' class ='list-unstyled pull-right d-flex justify-content-end header-icons -with-tabs'>";
                echo "<li>
					<a href='https://wishlistmember.com/doc-categories/video-tutorials/' title='Video Tutorials' target='_blank'> <i class='wlm-icons md-24'>ondemand_video</i></a>
					</li>";
                echo "<li>
					<a href='https://wishlistmember.com/docs/' title='Help' target='_blank'> <i class='wlm-icons md-24'>find_in_page</i></a>
					</li>";
                echo "<li>
					<a href='https://my.wishlistmember.com/support/' title='Support' target='_blank'> <i class='wlm-icons md-24'>support_icon</i></a>
					</li>";
                echo '</ul>';
                echo '</div>';
            }
            $this->ajaxurl = null;

            // Body.
            echo '<div id="the-screen" class="container-fluid pb-5">';
            echo '<div class="row">';
            echo '<div class="col-md-12">';
            $wl = $this->show_screen();
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        public function get_screen()
        {
            static $wl;
            if (empty($wl)) {
                $wl = implode('/', array_diff(preg_split('/[\/#]/', (string) wlm_get_data()['wl']), ['']));
                if (empty($wl)) {
                    $wl = $this->get_default_menu();
                }
                $wl = apply_filters('wishlistmember_current_admin_screen', $wl, true);
            }
            return $wl;
        }

        /**
         * Shows the admin screen as per requested menu item
         */
        public function show_screen()
        {
            $base = $this->plugin_dir3 . '/ui/admin_screens/';
            $wl   = $this->get_screen();

            $this->show_notices($wl, $base);
            do_action('wishlistmember_pre_admin_screen', $wl, $base);
            do_action('wishlistmember_admin_screen', $wl, $base);
            do_action('wishlistmember_post_admin_screen', $wl, $base);

            return $wl;
        }

        public function show_notices($wl, $base)
        {
            // Add space at the top of dashboard.
            $class = 'dashboard' === $wl ? 'mb-3' : '';
            echo '<div class="row ' . esc_attr($class) . '">';
            echo '<div class="col-md-12">';
            do_action('wishlistmember_admin_screen_notices', $wl, $base);
            echo '</div>';
            echo '</div>';
        }

        public function to_js_vars($vars, $main)
        {
            printf("<script type='text/javascript'>\n%s = %s;\n</script>", esc_js($main), wp_json_encode($vars));
        }

        public function get_payperposts()
        {
            $ppps = call_user_func_array([$this, 'get_pay_per_posts'], func_get_args());
            $none = true;
            foreach ($ppps as &$posts) {
                if (count($posts)) {
                    $none = false;
                    foreach ($posts as &$post) {
                        $post         = (array) $post;
                        $post['name'] = $post['post_title'];
                        $post['id']   = sprintf('payperpost-%d', $post['ID']);
                    }
                    unset($post);
                }
            }
            unset($posts);
            return $none ? [] : $ppps;
        }

        public function get_incompleteregistration_count()
        {
            global $wpdb;
            $ids = $wpdb->get_col("SELECT ID FROM `{$wpdb->users}` WHERE `user_login` REGEXP 'temp_[a-f0-9]{32}' AND `user_login`=`user_email`");

            if ($ids) {
                $users = new \WP_User_Query(['include' => $ids]);
                return $users->get_total();
            } else {
                return 0;
            }
        }

        public function get_nonmembers_ids()
        {
            global $wpdb;
            return $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID` NOT IN (SELECT DISTINCT `user_id` FROM `" . esc_sql($this->table_names->userlevels) . '`)');
        }

        public function get_screen_js()
        {
            $screen_js = apply_filters('wishlistmember_admin_screen_js', '', wlm_get_data()['wl']);
            if ($screen_js) {
                return $screen_js;
            }
            $js_url = $this->plugin_url3 . '/ui/js/';
            $wl     = implode('/', array_diff(explode('/', (string) wlm_get_data()['wl']), ['']));
            if (empty($wl)) {
                $wl = $this->get_default_menu();
            }

            $wl   = apply_filters('wishlistmember_current_admin_screen', $wl, true);
            $base = $this->plugin_dir3 . '/ui/js/admin_js/';

            while (strlen($wl) > 1 && ! file_exists($base . $wl . '.js')) {
                $wl = dirname($wl);
            }
            if ($wl) {
                $wl     .= '.js';
                $js_file = $this->plugin_dir3 . '/ui/js/admin_js/' . $wl;
                if (is_file($js_file)) {
                    $js_url .= 'admin_js/' . $wl;
                    return $js_url;
                }
            }
            return '';
        }

        public function get_country_list()
        {
            static $country_list;
            if (is_null($country_list)) {
                $country_list = include_once $this->plugin_dir3 . '/helpers/countries.php';
            }
            return $country_list;
        }

        public function php2js_date_format($php_date_format)
        {
            static $php2js_dates;
            if (is_null($php2js_dates)) {
                $php2js_dates = include_once $this->plugin_dir3 . '/helpers/php2jsdates.php';
            }
            $php_parts = str_split($php_date_format);
            $js_parts  = [];

            foreach ($php_parts as $part) {
                if (end($js_parts) == '\\') {
                    $js_parts[] = $part;
                } else {
                    $js_parts[] = isset($php2js_dates[ $part ]) ? $php2js_dates[ $part ] : $part;
                }
            }
            return implode('', $js_parts);
        }

        /**
         * Generate tooltip and return or print it
         *
         * @param  string  $tooltip      Tooltip message
         * @param  string  $tooltip_size (optional) Defalt 'sm': Tooltip message size (i.e. md);
         * @param  boolean $return       (optional) Defaule false: True to return tooltip markup instead of printing it
         * @param  array   $options      (optional) Default [ 'icon' => 'help' ]: Additional options Ex. [ 'icon-class' => 'md-20', 'icon' => 'some-icon', 'style' => 'css-style' ]
         * @return string                Tooltip markup if $return is TRUE
         */
        public function tooltip($tooltip, $tooltip_size = 'sm', $return = false, $options = [])
        {
            // Set default tooltip size if empty.
            $tooltip_size = wlm_trim($tooltip_size);
            if (empty($tooltip_size)) {
                $tooltip_size = 'sm';
            }
            // Set default $options.
            $options = wp_parse_args(
                $options,
                [
                    'icon-class' => '',
                    'icon'       => 'help',
                    'style'      => '',
                ]
            );

            $text    = '<a href="#" data-size="%s" class="wlm-icons help-icon %s" title="%s" style="%s">%s</a>';
            $tooltip = sprintf($text, $tooltip_size, $options['icon-class'], htmlentities($tooltip, ENT_QUOTES), $options['style'], $options['icon']);
            if ($return) {
                return $tooltip;
            } else {
                echo wp_kses_post($tooltip);
            }
        }

        public function get_js($js)
        {
            return sprintf('%s/assets/js/%s', $this->plugin_url3, $js);
        }
        public function get_css($css)
        {
            return sprintf('%s/assets/css/%s', $this->plugin_url3, $css);
        }

        public function get_latest_membership_level($user_id)
        {
            global $wpdb;

            $values = array_keys($this->get_option('wpm_levels'));

            return $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT `a`.`level_id` FROM `' . esc_sql($this->table_names->userlevels) . '` `a` LEFT JOIN `' . esc_sql($this->table_names->userlevel_options) . '` `b` ON `a`.`ID`=`b`.`userlevel_id` WHERE `a`.`user_id`=%s AND `a`.`level_id` IN(' . implode(', ', array_fill(0, count($values), '%s')) . ') AND `b`.`option_name`="registration_date" ORDER BY `b`.`option_value` DESC, `a`.`ID` DESC LIMIT 1',
                    $user_id,
                    ...array_values($values)
                )
            );
        }

        /**
         * Get Custom Fields from Custom Registration Forms
         *
         * @return array
         */
        public function get_custom_fields()
        {
            $forms         = $this->get_custom_reg_forms();
            $skip          = ['username', 'password', 'password1', 'password2', 'email'];
            $custom_fields = [];
            foreach ($forms as $form) {
                if (empty($form->option_value['form_dissected'])) {
                    $form->option_value['form_dissected'] = wlm_dissect_custom_registration_form($form->option_value);
                    $this->save_option($form->option_name, $form->option_value);
                }
                $data = $form->option_value['form_dissected'];
                foreach ($data['fields'] as $field) {
                    if (empty($field['attributes']['name']) || in_array($field['attributes']['name'], $skip)) {
                        continue;
                    }
                    $custom_fields[ $field['attributes']['name'] ] = $field;
                }
            }
            /*
             * wishlist_member_custom_fields filter documentation:
             * https://github.com/wishlistproducts/wlm3beta/wiki/filter:-wishlist_member_custom_fields
             */
            return apply_filters('wishlist_member_custom_fields', $custom_fields);
        }

        /**
         * Get User Custom Fields with values
         *
         * @return array
         */
        public function get_user_custom_registration_fields($userid)
        {
            $custom_fields = $this->get_custom_fields();
            // $custom_fields = apply_filters( 'wishlist_member_other_fields', $custom_fields, $profileuser->ID );
            $user_custom_fields = $this->get_user_custom_fields($userid);
            foreach ($custom_fields as $key => $value) {
                if (! isset($user_custom_fields[ $value['attributes']['name'] ])) {
                    continue;
                }
                $user_value = $user_custom_fields[ $value['attributes']['name'] ];
                switch ($value['type']) {
                    case 'radio':
                        foreach ($value['options'] as $k => $v) {
                            if ($user_value == $v['value']) {
                                $custom_fields[ $key ]['options'][ $k ]['checked'] = 1;
                            } else {
                                $custom_fields[ $key ]['options'][ $k ]['checked'] = 0;
                            }
                        }
                        // Proceed to checkbox.
                    case 'checkbox':
                        $user_value = is_array($user_value) ? $user_value : [];
                        if (count($user_value) <= 0) {
                            break;
                        }
                        foreach ($value['options'] as $k => $v) {
                            if (in_array($v['value'], $user_value)) {
                                $custom_fields[ $key ]['options'][ $k ]['checked'] = 1;
                            } else {
                                $custom_fields[ $key ]['options'][ $k ]['checked'] = 0;
                            }
                        }
                        break;
                    case 'select':
                        foreach ($value['options'] as $k => $v) {
                            if (esc_html($user_value) == $v['value']) {
                                $custom_fields[ $key ]['options'][ $k ]['selected'] = 1;
                            } else {
                                $custom_fields[ $key ]['options'][ $k ]['selected'] = 0;
                            }
                        }
                        break;
                    default:
                        $custom_fields[ $key ]['attributes']['value'] = $user_value;
                }
            }

            $custom_fields = array_diff_key($custom_fields, array_flip(['firstname', 'lastname', 'email', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'company']));

            return apply_filters('wishlist_member_user_custom_fields', $custom_fields, $userid);
        }

        public function create_rollback_version($version)
        {
            if (! file_exists(WLM_ROLLBACK_PATH)) {
                mkdir(WLM_ROLLBACK_PATH, 0755, true);
            }
            touch(WLM_ROLLBACK_PATH . $version);
        }

        public function make_thankyou_url($slug)
        {
            $base = '/register/';
            if ('' == trim(get_option('permalink_structure'))) {
                $base = '/index.php' . $base;
            }
            return home_url($base . $slug);
        }

        public function get_official_versions()
        {
            $official_versions = trim(file_get_contents($this->plugin_dir3 . '/versions.txt'));
            return $official_versions ? explode("\n", trim(preg_replace('/\s+/', "\n", $official_versions))) : [];
        }

        /**
         * Allow adding of additional methods to WishList Member's core class without having to extend it
         */
        public function overload()
        {
            /**
             * Filter: wishlistmember_instance_methods
             * Expects an associative array in the following format:
             * [
             *  'method_name' => [ callable $function, boolean $deprecated ],
             * ]
             */
            $this->instance_methods = apply_filters('wishlistmember_instance_methods', []);
        }

        /**
         * Calls overloaded function added by the wishlistmember_instance_methods filter
         *
         * @param  string $method_name
         * @param  array  $arguments
         * @return mixed
         */
        public function __call($method_name, $arguments)
        {
            if (isset($this->instance_methods[ $method_name ])) {
                list( $function, $deprecated ) = array_pad($this->instance_methods[ $method_name ], 2, false);
                if ($deprecated) {
                    error_log('Deprecated method: ' . $method_name);
                }
                return call_user_func_array($function, $arguments);
            } elseif (array_key_exists($method_name, (array) $this->imported_functions)) {
                // Old way of overloading integration methods.
                // $this->imported_functions is populated ::RegisterClass()
                $arguments = (array) $arguments;
                array_unshift($arguments, $this);
                return call_user_func_array([$this->imported_functions[ $method_name ], $method_name], $arguments);
            } elseif (preg_match('/[A-Z]/', $method_name)) {
                // Check if CamelCase method has a snake_case equivalent.
                $old_method_name = $method_name;
                $new_method_name = preg_replace('/_{2,}/', '_', '_' . strtolower(preg_replace('/[A-Z]/', '_$0', $old_method_name)) . '_');

                // Replace _s_t_r_i_n_g_s_ with _strings_. This takes care of _i_d_s_, _u_r_l_, _w_p_m_, etc.
                if (preg_match_all('/_([^_]_){2,}/', $new_method_name, $matches)) {
                    foreach ($matches[0] as $match) {
                        $new_method_name = str_replace($match, '_' . str_replace('_', '', $match) . '_', $new_method_name);
                    }
                }
                // Special replacement for _i_ds_.
                $new_method_name = str_replace('_i_ds_', '_ids_', $new_method_name);

                // Special replacement for java_script.
                $new_method_name = str_replace('java_script', 'javascript', $new_method_name);

                // Special replacement for wish_list.
                $new_method_name = str_replace('wish_list', 'wishlist', $new_method_name);

                $new_method_name = preg_replace(['/^[_]+/', '/[_]+$/'], '', $new_method_name);
                if (method_exists($this, $new_method_name)) {
                    wlm_deprecated_method_error_log($old_method_name, $new_method_name);
                    return $this->$new_method_name(...array_values($arguments));
                }
            }
            error_log('Undefined WishList Member method: ' . $method_name);
        }

        /**
         * Getter
         *
         * @param  string $property Property to get.
         * @return mixed
         */
        public function &__get($property)
        {
            /**
             * Associative array of deprecated properties with deprecated property as key
             * and the new property name as value
             *
             * @var array
             */
            static $deprecated_properties = [
                'DataMigrated'        => 'data_migrated',
                'emailbroadcast'      => 'email_broadcast',
                'FormOption'          => 'form_option',
                'FormOptions'         => 'form_options',
                'isBetaTester'        => 'is_beta_tester',
                'LevelOptions'        => 'level_options',
                'Link'                => 'link',
                'loadedExtensions'    => 'loaded_extensions',
                'Marketplace'         => 'marketplace',
                'MarketplaceCheckURL' => 'marketplace_check_url',
                'MenuID'              => 'menu_id',
                'Menus'               => 'menus',
                'OrigGet'             => 'orig_get',
                'OrigPost'            => 'orig_post',
                'Permalink'           => 'permalink',
                'pluginBasename'      => 'plugin_basename',
                'plugindir'           => 'plugin_dir',
                'plugindir3'          => 'plugin_dir3',
                'PluginFile'          => 'plugin_file',
                'PluginOptionName'    => 'plugin_option_name',
                'pluginPath'          => 'plugin_path',
                'PluginSlug'          => 'plugin_slug',
                'pluginURL'           => 'plugin_url',
                'pluginURL3'          => 'plugin_url3',
                'ProductSKU'          => 'product_sku',
                'Table'               => 'table',
                'TablePrefix'         => 'table_prefix',
                'Tables'              => 'table_names',
                'taxonomyIds'         => 'taxonomy_ids',
                'Title'               => 'title',
                'Version'             => 'version',
                'WPVersion'           => 'wp_version',
                'WPWLCheckResponse'   => 'wpwl_check_response',
            ];

            if (isset($deprecated_properties[ $property ])) {
                wlm_deprecated_property_error_log($property, $deprecated_properties[ $property ]);
                $property = $deprecated_properties[ $property ];
            }

            return $this->$property;
        }
    }
}
