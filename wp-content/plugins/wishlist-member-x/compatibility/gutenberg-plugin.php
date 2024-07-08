<?php

/**
 * Compatibility for Gutenberg plugin
 *
 * @package WishListMember/Compatibility
 */

if (wlm_arrval($_GET, 'page') === 'WishListMember') {
    // Fix for wp.media issues when gutenberg plugin is active.
    remove_action('wp_default_scripts', 'gutenberg_register_vendor_scripts');
    remove_action('wp_default_scripts', 'gutenberg_register_packages_scripts');
    remove_action('wp_default_scripts', 'gutenberg_register_vendor_scripts_62');
}
