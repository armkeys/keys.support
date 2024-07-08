<?php

/**
 * Check for collision with another active WishList Member install.
 * We do not want to cause a conflict with ourselves.
 *
 * Prevent further code execution if another instance of
 * WishList Member is already running.
 *
 * This file must return a boolean value.
 *
 * @return boolean
 *
 * @package WishList Member
 */

if (class_exists('WishListMember') || class_exists('WishListMember3')) {
    add_action(
        'admin_notices',
        function () {
            printf(
                '<div class="notice notice-error"><p>%1$s</p><p><a href="%3$s">%2$s</a></p></div>',
                esc_html__('Multiple versions of WishList Member are currently running.', 'wishlist-member'),
                esc_html__('Click here to deactivate the others.', 'wishlist-member'),
                esc_url(admin_url('plugins.php'))
            );
        }
    );
    return true;
}
return false;
