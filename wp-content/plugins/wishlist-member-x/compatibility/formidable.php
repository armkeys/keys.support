<?php

/**
 * Compatibility for Formidable plugin
 *
 * @package WishListMember/Compatibility
 */

add_filter(
    'wishlistmember_pre_add_attachment',
    function ($obj) {
        if (0 === strpos($obj->guid, trailingslashit(wp_upload_dir()['baseurl']) . 'formidable/', 0)) {
            return false;
        }
        return $obj;
    }
);
