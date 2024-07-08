<?php

/**
 * Thrive Automator auto-activate
 *
 * @package WishListMember\Features
 */

add_action(
    'shutdown',
    function () {
        $active_integrations   = (array) wishlistmember_instance()->get_option('active_other_integrations');
        $integration_is_active = in_array('thriveautomator', $active_integrations, true);
        $plugin_is_active      = function_exists('thrive_automator_register_app');
        $changed               = true;
        if ($plugin_is_active && ! $integration_is_active) {
            $active_integrations[] = 'thriveautomator';
        } elseif (! $plugin_is_active && $integration_is_active) {
            $active_integrations = array_flip($active_integrations);
            unset($active_integrations['thriveautomator']);
            $active_integrations = array_keys($active_integrations);
        } else {
            $changed = false;
        }
        $changed && wishlistmember_instance()->save_option('active_other_integrations', $active_integrations);
    }
);
