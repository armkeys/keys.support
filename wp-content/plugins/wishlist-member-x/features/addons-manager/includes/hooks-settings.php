<?php

/**
 * Settings
 *
 * @package WishListMember/Features/Addons_Manager
 */

namespace WishListMember\Features\Addons_Manager;

add_filter('wishlist_member_menu', __NAMESPACE__ . '\settings_menu', 1);
/**
 * Add Add-ons Manager menu
 * Called by 'wishlist_member_menu' hook
 *
 * @param  array $menus WishList Member menu items.
 * @return array
 */
function settings_menu($menus)
{
    $menus[] = [
        'key'   => 'addons',
        'name'  => __('Add-ons', 'wishlist-member'),
        'title' => __('WishList Member Add-ons', 'wishlist-member'),
        'icon'  => 'open_in_browser',
        'sub'   => [],
    ];
    return $menus;
}

add_action('wishlistmember_pre_admin_screen', __NAMESPACE__ . '\settings_scripts_and_styles', 10);
/**
 * Load view styles and scripts.
 * Called by 'wishlistmember_pre_admin_screen' hook.
 *
 * @param string $wl Current view being requested.
 */
function settings_scripts_and_styles($wl)
{
    $css_handle = 'wishlist-member-addons-manager';
    $css_file   = __DIR__ . '/views/admin/settings.css';
    wp_enqueue_style(
        $css_handle,
        plugins_url(basename($css_file), $css_file),
        [],
        WLM_PLUGIN_VERSION
    );
    $js_handle = 'wishlist-member-addons-manager';
    $js_file   = __DIR__ . '/views/admin/settings.js';
    wp_enqueue_script(
        $js_handle,
        plugins_url(basename($js_file), $js_file),
        [],
        WLM_PLUGIN_VERSION,
        false
    );
    wp_print_styles($css_handle);
    wp_print_scripts($js_handle);
}

add_action('wishlistmember_admin_screen', __NAMESPACE__ . '\load_settings_screens');
/**
 * Display admin views
 * Called by 'wishlistmember_admin_screen' hook
 *
 * @param string $wl Current view being requested.
 */
function load_settings_screens($wl)
{
    switch ($wl) {
        case 'addons':
            require_once __DIR__ . '/views/admin/settings.php';
            break;
    }
}

add_action('wp_ajax_wlm_toggle_enable_addon', __NAMESPACE__ . '\toggle_enable_addon');
/**
 * `wp_ajax_wlm_toggle_enable_addon` handler to enable/disable an add-on.
 */
function toggle_enable_addon()
{
    $addon_file = wlm_or(wlm_arrval(wlm_post_data(true), 'addon_file'), '');
    $enable     = (int) wlm_or(wlm_arrval(wlm_post_data(true), 'enable'), '');
    $array_ret  = ['enabled' => $enable];
    if (1 === $enable) {
        $is_coursecure_active = \WishListMember\Features\Addons_Manager\Addons::instance()->is_coursecure_active();
        if (
            $is_coursecure_active &&
            (
                'wishlist-member-badges/main.php' === $addon_file ||
                'wishlist-member-points/main.php' === $addon_file ||
                'coursecure-courses/main.php' === $addon_file ||
                'coursecure-quizzes/main.php' === $addon_file
            )
        ) {
            $array_ret['message'] = __('Please deactivate the CourseCure plugin', 'wishlist-member');
            wp_send_json_error($array_ret);
            exit();
        } else {
            $ret = \WishListMember\Features\Addons_Manager\Addons::instance()->activate_addon($addon_file);
            if (! is_wp_error($ret)) {
                $array_ret['message'] = 'Addon enabled';
                wp_send_json_success($array_ret);
            } else {
                $array_ret['message'] = 'An error occured while enabling the addon. ' . $ret->get_error_message();
                wp_send_json_error($array_ret);
            }
        }
    } else {
        if (\WishListMember\Features\Addons_Manager\Addons::instance()->deactivate_addon($addon_file)) {
            $array_ret['message'] = 'Addon disabled';
            wp_send_json_success($array_ret);
        } else {
            // Prepare this part in case we change the deactivate_addon later.
            $array_ret['message'] = 'An error occured while disabling the addon';
            wp_send_json_error($array_ret);
        }
    }
}

