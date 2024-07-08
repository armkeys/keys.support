<?php

/**
 * Member Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Member Methods trait
 */
trait Script_Translations
{
    public function set_script_translations()
    {
        static $loaded = [];
        global $wp_scripts;
        $url = $this->plugin_url3;
        foreach ($wp_scripts->registered as $s) {
            if (0 === strpos($s->src, $url) && ! in_array($s->handle, $loaded)) {
                $loaded[] = $s->handle;
                wp_set_script_translations($s->handle, 'wishlist-member', WLM_PLUGIN_DIR . '/lang/');
            }
        }
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wp_print_scripts', [$wlm, 'set_script_translations'], PHP_INT_MAX);
    }
);
