<?php

/**
 * Stripe Integration Hooks
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

add_action('admin_init', '\WishListMember\PaymentProviders\Stripe\Authenticator::clear_connection_data');
add_action('init', '\WishListMember\PaymentProviders\Stripe\Authenticator::process_connect');
add_action('init', '\WishListMember\PaymentProviders\Stripe\Authenticator::process_disconnect');
add_action('admin_notices', '\WishListMember\PaymentProviders\Stripe\Authenticator::wlm_stripe_disconnect_notice');
add_action('admin_notices', '\WishListMember\PaymentProviders\Stripe\Connect::upgrade_notice');
add_action('wishlistmember_pre_admin_screen', '\WishListMember\PaymentProviders\Stripe\Connect::upgrade_notice');
add_action('wp_ajax_wlm_stripe_connect_update_creds', '\WishListMember\PaymentProviders\Stripe\Connect::process_update_creds');
add_action('wp_ajax_wlm_stripe_connect_refresh', '\WishListMember\PaymentProviders\Stripe\Connect::process_refresh_tokens');
add_action('wp_ajax_wlm_stripe_connect_disconnect', '\WishListMember\PaymentProviders\Stripe\Connect::process_disconnect');
add_filter('site_status_tests', '\WishListMember\PaymentProviders\Stripe\Connect::add_site_health_test');
add_action('wp_ajax_wlm_stripe_connect_save_settings', '\WishListMember\PaymentProviders\Stripe\Connect::stripe_connect_save_settings');
add_action(
    'admin_footer',
    function () {
        if (isset($_GET['wlm_strip_connect_modal'])) {
            echo "<script>jQuery(document).ready(function($) { setTimeout(function(){ jQuery('#stripe_settings button').trigger('click'); }, 1000);  });</script>";
        }
    }
);

add_action(
    'wishlist_member_menu',
    function ($menu) {
        $menu[] = [
            'key'   => 'account_login',
            'name'  => esc_html__('Account Login', 'wishlist-member'),
            'title' => esc_html__('Account Login', 'wishlist-member'),
            'icon'  => '',
            'sub'   => [],
        ];

        return $menu;
    },
    PHP_INT_MAX
);

add_filter(
    'submenu_file',
    function ($submenu, $parent) {
        remove_submenu_page(wishlistmember_instance()->menu_id, 'WishListMember&amp;wl=account_login');
        return $submenu;
    },
    10,
    2
);


add_action('init', '\WishListMember\PaymentProviders\Stripe\Connect::parse_standalone_request', 10);
add_action('init', '\WishListMember\PaymentProviders\Stripe\Connect::maybe_swap_stripe_keys', 1);
