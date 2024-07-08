<?php

/**
 * Team join functions
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

// Short circuit magic page.
if (wlm_arrval(filter_input_array(INPUT_GET), 'wlm-team-accounts-team-registration')) {
    add_filter('wishlistmember_using_magic_page', '__return_true');
}

add_filter('the_content', __NAMESPACE__ . '\join_team_view');
/**
 * Display Team registration form inside of WishList Member magic page
 * Called by 'the_content' filter.
 *
 * @param  string $content Content.
 * @return string Team Registration HTML Markup.
 */
function join_team_view($content)
{
    global $post, $wpdb;
    $invite_key = wlm_arrval(wlm_get_data(true), 'wlm-team-accounts-team-registration');
    if (! $invite_key) {
        return $content;
    }
    if (wishlistmember_instance()->magic_page(false) != $post->ID) {
        return $content;
    }

    $data      = Team_Parent::get_invite_data($invite_key);
    $parent_id = $data['parent_id'];
    $team_id   = $data['team_id'];
    $email     = $data['email'];

    enqueue_frontend_assets();

    // Content.
    ob_start();
    require_once __DIR__ . '/views/frontend/team-join.php';
    return ob_get_clean();
}

add_filter('the_title', __NAMESPACE__ . '\join_team_title', 10, 2);
/**
 * Display Team registration title inside of WishList Member magic page
 * Called by 'the_title' filter
 *
 * @param  string  $title   Post Title.
 * @param  integer $post_id Post ID.
 * @return string Team Management title
 */
function join_team_title($title, $post_id = null)
{
    if (empty($post_id)) {
        return $title;
    }

    $invite_key = wlm_trim(wlm_arrval(wlm_get_data(true), 'wlm-team-accounts-team-registration'));
    if (! $invite_key) {
        return $title;
    }
    if (wishlistmember_instance()->magic_page(false) != $post_id) {
        return $title;
    }

    $data  = Team_Parent::get_invite_data($invite_key);
    $title = sprintf(
        __('Team Registration: %s', 'wishlist-member'),
        Team_Account::get($data['team_id'])->name
    );
    return $title;
}

add_action('wp_ajax_wishlistmember_team_accounts_register', __NAMESPACE__ . '\join_team');
add_action('wp_ajax_nopriv_wishlistmember_team_accounts_register', __NAMESPACE__ . '\join_team');
/**
 * Team registration
 * Called by 'wp_ajax_wishlistmember_team_accounts_register' hook
 * Called by 'wp_ajax_nopriv_wishlistmember_team_accounts_register' hook
 *
 * @uses join_team_existing()
 * @uses join_team_new()
 */
