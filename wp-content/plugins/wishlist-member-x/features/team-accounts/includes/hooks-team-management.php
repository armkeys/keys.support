<?php

/**
 * Team management functions
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

add_shortcode('wlm_managed_team_plan_name', __NAMESPACE__ . '\managed_team_plan_name');
/**
 * Shortcode handler for [wlm_managed_team_plan_name].
 * Return name of latest managed team.
 *
 * @return string
 */
function managed_team_plan_name()
{
    $p = Team_Parent::current_user()->teams_grouped();
    if (count($p)) {
        return current($p)[0]['name'];
    } else {
        return __('No Team Plans', 'wishlist-member');
    }
}
add_shortcode('wlm_team_management', __NAMESPACE__ . '\team_management_shortcode');
/**
 * Shortcode handler for [wlm_team_management].
 *
 * @uses   team_management_markup()
 * @param  array $atts Shortcode attributes.
 * @return string Team Management HTML Markup.
 */
function team_management_shortcode($atts)
{
    if (! is_user_logged_in()) {
        // Show login form using WLM's [wlm_loginform] shortcode if not logged-in.
        return do_shortcode(
            sprintf(
                '[wlm_loginform redirect="%s"]',
                esc_attr(get_permalink(wishlistmember_instance()->get_option('team-accounts/team_management_page')))
            )
        );
    }

    if (! Team_Parent::current_user()->teams) {
        return sprintf('<p>%s</p>', __('Only team admins can manage team accounts.', 'wishlist-member'));
    }

    return team_management_markup();
}

/**
 * Return team management HTML markup.
 *
 * @return string Team Management HTML Markup.
 */
function team_management_markup()
{
    enqueue_frontend_assets();
    // Content.
    ob_start();
    require_once __DIR__ . '/views/frontend/team-management.php';
    return preg_replace('/(type="search".*?)required/', '\1', do_blocks(ob_get_clean()));
}

add_action('wp_ajax_wishlistmember_team_accounts_remove_user_member', __NAMESPACE__ . '\remove_member');
/**
 * Ajax handler for removing a team member
 * Called by 'wp_ajax_wishlistmember_team_accounts_remove_user_member' hook
 */
function remove_member()
{
    get_current_user_id() || wp_send_json_error();
    verify_wlm_nonces();
    if (! WLM_POST_NONCED) {
        wp_send_json_error();
    }
    $data = filter_input_array(INPUT_POST);

    $member_id = wlm_arrval($data, 'member_id');
    $team_id   = wlm_arrval($data, 'team_id');
    if ($member_id) {
        // Remove from team.
        Team_Parent::current_user()->remove_children($team_id, $member_id);

        // Remove invite by current user.
        if (is_numeric($member_id)) {
            $email = get_user_by('ID', $member_id);
            $email = is_object($email) ? $email->user_email : '0';
        } else {
            $email = $member_id;
        }
        Team_Parent::current_user()->delete_team_invite($team_id, $email);
    }
    wp_send_json_success();
}

add_action('wp_ajax_wishlistmember_team_accounts_search_member', __NAMESPACE__ . '\search_member');
/**
 * Ajax handler for team member search.
 * Called by 'wp_ajax_wishlistmember_team_accounts_search_member' hook.
 */
function search_member()
{
    verify_wlm_nonces();
    if (! WLM_POST_NONCED) {
        wp_send_json_error();
    }
    $data = filter_input_array(INPUT_POST);

    $user_id = wlm_or(wlm_arrval($data, 'user_id'), get_current_user_id());
    $user_id || wp_send_json_error();

    $team_members = ( new Team_Parent($user_id) )->search_members(wlm_arrval($data, 'team_id'), wlm_arrval($data, 'search'));
    $row_markup   = require __DIR__ . '/views/frontend/team-management-table-row.php';
    foreach ($team_members as &$member) {
        $member['html'] = sprintf(
            $row_markup,
            $member['id'],
            $member['name'],
            $member['email'],
            $member['active'] ? __('Active', 'wishlist-member') : __('Invited', 'wishlist-member'),
            (int) $member['active']
        );
    }
    unset($member);
    wp_send_json_success(['members' => $team_members]);
}

add_action('wp_ajax_wishlistmember_team_accounts_invite_member', __NAMESPACE__ . '\invite_member');
/**
 * Ajax handler for sending a team invite email
 * Called by 'wp_ajax_wishlistmember_team_accounts_invite_member' hook.
 */
