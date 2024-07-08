<?php

/**
 * Integration Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Integration Methods trait
*/
trait Integration_Methods
{
    /**
     * Extensions
     *
     * @var array
     */
    public $extensions;

    /**
     * Loaded extensions
     *
     * @var array
     */
    public $loaded_extensions = [];

    /**
     * Shopping Cart integration URIs
     *
     * @var array
     */
    public $sc_integration_uris = [];

    /**
     * Webinar integration information
     *
     * @var array
     */
    public $webinar_integrations = [];

    /**
     * Autoresponder integration information
     *
     * @var array
     */
    public $ar_integration_methods = [];

    /**
     * Subscribe to Autoresponder.
     *
     * @param string  $fname    First name.
     * @param string  $lname    Last name.
     * @param string  $email    Email address.
     * @param integer $level_id Level ID.
     */
    public function ar_subscribe($fname, $lname, $email, $level_id)
    {
        // Autoresponder subscription.
        if ($this->get_option('privacy_enable_consent_to_market') && $this->get_option('privacy_consent_affects_autoresponder')) {
            $user = get_user_by('email', $email);
            if (false !== $user && ! $this->Get_UserMeta($user->ID, 'wlm_consent_to_market')) {
                return; // No consent to market by email.
            }
        }

        $this->sending_mail = true; // We add this to trigger our hook.
        $this->ar_sender    = [
            'name'       => "{$fname} {$lname}",
            'email'      => "{$email}",
            'first_name' => $fname,
            'last_name'  => $lname,
        ];
        $ars                = $this->get_option('Autoresponders');
        $arps               = (array) $this->get_option('active_email_integrations');

        if (! empty($ars['ARProvider'])) {
            $arps[] = (string) $ars['ARProvider'];
            $arps   = array_unique($arps);
        }

        foreach ($arps as $arp) { // Go through all active integrations.
            if (empty($ars[ $arp ])) {
                continue;
            }
            $ar_settings = $ars[ $arp ];
            // Retrieve the method to call.
            $ar_integration_info = wlm_arrval($this->ar_integration_methods, $arp);
            // And call it.
            if ($ar_integration_info) {
                if (! class_exists($ar_integration_info['class'])) {
                    include_once $ar_integration_info['file'];
                    $this->RegisterClass($ar_integration_info['class']);
                }
                call_user_func_array([wishlistmember_instance(), $ar_integration_info['method']], [$ar_settings, $level_id, $email, false]);
            }
        }

        do_action_deprecated('wishlistmember3_autoresponder_subscribe', [$email, $level_id], '3.10', 'wishlistmember_autoresponder_subscribe');
        do_action('wishlistmember_autoresponder_subscribe', $email, $level_id);

        $this->ar_sender    = '';
        $this->sending_mail = false;
    }

    /**
     * Unsubscribe from Autoresponder
     *
     * @param string  $fname    First name.
     * @param string  $lname    Last name.
     * @param string  $email    Email address.
     * @param integer $level_id Level ID.
     */
    public function ar_unsubscribe($fname, $lname, $email, $level_id)
    {
        $this->sending_mail = true; // We add this to trigger our hook.
        $this->ar_sender    = [
            'name'  => "{$fname} {$lname}",
            'email' => "{$email}",
        ];
        $ars                = $this->get_option('Autoresponders');
        $arps               = (array) $this->get_option('active_email_integrations');

        if (! empty($ars['ARProvider'])) {
            $arps[] = (string) $ars['ARProvider'];
            $arps   = array_unique($arps);
        }

        foreach ($arps as $arp) { // Go through all active integrations.
            if (empty($ars[ $arp ])) {
                continue;
            }
            $ar_settings = $ars[ $arp ];
            // Retrieve the method to call.
            $ar_integration_info = $this->ar_integration_methods[ $arp ];
            // And call it.
            if ($ar_integration_info) {
                if (! class_exists($ar_integration_info['class'])) {
                    include_once $ar_integration_info['file'];
                    $this->RegisterClass($ar_integration_info['class']);
                }
                call_user_func_array([wishlistmember_instance(), $ar_integration_info['method']], [$ar_settings, $level_id, $email, true]);
            }
        }

        do_action_deprecated('wishlistmember3_autoresponder_unsubscribe', [$email, $level_id], '3.10', 'wishlistmember_autoresponder_unsubscribe');
        do_action('wishlistmember_autoresponder_unsubscribe', $email, $level_id);

        $this->ar_sender    = '';
        $this->sending_mail = false;
    }

    /**
     * Subscribe to Webinar
     *
     * @param string  $fname    First name.
     * @param string  $lname    Last name.
     * @param string  $email    Email address.
     * @param integer $level_id Level ID.
     */
    public function webinar_subscribe($fname, $lname, $email, $level_id)
    {
        $data = [
            'first_name' => $fname,
            'last_name'  => $lname,
            'email'      => $email,
            'level'      => $level_id,
        ];
        do_action('wishlistmember_webinar_subscribe', $data);
    }

