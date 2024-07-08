<?php

/**
 * Compatibility for Kali Forms issue where the builder js is
 * conflicting with WishList Member.
 *
 * @package WishListMember/Compatibility
 */

add_filter(
    'wishlistmember_disable_postpage_options',
    function ($x, $post) {
        if ('kaliforms_forms' === $post->post_type) {
            return true;
        }
        return $x;
    },
    10,
    2
);
