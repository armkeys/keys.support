<?php

/**
 * Compatibility for Elementor plugin
 *
 * @package WishListMember/Compatibility
 */

/**
 * Fix for issue where editing a page that has the WLM profile_form shortcode stops the elementor edit page from loading.
 */
add_filter(
    'wishlistmember_profile_form_shortcode_include_script_markup',
    function () {

        if (!class_exists('\Elementor\Plugin')) {
            return true;
        }

        // Let's not load the JS markup if the page is in Elementor Edit Mode.
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return false;
        }

        return true;
    },
    10
);
