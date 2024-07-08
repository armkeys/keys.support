<?php

namespace WishListMember\Features\Team_Accounts;

/**
 * Common team accounts functions.
 *
 * @package WishListMember/Features/TeamAccounts
 */

add_filter('wishlistmember_payment_integration_levels', __NAMESPACE__ . '\add_team_levels');
/**
 * Adds ala-carte levels to payment integration.
 * Called by 'wishlistmember_payment_integration_levels' filter
 *
 * @param  array $levels Associative of levels.
 * @return array
 */
function add_team_levels($levels)
{
    $teams                   = Team_Account::get_all();
    $levels['team-accounts'] = array_map(
        function ($team) {
            return [
                'id'   => 'team-' . $team->id,
                'name' => $team->name,
            ];
        },
        $teams
    );
    return $levels;
}

add_filter('wishlistmember_payment_integration_levels_labels', __NAMESPACE__ . '\add_team_levels_labels');
/**
 * Adds ala-carte levels to payment integration.
 * Called by 'wishlistmember_payment_integration_levels_labels' filter
 *
 * @param  array $levels Associative of levels.
 * @return array
 */
function add_team_levels_labels($labels)
{
    $labels['team-accounts'] = (object) ['labels' => (object) ['name' => __('Team Plans', 'wishlist-member')]];
    return $labels;
}

add_filter('wishlistmember_get_option_wpm_levels', __NAMESPACE__ . '\remove_team_levels', 1);
function remove_team_levels($levels)
{
    foreach (array_keys($levels) as $key) {
        if ('team-' === substr($key, 0, 5)) {
            unset($levels[ $key ]);
        }
    }
    return $levels;
}

add_action('wishlistmember_call_payment_provider_method', __NAMESPACE__ . '\handle_team_purchase', 1);
/**
 * Handle team purchase.
 * Called by 'wishlistmember_call_payment_provider_method' hook.
 *
 * @return void
 */
function handle_team_purchase()
{
    add_filter('wishlistmember_get_option_wpm_levels', __NAMESPACE__ . '\inject_team_levels');
}


add_filter('wishlistmember_registration_levels', __NAMESPACE__ . '\inject_team_levels');
/**
 * Inject "team" levels so payment providers work.
 * Called by 'wishlistmember_get_option_wpm_levels' hook.
 *
 * @param  array $levels Membership levels.
 * @return array
 */
function inject_team_levels($levels)
{
    if (! is_array($levels)) {
        $levels = [];
    }
    $teams           = Team_Account::get_all();
    $welcome_message = wlm_or(wishlistmember_instance()->get_option('team-accounts/admin-join-message'), default_admin_join_message());
    foreach ($teams as $team) {
        $levels[ 'team-' . $team->id ] = [
            'ID'                       => 'team-' . $team->id,
            'id'                       => 'team-' . $team->id,
            'rawid'                    => $team->id,
            'name'                     => $team->name,
            'url'                      => md5('team-' . $team->id . AUTH_KEY),
            'custom_afterreg_redirect' => 1,
            'afterreg_redirect_type'   => 'message',
            'afterreg_message'         => $welcome_message,
        ];
    }
    return $levels;
}

// Add_action( 'wishlistmember_pre_shopping_cart_registration', __NAMESPACE__ . '\add_team_to_user', 1 );
add_action('wishlistmember_shoppingcart_register', __NAMESPACE__ . '\add_team_to_user', 1);
add_action('wishlistmember_existing_member_purchase', __NAMESPACE__ . '\add_team_to_user', 1);
/**
 * Add team to user.
 * Called by 'wishlistmember_shoppingcart_register' hook.
 *
 * @return void
 */
function add_team_to_user()
{
    $team_id = wlm_post_data()['wpm_id'];
    if ('team-' !== substr($team_id, 0, 5)) {
        return;
    }
    $team = Team_Account::get(substr($team_id, 5));
    if (! $team) {
        return;
    }
    $email = wlm_post_data()['email'];
    $x     = get_user_by('email', $email);
    if ($x) {
        $x = new Team_Parent($x->ID);
        $x->add_team($team->id, wlm_post_data()['sctxnid'], $team->default_children);
    }
}

add_action('wishlistmember_shoppingcart_deactivate', __NAMESPACE__ . '\cancel_team_from_user', 1);
/**
 * Cancel teams with matching transaction ID.
 * Called by 'wishlistmember_shoppingcart_deactivate' hook.
 *
 * @return void
 */
function cancel_team_from_user()
{
    Team_Parent::cancel_team_by_transaction_id(wlm_post_data()['sctxnid']);
}

add_action('wishlistmember_shoppingcart_reactivate', __NAMESPACE__ . '\uncancel_team_from_user', 1);
/**
 * Uncancel teams with matching transaction ID.
 * Called by 'wishlistmember_shoppingcart_reactivate' hook.
 *
 * @return void
 */
function uncancel_team_from_user()
{
    Team_Parent::uncancel_team_by_transaction_id(wlm_post_data()['sctxnid']);
}

add_filter('wishlistmember_continue_incomplete_registration_levels', __NAMESPACE__ . '\incomplete_registration_levels', 10, 2);
/**
 * Insert Team "Levels" for incomplete registration processing
 * Called by 'wishlistmember_continue_incomplete_registration_levels' hook.
 *
 * @param  array   $levels  Membership levels.
 * @param  integer $user_id User ID.
 * @return array
 */
function incomplete_registration_levels($levels, $user_id)
{
    if (count($levels)) {
        return $levels;
    }
    $p = new Team_Parent($user_id);
    if ($p->teams) {
        $team     = array_shift($p->teams);
        $levels[] = 'team-' . $team['id'];
    }
    return $levels;
}

add_action('wishlistmember_registration_success', __NAMESPACE__ . '\merge_registration_team_levels', 10, 2);
/**
 * Merge team "levels" when completing an incomplete registration.
 * Called by 'wishlistmember_registration_success' hook.
 *
 * @param  integer $user_id   User data.
 * @param  array   $post_data Registration POST data
 * @return void
 */
function merge_registration_team_levels($user_id, $post_data)
{
    $level     = wlm_arrval($post_data, 'wpm_id');
    $mergewith = wlm_arrval($post_data, 'mergewith');
    if (substr($level, 0, 5) === 'team-' && $mergewith) {
        $temp = new Team_Parent($mergewith);
        $p    = new Team_Parent($user_id);
        foreach ($temp->teams as $team) {
            $p->add_team($team['id'], $team['transaction_id'], $team['quantity']);
        }
    }
}

add_filter('wishlistmember_custom_error_page_levels', __NAMESPACE__ . '\inject_team_levels_redirect', 10, 3);
add_filter('wishlistmember_redirect_levels', __NAMESPACE__ . '\inject_team_levels_redirect', 10, 3);
function inject_team_levels_redirect($levels, $level_id, $index)
{
    if (substr($level_id, 0, 5) === 'team-' && 'afterreg' === $index) {
        $levels = inject_team_levels($levels);
    }
    return $levels;
}
