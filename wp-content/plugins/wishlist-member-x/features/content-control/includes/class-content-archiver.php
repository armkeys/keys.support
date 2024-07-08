<?php

/**
 * Content Archiver Module
 *
 * @package WishListMember/Features/ContentControl
 */

namespace WishListMember\Features;

defined('ABSPATH') || die();

/**
 * Content Archiver Core Class
 */
class Content_Archiver
{
    /**
     * Load hooks.
     */
    public function load_hooks()
    {
        // Save Content Archiver Options when savign the post.
        add_action('wp_insert_post', [&$this, 'save_content_arch_options']);
        // Post filters.
        add_filter('posts_where', [&$this, 'post_expiration_where']);
        add_filter('get_next_post_where', [&$this, 'post_expiration_adjacent_where']);
        add_filter('get_previous_post_where', [&$this, 'post_expiration_adjacent_where']);

        // Filter for get_pages function because it does not use WP_Query.
        add_filter('get_pages', [&$this, 'arc_getpages'], 9999, 2);
        add_filter('pre_get_posts', [&$this, 'the_preget_post']);

        add_action('wishlistmember_post_page_options_menu', [&$this, 'wlm3_post_options_menu']);
        add_action('wishlistmember_post_page_options_content', [&$this, 'content_arch_options']);
    }

    /**
     * Display the post options menu.
     */
    public function wlm3_post_options_menu()
    {
        echo '<li><a href="#" data-target=".wlm-inside-archiver" class="wlm-inside-toggle">' . esc_html__('Archiver', 'wishlist-member') . '</a></li>';
    }

