<?php

/**
 * Tutor LMS Compatibility with WishList Member Post Inheritance.
 *
 * @package WishListMember/Compatibility
 */

/**
 * Filter name to be used for sub-posts when displayed in the post/page edit screen
 */
add_filter(
    'wishlistmember_post_inheritance_child_name',
    function ($child_name, $post_type) {
        // Get active plugins.
        $active_plugins = wlm_get_active_plugins();

        // Begin: handling for Tutor LMS.
        if (in_array('Tutor LMS', $active_plugins, true)) {
            $new_name = [];
            switch ($post_type->name) {
                case 'courses':
                    $new_name[] = 'Topics';
                    // Continue to "topics".
                case 'topics':
                    $new_name[] = 'Lessons';
                    // Continue to default.
                default:
                    if ($new_name) {
                        // Generate the new name.
                        if (count($new_name) > 1) {
                            $new_name[] = 'and ' . array_pop($new_name);
                            $child_name = str_replace(', and ', ' and ', implode(', ', $new_name));
                        } else {
                            list( $child_name ) = $new_name;
                        }
                    }
            }
        }
        // End: handling for Tutor LMS.
        // Return child name.
        return $child_name;
    },
    10,
    2
);