add_action('wp_ajax_wlm_install_addon', __NAMESPACE__ . '\install_addon');
/**
 * `wp_ajax_wlm_install_addon` handler to install an add-on.
 */
function install_addon()
{
    $addon_url = isset(wlm_post_data()['addon_url']) ? wlm_post_data()['addon_url'] : '';
    $ret       = \WishListMember\Features\Addons_Manager\Addons::instance()->install_addon($addon_url);
    $array_ret = ['addon_url' => $addon_url];
    if (is_array($ret)) {
        $array_ret['activated'] = $ret['activated'];
        $array_ret['message']   = $ret['message'];
        wp_send_json_success($array_ret);
    } else {
        $array_ret['activated'] = false;
        $array_ret['message']   = __('An error occured while installing the addon', 'wishlist-member');
        wp_send_json_error($array_ret);
    }
}

add_action('wp_ajax_wlm_deactivate_coursecure', __NAMESPACE__ . '\deactivate_coursecure');
/**
 * `wp_ajax_wlm_install_addon` handler to install an add-on.
 */
function deactivate_coursecure()
{
    deactivate_plugins('coursecure/coursecure.php');
    wp_send_json_success(['message' => 'CourseCure plugin has been deactivated']);
}

add_action('admin_notices', __NAMESPACE__ . '\deactivate_coursecure_notice');
/**
 * Display notice to deactivate CourseCure whenever it is active
 * and CourseCure Addons are active at the same time
 */
function deactivate_coursecure_notice()
{
    global $pagenow;
    if ('plugins.php' !== $pagenow) {
        return;
    }
    $is_coursecure_active = \WishListMember\Features\Addons_Manager\Addons::instance()->is_coursecure_active();
    if (! $is_coursecure_active) {
        return;
    }
    $addons_list = [];
    $cc_addons   = [
        'wishlist-member-badges/main.php' => 'WishList Badges',
        'wishlist-member-points/main.php' => 'WishList Points',
        'coursecure-courses/main.php'     => 'CourseCure Courses',
        'coursecure-quizzes/main.php'     => 'CourseCure Quizzes',
    ];
    foreach ($cc_addons as $id => $addon_name) {
        if (is_plugin_active($id)) {
            $addons_list[] = $addon_name;
        }
    }
    if (empty($addons_list)) {
        return;
    }
    $addons_list = implode(', ', $addons_list);
    $class       = 'notice notice-warning is-dismissible';
    $message     = __('Please deactivate the CourseCure plugin to enable the following WishList Member addon(s):', 'wishlist-member-addons');
    printf('<div class="%1$s"><p>%2$s %3$s</p></div>', esc_attr($class), esc_html($message), esc_html($addons_list));
}

add_action('coursecure_admin_screen', __NAMESPACE__ . '\deactivate_coursecure_page_nag', 9);
/**
 * Display notice to deactivate CourseCure whenever it is active
 * and CourseCure Addons are active at the same time in CourseCure admin page
 */
