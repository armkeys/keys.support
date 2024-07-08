<?php

/**
 * Team Accounts feature
 *
 * @package WishListMember/Features
 */

namespace WishListMember\Features\Team_Accounts;

define('WLM_PRO_TEAM_ACCOUNTS_DIR', __DIR__);
define('WLM_PRO_TEAM_ACCOUNTS_FILE', __FILE__);

/**
 * Returns a member's name and email formatted as Name <e@ma.il>
 *
 * @param  integer $user_id      User ID.
 * @param  boolean $return_array True to return an array containing name and email, false to return string.
 * @return string|string[]
 */
function format_member_name($user_id, $return_array = false)
{
    $u    = get_user_by('ID', $user_id);
    $name = trim($u->first_name . ' ' . $u->last_name);
    if (! $name) {
        $name = $u->display_name;
    }
    return $return_array ? [$name, $u->user_email] : sprintf("%s\t<%s>", $name, $u->user_email);
}

spl_autoload_register(
    /**
     * Autoloads classes for the Team Accounts feature.
     *
     * @param string $class Class name.
     */
    function ($class) {
        $prefix   = __NAMESPACE__ . '\\';
        $base_dir = __DIR__ . '/includes/';
        $len      = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relative_class = substr($class, $len);
        $file           = $base_dir . strtolower('class-' . str_replace(['\\', '_'], ['/', '-'], $relative_class) . '.php');
        if (file_exists($file)) {
            require $file;
        }
    }
);

require_once __DIR__ . '/includes/hooks-settings.php';
require_once __DIR__ . '/includes/hooks-help.php';

if (Team_Account::get_all()) {
    require_once __DIR__ . '/includes/hooks-member-manage.php';
    require_once __DIR__ . '/includes/hooks-member-edit.php';
    require_once __DIR__ . '/includes/hooks-payment-integration.php';
    require_once __DIR__ . '/includes/hooks-level-triggers.php';
    require_once __DIR__ . '/includes/hooks-team-management.php';
    require_once __DIR__ . '/includes/hooks-team-join.php';
    require_once __DIR__ . '/includes/hooks-child-access.php';
}