    /**
     * Content Archiver Options.
     */
    public function content_arch_options()
    {
        $post_id      = wlm_get_data()['post'];
        $custom_types = get_post_types(
            [
                'public'   => true,
                '_builtin' => false,
            ]
        );
        $ptypes       = array_merge(['post', 'page'], $custom_types);
        $post_type    = $post_id ? get_post_type($post_id) : wlm_get_data()['post_type'];
        $post_type    = $post_type ? $post_type : 'post';
        if ($post_type) {
            if (! in_array($post_type, $ptypes)) {
                return false;
            }
        } else {
            return false;
        }

        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        wlm_print_script(plugins_url('views/assets/js/post-page-options-archiver.js', WLM_CONTENT_CONTROL_FILE));
        ?>
            <div class="wlm-inside wlm-inside-archiver" style="display: none;">
                <table class="widefat" id='wlcc_ca' style="text-align: left;" cellspacing="0">
                    <thead>
                    <tr style="width:100%;">
                        <th style="width: 60%;"> <?php esc_html_e('Membership Level/s'); ?></th>
                        <th style="width: 40%;"> <?php esc_html_e('Archive Date'); ?> </th>
                    </tr>
                    </thead>
                </table>
                <div id="wlcclevels_ca" style="text-align:left;overflow:auto;">
                    <table class="widefat" id="wlcc_ca" cellspacing="0" style="text-align:left;">
                        <tbody>
                    <?php foreach ((array) $wpm_levels as $id => $level) : ?>
                        <?php
                            $alt         = 0;
                            $date        = '';
                            $post_expiry = $this->get_post_expiry_date($post_id, $id);
                            $post_expiry = is_array($post_expiry) ? $post_expiry : [];
                        if (count($post_expiry) > 0 && $post_id) {
                            $date = gmdate(get_option('date_format') . ' ' . get_option('time_format'), wlm_strtotime(wlm_trim($post_expiry[0]->exp_date)));
                            $date = $date ? $date : '';
                        }
                        ?>
                            <tr id="tr<?php echo esc_attr($id); ?>" style="width:100%;" class="<?php echo ( $alt++ ) % 2 ? '' : 'alternate'; ?>">
                                <td style="width: 60%;border-bottom: 1px solid #eeeeee;"><strong><?php echo esc_html($level['name']); ?></strong></td>
                                    <td style="width: 40%;border-bottom: 1px solid #eeeeee;">
                                        <input style="width: 200px;" type="text" class="form-control wlm-datetimepicker" id="wlcc_expiry<?php echo esc_attr($id); ?>" name="wlcc_expiry[<?php echo esc_attr($id); ?>]" value="<?php echo esc_attr($date); ?>" >
                                    </td>
                            </tr>
                    <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="text-align: right; padding-top: 4px; padding-bottom: 8px;">
                    <div class="wlm-message" style="display: none"><?php esc_html_e('Saved', 'wishlist-member'); ?></div>
                    <a href="#" class="wlm-btn -with-icons -success -centered-span wlm-archiver-save">
                        <i class="wlm-icons"><img src="<?php echo esc_url(wishlistmember_instance()->plugin_url3); ?>/ui/images/baseline-save-24px.svg" alt=""></i>
                        <span><?php esc_html_e('Save Schedule', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div>
            <input type='hidden' name='wlccca_save_marker' value='1'>
        <?php
    }

    /**
     * Save content archiver options.
     */
    public function save_content_arch_options()
    {
        $post_ID = wlm_post_data()['post_ID'];

        $wlccca_save_marker = wlm_post_data()['wlccca_save_marker'];
        if (1 !== (int) $wlccca_save_marker) {
            return false;
        }

        $wpm_levels  = wishlistmember_instance()->get_option('wpm_levels');
        $wlcc_expiry = wlm_post_data()['wlcc_expiry'];
        foreach ((array) $wpm_levels as $id => $level) {
            $wlccexpiry  = '' === $wlcc_expiry[ $id ] || empty($wlcc_expiry[ $id ]) ? 0 : $wlcc_expiry[ $id ];
            $wlccexpdate = date_parse($wlccexpiry);
            if (( isset($wlccexpdate['error_count']) && $wlccexpdate['error_count'] > 0 ) || ! $wlccexpdate['year']) {
                $this->delete_post_expiry_date($post_ID, $id);
            } else {
                $date = wlm_date('Y-m-d H:i:s', wlm_strtotime(wlm_trim($wlccexpiry) . ' ' . wlm_timezone_string()));
                $this->save_post_expiry_date($post_ID, $id, $date);
            }
        }
    }

    /**
     * Save post expiry date.
     *
     * @param integer $post_id The post ID.
     * @param string  $mlevel  The membership level.
     * @param string  $d       The expiry date.
     */
    public function save_post_expiry_date($post_id, $mlevel, $d)
    {
        global $wpdb;
        $exp = $this->get_post_expiry_date($post_id, $mlevel);
        $exp = is_array($exp) ? $exp : [];
        if (count($exp) > 0) {
            $wpdb->update(
                $wpdb->prefix . 'wlcc_contentarchiver',
                ['exp_date' => $d],
                [
                    'mlevel'  => $mlevel,
                    'post_id' => $post_id,
                ]
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'wlcc_contentarchiver',
                [
                    'post_id'  => $post_id,
                    'mlevel'   => $mlevel,
                    'exp_date' => $d,
                ]
            );
        }
    }

    /**
     * Get post expiry date.
     *
     * @param  string  $post_id The post ID.
     * @param  string  $mlevel  The membership level.
     * @param  integer $start   The start index.
     * @param  integer $limit   The limit.
     * @return array|object|null The post expiry date.
     */
    public function get_post_expiry_date($post_id = '', $mlevel = '', $start = 0, $limit = 0)
    {
        global $wpdb;

        $mlevels = is_array($mlevel) ? $mlevel : [$mlevel];
        $q_limit = [$start, $limit];

        if (! empty($post_id) && ! empty($mlevel)) {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' WHERE post_id=%d AND mlevel IN (' . implode(', ', array_fill(0, count($mlevels), '%s')) . ')',
                        $post_id,
                        ...array_values($mlevels)
                    )
                );
        } elseif (! empty($post_id)) {
            if ($limit > 0) {
                    return $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' WHERE post_id=%d ORDER BY date_added DESC LIMIT %d,%d',
                            $post_id,
                            ...array_values($q_limit)
                        )
                    );
            } else {
                    return $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' WHERE post_id=%d',
                            $post_id
                        )
                    );
            }
        } elseif (! empty($mlevel)) {
            if ($limit > 0) {
                    return $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' WHERE mlevel IN (' . implode(', ', array_fill(0, count($mlevels), '%s')) . ') ORDER BY date_added DESC LIMIT %d,%d',
                            ...array_values($mlevels),
                            ...array_values($q_limit)
                        )
                    );
            } else {
                    return $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' WHERE mlevel IN (' . implode(', ', array_fill(0, count($mlevels), '%s')) . ')',
                            ...array_values($mlevels)
                        )
                    );
            }
        } elseif ($limit > 0) {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' ORDER BY date_added DESC LIMIT %d,%d',
                        ...array_values($q_limit)
                    )
                );
        } else {
                return $wpdb->get_results('SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' ORDER BY date_added DESC');
        }
    }

    /**
     * Delete post expiry date.
     *
     * @param integer $post_id The post ID.
     * @param string  $mlevel  The membership level.
     */
    public function delete_post_expiry_date($post_id, $mlevel = '%')
    {
        global $wpdb;

        $mlevel   = wlm_or(wlm_trim($mlevel), '%');
        $post_ids = (array) $post_id;

        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentarchiver') . ' WHERE `mlevel` LIKE %s AND `post_id` IN (' . implode(', ', array_fill(0, count($post_ids), '%d')) . ')',
                $mlevel,
                ...array_values($post_ids)
            )
        );
    }

    /**
     * Retrieves expired posts.
     */
    public function get_expired_post()
    {
        $date_today         = wlm_date('Y-m-d H:i:s');
        $wpm_current_user   = wp_get_current_user();
        $levels             = [];
        $user_direct_levels = [];
        $post_levels        = [];
        $pplevel            = [];
        $user_pp_posts      = [];
        $expired_posts      = [];
        $unexpired_posts    = [];

        if ($wpm_current_user->ID > 0) {
            $levels = $this->get_users_level($wpm_current_user->ID);
            // Remove payper post membership level.
            foreach ((array) $levels as $id => $level) {
                if (false !== strpos($level, 'U')) {
                    $pplevel[] = $level;
                    unset($levels[ $id ]);
                }
            }
            if (method_exists(wishlistmember_instance(), 'get_user_pay_per_post') && count($pplevel) > 0) {
                $user_pp_posts = wishlistmember_instance()->get_user_pay_per_post($pplevel, false, null, true);
            }
        }
        // Get the post with expiration date.
        if (count($levels) > 0) {
            $mlevel_post = $this->get_post_expiry_date('', $levels);
        } else {
            $mlevel_post = $this->get_post_expiry_date();
        }

        // $user_direct_levels will contain user levels that has no archive date.
        $user_direct_levels = $levels;
        foreach ((array) $mlevel_post as $lvl_post) {
            array_splice($user_direct_levels, array_search($lvl_post->mlevel, $user_direct_levels), 1);
        }

        // Start checking the posts with expiration date if the user has access.
        foreach ((array) $mlevel_post as $lvl_post) {
            $postdate_diff = $this->date_diff($lvl_post->exp_date, $date_today, 86400); // + result means expired.
            if (count($levels) <= 0) { // Non users, or non members.
                if ($postdate_diff > 0) { // Check if the post itself is expired.
                    $expired_posts[] = $lvl_post->post_id;
                }
            } else {
                // Get level registration date of the user.
                $user_leveldate = gmdate('Y-m-d H:i:s', wishlistmember_instance()->user_level_timestamp($wpm_current_user->ID, $lvl_post->mlevel) + wishlistmember_instance()->gmt);
                $leveldate_diff = $this->date_diff($lvl_post->exp_date, $user_leveldate, 86400); // + result means user cannot access this post.

                if ($postdate_diff > 0) { // Check if the post is expired and if the user has previous access to the post.
                    if ($leveldate_diff > 0) {
                        $expired_posts[] = $lvl_post->post_id;
                    } else {
                        $unexpired_posts[] = $lvl_post->post_id;
                    }
                } else {
                    $unexpired_posts[] = $lvl_post->post_id;
                }
            }
        }

        // Lets check if user has any levels that has no archive date.
        if (count($user_direct_levels) > 0) {
            // Lets see if if post is protected for any of user_direct_level.
            $lvl_post_id             = $lvl_post->post_id ?? '';
            $post_levels             = wishlistmember_instance()->get_content_levels('posts', $lvl_post_id);
            $direct_access_by_levels = ! empty(array_intersect($post_levels, $user_direct_levels));

            // We dont archive the post if the post and the user has level(s) without any archive date.
            if ($direct_access_by_levels) {
                $unexpired_posts[] = $lvl_post->post_id;
            }
        }

        $unexpired_posts = array_unique($unexpired_posts); // Remove duplicate post id from unexpired post.
        $expired_posts   = array_diff($expired_posts, $unexpired_posts); // Take out post if the user still has access on it using different membership level.
        $expired_posts   = array_unique($expired_posts); // Remove duplicate post id from expired post.

        // Remove users pp post from the list.
        if (count($user_pp_posts) > 0) {
            $expired_posts = array_diff($expired_posts, $user_pp_posts);
        }

        return $expired_posts;
    }

    /**
     * The pre get post hook.
     *
     * @param WP_Query $query The WP_Query instance.
     */
    public function the_preget_post($query)
    {
        global $wpdb;
        $wpm_current_user = wp_get_current_user();
        $is_single        = is_single() || is_page() ? true : false;
        // If this is not a single post or page or its in the admin area, dont try redirect.
        if (! $is_single || current_user_can('manage_options')) {
            return $query;
        }

        // Retrieve the post id and post name (if needed).
        $pid  = false;
        $name = false;
        if (is_page()) {
            $pid   = isset($query->query['page_id']) ? $query->query['page_id'] : false;
            $name  = ! $pid && isset($query->query['pagename']) ? $query->query['pagename'] : '';
            $ptype = ! $pid && isset($query->query['post_type']) ? $query->query['post_type'] : '';
            // Check if WP queried_object have post_type if WP Query post_type is empty.
            if (! $ptype && isset($name)) {
                $ptype = isset($query->queried_object->post_type) ? $query->queried_object->post_type : false;
            }
        } elseif (is_single()) {
            $pid   = isset($query->query['p']) ? $query->query['p'] : false;
            $name  = isset($query->query['name']) ? $query->query['name'] : '';
            $ptype = isset($query->query['post_type']) ? $query->query['post_type'] : '';
        } else {
            $pid  = false;
            $name = '';
        }

        // Get the post id based from the post name we got.
        $name_array = explode('/', $name);
        $name       = array_slice($name_array, -1, 1);
        $name       = $name[0];
        if ($name) {
            if ($ptype) {
                $pid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM `$wpdb->posts` WHERE post_name=%s AND post_type=%s", $name, $ptype));
            } else {
                $pid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM `$wpdb->posts` WHERE post_name=%s", $name));
            }
        } else {
            return $query;
        }

        // If theres a postid, lets redirect.
        if ($pid) {
            $archived_content = $this->get_expired_post();

            if ($wpm_current_user->ID > 0) {
                $levels = $this->get_users_level($wpm_current_user->ID);

                // Get the post with expiration date.
                if (count($levels) > 0) {
                    $mlevel_post = $this->get_post_expiry_date($pid, $levels);
                } else {
                    $mlevel_post = $this->get_post_expiry_date();
                }

                // Levels that has archive date.
                $post_arc_levels = [];
                foreach ((array) $mlevel_post as $lvl_post) {
                    $post_arc_levels[ $lvl_post->post_id ][] = $lvl_post->mlevel;
                }

                // All levels ( with and without archive date).
                $post_levels = [];
                foreach ((array) $post_arc_levels as $post_id => $arc_levels) {
                    $post_levels[ $post_id ] = wishlistmember_instance()->get_content_levels('posts', $post_id);
                }

                // Post normal levels ( levels without archive date ).
                $unexpired_posts = [];
                foreach ((array) $post_arc_levels as $post_id => $arc_levels) {
                    $post_non_arc_levels = array_diff(
                        $post_levels[ $post_id ],
                        $post_arc_levels[ $post_id ]
                    );

                    $protection_key = array_search('Protection', $post_non_arc_levels, true);
                    if (false !== $protection_key) {
                        unset($post_non_arc_levels[ $protection_key ]);
                    }

                    // We dont archive the post if the post and the user has level(s) without any archive date.
                    if (! empty($post_non_arc_levels)) {
                        $unexpired_posts[] = $post_id;
                    }
                }

                if (in_array($pid, $unexpired_posts)) {
                    // User had access to the post with normall level that has no archive date.
                    if ($levels) {
                        foreach ((array) $levels as $user_lvl) {
                            if (in_array($user_lvl, $post_non_arc_levels, true)) {
                                return $query;
                            }
                        }
                    }
                }
            }

            if (in_array($pid, $archived_content)) {
                $wlcc_archived_error_page = wishlistmember_instance()->get_option('archiver_error_page_type');
                $wlcc_archived_error_page = $wlcc_archived_error_page ? $wlcc_archived_error_page : get_option('wlcc_archived_error_page');
                $wlcc_archived_error_page = $wlcc_archived_error_page ? $wlcc_archived_error_page : 'text';

                if ('url' === $wlcc_archived_error_page) {
                    $wlcc_archived_error_page_url = wishlistmember_instance()->get_option('archiver_error_page_url');
                    $wlcc_archived_error_page_url = $wlcc_archived_error_page_url ? $wlcc_archived_error_page_url : get_option('wlcc_archived_error_page_url');

                    if (! empty($wlcc_archived_error_page_url)) {
                        $url   = wlm_trim($wlcc_archived_error_page_url);
                        $p_url = parse_url($url);
                        if (! isset($p_url['scheme'])) {
                            $url = 'http://' . $url;
                        }
                    }
                } elseif ('internal' === $wlcc_archived_error_page) {
                    $wlcc_archived_error_page = wishlistmember_instance()->get_option('archiver_error_page_internal');
                    if (! $wlcc_archived_error_page) {
                        $wlcc_archived_error_page = $wlcc_archived_error_page && 'url' !== $wlcc_archived_error_page && 'internal' !== $wlcc_archived_error_page && 'text' !== $wlcc_archived_error_page ? $wlcc_archived_error_page : false;
                    }
                    $r_pid = (int) $wlcc_archived_error_page;
                    if (is_int($r_pid) && $r_pid > 0 && ! isset($archived_content[ $r_pid ])) {
                        $url = get_permalink($r_pid);
                    }
                } else {
                    $url = add_query_arg('sp', 'archiver_error_page', wishlistmember_instance()->magic_page());
                    // If not set, save the default.
                    $pages_text = wishlistmember_instance()->get_option('archiver_error_page_text');
                    if (! $pages_text) {
                        $f = wishlistmember_instance()->legacy_wlm_dir . '/resources/page_templates/archiver_internal.php';
                        if (file_exists($f)) {
                            include $f;
                        }
                        $pages_text = $content ? nl2br($content) : '';
                        // Lets save it.
                        if ($pages_text) {
                            wishlistmember_instance()->save_option('archiver_error_page_text', $pages_text);
                            wishlistmember_instance()->save_option('archiver_error_page_type', 'text');
                        }
                    }
                }
                if (! $url) {
                    $url = add_query_arg('sp', 'archiver_error_page', wishlistmember_instance()->magic_page());
                }
                wp_safe_redirect($url);
                exit(0);
            }
        }

        return $query;
    }

    /**
     * Filters the WHERE clause for post expiration.
     *
     * @param  string $where The WHERE clause.
     * @return string The modified WHERE clause.
     */
    public function post_expiration_where($where)
    {
        global $wpdb;
        $wpm_current_user = wp_get_current_user();
        $table            = $wpdb->prefix . 'posts';
        $levels           = [];
        $utype            = 'non_users';
        $w                = $where;
        if (isset($wpm_current_user->caps['administrator']) && $wpm_current_user->caps['administrator']) {
            return $w;
        }
        // Determine the user type.
        if ($wpm_current_user->ID > 0) {
            $levels = $this->get_users_level($wpm_current_user->ID);
            // Remove payper post membership level.
            foreach ((array) $levels as $id => $level) {
                if (false !== strpos($level, 'U')) {
                    unset($levels[ $id ]);
                }
            }

            if (count($levels) > 0) {
                $utype = 'members';
            } else {
                $utype = 'non_members';
            }
        }

        // Get the post with expiration date.
        if (count($levels) > 0) {
            $mlevel_post = $this->get_post_expiry_date('', $levels);
        } else {
            $mlevel_post = $this->get_post_expiry_date();
        }

        // Levels that has archive date.
        $posts_arc_levels = [];
        foreach ((array) $mlevel_post as $lvl_post) {
            $posts_arc_levels[ $lvl_post->post_id ][] = $lvl_post->mlevel;
        }

        // All levels ( with and without archive date).
        $posts_levels = [];
        foreach ((array) $posts_arc_levels as $post_id => $arc_levels) {
            $posts_levels[ $post_id ] = wishlistmember_instance()->get_content_levels('posts', $post_id);

            // Removing Protection from levels array.
            $protection_key = array_search('Protection', $posts_levels[ $post_id ], true);
            if (false !== $protection_key) {
                unset($posts_levels[ $post_id ][ $protection_key ]);
            }
            // Removing PayPerPost from levels array.
            $pay_per_post_key = array_search('PayPerPost', $posts_levels[ $post_id ], true);
            if (false !== $pay_per_post_key) {
                unset($posts_levels[ $post_id ][ $pay_per_post_key ]);
            }
            // Removing U-xxxx from levels array.
            foreach ($posts_levels[ $post_id ] as $key => $ll) {
                if (false !== stripos($ll, 'U-')) {
                    unset($posts_levels[ $post_id ][ $key ]);
                }
            }
        }
        // Post normal levels ( levels without archive date ).
        $unexpired_posts = [];
        foreach ((array) $posts_arc_levels as $post_id => $arc_levels) {
            $post_non_arc_levels = array_diff(
                $posts_levels[ $post_id ],
                $arc_levels
            );
            // Removing Protection from levels array.
            $protection_key = array_search('Protection', $post_non_arc_levels, true);
            if (false !== $protection_key) {
                unset($post_non_arc_levels[ $protection_key ]);
            }
            // Removing PayPerPost from levels array.
            $pay_per_post_key = array_search('PayPerPost', $post_non_arc_levels, true);
            if (false !== $pay_per_post_key) {
                unset($post_non_arc_levels[ $pay_per_post_key ]);
            }
            // Removing U-xxxx from levels array.
            foreach ($post_non_arc_levels as $key => $ll) {
                if (false !== stripos($ll, 'U-')) {
                    unset($post_non_arc_levels[ $key ]);
                }
            }
            // We dont archive the post if the post and the user has level(s) without any archive date.
            if (! empty($post_non_arc_levels)) {
                $unexpired_posts[] = $post_id;
            }

            // $post_non_arc_levels are levels that have access to the Post ID with the levels that has acrhived date already removed.
            // Let's check if any of the user's levels belongs to $post_non_arc_levels.
            $levels_of_user_that_has_access = array_intersect($post_non_arc_levels, $levels);
            if (empty($levels_of_user_that_has_access)) {
                $unexpired_posts = array_diff($unexpired_posts, [$post_id]);
            }
        }
        $is_single = is_single() || is_page() ? true : false;
        if (! $is_single) {
            $archiver_hide_post_listing = wishlistmember_instance()->get_option('archiver_hide_post_listing');
            if ($archiver_hide_post_listing) {
                $expired_posts = $this->get_expired_post();
            } else {
                $expired_posts = [];
            }
        } else {
            $expired_posts = $this->get_expired_post();
        }
        $expired_posts = array_diff($expired_posts, $unexpired_posts);
        // Filter the post thats not to be shown.
        if (count($expired_posts) > 0) {
            $w .= " AND $table.ID NOT IN (" . implode(',', $expired_posts) . ')';
        }
        return $w;
    }


    /**
     * Modify the WHERE clause for adjacent posts to exclude expired posts.
     *
     * @param  string $where The original WHERE clause.
     * @return string The modified WHERE clause.
     */
    public function post_expiration_adjacent_where($where)
    {
        global $post;
        $wpm_current_user  = wp_get_current_user();
        $current_post_date = $post->post_date;
        $w                 = $where;
        if (! $wpm_current_user->caps['administrator']) { // Disregard content expiry for admin.
            $expired_posts = $this->get_expired_post();
            // Filter the post thats not to be shown.
            if (count($expired_posts) > 0) {
                $postids = implode(',', $expired_posts) . ',' . $post->ID;
                $w      .= ' AND p.ID NOT IN (' . $postids . ') ';
            }
        }
        return $w;
    }

    /**
     * Get pages for archiving.
     *
     * @param  array $pages The array of pages.
     * @return array The updated array of pages.
     */
    public function arc_getpages($pages)
    {
        if (count((array) $pages) <= 0) {
            return $pages;
        }
        $wpm_current_user = wp_get_current_user();
        $levels           = [];
        $utype            = 'non_users';
        if (! $wpm_current_user->caps['administrator']) {
            // Determine the user type.
            if ($wpm_current_user->ID > 0) {
                $levels = $this->get_users_level($wpm_current_user->ID);
                // Remove payper post membership level.
                foreach ((array) $levels as $id => $level) {
                    if (false !== strpos($level, 'U')) {
                        unset($levels[ $id ]);
                    }
                }

                if (count($levels) > 0) {
                    $utype = 'members';
                } else {
                    $utype = 'non_members';
                }
            }

            $is_single     = false; // Post listing always.
            $expired_posts = [];
            if (! $is_single) {
                $archiver_hide_post_listing = wishlistmember_instance()->get_option('archiver_hide_post_listing');
                if ($archiver_hide_post_listing) {
                    $expired_posts = $this->get_expired_post();
                }
            }

            if (count($expired_posts) > 0) {
                foreach ($pages as $pid => $page) {
                    if (in_array($page->ID, $expired_posts, true)) {
                        unset($pages[ $pid ]);
                    }
                }
            }
        }
        return $pages;
    }


    /**
     * FUNCTION to get users membership levels.
     *
     * @param  integer $uid The user ID.
     * @return array The array of membership levels.
     */
    public function get_users_level($uid)
    {
        static $levels  = false;
        static $user_id = false;
        if ($user_id && $user_id === $uid && is_array($levels)) {
            return $levels;
        }

        $user_id = $uid;
        if ($user_id > 0) {
            if (method_exists(wishlistmember_instance(), 'get_member_active_levels')) {
                $levels = wishlistmember_instance()->get_member_active_levels($user_id);
            } else {
                $levels = wishlistmember_instance()->get_membership_levels($user_id, false, true);
            }
        } else {
            $levels = [];
        }

        return $levels;
    }

    /**
     * FUNCTION to sort a multidimensional array by a specific subkey.
     *
     * @param  array   $a      The input array.
     * @param  string  $subkey The subkey to sort by.
     * @param  boolean $sort   Whether to sort the array or not.
     * @param  boolean $asc    Whether to sort in ascending order or not.
     * @return array The sorted array.
     */
    public function subval_sort($a, $subkey, $sort = true, $asc = true)
    {
        $c = [];
        if (count((array) $a) > 0) {
            foreach ($a as $k => $v) {
                        $b[ $k ] = $v->$subkey;
            }
            if ($asc) {
                arsort($b);
            } else {
                asort($b);
            }
            foreach ($b as $key => $val) {
                    $c[] = $a[ $key ];
                    // Save the post arrangement.
                    $d[] = $a[ $key ]->ID;
            }

            // Save this if viewing post.
            if (! is_single() && $sort) {
                wishlistmember_instance()->save_option('wlcc_post_arr', $d);
            }
        }
            return $c;
    }

    /**
     * FUNCTION to calculate the difference between two dates.
     *
     * @param  string  $start   The start date.
     * @param  string  $end     The end date.
     * @param  integer $divisor The divisor (optional).
     * @return integer   The calculated difference.
     */
    public function date_diff($start, $end, $divisor = 0)
    {
        $d1        = date_parse($start);
        $sdate     = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], $d1['year']);
        $d2        = date_parse($end);
        $edate     = mktime($d2['hour'], $d2['minute'], $d2['second'], $d2['month'], $d2['day'], $d2['year']);
        $time_diff = $edate - $sdate;
        return $time_diff / $divisor;
    }

    /**
     * FUNCTION to validate an integer.
     *
     * @param  mixed $in_data The data to validate.
     * @return boolean Whether the data is a valid integer or not.
     */
    public function validateint($in_data)
    {
        $int_retval = false;
        $int_value  = intval($in_data);
        $str_value  = strval($int_value);
        if ($str_value === $in_data) {
            $int_retval = true;
        }
        return $int_retval;
    }
}