function invite_member()
{
    get_current_user_id() || wp_send_json_error();
    verify_wlm_nonces();
    if (! WLM_POST_NONCED) {
        wp_send_json_error();
    }
    $data    = filter_input_array(INPUT_POST);
    $email   = wlm_arrval($data, 'email');
    $team_id = wlm_arrval($data, 'team_id');

    // Validate email.
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        wp_send_json_error(['message' => __('Invalid email format', 'wishlist-member')]);
    }

    // Check if email is already a team member.
    $child = new Team_Child(wlm_arrval(get_user_by('email', $email), 'ID'));
    if ($child->teams) {
        if (isset($child->teams[ get_current_user_id() ])) {
            // Already a team member.
            wp_send_json_error(
                [
                    // Translators: %s Invited email address.
                    'message' => sprintf(__('%s is already a member of your team', 'wishlist-member'), $email),
                ]
            );
        }

        // Already a member of another team.
        wp_send_json_error(
            [
                // Translators: %s Invited email address.
                'message' => sprintf(__('%s is already a member of another team', 'wishlist-member'), $email),
            ]
        );
    }

    $invited = wlm_or(Team_Parent::current_user()->get_team_invites($team_id), []);
    if (in_array($email, $invited, true)) {
        // Invite already sent.
        wp_send_json_error(
            [
                // Translators: %s Invited email address.
                'message' => sprintf(__('You have already sent an invite to %s', 'wishlist-member'), $email),
            ]
        );
    }

    if (Team_Parent::current_user()->send_team_invite($team_id, $email)) {
        wp_send_json_success(
            [
                // Translators: %s Invited email address.
                'message' => sprintf(__('Invite sent to %s', 'wishlist-member'), $email),
            ]
        );
    } else {
        wp_send_json_error(
            [
                'message' => __('Not enough spots on your team.', 'wishlist-member'),
            ]
        );
    }
}

add_action('get_pages', __NAMESPACE__ . '\hide_team_management_page');

/**
 * Hide Team Management Page on frontend for non-team admins
 *
 * @param  array $pages Array of page objects
 * @return array
 */
function hide_team_management_page($pages)
{
    static $team_management_page;
    if (is_admin()) {
        return $pages;
    }
    if (false && current_user_can('manage_options') || Team_Parent::current_user()->teams) {
        return $pages;
    }
    $team_management_page = wlm_or($team_management_page, wishlistmember_instance()->get_option('team-accounts/team_management_page'));
    return array_filter(
        $pages,
        function ($x) use ($team_management_page) {
            return $x->ID != $team_management_page;
        }
    );
}

/**
 * Enqueue frontend assets
 */
function enqueue_frontend_assets()
{
    // Enqueue style.
    wp_enqueue_style(
        'wlm-team-accounts-team-management',
        plugin_dir_url(WLM_PRO_TEAM_ACCOUNTS_FILE) . 'assets/css/team-management.css',
        ['wlm3_form_css'],
        WLM_PLUGIN_VERSION
    );
    $custom_css_enabled = (bool) wishlistmember_instance()->get_option('team-accounts/custom-css-enabled');
    $custom_css         = trim((string) wishlistmember_instance()->get_option('team-accounts/custom-css'));
    if ($custom_css_enabled && $custom_css) {
        wp_add_inline_style('wlm-team-accounts-team-management', $custom_css);
    }

    // Enqueue scripts.
    wp_enqueue_script(
        'wlm-team-accounts-team-management',
        plugin_dir_url(WLM_PRO_TEAM_ACCOUNTS_FILE) . 'assets/js/team-management.js',
        ['underscore', 'wp-i18n', 'jquery-ui-dialog'],
        WLM_PLUGIN_VERSION,
        true
    );
    $data = [
        'nonce_field'      => get_wlm_nonce_field_name(),
        'nonce'            => get_wlm_nonce(),
        'ajaxurl'          => admin_url('admin-ajax.php'),
        'registration_url' => home_url(),
    ];
    wp_add_inline_script('wlm-team-accounts-team-management', 'var wlm_teamaccounts = ' . wp_json_encode($data));
}

/**
 * Return kses allowed html for use with wp_kses() function
 *
 * @return array
 */
function kses_allowed_html()
{
    return [
        'div'   => ['class' => true],
        'label' => ['for' => true],
        'input' => [
            'name'        => true,
            'value'       => true,
            'type'        => true,
            'placeholder' => true,
            'id'          => true,
            'class'       => true,
        ],
    ];
}
