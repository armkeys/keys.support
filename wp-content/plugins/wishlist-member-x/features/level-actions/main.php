<?php

/**
 * Level Actions Feature
 *
 * @package WishListMember/Features
 */

namespace WishListMember\Features\Level_Actions;

if (file_exists(__DIR__ . '/includes/class-level-action-methods.php')) {
    require __DIR__ . '/includes/class-level-action-methods.php';
    new Level_Action_Methods();
}

add_filter('wishlistmember_level_edit_tabs', __NAMESPACE__ . '\add_level_edit_menu');
/**
 * Add Actions submenu to level edit screen
 *
 * @param  array $tabs Tabs.
 * @return array
 */
function add_level_edit_menu($tabs)
{
    return (array) $tabs + ['level_actions' => esc_html__('Actions', 'wishlist-member')];
}

if (file_exists(__DIR__ . '/views/level-actions.php')) {
    add_action('wishlistmember_level_edit_tab_pane_level_actions', __NAMESPACE__ . '\show_level_edit_screen', 10, 2);
}
/**
 * Show level edit screen
 *
 * @param string $level_id   Level ID.
 * @param array  $level_data Level Data.
 */
function show_level_edit_screen($level_id, $level_data)
{
    require __DIR__ . '/views/level-actions.php';
    require __DIR__ . '/views/modals/level-actions.php';
    wp_enqueue_script('wlm-level-actions', plugins_url('assets/js/level-actions.js', __FILE__), ['jquery'], WLM_PLUGIN_VERSION, true);
    wp_doing_ajax() && wp_print_scripts('wlm-level-actions');
}
