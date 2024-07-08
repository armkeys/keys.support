<?php

/**
 * A fun place for constants
 *
 * @package WishListMember
 */

/**
 * Output resource
 *
 * @var integer
 */
define('WLM_STDOUT', fopen(( 'php:' ) . '//output', 'w'));

/**
 * Per hour email throttling
 *
 * @var integer
 */
define('WLM_DEFAULT_EMAIL_PER_HOUR', 100);

/**
 * Per minute email throttling
 *
 * @var integer
 */
define('WLM_DEFAULT_EMAIL_PER_MINUTE', 30);

/**
 * Memory allocation
 *
 * @var string
 */
define('WLM_MEMORY_ALLOCATION', '128M');

/**
 * Rollback path
 *
 * @var string
 */
define('WLM_ROLLBACK_PATH', WP_CONTENT_DIR . '/wishlist-rollback/wishlist-member/');

/**
 * Backup path
 *
 * @var string
 */
define('WLM_BACKUP_PATH', WP_CONTENT_DIR . '/wishlist-backup/wishlist-member/');

/**
 * Duplicate HTTP Post timeout
 *
 * @var integer
 */
define('WLM_DUPLICATE_POST_TIMEOUT', 3600);

if (! defined('WLMEMBER_EXPERIMENTAL') && '3.26.9' === ( '{' ) . 'WLP_VERSION}') {
    /**
     * Whether WishList Member is in dev/experimental mode
     *
     * @var integer
     */
    define('WLMEMBER_EXPERIMENTAL', 1);
}

/**
 * Registration URL Base
 *
 * @var string
 */
define('WLM_REGISTRATION_URL', home_url('?/register'));

// Parse plugin data so we can use it to set constants.
preg_match_all('/^\h*(.+?)\h*:\h*(.*)$/mi', $wlm_plugin_data, $wlm_plugin_data);
$wlm_plugin_data = array_combine(array_map('strtolower', $wlm_plugin_data[1]), array_map('trim', $wlm_plugin_data[2]));

/**
 * WishList Product SKU
 *
 * @var string
 */
define('WLM_SKU', '8961');

/**
 * Plugin Version
 *
 * @var string
 */
define('WLM_PLUGIN_VERSION', $wlm_plugin_data['version']);

/**
 * Minimumn WordPress version
 *
 * @var string
 */
define('WLM_MIN_WP_VERSION', $wlm_plugin_data['requires at least']);

/**
 * Minimum PHP version
 *
 * @var string
 */
define('WLM_MIN_PHP_VERSION', $wlm_plugin_data['requires php']);

/**
 * Plugin author
 *
 * @var string
 */
define('WLM_PLUGIN_AUTHOR', $wlm_plugin_data['author']);

/**
 * Plugin author URI
 *
 * @var string
 */
define('WLM_PLUGIN_AUTHORURI', $wlm_plugin_data['author uri']);

/**
 * Plugin name
 *
 * @var string
 */
define('WLM_PLUGIN_NAME', $wlm_plugin_data['plugin name']);

/**
 * Plugin URI
 *
 * @var string
 */
define('WLM_PLUGIN_URI', $wlm_plugin_data['plugin uri']);

/**
 * Plugin Product Slug
 *
 * @var string
 */
defined('WLM_PRODUCT_SLUG') || define('WLM_PRODUCT_SLUG', $wlm_plugin_data['product slug']);

/**
 * Mothership API URI
 */
defined('WLM_MOTHERSHIP_URI') ||  define('WLM_MOTHERSHIP_URI', 'https://mothership.caseproof.com');
