<?php

/**
 * Payperpost Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Payperpost Methods trait
 */
trait Payperpost_Methods
{
    use Payperpost_Methods_Deprecated;

    /**
     * Checks if a level is a User Level (starts with U-)
     *
     * @param  string  $level  Level ID.
     * @param  boolean $strict True for strict comparison, default false.
     * @return boolean
     */
    public function is_user_level($level, $strict = false)
    {
        $level = explode('-', $level, 2);
        if ('U' !== $level[0]) {
            return false;
        }
        if ($strict) {
            return get_userdata($level[1]) ? true : false;
        } else {
            return ( (int) $level[1] ) ? true : false;
        }
    }

    /**
     * Returns the number of posts for the specified $user_id
     *
     * @param  integer $user_id User ID.
     * @return integer
     */
    public function count_user_posts($user_id)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `%0s` WHERE `level_id`=%s', $this->table_names->contentlevels, 'U-' . $user_id));
    }

    /**
     * Returns the number of users for the specified $post_id
     *
     * @param  integer $post_id Post ID.
     * @param  string  $type    Post Type.
     * @return integer
     */
    public function count_post_users($post_id, $type = 'post')
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `%0s` WHERE `content_id`=%d AND `type`=%s AND `level_id` LIKE %s', $this->table_names->contentlevels, $post_id, $type, 'U-%%'));
    }

    /**
     * Add Transaction ID to user post
     *
     * @param  integer $user_id    User ID.
     * @param  integer $content_id Content ID.
     * @param  string  $txn_id     Transaction ID.
     * @param  boolean $update     True to update if entry meta already exists. Default TRUE.
     * @return boolean
     */
    public function add_user_post_transaction_id($user_id, $content_id, $txn_id, $update = true)
    {
        global $wpdb;
        if (empty($txn_id)) {
            $txn_id = sprintf('WL-%d-C-%d', $user_id, $content_id);
        }
        $level_id = 'U-' . $user_id;
        if ($this->Add_ContentLevelMeta($level_id, $content_id, 'transaction_id', $txn_id)) {
            return true;
        } else {
            if ($update) {
                if ($this->Update_ContentLevelMeta($level_id, $content_id, 'transaction_id', $txn_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Add Timestamp to user post
     *
     * @param  integer $user_id    User ID.
     * @param  integer $content_id Content ID.
     * @param  integer $timestamp  Timestamp.
     * @param  boolean $update     True to update if entry meta already exists. Default TRUE.
     * @return boolean
     */
    public function add_user_post_timestamp($user_id, $content_id, $timestamp, $update = true)
    {
        global $wpdb;
        if (empty($timestamp)) {
            $timestamp = time();
        }
        $level_id = 'U-' . $user_id;
        if ($this->Add_ContentLevelMeta($level_id, $content_id, 'registration_date', $timestamp)) {
            return true;
        } else {
            if ($update) {
                if ($this->Update_ContentLevelMeta($level_id, $content_id, 'registration_date', $timestamp)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Add post to users
     *
     * @param string  $content_type Content type.
     * @param integer $content_id   Content ID.
     * @param array   $user_levels  Array of user ids.
     */
    public function add_post_users($content_type, $content_id, $user_levels)
    {
        $user_levels = (array) $user_levels;
        foreach ($user_levels as $level) {
            // Just in case only the user ID was passed.
            if ('U-' !== substr($level, 0, 2)) {
                $level = 'U-' . $level;
            }

            $data = [
                'ContentType' => $content_type,
                'Level'       => $level,
                'ID'          => [$content_id => 0],
                'Checked'     => [$content_id => 1],
            ];
            $this->save_membership_content($data);

            $this->add_user_post_transaction_id(substr($level, 2), $content_id, '');
            $this->add_user_post_timestamp(substr($level, 2), $content_id, '');

            $this->record_user_ppp_history(substr($level, 2), 'added', $content_id, $content_type);

            do_action('wishlistmember_payperpost_added', substr($level, 2), $content_id, $content_type);

            if ('post' === $content_type) {
                $content_type = 'posts';
            }

            if ('page' === $content_type) {
                $content_type = 'pages';
            }

            // Run hook for adding content to user.
            if ('posts' === $content_type || 'pages' === $content_type) {
                do_action('wishlistmember_addpp_' . $content_type . '_user', $content_id, $level);
            }
        }
    }

    /**
     * Remove post from users
     *
     * @param string  $content_type Content type.
     * @param integer $content_id   Content ID.
     * @param array   $user_levels  Array of user ids.
     */
    public function remove_post_users($content_type, $content_id, $user_levels)
    {
        $user_levels = (array) $user_levels;
        foreach ($user_levels as $level) {
            if ('U-' !== substr($level, 0, 2)) {
                $level = 'U-' . $level;
            }

            // Run hook for removing content from user.
            if ('posts' === $content_type || 'pages' === $content_type) {
                do_action('wishlistmember_removepp_' . $content_type . '_user', $content_id, $level);
            }

            $data = [
                'ContentType' => $content_type,
                'Level'       => $level,
                'ID'          => [$content_id => 0],
            ];
            $this->save_membership_content($data);
            $this->Delete_AllContentLevelMeta($level, $content_id);

            $this->record_user_ppp_history(substr($level, 2), 'removed', $content_id, $content_type);

            do_action('wishlistmember_payperpost_removed', substr($level, 2), $content_id, $content_type);
        }
    }

    /**
     * Get post users.
     *
     * @param  string  $content_type Content type.
     * @param  integer $content_id   Content ID.
     * @return array                 Array of user-level IDs
     */
    public function get_post_users($content_type, $content_id)
    {
        $post_users = preg_grep('/^U-\d+$/', $this->get_content_levels($content_type, $content_id));
        return $post_users;
    }

    /**
     * Checks if a level is a valid Pay Per Post Level
     *
     * @param  string $level Level ID
     * @return mixed FALSE on Error, Post Object on Success
     */
    public function is_ppp_level($level)
    {
        static $levels;
        if (empty($levels)) {
            $levels = [];
        }
        if (isset($levels[ $level ])) {
            return $levels[ $level ];
        }
        $result = false;
        if (preg_match('/^payperpost-(\d+)$/', (string) $level, $match)) {
            if ($this->pay_per_post($match[1])) {
                $post   = get_post($match[1]);
                $result = $post;
            }
        }
        $levels[ $level ] = $result;
        return $result;
    }

    /**
     * Sets/Gets Post Pay Per Post status
     *
     * @param  integer $post_id Post ID.
     * @param  boolean $status  Optional Pay Per Post status.
     * @return boolean
     */
    public function pay_per_post($post_id, $status = null)
    {
        return $this->special_content_level($post_id, 'PayPerPost', $status);
    }

    /**
     * Assigns Post to User
     *
     * @param integer $user_id                   User ID.
     * @param array   $posts                     Array of Special Pay Per Post Levels. Each level is
     *                                           formatted as payperpost-xx where xx is the Post/Page ID.
     * @param boolean $keep_existing_payperposts TRUE (default) to keep pay per posts that are not included in the $posts array.
     */
    public function set_pay_per_post($user_id, $posts, $keep_existing_payperposts = true)
    {
        $posts = (array) $posts;

        if (! $keep_existing_payperposts) {
            // Remove existing pay per posts that are not included in $posts.
            foreach ($this->get_user_pay_per_post($user_id, false, null, true) as $post) {
                if (! in_array('payperpost-' . $post, $posts, true)) {
                    $this->remove_post_users(get_post_type($post), $post, 'U-' . $user_id);
                }
            }
        }

        foreach ($posts as $post) {
            $post = $this->is_ppp_level($post);
            if ($post) {
                $post_type = $post->post_type;
                $this->add_post_users($post_type, $post->ID, 'U-' . $user_id);
            }
        }
    }

    /**
     * Retrieves all Pay Per Post enabled posts
     *
     * @param  boolean|array $data               Data to retrieve
     *                                          - true to retrieve all
     *                                          - false (default) to retrieve content_id only
     *                                          - array of column names to retrieve specific column names.
     * @param  boolean       $group_by_post_type True to group results by post type. Processed only if $data is not false.
     * @param  string        $search             Search for post title. Default is '%'. Processed only if $data is not false.
     * @param  string        $search_limit       Search limit as per MySQL LIMIT syntax.
     * @param  integer       $total_rows         Total rows found.
     * @param  array         $exclude_ids        Array of IDs to exclude.
     * @return array
     */
    public function get_pay_per_posts($data = false, $group_by_post_type = true, $search = null, $search_limit = null, &$total_rows = null, $exclude_ids = [])
    {
        global $wpdb;
        if (is_null($search)) {
            $search = '%';
        }
        $search = esc_sql($search);

        $search_limit = wlm_trim($search_limit);
        if (! empty($search_limit) && ( preg_match('/\d+/', wlm_trim($search_limit)) || ! preg_match('/\d+\s*,\s*\d+/', $search_limit) )) {
            $search_limit = preg_replace('/[^0-9,]/', '', $search_limit);
        } else {
            $search_limit = PHP_INT_MAX;
        }

        if ($data) {
            if (true === $data) {
                $data = ['*'];
            } else {
                $data[] = 'ID';
                $data[] = 'post_type';
                $cols   = array_keys($wpdb->get_row("SELECT * FROM `{$wpdb->posts}` LIMIT 1", ARRAY_A));
                $data   = array_intersect(array_unique($data), $cols);
            }

            if (! is_array($exclude_ids) || empty($exclude_ids)) {
                $exclude_ids = [0];
            }

            $active_post_types = get_post_types();

            $posts = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT SQL_CALC_FOUND_ROWS ' . implode(', ', array_fill(0, count($data), '%0s')) . " FROM `{$wpdb->posts}` WHERE `post_title` LIKE %s AND `ID` NOT IN (" . implode(', ', array_fill(0, count($exclude_ids), '%d')) . ') AND `ID` IN (SELECT DISTINCT `content_id` FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `level_id`="PayPerPost") AND `post_type` IN (' . implode(', ', array_fill(0, count($active_post_types), '%s')) . ') LIMIT %0s',
                    ...array_values($data),
                    ...[$search],
                    ...array_values($exclude_ids),
                    ...array_values($active_post_types),
                    ...[$search_limit]
                )
            );

            $total_rows = $wpdb->get_var('SELECT FOUND_ROWS()');

            if ($group_by_post_type) {
                $xposts = [
                    'post' => [],
                    'page' => [],
                ];
                foreach ($posts as $post) {
                    $xposts[ $post->post_type ][] = $post;
                }
                return $xposts;
            } else {
                return $posts;
            }
        } else {
            $posts = $wpdb->get_col('SELECT DISTINCT `content_id` FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `level_id`="PayPerPost"');
        }
        return $posts;
    }

    /**
     * Injects Pay Per Post settings to wpm_levels
     *
     * @param array  $wpm_levels Passed by reference, wpm_levels.
     * @param string $level_id   Optional level ID. Default 'payperpost'.
     */
    public function inject_ppp_settings(&$wpm_levels, $level_id = 'payperpost')
    {
        // Make sure ppp_settings is an array or else array_merge will fail.
        $ppp_settings = (array) $this->get_option('payperpost');
        // Cast $wpm_levels[$level_id] as array to make sure it's an array.
        $wpm_levels[ $level_id ] = array_merge((array) ( $wpm_levels[ $level_id ] ?? [] ), $ppp_settings);
    }

    /**
     * Retrieve user posts based on transaction ID
     *
     * @param  string $transaction_id Transaction ID.
     * @return array
     */
    public function get_user_posts_from_txn_id($transaction_id)
    {
        global $wpdb;
        $cids = $wpdb->get_col($wpdb->prepare('SELECT contentlevel_id FROM `%0s` WHERE `option_name`="transaction_id" AND `option_value`=%s', $this->table_names->contentlevel_options, $transaction_id));
        if ($cids) {
            return $wpdb->get_results($wpdb->prepare('SELECT `content_id`,`level_id`,`type` FROM `%0s` WHERE `ID` IN (' . implode(', ', array_fill(0, count($cids), '%d')) . ')', $this->table_names->contentlevels, ...array_values($cids)));
        }
        return false;
    }

    /**
     * Sets and gets whether a payperpost is allowed for free registration
     *
     * @param  string         $post_id Post ID.
     * @param  boolean|string $status  Optional status.
     * @return boolean
     */
    public function free_pay_per_post($post_id, $status = null)
    {
        return $this->special_content_level($post_id, 'Free_PayPerPost', $status);
    }

    /**
     * Get User's Pay Per Posts
     *
     * @param  array|string $ids                  UserLevel ID or array of UserLevel IDs.
     * @param  boolean      $include_content_type Include post type in result.
     * @param  string       $content_type         Content type.
     * @param  boolean      $return_ids_only      true to return IDs only.
     * @param  string       $sort_by              Any of 'date-published', 'date-assigned' or 'post-title'.
     * @param  string       $sort_order           Any of 'asc', 'ascending', 'desc' or 'descending'.
     * @return array array of objects by default or one-dimensional array if $return_ids_only is true.
     */
    public function get_user_pay_per_post($ids, $include_content_type = false, $content_type = null, $return_ids_only = false, $sort_by = 'date-published', $sort_order = 'asc')
    {
        global $wpdb;

        if (! is_array($ids)) {
            if (is_numeric($ids)) {
                $ids = 'U-' . ( (int) $ids );
            }
            $ids = [$ids];
        }

        $for_approval = $this->get_user_for_approval_pay_per_post($ids);
        if (empty($for_approval)) {
            $for_approval = [0];
        }

        $sort_order = 'desc' === str_replace('ending', '', strtolower($sort_order)) ? 'desc' : 'asc';
        switch ($sort_by) {
            case 'post-title':
                $sort_by = 'wp.post_title';
                break;
            case 'date-published':
                $sort_by = 'wp.post_date';
                break;
            default:
                $sort_by = 'wlmo.option_value';
        }

        if (! $include_content_type) {
            $fields = '`content_id`';
        } else {
            $fields = '`content_id`, `type`';
        }

        $query_result = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT `content_id`, `type` FROM `' . esc_sql($this->table_names->contentlevels) . "` `wlm` left join `{$wpdb->posts}` `wp` on `wlm`.`content_id`=`wp`.`ID` left join `" . esc_sql($this->table_names->contentlevel_options) . '` `wlmo` on `wlm`.`ID` = `wlmo`.`contentlevel_id` and `wlmo`.`option_name`="registration_date" WHERE wp.post_status = "publish" AND wlm.type LIKE %s AND wlm.level_id IN(' . implode(', ', array_fill(0, count($ids), '%s')) . ') AND wlm.content_id NOT IN(' . implode(', ', array_fill(0, count($for_approval), '%s')) . ') ORDER BY %0s %0s',
                empty($content_type) ? '%' : $content_type,
                ...array_values($ids),
                ...array_values($for_approval),
                ...[$sort_by, $sort_order]
            )
        );

        /**
         * Filters the user's pay per post query result.
         *
         * @param array $query_result {
         *     Query result.
         *
         *     @type integer $content_id Content ID.
         *     @type string  $type Content type.
         * }
         */
        $query_result = apply_filters('wishlistmember_get_user_pay_per_post', $query_result, $ids);

        if (false === $query_result) {
            return [];
        }

        if ($return_ids_only) {
            if ((int) PHP_VERSION < 7) {
                $query_result = array_map(function ($x) {
                    return (array) $x;
                }, $query_result);
            }
            return array_column($query_result, 'content_id');
        }

        if (! $include_content_type) {
            return array_map(
                function ($x) {
                    unset($x->type);
                    return $x;
                },
                $query_result
            );
        }

        return $query_result;
    }

    /**
     * Get User's For Approval Pay Per Posts
     *
     * @param  array|string|integer $ids User Id or array of User IDs.
     * @return array
     */
    public function get_user_for_approval_pay_per_post($ids)
    {
        global $wpdb;

        if (! is_array($ids)) {
            if (is_numeric($ids)) {
                $ids = 'U-' . ( (int) $ids );
            }
            $ids = [$ids];
        }

        $cache_key   = md5(wp_json_encode($ids));
        $cache_group = 'get_user_for_approval_pay_per_post';

        $value = wlm_cache_get($cache_key, $cache_group);

        if (false !== $value) {
            return $value;
        }

        $for_approval = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT DISTINCT cl.content_id FROM `' . esc_sql($this->table_names->contentlevel_options) . '` AS clo LEFT JOIN `' . esc_sql($this->table_names->contentlevels) . '` AS `cl` ON cl.ID=clo.contentlevel_id WHERE clo.option_name="forapproval" AND cl.level_id IN ( ' . implode(', ', array_fill(0, count($ids), '%s')) . ')',
                ...array_values($ids)
            )
        );
        if (false === $for_approval) {
            return [];
        }

        wlm_cache_set($cache_key, $for_approval, $cache_group);

        return $for_approval;
    }

    /**
     * Checks if a Pay Per Post can be deleted.
     *
     * @param integer $post_id Post type.
     */
    public function check_post_to_delete($post_id)
    {

        $prevent_deletion = (int) $this->get_option('prevent_ppp_deletion');

        if ($this->pay_per_post($post_id) && 1 === $prevent_deletion) {
            $title       = get_the_title($post_id);
            $postlink    = admin_url('post.php') . "?post={$post_id}&action=edit";
            $error_title = __('WishList Member Error: Pay Per Post content cannot be deleted', 'wishlist-member');

            wp_die(
                sprintf(
                    // Translators: 1: post title.
                    esc_html__('%s is Pay Per Post content and cannot be deleted or trashed.', 'wishlist-member'),
                    esc_html($title)
                ),
                esc_html($error_title),
                [
                    'link_text' => esc_html__('Disable Pay Per Post', 'wishlist-member'),
                    'link_url'  => esc_url($postlink),
                ]
            );
        }
    }
    public function get_ppp_users_ajax()
    {
        @ini_set('zlib.output_compression', 1);
        $post_id   = (int) wlm_post_data()['post_id'];
        $post_type = get_post_type($post_id);
        if (empty($post_type)) {
            echo wp_json_encode(['success' => 0]);
        } else {
            $users = str_replace('U-', '', array_values($this->get_post_users($post_type, $post_id)));
            if (! empty($users)) {
                $filter = [
                    'fields'  => ['ID', 'user_login', 'user_email', 'display_name'],
                    'include' => $users,
                ];
                $users  = get_users($filter);
            }
            echo wp_json_encode(
                [
                    'success' => 1,
                    'data'    => $users,
                ]
            );
        }
        wp_die();
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('before_delete_post', [$wlm, 'check_post_to_delete']);
        add_action('wp_ajax_wlm_get_ppp_users', [$wlm, 'get_ppp_users_ajax']);
        add_action('wp_trash_post', [$wlm, 'check_post_to_delete']);
    }
);
