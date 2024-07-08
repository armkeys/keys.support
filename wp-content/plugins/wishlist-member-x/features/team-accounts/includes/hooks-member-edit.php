<?php

/**
 * Member edit
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

add_filter('wishlistmember_member_edit_tabs', __NAMESPACE__ . '\member_edit_tab', 1);
/**
 * Adds Team tab to member edit dialog
 *
 * @param  array $tabs Tabs.
 * @return array
 */
function member_edit_tab($tabs)
{
    $tabs['team-accounts'] = __('Teams', 'wishlist-member');
    return $tabs;
}

add_action('wishlistmember_member_edit_tab_pane-team-accounts', __NAMESPACE__ . '\member_edit_tab_pane');
/**
 * Displays member edit tab pane
 *
 * @param integer $user_id User ID.
 */
function member_edit_tab_pane($user_id)
{
    require __DIR__ . '/views/member-edit/teams.php';
}

add_action('wp_ajax_wishlistmember-team-accounts-member-search', __NAMESPACE__ . '\member_search');
/**
 * AJAX handler for searching a member
 * Called by 'wp_ajax_wishlistmember-team-accounts-member-search' hook
 */
function member_search()
{
    verify_wlm_nonces();
    if (! WLM_POST_NONCED) {
        wp_send_json([]);
    }
    $post   = filter_input_array(INPUT_POST);
    $search = wlm_trim(wlm_arrval($post, 'q'));
    if (! $search) {
        wp_send_json([]);
    }
    $search = new \WishListMember\User_Search(
        [
            'search_term' => $search,
            'exclude'     => (array) wlm_arrval($post, 'current_members'),
            'meta_query'  => [
                'relation' => 'AND',
                [
                    'key'     => 'team_accounts_parent',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => 'team_accounts',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]
    );
    $data   = [];
    foreach ($search->results as $id) {
        $data[] = [
            'id'   => $id,
            'text' => format_member_name($id),
        ];
    }

    wp_send_json(['results' => $data]);
}

add_action('wishlistmember_pre_admin_screen', __NAMESPACE__ . '\member_manage_scripts_and_styles', 10);
/**
 * Enqueue scripts and styles for member > manage section.
 * Called by 'wishlistmember_pre_admin_screen' hook.
 *
 * @param string $wl Requested view.
 */
function member_manage_scripts_and_styles($wl)
{
    if ('members/manage' !== $wl) {
        return;
    }
    wp_enqueue_style('wishlist-member-team-accounts-member-manage', plugin_dir_url(__DIR__) . '/assets/css/member-manage.css', [], WLM_PLUGIN_VERSION);
    wp_enqueue_script('wishlist-member-team-accounts-member-manage', plugin_dir_url(__DIR__) . '/assets/js/member-manage.js', [], WLM_PLUGIN_VERSION);
    wp_print_scripts('wishlist-member-team-accounts-member-manage');
}
