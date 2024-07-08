<?php

/**
 * WooCommerce Compatibility
 *
 * @package WishListMember/Compatibility
 */

/*
 * Let's check protection for pages set as the Shop Page in WooCommerce.
 */
add_filter(
    'wishlistmember_process_protection',
    function ($redirect) {
        $is_woocommerce_shop_page = false;
        if (class_exists('WooCommerce') && function_exists('is_shop') && is_shop()) {
            $is_woocommerce_shop_page = true;

            $woocommerce_shop_page_id = get_option('woocommerce_shop_page_id');
            if (! wishlistmember_instance()->protect($woocommerce_shop_page_id)) {
                return 'STOP';
            }

            if (! is_user_logged_in()) {
                return 'NOACCESS';
            }
        }
        return $redirect;
    }
);

/**
 * Exclude some of WC's custom post types
 */
add_filter(
    'wishlistmember_excluded_post_types',
    function ($excluded_post_types) {
        // WooCommerce.
        if (true || class_exists('WooCommerce')) {
            $excluded_post_types[] = 'shop_coupon';
            $excluded_post_types[] = 'shop_order_refund';
            $excluded_post_types[] = 'shop_order';
        }
        return $excluded_post_types;
    }
);