function join_team()
{
    global $wpdb;
    $bad_request = ['message' => __('Bad request.', 'wishlist-member')];
    $post        = filter_input_array(INPUT_POST);

    // No post.
    $post || wp_send_json_error($bad_request);

    verify_wlm_nonces();
    // Invalid nonce.
    WLM_POST_NONCED || wp_send_json_error($bad_request);

    // Trim post data.
    $post = array_map('trim', $post);

    $user_id = false;
    if (wlm_arrval($post, 'existing-user')) {
        // Existing user. verify credentials.
        $user = wp_authenticate_email_password(null, wlm_arrval($post, 'email'), wlm_arrval($post, 'password'));
        // Invalid credentials.
        is_wp_error($user) && wp_send_json_error(['message' => __('Incorrect password.', 'wishlist-member')]);
        $user_id = $user->ID;
        unset($user);
    } elseif (wlm_arrval($post, 'new-user')) {
        // Username checking.
        ! wlm_arrval($post, 'username') && wp_send_json_error(['message' => __('Please enter a username', 'wishlist-member')]);

        // Password length checking.
        $min_password_length   = wlm_or((int) wishlistmember_instance()->get_option('min_passlength'), 8);
        $password_length_error = sprintf(__('Password has to be at least %1$d characters long and must not contain spaces.', 'wishlist-member'), $min_password_length);
        strlen(wlm_arrval($post, 'password')) < $min_password_length && wp_send_json_error(['message' => $password_length_error]);

        // Password strength checking.
        wishlistmember_instance()->get_option('strongpassword') && ! wlm_check_password_strength(wlm_arrval($post, 'password')) && wp_send_json_error(['message' => __('Please provide a strong password. Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.', 'wishlist-member')]);

        // New user. add user.
        $user_id = wp_insert_user(
            [
                'first_name' => wlm_arrval($post, 'first_name'),
                'last_name'  => wlm_arrval($post, 'last_name'),
                'user_email' => wlm_arrval($post, 'email'),
                'user_login' => wlm_arrval($post, 'username'),
                'user_pass'  => wlm_arrval($post, 'password'),
            ]
        );
        // Failed to add.
        is_wp_error($user_id) && wp_send_json_error(['message' => $user_id->get_error_messages()]);
    }

    // No user id.
    $user_id || wp_send_json_error($bad_request);

    // Get team's parent ID.
    $invite_found = in_array(
        wlm_arrval($post, 'join_key'),
        (array) get_user_meta(wlm_arrval($post, 'parent_id'), 'team-accounts/invite-key/' . wlm_arrval($post, 'team_id') . '/' . strtolower((string) ( wlm_arrval($post, 'email') ?? '' ))),
        true
    );

    $invite_found || wp_send_json_error(['message' => __('The team you are trying to join does not exist.', 'wishlist-member')]);

    $parent = new Team_Parent(wlm_arrval($post, 'parent_id'));

    // Delete invites.
    $parent->delete_team_invite(wlm_arrval($post, 'team_id'), wlm_arrval($post, 'email'));

    // Add to team.
    $parent->add_children(wlm_arrval($post, 'team_id'), $user_id);

    // Log the user in.
    wp_signon(
        [
            'user_login'    => wlm_arrval($post, 'email'),
            'user_password' => wlm_arrval($post, 'password'),
        ]
    );

    $redirect = add_query_arg(
        [
            'sp'        => 'wlmta-join',
            'team_id'   => wlm_arrval($post, 'team_id'),
            'parent_id' => wlm_arrval($post, 'parent_id'),
        ],
        wishlistmember_instance()->magic_page()
    );
    // Yay!
    wp_send_json_success(['redirect' => $redirect]);
}

add_filter('wishlistmember_custom_error_page', __NAMESPACE__ . '\team_join_welcome_page', 10, 2);
/**
 * Team member welcome message
 * Called by 'wishlistmember_custom_error_page' filter.
 *
 * @param  string $content Content.
 * @param  string $sp      Requested special page.
 * @return string
 */
function team_join_welcome_page($content, $sp)
{
    if ('wlmta-join' !== $sp) {
        return $content;
    }
    return wlm_or(wishlistmember_instance()->get_option('team-accounts/member-join-message'), default_member_join_message());
}

add_shortcode('wlm_joined_team_plan_name', __NAMESPACE__ . '\joined_team_plan_name');
/**
 * Shortcode handler for [wlm_joined_team_plan_name]
 * Returns name of latest joined team.
 *
 * @return string
 */
function joined_team_plan_name()
{
    return joined_team_shortcode_data('team_name');
}
add_shortcode('wlm_joined_team_plan_admin', __NAMESPACE__ . '\joined_team_plan_admin');
/**
 * Shortcode handler for [wlm_joined_team_plan_admin]
 * Returns name of latest joined team's admin.
 *
 * @return string
 */
function joined_team_plan_admin()
{
    return joined_team_shortcode_data('parent_name');
}

/**
 * Get joined team data.
 *
 * @param  string $property Property to return. Can be 'team_name' or 'parent_name'.
 * @return string
 */
function joined_team_shortcode_data($property)
{
    $teams = wlm_or(( new Team_Child(get_current_user_id()) )->teams, []);
    if (count($teams)) {
        $team = array_pop($teams); // Newest is last.
        switch ($property) {
            case 'team_name':
                return Team_Account::get($team['team_id'])->name;
                break;
            case 'parent_name':
                $u = get_user_by('id', $team['parent_id']);
                if ($u) {
                    return $u->display_name;
                } else {
                    return '';
                }
                break;
            default:
                return '';
        }
    } else {
        return __('No Teams', 'wishlist-member');
    }
}
