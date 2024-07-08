<?php

/**
 * WishList Member
 *
 * @package WishListMember
 */

/**
 * Plugin meta data.
 *
 * @var string
 */
$wlm_plugin_data = <<<plugin_data
Plugin Name: WishList Member&trade; X
Plugin URI: https://wishlistmember.com/
Description: <strong>WishList Member&trade; X</strong> (Extended Edition) is the most comprehensive membership plugin for WordPress users. It allows you to create multiple membership levels, protect desired content and much more. For more WordPress tools please visit the <a href="http://wishlistproducts.com/blog" target="_blank">WishList Products Blog</a>. Requires at least WordPress 6.0+ and PHP 7.4+.

Version: 3.26.9
Requires at least: 6.0
Requires PHP: 7.4

Author: WishList Products
Author URI: https://wishlistproducts.com/

Text Domain: wishlist-member
Domain Path: /lang

Product Slug: wishlist-member-x

License: GPLv2
plugin_data;

defined('ABSPATH') || die();

if (require_once 'includes/collision.php') {
    // Collision detected.
    return;
}

// Constants.
require_once 'includes/constants.php';

/**
 * Plugin base directory
 *
 * @var string
 */
define('WLM_PLUGIN_DIR', __DIR__);

/**
 * Plugin file
 *
 * @var string
 */
define('WLM_PLUGIN_FILE', __FILE__);

if (! require_once 'versioncheck.php') {
    // Version requirements not met.
    return;
}

// Load includes.
require_once 'includes/includes.php';

if (class_exists('WishListMember')) {
    /**
     * Helper function to return $WishListMemberInstance
     *
     * Long term goal is to avoid using $WishListMemberInstance directly
     *
     * @return (object) \WishListMember
     */
    function wishlistmember_instance()
    {
        return $GLOBALS['WishListMemberInstance'];
    }

	// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase.
    global $WishListMemberInstance; // Make sure $WishListMemberInstance is global.
    $WishListMemberInstance = new WishListMember();
    // Initialize WishList Member.
    wishlistmember_instance()->initialize(WLM_PLUGIN_FILE, WLM_SKU, 'WishListMember', 'WishList Member', 'WishListMember');

    // Load the rest of the includes.
    require_once 'includes/includes-after-init.php';

    // Additional methods.
    wishlistmember_instance()->overload();
}
