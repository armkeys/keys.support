<?php

/**
 * Marketplace Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Marketplace Methods trait
*/
trait Marketplace_Methods
{
    /**
     * Run Marketplace actions.
     */
    public function do_market_place_actions()
    {
        $nonce = get_transient('wl_market_iframe_nonce');

        if (! empty($this->post_data)) {
            if (! empty($this->post_data['wl_market_nonce']) && ! empty($this->post_data['wl_market_action'])) {
                if ($this->post_data['wl_market_nonce'] === $nonce) {
                    if ('download_product' === $this->post_data['wl_market_action']) {
                        $this->market_process_product($this->post_data['product_id'], $this->post_data['product_slug'], $this->post_data['download_url'], $this->post_data['plugin_path'], $this->post_data['plugin_file'], $this->post_data['plugin_class_name'], $this->post_data['plugin_db_prefix']);
                    }
                }
            }
        }
    }

    /**
     * Install / activate marketplace product.
     *
     * @param string $product_id        Product ID.
     * @param string $product_slug      Product Slug.
     * @param string $download_url      Download Url.
     * @param string $plugin_path       Plugin Path.
     * @param string $plugin_file       Plugin File.
     * @param string $plugin_class_name Plugin Class Name.
     * @param string $plugin_db_prefix  Plugin DB Prefix.
     */
    public function market_process_product($product_id, $product_slug, $download_url, $plugin_path, $plugin_file, $plugin_class_name, $plugin_db_prefix)
    {
        if (empty($plugin_path)) {
            $plugin_path = trailingslashit(WP_PLUGIN_DIR) . $product_slug;
        } else {
            $plugin_path = trailingslashit(WP_PLUGIN_DIR) . $plugin_path;
        }

        if (empty($plugin_file)) {
            $plugin_file = trailingslashit($plugin_path) . $product_slug . '.php';
        } else {
            $plugin_file = trailingslashit($plugin_path) . $plugin_file;
        }

        if (empty($plugin_path)) {
            wp_die("There's something strange in the neighborhood.", "This Error Shouldn't Happen");
        }

        if (file_exists($plugin_path)) {
            $this->market_activate_plugin($plugin_file, $plugin_class_name);
        } else {
            $this->market_install_plugin($download_url, $plugin_path, $plugin_file, $plugin_class_name);
        }
    }

    /**
     * Active marketplace plugin
     *
     * @param string $plugin_file       Plugin File.
     * @param string $plugin_class_name Plugin Class Name.
     */
    public function market_activate_plugin($plugin_file, $plugin_class_name)
    {
        $activated = activate_plugin($plugin_file);

        // Attempt to edirect user to activated plugin dashboard page.
        if (is_null($activated)) {
            if (empty($plugin_class_name)) {
                wp_safe_redirect(admin_url('admin.php') . '?page=WishListMember&wl=marketplace');
            }

            if (class_exists($plugin_class_name)) {
                wp_safe_redirect(admin_url('admin.php') . '?page=' . $plugin_class_name);
            } else {
                wp_safe_redirect(admin_url('admin.php') . '?page=WishListMember&wl=marketplace');
            }
        }
    }

    /**
     * Install marketplace plugin
     *
     * @param string $download_url      Download Url.
     * @param string $plugin_path       Plugin Path.
     * @param string $plugin_file       Plugin File.
     * @param string $plugin_class_name Plugin Class Name.
     */
    public function market_install_plugin($download_url, $plugin_path, $plugin_file, $plugin_class_name)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        WP_Filesystem();

        global $wp_filesystem;

        $remote_file = download_url($download_url);

        if (! is_wp_error($remote_file)) {
            $downloaded = unzip_file($remote_file, trailingslashit(WP_PLUGIN_DIR));

            if ($downloaded) {
                unlink($remote_file);
                $this->market_activate_plugin($plugin_file, $plugin_class_name);
            }
        }

        unlink($remote_file);
    }
}
