<?php

namespace WishListMember\Features\Team_Accounts;

add_filter('wishlistmember_member_manage_user_tbody_additional_classes', __NAMESPACE__ . '\add_member_manage_user_team_classes', 10, 2);
/**
 * Add user team classes to member management table.
 *
 * @param  string  $classes Classes.
 * @param  integer $user_id User ID.
 * @return string
 */
function add_member_manage_user_team_classes($classes, $user_id)
{
    if (( new Team_Parent($user_id) )->teams) {
        $classes .= ' team-accounts-parent';
    }
    if (( new Team_Child($user_id) )->teams) {
        $classes .= ' team-accounts-child';
    }
    return $classes;
}

add_action(
    'wp_ajax_wishlistmember_team_accounts_add_team_to_parent',
    function () {
        verify_wlm_nonces();
        if (! WLM_POST_NONCED) {
            wp_send_json_error();
        }
        $data = filter_input_array(INPUT_POST);

        $team_id        = wlm_trim(wlm_arrval($data, 'team_id'));
        $transaction_id = wlm_trim(wlm_arrval($data, 'transaction_id'));
        $quantity       = (int) wlm_arrval($data, 'quantity');
        $user_id        = (int) wlm_arrval($data, 'user_id');

        ! $team_id && wp_send_json_error(['message' => __('Team required', 'wishlist-member')]);
        ! $quantity && wp_send_json_error(['message' => __('Team members has to be 1 or more', 'wishlist-member')]);
        ! $user_id && wp_send_json_error(['message' => __('Invalid user', 'wishlist-member')]);

        if (! $transaction_id) {
            $transaction_id = 'WLTA-' . $user_id . '-' . (int) ( microtime(true) * 1000 );
        }
        $p = new Team_Parent($user_id);
        $p->add_team($team_id, $transaction_id, $quantity) || wp_send_json_error(['message' => __('Failed adding team to user', 'wishlist-member')]);
        wp_send_json_success();
    }
);

add_action(
    'wp_ajax_wishlistmember_team_accounts_add_member',
    function () {
        verify_wlm_nonces();
        if (! WLM_POST_NONCED) {
            wp_send_json_error();
        }
        $data = filter_input_array(INPUT_POST);

        $user_id = (int) wlm_arrval($data, 'user_id');
        $team_id = wlm_trim(wlm_arrval($data, 'team_id'));
        $email   = wlm_trim(wlm_arrval($data, 'email'));
        $method  = wlm_trim(wlm_arrval($data, 'method'));
        ! $user_id && wp_send_json_error(['message' => __('Parent required')]);
        ! $team_id && wp_send_json_error(['message' => __('Team required')]);
        ! $email && wp_send_json_error(['message' => __('Email required')]);

        $p = new Team_Parent($user_id);

        switch ($method) {
            case 'add':
                $u = get_user_by('email', $email);
                if ($u) {
                    $p->add_children($team_id, $u->ID) && wp_send_json_success(['message' => __('Team Member added.', 'wishlist-member')]);
                }
                // No user found continue to sending invite.
            default: // send invite.
                $p->send_team_invite($team_id, $email) && wp_send_json_success(['message' => __('Invite sent to user.', 'wishlist-member')]);
        }
        wp_send_json_error(['message' => __('Error adding/inviting user to team', 'wishlist-member')]);
    }
);

