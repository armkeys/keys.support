<?php

/**
 * Folder Protection Methods Feature
 *
 * @package WishListMember/Features/Folder_Protection
 */

namespace WishListMember\Features\Folder_Protection;

/**
 * Folder Protection Methods class
 */
class Folder_Protection_Methods
{
    /**
     * Adds/removes .htaccess in protected folders
     *
     * @param boolean $install True to install. False to remove. Default true.
     */
    public function folder_protection_htaccess($install = true)
    {
        $parent_folder = wishlistmember_instance()->folder_protection_full_path(wishlistmember_instance()->get_option('rootOfFolders'));
        if (! is_dir($parent_folder)) {
            return;
        }

        $folders = glob($parent_folder . '/*', GLOB_ONLYDIR);
        if (empty($folders)) {
            return;
        }
        foreach ($folders as $folder) {
            $folder_id = wishlistmember_instance()->folder_id($folder);
            wishlistmember_instance()->folder_protect_htaccess($parent_folder . '/' . basename($folder), $install);
        }
    }

    /**
     * Adds .htaccess to protected folders
     */
    public function add_htaccess_to_protected_folders()
    {
        wishlistmember_instance()->folder_protection_htaccess(true);
    }

    /**
     * Removes .htaccess from protected folders
     */
    public function remove_all_htaccess_from_protected_folders()
    {
        wishlistmember_instance()->folder_protection_htaccess(false);
    }

    /**
     * Get folder's "force download" status.
     *
     * @param  string $folder Folder Path.
     * @return boolean
     */
    public function get_folder_protect_force_download($folder)
    {
        return wishlistmember_instance()->folder_force_download(wishlistmember_instance()->folder_id($folder));
    }

    /**
     * Get all levels of a folder in an array
     *
     * @param  string $folder Folder Path.
     * @return array Membership Levels
     */
    public function get_folder_levels($folder)
    {
        return wishlistmember_instance()->get_content_levels('folders', wishlistmember_instance()->folder_id($folder), false, false);
    }

    /**
     * Processes Folder protection
     *
     * @param string $wlm_folder Folder to process (relative to the Root of folders option).
     * @param string $wlm_file   File to download.
     */
    public function folder_protect($wlm_folder, $wlm_file)
    {

        $folder_id = wishlistmember_instance()->folder_id($wlm_folder);

        $wlm_file = wishlistmember_instance()->get_option('rootOfFolders') . '/' . $wlm_folder . '/' . $wlm_file;
        if (! file_exists($wlm_file)) {
            // File does not exist.
            header('HTTP/1.0 404 Not Found');
            print( '404 - File Not Found' );
            exit;
        }

        $force_download = wishlistmember_instance()->folder_force_download($folder_id);
        $user           = wp_get_current_user();

        if (! wishlistmember_instance()->folder_protected($folder_id) || $user->caps['administrator']) {
            // Folder not protected or user is admin.
            wishlistmember_instance()->download($wlm_file, $force_download);
            exit;
        }

        $redirect = false;

        if (! $user->ID) {
            // Not logged in.
            header(sprintf('Location:%s', wishlistmember_instance()->non_members_url()));
            exit;
        }

        $ulevels = wishlistmember_instance()->get_membership_levels($user->ID, null, null, null, true);
        $levels  = array_intersect(wishlistmember_instance()->get_folder_levels($wlm_folder), $ulevels);

        if (! count($levels)) {
            // No valid levels.
            header(sprintf('Location:%s', wishlistmember_instance()->wrong_level_url()));
            exit;
        }

        // Remove expired levels.
        foreach ((array) $levels as $key => $level) {
            if (wishlistmember_instance()->level_expired($level, $user->ID)) {
                unset($levels[ $key ]);
            }
        }
        if (! count($levels)) {
            header(sprintf('Location:%s', wishlistmember_instance()->expired_url()));
            exit;
        }

        // Remove unconfirmed levels.
        foreach ((array) $levels as $key => $level) {
            if (wishlistmember_instance()->level_unconfirmed($level, $user->ID)) {
                unset($levels[ $key ]);
            }
        }
        if (! count($levels)) {
            header(sprintf('Location:%s', wishlistmember_instance()->for_confirmation_url()));
            exit;
        }

        // Remove forapproval levels.
        foreach ((array) $levels as $key => $level) {
            if (wishlistmember_instance()->level_for_approval($level, $user->ID)) {
                unset($levels[ $key ]);
            }
        }
        if (! count($levels)) {
            header(sprintf('Location:%s', wishlistmember_instance()->for_approval_url()));
            exit;
        }

        // Remove cancelled levels.
        foreach ((array) $levels as $key => $level) {
            if (wishlistmember_instance()->level_cancelled($level, $user->ID)) {
                unset($levels[ $key ]);
            }
        }
        if (! count($levels)) {
            header(sprintf('Location:%s', wishlistmember_instance()->cancelled_url()));
            exit;
        }

        // All is well. release the kraken!
        wishlistmember_instance()->download($wlm_file, $force_download);
        exit;
    }

