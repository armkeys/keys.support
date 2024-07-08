<?php

/**
 * Load add-ons file.
 *
 * @package WishListMember\Features\Addons_Manager
 */

namespace WishListMember\Features\Addons_Manager;

use WP_Error;

/**
 * Addons class
 */
class Addons
{
    /**
     * Our own instance.
     *
     * @var Addons
     */
    private static $instance = null;

    /**
     * Return instasnce of this class
     *
     * @return Addons
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get add-ons for this specific WishList Member product
     *
     * @param  boolean $cached Whether to use cached data or not.
     * @return array
     */
    public function get_addons($cached = false)
    {
        if ($cached) {
            $addons = get_transient('wlm_addons/' . WLM_PRODUCT_SLUG);
            if (false !== $addons) {
                return $addons;
            }
        }
        try {
            $resp   = wlm_mothership_request(
                '/versions/addons/' . WLM_PRODUCT_SLUG . '/' . wishlistmember_instance()->get_option('LicenseKey'),
                [
                    'all'    => 'true',
                    'domain' => preg_replace('#https?://#i', '', get_bloginfo('url')),
                ]
            );
        } catch (\Exception $e) {
            return [];
        }
        $addons = is_object($resp) ? (array) $resp : [];
        set_transient('wlm_addons/' . WLM_PRODUCT_SLUG, $addons, HOUR_IN_SECONDS);
        return $addons;
    }


    /**
     * Install add-on
     *
     * @param  string $addon_url Add-on url.
     * @return array|false Array containing installation status or false on failure.
     */
    public function install_addon($addon_url)
    {
        // Lets included necessary files.
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
        require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
        // Install the plugin.
        $skin      = new \WP_Ajax_Upgrader_Skin();
        $installer = new \Plugin_Upgrader($skin);
        $plugin    = wp_unslash($addon_url);
        $installer->install($plugin);
        wp_cache_flush();
        // Installation successfull if plugin details is available.
        if ($installer->plugin_info()) {
            // Lets try activating it.
            $plugin_basename      = $installer->plugin_info();
            $is_coursecure_active = $this->is_coursecure_active();
            $activated            = false;
            if (
                $is_coursecure_active &&
                ( 'wishlist-member-badges/main.php' === $plugin_basename ||
                'wishlist-member-points/main.php' === $plugin_basename ||
                'coursecure-courses/main.php' === $plugin_basename ||
                'coursecure-quizzes/main.php' === $plugin_basename )
            ) {
                deactivate_plugins(wp_unslash($plugin_basename));
            } else {
                $activated = activate_plugin($plugin_basename);
            }

            if (false === $activated || is_wp_error($activated)) {
                return [
                    'message'   => __('Plugin installed.', 'wishlist-member'),
                    'activated' => false,
                    'basename'  => $plugin_basename,
                ];
            } else {
                return [
                    'message'   => __('Plugin installed & activated.', 'wishlist-member'),
                    'activated' => true,
                    'basename'  => $plugin_basename,
                ];
            }
        }
        return false;
    }

    /**
     * Active add-on
     *
     * @param  string $addon_file Add-on file.
     * @return boolean | WP_Error
     */
    public function activate_addon($addon_file)
    {
        return activate_plugin(wp_unslash($addon_file));
    }

    /**
     * Deactivate add-on
     *
     * @param  string $addon_file Add-on file.
     * @return true
     */
    public function deactivate_addon($addon_file)
    {
        deactivate_plugins(wp_unslash($addon_file));
        return true;
    }

    /**
     * Check if CourseCure plugin is active
     *
     * @return boolean
     */
    public function is_coursecure_active()
    {
        $is_coursecure_active = is_plugin_active('coursecure/coursecure.php');
        if (defined('WLM_ADDONS_PLUGIN_FILE') && strpos(WLM_ADDONS_PLUGIN_FILE, 'plugins\coursecure\coursecure.php') !== false) {
            $is_coursecure_active = true;
        }
        return $is_coursecure_active;
    }
}