add_action('wp_ajax_wishlistmember_team_accounts_remove_team', __NAMESPACE__ . '\remove_team_or_change_team_status');
add_action('wp_ajax_wishlistmember_team_accounts_cancel_team', __NAMESPACE__ . '\remove_team_or_change_team_status');
add_action('wp_ajax_wishlistmember_team_accounts_uncancel_team_from_parent', __NAMESPACE__ . '\remove_team_or_change_team_status');
function remove_team_or_change_team_status()
{
    verify_wlm_nonces();
    if (! WLM_POST_NONCED) {
        wp_send_json_error();
    }
    $data           = filter_input_array(INPUT_POST);
    $user_id        = (int) wlm_arrval($data, 'user_id');
    $transaction_id = wlm_trim(wlm_arrval($data, 'transaction_id'));
    $team_id        = wlm_trim(wlm_arrval($data, 'team_id'));

    ! $user_id && wp_send_json_error(['message' => __('Invalid user', 'wishlist-member')]);
    ! $transaction_id && wp_send_json_error(['message' => __('Transaction ID not specified', 'wishlist-member')]);
    ! $team_id && wp_send_json_error(['message' => __('Team ID not specified', 'wishlist-member')]);
    switch (current_action()) {
        case 'wp_ajax_wishlistmember_team_accounts_remove_team':
            ( new Team_Parent($user_id) )->remove_team($team_id, $transaction_id);
            wp_send_json_success(
                [
                    'message' => sprintf(
                        __('Team Plan removed: %1$s (%2$s)', 'wishlist-member'),
                        wlm_arrval($data, 'team_name'),
                        $transaction_id
                    ),
                ]
            );
            break;
        case 'wp_ajax_wishlistmember_team_accounts_cancel_team':
            ( new Team_Parent($user_id) )->set_team_status(Team_Parent::STATUS_INACTIVE, $team_id, $transaction_id);
            wp_send_json_success(
                [
                    'message' => sprintf(
                        __('Team Plan cancelled: %1$s (%2$s)', 'wishlist-member'),
                        wlm_arrval($data, 'team_name'),
                        $transaction_id
                    ),
                ]
            );
            break;
        case 'wp_ajax_wishlistmember_team_accounts_uncancel_team_from_parent':
            ( new Team_Parent($user_id) )->set_team_status(Team_Parent::STATUS_ACTIVE, $team_id, $transaction_id);
            wp_send_json_success(
                [
                    'message' => sprintf(
                        __('Team Plan uncancelled: %1$s (%2$s)', 'wishlist-member'),
                        wlm_arrval($data, 'team_name'),
                        $transaction_id
                    ),
                ]
            );
            break;
        default:
            wp_send_json_error(['message' => __('Invalid action', 'wishlist-member')]);
    }
}


add_action(
    'wp_ajax_wishlistmember_team_accounts_get_parent_team_data',
    function () {
        verify_wlm_nonces();
        if (! WLM_POST_NONCED) {
            wp_send_json_error();
        }
        $data = filter_input_array(INPUT_POST);

        $user_id = (int) wlm_arrval($data, 'user_id');
        ! $user_id && wp_send_json_error(['message' => __('Invalid user', 'wishlist-member')]);
        $p = new Team_Parent($user_id);
        wp_send_json_success(
            [
                'teams'    => $p->teams_grouped(),
                'children' => $p->children,
            ]
        );
    }
);

add_action(
    'wishlistmember_member_manage_user_row',
    function ($user_id, $tbl_collapse) {
        $child                   = new Team_Child($user_id);
        $parent                  = new Team_Parent($user_id);
        $parent_allowed_children = array_filter(
            array_map(
                function ($team) use ($parent) {
                    return $parent->get_max_allowed_children($team[0]['id']);
                },
                $parent->teams_grouped()
            )
        );
        $parent_children_count   = array_sum(
            array_map(
                function ($children) {
                    return count($children);
                },
                $parent->children
            )
        );
        if ($child->teams) {
            require __DIR__ . '/views/member-rows/child.php';
        }
        if ($parent_allowed_children) {
            require __DIR__ . '/views/member-rows/parent.php';
        }
    },
    10,
    2
);

add_action(
    'wp_ajax_wishlistmember_team_accounts_remove_member',
    function () {
        verify_wlm_nonces();
        if (! WLM_POST_NONCED) {
            wp_send_json_error();
        }
        $data = filter_input_array(INPUT_POST);

        $user_id   = (int) wlm_arrval($data, 'user_id');
        $member_id = (int) wlm_arrval($data, 'member_id');
        $team_id   = (int) wlm_arrval($data, 'team_id');
        if (! $user_id || ! $member_id || ! $team_id) {
            wp_send_json_error(['message' => __('Invalid request', 'wishlist-member')]);
        }
        $p = new Team_Parent($user_id);
        $p->remove_children($team_id, $member_id);

        wp_send_json_success(['message' => __('Team Member removed.', 'wishlist-member')]);
    }
);

add_action(
    'wp_ajax_wishlistmember_team_accounts_cancel_invite',
    function () {
        verify_wlm_nonces();
        if (! WLM_POST_NONCED) {
            wp_send_json_error();
        }
        $data = filter_input_array(INPUT_POST);

        $user_id      = (int) wlm_arrval($data, 'user_id');
        $member_email = wlm_trim(wlm_arrval($data, 'member_email'));
        $team_id      = (int) wlm_arrval($data, 'team_id');
        if (! $user_id || ! $member_email || ! $team_id) {
            wp_send_json_error(['message' => __('Invalid request', 'wishlist-member')]);
        }
        $p = new Team_Parent($user_id);
        $p->delete_team_invite($team_id, $member_email);

        wp_send_json_success(['message' => __('Invite cancelled', 'wishlist-member')]);
    }
);
