<?php

/**
 * Thrive Automator handler
 *
 * @package WishListMember/Integrations/Others
 */

namespace WishListMember\Integrations\Others;

if (PHP_MAJOR_VERSION < 7) {
    return; // PHP 7 required.
}

const wlmta_actions = [
    'wishlistmember_add_user_levels_shutdown' => ['wlmta_add_user_levels', 10, 2],
    'wishlistmember_pre_remove_user_levels'   => ['wlmta_remove_user_levels', 10, 2],
    'wishlistmember_cancel_user_levels'       => ['wlmta_cancel_user_levels', 10, 2],
    'wishlistmember_uncancel_user_levels'     => ['wlmta_uncancel_user_levels', 10, 2],
    'wishlistmember_expire_user_levels'       => ['wlmta_expire_user_levels', 10, 2],
    'wishlistmember_unexpire_user_levels'     => ['wlmta_unexpire_user_levels', 10, 2],
    'wishlistmember_approve_user_levels'      => ['wlmta_approve_user_levels', 10, 2],
    'wishlistmember_confirm_user_levels'      => ['wlmta_confirm_user_levels', 10, 2],
    'wishlistmember_user_registered'          => ['wlmta_wlm_user_register', 10, 2],
    'user_register'                           => ['wlmta_wlm_user_register', 10, 2],
    'delete_user'                             => ['wlmta_delete_user'],
];

/**
 * Map level actions to wlmta_* actions
 */
function wlmta_actions()
{
    static $level_actions = [
        'wishlistmember_add_user_levels_shutdown',
        'wishlistmember_pre_remove_user_levels',
        'wishlistmember_cancel_user_levels',
        'wishlistmember_uncancel_user_levels',
        'wishlistmember_expire_user_levels',
        'wishlistmember_unexpire_user_levels',
        'wishlistmember_approve_user_levels',
        'wishlistmember_confirm_user_levels',
    ];
    $action               = current_action();
    if (empty(wlmta_actions[ $action ])) {
        return;
    }
    $args = func_get_args();
    if (in_array($action, $level_actions, true)) {
        $user_id = $args[0];
        $levels  = (array) $args[1];
        foreach ($levels as $level) {
            do_action(wlmta_actions[ $action ][0], $user_id, $level);
        }
    } else {
        do_action(wlmta_actions[ $action ][0], ...$args);
    }
}

add_action('wishlistmember_suppress_other_integrations', __NAMESPACE__ . '\wlmta_suppress_integration');
/**
 * Suppress integration triggers.
 */
function wlmta_suppress_integration()
{
    wlmta_map_actions(false);
}

function wlmta_map_actions($enable = true)
{
    $function = $enable ? 'add_action' : 'remove_action';
    foreach (wlmta_actions as $action => $params) {
        $function($action, __NAMESPACE__ . '\wlmta_actions', ...$params);
    }
}

wlmta_map_actions();

// Registration integration with Thrive Automator.
require_once __DIR__ . '/automations/main.php';