    /**
     * Adds/Removes .htaccess code to the protected upload folders
     *
     * @param string  $folder_full_path Full path to protected folder.
     * @param boolean $install          True to install. False to remove. Default true.
     */
    public function folder_protect_htaccess($folder_full_path, $install = true)
    {

        if (empty(wishlistmember_instance()->get_option('rootOfFolders'))) {
            return false;
        }
        $folder = basename($folder_full_path);

        if (! wishlistmember_instance()->get_option('folder_protection')) {
            $install = false;
        }

        if (is_dir($folder_full_path)) {
            // Folder protection code markers.
            $htaccess_start = '# BEGIN WishList Member Folder Protection';
            $htaccess_end   = '# END WishList Member Folder Protection';

            // Apache - read .htaccess.
            $htaccess_file = $folder_full_path . '/.htaccess';
            $htaccess      = file_exists($htaccess_file) ? file_get_contents($htaccess_file) : '';
            // Apache - remove our .htaccess code.
            list($start)   = explode($htaccess_start, $htaccess);
            list($x, $end) = explode($htaccess_end, $htaccess);
            $htaccess      = trim(wlm_trim($start) . "\n" . wlm_trim($end));

            // Nginx - read config.
            $nginx_file = wishlistmember_instance()->wp_upload_path . '/wlm_file_protect_nginx.conf';
            $nginx      = file_exists($nginx_file) ? trim(file_get_contents($nginx_file)) : '';
            // Apache - remove our config code.
            list($start)   = explode($htaccess_start, $nginx);
            list($x, $end) = explode($htaccess_end, $nginx);
            $nginx         = trim(wlm_trim($start) . "\n" . wlm_trim($end));

            if ($install) {
                /*
                 * Apache - prepare htaccess code
                 */
                $siteurl   = wp_parse_url(get_option('home'));
                $siteurl   = $siteurl['path'] . '/index.php';
                $htaccess .= "\n{$htaccess_start}";
                $htaccess .= "\nOptions FollowSymLinks";
                $htaccess .= "\nRewriteEngine on";
                $htaccess .= "\nRewriteRule ^(.*)$ {$siteurl}?wlmfolder={$folder}&restoffolder=$1 [L]";
                $htaccess .= "\n{$htaccess_end}";

                /*
                 * Nginx - prepare config code
                 */
                $nginx_header = "# Include this file in your site configuration's server {} block";
                $nginx        = "{$nginx_header}\n\n" . trim(str_replace($nginx_header, '', $nginx));

                $base_url = site_url(wishlistmember_instance()->get_option('parentFolder'), 'relative');
                $full_url = site_url();
                $nginx   .= "\n$htaccess_start\n";
                $nginx   .= "location {$base_url} {\n";
                $nginx   .= "\trewrite ^{$base_url}/(.+?)/(.+)$ {$full_url}?wlmfolder=$1&restoffolder=$2 break;\n";
                $nginx   .= "}\n";
                $nginx   .= "$htaccess_end\n";
            }
            // Apache - write .htaccess.
            file_put_contents($htaccess_file, wlm_trim($htaccess) . "\n");

            // Nginx - write config.
            file_put_contents($nginx_file, wlm_trim($nginx) . "\n");
        }
    }

