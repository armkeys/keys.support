<?php

/**
 * Compatibility with Content Aware Sidebars
 *
 * @package WishListMember/Compatibility
 */

add_filter(
    'wishlistmember_excluded_post_types',
    function ($excluded_post_types) {
        if (true || class_exists('CAS_App')) {
            $excluded_post_types[] = 'sidebar';
        }
        return $excluded_post_types;
    }
);
