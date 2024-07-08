<?php

/**
 * Settings
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

add_filter('wishlist_member_menu', __NAMESPACE__ . '\settings_menu', 1);
/**
 * Add Team Accounts menu
 * Called by 'wishlist_member_menu' hook
 *
 * @param  array $menus WishList Member menu items.
 * @return array
 */
function settings_menu($menus)
{
    $new_menu = [
        'key'   => 'team-accounts',
        'name'  => __('Team Accounts', 'wishlist-member'),
        'title' => __('Team Accounts', 'wishlist-member'),
        'icon'  => 'supervisor_account',
        'sub'   => [
            [
                'key'   => 'plans',
                'name'  => __('Team Plans', 'wishlist-member'),
                'title' => __('Team Plans', 'wishlist-member'),
                'icon'  => 'group',
            ],
            [
                'key'   => 'settings',
                'name'  => __('Settings', 'wishlist-member'),
                'title' => __('Settings', 'wishlist-member'),
            ],
            [
                'key'   => 'email-template',
                'name'  => __('Email Templates', 'wishlist-member'),
                'title' => __('Email Templates', 'wishlist-member'),
                'icon'  => 'email',
            ],
            [
                'key'   => 'css',
                'name'  => __('Custom CSS', 'wishlist-member'),
                'title' => __('Custom CSS', 'wishlist-member'),
                'icon'  => 'code',
            ],
        ],
    ];
    foreach ($menus as $key => $menu) {
        if ('setup' === $menu['key']) {
            array_splice($menus[ $key ]['sub'], 3, 0, [$new_menu]);
            break;
        }
    }
    return $menus;
}

add_action('wishlistmember_admin_screen', __NAMESPACE__ . '\load_settings_screens');
/**
 * Display admin views
 * Called by 'wishlistmember_admin_screen' hook
 *
 * @param string $wl Current view being requested.
 */
function load_settings_screens($wl)
{
    switch ($wl) {
        case 'setup/team-accounts/settings':
            require_once __DIR__ . '/views/settings/settings.php';
            break;
        case 'setup/team-accounts/email-template':
            require_once __DIR__ . '/views/settings/email-template.php';
            break;
        case 'setup/team-accounts/css':
            require_once __DIR__ . '/views/settings/css.php';
            break;
        case 'setup/team-accounts/plans':
            require_once __DIR__ . '/views/settings/plans.php';
            break;
    }
}

add_action('wishlistmember_pre_admin_screen', __NAMESPACE__ . '\team_accounts_scripts_and_styles', 10);
/**
 * Load view styles and scripts.
 * Called by 'wishlistmember_pre_admin_screen' hook.
 *
 * @param string $wl Current view being requested.
 */
function team_accounts_scripts_and_styles($wl)
{
    $parts = explode('/', $wl);
    if ('setup' !== $parts[0] || 'team-accounts' !== $parts[1]) {
        return;
    }
    $parts  = implode('/', array_slice($parts, 2));
    $handle = 'wishlist-member-team-accounts-' . $parts;
    $parts  = __DIR__ . '/views/settings/' . $parts;
    if (file_exists($parts . '.css')) {
        wp_enqueue_style(
            $handle,
            plugins_url(basename($parts) . '.css', $parts),
            [],
            WLM_PLUGIN_VERSION
        );
    }
    if (file_exists($parts . '.js')) {
        wp_enqueue_script(
            $handle,
            plugins_url(basename($parts) . '.js', $parts),
            [],
            WLM_PLUGIN_VERSION
        );
    }
    wp_print_scripts($handle);
    wp_print_styles($handle);
}

add_action('wp_ajax_wishlistmember_team_accounts_save_settings', __NAMESPACE__ . '\save_settings');
/**
 * Save admin settings
 * Called by 'wp_ajax_wishlistmember_team_accounts_save_settings' hook
 */
function save_settings()
{
    verify_wlm_nonces();

    if (! WLM_POST_NONCED) {
        wp_send_json_error();
    }

    $post = filter_input_array(INPUT_POST);
    unset($post['action']);
    unset($post[ get_wlm_nonce_field_name() ]);
    foreach ($post as $key => $value) {
        wishlistmember_instance()->save_option($key, $value);
    }
    wp_send_json_success($post);
}

add_action('wp_ajax_wishlistmember_team_accounts_save_team', __NAMESPACE__ . '\save_team');
/**
 * Save team
 * Called by 'wp_ajax_wishlistmember_team_accounts_save_team' hook
 */
function save_team()
{
    verify_wlm_nonces();
    $old_teams = Team_Account::get_all(true);

    if (! WLM_POST_NONCED) {
        wp_send_json_error($old_teams);
    }

    if (Team_Account::create(wlm_arrval(filter_input_array(INPUT_POST), 'team'))) {
        wp_send_json_success(Team_Account::get_all(true));
    } else {
        wp_send_json_error($old_teams);
    }
}

add_action('wp_ajax_wishlistmember_team_accounts_delete_team', __NAMESPACE__ . '\delete_team');
/**
 * Delete team
 * Called by 'wp_ajax_wishlistmember_team_accounts_delete_team' hook
 */
function delete_team()
{
    verify_wlm_nonces();
    $old_teams = Team_Account::get_all(true);

    if (! WLM_POST_NONCED) {
        wp_send_json_error($old_teams);
    }

    $team = Team_Account::get(wlm_arrval(filter_input_array(INPUT_POST), 'id'));
    if ($team) {
        $team->delete();
        wp_send_json_success(Team_Account::get_all(true));
    } else {
        wp_send_json_error($old_teams);
    }
}


