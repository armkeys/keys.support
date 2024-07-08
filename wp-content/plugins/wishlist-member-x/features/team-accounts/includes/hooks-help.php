<?php

/**
 * Beta Stickers and MEssages
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

// Beta menu sticker.
// Add_action(
// 'admin_enqueue_scripts',
// Function() {
// Wp_add_inline_style(
// 'wishlistmember3-combined-styles',
// '[data-target="#member-edit-team-accounts"]:after,#wlm3-sidebar a[href*="wl=setup%2Fteam-accounts%2Fplans"]:after{
// Content: "NEW";
// Color: goldenrod;
// Font-weight: bold;
// Font-size: 9px;
// Vertical-align: middle;
// Padding: 2px 7px;
// Margin-left: 5px;
// Border: 1px solid goldenrod;
// Border-radius:10px;
// }'
// );
// }
// );
// Show get help message.
add_action('wishlistmember_admin_screen_notices', __NAMESPACE__ . '\help_message');

/**
 * Display get help message.
 * Called by 'wishlistmember_admin_screen_notices' hook.
 *
 * @param  string $wl Current WLM page being viewed.
 * @return void
 */
function help_message($wl)
{
    if (get_user_meta(get_current_user_id(), 'wishlistmember_team-accounts-get-help_dismiss')) {
        return;
    }
    if ('wishlistmember_member_edit_tab_pane-team-accounts' === current_action() || preg_match('#setup/team\-accounts/(plans|settings|email\-template|css)#', $wl)) {
        printf(
            '<div id="team-accounts-get-help" class="form-text text-info help-block mb-3 is-dismissible"><p class="mb-0"><a href="%s" target="_blank">%s</a></p><button type="button" class="notice-dismiss" title="Dismiss this notice"></button></div>',
            esc_url('https://wishlistmember.com/docs/team-accounts/'),
            esc_html__('Click here for more information about Team Accounts.', 'wishlist-member')
        );
    }
}

/**
 * Enqueue script to dismiss get help message.
 */
add_action(
    'admin_enqueue_scripts',
    function () {
        wp_add_inline_script(
            'wishlistmember3-combined-scripts-footer',
            'jQuery(document).on("click", "#team-accounts-get-help .notice-dismiss", function() { jQuery.post(ajaxurl, {action: "wishlistmember_team-accounts-get-help_dismiss"})});'
        );
    }
);

/**
 * Ajax handler to dismiss get help message.
 */
add_action(
    'wp_ajax_wishlistmember_team-accounts-get-help_dismiss',
    function () {
        update_user_meta(get_current_user_id(), 'wishlistmember_team-accounts-get-help_dismiss', 1);
        wp_send_json_success();
    }
);