    /**
     * Convert string to slug
     *
     * @param  string $string String to convert.
     * @return string         Slug.
     */
    public function string_to_slug($string)
    {
        $utf8 = [
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u'  => 'A',
            '/[ÍÌÎÏ]/u'   => 'I',
            '/[íìîï]/u'   => 'i',
            '/[éèêë]/u'   => 'e',
            '/[ÉÈÊË]/u'   => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u'  => 'O',
            '/[úùûü]/u'   => 'u',
            '/[ÚÙÛÜ]/u'   => 'U',
            '/ç/'         => 'c',
            '/Ç/'         => 'C',
            '/ñ/'         => 'n',
            '/Ñ/'         => 'N',
            '/–/'         => '-', // UTF-8 hyphen to "normal" hyphen.
            '/[’‘‹›‚]/u'  => ' ', // Literally a single quote.
            '/[“”«»„]/u'  => ' ', // Double quote.
            '/ /'         => ' ', // nonbreaking space (equiv. to 0x160).
        ];
        $slug = preg_replace(array_keys($utf8), array_values($utf8), is_null($string) ? '' : $string);
        $slug = sanitize_title_with_dashes($slug);
        return $slug;
    }

    /**
     * Setup Easy Folder Protection
     */
    public function easy_folder_protection()
    {
        global $wpdb;

        // Reset.
        $wpdb->query('DELETE FROM `' . esc_sql(wishlistmember_instance()->table_names->contentlevels) . '` WHERE `type`="~FOLDER"');

        // Some clean up.
        $default_parent_folder_name = 'files';

        $root_of_folders = ABSPATH . '/' . $default_parent_folder_name;
        wishlistmember_instance()->save_option('rootOfFolders', $root_of_folders);

        if (! is_dir($root_of_folders)) {
            // If folder does not exist, we create it.
            if (! mkdir($root_of_folders)) {
                wishlistmember_instance()->err = __('<b>Could not create folder.</b><br>', 'wishlist-member');
                return false;
            }
        }

        wishlistmember_instance()->save_option('parentFolder', $default_parent_folder_name);

        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');

        foreach ((array) $wpm_levels as $level_id => $level) {
            $level_name = $level['name'];
            $subfolder  = $root_of_folders . '/' . wishlistmember_instance()->string_to_slug($level_name);
            if (! is_dir($subfolder)) {
                mkdir($subfolder);
            }
            $folder_id = wishlistmember_instance()->folder_id($subfolder);
            wishlistmember_instance()->folder_protected($folder_id, true);
            wishlistmember_instance()->set_content_levels('folders', $folder_id, $level_id);
        }

        wishlistmember_instance()->remove_all_htaccess_from_protected_folders();
        wishlistmember_instance()->add_htaccess_to_protected_folders();
        wishlistmember_instance()->save_option('folder_protection', 1);

        wishlistmember_instance()->msg = sprintf('Folder protection successfully auto-configured at <b>%s</b>', $root_of_folders);
        return true;
    }

    /**
     * Setup folder protection parent folder.
     */
    public function folder_protection_parent_folder()
    {

        $parent_folder = wlm_post_data()['parentFolder'];

        if (in_array($parent_folder, ['', 'wp-content', 'wp-includes', 'wp-admin', 'uploads', 'themes', 'plugins'], true)) {
            wishlistmember_instance()->err = __('Parent Folder can not be one of WordPress default folders such as wp-content, wp-includes, wp-admin, uploads, themes or plugins folder.<br /><br />Try to create a folder inside your WordPress instalation path and set it as Parent Folder.', 'wishlist-member');
            return false;
        }

        $root_of_folders = addslashes(ABSPATH . $parent_folder);

        if (! is_dir($root_of_folders)) {
            wishlistmember_instance()->err = __('Folder not found. Please create it first.', 'wishlist-member');
            return false;
        }

        wishlistmember_instance()->remove_all_htaccess_from_protected_folders();

        wishlistmember_instance()->save_option('parentFolder', $parent_folder);
        wishlistmember_instance()->save_option('rootOfFolders', $root_of_folders);

        wishlistmember_instance()->add_htaccess_to_protected_folders();

        wishlistmember_instance()->msg = __('<b>Parent Folder Updated.</b><br>', 'wishlist-member');
        return true;
    }

