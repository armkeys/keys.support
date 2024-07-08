<?php

/**
 * File Protection feature
 *
 * @package WishListMember/Features
 */

namespace WishListMember\Features\File_Protection;

require_once 'includes/class-file-protection-methods.php';
add_filter(
    'wishlistmember_instance_methods',
    function ($methods) {
        $object         = new File_Protection_Methods();
        $object_methods = get_class_methods($object);
        foreach ($object_methods as $method) {
            $methods[ $method ] = [[$object, $method]];
        }
        return $methods;
    }
);

add_filter(
    'wishlist_member_submenu',
    function ($menu, $key) {
        if ('content_protection' === $key) {
            // Insert file protection menu after folders.
            $menu_item = [
                [
                    'key'   => 'files',
                    'name'  => 'Files',
                    'title' => 'Files',
                    'icon'  => 'attach_file',
                    'sub'   => [],
                ],
            ];
            $keys      = array_flip(array_column($menu, 'key'));
            $position  = isset($keys['folders']) ? $keys['folders'] : count($menu);
            array_splice($menu, $position + 1, 0, $menu_item);
        }
        return $menu;
    },
    10,
    2
);

add_action(
    'wishlistmember_admin_screen',
    function ($screen) {
        if ('content_protection/files' === $screen) {
            require_once 'includes/views/files.php';
        }
    }
);

add_filter(
    'wishlistmember_admin_screen_js',
    function ($js_url, $screen) {
        if ('content_protection/files' === $screen) {
            return plugin_dir_url(__FILE__) . 'includes/views/files.js';
        }
        return $js_url;
    },
    10,
    2
);
