<?php

/**
 * Backup Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Backup Methods trait
 */
trait Backup_Methods
{
    /**
     * Backs up WishList Member Data
     *
     * @param  boolean $doing_reset True if doing a reset. Default false.
     * @return mixed FALSE on failure OR Date and Time of the Backup formatted as yyyymmddhhmmss on success
     */
    public function backup_queue($doing_reset = false)
    {
        ignore_user_abort(true);
        global $wpdb;
        wlm_set_time_limit(60 * 60 * 24);
        $post_data = wlm_post_data(true);

        $tables = [];
        $up     = [];
        if ('BackupSettings' === wlm_arrval($post_data, 'WishListMemberAction')) {
            if (wlm_arrval($post_data, 'backup_include_settings')) {
                $this->save_option('backup_include_settings', 1);
                $tables = array_values((array) $this->table_names);
                $up[]   = 's';
            } else {
                $this->save_option('backup_include_settings', 0);
                $tables = [];
            }
            if (wlm_arrval($post_data, 'backup_include_users')) {
                $this->save_option('backup_include_users', 1);
                array_unshift($tables, $wpdb->users, $wpdb->usermeta);
                $up[] = 'u';
            } else {
                $this->save_option('backup_include_users', 0);
            }
            if (wlm_arrval($post_data, 'backup_include_posts')) {
                $this->save_option('backup_include_posts', 1);
                array_unshift($tables, $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->commentmeta);
                $up[] = 'p';
            } else {
                $this->save_option('backup_include_posts', 0);
            }
        }

        if ($doing_reset) {
            $tables = array_values((array) $this->table_names);
            $up[]   = 's';
        } else {
            if ($this->get_option('backup_include_settings')) {
                $tables = array_values((array) $this->table_names);
                $up[]   = 's';
            }
            if ($this->get_option('backup_include_users')) {
                array_unshift($tables, $wpdb->users, $wpdb->usermeta);
                $up[] = 'u';
            }
            if ($this->get_option('backup_include_posts')) {
                array_unshift($tables, $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->commentmeta);
                $up[] = 'p';
            }
        }

        $tables = array_unique($tables);
        if (count($tables) <= 0) {
            $this->err = __('ERROR: No data to backup', 'wishlist-member');
            return false;
        }

        $up = count($up) ? '-' . implode('', $up) : '';

        $date = gmdate('YmdHis');

        $backupname = 'wlmbackup' . $up . '_' . $date . '_' . str_replace('.', '-', $this->version);
        $sqlname    = $backupname . '.sql';
        $tmpname    = $backupname . '.tmp';

        // Fix, some host add the ABSPATH to the WLM_BACKUP_PATH, following remove the equal part.
        $backupfolder = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));

        $outfile = $backupfolder . $tmpname;
        $httfile = $backupfolder . '.htaccess';
        if (! file_exists($backupfolder)) {
            @mkdir($backupfolder, 0755, true);
        }

        $httfilehandler = fopen($httfile, 'w');
        if (! $httfilehandler) {
            // Translators: 1: Backup path.
            $this->err = sprintf(__('ERROR: Cannot create backup file. Please check file permissions for <b>%1$s</b>', 'wishlist-member'), WLM_BACKUP_PATH);
            return false;
        }

        $deny = ! wlm_get_apache_version() || version_compare(wlm_get_apache_version(), '2.4') >= 0;
        $deny = $deny ? 'Require all denied' : 'deny from all';
        fwrite($httfilehandler, "<Limit GET POST>\n");
        fwrite($httfilehandler, "$deny\n");
        fwrite($httfilehandler, "</Limit>\n");
        fclose($httfilehandler);

        $f = fopen($outfile, 'w');

        if (! $f) {
            // Translators: 1: Backup path.
            $this->err = sprintf(__('ERROR: Cannot create backup file. Please check file permissions for <b>%1$s</b>', 'wishlist-member'), WLM_BACKUP_PATH);
            return false;
        }

        // Write file description.
        fwrite($f, "# WishList Member Backup\n");
        $date = $this->format_date($date);
        fwrite($f, "# Generated on {$date}\n");
        if (false !== strpos($up, 's')) {
            fwrite($f, "# Includes: WishList Member Settings\n");
        }
        if (false !== strpos($up, 'u')) {
            fwrite($f, "# Includes: Users\n");
        }
        if (false !== strpos($up, 'p')) {
            fwrite($f, "# Includes: Content\n");
        }
        fwrite($f, "\n# ----------------------\n\n");

        $backup_data = [
            'backup_name'      => $backupname,
            'folder'           => $backupfolder,
            'tables'           => $tables,
            'tables_cnt'       => count($tables),
            'processed_tables' => [],
        ];

        $x = ( new \WishListMember\API_Queue() )->add_queue('backup_queue', serialize($backup_data));
        if ($x) {
            $this->err = __('Unable to queue backup', 'wishlist-member');
            return false;
        }
        $this->save_option('backup_queue_pause', 0);

        $this->msg = __('WishList Member is currently doing a backup.', 'wishlist-member');

        return $result;
    }

    /**
     * Process backup queue
     *
     * @return boolean
     */
    public function process_backup_queue()
    {
        global $wpdb;

        // Pause?
        if ($this->get_option('backup_queue_pause')) {
            return false;
        }

        if (false !== get_transient('wlm_is_doing_backup')) {
            return false;
        }

        $api_queue   = new \WishListMember\API_Queue();
        $queue       = $api_queue->get_queue('backup_queue');
        $queue_count = 0;
        $queue_left  = 0;
        $queue_val   = [];
        $queue_notes = '';
        $queue_table = '';

        if (count($queue)) {
            $queue       = array_pop($queue);
            $queue_val   = wlm_maybe_unserialize($queue->value);
            $queue_count = $queue_val['tables_cnt'];
            $queue_left  = count($queue_val['tables']);
        } else {
            delete_transient('wlm_is_doing_backup');
            return false;
        }

        $sqlname = $queue_val['backup_name'] . '.sql';
        $tmpname = $queue_val['backup_name'] . '.tmp';
        $outfile = $queue_val['folder'] . $tmpname;

        if (! file_exists($outfile)) {
            $api_queue->delete_queue($queue->ID);
            delete_transient('wlm_is_doing_backup');
            return false;
        }

        $f = fopen($outfile, 'a');
        if (! $f) {
            // Translators: 1: Backup folder.
            $queue_notes = sprintf(__('ERROR: Cannot create backup file. Please check file permissions for <b>%1$s</b>', 'wishlist-member'), $queue_val['folder']);
            delete_transient('wlm_is_doing_backup');
            return false;
        }

        wlm_set_time_limit(60 * 60 * 24);
        ignore_user_abort(true);

        set_transient('wlm_is_doing_backup', 1, MINUTE_IN_SECONDS);

        $queue_table = array_shift($queue_val['tables']);

        set_transient('wlm_backup_monitor', $queue_table, MINUTE_IN_SECONDS);

        fwrite($f, "# Table {$queue_table}\n");
        fwrite($f, "DROP TABLE IF EXISTS `{$queue_table}`;\n");
        $create = $wpdb->get_row('SHOW CREATE TABLE `' . esc_sql($queue_table) . '`', ARRAY_A);
        $create = str_replace(["\r", "\n"], ' ', $create['Create Table']);
        fwrite($f, $create . ";\n");

        // WP uses mysqli from v3.9 onwards so we check for it.
        if ($wpdb->use_mysqli) {
            // Using mysqli_query directly is less memory intensive for this purpose. Using call_user_func() here to prevent phpcs flagging this.
            $r              = call_user_func('mysqli_query', $wpdb->dbh, 'SELECT * FROM ' . esc_sql($queue_table));
            $fetch_function = 'mysqli_fetch_assoc';
        } else {
            $r              = $wpdb->get_results('SELECT * FROM ' . esc_sql($queue_table));
            $fetch_function = 'array_shift';
        }

        while ($out = $fetch_function($r)) {
            $cols = array_keys($out);
            $vals = array_values($out);
            $out  = $wpdb->prepare(
                'INSERT INTO `' . esc_sql($queue_table) . '` (' . implode(', ', array_fill(0, count($cols), '%0s')) . ') VALUES (' . implode(', ', array_fill(0, count($vals), '%s')) . ')',
                ...array_values($cols),
                ...array_values($vals)
            );
            fwrite($f, $out . ";\n");
        }
        fwrite($f, "\n");

        array_unshift($queue_val['processed_tables'], $queue_table);
        $d = [
            'value' => serialize($queue_val),
            'notes' => $queue_notes,
        ];
        $api_queue->update_queue($queue->ID, $d);

        if (count($queue_val['tables']) <= 0) {
            fwrite($f, "\n# --- END OF BACKUP FILE {$queue_val['backup_name']} ---\n");
            fclose($f);
            rename($outfile, $queue_val['folder'] . $sqlname);
            $api_queue->delete_queue($queue->ID);
        }

        delete_transient('wlm_is_doing_backup');

        return false;
    }

    /**
     * Backs up WishList Member Data
     *
     * @param  boolean $doing_reset True if doing a reset. Default false.
     * @return mixed FALSE on failure OR Date and Time of the Backup formatted as yyyymmddhhmmss on success
     */
    public function backup_generate($doing_reset = false)
    {
        global $wpdb;

        ignore_user_abort(true);
        wlm_set_time_limit(60 * 60 * 24);
        $post_data = wlm_post_data(true);

        $tables = [];
        $up     = [];
        if ('BackupSettings' === wlm_arrval($post_data, 'WishListMemberAction')) {
            if (wlm_arrval($post_data, 'backup_include_settings')) {
                $this->save_option('backup_include_settings', 1);
                $tables = array_values((array) $this->table_names);
                $up[]   = 's';
            } else {
                $this->save_option('backup_include_settings', 0);
                $tables = [];
            }
            if (wlm_arrval($post_data, 'backup_include_users')) {
                $this->save_option('backup_include_users', 1);
                array_unshift($tables, $wpdb->users, $wpdb->usermeta);
                $up[] = 'u';
            } else {
                $this->save_option('backup_include_users', 0);
            }
            if (wlm_arrval($post_data, 'backup_include_posts')) {
                $this->save_option('backup_include_posts', 1);
                array_unshift($tables, $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->commentmeta);
                $up[] = 'p';
            } else {
                $this->save_option('backup_include_posts', 0);
            }
        }

        if ($doing_reset) {
            $tables = array_values((array) $this->table_names);
            $up[]   = 's';
        } else {
            if ($this->get_option('backup_include_settings')) {
                $tables = array_values((array) $this->table_names);
                $up[]   = 's';
            }
            if ($this->get_option('backup_include_users')) {
                array_unshift($tables, $wpdb->users, $wpdb->usermeta);
                $up[] = 'u';
            }
            if ($this->get_option('backup_include_posts')) {
                array_unshift($tables, $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->commentmeta);
                $up[] = 'p';
            }
        }

        $tables = array_unique($tables);
        if (count($tables) <= 0) {
            $this->err = __('ERROR: No data to backup', 'wishlist-member');
            return false;
        }

        $up = count($up) ? '-' . implode('', $up) : '';

        $date = gmdate('YmdHis');

        $backupname = 'wlmbackup' . $up . '_' . $date . '_' . str_replace('.', '-', $this->version);
        $sqlname    = $backupname . '.sql';
        $tmpname    = $backupname . '.tmp';

        // Fix, some host add the ABSPATH to the WLM_BACKUP_PATH, following remove the equal part.
        $backupfolder = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));

        $outfile = $backupfolder . $tmpname;
        $httfile = $backupfolder . '.htaccess';
        if (! file_exists($backupfolder)) {
            @mkdir($backupfolder, 0755, true);
        }

        $httfilehandler = fopen($httfile, 'w');
        if (! $httfilehandler) {
            // Translators: 1: Backup path.
            $this->err = sprintf(__('ERROR: Cannot create backup file. Please check file permissions for <b>%1$s</b>', 'wishlist-member'), WLM_BACKUP_PATH);
            return false;
        }

        $deny = ! wlm_get_apache_version() || version_compare(wlm_get_apache_version(), '2.4') >= 0;
        $deny = $deny ? 'Require all denied' : 'deny from all';
        fwrite($httfilehandler, "<Limit GET POST>\n");
        fwrite($httfilehandler, "$deny\n");
        fwrite($httfilehandler, "</Limit>\n");
        fclose($httfilehandler);

        $f = fopen($outfile, 'w');

        if (! $f) {
            // Translators: 1: Backup path.
            $this->err = sprintf(__('ERROR: Cannot create backup file. Please check file permissions for <b>%1$s</b>', 'wishlist-member'), WLM_BACKUP_PATH);
            return false;
        }

        // Write file description.
        fwrite($f, "# WishList Member Backup\n");
        $date = $this->format_date($date);
        fwrite($f, "# Generated on {$date}\n");
        if (false !== strpos($up, 's')) {
            fwrite($f, "# Includes: WishList Member Settings\n");
        }
        if (false !== strpos($up, 'u')) {
            fwrite($f, "# Includes: Users\n");
        }
        if (false !== strpos($up, 'p')) {
            fwrite($f, "# Includes: Content\n");
        }
        fwrite($f, "\n# ----------------------\n\n");

        foreach ($tables as $table) {
            fwrite($f, "# Table {$table}\n");
            fwrite($f, "DROP TABLE IF EXISTS `{$table}`;\n");
            $create = $wpdb->get_row('SHOW CREATE TABLE `' . esc_sql($table) . '`', ARRAY_A);
            $create = str_replace(["\r", "\n"], ' ', $create['Create Table']);
            fwrite($f, $create . ";\n");

            // WP uses mysqli from v3.9 onwards so we check for it.
            if ($wpdb->use_mysqli) {
                // Using mysqli_query directly is less memory intensive for this purpose. Using call_user_func() here to prevent phpcs flagging this.
                $r              = call_user_func('mysqli_query', $wpdb->dbh, 'SELECT * FROM ' . esc_sql($table));
                $fetch_function = 'mysqli_fetch_assoc';
            } else {
                $r              = $wpdb->get_results('SELECT * FROM ' . esc_sql($table));
                $fetch_function = 'array_shift';
            }

            while ($out = $fetch_function($r)) {
                $cols = array_keys($out);
                $vals = array_values($out);
                $out  = $wpdb->prepare(
                    'INSERT INTO `' . esc_sql($table) . '` (' . implode(', ', array_fill(0, count($cols), '%0s')) . ') VALUES (' . implode(', ', array_fill(0, count($vals), '%s')) . ')',
                    ...array_values($cols),
                    ...array_values($vals)
                );
                fwrite($f, $out . ";\n");
            }
            fwrite($f, "\n");
        }
        fwrite($f, "\n# --- END OF BACKUP FILE {$backupname} ---\n");
        fclose($f);
        rename($outfile, $backupfolder . $sqlname);

        $result = $this->backup_details($sqlname);
        // Translators: 1: Backup Date.
        $this->msg = sprintf(__('WishList Member successfully backed-up on %1$s.', 'wishlist-member'), $this->format_date($result['date']));

        return $result;
    }

    /**
     * Download Backup
     *
     * @param  string $backup_name Name of backup file.
     * @return boolean
     */
    public function backup_download($backup_name)
    {
        $folder_path = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
        $file        = $folder_path . $backup_name . '.sql';
        if (! file_exists($file)) {
            $this->err = __('Backup file not found.', 'wishlist-member');
            return false;
        }

        $fname = basename($file);
        header('Content-type: text/plain');
        header('Conent-length: ' . filesize($file));
        header('Content-disposition: attachment; filename="' . esc_attr($fname) . '"');
        readfile($file);
        exit;
    }

    /**
     * Restore a WishList Member Backup
     *
     * @param  string  $backup_name    Name of backup file.
     * @param  boolean $backup_current TRUE to backup current database first before restoration. Default true.
     * @return boolean
     */
    public function backup_restore($backup_name, $backup_current = true)
    {
        $result = $this->backup_import($backup_current, $backup_name);
        if ($result) {
            // Translators: 1: Backup date.
            $this->msg = sprintf(__('WishList Member Settings successfully restored to %1$s.', 'wishlist-member'), $this->format_date($result['date']));
            return true;
        } else {
            $this->err = __('An error occured while trying to restore WishList Member Settings', 'wishlist-member');
            return false;
        }
    }

    /**
     * Get backup Details
     *
     * @param  string $backup_name Name of backup file.
     * @return array
     */
    public function backup_details($backup_name)
    {

        if ('.sql' === substr($backup_name, -4)) {
            $backup_name = substr($backup_name, 0, -4);
        }

        $ar              = explode('_', $backup_name);
        list($name, $up) = explode('-', $ar[0]);
        if ($up) {
            $settings = false !== strpos($up, 's');
            $users    = false !== strpos($up, 'u');
            $posts    = false !== strpos($up, 'p');
        }
        $date = $ar[1];
        $ver  = str_replace('-', '.', $ar[2]);
        $full = $backup_name;

        $backup = [
            'name'     => $name,
            'date'     => $date,
            'ver'      => $ver,
            'full'     => $full,
            'users'    => $users,
            'posts'    => $posts,
            'settings' => $settings,
        ];

        return $backup;
    }

    /**
     * Deletes a WishList Member backup.
     *
     * @param  string $backup_name Name of backup file.
     * @return array               Backup details
     */
    public function backup_delete($backup_name)
    {
        $folder_path = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
        unlink($folder_path . $backup_name . '.sql');

        $result = $this->backup_details($backup_name);
        // Translators: 1: Backup date.
        $this->msg = sprintf(__('WishList Member Settings "%1$s" deleted.', 'wishlist-member'), $this->format_date($result['date']));
        return $result;
    }

    /**
     * Lists all WishList Member Backups
     *
     * @return array of Backup Codes (yyyymmddhhmmss)
     */
    public function backup_list_all()
    {
        global $wpdb;
        $folder_path = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));

        $results = glob($folder_path . '*.sql');
        foreach ($results as $k => $v) {
            $results[ $k ] = substr(basename($v), 0, -4);
        }

        $backups = [];
        foreach ($results as $result) {
            $backup = $this->backup_details($result);
            $file   = $folder_path . $backup['full'] . '.sql';
            if (file_exists($file)) {
                $backup['size'] = filesize($file);
            }
            $backups[ $backup['date'] ] = $backup;
        }
        krsort($backups);
        return $backups;
    }

    /**
     * Import Settings
     *
     * @return boolean
     */
    /**
     * Import backup
     *
     * @param  boolean $backup_current True to backup current. Default true.
     * @param  string  $backup_name    Name of backup file.
     * @return boolean
     */
    public function backup_import($backup_current = true, $backup_name = null)
    {
        ignore_user_abort(true);
        wp_raise_memory_limit('backup_import');

        global $wpdb;
        if ($backup_current) {
            $this->backup_generate();
        }
        if (is_null($backup_name)) {
            $files_data  = $_FILES;
            $showmsg     = true;
            $file_name   = $files_data['ImportSettingsfile']['name'];
            $backup_file = $files_data['ImportSettingsfile']['tmp_name'];
            $file_size   = $files_data['ImportSettingsfile']['size'];
            $file_type   = $files_data['ImportSettingsfile']['type'];
            $err_num     = isset($files_data['ImportSettingsfile']['error']) ? $files_data['ImportSettingsfile']['error'] : 0;
        } else {
            $showmsg     = false;
            $folder_path = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
            $backup_file = $folder_path . $backup_name . '.sql';
        }

        if ($err_num && $showmsg) {
            $file_errors = [
                1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                3 => 'The uploaded file was only partially uploaded',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
            ];
            if (isset($file_errors[ $err_num ])) {
                $this->err = $file_errors[ $err_num ];
            } else {
                $this->err = __('An unknown error number occured while trying to import file', 'wishlist-member');
            }
            return false;
        }

        $f = fopen($backup_file, 'r');
        if (! $f) {
            if ($showmsg) {
                $this->err = __('An error occured while trying to import file', 'wishlist-member');
            }
            return false;
        }

        while (! feof($f)) {
            $line       = trim(fgets($f, 1000000));
            $first_char = substr($line, 0, 1);
            if ('DROP TABLE IF EXISTS ' === substr($line, 0, 21) || 'CREATE TABLE ' === substr($line, 0, 13) || 'INSERT INTO ' === substr($line, 0, 12)) {
                if (false === call_user_func([$wpdb, 'query'], $line)) {
                    $this->err = __('An SQL error occured while trying to import file.', 'wishlist-member');
                }
            }
        }

        if ($showmsg) {
            $this->msg = __('WishList Member Settings successfully imported.', 'wishlist-member');
        }
        return $this->backup_details($backup_name);
    }

    /**
     * Reset WishList Member settings
     */
    public function reset_settings($data = null)
    {
        global $wpdb;
        if (is_null($data)) {
            $data = wlm_post_data(true);
        }
        if (wlm_arrval($data, 'resetSettingConfirm')) {
            wlm_cache_flush();
            if ($this->backup_generate(true)) {
                $license_key = $this->get_option('LicenseKey');

                foreach ($this->table_names as $table) {
                    $wpdb->query('TRUNCATE `' . esc_sql($table) . '`');
                }

                $data = [
                    'option_name'  => 'LicenseKey',
                    'option_value' => wlm_maybe_serialize($license_key),
                ];
                $wpdb->insert($this->options_table, $data);

                $this->activate();
                $this->WPWLKeyProcess();

                $this->msg = __('WishList Member reset to default settings', 'wishlist-member');
            } else {
                $this->err = __('Reset Aborted. Failed to backup current settings.', 'wishlist-member');
                return false;
            }
        }
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_backup_queue', [$wlm, 'process_backup_queue']);
    }
);
