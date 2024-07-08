<?php

/**
 * Loader for WishList Member Box Basic Blocks
 *
 * @package WishListMember\Features
 */

if (! defined('ABSPATH')) {
    exit;
}

define('WISHLISTMEMBER_BOX_BASIC_VERSION', '1.0.0');
define('WISHLISTMEMBER_BOX_BASIC_DIR', plugin_dir_path(__FILE__));
define('WISHLISTMEMBER_BOX_BASIC_URL', plugin_dir_url(__FILE__));

require_once 'classes/class-core.php';
require_once 'classes/class-blocks.php';

global $wishlistmember_box_basic_instant;
$wishlistmember_box_basic_instant = \WishListMember\Features\Box_Basic\Core::instance();