add_action('wishlistmember_version_changed', __NAMESPACE__ . '\activate');

/**
 * Plugin activation handler
 * Called by 'wishlistmember_version_changed' hook
 */
function activate()
{
    set_initial_values();
}

/**
 * Set initial option values.
 */
function set_initial_values()
{
    // Team invite email settings.
    wishlistmember_instance()->add_option(
        'team-accounts/team_invite_email_sender_email',
        wishlistmember_instance()->get_option('email_sender_address')
    );
    wishlistmember_instance()->add_option(
        'team-accounts/team_invite_email_sender_name',
        wishlistmember_instance()->get_option('email_sender_name')
    );
    wishlistmember_instance()->add_option(
        'team-accounts/team_invite_email_subject',
        'You have been invited to [site_name]'
    );
    wishlistmember_instance()->add_option(
        'team-accounts/team_invite_email_message',
        file_get_contents(WLM_PRO_TEAM_ACCOUNTS_DIR . '/templates/invite-email.txt')
    );
    wishlistmember_instance()->add_option(
        'team-accounts/custom-css',
        file_get_contents(WLM_PRO_TEAM_ACCOUNTS_DIR . '/templates/custom-css.txt')
    );
    wishlistmember_instance()->add_option(
        'team-accounts/admin-join-message',
        default_admin_join_message()
    );
    wishlistmember_instance()->add_option(
        'team-accounts/member-join-message',
        default_member_join_message()
    );
    add_action(
        'wp_loaded',
        function () {
            $management_page = wishlistmember_instance()->get_option('team-accounts/team_management_page');
            if (! $management_page || ! get_post($management_page)) {
                $management_page = create_team_accounts_page(__('Team Management', 'wishlist-member'));
            }
        }
    );
}

/**
 * Get default welcome message when user becomes a team admin.
 *
 * @return string
 */
function default_admin_join_message()
{
    return file_get_contents(WLM_PRO_TEAM_ACCOUNTS_DIR . '/templates/admin-join-welcome-message.txt');
}

/**
 * Get default welcome message when user becomes a team member.
 *
 * @return string
 */
function default_member_join_message()
{
    return file_get_contents(WLM_PRO_TEAM_ACCOUNTS_DIR . '/templates/member-join-welcome-message.txt');
}

add_action('wp_ajax_wishlistmember_team_accounts_create_page', __NAMESPACE__ . '\team_accounts_create_page');
/**
 * Ajax handler to create a team accounts page.
 * Called by 'wp_ajax_wishlistmember_team_accounts_create_page' hook.
 */
function team_accounts_create_page()
{
    current_user_can('publish_pages') || wp_send_json_error();
    verify_wlm_nonces();
    if (! WLM_POST_NONCED) {
        wp_send_json_error();
    }
    $data            = filter_input_array(INPUT_POST);
    $page_title      = wlm_or(trim((string) wlm_arrval($data, 'title')), __('Team Management', 'wishlist-member'));
    $management_page = create_team_accounts_page($page_title);
    if (is_int($management_page) && $management_page) {
        wp_send_json_success(
            [
                'id'    => $management_page,
                'title' => $page_title,
            ]
        );
    } else {
        wp_send_json_error();
    }
}

/**
 * Create team account pages
 *
 * @param  string $title Page title.
 * @return integer|WP
 */
function create_team_accounts_page($title)
{
    $management_page = wp_insert_post(
        [
            'post_title'     => $title,
            'post_content'   => file_get_contents(WLM_PRO_TEAM_ACCOUNTS_DIR . '/templates/team-management-page.txt'),
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'comment_status' => 'close',
            'ping_status'    => 'close',
        ]
    );
    if (is_int($management_page) && $management_page) {
        wishlistmember_instance()->save_option('team-accounts/team_management_page', $management_page);
    }
    return $management_page;
}

add_filter('display_post_states', __NAMESPACE__ . '\add_display_post_states', 10, 2);
/**
 * Add Team Accounts post states
 * Called by 'display_post_states' hook.
 *
 * @param  string[] $post_states String of post states.
 * @param  WP_Post  $post        WP_Post object
 * @return string[]
 */
function add_display_post_states($post_states, $post)
{
    if ($post->ID === (int) wishlistmember_instance()->get_option('team-accounts/team_management_page')) {
        $post_states['wlmta_page_for_team_management'] = __('Team Management Page', 'wishlist-member');
    }
    return $post_states;
}

add_filter('wishlistmember_mergecodes', __NAMESPACE__ . '\mergecode_manifest');
/**
 * Add Team Accounts to mergecodes manifest
 *
 * @param  array $manifest Mergecode manifest.
 * @return array
 */
function mergecode_manifest($manifest)
{
    $manifest['Team Accounts'] = [
        'wlm_team_management'  => [
            'label' => 'Team Management Interface',
        ],
        'wlm_managed_team_plan_name'  => [
            'label' => 'Managed Team Plan Name',
        ],
        'wlm_joined_team_plan_name'  => [
            'label' => 'Joined Team Plan Name',
        ],
        'wlm_joined_team_plan_admin'  => [
            'label' => 'Joined Team Plan Admin',
        ],
    ];
    return $manifest;
}
