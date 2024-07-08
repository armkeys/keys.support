<?php

/**
 * Team accounts - Child Access hooks.
 *
 * @package WishListMember/Features/Team_Accounts
 */

namespace WishListMember\Features\Team_Accounts;

add_filter('wishlistmember_protection_all_user_levels', __NAMESPACE__ . '\merge_parent_child_levels', 10, 2);
/**
 * Merge parent levels to child.
 * Called by 'wishlistmember_protection_all_user_levels' hook.
 *
 * @param  array   $levels  Array of Level data.
 * @param  integer $user_id User ID.
 * @return array
 */
function merge_parent_child_levels($levels, $user_id)
{
    if ((int) $user_id !== (int) get_current_user_id()) {
        return $levels;
    }
    $team_child = new Team_Child($user_id);
    if (! $team_child->teams) {
        return $levels;
    }
    return $team_child->all_levels;
}

add_filter('wishlistmember_protection_active_user_level_ids', __NAMESPACE__ . '\merge_parent_child_active_levels', 10, 2);
/**
 * Merge active parent levels to child.
 * Called by 'wishlistmember_protection_active_user_level_ids' hook.
 *
 * @param  array   $levels  Active level ids.
 * @param  integer $user_id User ID.
 * @return array
 */
function merge_parent_child_active_levels($levels, $user_id)
{
    if ((int) $user_id !== (int) get_current_user_id()) {
        return $levels;
    }
    $team_child = new Team_Child($user_id);
    if (! $team_child->teams) {
        return $levels;
    }
    return $team_child->active_levels;
}

add_filter('wishlistmember_protection_user_has_pay_per_post_access', __NAMESPACE__ . '\merge_parent_child_pay_per_posts', 10, 3);
/**
 * Merge parent pay per posts to child
 *
 * @param  boolean $has_pay_per_post_access Pay per post access.
 * @param  integer $post_id                 Post ID.
 * @param  integer $user_id                 User ID.
 * @return boolean
 */
function merge_parent_child_pay_per_posts($has_pay_per_post_access, $post_id, $user_id)
{
    if ((int) $user_id !== (int) get_current_user_id()) {
        return $has_pay_per_post_access;
    }
    if ($has_pay_per_post_access) {
        return $has_pay_per_post_access;
    }
    $team_child = new Team_Child($user_id);
    if (! $team_child->teams) {
        return $has_pay_per_post_access;
    }
    return in_array($post_id, $team_child->pay_per_posts);
}


add_action('wishlistmember_get_membership_levels', __NAMESPACE__ . '\inject_inherited_levels', PHP_INT_MAX, 2);
/**
 * Inject levels inherited from parent
 * Called by 'wishlistmember_get_membership_levels' filter.
 *
 * @param  array   $levels  Array of level IDs.
 * @param  integer $user_id User ID.
 * @return array
 */
function inject_inherited_levels($levels, $user_id)
{
    static $results    = [];
    static $is_running = false;

    // Prevent loops.
    if ($is_running) {
        return $levels;
    }

    // Only frontend, logged in and for the current user.
    if (is_admin() || ! is_user_logged_in() || (int) get_current_user_id() !== (int) $user_id) {
        return $levels;
    }

    $is_running = true;
    if (! isset($results[ get_current_user_id() ])) {
        $results[ get_current_user_id() ] = [];
        $child                            = new Team_Child(get_current_user_id());
        if ($child->teams) {
            $results[ get_current_user_id() ] = $child->active_levels;
        }
    }

    $levels = array_unique(array_merge(wlm_or($levels, []), wlm_or($results[ get_current_user_id() ], [])));

    $is_running = false;
    return $levels;
}

add_action('wishlistmember_get_user_pay_per_post', __NAMESPACE__ . '\inject_inherited_payperposts', PHP_INT_MAX, 2);
/**
 * Inject pay per posts inherited from parent
 * Called by 'wishlistmember_get_user_pay_per_post' filter.
 *
 * @param  array    $pay_per_posts Array of pay per post objects.
 * @param  string[] $user_ids      Array of U-X User IDs.
 * @return array
 */
function inject_inherited_payperposts($pay_per_posts, $user_ids)
{
    global $wpdb;
    static $results    = [];
    static $is_running = false;

    // Prevent loops.
    if ($is_running) {
        return $pay_per_posts;
    }

    // Only frontend, logged in and for the current user.
    if (is_admin() || ! is_user_logged_in() || ! in_array('U-' . get_current_user_id(), $user_ids, true)) {
        return $pay_per_posts;
    }

    $is_running = true;
    if (! isset($results[ get_current_user_id() ])) {
        $results[ get_current_user_id() ] = [];
        $child                            = new Team_Child(get_current_user_id());
        if ($child->teams) {
            $ppps                             = array_diff(wlm_or($child->pay_per_posts, []), wlm_or($child->original_pay_per_posts, []));
            $ppps[]                           = 0;
            $results[ get_current_user_id() ] = $wpdb->get_results(
                $wpdb->prepare(
                    'select `ID` as `content_id`, `post_type` as `type` from `' . $wpdb->posts . '` where `ID` IN (' . implode(', ', array_fill(0, count($ppps), '%d')) . ')',
                    ...array_values($ppps)
                )
            );
        }
    }
    $pay_per_posts = array_merge(wlm_or($pay_per_posts, []), wlm_or($results[ get_current_user_id() ], []));

    $is_running = false;
    return $pay_per_posts;
}
