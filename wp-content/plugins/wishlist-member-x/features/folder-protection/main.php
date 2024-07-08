<?php

/**
 * Folder Protection feature
 *
 * @package WishListMember/Features
 */

namespace WishListMember\Features\Folder_Protection;

require_once 'includes/class-folder-protection-methods.php';
add_filter(
    'wishlistmember_instance_methods',
    function ($methods) {
        $object         = new Folder_Protection_Methods();
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
            // Insert folder protection menu before files.
            $menu_item = [
                [
                    'key'   => 'folders',
                    'name'  => 'Folders',
                    'title' => 'Folders',
                    'icon'  => 'folder',
                    'sub'   => [],
                ],
            ];
            $keys      = array_flip(array_column($menu, 'key'));
            $position  = isset($keys['files']) ? $keys['files'] : count($menu);
            array_splice($menu, $position, 0, $menu_item);
        }
        return $menu;
    },
    10,
    2
);

add_action(
    'wishlistmember_admin_screen',
    function ($screen) {
        if ('content_protection/folders' === $screen) {
            require_once 'includes/views/folders.php';
        }
    }
);

add_filter(
    'wishlistmember_admin_screen_js',
    function ($js_url, $screen) {
        if ('content_protection/folders' === $screen) {
            return plugin_dir_url(__FILE__) . 'includes/views/folders.js';
        }
        return $js_url;
    },
    10,
    2
);

add_action(
    'wishlistmember_update_content_protection_content_item',
    function ($content_type, $content_id) {
        static $folder_items;
        if ('folders' === $content_type) {
            if (is_null($folder_items)) {
                $folder_items                = [];
                $root_of_folders             = wlm_trim(wishlistmember_instance()->get_option('rootOfFolders'));
                $folder_protection_full_path = wishlistmember_instance()->folder_protection_full_path($root_of_folders);
                if ($root_of_folders && is_dir($folder_protection_full_path)) {
                    foreach (glob($folder_protection_full_path . '/*', GLOB_ONLYDIR) as $dir_name) {
                        $item     = [];
                        $dir_name = basename($dir_name);
                        $fullpath = $folder_protection_full_path . '/' . $dir_name;
                        if (is_dir($fullpath)) {
                            $folder_id                  = wishlistmember_instance()->folder_id($dir_name);
                            $item['full_path']          = $fullpath;
                            $item['post_title']         = basename($fullpath);
                            $item['writable']           = is_writable($fullpath);
                            $item['htaccess_exists']    = file_exists($fullpath . '/.htaccess');
                            $item['htaccess_writable']  = is_writable($fullpath . '/.htaccess');
                            $item['wlm_protection']     = [wishlistmember_instance()->folder_protected($folder_id)];
                            $item['force_download']     = wishlistmember_instance()->folder_force_download($folder_id);
                            $item['ID']                 = $folder_id;
                            $folder_items[ $folder_id ] = $item;
                        }
                    }
                }
            }
            $item = $folder_items[ $content_id ];
            require 'includes/views/folders/content-item.php';
        }
    },
    10,
    2
);
