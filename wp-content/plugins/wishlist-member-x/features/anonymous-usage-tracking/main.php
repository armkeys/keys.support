<?php

/**
 * Anonymous usage tracking
 *
 * @package WishListMember
 */

namespace WishListMember\Features\Anonymous_Usage_Tracking;

defined('ABSPATH') || die();

$anonymous_usage_tracking = wishlistmember_instance()->get_option('anonymous_usage_tracking');
if (false === $anonymous_usage_tracking) {
    add_action('admin_notices', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin');
    add_action('wishlistmember_pre_admin_screen', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin');
    /**
     * Show opt-in message for anonymous data tracking
     *
     * @wp-hook admin_notices
     * @wp-hook wishlistmember_pre_admin_screen
     */
    function show_anonymous_usage_tracking_optin()
    {
        if (! apply_filters('wishlistmember_show_anonymous_usage_tracking_optin', true)) {
            return;
        }
        $base_url = wlm_or(wp_get_referer(), wlm_arrval($_SERVER, 'REQUEST_URI'));
        $url_yes  = wp_nonce_url(add_query_arg('wlm-anonymous-usage-tracking', 'yes', $base_url), 'wlm-anonymous-usage-tracking-yes');
        $url_no   = wp_nonce_url(add_query_arg('wlm-anonymous-usage-tracking', 'no', $base_url), 'wlm-anonymous-usage-tracking-no');
        echo wp_kses_post(
            sprintf(
                '<div class="mx-0 my-3 notice notice-info"><p><strong>%s</strong></p><p>%s</p><p><a href="%s" class="button button-secondary" style="float: right; color: #bbb; background: none; border: none">%s</a> <a href="%s" class="button button-primary">%s</a></p></div>',
                __('Build a better WishList Member', 'wishlist-member '),
                __('Get improved features by sharing non-sensitive usage data that shows us how WishList Member is used. No personal data is tracked or stored.', 'wishlist-member'),
                $url_no,
                __('No, thanks', 'wishlist-member'),
                $url_yes,
                __('Okay', 'wishlist-member')
            )
        );
    }

    add_action('admin_init', __NAMESPACE__ . '\save_anonymous_data_setting');
    /**
     * Save anonymous usage tracking from opt-in message
     *
     * @wp-hook admin_init
     */
    function save_anonymous_data_setting()
    {
        $setting = wlm_get_data()['wlm-anonymous-usage-tracking'];
        if (! in_array($setting, ['yes', 'no'], true)) {
            return;
        }
        check_admin_referer('wlm-anonymous-usage-tracking-' . $setting);
        remove_action('admin_notices', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin');
        remove_action('wishlistmember_pre_admin_screen', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin');
        wishlistmember_instance()->save_option('anonymous_usage_tracking', $setting);
    }
}

add_action('wishlistmember_post_admin_screen', __NAMESPACE__ . '\show_settings_screen', 10, 2);
/**
 * Insert settings under Advanced Settings > Miscellaneous
 *
 * @param string $wl   Screen being displayed.
 * @param string $base Base path to ui/admin_screens/.
 */
function show_settings_screen($wl, $base)
{
    if ('advanced_settings/miscellaneous' !== $wl) {
        return;
    }
    require __DIR__ . '/settings-view.php';
}

add_action('wishlistmember_license_key_validated', __NAMESPACE__ . '\send_anonymous_usage_tracking');
/**
 * Send anonymous usage tracking to WishList Products servers
 *
 * @param string $license_key License key.
 */
function send_anonymous_usage_tracking($license_key)
{
    if ('yes' !== wishlistmember_instance()->get_option('anonymous_usage_tracking')) {
        // Opted out. end.
        return;
    }

    $levels            = wishlistmember_instance()->get_option('wpm_levels');
    $payperposts       = wishlistmember_instance()->get_pay_per_posts();
    $email_providers   = wishlistmember_instance()->get_option('active_email_integrations');
    $other_providers   = wishlistmember_instance()->get_option('active_other_integrations');
    $payment_providers = wishlistmember_instance()->get_option('ActiveShoppingCarts');
    if (is_array($payment_providers)) {
        $payment_providers = array_map(
            /**
             * Cleanup payment provider names
             *
             * @return string
             */
            function ($value) {
                return str_replace(['integration.shoppingcart.', '.php'], '', $value);
            },
            $payment_providers
        );
    } else {
        $payment_providers = [];
    }

    $sys_info = new \WishListMember\System_Info();
    /**
     * Callback for array_map to simplify $sys_info values
     *
     * @return string
     */
    $simplify = function ($value) {
        return $value['value'];
    };

    // Wizard Data.
    $wizard_data = [
        'integration' => [
            // Payment integration.
            'payment' => wishlistmember_instance()->get_option('wizard/integration/payment/configure'),
            // Email integration.
            'email'   => wishlistmember_instance()->get_option('wizard/integration/email/configure'),
        ],
        'pages'       => [
            // Free registration page.
            'free_registration' => array_column(
                get_posts(
                    [
                        'post_type'   => 'page',
                        'meta_key'    => 'wishlist-member/wizard/pages/registration/free',
                        'post_status' => 'all',
                    ]
                ),
                'post_status',
                'ID'
            ),
            // Paid registration page.
            'paid_registration' => array_column(
                get_posts(
                    [
                        'post_type'   => 'page',
                        'meta_key'    => 'wishlist-member/wizard/pages/registration/paid',
                        'post_status' => 'all',
                    ]
                ),
                'post_status',
                'ID'
            ),
        ],
    ];

    $onboarding = (int) wishlistmember_instance()->get_option('wizard/membership-pages/onboarding/configure');
    $dashboard  = (int) wishlistmember_instance()->get_option('wizard/membership-pages/dashboard/configure');

    $wizard_data['pages'] += [
        // Onboarding page.
        'onboarding' => [
            'configured' => $onboarding,
            'used'       => 'internal' === wishlistmember_instance()->get_option('after_login_type') && (int) wishlistmember_instance()->get_option('after_login_internal') === $onboarding,
        ],
        // Dashboard page.
        'dashboard'  => [
            'configured' => $dashboard,
            'used'       => 'internal' === wishlistmember_instance()->get_option('after_registration_type') && (int) wishlistmember_instance()->get_option('after_registration_internal') === $dashboard,
        ],
    ];

    // Checklist Data.
    $checklist_data = [
        'done'     => [],
        'archived' => [],
    ];
    array_walk(
        $GLOBALS['wpdb']->get_results('SELECT `option_name`,`option_value` FROM `' . esc_sql(wishlistmember_instance()->options_table) . '` WHERE `option_name` REGEXP "^checklist/(done|archived)/"'),
        function ($item, $key) use (&$checklist_data) {
            $parts                                    = explode('/', $item->option_name, 3);
            $checklist_data[ $parts[1] ][ $parts[2] ] = (bool) $item->option_value;
        }
    );
    $checklist_data['archived'] = array_filter($checklist_data['archived']);

    $data = [
        'key'                   => $license_key, // used only for validation and not stored.
        'sku'                   => WLM_SKU,
        'version'               => WLM_PLUGIN_VERSION,
        'timestamp'             => time(),
        'number_of_levels'      => is_array($levels) ? count($levels) : 0,
        'number_of_payperposts' => is_array($payperposts) ? count($payperposts) : 0,
        'payment_providers'     => $payment_providers,
        'email_providers'       => is_array($email_providers) ? $email_providers : [],
        'other_providers'       => is_array($other_providers) ? $other_providers : [],
        'active_plugins'        => array_map($simplify, $sys_info->info['plugins']),
        'active_theme'          => array_map($simplify, $sys_info->info['theme']),
        'server'                => array_map($simplify, $sys_info->info['server']),
        'wordpress'             => array_map($simplify, $sys_info->info['wordpress']),
        'login_styling_enabled' => (int) wishlistmember_instance()->get_option('login_styling_enable_custom_template'),
        'wizard'                => $wizard_data,
        'checklist'             => $checklist_data,
    ];

    unset($data['wordpress']['admin_email']);
    unset($data['wordpress']['home_url']);

    // Send data to WishList Products.
    wp_remote_post(
        'https://api.wishlistproducts.com/wlm_anon_usage_tracking.php',
        [
            'body'     => $data,
            'blocking' => false,
        ]
    );
}
