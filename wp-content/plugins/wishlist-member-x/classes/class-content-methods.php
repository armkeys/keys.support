<?php

/**
 * Content Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Content Methods trait
 */
trait Content_Methods
{
    use Content_Methods_Hooks;
    use Content_Methods_Deprecated;

    /**
     * Save content protection settings
     *
     * @param array   $data    {
     *        'Checked'     => array     Selected items. Content IDs as keys, booleanish as values
     *        'ID'          => array     {ContentID => 0},
     *        'ContentType' => string    Custom post type name or '~FOLDER', '~CATEGORY', 'page', 'post', '~COMMENT'
     *        'Level'       => string    Membership Level or 'Protection', 'PayPerPost', 'Free_PayPerPost'
     *    } optional associative array of data to process. Uses $_POST if $data is empty.
     * @param boolean $nohooks TRUE to disable custom hooks. Default false.
     */
    public function save_membership_content($data = '', $nohooks = false)
    {
        global $wpdb;
        if ($data) {
            $msg = false;
        } else {
            $msg  = true;
            $data = wlm_post_data(true);
        }

        $data_checked      = isset($data['Checked']) ? $data['Checked'] : '';
        $data_id           = isset($data['ID']) ? $data['ID'] : '';
        $data_content_type = isset($data['ContentType']) ? $data['ContentType'] : '';
        $data_level        = isset($data['Level']) ? $data['Level'] : '';

        $data_checked = (array) $data_checked + (array) $data_id;
        switch ($data_content_type) {
            case 'folders':
                $content_type = '~FOLDER';
                break;
            case 'categories':
                $content_type = '~CATEGORY';
                break;
            case 'pages':
                $content_type = 'page';
                break;
            case 'posts':
                $content_type = 'post';
                break;
            case 'comments':
                $content_type = '~COMMENT';
                break;
            default:
                $content_type = $data_content_type;
        }

        $content_ids = (array) $data_checked + (array) $data_id;
        $removed     = [];
        $added       = [];
        foreach ($content_ids as $content_id => $status) {
            if ($status) {
                $result = $wpdb->query($wpdb->prepare('INSERT IGNORE INTO `' . esc_sql($this->table_names->contentlevels) . '` (`content_id`, `level_id`, `type`) VALUES (%d,%s,%s)', $content_id, $data_level, $content_type));
                if ($result) {
                    $added[] = $content_id;
                }
            } else {
                $result = $wpdb->query($wpdb->prepare('DELETE FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `content_id`=%d AND `level_id`=%s AND `type`=%s', $content_id, $data_level, $content_type));
                if ($result) {
                    $removed[] = $content_id;
                }
            }
        }

        // Trigger Content Action Hooks Routine.
        if (! $nohooks) {
            if (count($removed)) {
                foreach ((array) $removed as $id) {
                    $this->trigger_content_action_hooks($data_content_type, $id, [$data_level], []);
                }
            }
            if (count($added)) {
                foreach ((array) $added as $id) {
                    $this->trigger_content_action_hooks($data_content_type, $id, [], [$data_level]);
                }
            }
        }
        // End of Trigger Action Hooks Routine.
        if ($msg) {
            if ('Protection' === $data_level) {
                $this->msg = sprintf('<strong>%s</strong>', __('Content Protection updated.', 'wishlist-member'));
            } elseif ('PayPerPost' === $data_level) {
                $this->msg = sprintf('<strong>%s</strong>', __('Pay Per Post access updated.', 'wishlist-member'));
            } else {
                $this->msg = sprintf('<strong>%s</strong>', __('Membership Level access updated.', 'wishlist-member'));
            }
        }

        if (empty($this->save_membership_content_do_not_pass_protection)) {
            $changed = array_unique((array) $added + (array) $removed);
            foreach ($changed as $id) {
                $this->pass_protection($id, 'categories' === $data_content_type);
            }
        }
        $this->save_membership_content_do_not_pass_protection = false;
    }

    /**
     * Save Pay per post protection settings
     *
     * @uses save_membership_content()
     */
    public function save_membership_content_pay_per_post()
    {
        $post = wlm_post_data(true);

        // Save content protection settings.
        $this->save_membership_content($post);

        // Save Free_PayPerPost settings.
        $post['Checked'] = array_intersect($post['enable_free_payperpost'], [1]);
        $post['Level']   = 'Free_PayPerPost';
        $this->save_membership_content($post);
    }