    /**
     * Registers a WishList Member Extensions
     *
     * @param string $name        Extension name.
     * @param string $url         Extension Website.
     * @param string $version     Extension version.
     * @param string $description Extension description.
     * @param string $author      Extension's author.
     * @param string $authorurl   Extension author's URL.
     * @param string $file        Extension's filename.
     */
    public function register_extension($name, $url, $version, $description, $author, $authorurl, $file)
    {
        $file = basename($file);
        if ($file) {
            $this->loaded_extensions[ $file ] = [
                'Name'        => $name,
                'URL'         => $url,
                'Version'     => $version,
                'Description' => $description,
                'Author'      => $author,
                'AuthorURL'   => $authorurl,
                'File'        => $file,
            ];
        }
    }

    /**
     * Unregisters an extension
     *
     * @param string $file Extension's filename.
     */
    public function unregister_extension($file)
    {
        unset($this->loaded_extensions[ $file ]);
    }

    /**
     * Returns an array of loaded extensions
     *
     * @return array Loaded extensions
     */
    public function get_registered_extensions()
    {
        return $this->loaded_extensions;
    }

    /**
     * Loads the init file for the integration
     *
     * @param string $file File name.
     */
    public function load_init_file($file)
    {
        $init_file = str_replace('.php', '.init.php', $file);
        if (basename($init_file) === $init_file) {
            $init_file = $this->plugin_dir . '/lib/' . $init_file;
        }
        if (file_exists($init_file)) {
            include_once $init_file;
        }
    }

    /**
     * Register a Payment Provider Integration Function
     *
     * @param string $uri        URI Prefix.
     * @param string $filename   File name.
     * @param string $classname  Class name.
     * @param string $methodname Method name.
     */
    public function register_sc_integration($uri, $filename, $classname, $methodname)
    {
        if (file_exists($this->plugin_dir . '/lib/' . $filename)) {
            $this->sc_integration_uris[ $uri ] = [
                'file'   => $filename,
                'class'  => $classname,
                'method' => $methodname,
            ];
        }
    }

    /**
     * Register an Autoresponder Integration Function
     *
     * @param string $ar_option  Autoresponder Option Name.
     * @param string $filename   File name.
     * @param string $classname  Class name.
     * @param string $methodname Method name.
     */
    public function register_ar_integration($ar_option, $filename, $classname, $methodname)
    {
        if ($classname && $methodname) {
            $this->ar_integration_methods[ $ar_option ] = [
                'file'   => $filename,
                'class'  => $classname,
                'method' => $methodname,
            ];
        }
    }

    /**
     * Register an Webinar Integration Function
     *
     * @param string $webinar   Autoresponder Option Name.
     * @param string $filename  File name.
     * @param string $classname Class name.
     */
    public function register_webinar_integration($webinar, $filename, $classname)
    {
        $this->webinar_integrations[ $webinar ] = [
            'file'  => $filename,
            'class' => $classname,
        ];
    }

    /**
     * This function returns a 200 OK Response Header and
     * Displays the text WishList Member and a link to the WP homepage
     *
     * @param string $scuri Shopping cart URI.
     */
    public function cart_integration_terminate($scuri = '')
    {
        global $wlm_no_cartintegrationterminate;
        if (! empty($wlm_no_cartintegrationterminate)) {
            return;
        }

        if ('POST' === wlm_server_data()['REQUEST_METHOD']) {
            exit;
        }

        $url = add_query_arg('sp', $scuri ? 'invalid_registration2' : 'invalid_registration1', $this->magic_page());

        // Http redirect.
        wp_safe_redirect($url);
        // Meta redirect.
        printf('<meta http-equiv="refresh" content="0;URL=\'%s\'" />', esc_url($url));
        // Javascript redirect.
        printf('<script type="text/javascript">document.location = "%s";</script>', esc_url($url));
        exit;
    }

    /**
     * Get/set active status of an "Other Provider" integration.
     *
     * @param  string  $integration_file Integration file.
     * @param  boolean $status           Active status.
     * @return boolean|null                Active status. Null if not found.
     */
    public function integration_active($integration_file, $status = null)
    {
        $integrations = (array) $this->get_option('ActiveIntegrations');
        if (! is_null($status)) {
            $integrations[ $integration_file ] = (bool) $status;
            $this->save_option('ActiveIntegrations', $integrations);
        }

        if (isset($integrations[ $integration_file ])) {
            return (bool) $integrations[ $integration_file ];
        } else {
            return null;
        }
    }

    /**
     * Load integration shortcodes into the TinyMCE editor.
     */
    public function integration_shortcodes()
    {
        // Register tinymce plugin for integrations.
        if ($GLOBALS['WLMTinyMCEPluginInstanceOnly'] && count($this->integration_shortcodes) > 0) {
            $GLOBALS['WLMTinyMCEPluginInstanceOnly']->RegisterShortcodes('Integrations', [], [], 0, null, $this->integration_shortcodes);
        }
    }