    /**
     * Migrate folder protection data.
     */
    public function folder_protection_migrate()
    {
        $need_migrate = wishlistmember_instance()->get_option(wishlistmember_instance()->plugin_option_name . '_MigrateFolderProtectionData');

        if (1 !== (int) $need_migrate) {
            $parent_folder = wishlistmember_instance()->folder_protection_relative_path(wishlistmember_instance()->get_option('rootOfFolders'));
            wishlistmember_instance()->save_option('parentFolder', $parent_folder);
            wishlistmember_instance()->save_option(wishlistmember_instance()->plugin_option_name . '_MigrateFolderProtectionData', '1');
        }
    }

    /**
     * Migrate folder protection settings to wlm_contentlevels
     */
    public function migrate_folder_protection()
    {
        if (1 !== (int) wishlistmember_instance()->get_option('folder_protection_migrated')) {
            $folder_levels = (array) wishlistmember_instance()->get_option('FolderProtect');

            $x = [];
            foreach ($folder_levels as $level => $folders) {
                if (is_array($folders)) {
                    foreach ($folders as $folder) {
                        if ('Protection' === $level) {
                            wishlistmember_instance()->folder_protected(wishlistmember_instance()->folder_id($folder), 'Y');
                        } else {
                            $x[ $folder ][] = $level;
                        }
                    }
                }
            }

            foreach ($x as $folder => $levels) {
                wishlistmember_instance()->set_content_levels('~FOLDER', wishlistmember_instance()->folder_id($folder), $levels);
            }

            $force_download = (array) wishlistmember_instance()->get_option('FolderForceDownload');
            foreach ($force_download as $level => $folders) {
                if (is_array($folders)) {
                    foreach (array_keys($folders) as $folder) {
                        wishlistmember_instance()->folder_force_download(wishlistmember_instance()->folder_id($folder), 'Y');
                    }
                }
            }
            wishlistmember_instance()->save_option('folder_protection_migrated', 1);
        }
    }

    /**
     * Set Folder Protection
     *
     * @param  integer        $folder_id Folder ID.
     * @param  boolean|string $status    Boolean value or Y/N.
     * @return boolean
     */
    public function folder_protected($folder_id, $status = null)
    {
        if (! is_null($status)) {
            wishlistmember_instance()->folder_protection_htaccess(true);
            wishlistmember_instance()->special_content_level($folder_id, 'Protection', $status, '~FOLDER');
        }
        return wishlistmember_instance()->special_content_level($folder_id, 'Protection', null, '~FOLDER');
    }

    /**
     * Set "force download" status for folders
     *
     * @param  integer        $folder_id Folder ID.
     * @param  boolean|string $status    Boolean value or Y/N.
     * @return boolean
     */
    public function folder_force_download($folder_id, $status = null)
    {
        if (! is_null($status)) {
            wishlistmember_instance()->special_content_level($folder_id, 'ForceDownload', $status, '~FOLDER');
        }
        return wishlistmember_instance()->special_content_level($folder_id, 'ForceDownload', null, '~FOLDER');
    }

    /**
     * Returns relative path of folder protection parent folder
     *
     * @param  string $folder_name Folder name.
     * @return string
     */
    public function folder_protection_relative_path($folder_name)
    {
        $folder_name = explode(ABSPATH, $folder_name); // To fix the Strict Standards: Only variables should be passed by reference.
        return preg_replace(['/^\/*/', '/\/*$/'], '', array_pop($folder_name));
    }

    /**
     * Returns full path of folder protection parent folder
     *
     * @param  string $folder_name Folder name.
     * @return string
     */
    public function folder_protection_full_path($folder_name)
    {
        return ABSPATH . wishlistmember_instance()->folder_protection_relative_path($folder_name);
    }

    /**
     * Computes unsigned crc32 of base folder name and returns it as ID
     *
     * @param  string $folder_name Folder name.
     * @return integer
     */
    public function folder_id($folder_name)
    {
        return crc32(basename($folder_name));
    }

