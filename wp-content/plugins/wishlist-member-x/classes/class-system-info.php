<?php

/**
 * System Info Class for WishList Member
 *
 * @package WishListMember
 */

namespace WishListMember;

defined('ABSPATH') || die();

/**
 * System Info Class
 */
class System_Info
{
    /**
     * Fields
     *
     * @var array
     */
    public $fields = [];

    /**
     * Info
     *
     * @var array
     */
    public $info = [];

    /**
     * WP Theme Object
     *
     * @var object
     */
    public $theme = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $server_flds = [
            'os'                 => __('Operating System', 'wishlist-member'),
            'software'           => __('Software', 'wishlist-member'),
            'mysql_version'      => __('MySQL version', 'wishlist-member'),
            'php_version'        => __('PHP Version', 'wishlist-member'),
            'php_max_input_vars' => __('PHP Max Input Vars', 'wishlist-member'),
            'php_max_post_size'  => __('PHP Max Post Size', 'wishlist-member'),
            'asp_tags'           => __('ASP Tags', 'wishlist-member'),
            // 'gd_installed' => __('GD Library', 'wishlist-member'),
            // 'zip_installed' => __('ZIP Installed', 'wishlist-member'),
            'openssl'            => __('OpenSSL', 'wishlist-member'),
            'curl'               => __('cURL', 'wishlist-member'),
            'write_permissions'  => __('Write Permissions', 'wishlist-member'),
            'path'               => __('Install Folder', 'wishlist-member'),
        ];

        $wordpress_flds = [
            'version'             => __('Version', 'wishlist-member'),
            'site_url'            => __('Site URL', 'wishlist-member'),
            'home_url'            => __('Home URL', 'wishlist-member'),
            'is_multisite'        => __('WP Multisite', 'wishlist-member'),
            'max_upload_size'     => __('Max Upload Size', 'wishlist-member'),
            'memory_limit'        => __('Memory limit', 'wishlist-member'),
            'permalink_structure' => __('Permalink Structure', 'wishlist-member'),
            'language'            => __('Language', 'wishlist-member'),
            'timezone'            => __('Timezone', 'wishlist-member'),
            'admin_email'         => __('Admin Email', 'wishlist-member'),
            'debug_mode'          => __('Debug Mode', 'wishlist-member'),
        ];

        $user_flds = [
            'role'   => __('Role', 'wishlist-member'),
            'locale' => __('WP Profile lang', 'wishlist-member'),
            'agent'  => __('User Agent', 'wishlist-member'),
        ];

        $this->theme = wp_get_theme();
        $theme_flds  = [
            'name'           => __('Name', 'wishlist-member'),
            'version'        => __('Version', 'wishlist-member'),
            'author'         => __('Author', 'wishlist-member'),
            'is_child_theme' => __('Child Theme', 'wishlist-member'),
        ];
        if ($this->theme->parent()) {
            $parent_fields = [
                'parent_name'    => __('Parent Theme Name', 'wishlist-member'),
                'parent_version' => __('Parent Theme Version', 'wishlist-member'),
                'parent_author'  => __('Parent Theme Author', 'wishlist-member'),
            ];
            $theme_flds    = array_merge($theme_flds, $parent_fields);
        }

        $plugin_flds    = [];
        $active_plugins = $this->get_active_plugins();
        foreach ($active_plugins as $key => $value) {
            $plugin_flds[ $key ]           = $value['Name'] . ' - ' . $value['Version'];
            $this->info['plugins'][ $key ] = [
                'value'   => $value['Author'],
                'fld_url' => $value['PluginURI'],
                'val_url' => $value['AuthorURI'],
            ];
        }

        $this->fields = [
            'server'    => [
                'title'  => __('Server Environment', 'wishlist-member'),
                'fields' => $server_flds,
            ],
            'wordpress' => [
                'title'  => __('WordPress Environment', 'wishlist-member'),
                'fields' => $wordpress_flds,
            ],
            'theme'     => [
                'title'  => __('Theme', 'wishlist-member'),
                'fields' => $theme_flds,
            ],
            'user'      => [
                'title'  => __('User', 'wishlist-member'),
                'fields' => $user_flds,
            ],
            'plugins'   => [
                'title'  => __('Active Plugins', 'wishlist-member'),
                'fields' => $plugin_flds,
            ],
        ];

