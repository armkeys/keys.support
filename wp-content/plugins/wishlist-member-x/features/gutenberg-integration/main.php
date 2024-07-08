<?php

/**
 * Gutenberg Integration
 *
 * This ensures that our Gutenberg integration is always active.
 *
 * @package WishListMember
 */

// WooCommerce integration is always active.
add_filter(
    'wishlistmember_get_option_active_other_integrations',
    /**
     * Filter for wishlistmember_get_option_active_other_integrations
     * to force the Gutenberg integration to always be enabled.
     *
     * @param  mixed $value Value to filter.
     * @return mixed
     */
    function ($value) {
        // Only run this once per load.
        static $ran = false;
        if ($ran) {
            return $value;
        }
        $ran = true;

        // Add gutenberg to active other integrations.
        if (! in_array('gutenberg', (array) $value, true)) {
            if (! is_array($value)) {
                $value = [];
            }
            $value[] = 'gutenberg';
            wishlistmember_instance()->save_option('active_other_integrations', $value);
        }
        return $value;
    }
);