    /**
     * Enable folder protection
     *
     * @param  array $data Folder protection data.
     * @return array
     */
    public function enable_folder_protection($data)
    {
        if (! isset($data['folder_protection'])) {
            return [
                'success'  => false,
                'msg'      => __('Invalid setting', 'wishlist-member'),
                'msg_type' => 'danger',
                'data'     => $data,
            ];
        }

        wishlistmember_instance()->save_option('folder_protection', $data['folder_protection']);
        if (1 === (int) $data['folder_protection']) {
            wishlistmember_instance()->save_option('folder_protection_autoconfig', '1'); // Enable auto configure.

            $parentfolder = wlm_trim(wishlistmember_instance()->get_option('parentFolder'));
            $parentfolder = $parentfolder ? $parentfolder : 'files';
            wishlistmember_instance()->save_option('parentFolder', $parentfolder);

            $root_of_folders = wishlistmember_instance()->folder_protection_full_path($parentfolder);
            wishlistmember_instance()->save_option('rootOfFolders', $root_of_folders);
            $data['rootoffolders'] = $root_of_folders;
            // Run reset.
            if (! is_dir($root_of_folders)) {
                // If folder does not exist, we create it.
                if (! mkdir($root_of_folders)) {
                    return [
                        'success'  => false,
                        'msg'      => __('Folder Protection enabled but we were not able to create the folder on your host', 'wishlist-member'),
                        'msg_type' => 'warning',
                        'data'     => $data,
                    ];
                }
            }
            if (is_dir($root_of_folders)) {
                $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
                foreach ((array) $wpm_levels as $level_id => $level) {
                    $level_name = $level['name'];
                    $subfolder  = $root_of_folders . '/' . wishlistmember_instance()->string_to_slug($level_name);
                    $folder_id  = wishlistmember_instance()->folder_id($subfolder);
                    if (! is_dir($subfolder)) {
                        mkdir($subfolder);
                    }
                    $content_lvls   = wishlistmember_instance()->get_content_levels('~FOLDER', $folder_id, true, false);
                    $content_lvls   = count($content_lvls) > 0 ? array_keys($content_lvls) : [];
                    $content_lvls[] = $level_id;
                    wishlistmember_instance()->set_content_levels('folders', $folder_id, $content_lvls);
                    wishlistmember_instance()->folder_protected($folder_id, true);
                }
                wishlistmember_instance()->remove_all_htaccess_from_protected_folders();
                wishlistmember_instance()->add_htaccess_to_protected_folders();
            }

            $return = [
                'success'  => true,
                'msg'      => __('Folder Protection enabled', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        } else {
            wishlistmember_instance()->remove_all_htaccess_from_protected_folders();

            $return = [
                'success'  => true,
                'msg'      => __('Folder Protection disabled', 'wishlist-member'),
                'msg_type' => 'warning',
                'data'     => $data,
            ];
        }
        return $return;
    }

    /**
     * Automatically configure folder protection
     *
     * @param  array $data Configuration data.
     * @return array
     */
    public function folder_protection_autoconfig($data)
    {
        global $wpdb;

        $parentfolder = wlm_trim(wishlistmember_instance()->get_option('parentFolder'));
        $parentfolder = $parentfolder ? $parentfolder : 'files';
        wishlistmember_instance()->save_option('parentFolder', $parentfolder);

        $root_of_folders = wishlistmember_instance()->folder_protection_full_path($parentfolder);
        wishlistmember_instance()->save_option('rootOfFolders', $root_of_folders);

        if ('reset' === $data['type']) {
            if (! is_dir($root_of_folders)) {
                // If folder does not exist, we create it.
                if (! mkdir($root_of_folders)) {
                    return [
                        'success' => false,
                        'msg'     => __('Could not create folder', 'wishlist-member'),
                        'data'    => $data,
                    ];
                }
            }

            $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
            foreach ((array) $wpm_levels as $level_id => $level) {
                $level_name = $level['name'];
                $subfolder  = $root_of_folders . '/' . wishlistmember_instance()->string_to_slug($level_name);
                $folder_id  = wishlistmember_instance()->folder_id($subfolder);
                if (! is_dir($subfolder)) {
                    mkdir($subfolder);
                }
                $content_lvls   = wishlistmember_instance()->get_content_levels('~FOLDER', $folder_id, true, false);
                $content_lvls   = count($content_lvls) > 0 ? array_keys($content_lvls) : [];
                $content_lvls[] = $level_id;
                wishlistmember_instance()->set_content_levels('folders', $folder_id, $content_lvls);
                wishlistmember_instance()->folder_protected($folder_id, true);
            }

            wishlistmember_instance()->remove_all_htaccess_from_protected_folders();
            wishlistmember_instance()->add_htaccess_to_protected_folders();
        } elseif ('remove' === $data['type']) {
            $wpdb->query('DELETE FROM `' . esc_sql(wishlistmember_instance()->table_names->contentlevels) . '` WHERE `type`="~FOLDER"');

            if ($parentfolder && is_dir($root_of_folders)) {
                foreach (glob($root_of_folders . '/*', GLOB_ONLYDIR) as $dir_name) {
                    $dir_name = basename($dir_name);
                    $fullpath = $root_of_folders . '/' . $dir_name;
                    if (is_dir($fullpath)) {
                        $folder_id = wishlistmember_instance()->folder_id($dir_name);
                        wishlistmember_instance()->folder_protected($folder_id, false);
                        wishlistmember_instance()->set_content_levels('folders', $folder_id, []);
                    }
                }
            }
            wishlistmember_instance()->remove_all_htaccess_from_protected_folders();
        } else {
            return [
                'success' => false,
                'msg'     => __('Invalid operation', 'wishlist-member'),
                'data'    => $data,
            ];
        }
        return [
            'success' => true,
            'msg'     => __('Done', 'wishlist-member'),
            'data'    => $data,
        ];
    }

    /**
     * Get folders list.
     */
    public function get_folders_list()
    {
        $root_of_folders             = wlm_trim(wishlistmember_instance()->get_option('parentFolder'));
        $folder_protection_full_path = wishlistmember_instance()->folder_protection_full_path($root_of_folders);
        $items                       = [];
        if ($root_of_folders && is_dir($folder_protection_full_path)) {
            foreach (glob($folder_protection_full_path . '/*', GLOB_ONLYDIR) as $dir_name) {
                $item     = [];
                $dir_name = basename($dir_name);
                $fullpath = $folder_protection_full_path . '/' . $dir_name;
                if (is_dir($fullpath)) {
                    $folder_id          = wishlistmember_instance()->folder_id($dir_name);
                    $item['full_path']  = $fullpath;
                    $item['post_title'] = basename($fullpath);

                    $item['writable']          = is_writable($fullpath);
                    $item['htaccess_exists']   = file_exists($fullpath . '/.htaccess');
                    $item['htaccess_writable'] = is_writable($fullpath . '/.htaccess');
                    $item['wlm_protection']    = [wishlistmember_instance()->folder_protected($folder_id)];
                    $item['force_download']    = wishlistmember_instance()->folder_force_download($folder_id);

                    $item['ID'] = $folder_id;

                    $items[] = $item;
                }
            }
        }
        $content_type = 'folders';
        $folders      = '';
        foreach ($items as $item) {
            ob_start();
            require 'views/folders/content-item.php';
            $folders .= ob_get_clean();
        }
        return [
            'success' => true,
            'msg'     => __('Done', 'wishlist-member'),
            'folders' => $folders,
        ];
    }

    /**
     * Return files in a folder.
     *
     * @param  array $data Folder Data.
     * @return array
     */
    public function get_folders_files($data)
    {
        $files  = [];
        $handle = opendir($data['path']);
        if ($handle) {
            while (false !== ( $entry = readdir($handle) )) {
                if ('.' === $entry || '..' === $entry) {
                    continue;
                }
                if (! is_file($data['path'] . '/' . $entry)) {
                    continue;
                }
                if ('.htaccess' === $entry) {
                    continue;
                }
                $files[] = $entry;
            }
            if (count($files) <= 0) {
                return [
                    'success' => false,
                    'msg'     => __('Empty folder', 'wishlist-member'),
                    'data'    => $data,
                ];
            }
        } else {
            return [
                'success' => false,
                'msg'     => __('Invalid folder', 'wishlist-member'),
                'data'    => $data,
            ];
        }
        return [
            'success' => true,
            'msg'     => __('Done', 'wishlist-member'),
            'files'   => $files,
        ];
    }
}
