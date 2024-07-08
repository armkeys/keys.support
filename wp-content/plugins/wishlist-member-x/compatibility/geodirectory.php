<?php

/**
 * Compatibility for the Page set as Search Page for Geodirectory where protection is not working.
 * GeoDirectory Version 2.1.0.15
 *
 * @package WishListMember/Compatibility
 */

add_filter(
    'wishlistmember_content_not_protected',
    function ($x) {
        global $geodirectory;
        global $wp_query;
        if (! isset($geodirectory) || ! isset($geodirectory->settings)) {
            return $x;
        }
        $active_plugins = wlm_get_active_plugins();
        if (is_search() && in_array('GeoDirectory', $active_plugins, true) && wishlistmember_instance()->protect(get_queried_object_id())) {
            return ! ( get_queried_object_id() === (int) $geodirectory->settings['page_search'] );
        }
        if (is_archive() && wishlistmember_instance()->protect($geodirectory->settings['page_archive'])) {
            return ! ( 'gd_families' === $wp_query->post->post_type );
        }
        return $x;
    }
);
