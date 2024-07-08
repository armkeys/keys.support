<?php

/**
 * Registration hooks
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

add_action('wishlistmember_confirm_user_levels', __NAMESPACE__ . '\add_team_to_parent', 10, 2);
add_action('wishlistmember_approve_user_levels', __NAMESPACE__ . '\add_team_to_parent', 10, 2);
add_action('wishlistmember_uncancel_user_levels', __NAMESPACE__ . '\add_team_to_parent', 10, 2);
add_action('wishlistmember_add_user_levels_shutdown', __NAMESPACE__ . '\add_team_to_parent', 10, 2);
/**
 * Update member's team account settings upon level registration
 * Called by 'wishlistmember_confirm_user_levels' hook.
 * Called by 'wishlistmember_approve_user_levels' hook.
 * Called by 'wishlistmember_uncancel_user_levels' hook.
 * Called by 'wishlistmember_add_user_levels_shutdown' hook.
 *
 * @param integer  $user_id User ID.
 * @param string[] $levels  Array of levels that the member was added to.
 */
function add_team_to_parent($user_id, $levels)
{
    $triggers = [];
    $teams    = Team_Account::get_all(true);
    while ($team = array_shift($teams)) {
        if (empty($team['default_children'])) {
            continue;
        }
        while ($level = array_shift($team['triggers'])) {
            $triggers[ $level ][] = $team['id'];
        }
    }
    $parent = new Team_Parent($user_id);
    while ($level = array_shift($levels)) {
        if (empty($triggers[ $level ])) {
            continue;
        }
        $transaction_id = wishlistmember_instance()->get_membership_levels_txn_id($user_id, $level);
        while ($team_id = array_shift($triggers[ $level ])) {
            switch (current_action()) {
                case 'wishlistmember_uncancel_user_levels':
                case 'wishlistmember_confirm_user_levels':
                case 'wishlistmember_approve_user_levels':
                    $parent->set_team_status(
                        Team_Parent::STATUS_ACTIVE,
                        null,
                        wishlistmember_instance()->get_membership_levels_txn_id($user_id, $level)
                    );
                    break;
                default:
                    $parent->add_team($team_id, $transaction_id);
            }
        }
    }
}

add_action('wishlistmember_pre_remove_user_levels', __NAMESPACE__ . '\remove_team_from_parent', 10, 2);
add_action('wishlistmember_unconfirm_user_levels', __NAMESPACE__ . '\remove_team_from_parent', 10, 2);
add_action('wishlistmember_unapprove_user_levels', __NAMESPACE__ . '\remove_team_from_parent', 10, 2);
add_action('wishlistmember_cancel_user_levels', __NAMESPACE__ . '\remove_team_from_parent', 10, 2);
/**
 * Update team account's quantity upon level removal or cancellation.
 * Called by 'wishlistmember_pre_remove_user_levels'
 * Called by 'wishlistmember_cancel_user_levels'
 * Called by 'wishlistmember_unconfirm_user_levels'
 * Called by 'wishlistmember_unapprove_user_levels'
 *
 * @param integer  $user_id User ID.
 * @param string[] $levels  Level IDs.
 */
function remove_team_from_parent($user_id, $levels)
{
    $parent = new Team_Parent($user_id);
    while ($level = array_shift($levels)) {
        $parent->set_team_status(
            Team_Parent::STATUS_INACTIVE,
            null,
            wishlistmember_instance()->get_membership_levels_txn_id($user_id, $level)
        );
    }
}