        if (is_multisite()) {
            $nplugin_flds   = [];
            $active_plugins = $this->get_network_plugins();
            foreach ($active_plugins as $key => $value) {
                $nplugin_flds[ $key ]                  = $value['Name'] . ' - ' . $value['Version'];
                $this->info['network_plugins'][ $key ] = [
                    'value'   => $value['Author'],
                    'fld_url' => $value['PluginURI'],
                    'val_url' => $value['AuthorURI'],
                ];
            }
            $this->fields['network_plugins'] = [
                'title'  => 'Network Plugins',
                'fields' => $nplugin_flds,
            ];
        };

        $this->info['theme']     = $this->get_theme_values();
        $this->info['user']      = $this->get_user_values();
        $this->info['server']    = $this->get_server_values();
        $this->info['wordpress'] = $this->get_wordpress_values();
    }

    /**
     * Get theme values
     *
     * @return array
     */
    public function get_theme_values()
    {
        global $wpdb;
        $theme = [];

        $theme['name']           = ['value' => $this->theme->get('Name')];
        $theme['version']        = ['value' => $this->theme->get('Version')];
        $theme['author']         = ['value' => $this->theme->get('Author')];
        $theme['is_child_theme'] = ['value' => is_child_theme() ? 'Yes' : 'No'];

        if ($this->theme->parent()) {
            $theme['parent_name']    = ['value' => $this->theme->parent()->get('Name')];
            $theme['parent_version'] = ['value' => $this->theme->parent()->get('Version')];
            $theme['parent_author']  = ['value' => $this->theme->parent()->get('Author')];
        }
        return $theme;
    }

    /**
     * Get user values
     *
     * @return array
     */
    public function get_user_values()
    {
        global $wpdb;
        $user = [];

        $role         = null;
        $current_user = wp_get_current_user();
        if (! empty($current_user->roles)) {
            $role = $current_user->roles[0];
        }
        $user['role'] = ['value' => $role];

        $user['locale'] = ['value' => get_locale()];

        $user['agent'] = ['value' => wlm_server_data()['HTTP_USER_AGENT']];

        return $user;
    }

    /**
     * Get server values
     *
     * @return array
     */
    public function get_server_values()
    {
        global $wpdb;
        $server = [];

        $server['os'] = ['value' => PHP_OS];

        $server['software'] = ['value' => wlm_server_data()['SERVER_SOFTWARE']];

        $server['mysql_version'] = ['value' => $wpdb->db_version()];

        $server['php_version'] = ['value' => PHP_VERSION];
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            // Translators: %s - minimum PHP version required.
            $server['php_version']['notes'] = sprintf(__('We recommend to use php %s or higher', 'wishlist-member'), WLM_MIN_PHP_VERSION);
        }

        $server['php_max_input_vars'] = ['value' => ini_get('max_input_vars')];

        $server['php_max_post_size'] = ['value' => ini_get('post_max_size')];
        $server['asp_tags']          = ['value' => wlm_or(ini_get('asp_tags'), 'Off')];

        $gdlib = 'No';
        if (extension_loaded('gd')) {
            $gdlib = gd_info();
            $gdlib = $gdlib['GD Version'];
        }
        $server['gd_installed'] = ['value' => $gdlib];

        $server['zip_installed'] = ['value' => extension_loaded('zip') ? 'Yes' : 'No'];
        if ('No' === $server['zip_installed']['value']) {
            $server['zip_installed']['notes'] = __('Zip Library not installed', 'wishlist-member');
        }

        $openssl = 'No';
        if (extension_loaded('openssl')) {
            $openssl = OPENSSL_VERSION_TEXT . ' (ver. ' . OPENSSL_VERSION_NUMBER . ')';
        }
        $server['openssl'] = ['value' => $openssl];

        $curl = 'No';
        if (extension_loaded('curl')) {
            $curl = curl_version();
            $curl = $curl['version'];

            $ch = curl_init('https://www.howsmyssl.com/a/check');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);
            $json = json_decode($data);
            if (isset($json->tls_version)) {
                $curl .= ' (using ' . $json->tls_version . ')';
            } else {
                $curl .= ' (TLS not available)';
            }
        }
        $server['curl'] = ['value' => $curl];

        $paths_to_check = [ABSPATH => 'WordPress root directory'];
        $write_problems = [];
        $wp_upload_dir  = wp_upload_dir();
        if ($wp_upload_dir['error']) {
            $write_problems[] = 'WordPress root uploads directory';
        }
        $wlm_uploads_path = $wp_upload_dir['basedir'];
        if (is_dir($wlm_uploads_path)) {
            $paths_to_check[ $wlm_uploads_path ] = 'Uploads directory';
        }
        $htaccess_file = ABSPATH . '/.htaccess';
        if (file_exists($htaccess_file)) {
            $paths_to_check[ $htaccess_file ] = '.htaccess file';
        }
        foreach ($paths_to_check as $dir => $description) {
            if (! is_writable($dir)) {
                $write_problems[] = $description;
            }
        }
        if ($write_problems) {
            $value  = 'There are some writing permissions issues with the following directories/files:<br />- ';
            $value .= implode('<br />- ', $write_problems);
        } else {
            $value = 'OK';
        }
        $server['write_permissions'] = ['value' => $value];

        $server['path'] = ['value' => ABSPATH];

        return $server;
    }

    /**
     * Get WordPress values
     *
     * @return array
     */
    public function get_wordpress_values()
    {
        global $wp_rewrite;
        $wordpress = [];

        $wordpress['version']         = ['value' => get_bloginfo('version')];
        $wordpress['site_url']        = ['value' => get_site_url()];
        $wordpress['home_url']        = ['value' => get_home_url()];
        $wordpress['is_multisite']    = ['value' => is_multisite() ? 'Yes' : 'No'];
        $wordpress['max_upload_size'] = ['value' => size_format(wp_max_upload_size())];

        $wordpress['memory_limit'] = ['value' => WP_MEMORY_LIMIT];
        $min_recommended_memory    = '64M';
        $memory_limit_bytes        = wp_convert_hr_to_bytes(WP_MEMORY_LIMIT);
        $min_recommended_bytes     = wp_convert_hr_to_bytes($min_recommended_memory);
        // If ( $memory_limit_bytes < $min_recommended_bytes ) {
        // $wordpress['memory_limit']['notes'] = sprintf(
        // _( 'We recommend setting memory to at least %1$s. For more information, read about <a href="%2$s">how to Increase memory allocated to PHP</a>.'),
        // $min_recommended_memory, 'https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP'
        // );
        // }
        $structure = $wp_rewrite->permalink_structure;
        if (! $structure) {
            $structure = 'Plain';
        }
        $wordpress['permalink_structure'] = ['value' => $structure];

        $wordpress['language'] = ['value' => get_bloginfo('language')];

        $timezone = get_option('timezone_string');
        if (! $timezone) {
            $timezone = get_option('gmt_offset');
        }
        $wordpress['timezone'] = ['value' => $timezone];

        $wordpress['admin_email'] = ['value' => get_option('admin_email')];
        $wordpress['debug_mode']  = ['value' => WP_DEBUG ? 'Active' : 'Inactive'];

        return $wordpress;
    }

    /**
     * Get raw output
     *
     * @return string
     */
    public function get_raw()
    {
        $file = '';
        foreach ($this->fields as $key => $fld) :
            $file .= "*** {$fld['title']} ***\r\n";
            foreach ($fld['fields'] as $fld_key => $fld_label) :
                $file .= str_pad($fld_label, 20, ' ') . ':' . $this->info[ $key ][ $fld_key ]['value'] . "\r\n";
            endforeach;
            $file .= "\r\n";
        endforeach;
        return $file;
    }

    /**
     * Get active plugins
     *
     * @return array
     */
    public function get_active_plugins()
    {
        if (! function_exists('get_plugins')) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $active_plugins = get_option('active_plugins');
        $plugins        = array_intersect_key(get_plugins(), array_flip($active_plugins));

        return $plugins;
    }

    /**
     * Get network plugins
     *
     * @return array
     */
    public function get_network_plugins()
    {
        if (! function_exists('get_plugins')) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }
        $active_plugins = get_site_option('active_sitewide_plugins');
        $plugins        = array_intersect_key(get_plugins(), $active_plugins);

        return $plugins;
    }
}
