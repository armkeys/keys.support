<?php

/**
 * Addons Manager feature
 *
 * @package WishListMember/Features
 */

namespace WishListMember\Features\Addons_Manager;

if (! defined('WLM_PRODUCT_SLUG') || 'wishlist-member-product-slug' === WLM_PRODUCT_SLUG) {
    return;
}

if (empty(wlm_trim(wishlistmember_instance()->get_option('LicenseKey')))) {
    return;
}

require_once __DIR__ . '/includes/class-addons.php';
require_once __DIR__ . '/includes/hooks-settings.php';
