<?php

/**
 * Includes
 *
 * @package WishList Member
 */

    // Includes.
    require_once WLM_PLUGIN_DIR . '/includes/functions.php';
    require_once WLM_PLUGIN_DIR . '/includes/autoloader.php';
    require_once WLM_PLUGIN_DIR . '/includes/error-reporting.php';

    // Legacy code.
    require_once WLM_PLUGIN_DIR . '/legacy/core/Class.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/WLMDB.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/WishListWidget.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/WishListAcl.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/WishlistDebug.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/api-helper/functions.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/TinyMCEPlugin.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/WishListXhr.php';
    require_once WLM_PLUGIN_DIR . '/legacy/core/NavMenu.php';

    // Classes.
    require_once WLM_PLUGIN_DIR . '/classes/wishlist-member3-core.php';
    require_once WLM_PLUGIN_DIR . '/classes/wishlist-member3-actions.php';
    require_once WLM_PLUGIN_DIR . '/classes/wishlist-member3-hooks.php';
    require_once WLM_PLUGIN_DIR . '/classes/class-wishlistmember3.php';

    require_once WLM_PLUGIN_DIR . '/compatibility/wlm3-alias.php';
    require_once WLM_PLUGIN_DIR . '/compatibility/wlm3-constants.php';
