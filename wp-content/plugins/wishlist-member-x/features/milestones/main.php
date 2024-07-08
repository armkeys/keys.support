<?php

/**
 * Milestones
 *
 * @package WishListMember\Features\Milestones
 */

namespace WishListMember\Features\Milestones;

/**
 * Display congrats notice if site has 50 members or more.
 * Called by 'wishlistmember_admin_screen_notices' and 'admin_notices' hooks
 */
function members_reached_50()
{
    if (! current_user_can('manage_options')) {
        return;
    }
    $x = new \WishListMember\User_Search(
        [
            'howmany'      => 1,
            'more_filters' => ['status' => 'active'],
        ]
    );
    if ($x->get_total() < 50) {
        return;
    }

    $msg = __('Congratulations! It looks like your membership site is doing very well. If you are interested in sharing more about how you are building and growing your membership site, you can <a href="https://wishlistmember.com/submit-case-study/" target="_blank">get more details right here</a>.', 'wishlist-member');
    switch (current_action()) {
        case 'admin_notices':
            echo '<div id="members-reached-50" class="notice notice-info is-dismissible"><p>' . wp_kses_post($msg) . '</p></div>';
            break;
        default:
            echo '<div id="members-reached-50" class="form-text text-info help-block mb-1 is-dismissible"><p class="mb-0">' . wp_kses_post($msg) . '</p></div>';
    }

    wp_enqueue_script('wishlistmember-milestones-members-50', plugin_dir_url(__FILE__) . '/members_50.js', [], WLM_PLUGIN_VERSION, true);
}

/**
 * Dismiss the members members_reached_50 message.
 * Called by 'wp_ajax_wishlistmember_milestones_members_reached_50_dismiss' hook.
 */
function members_reached_50_dismiss()
{
    if (! current_user_can('manage_options')) {
        wp_send_json_error();
    }
    update_option('wishlistmember_members_reached_50_dismissed', wlm_date());
}

if (! get_option('wishlistmember_members_reached_50_dismissed')) {
    add_action('wishlistmember_admin_screen_notices', __NAMESPACE__ . '\members_reached_50');
    add_action('admin_notices', __NAMESPACE__ . '\members_reached_50');
    add_action('wp_ajax_wishlistmember_milestones_members_reached_50_dismiss', __NAMESPACE__ . '\members_reached_50_dismiss');
}