function deactivate_coursecure_page_nag()
{
    $is_coursecure_active = \WishListMember\Features\Addons_Manager\Addons::instance()->is_coursecure_active();
    if (! $is_coursecure_active) {
        return;
    }
    $addons_list = [];
    $cc_addons   = [
        'wishlist-member-badges/main.php' => 'WishList Badges',
        'wishlist-member-points/main.php' => 'WishList Points',
        'coursecure-courses/main.php'     => 'CourseCure Courses',
        'coursecure-quizzes/main.php'     => 'CourseCure Quizzes',
    ];
    foreach ($cc_addons as $id => $addon_name) {
        if (is_plugin_active($id)) {
            $addons_list[] = $addon_name;
        }
    }
    if (empty($addons_list)) {
        return;
    }
    $addons_list = implode(', ', $addons_list);
    $message     = __('Please deactivate the CourseCure plugin to enable the following WishList Member addon(s):', 'wishlist-member-addons');
    printf(
        '<div id="license-nag" class="container-fluid pt-3 pb-3">
			<div class="row">
				<div class="col-md-12">
					<div class="form-text text-warning help-block mb-0"><p class="mb-0">%1$s %2$s</p></div>
				</div>
			</div>
		</div>',
        esc_html($message),
        esc_html($addons_list)
    );
}

add_filter('site_transient_update_plugins', __NAMESPACE__ . '\addons_update_plugins');
/**
 * Check for updates for addons
 *
 * @param  object $transient Transient data.
 * @return object
 */
function addons_update_plugins($transient)
{
    if (empty($transient)) {
        $transient = new \stdClass();
    }

    if (empty($transient->response)) {
        $transient->response = [];
    }

    $addons = Addons::instance()->get_addons(true);
    foreach ($addons as $data) {
        if (wlm_arrval($data, 'error')) {
            continue;
        }
        $cver = (string) wlm_arrval($transient, 'checked', $data->extra_info->main_file);
        if (empty($cver) || preg_match('/^{WLP_' . 'VERSION}$/', $cver)) {
            continue;
        }
        $item = (object) [
            'id'            => $data->extra_info->main_file,
            'slug'          => $data->product_slug,
            'plugin'        => $data->extra_info->main_file,
            'new_version'   => $data->version,
            'url'           => 'https://wishlistmember.com/',
            'package'       => $data->url,
            'requires_php'  => isset($data->extra_info->requires_php) ? $data->extra_info->requires_php : WLM_MIN_PHP_VERSION,
            'requires'      => isset($data->extra_info->requires_wp) ? $data->extra_info->requires : WLM_MIN_WP_VERSION,
        ];
        if (version_compare($cver, $data->version, '>=')) {
            $transient->no_update[ $data->extra_info->main_file ] = $item;
        } else {
            $transient->response[ $data->extra_info->main_file ] = $item;
        }
    }
    return $transient;
}

add_filter('plugins_api', __NAMESPACE__ . '\addons_plugin_info', 10, 3);
/**
 * Addons plugin info.
 *
 * @param  object $item   Plugin info.
 * @param  string $action Action.
 * @param  object $args   Arguments.
 * @return object
 */
function addons_plugin_info($item, $action, $args)
{
    $addons = Addons::instance()->get_addons(true);
    if (false === $item && 'plugin_information' === $action && isset($addons[ $args->slug ])) {
        $addon = $addons[$args->slug];
        $item   = (object) [
            'name'           => $addon->product_name,
            'slug'           => $args->slug,
            'version'        => $addon->version,
            'author'         => $addon->extra_info->author,
            'author_profile' => $addon->extra_info->author_profile,
            'homepage'       => 'https://wishlistmember.com/',
            'requires_php'   => isset($addon->extra_info->requires_php) ? $addon->extra_info->requires_php : WLM_MIN_PHP_VERSION,
            'requires'       => isset($addon->extra_info->requires_wp) ? $addon->extra_info->requires : WLM_MIN_WP_VERSION,
        ];
        $item->sections = [
            'description' => $addon->extra_info->description,
        ];
        if (isset($addon->extra_info->changelog)) {
            $item->sections['changelog'] = $addon->extra_info->changelog;
        }
        if (isset($addon->extra_info->support)) {
            $item->sections['support'] = $addon->extra_info->support;
        }
        if (isset($addon->extra_info->banner)) {
            $item->banners = [
                'low'  => $addon->extra_info->banner->lo,
                'high' => $addon->extra_info->banner->hi,
            ];
        }
    }
    return $item;
}
