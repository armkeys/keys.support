<?php

/**
 * Protected RSS Feed
 *
 * @package WishListMember/Features
 */

namespace WishListMember\Features\Protected_RSS;

add_filter('wishlistmember_verify_feed_key', __NAMESPACE__ . '\verify_feed_key', 10, 2);
/**
 * Verifies the protected RSS feed key
 *
 * @param  integer $user_id User ID.
 * @param  string  $feedkey Feed key.
 * @return integer
 */
function verify_feed_key($user_id, $feedkey)
{
    list($id) = explode(';', $feedkey);
    return wishlistmember_instance()->feed_key($id) === $feedkey ? $id : $user_id;
}

add_action('wishlistmember_member_edit_tab_pane-member-advance', __NAMESPACE__ . '\member_edit_rss_feed_field');
/**
 * Display RSS feed field in member edit modal
 * Called by 'wishlistmember_member_edit_tab_pane-member-advance' hook.
 *
 * @param  object $profileuser User object of user being edited.
 * @return void
 */
function member_edit_rss_feed_field($profileuser)
{
    require __DIR__ . '/views/member-edit-rss-feed-field.php';
}