    /**
     * Get Content for Membership Level
     *
     * @param  string $content_type     Content type to retrieve. Possible values are 'folders',
     *                                 'categories', 'pages', 'posts', 'comments', 'attachments',
     *                                 and registered custom post type slugs.
     * @param  string $membership_level Membership level ID.
     * @return array                   Array of content IDs that the membership level can access.
     */
    public function get_membership_content($content_type, $membership_level = '')
    {
        global $wpdb;

        $cache_key   = $content_type . '_' . md5(wlm_maybe_serialize($membership_level));
        $cache_group = 'GetMembershipContent';

        $value = wlm_cache_get($cache_key, $cache_group);
        if (false !== $value) {
            return $value;
        }

        $actual_content_type = '';
        $post_type           = '';

        $all_categories = $wpdb->get_col("SELECT `{$wpdb->terms}`.term_id as ID from `{$wpdb->terms}` left join `{$wpdb->term_taxonomy}` ON `{$wpdb->terms}`.term_id = `{$wpdb->term_taxonomy}`.term_id WHERE `{$wpdb->term_taxonomy}`.taxonomy = 'category'");

        switch ($content_type) {
            case 'folders':
                $actual_content_type = '~FOLDER';
                break;
            case 'categories':
                $actual_content_type = '~CATEGORY';
                $taxonomyids         = ( isset($this->taxonomy_ids) ) ? (array) $this->taxonomy_ids : [];
                if ($taxonomyids) {
                    $all_categories = array_unique(array_merge((array) $all_categories, (array) $this->taxonomy_ids));
                }
                break;
            case 'pages':
                $actual_content_type = 'page';
                $post_type           = 'page';
                break;
            case 'posts':
                $actual_content_type = 'post';
                $post_type           = 'post';
                break;
            case 'comments':
                $actual_content_type = '~COMMENT';
                $post_type           = 'post';
                break;
            case 'attachments':
                $actual_content_type = 'attachment';
                $post_type           = 'attachment';
                break;
            default: // custom post types.
                $actual_content_type = $content_type;
                $post_type           = $actual_content_type;
        }

        $content    = [];
        $wpm_levels = $this->get_option('wpm_levels');
        if ($membership_level && ! is_array($membership_level)) {
            if (wlm_arrval(wlm_arrval($wpm_levels, $membership_level), 'all' . $content_type)) {
                if ($post_type) {
                    $content = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT DISTINCT `ID` FROM `'
                            . $wpdb->posts
                            . "` WHERE `post_status` IN ('publish','pending','draft','private','future') AND `post_type`=%s",
                            $post_type
                        )
                    );
                }
                if ('categories' === $content_type) {
                    $content = $all_categories;
                }
            } else {
                if (false !== strrpos($membership_level, 'U-')) { // For pay per post.
                    $content = $this->get_user_pay_per_post($membership_level, false, $actual_content_type, true);
                } else {
                    $content = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT `content_id` FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE type=%s AND level_id=%s GROUP BY content_id',
                            $actual_content_type,
                            $membership_level
                        )
                    );
                }
            }
        } elseif (is_array($membership_level)) {
            $x_levels     = [];
            $x_ppp_levels = [];
            foreach ((array) $membership_level as $lvlid) {
                if (isset($wpm_levels[ $lvlid ][ 'all' . $content_type ])) {
                    if ($post_type) {
                        $content = $wpdb->get_col(
                            $wpdb->prepare(
                                'SELECT DISTINCT `ID` FROM `'
                                . $wpdb->posts
                                . "` WHERE `post_status` IN ('publish','pending','draft','private','future') AND `post_type`=%s",
                                $post_type
                            )
                        );
                    }
                    if ('categories' === $content_type) {
                        $content = $all_categories;
                    }
                    $x_levels = [];
                    break;
                } else {
                    if ($lvlid) {
                        if (false !== strrpos($lvlid, 'U-')) {
                            $x_ppp_levels[] = esc_sql($lvlid);
                        } else {
                            $x_levels[] = $lvlid;
                        }
                    }
                }
            }
            // If content is empty.
            if (empty($content)) {
                $level_content = [];
                $ppp_content   = [];
                if (count($x_levels)) { // For levels.
                    $level_content = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT DISTINCT `content_id` FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `type`=%s AND `level_id` IN (' . implode(', ', array_fill(0, count($x_levels), '%s')) . ')',
                            $actual_content_type,
                            ...array_values($x_levels)
                        )
                    );
                }
                if (count($x_ppp_levels)) { // For pay per posts.
                    $ppp_content = $this->get_user_pay_per_post($x_ppp_levels, false, $actual_content_type, true);
                }
                $content = array_merge($ppp_content, $level_content);
            }
        } else {
            foreach (array_keys($wpm_levels) as $level_id) {
                $content[ $level_id ] = $this->get_membership_content($content_type, $level_id);
            }
        }

        $value = wlm_maybe_unserialize($content);
        wlm_cache_set($cache_key, $value, $cache_group);

        return $content;
    }

    /**
     * Clone Content Membership Level
     *
     * @param string $from Source Level.
     * @param string $to   Destination Level.
     */
    public function clone_membership_content($from, $to)
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare('DELETE FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `level_id`=%s', $to));
        $wpdb->query($wpdb->prepare('INSERT INTO `' . esc_sql($this->table_names->contentlevels) . '` (`content_id`,`level_id`,`type`) SELECT `content_id`,%s,`type` FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `level_id`=%s', $to, $from));
    }

    /**
     * Set Membership Content Levels
     *
     * @param string  $content_type Content Type.
     * @param integer $id           Content ID.
     * @param array   $levels       Array of Level IDs.
     */
    public function set_content_levels($content_type, $id, $levels)
    {
        $wpm_levels = $this->get_option('wpm_levels');
        $this->validate_levels($levels);

        $current_levels = $this->get_content_levels($content_type, $id);
        $this->array_diff($levels, $current_levels, $removed_levels, $new_levels);

        $post = wlm_post_data(true);

        $post_id = isset($post['post_ID']) ? $post['post_ID'] : '';
        $post    = [
            'ContentType' => $content_type,
            'ID'          => [
                $post_id => 0,
                $id      => 0,
            ],
        ];

        $level_keys = array_keys((array) $wpm_levels);

        while ($key = array_shift($level_keys)) {
            $post['Level'] = $key;
            if (in_array($key, $levels)) {
                $post['Checked'] = [
                    $post_id => 1,
                    $id      => 1,
                ];
            } else {
                unset($post['Checked']);
            }

            $this->save_membership_content_do_not_pass_protection = count($level_keys) > 1;

            $this->save_membership_content($post, true);
        }

        // Trigger WordPress action hooks.
        $this->trigger_content_action_hooks($content_type, $id, $removed_levels, $new_levels);
    }

    /**
     * Set content levels for child content that are set to inherit protection.
     *
     * @param string  $content_type Content Type.
     * @param integer $id           Content ID.
     * @param array   $levels       Array of membership levels.
     * @param boolean $ascend       TRUE to start from topmost parent.
     */
    public function set_content_levels_deep($content_type, $id, $levels, $ascend = true)
    {
        /**
         * How this is achieved
         * 1. Find the topmost level
         * 2. Find all the children
         * 3. Filter all childrens that "Inherit"s
         * 4. Update those children
         */
        switch ($content_type) {
            case 'pages':
            case 'page':
                // Find topmost level.
                $page = get_page($id);
                if ($ascend) {
                    $root     = wlm_get_page_root($page->ID);
                    $children = wlm_get_page_children($root);

                    if ($root == $id) {
                        if ($this->protect($id)) {
                            $levels[] = 'Protection';
                        }
                    } else {
                        $levels = $this->get_content_levels($content_type, $root);
                    }

                    foreach ($children as $c) {
                        $this->set_content_levels_deep($content_type, $c, $levels, false);
                    }
                } else {
                    if (! $this->special_content_level($id, 'Inherit')) {
                        return;
                    }
                    $this->set_content_levels($content_type, $page->ID, $levels);
                    if (in_array('Protection', $levels)) {
                        $this->protect($id, 'Y');
                    } else {
                        $this->protect($id, 'N');
                    }
                }
                break;
            case 'category':
                // Update the relevant category children.
                $children_cat = wlm_get_category_children($id, 'category');
                foreach ($children_cat as $c) {
                    if ($this->special_content_level($c, 'Inherit', null, '~CATEGORY')) {
                        $this->set_content_levels('categories', $c, $levels);
                    }
                }

                // Go through all posts and collect categories.
                $children_post = wlm_get_category_children($id, 'post');

                // Collect all relevant categories.
                $cats = [];
                foreach ($children_post as $child) {
                    $cats = array_merge($cats, get_the_category($child->ID));
                }

                // Filter & reformat for easy manipulation later.
                $levels = [];
                foreach ($cats as $c) {
                    $levels[ $c->term_id ] = $this->get_content_levels('categories', $c->term_id);
                }

                // Now update the relevant posts. The final levels is set to the combined levels of all parent categories.
                foreach ($children_post as $child) {
                    if (! $this->special_content_level($child->ID, 'Inherit')) {
                        continue;
                    }
                    $cats              = get_the_category($child->ID);
                    $combined_settings = [];
                    foreach ($cats as $c) {
                        $combined_settings = array_merge($combined_settings, $levels[ $c->term_id ]);
                    }

                    wlm_post_data()['post_ID'] = null; // Apparently this variable needs to be nullified.
                    $this->set_content_levels('posts', $child->ID, $combined_settings);

                    if (in_array('Protection', $combined_settings)) {
                        $this->protect($child->ID, 'Y');
                    } else {
                        $this->protect($child->ID, 'N');
                    }
                }
                break;
            default:
                $roots   = wlm_get_post_roots($id);
                $levels  = [];
                $protect = false;
                foreach ($roots as $r) {
                    $root_levels = $this->get_content_levels('categories', $r);
                    $this->set_content_levels_deep('category', $r, $root_levels);
                }
                break;
        }
    }

    /**
     * Get Content Levels
     *
     * @param  string       $content_type  Content type.
     * @param  integer      $id            Content ID.
     * @param  boolean      $names         TRUE to return level names instead of IDs.
     * @param  boolean      $implode_names Implode level names with ', '.
     * @param  string|array $immutable     Array of immutable levels or 'nothing'. This variable is passed by reference.
     * @return array                        Array of level IDs or Array of level names if $names == true
     */
    public function get_content_levels($content_type, $id, $names = null, $implode_names = null, &$immutable = 'nothing')
    {
        if (is_null($names)) {
            $names = false;
        }
        if (is_null($implode_names)) {
            $implode_names = false;
        }
        global $wpdb;
        switch ($content_type) {
            case 'folders':
                $actual_content_type = '~FOLDER';
                break;
            case 'categories':
                $actual_content_type = '~CATEGORY';
                break;
            case 'pages':
                $actual_content_type = 'page';
                break;
            case 'posts':
                $actual_content_type = get_post_type($id);
                break;
            case 'comments':
                $actual_content_type = '~COMMENT';
                break;
            default:
                $actual_content_type = $content_type;
        }

        $levels = $wpdb->get_col(
            $wpdb->prepare(
                'SELECT DISTINCT `level_id` FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `level_id` NOT LIKE %s AND `type`=%s AND `content_id`=%d',
                $names ? 'U-%' : -1,
                $actual_content_type,
                $id
            )
        );

        $wpm_levels = $this->get_option('wpm_levels');
        if (in_array($actual_content_type, ['page', 'post', '~CATEGORY', '~COMMENT'], true) && 'nothing' !== $immutable) {
            $immutable = [];
            foreach ($wpm_levels as $key => $val) {
                switch ($actual_content_type) {
                    case '~CATEGORY':
                        $allkey = 'allcategories';
                        break;
                    case '~COMMENT':
                        $allkey = 'allcomments';
                        break;
                    case 'page':
                        $allkey = 'allpages';
                        break;
                    case 'post':
                        $allkey = 'allposts';
                        break;
                }
                if (wlm_arrval($val, $allkey)) {
                    $immutable[] = $key;
                    array_unshift($levels, $key);
                }
            }
            $levels = array_unique($levels);
        } else {
            $immutable = [];
        }

        if ($names) {
            $names = [];
            foreach ((array) $levels as $level) {
                if (isset($wpm_levels[ $level ])) {
                    $names[ $level ] = $wpm_levels[ $level ]['name'];
                    if (is_array($immutable) && in_array($level, $immutable)) {
                        $names[ $level ] = sprintf('<wlmimmutable>%s</wlmimmutable>', $names[ $level ]);
                    }
                }
            }

            $levels = $implode_names ? implode(', ', $names) : $names;
        }
        return $levels;
    }

    /**
     * Clone Protection
     *
     * @param integer $orig       Source Content ID.
     * @param integer $clone      Destination Content ID.
     * @param string  $orig_type  Source Content Type.
     * @param string  $clone_type (optional) Destination Content Type.
     */
    public function clone_protection($orig, $clone, $orig_type = 'posts', $clone_type = null)
    {
        // First clone the levels.
        if (is_null($clone_type)) {
            $clone_type = $orig_type;
        }
        $this->set_content_levels($clone_type, $clone, $this->get_content_levels($orig_type, $orig));
        $protect = $this->protect($orig) ? 'Y' : 'N';
        $this->protect($clone, $protect);
    }

    /**
     * Synchronize Content Levels
     */
    public function sync_content()
    {
        global $wpdb;

        // Fix all invalid post types.
        $wpdb->query('UPDATE IGNORE `' . esc_sql($this->table_names->contentlevels) . "`,`{$wpdb->posts}` SET `" . esc_sql($this->table_names->contentlevels) . "`.`type`=`{$wpdb->posts}`.`post_type` WHERE `" . esc_sql($this->table_names->contentlevels) . "`.`content_id`=`{$wpdb->posts}`.`ID` AND `" . esc_sql($this->table_names->contentlevels) . "`.`type` NOT LIKE '~%%'");

        // Remove all entries in wlm_contentlevels where type does not begin with ~ and no matching posts (any post type) in wp_posts.
        $wpdb->query('DELETE `' . esc_sql($this->table_names->contentlevels) . '` FROM `' . esc_sql($this->table_names->contentlevels) . "` LEFT JOIN `{$wpdb->posts}` ON `" . esc_sql($this->table_names->contentlevels) . "`.`content_id`=`{$wpdb->posts}`.`ID` AND `" . esc_sql($this->table_names->contentlevels) . "`.`type`=`{$wpdb->posts}`.`post_type` WHERE `" . esc_sql($this->table_names->contentlevels) . "`.`type` NOT LIKE '~%%' AND `{$wpdb->posts}`.`ID` IS NULL");

        // Remove all data from wlm_contentlevels if the membership level deleted.
        $wpm_levels = $this->get_option('wpm_levels');
        if (count($wpm_levels) > 0) {
            $level_ids = array_keys($wpm_levels);
            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `level_id` NOT LIKE %s AND `level_id` NOT IN (' . implode(', ', array_fill(0, count($level_ids), '%s')) . ") AND `level_id` NOT IN ('Protection','Free_PayPerPost','PayPerPost', 'Inherit', 'ForceDownload')",
                    'U-%',
                    ...array_values($level_ids)
                )
            );
        }
    }

    /**
     * Get parents of content
     *
     * @param  integer $id        Content ID.
     * @param  string  $post_type Content Type.
     * @return array  {
     *     Associative array.
     * @type   string $type     Parent content ype
     * @type   array  $contents IDs of all parents
     * }
     */
    public function get_content_parents($id, $post_type = null)
    {
        if (empty($post_type)) {
            $post_type = get_post_type($id);
        }

        $parent_ids  = [];
        $parent_type = null;

        switch ($post_type) {
            case 'post':
                $parent_type = 'categories';
                $parent_ids  = wp_get_post_categories($id);
                break;
            case 'page':
                $parent_type = 'posts';
                if ($post->post_parent) {
                    $ancestors    = get_post_ancestors($id);
                    $root         = count($ancestors) - 1;
                    $parent_ids[] = $ancestors[ $root ];
                }
                break;
            case 'category':
                break;
            default:
                $parent_type = 'categories';
                $parent_ids  = wp_get_post_categories($id);
                return $parent_ids;
        }
        return [
            'type'     => $parent_type,
            'contents' => $parent_ids,
        ];
    }

    /**
     * Get/Set Post/Page Protection
     *
     * Internally handles inheritance when protecting. So make sure
     * you set the content's inheritance status before protecting
     * to be able to cascade protection the the content.
     *
     * @uses ::special_content_level()
     *
     * @param  integer        $id     Post/Page ID.
     * @param  string|boolean $status (optional) 'Y', true, 'N', false.
     * @return boolean
     */
    public function protect($id, $status = null)
    {
        $current_status = $this->special_content_level($id, 'Protection');
        if (! empty($status)) {
            $new_status = $this->special_content_level($id, 'Protection', $status);
            if ($current_status !== $new_status) {
                $current_status = $new_status;
            }
        }
        return $current_status;
    }

    /**
     * Sets special content level
     *
     * @param  integer        $id     Content ID.
     * @param  string         $level  Special Level i.e. 'Protection', 'Inherit', etc.
     * @param  string|boolean $status Optional. 'Y', true, 'N', false.
     * @param  string         $type   Content Type.
     * @return boolean                Content's level status.
     */
    public function special_content_level($id, $level, $status = null, $type = null)
    {
        global $wpdb;
        $id += 0;

        // Always return true if $id is falsish.
        if (! $id) {
            return true;
        }

        if (empty($type)) {
            $type = get_post_type($id);
            if (! $this->post_type_enabled($type)) {
                return false;
            }
        }

        $cache_key   = $id . '_' . $level . '_' . $status . '_' . $type;
        $cache_group = 'SpecialContentLevel';

        $value = wlm_cache_get($cache_key, $cache_group);

        if (false !== $value) {
            return $value;
        }

        if (! is_null($status)) {
            if (is_bool($status)) {
                $status = $status ? 'Y' : 'N';
            }
            switch (strtoupper($status)) {
                case 'Y':
                    $wpdb->query($wpdb->prepare('INSERT IGNORE INTO `' . esc_sql($this->table_names->contentlevels) . '` (`content_id`,`level_id`,`type`) VALUES (%d,%s,%s)', $id, $level, $type));
                    break;
                case 'N':
                    $wpdb->query($wpdb->prepare('DELETE FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `content_id`=%d AND `level_id`=%s AND `type`=%s', $id, $level, $type));
                    break;
            }
            if ('Protection' === $level && ( '~' !== substr($type, 0, 1) || '~CATEGORY' === $type )) {
                $this->pass_protection($id, '~CATEGORY' === $type);
            }
        }

        $value = (bool) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `' . esc_sql($this->table_names->contentlevels) . '` WHERE `content_id`=%d AND `level_id`=%s AND `type`=%s', $id, $level, $type));
        wlm_cache_set($cache_key, $value, $cache_group);

        return $value;
    }

    /**
     * Inherit protection of parents (both parent posts and terms/categories)
     *
     * @param integer        $content_id  Content ID.
     * @param boolean        $is_taxonomy True if Content is Taxonomy. Default false.
     * @param boolean        $is_comment  True if Content is Comment. Default false.
     * @param string|boolean $new_protect New protection status. Passed by reference.
     * @param array          $new_levels  New levels. Passed by reference.
     */
    public function inherit_protection($content_id, $is_taxonomy = false, $is_comment = false, &$new_protect = null, &$new_levels = null)
    {
        global $wpdb;

        if ($is_taxonomy) {
            // Taxonomy.
            $levels   = [];
            $ancestor = $wpdb->get_var($wpdb->prepare("SELECT `parent` FROM `{$wpdb->term_taxonomy}` WHERE `term_id`=%d", $content_id));
            if (! empty($ancestor)) {
                $levels = $this->get_content_levels('categories', $ancestor);
            }

            if (empty($levels) && empty($ancestor)) {
                $this->cat_protected($content_id, 'N');
                $this->set_content_levels('categories', $content_id, []);
                return;
            }

            $protect     = in_array('Protection', $levels, true) ? 'Y' : 'N';
            $new_protect = $this->cat_protected($content_id, $protect);
            $new_levels  = $levels;
            $this->set_content_levels('categories', $content_id, $levels);
            $this->special_content_level($content_id, 'Inherit', 'Y', '~CATEGORY');
        } else {
            $post_type = get_post_type($content_id);
            if (empty($post_type)) {
                return;
            }

            if ($is_comment) {
                $post_type = '~COMMENT';
            }

            $this->special_content_level($content_id, 'Inherit', 'Y', $post_type);

            if ($is_comment) {
                $new_protect = $this->protect($content_id);
                $new_levels  = $this->get_content_levels(get_post_type($content_id), $content_id);
                $this->special_content_level($content_id, 'Protection', $new_protect, $post_type);
                $this->set_content_levels($post_type, $content_id, $new_levels, $content_id);
            } else {
                $levels = [];

                $taxonomy_names = get_taxonomies(['_builtin' => false], 'names');
                array_unshift($taxonomy_names, 'category');
                $taxonomies = wp_get_object_terms($content_id, $taxonomy_names, ['fields' => 'ids']);

                if (! is_wp_error($taxonomies) && ! empty($taxonomies)) {
                    foreach ($taxonomies as $taxonomy) {
                        $levels = array_merge($levels, $this->get_content_levels('categories', $taxonomy, null, null, $immutable));
                    }
                }

                $ancestor = get_post_ancestors($content_id);
                if (! empty($ancestor)) {
                    $levels = array_merge($levels, $this->get_content_levels(get_post_type($ancestor[0]), $ancestor[0], null, null, $immutable));
                }

                if (empty($levels)) {
                    $new_protect = $this->protect($content_id, 'N');
                    $new_levels  = [];
                    $this->set_content_levels($post_type, $content_id, $new_levels);
                    return;
                }

                $protect     = in_array('Protection', $levels, true) ? 'Y' : 'N';
                $new_protect = $this->protect($content_id, $protect);
                $new_levels  = $levels;
                $this->set_content_levels($post_type, $content_id, $levels);
            }
        }
    }

    /**
     * Pass protection settings of content to its children marked with "Inherit"
     *
     * @param integer $content_id  The post ID or term ID of the parent content.
     * @param boolean $is_taxonomy True if $content_id is a taxonomy.
     */
    public function pass_protection($content_id, $is_taxonomy = null)
    {
        static $call_record = [];
        global $wpdb;

        if (empty($content_id)) {
            return;
        }

        wlm_set_time_limit(60 * 60 * 24);

        $args = (string) $content_id . '_' . (int) (bool) $is_taxonomy;
        if (isset($call_record[ $args ])) {
            return;
        }
        $call_record[ $args ] = 1;

        if ($is_taxonomy) {
            // Taxonomy.
            $protect = $this->cat_protected($content_id);

            $taxonomy = $wpdb->get_var($wpdb->prepare("SELECT `taxonomy` FROM `{$wpdb->term_taxonomy}` WHERE `term_id`=%d", $content_id));

            if (empty($taxonomy)) {
                unset($call_record[ $args ]);
                return;
            }

            // Sub-taxonomies.
            $children = get_term_children($content_id, $taxonomy);
            foreach ($children as $child) {
                if ($this->special_content_level($child, 'Inherit', null, '~CATEGORY')) {
                    $this->inherit_protection($child, true);
                }
            }

            // Posts under taxonomy.
            $children = get_objects_in_term($content_id, $taxonomy);
            foreach ($children as $child) {
                if ($this->special_content_level($child, 'Inherit')) {
                    $this->inherit_protection($child);
                }
            }
            unset($call_record[ $args ]);
        } else {
            $protect  = $this->protect($content_id);
            $children = get_children(
                [
                    'post_parent' => $content_id,
                    'post_type'   => get_post_types(),
                ]
            );

            foreach (array_keys($children) as $child) {
                if ($this->special_content_level($child, 'Inherit')) {
                    $this->inherit_protection($child);
                }
            }

            if ($this->special_content_level($content_id, 'Inherit', null, '~COMMENT')) {
                $this->inherit_protection($content_id, false, true);
            }
            unset($call_record[ $args ]);
        }
    }

    /**
     * Triggers the correct hook when a content changes levels
     *
     * @param string  $content_type   The content type. Can be categories, posts, pages, or comments.
     * @param integer $content_id     Unique ID of the content.
     * @param array   $removed_levels Array of levels that were removed.
     * @param array   $new_levels     Array of levels that were added.
     */
    public function trigger_content_action_hooks($content_type, $content_id, $removed_levels, $new_levels)
    {
        // Trigger remove_***content***_levels action if a content is removed from at least one level.
        if (count($removed_levels)) {
            do_action('wishlistmember_remove_' . $content_type . '_levels', $content_id, $removed_levels);
        }
        // Trigger add_***content***_levels action if content is added to at least one level.
        if (count($new_levels)) {
            do_action('wishlistmember_add_' . $content_type . '_levels', $content_id, $new_levels);
        }
    }

    /**
     * Check for Category Protection Status
     *
     * @param  integer $id     Category ID.
     * @param  string  $status (optional) Y/N.
     * @return boolean
     */
    public function cat_protected($id, $status = null)
    {
        $id            += 0;
        $current_status = $this->special_content_level($id, 'Protection', null, '~CATEGORY');
        if (! is_null($status)) {
            $new_status = $this->special_content_level($id, 'Protection', $status, '~CATEGORY');
            if ($current_status !== $new_status) {
                $current_status = $new_status;
            }
        }
        return $current_status;
    }

    /**
     * Get IDs of protected content
     *
     * @param  array $filter_types Array of post types to limit to. Default, all post types.
     * @return array
     */
    public function protected_ids($filter_types = [])
    {
        global $wpdb;
        static $post_types; // Static cache of non-built-in post types.
        static $enabled_types; // Static cache of post types enabled for protection.
        static $protected = []; // Static cache of protected IDs.

        $post_types    = is_null($post_types) ? get_post_types(['_builtin' => false]) : $post_types;
        $enabled_types = is_null($enabled_types) ? (array) $this->get_option('protected_custom_post_types') : $enabled_types;
        $remove_types  = array_diff($post_types, $enabled_types);
        $filter_types  = array_unique((array) $filter_types);

        if ($remove_types && $filter_types) {
            $query = $wpdb->prepare(
                'SELECT `content_id` FROM '
                . esc_sql($this->table_names->contentlevels)
                . " WHERE `level_id`='Protection' AND `type` NOT LIKE %s"
                // $remove_types.
                . ' AND `content_id` NOT IN (SELECT `ID` FROM '
                . $wpdb->posts
                . ' WHERE `post_type` IN ('
                . implode(', ', array_fill(0, count($remove_types), '%s'))
                . '))'
                // $filter_types.
                . ' AND `content_id` IN (SELECT `ID` FROM '
                . $wpdb->posts
                . ' WHERE `post_type` IN ('
                . implode(', ', array_fill(0, count($filter_types), '%s'))
                . '))',
                '~%',
                ...array_values($remove_types),
                ...array_values($filter_types)
            );
        } elseif ($remove_types) {
            $query = $wpdb->prepare(
                'SELECT `content_id` FROM '
                . esc_sql($this->table_names->contentlevels)
                . " WHERE `level_id`='Protection' AND `type` NOT LIKE %s"
                // $remove_types.
                . ' AND `content_id` NOT IN (SELECT `ID` FROM '
                . $wpdb->posts
                . ' WHERE `post_type` IN ('
                . implode(', ', array_fill(0, count($remove_types), '%s'))
                . '))',
                '~%',
                ...array_values($remove_types)
            );
        } elseif ($filter_types) {
            $query = $wpdb->prepare(
                'SELECT `content_id` FROM '
                . esc_sql($this->table_names->contentlevels)
                . " WHERE `level_id`='Protection' AND `type` NOT LIKE %s"
                // $filter_types.
                . ' AND `content_id` IN (SELECT `ID` FROM '
                . $wpdb->posts
                . ' WHERE `post_type` IN ('
                . implode(', ', array_fill(0, count($filter_types), '%s'))
                . '))',
                '~%',
                ...array_values($filter_types)
            );
        } else {
            $query = 'SELECT `content_id` FROM '
                . esc_sql($this->table_names->contentlevels)
                . " WHERE `level_id`='Protection' AND `type` NOT LIKE '~%'";
        }

        $query_hash = md5($query);
        if (! isset($protected[ $query_hash ])) {
            $protected[ $query_hash ] = $wpdb->get_col($query);
        }

        return $protected[ $query_hash ];
    }

    /**
     * Checks whether a custom post type is configured to be protected by WishList Member.
     *
     * @param  string $post_type Post type.
     * @return boolean
     */
    public function post_type_enabled($post_type)
    {
        if (wlm_post_type_is_excluded($post_type)) {
            return false;
        }
        $protected_types   = (array) $this->get_option('protected_custom_post_types');
        $protected_types[] = 'post';
        $protected_types[] = 'page';
        $protected_types[] = 'attachment';
        return in_array($post_type, $protected_types, true);
    }

    /**
     * Get User Feed Key
     *
     * @param  integer $user_id   User ID.
     * @param  boolean $no_verify True to not verify the valifity of $user_id. Default false.
     * @return string
     */
    public function feed_key($user_id = null, $no_verify = false)
    {
        static $ids_requested = [];
        $new_request          = ! in_array($user_id, $ids_requested, true);
        if ($new_request) {
            $ids_requested[] = $user_id;
        }
        $public = '';
        $user   = new \stdClass();
        if (is_null($user_id)) {
            $user = wp_get_current_user();
        } else {
            if ($no_verify) {
                $user->ID = $user_id;
            } else {
                $user = $this->get_user_data($user_id);
            }
        }
        if ($user->ID) {
            if (is_feed()) {
                $rss_ip_limit = $this->get_option('rss_ip_limit');
                // Limit the number of IPs that can access the protected feed per day.
                if ($rss_ip_limit > 0) {
                    // Get what's stored. set to array() if there's none.
                    $transient_name = sprintf('wlm_feed_limit_%s_%d', gmdate('Ymd'), $user->ID);
                    $limit          = get_transient($transient_name);
                    if (! is_array($limit)) {
                        $limit = [];
                    }
                    // Get the IP.
                    $ip = wlm_get_client_ip();

                    // If IP is not stored then we run our check.
                    if (! in_array($ip, $limit, true)) {
                        // Return $public if number of IPs is greater than the limit.
                        if (count($limit) >= $rss_ip_limit) {
                            return $public;
                        }

                        // Add $ip to $limit and save.
                        $limit[] = $ip;
                        $limit   = array_unique($limit);
                        set_transient($transient_name, $limit, strtotime('tomorrow') - time());
                    }
                }
            }

            $sk = $this->get_option('rss_secret_key');

            $public = $user->ID . ';' . md5($user->ID . $this->Get_UserMeta($user->ID, 'wishlistmember_feedkey') . ';' . md5($sk) . ';' . $sk);
        }
        return $public;
    }

    /**
     * Verifies if the feed key passed is valid
     *
     * @param  string $feedkey Feed key.
     * @return integer            User ID for feedkey or 0 on failure
     */
    public function verify_feed_key($feedkey)
    {
        /**
         * Filters the verify_feed_key result.
         *
         * @param integer $user_id User ID.
         * @param string  $feedkey Feed key.
         */
        return apply_filters('wishlistmember_verify_feed_key', 0, $feedkey);
    }

    /**
     * Processes the Private Tags
     *
     * @param  string $content Data to filter.
     * @param  array  $regtags Passed by reference, loaded with the registration form tags.
     * @return string filtered Data
     */
    public function private_tags($content, &$regtags)
    {

        global $wp_query;

        $is_userpost = false;

        $wpm_current_user = $GLOBALS['current_user'];
        $wpm_levels       = (array) $this->get_option('wpm_levels');

        // Generate tags.
        $tags    = [];
        $regtags = [];
        foreach ((array) $wpm_levels as $id => $level) {
            $tags[ $id ]    = '(wlm_|wlm|)private_' . preg_quote(strtolower($level['name']), '/');
            $regtags[ $id ] = '(wlm_|wlm|)register_' . preg_quote(strtolower($level['name']), '/');
        }
        $alltags = $tags;

        // Pick our tags.
        $thelevels = $this->get_membership_levels($wpm_current_user->ID, false, true);

        // Ignore non-standard membership levels (ppp levels).
        foreach ($thelevels as $key => $lvl) {
            if (preg_match('/U-\d+/', $lvl)) {
                unset($thelevels[ $key ]);
            }
        }
        $mytags   = [];
        $mylevels = [];

        foreach ((array) $thelevels as $thelevelid) {
            $mytags[] = $tags[ $thelevelid ];
            unset($tags[ $thelevelid ]);
            $mylevels[ $thelevelid ] = strtolower($wpm_levels[ $thelevelid ]['name']);
        }

        // Just strip private tags for admins and not for unprotected posts so that private tags still work on unprotected posts.
        if (wlm_arrval($wpm_current_user->caps, 'administrator')) {
            $content = preg_replace('/\[\/{0,1}(wlm_|wlm|)private_.+?\]/i', '', $content);
            $content = preg_replace('/\[\/{0,1}ismember\]/i', '', $content);
            $content = preg_replace('/\[\/{0,1}nonmember\]/i', '', $content);
        }
        // Remove all private tags inside user's private blocks.
        if (! isset($tag)) {
            $tag = '';
        }

        foreach ((array) $mytags as $mytag) {
            $myblocks = preg_match_all('/\[' . $tag . '\](.*?)\[\/' . $mytag . '\]/is', $content, $matches);
            foreach ((array) $matches[1] as $match) {
                $content = str_replace($match, preg_replace('/\[\/{0,1}(wlm_|wlm|)private_.+?\]/i', '', $match), $content);
            }
        }

        // Fix tag nesting.
        $xtags    = $alltags;
        $prevtags = [];
        foreach ((array) $tags as $id => $tag) {
            unset($xtags[ $id ]);
            preg_match_all('/\[(' . $tag . ')\].*?\[\/' . $tag . '\]/is', $content, $matches, PREG_SET_ORDER);
            foreach ((array) $matches as $match_set) {
                $match  = $match_set[0];
                $tag    = $match_set[1];
                $xmatch = preg_replace('/\[\/{0,1}' . $tag . '\]/i', '', $match);
                foreach ((array) $xtags as $xtag) {
                    $xmatch = preg_replace('/\[' . $xtag . '\]/i', '[/' . $tag . ']\0', $xmatch);
                    $xmatch = preg_replace('/\[\/' . $xtag . '\]/i', '\0[' . $tag . ']', $xmatch);
                }
                foreach ((array) $prevtags as $prevtag) {
                    $xmatch = preg_replace('/\[\/{0,1}' . $prevtag . '\]/i', '', $xmatch);
                }
                $content    = stripslashes(str_replace($match, '[' . $tag . ']' . $xmatch . '[/' . $tag . ']', $content));
                $prevtags[] = $tag;
            }
        }

        // Remove tags with whitespace only and empty tags.
        foreach ((array) $alltags as $tag) {
            $content = preg_replace('/\[' . $tag . '\]\[\/' . $tag . '\]/is', '', $content);
            $content = preg_replace('/\[' . $tag . '\](<\/p>\s<p>)\[\/' . $tag . '\]/is', '\1', $content);
        }

        // Remove blocks enclosed in private tags that don't belong to the user.
        $protectmsg = $this->get_option('private_tag_protect_msg');
        foreach ((array) $tags as $id => $tag) {
            $pmsg    = str_replace('[level]', ucwords(strtolower($wpm_levels[ $id ]['name'])), $protectmsg);
            $content = preg_replace('/\[' . $tag . '\].+?\[\/' . $tag . '\]/is', $pmsg, $content);
        }

        // Multiple private tag - multiple levels [private level1|level2|level3|...].
        while (preg_match_all('/\[private ([^\]]+)?\](.*?)\[\/private\]?/is', $content, $privates)) {
            foreach ((array) $privates[0] as $key => $private) {
                $private_levels = explode('|', strtolower(wlm_trim($privates[1][ $key ])));
                if (count(array_intersect($private_levels, $mylevels))) {
                    $content = str_replace($privates[0][ $key ], $privates[2][ $key ], $content);
                } else {
                    $pmsg    = str_replace('[level]', ucwords(strtolower(implode(', ', $private_levels))), $protectmsg);
                    $content = str_replace($privates[0][ $key ], $pmsg, $content);
                }
            }
        }

        if ($this->get_option('payperpost_ismember')) {
            $wpm_current_user = wp_get_current_user();
            $is_userpost      = in_array($wp_query->post->ID, $this->get_membership_content($wp_query->post->post_type, 'U-' . $wpm_current_user->ID));
        }

        // Private all, ismember and nonmember.
        if (! count($mylevels) && ( false === $is_userpost )) {
            $lnames = [];
            foreach ((array) $wpm_levels as $level) {
                $lnames[] = $level['name'];
            }
            $pmsg    = str_replace('[level]', ucwords(strtolower(implode(', ', $lnames))), $protectmsg);
            $content = preg_replace('/\[private all\].+?\[\/private\]/is', $pmsg, $content);

            // Not a member of any level - strip out ismember.
            $content = preg_replace('/\[ismember\].+?\[\/ismember\]/is', '', $content);
        } else {
            // Member of at least one level - strip out nonmember.
            $content = preg_replace('/\[nonmember\].+?\[\/nonmember\]/is', '', $content);
        }

        // Cleanup remaining private tags if any.
        $content = preg_replace('/\[\/{0,1}private[_ ]{0,1}[^\]]*?\]/i', '', $content);
        $content = preg_replace('/\[\/{0,1}ismember\]/i', '', $content);
        $content = preg_replace('/\[\/{0,1}nonmember\]/i', '', $content);

        return $content;
    }

    /**
     * Exclude certain pages from the list
     * Called by 'wp_list_pages_excludes' hook.
     *
     * @param  string[] $pages   Array of page IDs.
     * @param  boolean  $noerror True to exclude error pages as well.
     * @return string[]
     */
    public function exclude_pages($pages, $noerror = false)
    {
        $x = array_unique(array_merge($pages, [$this->magic_page(false)]));
        if (! $noerror) {
            foreach (['non_members_error_page', 'wrong_level_error_page', 'after_registration', 'membership_cancelled', 'membership_expired', 'membership_forapproval', 'membership_forconfirmation', 'unsubscribe', 'after_logout'] as $page_type) {
                $x[] = in_array($this->get_option($page_type . '_type'), [false, 'internal'], true) ? $this->get_option($page_type . '_internal') : '';
            }

            // Get the specific pages.
            $y = $this->get_specific_system_pages_id();

            $x = array_merge($x, $y);

            if ($this->get_option('exclude_pages')) {
                $wpm_levels = (array) $this->get_option('wpm_levels');
                // Exclude after reg pages for each level.
                foreach ((array) $wpm_levels as $level) {
                    if ($level['custom_afterreg_redirect'] && 'page' === $level['afterreg_redirect_type'] && is_numeric($level['afterreg_page'])) {
                        $x[] = $level['afterreg_page'];
                    }
                }
            }
        }
        return array_unique($x);
    }

    /**
     * 404 page handling for category pages
     * where all content are hidden due to protection
     *
     * Called by '404_template' hook.
     *
     * @param  string $template Path to the template.
     * @return string
     */
    public function the404($template)
    {
        // Check if 404 is a category page request.
        $cat = $GLOBALS['wp_query']->query_vars['cat'];
        if ($cat) {
            // If it's a category, check if the category has posts in it...
            $cat = get_category($cat);
            if ($cat && $cat->count) {
                /*
                 * if the category has posts in it then chances are we
                 * are just hiding content so we redirect to an error page instead...
                 */
                $redirect = is_user_logged_in() ? $this->wrong_level_url() : $this->non_members_url();
                // And redirect.
                header('Location:' . $redirect);
                exit;
            }
        }
        return $template;
    }

    /**
     * Process private tags.
     * Called by 'the_content_feed' and 'the_content' actions
     *
     * @param  string $content The content.
     * @return string
     */
    public function the_content($content)
    {
        global $current_user, $wp_query;

        if (false === apply_filters('wishlistmember_the_content_filter', $content)) {
            return $content;
        }

        $wpm_levels = (array) $this->get_option('wpm_levels');

        // Process private tags.
        $content = $this->private_tags($content, $regtags);

        // Process merge codes.
        // In-page registration form.
        foreach ((array) $regtags as $level => $regtag) {
            // Render the the reg form only when were supposed to.
            if (preg_match_all('/\[' . $regtag . '\]/i', $content, $match)) {
                // Don't process old register shotrtcodes if configured.
                // This will reduce the number of shortcodes WLM is registering,
                // Specially helpful with sites with large number of levels.
                if ($this->get_option('disable_legacy_reg_shortcodes')) {
                    if (strpos($match[0][0], 'wlm_register_')) {
                        continue;
                    }
                }

                $content = str_replace($match[0], $this->reg_content($level, true), $content);
            }
        }

        if (is_feed()) {
            $uid = $this->verify_feed_key(wlm_arrval($this->get_data, 'wpmfeedkey'));
            if (! $uid) {
                $pid = $wp_query->post->ID;
                if ($this->protect($pid)) {
                    $excerpt_length = apply_filters('excerpt_length', 55);
                    $excerpt_more   = '';

                    $content = wp_strip_all_tags($content);
                    $content = preg_split('/[\s]/', $content);

                    if (count($content) > $excerpt_length) {
                        list($content) = array_chunk($content, $excerpt_length);
                        $excerpt_more  = apply_filters('excerpt_more', ' [...]');
                    }

                    $content = implode(' ', $content) . $excerpt_more;
                }
            }
        }

        return $content;
    }

    /**
     * Auto insert the more tag
     * Called by 'the_posts' action
     *
     * @param  \WP_Post[] $posts Array of WP_Post objects.
     * @return array
     */
    public function the_more($posts)
    {
        if (is_page() || is_single() || is_admin()) {
            return $posts;
        }

        $isfeed            = is_feed();
        $authenticatedfeed = false;
        if ($isfeed && isset($this->get_data['wpmfeedkey'])) {
            $authenticatedfeed = $this->verify_feed_key(wlm_arrval($this->get_data, 'wpmfeedkey'));
        }

        $autoinsert       = $this->get_option('auto_insert_more');
        $protectaftermore = $this->get_option('protect_after_more');
        $insertat         = (int) $this->get_option('auto_insert_more_at') + 0;
        if ($insertat < 1) {
            $insertat = 0;
        }

        if (! is_array($posts)) {
            return $posts;
        }

        $posts_count = count($posts);
        for ($i = 0; $i < $posts_count; $i++) {
            $content   = wlm_trim($posts[ $i ]->post_content);
            $morefound = stristr($content, '<!--more-->');
            if (false === $morefound && $autoinsert) {
                $content       = preg_split('/([\s<>\[\]])/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
                $tag           = false;
                $wordcnt       = 0;
                $content_count = count($content);
                for ($ii = 0; $ii < $content_count; $ii++) {
                    $char = wlm_trim($content[ $ii ]);
                    if (false === $tag && '' !== wlm_trim($content[ $ii + 1 ])) {
                        if ('<' === $char || '[' === $char) {
                            $tag = '<' === $char ? '>' : ']';
                        }
                    } elseif ($char === $tag) {
                        $tag = false;
                    }
                    if (! $tag && '>' !== $char && ']' !== $char && '' !== $char) {
                        ++$wordcnt;
                    }
                    if ($wordcnt >= $insertat) {
                        $content[ $ii ] .= ' <!--more--> ';
                        break;
                    }
                }
                $content = implode('', $content);
            }
            if ($morefound || $autoinsert) {
                // If it's not an authenticated feed then we only return content before the "more" tag.
                if ($isfeed && $protectaftermore && ! $authenticatedfeed) {
                    $content = preg_split('/<!--more-->/i', $content);
                    $content = force_balance_tags($content[0]);
                }
            }
            $posts[ $i ]->post_content = $content;
        }
        return $posts;
    }

    /**
     * Feed links
     *
     * Called by 'feed_link' action.
     *
     * @param  string $link The feed permalink.
     * @param  string $key  Feed type.
     * @return string
     */
    public function feed_link($link, $key = null)
    {
        if (is_null($key)) {
            $key = $this->feed_key();
        }
        if ($key) {
            $param = 'wpmfeedkey=' . $key;
            if (! strpos($link, '?')) {
                $param = '?' . $param;
            } else {
                $param = '&' . $param;
            }
            $link .= $param;
        }
        return $link;
    }

    /**
     * Hide's Prev/Next Links as per Configuration.
     * Called by 'get_next_post_where' and 'get_previous_post_where' actions
     *
     * @param  string $where WHERE clause in the SQL query.
     * @return string
     */
    public function only_show_prev_next_links_for_level($where)
    {
        global $wpdb;
        if (is_admin()) {
            return $where;
        }
        if (! $this->get_option('only_show_content_for_level')) {
            return $where;
        }

        $id = wlm_arrval($GLOBALS, 'current_user', 'ID');

        if ($id) {
            if (! wlm_arrval($GLOBALS, 'current_user', 'caps', 'administrator') || is_feed()) {
                $wpm_levels = $this->get_option('wpm_levels');
                $levels     = $this->get_membership_levels($id, false, true);

                // Get all protected posts.
                $protected = $this->protected_ids();

                $enabled_types   = (array) $this->get_option('protected_custom_post_types');
                $enabled_types[] = 'post';
                $all             = $wpdb->get_col(
                    $wpdb->prepare(
                        'SELECT `ID` FROM `' . $wpdb->posts . '` WHERE `post_type` IN (' . implode(', ', array_fill(0, count($enabled_types), '%s')) . ')',
                        ...array_values($enabled_types)
                    )
                );
                $unp             = array_diff($all, $protected);
                $allpages        = false;
                $allposts        = false;

                // Retrieve post ids.
                if ($allposts) {
                    $ids = array_merge($unp, $wpdb->get_col("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type`='post' AND `post_status` IN ('publish','private')"));
                } else {
                    $ids = array_merge($unp, $this->get_membership_content('posts', $levels));
                }

                // Retrieve custom post types id.
                foreach ((array) $this->get_option('protected_custom_post_types') as $custom_type) {
                    $ids = array_merge($ids, $this->get_membership_content($custom_type, $levels));
                }

                $protected = array_diff($all, $ids);
            }
        } else {
            $protected = $this->protected_ids();
        }

        $protected[] = 0;
        $protected   = implode(',', $protected);
        $where      .= " AND p.ID NOT IN ({$protected})";
        return $where;
    }

    /**
     * Hide content per protection settings
     * Called by 'pre_get_posts' action
     *
     * @param  \WP_Query $query Query object.
     * @return [type]          [description]
     */
    public function only_show_content_for_level($query)
    {
        global $wpdb;

        /**
         * Filters the $query as passed by the WordPress pre_get_posts action
         *
         * @param \WP_Query $query.
         */
        $query = apply_filters('wishlistmember_only_show_content_for_level', $query);
        // Stop if $query is empty.
        if (empty($query)) {
            return;
        }

        /*
         * If we're trying to view post or page content then just return
         * the content to be processed by our the_content page This avoids
         * 404 pages to be displayed on hidden pages.
         */
        $pagename = isset($query->query['pagename']) ? $query->query['pagename'] : '';
        $pageid   = isset($query->query['page_id']) ? $query->query['page_id'] : '';
        $name     = isset($query->query['name']) ? $query->query['name'] : '';
        $p        = isset($query->query['p']) ? $query->query['p'] : '';

        if (( is_single() && ( $name || $p ) ) || ( is_page() && ( $pagename || $pageid ) )) {
            return;
        }

        $is_search = is_search();
        if ($is_search && ! $this->get_option('hide_from_search')) {
            return;
        }

        if (! is_feed() && ! $this->get_option('only_show_content_for_level')) {
            return;
        }

        $exclude_ids = $is_search ? $this->exclude_pages([]) : [];

        if (! is_admin()) {
            if (isset($GLOBALS['current_user']) && is_object($GLOBALS['current_user'])) {
                $id = $GLOBALS['current_user']->ID;
            }
            if (is_feed() && isset($this->get_data['wpmfeedkey'])) {
                $wpmfeedkey = $this->get_data['wpmfeedkey'];
                $id         = $this->verify_feed_key($wpmfeedkey);
            }
            if (! empty($id)) {
                if (! isset($GLOBALS['current_user']->caps['administrator']) || is_feed()) {
                    $wpm_levels = $this->get_option('wpm_levels');
                    $levels     = $this->get_membership_levels($id, false, true);

                    // Get all protected pages.
                    $protected       = $this->protected_ids();
                    $enabled_types   = (array) $this->get_option('protected_custom_post_types');
                    $enabled_types[] = 'post';
                    $enabled_types[] = 'page';
                    $enabled_types[] = 'attachment';

                    $all = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT `ID` FROM `' . $wpdb->posts . '` WHERE `post_type` IN (' . implode(', ', array_fill(0, count($enabled_types), '%s')) . ')',
                            ...array_values($enabled_types)
                        )
                    );
                    $unp = array_diff($all, $protected);
                    $ids = isset($ids) ? $ids : '';
                    $ids = array_merge((array) $ids, (array) $unp);

                    // Do we have all posts/pages enabled for any of the member's levels?
                    $allpages = false;
                    $allposts = false;
                    foreach ((array) $levels as $level) {
                        $allposts = $allposts | isset($wpm_levels[ $level ]['allposts']);
                        $allpages = $allpages | isset($wpm_levels[ $level ]['allpages']);
                    }

                    // Retrieve page ids.
                    if ($allpages) {
                        $ids = array_merge($ids, $wpdb->get_col("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type`='page' AND `post_status` IN ('publish','private')"));
                    } else {
                        $ids = array_merge($ids, $x = $this->get_membership_content('pages', $levels));
                    }

                    // Retrieve post ids.
                    if ($allposts) {
                        $ids = array_merge($ids, $wpdb->get_col("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type`='post' AND `post_status` IN ('publish','private')"));
                    } else {
                        $ids = array_merge($ids, $x = $this->get_membership_content('posts', $levels));
                    }

                    // Retrieve attachment ids.
                    $ids = array_merge($ids, $x = $this->get_membership_content('attachments', $levels));

                    // Retrieve custom post types id.
                    foreach ((array) $this->get_option('protected_custom_post_types') as $custom_type) {
                        $ids = array_merge($ids, $x = $this->get_membership_content($custom_type, $levels));
                    }

                    $no_access_ids = array_diff($all, $ids);
                    $exclude_ids   = array_merge($exclude_ids, $no_access_ids);
                }
            } else {
                // Public (not logged in).
                if (! is_feed() || ( is_feed() && $this->get_option('rss_hide_protected') )) {
                    $post_types = [];
                    if (! empty($query->query_vars['post_type'])) {
                        // If post_type is provided, use it.
                        $post_types = (array) $query->query_vars['post_type'];
                    } elseif (! empty($query->tax_query->queried_terms)) {
                        // If tax_query->queried_terms is provided, get post_types from it.
                        foreach (array_keys($query->tax_query->queried_terms) as $term) {
                            $tax        = get_taxonomy($term);
                            $post_types = array_merge($post_types, $tax->object_type);
                        }
                    }
                    $exclude_ids = $this->protected_ids($post_types);
                }
            }
        }
        $exclude_ids = apply_filters('wishlistmember_only_show_content_for_level_excluded_ids', $exclude_ids);
        if (count($exclude_ids)) {
            $exclude_ids                       = array_unique(array_merge($exclude_ids, (array) $query->query_vars['post__not_in']));
            $query->query_vars['post__not_in'] = $exclude_ids;
        }
    }

    /**
     * Only list pages that a user has access to
     * Called by 'wp_list_pages_excludes' hook.
     *
     * @param  string[] $pages Array of page IDs.
     * @return string[]
     */
    public function only_list_pages_for_level($pages)
    {
        if ($this->get_option('only_show_content_for_level') && ! wlm_arrval($GLOBALS['current_user']->caps, 'administrator')) {
            if ($GLOBALS['current_user']->ID) {
                $wpm_levels = $this->get_option('wpm_levels');
                $levels     = $this->get_membership_levels($GLOBALS['current_user']->ID, false, true);
                // Is the user a member of a level that can view all pages?
                $allpages = false;
                foreach ((array) $levels as $level) {
                    $allpages = $allpages | isset($wpm_levels[ $level ]['allpages']);
                }
                if ($allpages) {
                    return $pages;
                }

                // Retrieve pages that the user can't view.
                $protect = $this->protected_ids();
                $xpages  = $this->get_membership_content('pages');
                $allowed = [];
                foreach ((array) $levels as $level) {
                    $allowed = array_merge((array) $allowed, (array) $xpages[ $level ]);
                }
                $allowed = array_merge((array) $allowed, (array) $this->get_membership_content('pages', 'U-' . $GLOBALS['current_user']->ID));
                $pages   = array_merge($pages, array_diff($protect, $allowed));
            } else {
                $pages = array_merge($pages, $this->protected_ids());
            }

            $pages = array_unique($pages);

            /*
             * Filter so that we are only excluding pages.
             * Adding a lot of ID's in excludes greatly affects performance.
             */
            global $wpdb;
            $real_pages = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE `post_type`='page'");
            $pages      = array_intersect($pages, $real_pages);

            $k = array_search('', $pages, true);
            if (false !== $k) {
                unset($pages[ $k ]);
            }
        }
        return $pages;
    }

    /**
     * Disables RSS Enclosures for non-authenticated feeds
     * Called by 'rss_enclosure' hook
     *
     * @param string $html_link_tag The HTML link tag with a URI and other attributes.
     */
    public function rss_enclosure($html_link_tag)
    {
        $authenticatedfeed = $this->verify_feed_key(wlm_arrval($this->get_data, 'wpmfeedkey'));
        if ($authenticatedfeed) {
            return $html_link_tag;
        } else {
            return '';
        }
    }

    /**
     * Only show comments for level
     * Called by 'comment_feed_where' hook.
     *
     * @param  string $where WHERE clause of the SQL query.
     * @return string
     */
    public function only_show_comments_for_level($where)
    {
        $wpm_levels = $this->get_option('wpm_levels');
        $id         = 0;
        if (is_user_logged_in()) {
            $id = $GLOBALS['current_user']->ID;
        }
        if (isset($this->get_data['wpmfeedkey'])) {
            $wpmfeedkey = $this->get_data['wpmfeedkey'];
            $id         = $this->verify_feed_key($wpmfeedkey);
        }
        if ($id) {
            if (current_user_can('activate_plugins')) {
                return $where;
            }
            $levels = $this->get_membership_levels($id, $names, true);
            foreach ($levels as $level) {
                if ($wpm_levels[ $level ]['comments']) {
                    return $where;
                }
            }
            $protected_comments = $this->get_membership_content('comments', $levels);

            $comments = [0];
            foreach ($protected_comments as $comment) {
                $comments = array_merge($comments, (array) $comment);
            }
            $comments = implode(',', array_map('wlm_abs_int', array_unique($comments)));
            $where   .= ' AND comment_post_ID NOT IN (' . $comments . ') ';
        } else {
            $protected_comments = $this->get_membership_content('comments');
            $protect            = $this->protected_ids();
            $protect[]          = 0;
            foreach ($protected_comments as $pc) {
                $protect = array_merge($protect, (array) $pc);
            }
            $protect = implode(',', array_map('wlm_abs_int', array_unique($protect)));
            $where  .= ' AND comment_post_ID NOT IN (' . $protect . ') ';
        }
        return $where;
    }

    /**
     * AJAX handler for bulk saving of content tab.
     * Called by 'wp_ajax_wlm_contenttab_bulk_action' hook
     */
    public function contenttab_bulk_action_ajax()
    {
        @ini_set('zlib.output_compression', 1);
        $success        = 0;
        $data           = [];
        $msg            = '';
        $x_content_type = (bool) $this->post_data['manage_comments'] ? '~COMMENT' : $this->post_data['content_type'];
        $bulk_action    = wlm_arrval($this->post_data, 'bulk_action');

        switch ($bulk_action) {
            case 'protection':
                $protection = 'Unprotected' === $this->post_data['bulk_action_value'] ? 'N' : 'Y';
                $data       = [];
                foreach ($this->post_data['content_ids'] as $content_id) {
                    switch ($this->post_data['bulk_action_value']) {
                        case 'Unprotected':
                        case 'Protected':
                            switch ($this->post_data['content_type']) {
                                case 'categories':
                                    $this->cat_protected($content_id, $protection);
                                    break;
                                case 'folders':
                                    $this->folder_protected($content_id, $protection);
                                    $data[ $content_id ]['htaccess'] = 'ok';
                                    break;
                                default:
                                    $this->special_content_level($content_id, 'Protection', $protection, $x_content_type);
                            }
                            $this->special_content_level($content_id, 'Inherit', 'N', $x_content_type);
                            $data[ $content_id ]['padlock'] = 'Protected' === $this->post_data['bulk_action_value'] ? 1 : 0;
                            break;
                        case 'Inherited':
                            $this->inherit_protection($content_id, 'categories' === $this->post_data['content_type'], (bool) $this->post_data['manage_comments'], $new_protect, $new_levels);
                            $data[ $content_id ]['padlock']        = $new_protect ? 1 : 0;
                            $new_levels                            = $this->level_ids_to_level_names($new_levels);
                            $data[ $content_id ]['new_level_keys'] = empty($new_levels) ? '' : array_keys($new_levels);
                            $data[ $content_id ]['new_levels']     = empty($new_levels) ? '&nbsp;&mdash;' : implode(', ', $new_levels);
                            break;
                    }
                    $data[ $content_id ]['label'] = $this->post_data['bulk_action_value'];
                }
                $success = 1;
                // Translators: 1: Bulk action.
                $msg = sprintf(__('Protection status set to "%1$s" for selected items', 'wishlist-member'), $this->post_data['bulk_action_value']);
                break;
            case 'add_levels':
            case 'remove_levels':
                $the_levels = (array) $this->post_data['bulk_action_value'];
                foreach ($this->post_data['content_ids'] as $content_id) {
                    if (! $this->special_content_level($content_id, 'Inherit', null, $x_content_type)) {
                        $current_levels = $this->get_content_levels($x_content_type, $content_id, true, false, $immutable);
                        $the_levels     = array_diff($the_levels, $immutable);
                        if ('add_levels' === $bulk_action) {
                            $new_levels     = array_unique(array_merge(array_keys($current_levels), $the_levels));
                            $current_levels = $current_levels + $this->level_ids_to_level_names($the_levels);
                        } else {
                            $new_levels     = array_diff(array_keys($current_levels), $the_levels);
                            $current_levels = array_diff_key($current_levels, array_flip($the_levels));
                        }
                        $this->set_content_levels($x_content_type, $content_id, array_merge($new_levels, $immutable));

                        $data[ $content_id ]['new_level_keys'] = empty($current_levels) ? '' : array_keys($current_levels);
                        $data[ $content_id ]['new_levels']     = empty($current_levels) ? '&nbsp;&mdash;' : implode(', ', $current_levels);
                        $data[ $content_id ]['immutable']      = implode(',', $immutable);
                    }
                }

                $the_levels = $this->level_ids_to_level_names($the_levels);
                $success    = 1;
                $msg        = sprintf(
                    // Translators: 1: 'ADDED to' or 'REMOVED from' depending on bulk actions, 2: Membership levels.
                    __('The following membership levels were %1$s the selected items: %2$s', 'wishlist-member'),
                    'add_levels' === $bulk_action ? __('ADDED to', 'wishlist-member') : __('REMOVED from', 'wishlist-member'),
                    implode(', ', $the_levels)
                );
                break;
            case 'ppp':
                $data = [];
                foreach ($this->post_data['content_ids'] as $content_id) {
                    switch ($this->post_data['bulk_action_value']) {
                        case 'Disabled':
                            $this->pay_per_post($content_id, 'N');
                            break;
                        case 'Paid':
                            $this->pay_per_post($content_id, 'Y');
                            $this->free_pay_per_post($content_id, 'N');
                            break;
                        case 'Free':
                            $this->pay_per_post($content_id, 'Y');
                            $this->free_pay_per_post($content_id, 'Y');
                            break;
                    }
                    $data[ $content_id ] = $this->post_data['bulk_action_value'];
                }
                $success = 1;
                // Translators: 1: Bulk action.
                $msg = sprintf(__('Pay Per Post status set to "%1$s" for selected items', 'wishlist-member'), $this->post_data['bulk_action_value']);
                break;
            case 'pppusers':
                $add    = wlm_arrval($this->post_data, 'ppp_add');
                $remove = wlm_arrval($this->post_data, 'ppp_remove');
                $data   = [];
                foreach ($this->post_data['content_ids'] as $content_id) {
                    if (count($remove)) {
                        $this->remove_post_users($this->post_data['content_type'], $content_id, $remove);
                    }
                    if (count($add)) {
                        $this->add_post_users($this->post_data['content_type'], $content_id, $add);
                    }
                    $data[ $content_id ] = $this->count_post_users($content_id, $this->post_data['content_type']);
                }
                $success = 1;
                $msg     = __('Pay Per Post Users updated for selected items', 'wishlist-member');
                break;
            case 'force_download':
                $data = [];
                foreach ($this->post_data['content_ids'] as $content_id) {
                    $this->folder_force_download($content_id, 'Yes' === $this->post_data['bulk_action_value']);
                    $data[ $content_id ] = $this->post_data['bulk_action_value'];
                }
                $success = 1;
                $msg     = sprintf(
                    // Translators: 1: Either the text 'enabled' or 'disabled'.
                    __('Force download %1$s for selected folders', 'wishlist-member'),
                    'Yes' === $this->post_data['bulk_action_value'] ? __('enabled', 'wishlist-member') : __('disabled', 'wishlist-member')
                );
                break;
            default:
                $msg = 'Invalid bulk action';
        }
        echo wp_json_encode(
            [
                'success' => $success,
                'msg'     => $msg,
                'data'    => $data,
            ]
        );
        wp_die();
    }

    /**
     * AJAX save handler to update protection
     * Called by 'wp_ajax_wlm_update_protection' hook
     */
    public function update_protection_ajax()
    {
        @ini_set('zlib.output_compression', 1);

        $result          = new \stdClass();
        $result->success = 1;
        $result->data    = new \stdClass();

        $protection = 'Unprotected' === $this->post_data['protection'] ? 'N' : 'Y';

        $x_content_type = (bool) $this->post_data['manage_comments'] ? '~COMMENT' : $this->post_data['content_type'];

        switch ($this->post_data['protection']) {
            case 'Unprotected':
            case 'Protected':
                switch ($this->post_data['content_type']) {
                    case 'categories':
                        $this->cat_protected($this->post_data['content_id'], $protection);
                        break;
                    case 'folders':
                        $this->folder_protected($this->post_data['content_id'], $protection);
                        break;
                    default:
                        $this->special_content_level($this->post_data['content_id'], 'Protection', $protection, $x_content_type);
                }
                $this->set_content_levels($x_content_type, $this->post_data['content_id'], $this->post_data['levels']);
                $this->special_content_level($this->post_data['content_id'], 'Inherit', 'N', $x_content_type);
                break;
            case 'Inherited':
                $this->inherit_protection(
                    $this->post_data['content_id'],
                    'categories' === $this->post_data['content_type'],
                    (bool) $this->post_data['manage_comments']
                );
                break;
        }

        $result->data->protection = $this->post_data['protection'];

        switch ($this->post_data['content_type']) {
            case 'categories':
                $result->data->padlock = (int) $this->cat_protected($this->post_data['content_id']);
                break;
            case 'folders':
                $result->data->padlock = (int) $this->folder_protected($this->post_data['content_id']);
                break;
            default:
                $result->data->padlock = (int) $this->special_content_level($this->post_data['content_id'], 'Protection', null, $x_content_type);
        }

        $levels = $this->get_content_levels($x_content_type, $this->post_data['content_id'], true, false, $immutable);

        $result->data->levels     = implode(', ', $levels);
        $result->data->immutable  = implode(',', $immutable);
        $result->data->level_keys = implode(',', array_keys($levels));

        if (! in_array($x_content_type, ['categories', 'folders', 'files', '~COMMENT'], true)) {
            switch ($this->post_data['payperpost']) {
                case 'Disabled':
                    $this->pay_per_post($this->post_data['content_id'], 'N');
                    break;
                case 'Paid':
                    $this->pay_per_post($this->post_data['content_id'], 'Y');
                    $this->free_pay_per_post($this->post_data['content_id'], 'N');
                    break;
                case 'Free':
                    $this->pay_per_post($this->post_data['content_id'], 'Y');
                    $this->free_pay_per_post($this->post_data['content_id'], 'Y');
                    break;
            }
            $result->data->payperpost = $this->post_data['payperpost'];
        }

        if ('folders' === $this->post_data['content_type']) {
            switch ($this->post_data['forcedownload']) {
                case 'Yes':
                    $this->folder_force_download($this->post_data['content_id'], true);
                    break;
                case 'No':
                    $this->folder_force_download($this->post_data['content_id'], false);
                    break;
            }
            $result->data->forcedownload = $this->post_data['forcedownload'];
        }

        if (is_array($this->post_data['post_users'])) {
            $post_users = $this->post_data['post_users'];
            foreach ($post_users as $key => $value) {
                if (! preg_match('/^U-\d+$/', $value)) {
                    unset($post_users[ $key ]);
                }
            }

            $remove = array_diff(
                $this->get_post_users($this->post_data['content_type'], $this->post_data['content_id']),
                $post_users
            );

            if ($remove) {
                $this->remove_post_users($this->post_data['content_type'], $this->post_data['content_id'], $remove);
            }
            if ($post_users) {
                $this->add_post_users($this->post_data['content_type'], $this->post_data['content_id'], $post_users);
            }

            $result->data->post_users = $this->count_post_users($this->post_data['content_id'], $this->post_data['content_type']);
        }

        $this->pass_protection(
            $this->post_data['content_id'],
            'categories' === $this->post_data['content_type']
        );

        echo wp_json_encode($result);
        wp_die();
    }

    /**
     * Filter for wp_get_nav_menu_items
     * Handles the hiding/showing of Menu items
     *
     * Called by 'wp_get_nav_menu_items' hook
     *
     * @param  array $items Array of menu items.
     * @return array
     */
    public function only_list_nav_menu_items_for_level($items)
    {
        global $current_user;
        /*
         * we only filter when only_show_content_for_level is enabled
         * or if the current user is an administrator
         */
        if ($this->get_option('only_show_content_for_level') && ! wlm_arrval($GLOBALS['current_user']->caps, 'administrator')) {
            // Get all levels.
            $wpm_levels = $this->get_option('wpm_levels');

            /*
             * save $items to $orig
             * and set $items to empty array
             */
            $orig                        = $items;
            $items                       = [];
            $protected_custom_post_types = $this->get_option('protected_custom_post_types');
            // If a user is logged in.
            if ($current_user->ID) {
                // Get all levels for this user.
                $levels = $this->get_membership_levels($current_user->ID, false, true);

                // Process content.
                $allcategories = false;
                $allpages      = false;
                $allposts      = false;
                foreach ($levels as $level) {
                    if (! $allcategories) {
                        if (isset($wpm_levels[ $level ]['allcategories'])) {
                            $allcategories = true;
                        }
                    }
                    if (! $allpages) {
                        if (isset($wpm_levels[ $level ]['allpages'])) {
                            $allpages = true;
                        }
                    }
                    if (! $allposts) {
                        if (isset($wpm_levels[ $level ]['allposts'])) {
                            $allposts = true;
                        }
                    }
                }
                $categories = [];
                $pages      = [];
                $posts      = [];

                // Categories.
                if (! $allcategories) {
                    $categories = $this->get_membership_content('categories', $levels);
                }

                // Pages.
                if (! $allpages) {
                    $pages = $this->get_membership_content('pages', $levels);
                }

                // Posts.
                if (! $allposts) {
                    $posts = $this->get_membership_content('posts', $levels);
                    // Retrieve custom post types id.
                    foreach ((array) $protected_custom_post_types as $custom_type) {
                        $posts = array_merge($posts, $x = $this->get_membership_content($custom_type, $levels));
                    }
                }

                /*
                 * go through each menu item and remove anything
                 * that the user does not have access to
                 */
                foreach ($orig as $item) {
                    if (in_array($item->object, $this->taxonomies ?? [], true)) {
                        if ($allcategories || ! $this->cat_protected($item->object_id) || in_array($item->object_id, $categories)) {
                            $items[] = $item;
                        }
                    } elseif ('page' === $item->object) {
                        if ($allpages || ! $this->protect($item->object_id) || in_array($item->object_id, $pages)) {
                            $items[] = $item;
                        }
                    } elseif ('post' === $item->object) {
                        if ($allposts || ! $this->protect($item->object_id) || in_array($item->object_id, $posts)) {
                            $items[] = $item;
                        }
                    } elseif (in_array($item->object, (array) $protected_custom_post_types, true)) {
                        if ($allposts || ! $this->protect($item->object_id) || in_array($item->object_id, $posts)) {
                            $items[] = $item;
                        }
                    } else {
                        $items[] = $item;
                    }
                }
            } else {
                /*
                 * go through each menu item and
                 * remove all protected content
                 */
                foreach ($orig as $item) {
                    if (in_array($item->object, $this->taxonomies ?? [], true)) {
                        if (! $this->cat_protected($item->object_id)) {
                            $items[] = $item;
                        }
                    } elseif ('page' === $item->object || 'post' === $item->object) {
                        if (! $this->protect($item->object_id)) {
                            $items[] = $item;
                        }
                    } elseif (in_array($item->object, (array) $protected_custom_post_types, true)) {
                        if (! $this->protect($item->object_id)) {
                            $items[] = $item;
                        }
                    } else {
                        $items[] = $item;
                    }
                }
            }

            /*
             * re-organize menus, make sure that
             * hierarchy remains meaningful
             */

            // First we collect all IDs from $items to make it easier to search.
            $item_ids = [];
            foreach ($items as $key => $item) {
                $item_ids[ $item->ID ] = $key;
            }

            // Next, we collect all parent IDs from $orig.
            $parent_ids = [];
            foreach ($orig as $item) {
                $parent_ids[ $item->ID ] = $item->menu_item_parent;
            }

            // Then we walk through and fix the parent IDs if needed.
            $items_count = count($items);
            for ($i = 0; $i < $items_count; $i++) {
                $item   = &$items[ $i ];
                $parent = $item->menu_item_parent;

                while (! isset($item_ids[ $parent ])) {
                    $parent =  isset($parent_ids[ $parent ]) ? $parent_ids[ $parent ] : 0 ;
                    if (! $parent) {
                        break;
                    }
                }
                $item->menu_item_parent = $parent;
            }
        }
        // Return the filtered menu item.
        return $items;
    }

    /**
     * Ajax handler for searching pay per posts.
     * Called by 'wp_ajax_wlm_payperpost_search' hook
     */
    public function wlm_pay_per_post_search()
    {
        $func = wlm_arrval($this->get_data, 'callback');
        if ($func) {
            $data          = [];
            $limit         = sprintf('%d,%d', $this->post_data['page'] - 1, $this->post_data['page_limit']);
            $data['posts'] = $this->get_pay_per_posts(['ID', 'post_title', 'post_type'], false, $this->post_data['search'], $limit, $total, $query);
            $data['total'] = $total;
            $data['query'] = $query;
            printf('%s(%s)', esc_js($func), wp_json_encode($data));
        }
        exit;
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('pre_get_posts', [$wlm, 'only_show_content_for_level']);
        add_action('wp_ajax_wlm_contenttab_bulk_action', [$wlm, 'contenttab_bulk_action_ajax']);
        add_action('wp_ajax_wlm_payperpost_search', [$wlm, 'wlm_pay_per_post_search']);
        add_action('wp_ajax_wlm_update_protection', [$wlm, 'update_protection_ajax']);
        add_action('wp_list_pages_excludes', [$wlm, 'only_list_pages_for_level']);
        add_filter('404_template', [$wlm, 'the404']);
        add_filter('comment_feed_where', [$wlm, 'only_show_comments_for_level']);
        add_filter('feed_link', [$wlm, 'feed_link']);
        add_filter('post_comments_feed_link', [$wlm, 'feed_link']);
        add_filter('category_feed_link', [$wlm, 'feed_link']);
        add_filter('author_feed_link', [$wlm, 'feed_link']);
        add_filter('get_next_post_where', [$wlm, 'only_show_prev_next_links_for_level']);
        add_filter('get_previous_post_where', [$wlm, 'only_show_prev_next_links_for_level']);
        add_filter('the_content_feed', [$wlm, 'the_content']);
        add_filter('the_content', [$wlm, 'the_content']);
        add_filter('the_posts', [$wlm, 'the_more']);
        $wlm->get_option('disable_rss_enclosures') && add_filter('rss_enclosure', [$wlm, 'rss_enclosure']);
        add_filter('wp_get_nav_menu_items', [$wlm, 'only_list_nav_menu_items_for_level']);
        add_filter('wp_list_pages_excludes', [$wlm, 'exclude_pages']);
    }
);
