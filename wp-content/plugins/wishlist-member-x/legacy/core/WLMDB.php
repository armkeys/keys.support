<?php

/**
 * Plugin Methods Class for WishList Member
 *
 * @package wishlistmember
 */

if (
    ! defined('ABSPATH')
) {
    die();
}
if (! class_exists('WishListMemberDBMethods')) {

    /**
     * Plugin Methods WishList Member Class
     *
     * @package    wishlistmember
     * @subpackage classes
     */
    class WishListMemberDBMethods extends WishListMemberCore
    {
        /**
         * Migrate Level Information for all Content to Database
         *
         * @global object $wpdb
         */
        public function MigrateContentLevelData()
        {
            global $wpdb;

            if (1 === (int) get_option($this->plugin_option_name . '_MigrateContentLevelData')) {
                return;
            }

            ignore_user_abort(true);
            // Migrate category levels.
            $content_types = [
                'MembershipCategories' => '~CATEGORY',
                'MembershipPages'      => 'page',
                'MembershipPosts'      => 'post',
                'MembershipComments'   => '~COMMENT',
            ];

            foreach ($content_types as $Option => $content_type) {
                $data = $this->get_option($Option);
                if (false === $data) {
                    continue;
                }
                foreach ((array) $data as $level => $ids) {
                    $ids = array_diff(array_unique(explode(',', $ids)), ['0', '']);
                    if (count($ids)) {
                        foreach ($ids as $id) {
                            if (is_numeric($id)) {
                                $wpdb->insert(
                                    $this->table_names->contentlevels,
                                    [
                                        'content_id' => $id,
                                        'level_id'   => $level,
                                        'type'       => $content_type,
                                    ],
                                    ['%d', '%s', '%s']
                                );
                            }
                        }
                    }
                }
            }

            // Category protection.
            $ids = $this->get_option('CatProtect');
            if (false !== $ids) {
                $ids = array_diff(
                    get_terms(
                        [
                            'taxonomy' => 'category',
                            'fields'   => 'ids',
                            'get'      => 'all',
                        ]
                    ),
                    explode(',', $ids)
                );
                if (count($ids)) {
                    foreach ($ids as $id) {
                        if (is_numeric($id)) {
                            $wpdb->insert(
                                $this->table_names->contentlevels,
                                [
                                    'content_id' => $id,
                                    'level_id'   => 'Protection',
                                    'type'       => '~CATEGORY',
                                ],
                                ['%d', '%s', '%s']
                            );
                        }
                    }
                }
            }

            // Content protection.
            $ids = $this->get_option('Protect');
            if (false !== $ids) {
                $ids = array_diff(array_unique(explode(',', $ids)), ['0', '']);
                if (count($ids)) {
                    foreach ($ids as $id) {
                        if (is_numeric($id)) {
                            $wpdb->insert(
                                $this->table_names->contentlevels,
                                [
                                    'content_id' => $id,
                                    'level_id'   => 'Protection',
                                    'type'       => 'post',
                                ],
                                ['%d', '%s', '%s']
                            );
                        }
                    }
                }
            }

            update_option($this->plugin_option_name . '_MigrateContentLevelData', 1);
        }

        /**
         * Migrate Level Information
         *
         * @global object $wpdb
         */
        public function MigrateLevelData()
        {
            global $wpdb;

            if (1 !== (int) $this->data_migrated || 1 === (int) get_option($this->plugin_option_name . '_MigrateLevelData')) {
                return;
            }

            ignore_user_abort(true);
            $this->CreateWLMDBTables();

            $userlevelsTable        = $this->table_names->userlevels;
            $userlevelsTableOptions = $this->table_names->userlevel_options;
            $userTableOptions       = $this->table_names->user_options;

            // User level data.
            $memberLevels = (array) $this->get_option('Members');
            $cancelled    = (array) $this->get_option('Cancelled');
            $unconfirmed  = (array) $this->get_option('UnConfirmed');
            $forapproval  = (array) $this->get_option('Pending');

            $levels     = \WishListMember\Level::get_all_levels();
            $allmembers = [];
            foreach ($levels as $level) {
                $members    = array_unique(explode(',', $memberLevels[ $level ]));
                $allmembers = array_merge($allmembers, $members);

                foreach ($members as $member) {
                    // Membership Level.
                    $data = [
                        'user_id'  => $member,
                        'level_id' => $level,
                    ];
                    if ($wpdb->insert($userlevelsTable, $data)) {
                        $userlevel_id = $wpdb->insert_id;

                        // Transaction IDs.
                        $trans = get_user_meta($member, 'wlm_sctxns', true);
                        $data  = [
                            'userlevel_id' => $userlevel_id,
                            'option_name'  => 'transaction_id',
                            'option_value' => wlm_maybe_serialize($trans[ $level ]),
                        ];
                        $wpdb->insert($userlevelsTableOptions, $data);

                        // Level Registration Dates.
                        $regdates = get_user_meta($member, 'wpm_leveltimestamp', true);
                        $this->user_level_timestamp($member, $level, $regdates[ $level ]);

                        // Cancelled Status.
                        $status = preg_match('/,(' . $member . ';[0-9]*),/', ',' . $cancelled[ $level ] . ',', $match) > 0;
                        if ($status) {
                            list($id, $date) = explode(';', $match[1]);
                            $this->level_cancelled($level, $member, true, $date);
                        }

                        // Unconfirmed Status.
                        $status = preg_match('/,' . $member . ',/', ',' . $unconfirmed[ $level ] . ',') > 0;
                        if ($status) {
                            $this->level_unconfirmed($level, $member, true);
                        }

                        // For Approval Status.
                        $status = preg_match('/,' . $member . ',/', ',' . $forapproval[ $level ] . ',') > 0;
                        if ($status) {
                            $this->level_for_approval($level, $member, true);
                        }
                    }
                }
            }

            $allmembers = array_unique($allmembers);

            // Per user data.
            $nonseq = array_unique(explode(',', (string) wlm_arrval($memberLevels, 'nonsequential')));
            foreach ($allmembers as $member) {
                // Sequential upgrade.
                $wpdb->insert(
                    $userTableOptions,
                    [
                        'user_id'      => $member,
                        'option_name'  => 'sequential',
                        'option_value' => in_array($member, $nonseq) ? 0 : 1,
                    ]
                );
            }
            // Migrate all wpm_ and wlm_ data except wpm_leveltimestamp and wlm_sctxns.
            $wpdb->query($wpdb->prepare(' INSERT INTO `' . esc_sql($userTableOptions) . '` (`user_id`,`option_name`,`option_value`) SELECT `user_id`,`meta_key`,`meta_value` FROM `' . esc_sql($wpdb->usermeta) . "` WHERE `meta_key`<>'wpm_leveltimestamp' AND `meta_key`<>'wlm_sctxns' AND (`meta_key` LIKE %s OR `meta_key` LIKE %s)", 'wlm%', 'wpm%'));

            /*
             * remove old data format for membership levels, cancelled status,
             * unconfirmed status, and pending status from our options table
             */

            $this->delete_option('Members');
            $this->delete_option('Cancelled');
            $this->delete_option('UnConfirmed');
            $this->delete_option('Pending');

            // End of data migration.
            update_option($this->plugin_option_name . '_MigrateLevelData', 1);
        }

        /**
         * Create WishList Member Database Tables
         */
        public function CreateWLMDBTables()
        {
            global $wpdb;

            /*
             * Important: This now makes use of dbDelta function
             *
             * Please refer to the following URL for instructions:
             * http://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
             *
             * VIOLATORS OF dbDelta RULES WILL BE PROSECUTED :D
             */

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            $charset_collate = $wpdb->get_charset_collate();

            $table     = $this->table_prefix . 'level_options';
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  level_id bigint(20) NOT NULL,
			  option_name varchar(64) NOT NULL,
			  option_value longtext NOT NULL,
			  autoload varchar(20) NOT NULL DEFAULT 'yes',
			  PRIMARY KEY  (ID)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // Content Levels.
            $table     = $this->table_prefix . 'contentlevels';
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  content_id bigint(20) NOT NULL,
			  level_id varchar(32) NOT NULL,
			  type varchar(21) NOT NULL,
			  PRIMARY KEY  (ID),
			  UNIQUE KEY content_id (content_id,level_id,type),
			  KEY content_id2 (content_id),
			  KEY level_id (level_id),
			  KEY type (type)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // Content Levels Options.
            $table     = $this->table_prefix . 'contentlevel_options';
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  contentlevel_id bigint(20) NOT NULL,
			  option_name varchar(64) NOT NULL,
			  option_value longtext NOT NULL,
			  autoload varchar(20) NOT NULL DEFAULT 'yes',
			  PRIMARY KEY  (ID),
			  UNIQUE KEY contentlevel_id (contentlevel_id,option_name),
			  KEY autoload (autoload),
			  KEY contentlevel_id2 (contentlevel_id),
			  KEY option_name (option_name)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // User Levels.
            $table     = $this->table_prefix . 'userlevels';
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  user_id bigint(20) NOT NULL,
			  level_id bigint(20) NOT NULL,
			  PRIMARY KEY  (ID),
			  UNIQUE KEY user_id (user_id,level_id),
			  KEY user_id2 (user_id),
			  KEY level_id (level_id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // User Level Options Levels.
            $table     = $this->table_prefix . 'userlevel_options';
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  userlevel_id bigint(20) NOT NULL,
			  option_name varchar(64) NOT NULL,
			  option_value longtext NOT NULL,
			  autoload varchar(20) NOT NULL DEFAULT 'yes',
			  PRIMARY KEY  (ID),
			  UNIQUE KEY userlevel_id (userlevel_id,option_name),
			  KEY autoload (autoload),
			  KEY userlevel_id2 (userlevel_id),
			  KEY option_name (option_name)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // User Options.
            $table     = $this->table_prefix . 'user_options';
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  user_id bigint(20) NOT NULL,
			  option_name varchar(64) NOT NULL,
			  option_value longtext NOT NULL,
			  autoload varchar(20) NOT NULL DEFAULT 'yes',
			  PRIMARY KEY  (ID),
			  UNIQUE KEY user_id (user_id,option_name),
			  KEY autoload (autoload),
			  KEY user_id2 (user_id),
			  KEY option_name (option_name)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // API Queue.
            $table     = $this->table_prefix . 'api_queue';
            $structure = "CREATE TABLE {$table} (
			  ID bigint(20) NOT NULL AUTO_INCREMENT,
			  name varchar(64) CHARACTER SET latin1 NOT NULL,
			  value longtext NOT NULL,
			  notes varchar(500) CHARACTER SET latin1 DEFAULT NULL,
			  tries int(11) NOT NULL,
			  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY  (ID)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // WLM Logs.
            $table     = $this->table_prefix . 'logs';
            $structure = "CREATE TABLE {$table} (
			  ID bigint NOT NULL AUTO_INCREMENT,
			  user_id bigint NOT NULL,
			  log_group varchar(32) NOT NULL,
			  log_key varchar(64) NOT NULL,
  			date_added datetime NOT NULL,
			  log_value longtext NOT NULL,
			  PRIMARY KEY  (ID),
			  UNIQUE KEY user_id__log_group__log_key__date_added (user_id,log_group,log_key,date_added),
			  KEY user_id (user_id),
			  KEY user_id__log_group (user_id,log_group),
			  KEY user_id__log_group__log_key (user_id,log_group,log_key)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // Email Broadcast Table.
            $table     = $this->table_prefix . 'emailbroadcast';
            $structure = "CREATE TABLE {$table} (
			  id int(9) NOT NULL AUTO_INCREMENT,
			  from_name varchar(200) NOT NULL,
			  from_email varchar(200) NOT NULL,
			  subject varchar(400) NOT NULL,
			  text_body text NOT NULL,
			  footer text,
			  send_to varchar(15) DEFAULT NULL,
			  mlevel text NOT NULL,
			  sent_as varchar(5) DEFAULT NULL,
			  status varchar(10) NOT NULL DEFAULT 'Queueing',
			  otheroptions text,
			  total_queued int(11) DEFAULT '0',
			  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  UNIQUE KEY id (id),
			  PRIMARY KEY  (id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // Email Queue.
            $table     = $this->table_prefix . 'email_queue';
            $structure = "CREATE TABLE {$table} (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
			  broadcastid int(9) NOT NULL,
			  userid bigint(20) NOT NULL,
			  failed int(1) NOT NULL DEFAULT '0',
			  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  UNIQUE KEY id (id),
			  KEY broadcastid (broadcastid)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // Let s check if we already have a foreign key.
            // If not, lets add it.
            // If we have, let check if the script already made multiple instance of FKs and delete them if it did.
            $fks = $wpdb->get_col(
                $wpdb->prepare(
                    'SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = %s AND TABLE_NAME = %s AND REFERENCED_TABLE_NAME = %s',
                    DB_NAME,
                    $table,
                    $this->table_prefix . 'emailbroadcast'
                )
            );
            if (count($fks) > 0) {
                unset($fks[0]); // Lets leave the first one.
                if (count($fks) > 0) { // Delete the others.
                    foreach ($fks as $fk) {
                        $wpdb->query('ALTER TABLE `' . esc_sql($table) . '` DROP FOREIGN KEY `' . esc_sql($fk) . '`');
                    }
                }
            } else {
                try {
                    // Enclose in try..catch as this query can fail.
                    $wpdb->query('ALTER TABLE `' . esc_sql($table) . '` ADD FOREIGN KEY (broadcastid) REFERENCES ' . esc_sql($this->table_prefix . 'emailbroadcast') . ' (id) ON DELETE CASCADE ON UPDATE CASCADE');
                } catch (Exception $e) {
                    null;
                }
            }

            // For Scheduler.
            $old_table = $wpdb->prefix . 'wlm_contentsched';
            $table     = $wpdb->prefix . 'wlcc_contentsched';
            if ($wpdb->query($wpdb->prepare('SELECT * FROM Information_schema.tables WHERE table_name=%s AND table_schema=%s', $old_table, DB_NAME))) { // Check if the old table exists then rename.
                $wpdb->query('ALTER TABLE ' . esc_sql($old_table) . ' RENAME ' . esc_sql($table));
            }

            $structure = "CREATE TABLE {$table} (
			  id bigint(20) NOT NULL auto_increment,
			  post_id bigint(20) NOT NULL,
			  mlevel varchar(15) NOT NULL,
			  num_days int(11) NOT NULL,
				hide_days int(11) NOT NULL DEFAULT 0,
				show_type varchar(20) NOT NULL,
				show_on_date datetime NULL,
				on_date_hide_days int NOT NULL DEFAULT 0,
			  date_added timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;
            // Lets add hide_days NOTE: dbDelta sometimes seems not to work.
            if (! (bool) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND COLUMN_NAME='hide_days'", DB_NAME, $table))) {
                $wpdb->query('ALTER TABLE `' . esc_sql($table) . '` ADD `hide_days` int(11) NOT NULL DEFAULT 0 AFTER `num_days`');
            }

            // For Archiver.
            $table     = $wpdb->prefix . 'wlcc_contentarchiver';
            $structure = "CREATE TABLE {$table} (
			  id bigint(20) NOT NULL auto_increment,
			  post_id bigint(20) NOT NULL,
			  mlevel varchar(15) NOT NULL,
			  exp_date datetime,
			  date_added timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // For Manager.
            $table     = $wpdb->prefix . 'wlcc_contentmanager_repost';
            $structure = "CREATE TABLE {$table} (
			  id bigint(20) NOT NULL auto_increment,
			  post_id bigint(20) NOT NULL,
			  due_date datetime NOT NULL,
			  rep_num int,
			  rep_by varchar(10),
			  rep_end int,
			  date_added timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            $table     = $wpdb->prefix . 'wlcc_contentmanager_move';
            $structure = "CREATE TABLE {$table} (
			  id bigint(20) NOT NULL auto_increment,
			  post_id bigint(20) NOT NULL,
			  action varchar(15) NOT NULL,
			  categories text NOT NULL,
			  due_date datetime NOT NULL,
			  date_added timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            $table     = $wpdb->prefix . 'wlcc_contentmanager_set';
            $structure = "CREATE TABLE {$table} (
			  id bigint(20) NOT NULL auto_increment,
			  post_id bigint(20) NOT NULL,
			  due_date datetime NOT NULL,
			  status varchar(10) NOT NULL,
			  date_added timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            $table     = $wpdb->prefix . 'wlm_presto_player_visits';
            $structure = "CREATE TABLE {$table} (
			  id bigint(20) NOT NULL auto_increment,
			  visit_time bigint(20) NOT NULL,
			  user_id bigint(20) NOT NULL,
			  video_id bigint(20) NOT NULL,
			  percent bigint(20) NOT NULL,
			  created_at datetime NOT NULL,
			  updated_at timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id)
			) {$charset_collate};";
            dbDelta($structure);
            $this->table_names->$table = $table;

            // Reload table names.
            $this->LoadTables(true);
        }

        /**
         * Retreive contentlevel_id from a level/content combination
         *
         * @global object $wpdb
         * @param  <type> $level_id
         * @param  <type> $content_id
         * @return <type>
         */
        public function Get_ContentLevelID($level_id, $content_id)
        {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare('SELECT `ID` FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `content_id`=%d AND `level_id`=%s', $content_id, $level_id));
        }

        /**
         * Retrieve Content Level Meta
         *
         * @global object $wpdb
         * @param  <type> $level_id
         * @param  <type> $content_id
         * @param  <type> $meta
         * @return <type>
         */
        public function Get_ContentLevelMeta($level_id, $content_id, $meta)
        {
            global $wpdb;
            $contentlevel_id = $this->Get_ContentLevelID($level_id, $content_id);
            if ($contentlevel_id) {
                return $wpdb->get_var($wpdb->prepare('SELECT `option_value` FROM `' . esc_sql($this->table_names->contentlevel_options) . '` WHERE `option_name`=%s AND `contentlevel_id`=%d', $meta, $contentlevel_id));
            }
            return false;
        }

        /**
         * Add new Content Level Meta
         *
         * @global object $wpdb
         * @param  <type> $level_id
         * @param  <type> $content_id
         * @param  <type> $meta
         * @param  <type> $value
         * @return <type>
         */
        public function Add_ContentLevelMeta($level_id, $content_id, $meta, $value)
        {
            global $wpdb;
            if ($this->Get_ContentLevelMeta($level_id, $content_id, $meta)) {
                return false;
            }
            return $wpdb->insert(
                $this->table_names->contentlevel_options,
                [
                    'contentlevel_id' => $contentlevel_id = $this->Get_ContentLevelID($level_id, $content_id),
                    'option_name'     => $meta,
                    'option_value'    => $value,
                ],
                ['%d', '%s', '%s']
            );
        }

        /**
         * Update Content Level Meta
         *
         * @global object $wpdb
         * @param  <type> $level_id
         * @param  <type> $content_id
         * @param  <type> $meta
         * @param  <type> $value
         * @return <type>
         */
        public function Update_ContentLevelMeta($level_id, $content_id, $meta, $value)
        {
            global $wpdb;
            $contentlevel_id = $this->Get_ContentLevelID($level_id, $content_id);
            return $wpdb->update(
                $this->table_names->contentlevel_options,
                [
                    'contentlevel_id' => $contentlevel_id,
                    'option_name'     => $meta,
                    'option_value'    => $value,
                ],
                [
                    'contentlevel_id' => $contentlevel_id,
                    'option_name'     => $meta,
                ],
                ['%d', '%s', '%s'],
                ['%d', '%s']
            );
        }

        /**
         * Delete Content Level Meta
         *
         * @global object $wpdb
         * @param  <type> $level_id
         * @param  <type> $content_id
         * @param  <type> $meta
         * @return <type>
         */
        public function Delete_ContentLevelMeta($level_id, $content_id, $meta)
        {
            global $wpdb;
            return $wpdb->delete(
                $this->table_names->contentlevel_options,
                [
                    'contentlevel_id' => $this->Get_ContentLevelID($level_id, $content_id),
                    'option_name'     => $meta,
                ]
            );
        }

        /**
         * Retrieves all Content Level Meta and returns it as an associative array
         *
         * @global object $wpdb
         * @param  string|integer $level_id   Level ID.
         * @param  string|integer $content_id Content ID.
         * @return array Associative array of meta=>value pairs
         */
        public function Get_AllContentLevelMeta($level_id, $content_id)
        {
            global $wpdb;
            return array_column(
                $wpdb->get_results(
                    $wpdb->prepare(
                        'SELECT `option_name`,`option_value` FROM `' . esc_sql($this->table_names->contentlevel_options) . '` WHERE `contentlevel_id`=%d',
                        $this->Get_ContentLevelID($level_id, $content_id)
                    ),
                    ARRAY_N
                ),
                1, // array_column: 2nd column as value.
                0  // array_column: 1st column as key.
            );
        }

        /**
         * Deletes all Content Level Meta
         *
         * @global object $wpdb
         * @param  <type> $level_id
         * @param  <type> $content_id
         * @return <type>
         */
        public function Delete_AllContentLevelMeta($level_id, $content_id)
        {
            global $wpdb;
            return $wpdb->delete(
                $this->table_names->contentlevel_options,
                ['contentlevel_id' => $this->Get_ContentLevelID($level_id, $content_id)]
            );
        }

        /**
         * Retrieve WishList Member user meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  string  $meta
         * @return mixed
         */
        public function Get_UserMeta($user_id, $meta)
        {
            global $wpdb;
            if (empty($user_id)) {
                return false;
            }

            $cache_key   = $user_id . '_' . $meta;
            $cache_group = $this->table_names->user_options;
            $table       = $this->table_names->user_options;

            $value = wlm_cache_get($cache_key, $cache_group);
            if (false === $value) {
                $row = $wpdb->get_row($wpdb->prepare('SELECT `option_value` FROM `' . esc_sql($table) . '` WHERE `user_id`=%d AND `option_name`=%s', $user_id, $meta));
                if (! is_object($row)) {
                    return false;
                }
                $value = $row->option_value;
                $value = wlm_maybe_unserialize($value);
                wlm_cache_set($cache_key, $value, $cache_group);
            }
            return $value;
        }

        /**
         * Retrieve User ID based on  Level Meta Information
         *
         * @global object $wpdb
         * @param  <type> $meta
         * @param  <type> $value
         * @return integer User ID
         */
        public function Get_UserID_From_UserMeta($meta, $value)
        {
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare('SELECT `user_id` as ID FROM `' . esc_sql($this->table_names->user_options) . '` WHERE `option_name`=%s AND `option_value`=%s LIMIT 1', $meta, $value));
            if (! is_object($row)) {
                return false;
            }
            return $row->ID;
        }

        /**
         * Add WishList Member user meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  string  $meta
         * @param  mixed   $value
         * @return mixed
         */
        public function Add_UserMeta($user_id, $meta, $value)
        {
            global $wpdb;
            // Do not add meta if no userid or the value is null.
            if (empty($user_id) || is_null($value)) {
                return false;
            }

            $x = $this->Get_UserMeta($user_id, $meta);
            if (false !== $x && ! is_null($x)) {
                return false;
            }
            $cache_key   = $user_id . '_' . $meta;
            $cache_group = $this->table_names->user_options;
            $table       = $this->table_names->user_options;

            if (is_bool($value)) {
                $value = (int) $value;
            }

            $data = [
                'user_id'      => $user_id,
                'option_name'  => $meta,
                'option_value' => wlm_maybe_serialize($value),
            ];

            $format = ['%d', '%s', '%s'];

            $x = $wpdb->insert($table, $data, $format);
            if (false !== $x) {
                wlm_cache_delete($cache_key, $cache_group);
                return true;
            }
            return false;
        }

        /**
         * Update WishList Member user meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  string  $meta
         * @param  mixed   $value
         * @return mixed
         */
        public function Update_UserMeta($user_id, $meta, $value)
        {
            global $wpdb;
            // Do not update meta if no userid or the value is null.
            if (empty($user_id) || is_null($value)) {
                return false;
            }

            $cache_key   = $user_id . '_' . $meta;
            $cache_group = $this->table_names->user_options;
            $table       = $this->table_names->user_options;

            if (is_bool($value)) {
                $value = (int) $value;
            }

            $data = $this->Get_UserMeta($user_id, $meta);

            if ($data === $value) {
                return true;
            }

            if (false === $data) {
                return $this->Add_UserMeta($user_id, $meta, $value);
            }

            $data = [
                'user_id'      => $user_id,
                'option_name'  => $meta,
                'option_value' => wlm_maybe_serialize($value),
            ];

            $format = ['%d', '%s', '%s'];

            $where = [
                'user_id'     => $user_id,
                'option_name' => $meta,
            ];

            $x = $wpdb->update($table, $data, $where, $format);
            if (false !== $x) {
                wlm_cache_delete($cache_key, $cache_group);
                return true;
            }
            return false;
        }

        /**
         * Delete WishList Member user meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  string  $meta
         */
        public function Delete_UserMeta($user_id, $meta)
        {
            global $wpdb;
            $cache_key   = $user_id . '_' . $meta;
            $cache_group = $this->table_names->user_options;
            $table       = $this->table_names->user_options;
            $wpdb->query($wpdb->prepare('DELETE FROM `' . esc_sql($table) . '` WHERE `user_id`=%d AND `option_name`=%s', $user_id, $meta));
            wlm_cache_delete($cache_key, $cache_group);
        }

        /**
         * Get Primary Key of UserLevel
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  integer $level_id
         * @return integer
         */
        public function Get_UserLevelIndex($user_id, $level_id)
        {
            global $wpdb;
            $cache_key   = $user_id . '_' . $level_id;
            $cache_group = $this->table_names->userlevels;

            $value = wlm_cache_get($cache_key, $cache_group);
            if (false === $value) {
                if ('' === $level_id) {
                    $row = $wpdb->get_row($wpdb->prepare('SELECT `ID` FROM `' . esc_sql($this->table_names->userlevels) . '` WHERE `user_id`=%d', $user_id));
                } else {
                    $row = $wpdb->get_row($wpdb->prepare('SELECT `ID` FROM `' . esc_sql($this->table_names->userlevels) . '` WHERE `user_id`=%d AND `level_id`=%s', $user_id, $level_id));
                }
                if (! is_object($row)) {
                    return false;
                }
                $value = $row->ID;
                wlm_cache_set($cache_key, $value, $cache_group);
            }
            return $value;
        }

        /**
         * Get UserLevel meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  integer $level_id
         * @param  string  $meta
         * @return mixed
         */
        public function Get_UserLevelMeta($user_id, $level_id, $meta)
        {
            global $wpdb;
            if (empty($user_id)) {
                return false;
            }

            $cache_key   = $user_id . '_' . $level_id . '_' . $meta;
            $cache_group = $this->table_names->userlevel_options;

            $value = wlm_cache_get($cache_key, $cache_group);
            if (false !== $value) {
                return $value;
            }

            $userlevel_id = $this->Get_UserLevelIndex($user_id, $level_id);
            if (false === $userlevel_id) {
                $value = null;
            } else {
                $row = $wpdb->get_row($wpdb->prepare('SELECT `option_value` FROM `' . esc_sql($this->table_names->userlevel_options) . '` WHERE `userlevel_id`=%d AND `option_name`=%s', $userlevel_id, $meta));
                // Rewritten so that we don't actually return "false" instead.
                // Cache the null value so that we don't have.
                // To hit the db next time.
                if (! is_object($row)) {
                    $value = null;
                } else {
                    $value = $row->option_value;
                }
            }
            $value = wlm_maybe_unserialize($value);
            wlm_cache_set($cache_key, $value, $cache_group);
            return $value;
        }

        /**
         * Get ALL UserLevel meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  integer $level_id
         * @return array
         */
        public function Get_All_UserLevelMetas($user_id, $level_id)
        {
            global $wpdb;
            if (empty($user_id)) {
                return false;
            }

            $cache_key   = $user_id . '_' . $level_id . '_all';
            $cache_group = $this->table_names->userlevel_options;

            $value = wlm_cache_get($cache_key, $cache_group);
            if (false !== $value) {
                return $value;
            }

            $userlevel_id = $this->Get_UserLevelIndex($user_id, $level_id);
            if (false === $userlevel_id) {
                $value = null;
            } else {
                $result = $wpdb->get_results($wpdb->prepare('SELECT `option_name`,`option_value` FROM `' . esc_sql($this->table_names->userlevel_options) . '` WHERE `userlevel_id`=%d', $userlevel_id));
                // Rewritten so that we don't actually return "false" instead.
                // Cache the null value so that we don't have.
                // To hit the db next time.
                if (! is_object($result) && ! is_array($result)) {
                    $value = null;
                } else {
                    $value = $result;
                }
            }
            $value = wlm_maybe_unserialize($value);
            wlm_cache_set($cache_key, $value, $cache_group);
            return $value;
        }

        /**
         * Add UserLevel meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  integer $level_id
         * @param  string  $meta
         * @param  mixed   $value
         * @return boolean
         */
        public function Add_UserLevelMeta($user_id, $level_id, $meta, $value)
        {
            global $wpdb;
            if (empty($user_id)) {
                return false;
            }

            $x = $this->Get_UserLevelMeta($user_id, $level_id, $meta);

            if (false !== $x && ! is_null($x)) {
                return false;
            }
            $cache_key   = $user_id . '_' . $level_id . '_' . $meta;
            $cache_group = $this->table_names->userlevel_options;

            $value        = wlm_maybe_serialize($value);
            $userlevel_id = $this->Get_UserLevelIndex($user_id, $level_id);

            if (! $userlevel_id) {
                return false;
            }

            $data   = [
                'userlevel_id' => $userlevel_id,
                'option_name'  => $meta,
                'option_value' => wlm_maybe_serialize($value),
            ];
            $format = ['%d', '%s', '%s'];
            $result = $wpdb->insert($this->table_names->userlevel_options, $data, $format);
            if (false !== $result) {
                wlm_cache_delete($cache_key, $cache_group);
                return true;
            }
            return false;
        }

        /**
         * Update UserLevel meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  integer $level_id
         * @param  string  $meta
         * @param  mixed   $value
         * @return boolean
         */
        public function Update_UserLevelMeta($user_id, $level_id, $meta, $value)
        {
            global $wpdb;
            if (empty($user_id)) {
                return false;
            }

            $cache_key   = $user_id . '_' . $level_id . '_' . $meta;
            $cache_group = $this->table_names->userlevel_options;

            $value = wlm_maybe_serialize($value);
            if (! $this->Add_UserLevelMeta($user_id, $level_id, $meta, $value)) {
                $userlevel_id = $this->Get_UserLevelIndex($user_id, $level_id);
                $data         = [
                    'userlevel_id' => $userlevel_id,
                    'option_name'  => $meta,
                    'option_value' => wlm_maybe_serialize($value),
                ];
                $format       = ['%d', '%s', '%s'];
                $where        = [
                    'userlevel_id' => $userlevel_id,
                    'option_name'  => $meta,
                ];
                $result       = $wpdb->update($this->table_names->userlevel_options, $data, $where, $format);
                if (false !== $result) {
                    wlm_cache_delete($cache_key, $cache_group);
                    return true;
                }
                return false;
            }
            return true;
        }

        /**
         * Delete UserLevel meta information
         *
         * @global object $wpdb
         * @param  integer $user_id
         * @param  integer $level_id
         * @param  string  $meta
         * @return boolean
         */
        public function Delete_UserLevelMeta($user_id, $level_id, $meta)
        {
            global $wpdb;
            $cache_key   = $user_id . '_' . $level_id . '_' . $meta;
            $cache_group = $this->table_names->userlevel_options;

            $userlevel_id = $this->Get_UserLevelIndex($user_id, $level_id);

            if (! is_array($userlevel_id)) {
                $userlevel_id = [$userlevel_id];
            }
            $result = $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM `' . esc_sql($this->table_names->userlevel_options) . '` WHERE `option_name`=%s AND `userlevel_id` IN (' . implode(', ', array_fill(0, count($userlevel_id), '%d')) . ')',
                    $meta,
                    ...array_values($userlevel_id)
                )
            );

            if (false !== $result) {
                wlm_cache_delete($cache_key, $cache_group);
                return true;
            }
            return false;
        }

        public function Delete_UserLevelMeta2($user_id, $level_id, $meta)
        {
            global $wpdb;

            $cache_key    = $user_id . '_' . $level_id . '_' . $meta;
            $cache_group  = $this->table_names->userlevel_options;
            $userlevel_id = $this->Get_UserLevelIndex($user_id, $level_id);
            if (is_array($userlevel_id)) {
                foreach ($userlevel_id as $k => $v) {
                    $userlevel_id[ $k ] = (int) $v;
                }
                $userlevel_id = "'" . implode("','", $userlevel_id) . "'";
            } else {
                $userlevel_id = (int) $userlevel_id;
            }
            $wpdb->delete(
                $this->table_names->userlevel_options,
                [
                    'userlevel_id' => $user_id,
                    'option_name'  => $meta,
                ]
            );

            if (false !== $result) {
                wlm_cache_delete($cache_key, $cache_group);
                return true;
            }
            return false;
        }


        public function Delete_User_Scheduled_LevelsMeta($user_id)
        {
            global $wpdb;
            // Remove meta scheduled cancellations and removals.
            $result = $wpdb->query($wpdb->prepare('DELETE FROM `' . esc_sql($this->table_names->userlevel_options) . '` WHERE `userlevel_id` IN (SELECT `ID` FROM `' . esc_sql($this->table_names->userlevels) . "` WHERE `user_id` = %d) AND (`option_name` LIKE %s OR `option_name`='scheduled_remove')", $user_id, 'wlm_schedule_level%'));

            // Delete scheduled levels.
            $result = $wpdb->query($wpdb->prepare('DELETE `l`.* FROM `' . esc_sql($this->table_names->userlevels) . '` `l` LEFT JOIN `' . esc_sql($this->table_names->userlevel_options) . "` `lo` ON `l`.`ID`=`lo`.`userlevel_id` WHERE `l`.`user_id`=%d AND `lo`.`option_name` IN ('scheduled_add','scheduled_move')", $user_id));
            return true;
        }

        /**
         * Retrieve Membership Levels based on User Level Meta Information
         *
         * @global object $wpdb
         * @param  integer $uid   User ID
         * @param  string  $meta
         * @param  string  $value (optional)
         * @return array Membership Levels
         */
        public function Get_Levels_From_UserLevelsMeta($uid, $meta, $value = null)
        {
            global $wpdb;
            if (is_null($value)) {
                $value = '%';
            }

            return $wpdb->get_col(
                $wpdb->prepare(
                    'SELECT DISTINCT `ul`.`level_id` FROM `' . esc_sql($this->table_names->userlevel_options) . '` AS `ulm` LEFT JOIN `' . esc_sql($this->table_names->userlevels) . '` AS `ul` ON `ulm`.`userlevel_id`=`ul`.`ID` WHERE `ul`.`user_id`=%d AND `ulm`.`option_name`=%s AND `ulm`.`option_value` LIKE %s',
                    $uid,
                    $meta,
                    $value
                )
            );
        }

        /**
         * Get User which level is not yet added and get schedule content
         *
         * @global object $wpdb
         * @param  type $uid
         * @param  type $meta
         * @return array
         */
        public function Get_Scheduled_UserLevelMeta($uid, $meta)
        {
            global $wpdb;
            $results = $wpdb->get_row(
                $wpdb->prepare(
                    'SELECT `option_name`, `option_value` FROM `' . esc_sql($this->table_names->userlevel_options) . '` WHERE `userlevel_id`=%d AND `option_name` LIKE %s',
                    $uid,
                    $meta . '%'
                ),
                ARRAY_N
            );
            return $results;
        }

        /**
         * Retrieve User ID based on User Level Meta Information
         *
         * @global object $wpdb
         * @param  <type> $meta
         * @param  <type> $value
         * @return integer User ID
         */
        public function Get_UserID_From_UserLevelsMeta($meta, $value)
        {
            global $wpdb;
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    'SELECT DISTINCT `ul`.`user_id` AS `ID` FROM `' . esc_sql($this->table_names->userlevel_options) . '` AS `ulm` JOIN `' . esc_sql($this->table_names->userlevels) . '` AS `ul` ON `ulm`.`userlevel_id`=`ul`.`ID` WHERE `ulm`.`option_name`=%s AND `ulm`.`option_value`=%s LIMIT 1',
                    $meta,
                    $value
                )
            );
            if (! is_object($row)) {
                return false;
            }
            return $row->ID;
        }

        /**
         * Cache User Level Information for the specified IDs
         *
         * @param array $ids User IDs
         */
        public function PreLoad_UserLevelsMeta($ids, $force_cache = null)
        {
            global $wpdb;
            if (! is_array($ids) || ! count($ids)) {
                return;
            }

            if (! is_array($force_cache)) {
                $force_cache = [
                    'cancelled'         => 0,
                    'unconfirmed'       => 0,
                    'forapproval'       => 0,
                    'registration_date' => '',
                    'transaction_id'    => '',
                ];
            }

            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT `ulm`.`option_name` AS `option_name`, `ulm`.`option_value` AS `option_value`, `ul`.`level_id`, `ul`.`user_id` AS `user_id` FROM `' . esc_sql($this->table_names->userlevel_options) . '` AS `ulm` JOIN `' . esc_sql($this->table_names->userlevels) . '` AS `ul` ON `ul`.`ID`=`ulm`.`userlevel_id` WHERE `ul`.`user_id` IN (' . implode(', ', array_fill(0, count($ids), '%d')) . ') ORDER BY `ul`.`user_id` ASC, `ul`.`level_id` ASC',
                    ...array_values($ids)
                )
            );
            if (! count($results)) {
                return;
            }

            $cache_group = $this->table_names->userlevel_options;
            $fcache      = $force_cache;
            $prev_key    = '';
            foreach ($results as $result) {
                $key = $result->user_id . '_' . $result->level_id;
                if ($prev_key != $key && ! empty($prev_key)) {
                    foreach ($fcache as $meta => $value) {
                        wlm_cache_set($key . '_' . $meta, $value, $cache_group);
                    }
                    $fcache = $force_cache;
                }
                $cache_key = $result->user_id . '_' . $result->level_id . '_' . $result->option_name;
                $value     = wlm_maybe_unserialize($result->option_value);
                wlm_cache_set($cache_key, $value, $cache_group);
                unset($fcache[ $result->option_name ]);
                $prev_key = $key;
            }
        }

        /**
         * Migrate Schedule Level Meta Information for all membersip levels to Database
         *
         * @global object $wpdb
         */
        public function MigrateScheduledLevelsMeta()
        {
            global $wpdb;
            $metas = $wpdb->get_results($wpdb->prepare('SELECT ID, option_name, option_value, userlevel_id FROM `' . esc_sql($this->table_names->userlevel_options) . '` WHERE `option_name` LIKE %s', 'wlm_schedule_level_%'));

            foreach ($metas as $record) {
                list($current_schedule_type, $level) = explode('-', $record->option_name);
                $schedule_type                       = $this->checkScheduleLevelType($current_schedule_type);

                if ($schedule_type) {
                    $values = [
                        'date'             => $record->option_value,
                        'is_current_level' => $this->checkScheduledLevelIsActive($schedule_type, $level),
                        'type'             => $schedule_type,
                    ];

                    if (in_array($schedule_type, ['add', 'move'])) {
                        $object         = new \stdClass();
                        $object->Levels = [$level];
                        $object->Metas  = [$level => [['scheduled_' . $schedule_type, $values]]];
                        $this->set_membership_levels($record->userlevel_id, $object, ['keep_existing_levels' => true]);
                        $record->userlevel_id = $this->Get_UserLevelIndex($record->userlevel_id, $level);
                    } else {
                        $data   = [
                            'userlevel_id' => $record->userlevel_id,
                            'option_name'  => 'scheduled_' . $schedule_type,
                            'option_value' => wlm_maybe_serialize($values),
                        ];
                        $result = $wpdb->insert($this->table_names->userlevel_options, $data);
                    }
                    $wpdb->delete($this->table_names->userlevel_options, ['ID' => $record->ID]);
                }
            }
        }

        // Helper function to check old record schedule type.
        private function checkScheduleLevelType($type)
        {
            if (preg_match('/_(add|remove|move)$/', $type, $matches)) {
                return $matches[1];
            } else {
                return false;
            }
        }

        private function checkScheduledLevelIsActive($type, $level_id)
        {

            global $wpdb;
            $user_info = $wpdb->get_row($wpdb->prepare('SELECT user_id FROM `' . esc_sql($this->table_names->userlevels) . '` WHERE `level_id` = %s', $level_id));
            if ('remove' === $type) {
                $cancelled   = $this->level_cancelled($level_id, $user_info->user_id);
                $pending     = $this->level_for_approval($level_id, $user_info->user_id);
                $unconfirmed = $this->level_unconfirmed($level_id, $user_info->user_id);
                $expired     = $this->level_expired($level_id, $user_info->user_id);
                if (! $cancelled && ! $pending && ! $unconfirmed && ! $expired) {
                    return true;
                }
            }
            return false;
        }
    }

}
