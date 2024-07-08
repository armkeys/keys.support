<?php

/**
 * This file bootstraps the growth tools admin screen.
 *
 * Growth tools allow admins to easily install and activate plugins that can help them grow there membership site.
 *
 * @package WishListMember/Features
 */

// Do not include Growth Tools if not PHP 7.4 or greater.
if (version_compare(PHP_VERSION, 7.4, '<')) {
    return false;
}
// Autoload.
require_once WLM_PLUGIN_DIR . '/vendor-prefixed/autoload.php';

if (class_exists('\WishListMember\Caseproof\GrowthTools\App')) {
    add_action(
        'admin_enqueue_scripts',
        function () {
            $screen = get_current_screen();
            if ('wishlist-member_page_wishlist-member-growth-tools' === $screen->id) {
                wp_enqueue_style('wishlistmember3-main-styles', wishlistmember_instance()->plugin_url3 . '/ui/stylesheets/main.css', [], WLM_PLUGIN_VERSION);
            }
        }
    );
    $config = new \WishListMember\Caseproof\GrowthTools\Config(
        [
            'parentMenuSlug'   => 'WishListMember',
            'instanceId'       => 'wishlist-member',
            'menuSlug'         => 'wishlist-member-growth-tools',
            'buttonCSSClasses' => ['btn', '-primary'],
        ]
    );
    new \WishListMember\Caseproof\GrowthTools\App($config);
}
