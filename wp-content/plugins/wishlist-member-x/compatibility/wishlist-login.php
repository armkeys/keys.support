<?php

/**
 * WishList Login Compatibility
 *
 * @package WishListMember/Compatibility
 */

/*
 * Fix for Wishlist Login 2.0's Post Login functionality
 */
add_filter(
    'wishlistmember_process_protection',
    function ($redirect) {
        global $WishListLogin2Instance;
        if (is_object($WishListLogin2Instance) && method_exists($WishListLogin2Instance, 'show_login')) {
            if ($WishListLogin2Instance->show_login() && $WishListLogin2Instance->do_login_box) {
                return 'STOP';
            }
        }
        return $redirect;
    }
);
