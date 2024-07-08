<?php

/**
 * Content Control
 *
 * @package WishListMember/Features
 */

namespace WishListMember\Features;

defined('ABSPATH') || die();

define('WLM_CONTENT_CONTROL_DIR', __DIR__);
define('WLM_CONTENT_CONTROL_FILE', __FILE__);

require_once __DIR__ . '/includes/class-content-control.php';
require_once __DIR__ . '/includes/class-content-scheduler.php';
require_once __DIR__ . '/includes/class-content-archiver.php';
require_once __DIR__ . '/includes/class-content-manager.php';

wishlistmember_instance()->content_control = new Content_Control(wishlistmember_instance());
wishlistmember_instance()->content_control->load_hooks();


add_action(
    'wishlistmember_admin_screen_notices',
    function ($wl) {
        if (wishlistmember_instance()->content_control->old_contentcontrol_active && 'contentcontrol/settings' !== $wl) {
            printf(
                '<div class="form-text text-warning help-block mb-1"><p class="mb-0"><strong>WishList Member:</strong> %s</p></div>',
                esc_html__('Please deactivate WishList Content Control plugin in order to use the WishList Member Content Control feature.', 'wishlist-member')
            );
        }
    }
);

register_activation_hook(WLM_PLUGIN_FILE, [wishlistmember_instance()->content_control, 'activate']);

add_action(
    'wishlistmember_admin_screen',
    function ($wl) {
        $wl = explode('/', $wl);
        if ('contentcontrol' !== $wl[0]) {
            return;
        }
        switch ($wl[1]) {
            case 'settings':
                require __DIR__ . '/views/settings.php';
                wlm_print_script(plugins_url('views/assets/js/settings.js', WLM_CONTENT_CONTROL_FILE));
                break;
            default:
                require __DIR__ . '/views/content.php';
        }
        wlm_print_script(plugins_url('views/assets/js/content.js', WLM_CONTENT_CONTROL_FILE));
    }
);

add_filter(
    'wishlistmember_current_admin_screen',
    function ($wl) {
        $wl_list = explode('/', $wl);
        if ('contentcontrol' !== $wl_list[0] || count($wl_list) < 2 || 'settings' === $wl_list[1]) {
            return $wl;
        }
        $args       = [
            '_builtin' => false,
        ];
        $post_types = get_post_types($args, 'objects');

        $post_types['post'] = 'Posts';
        $post_types['page'] = 'Pages';
        if (array_key_exists($wl_list[1], $post_types)) {
            $wl_list[1] = 'content';
            $wl         = implode('/', $wl_list);
        }

        return $wl;
    },
    1
);


add_filter(
    'wishlist_member_menu',
    function ($menus) {
        $args       = [
            '_builtin' => false,
        ];
        $post_types = get_post_types($args, 'objects');
        if (wishlistmember_instance()->content_control->old_contentcontrol_active) {
            return $menus;
        }
        if (! wishlistmember_instance()->content_control->scheduler && ! wishlistmember_instance()->content_control->archiver && ! wishlistmember_instance()->content_control->manager) {
            // If all three are disabled then let's just add the settings menu/page.
            foreach ($menus as $key => $value) {
                if ('contentcontrol' === wlm_arrval($value, 'key')) {
                    // Settings.
                    $new_menu = [
                        'key'   => 'settings',
                        'name'  => 'Settings',
                        'title' => 'Settings',
                        'icon'  => 'settings',
                        'sub'   => [],
                    ];
                    $old      = $menus[ $key ]['sub'];
                    array_splice($old, 1, 0, [$new_menu]);
                    $menus[ $key ]['sub'] = $old;
                }
            }
            return $menus;
        }

        foreach ($menus as $key => $value) {
            // Add content control.
            if ('contentcontrol' === wlm_arrval($value, 'key')) {
                // Settings.
                $new_menu = [
                    'key'   => 'settings',
                    'name'  => 'Settings',
                    'title' => 'Settings',
                    'icon'  => 'settings',
                    'sub'   => [],
                ];
                $old      = $menus[ $key ]['sub'];
                array_splice($old, 1, 0, [$new_menu]);
                $menus[ $key ]['sub'] = $old;

                // Posts.
                $new_menu = [
                    'key'   => 'post',
                    'name'  => 'Posts',
                    'title' => 'Posts',
                    'icon'  => 'description',
                    'sub'   => [],
                ];
                $old      = $menus[ $key ]['sub'];
                array_splice($old, 2, 0, [$new_menu]);
                $menus[ $key ]['sub'] = $old;
                // Pages.
                $new_menu = [
                    'key'   => 'page',
                    'name'  => 'Pages',
                    'title' => 'Pages',
                    'icon'  => 'description',
                    'sub'   => [],
                ];
                $old      = $menus[ $key ]['sub'];
                array_splice($old, 3, 0, [$new_menu]);
                $menus[ $key ]['sub'] = $old;

                if (count($post_types) > 0) {
                    foreach ($post_types as $k => $v) {
                        $new_menu = [
                            'key'   => "{$k}",
                            'name'  => $v->label,
                            'title' => $v->label,
                            'icon'  => 'description',
                            'sub'   => [],
                        ];
                        $old      = $menus[ $key ]['sub'];
                        array_splice($old, 4, 0, [$new_menu]);
                        $menus[ $key ]['sub'] = $old;
                    }
                }
                break; // Done.
            }
        }

        return $menus;
    }
);
