<?php

/**
 * BuddyPress compatibility code
 *
 * @package WishListMember/Compatibility
 */

/*
 * Don't run redirect_canonical on BP profile pages.
 */
add_filter(
    'redirect_canonical',
    function ($redirect_url) {
        if (
            ( function_exists('bp_is_my_profile') && bp_is_my_profile() ) ||
            ( function_exists('bp_is_current_component') && ! bp_is_current_component() )
        ) {
            if (empty(wlm_get_data()['p'])) {
                return false;
            }
        }
        return $redirect_url;
    }
);

/*
 * Remove Email Templates, Profile Types and Group Types on WLM's menu since WLM protection doesn't really work for these BB custom post types.
 */
add_filter(
    'wishlistmember_excluded_post_types',
    function ($excluded_post_types) {
        if (is_admin()) {
            if (true || function_exists('bp_current_component')) {
                $excluded_post_types[] = 'bp-member-type';
                $excluded_post_types[] = 'bp-group-type';
                $excluded_post_types[] = 'bp-email';
            }
        }
        return $excluded_post_types;
    }
);

/**
 * Check whether the current logged in user can access the $page_id sepcified.
 *
 * @param  integer $page_id Page ID.
 * @return string STOP or NOACCESS
 */
function wlm_buddypress_compat_member_can_access($page_id)
{
    static $wpm_levels;
    /**
     * If the $page_id is not protected then let the user view it.
     * If it's protected then see if the logged in user can access it.
     */
    if (wishlistmember_instance()->protect($page_id)) {
        if (is_user_logged_in()) {
            if (! $wpm_levels) {
                $wpm_levels = (array) wishlistmember_instance()->get_option('wpm_levels');
            }

            // Get levels that has access to the protected page.
            $levels_with_access = wishlistmember_instance()->get_content_levels('posts', $page_id);

            // Get levels of the logged in user and see if any of them has access to the page.
            $user_levels = new \WishListMember\User(get_current_user_id());

            $levels = array_intersect($user_levels->active_levels, $levels_with_access);

            foreach ($user_levels->active_levels as $user_level) {
                // If one of user's level has access to all pages then just return.
                if (isset($wpm_levels[ $user_level ]['allpages']) || in_array($user_level, $levels_with_access)) {
                    return 'STOP';
                }
            }
            return 'NOACCESS';
        }
        return 'NOACCESS';
    }
    return 'STOP';
}

/*
 * Compatibiltiy fix for BuddyPress members and groups.
 */
add_filter(
    'wishlistmember_process_protection',
    function ($redirect) {
        global $wp_query, $bp, $wp;

        // Let's check protection for the page set as forums page in BuddyBoss.
        if (function_exists('bbp_get_forums_url')) {
            $current_page = home_url(add_query_arg([], $wp->request)) . '/';
            if (bbp_get_forums_url() === $current_page) {
                $forum_page_id = get_option('_bbp_root_slug_custom_slug');
                if ($forum_page_id) {
                    return wlm_buddypress_compat_member_can_access($forum_page_id);
                }
            }
        }

        // BuddyBoss compatibility for component pages.
        if (function_exists('bp_is_user') && function_exists('bp_is_group')) {
            // Don't run protection when current page is the currently logged in users profile page.
            if (function_exists('bp_is_my_profile')) {
                if (bp_is_my_profile()) {
                    return 'STOP';
                }
            }

            if (bp_is_user() || bp_is_members_directory()) {
                $bp_content_slug = $bp->pages->members->slug;
            } elseif (bp_is_group()) {
                $bp_content_slug = $bp->pages->groups->slug;
            }

            if (empty($bp_content_slug)) {
                if (bp_is_current_component('groups')) { // Check if current page is the groups component page.
                    $bp_content_slug = $bp->pages->groups->slug;
                } elseif (bp_is_current_component('activity')) {
                    $bp_content_slug = $bp->pages->activity->slug;
                }
            }

            if (! empty($bp_content_slug)) {
                /**
                 * Get the page ID of the component page for BuddyPress. (ie. Groups/Activity/Members's directory page)
                 */
                $page_id = (int) wlm_arrval(get_option('bp-pages'), $bp_content_slug);

                // For BuddyBoss.
                if (empty($page_id)) {
                    $wp_query2 = new WP_Query(['pagename' => $bp_content_slug]);

                    if (empty($wp_query2->post->ID)) {
                        $page_id = $wp_query2->queried_object->ID;
                    } else {
                        $page_id = $wp_query2->post->ID;
                    }
                }

                return wlm_buddypress_compat_member_can_access($page_id);
            }
        }

        return $redirect; // Return $redirect to allow for chaining.
    },
    10,
    3
);

/**
 * If BuddyPress/BuddyBoss is active then don't run this function on topics/discussion pages
 * as the $content->query_vars['post__not_in'] hides the first reply when it contains at least one post ID.
 */
add_filter(
    'wishlistmember_only_show_content_for_level',
    function ($content) {
        if (function_exists('bp_current_component')) {
            $bp_topic_slug = get_option('_bbp_topic_slug');
            if ($bp_topic_slug) {
                if (false !== strpos(wlm_server_data()['REQUEST_URI'], $bp_topic_slug)) {
                    return false;
                }
            }
        }
        return $content;
    }
);

/**
 * Inherit protection from parent topic/forum
 */
add_action(
    'save_post',
    function ($post_id, $post, $update) {
        // Only proceed if the custom post type is added (not updated).
        if (! $update && ! is_admin()) {
            // Also, only process if it's on the frontend.
            // For now only process BBPress Topics and replies created by members in the front end.
            if (isset(wlm_post_data()['action']) && ( 'bbp-new-topic' === wlm_post_data()['action'] || 'bbp-new-reply' === wlm_post_data()['action'] )) {
                $post_type = get_post_type($post_id);

                if ('reply' === $post_type) {
                    $lvls_that_have_access = wishlistmember_instance()->get_content_levels('topic', wlm_post_data()['bbp_topic_id']);
                } else {
                    $lvls_that_have_access = wishlistmember_instance()->get_content_levels('forum', wlm_post_data()['bbp_forum_id']);
                }

                // Add the levels the parent forum have.
                wishlistmember_instance()->set_content_levels($post_type, $post_id, $lvls_that_have_access);

                if (wishlistmember_instance()->protect(wlm_post_data()['bbp_forum_id'])) {
                    // Protect the Topic or reply if parent forum is protected.
                    wishlistmember_instance()->protect($post_id, true);
                }
            }
        }
    },
    10,
    3
);