    /**
     * Display integration errors.
     */
    public function integration_errors()
    {
        if (! empty($this->integration_errors)) {
            $active_shiopping_carts = (array) $this->get_option('ActiveShoppingCarts');
            foreach ((array) $this->integration_errors as $key => $error) {
                if (in_array($key, $active_shiopping_carts, true)) {
                    $show_error = true;
                    if ('WishListMember' === wlm_get_data()['page'] && 'integration' === wlm_get_data()['wl']) {
                        $show_error = false;
                    } else {
                        if (! empty($this->active_integration_indicators[ $key ]) && is_array($this->active_integration_indicators[ $key ])) {
                            foreach ($this->active_integration_indicators[ $key ] as $option) {
                                $show_error = $show_error & ( (bool) $this->get_option($option) );
                            }
                        }
                    }
                    if ($show_error) {
                        printf('<div class="error">%s</div>', wp_kses($error, 'data'));
                    }
                }
            }
        }
    }

    /**
     * Load payment providers
     */
    public function load_integrations_payment_providers()
    {
        // Payment providers.
        $providers = require WLM_PLUGIN_DIR . '/legacy/lib/integration.shoppingcarts.php';
        // Active providers.
        $active = (array) $this->get_option('ActiveShoppingCarts');
        // Load providers.
        foreach ($providers as $i_file => $i_data) {
            if (! empty($i_data['php_minimum'])) {
                if (version_compare(phpversion(), $i_data['php_minimum']) < 0) {
                    if (! empty($i_data['php_minimum_msg'])) {
                        $this->integration_errors[ $i_file ] = $i_data['php_minimum_msg'];
                    }
                    if (! empty($i_data['active_indicators'])) {
                        $this->active_integration_indicators[ $i_file ] = $i_data['active_indicators'];
                    }
                    continue;
                }
            }
            if (in_array($i_file, $active, true)) {
                if (empty($i_data['handler'])) {
                    if (file_exists($this->plugin_dir . '/lib/' . $i_file)) {
                        $this->load_init_file($i_file);
                        $this->register_sc_integration($i_data['optionname'], $i_file, $i_data['classname'], $i_data['methodname']);
                    }
                } else {
                    $handler = sprintf('%s/integrations/payments/%s/handler.php', $this->plugin_dir3, $i_data['name']);
                    if (file_exists($handler)) {
                        require_once $handler;
                    }
                }
            }
        }
    }

    /**
     * Load email providers
     */
    public function load_integrations_email_providers()
    {

        // Pre v3.0 active email providers.
        $providers = wlm_arrval($this->get_option('Autoresponders'), 'ARProvider');
        if (! is_array($providers)) {
            $providers = [];
        }

        // 3.0 active email providers.
        $active_email_integrations = $this->get_option('active_email_integrations');
        if (! is_array($active_email_integrations)) {
            $active_email_integrations = [];
        }
        $providers = array_merge($providers, $active_email_integrations);

        foreach ($providers as $provider) {
            if (! $provider) {
                continue;
            }
            require_once $this->plugin_dir . '/lib/integration.autoresponders.php';
            foreach ($wishlist_member_autoresponders as $i_file => $i_data) {
                // Only load the currently used autoresponder init file.
                if ($provider === $i_data['optionname']) {
                    if (! empty($i_data['handler'])) {
                        $i_file = sprintf('%s/integrations/emails/%s/handler.php', $this->plugin_dir3, $i_file);
                    } else {
                        $i_file = sprintf('%s/legacy/lib/%s', $this->plugin_dir3, $i_file);
                    }
                    if (file_exists($i_file)) {
                        $this->load_init_file($i_file);
                        $this->register_ar_integration(wlm_arrval($i_data, 'optionname'), $i_file, wlm_arrval($i_data, 'classname'), wlm_arrval($i_data, 'methodname'));
                    }
                }
            }
        }
    }

    /**
     * Load othe providers
     */
    public function load_integrations_other_providers()
    {
        $providers = (array) $this->get_option('active_other_integrations');
        foreach ($providers as $provider) {
            $i_files = [
                sprintf('%s/lib/integration.other.%s.php', $this->plugin_dir, $provider),
                sprintf('%s/lib/integration.webinar.%s.php', $this->plugin_dir, $provider),
                sprintf('%s/integrations/others/%s/handler.php', $this->plugin_dir3, $provider),
            ];
            foreach ($i_files as $i_file) {
                if (file_exists($i_file)) {
                    include_once $i_file;
                }
            }
        }
    }

    /**
     * Load legacy extensions.
     */
    public function load_legacy_extensions()
    {
        // Support for old extensions folder.
        $extensions = glob(WLM_PLUGIN_DIR . '/legacy/extensions/*.php');
        foreach ((array) $extensions as $k => $ex) {
            if ('api.php' === basename($ex)) {
                unset($extensions[ $k ]);
            }
        }
        sort($extensions);
        $this->extensions = $extensions;
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('admin_footer', [$wlm, 'integration_errors']);
        add_action('wishlistmember_load_integrations', [$wlm, 'load_integrations_email_providers']);
        add_action('wishlistmember_load_integrations', [$wlm, 'load_integrations_other_providers']);
        add_action('wishlistmember_load_integrations', [$wlm, 'load_integrations_payment_providers']);
        add_action('wishlistmember_load_integrations', [$wlm, 'load_legacy_extensions']);
    }
);
