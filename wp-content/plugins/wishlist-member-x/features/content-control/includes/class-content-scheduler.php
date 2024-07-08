<?php

/**
 * Content Scheduler Module
 *
 * @package WishListMember/Features/ContentControl
 */

namespace WishListMember\Features;

defined('ABSPATH') || die();

/**
 * Content Scheduler Core Class
 */
class Content_Scheduler
{
    /**
     * Debug flag.
     *
     * @var boolean
     */
    private $debug = false;
    /**
     * Content Scheduler Constructor
     */
    public function __construct()
    {
        // Used to debug queries.
        // Domain.com?debug=<licensekey> will disyplay the debug post query.
        $debug = isset(wlm_get_data()['wlcc_debug']) && '' !== wlm_get_data()['wlcc_debug'] ? wlm_get_data()['wlcc_debug'] : false;
        if ($debug && wishlistmember_instance()->get_option('LicenseKey') === $debug) {
            $this->debug = true;
        }
    }

    /**
     * Load hooks.
     */
    public function load_hooks()
    {
        // Widget setup.
        if (function_exists('wp_register_sidebar_widget')) {
            wp_register_sidebar_widget('wlm-wishlist-content-control', 'WishList Content Scheduler', [$this, 'sched_widget']);
            wp_register_widget_control('wlm-wishlist-content-control', 'WishList Content Scheduler', [$this, 'sched_widget_admin']);
        }

        // Save Content Drip Options when savign the post.
        add_action('wp_insert_post', [&$this, 'save_content_sched_options']);

        add_action('wishlistmember_post_page_options_menu', [&$this, 'wlm3_post_options_menu']);
        add_action('wishlistmember_post_page_options_content', [&$this, 'content_sched_posts_options']);

        add_filter('wishlistmember_shortcodes', [$this, 'add_shortcode'], 9999);

        add_shortcode('scheduled-contents', [&$this, 'process_shortcode']);

        if (! is_admin()) { // Do not run filters on admin area.
            // Hooks for Content Scheduler.
            add_filter('posts_join', [&$this, 'wlm_sched_content_join'], 9999);
            add_filter('posts_where', [&$this, 'wlm_sched_content_where'], 9999);
            add_filter('posts_groupby', [&$this, 'wlm_sched_content_group'], 9999);

            // Hooks for next and previous links for Content Scheduler.
            add_filter('get_next_post_where', [&$this, 'wlm_sched_adjacent_post_where'], 9999);
            add_filter('get_previous_post_where', [&$this, 'wlm_sched_adjacent_post_where'], 9999);

            add_filter('the_posts', [&$this, 'posts_pages_list'], 9999); // Use to filter the date.
            add_filter('the_content', [&$this, 'sched_the_content'], 9999); // Add private tag.
            add_filter('posts_clauses', [&$this, 'my_posts_clause_filter']);

            add_filter('get_terms', [&$this, 'sched_term_filter'], 9999, 3);

            add_filter('pre_get_posts', [&$this, 'the_preget_post']);

            // Filter for get_pages function because it does not use WP_Query.
            add_filter('get_pages', [&$this, 'arc_getpages'], 9999, 2);

            // Filter  for menu items.
            add_filter('wp_get_nav_menu_items', [&$this, 'wp_get_nav_menu_items'], 9999);

            if ($this->debug) {
                add_filter('posts_request', [&$this, 'debug_query']);
            }
        }
    }

    /**
     * Content Scheduler Table creation
     */
    public function activate()
    {
        global $wpdb;
        // Cleanup code -> delete records where membership level does not exist.
        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        if (count($wpm_levels) > 0) {
            $in = array_keys($wpm_levels);
            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM `' . esc_sql($wpdb->prefix . 'wlcc_contentsched') . '` WHERE mlevel NOT IN (' . implode(', ', array_fill(0, count($in), '%s')) . ')',
                    ...array_values($in)
                )
            );
        }
    }

    /**
     * Content Scheduler Post Options Menu.
     */
    public function wlm3_post_options_menu()
    {
        echo '<li><a href="#" data-target=".wlm-inside-scheduler" class="wlm-inside-toggle">' . esc_html__('Scheduler', 'wishlist-member') . '</a></li>';
    }

    /**
     * Content Scheduler Post Option Area
     */
    public function content_sched_posts_options()
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
            if (! in_array($post_type, $ptypes, true)) {
                return false; // Do not display option on pages.
            }
        } else {
            return false;
        }

        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        wlm_print_script(plugins_url('views/assets/js/post-page-options-scheduler.js', WLM_CONTENT_CONTROL_FILE));
        ?>
        <div class="wlm-inside wlm-inside-scheduler" style="display: none;">
            <table class="widefat" id='wlcc_ca' style="text-align: left;" cellspacing="0">
                <thead>
                    <tr style="width:100%;">
                        <th style="width: 30%;"> <?php esc_html_e('Membership Level/s', 'wishlist-member'); ?></th>
                        <th style="width: 20%;"> <center> <?php esc_html_e('Show', 'wishlist-member'); ?> </center> </th>
                        <th style="width: 20%;"> </th>
                        <th style="width: 30%;"> <?php esc_html_e('Show For', 'wishlist-member'); ?> </th>
                    </tr>
                </thead>
            </table>
            <div id="wlcclevels_ca" style="text-align:left;overflow:auto;">
                <table class="widefat" id="wlcc_ca" cellspacing="0" style="text-align:left;">
                    <tbody>
                    <?php $alt = 0; ?>
                    <?php foreach ((array) $wpm_levels as $id => $level) : ?>
                        <?php
                        $post_sched_data = [];
                        if (! empty($post_id)) {
                            $post_sched_data = $this->get_content_sched($post_id, $id, 0, 0, '', ['publish', 'draft', 'pending', 'private']);
                        }
                        $sched_show_type = isset($post_sched_data[0]->show_type) ? $post_sched_data[0]->show_type : 'after';
                        $sched_show_type = 'ondate' === $sched_show_type ? 'ondate' : 'after';

                        $show_on_date = isset($post_sched_data[0]->show_on_date) ? $post_sched_data[0]->show_on_date : null;

                        // If $show_on_date is not null and not 0000-00-00 00:00:00, then format it m-d-Y H:i A.
                        if (! is_null($show_on_date) && '0000-00-00 00:00:00' !== $show_on_date) {
                            $show_on_date = gmdate(get_option('date_format') . ' ' . get_option('time_format'), wlm_strtotime(wlm_trim($show_on_date)));
                            $show_on_date = $show_on_date ? $show_on_date : '';
                        } else {
                            $show_on_date = '';
                        }

                        ?>
                        <tr id="tr<?php echo esc_attr($id); ?>" style="width:100%;" class="<?php echo ( $alt++ ) % 2 ? '' : 'alternate'; ?>">
                            <td style="width: 30%;border-bottom: 1px solid #eeeeee;"><strong><?php echo esc_html($level['name']); ?></strong></td>
                            <td style="width: 20%;border-bottom: 1px solid #eeeeee;">

                                <div class="form-group schedule-type-holder">
                                    <div class="switch-toggle switch-toggle-wlm -compressed" style="width:80%;">
                                        <input class="toggle-radio scheduler-toggle-radio-sched  sched-after" id="after-<?php echo esc_attr($id); ?>" name="sched_toggle[<?php echo esc_attr($id); ?>]" type="radio" value="after"  <?php echo ( 'after' === $sched_show_type ) ? 'checked' : ''; ?> />
                                        <label for="after-<?php echo esc_attr($id); ?>"><?php esc_html_e('After', 'wishlist-member'); ?></label>
                                        <input class="toggle-radio scheduler-toggle-radio-sched sched-ondate" id="ondate-<?php echo esc_attr($id); ?>" name="sched_toggle[<?php echo esc_attr($id); ?>]" type="radio" value="ondate" <?php echo ( 'ondate' === $sched_show_type ) ? 'checked' : ''; ?> />
                                        <label for="ondate-<?php echo esc_attr($id); ?>"><?php esc_html_e('On', 'wishlist-member'); ?></label>
                                        <a href="" class="btn btn-primary"></a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="date-ranger-container scheduler-show-ondate-holder" <?php echo ( 'after' === $sched_show_type ) ? 'style="display:none;"' : ''; ?> >
                                    <input type="text" class="form-control wlm-datetimepicker schedule-ondate" name="show_on_date[<?php echo esc_attr($id); ?>]" style="width: 90%;" value='<?php echo esc_attr($show_on_date); ?>'>
                                </div>
                                <div class="scheduler-show-after-holder" <?php echo ( 'ondate' === $sched_show_type ) ? 'style="display:none;"' : ''; ?>>
                                    <input style='text-align:center; width: 100px; display: inline;' size='5' type='number' class="form-control scheddays" name='scheddays[<?php echo esc_attr($id); ?>]' value='<?php echo esc_attr($post_sched_data[0]->num_days ? $post_sched_data[0]->num_days : ''); ?>' />
                                    <?php esc_html_e('day(s)', 'wishlist-member'); ?>
                                </div>
                            </td>
                            <td style="width: 20%;border-bottom: 1px solid #eeeeee;">
                                <div class="date-ranger-container scheduler-show-ondate-holder" <?php echo ( 'after' === $sched_show_type ) ? 'style="display:none;"' : ''; ?> >
                                    <input style='text-align:center; width: 100px; display: inline;' size='5' type='number' class="form-control hidedays" name='ondate_hidedays[<?php echo esc_attr($id); ?>]' value='<?php echo esc_attr($post_sched_data[0]->on_date_hide_days ? $post_sched_data[0]->on_date_hide_days : ''); ?>' />
                                        <?php esc_html_e('day(s)', 'wishlist-member'); ?>
                                </div>
                                <div class="scheduler-show-after-holder" <?php echo ( 'ondate' === $sched_show_type ) ? 'style="display:none;"' : ''; ?>>
                                    <input style='text-align:center; width: 100px; display: inline;' size='5' type='number' class="form-control hidedays" name='hidedays[<?php echo esc_attr($id); ?>]' value='<?php echo esc_attr($post_sched_data[0]->hide_days ? $post_sched_data[0]->hide_days : ''); ?>' />
                                    <?php esc_html_e('day(s)', 'wishlist-member'); ?>
                                </div>


                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="text-align: right; padding-top: 4px; padding-bottom: 8px;">
                <div class="wlm-message" style="display: none"><?php esc_html_e('Saved', 'wishlist-member'); ?></div>
                <a href="#" class="wlm-btn -with-icons -success -centered-span wlm-scheduler-save">
                    <i class="wlm-icons"><img src="<?php echo esc_url(wishlistmember_instance()->plugin_url3); ?>/ui/images/baseline-save-24px.svg" alt=""></i>
                    <span><?php esc_html_e('Save Schedule', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
        <?php
    }
    /**
     * Saving Values on Content Scheduler Posts Options
     */
    public function save_content_sched_options()
    {
        $post_ID = wlm_post_data()['post_ID'];

        if (! empty($post_ID) && ( isset(wlm_post_data()['scheddays']) || isset(wlm_post_data()['hidedays']) )) { // Save if theres post id.
            $wpm_levels      = wishlistmember_instance()->get_option('wpm_levels'); // Get the membership levels.
            $scheddays       = isset(wlm_post_data()['scheddays']) ? wlm_post_data()['scheddays'] : [];
            $hidedays        = isset(wlm_post_data()['hidedays']) ? wlm_post_data()['hidedays'] : [];
            $sched_toggle    = isset(wlm_post_data()['sched_toggle']) ? wlm_post_data()['sched_toggle'] : [];
            $show_on_date    = isset(wlm_post_data()['show_on_date']) ? wlm_post_data()['show_on_date'] : [];
            $ondate_hidedays = isset(wlm_post_data()['ondate_hidedays']) ? wlm_post_data()['ondate_hidedays'] : [];
            $arr             = [];
            foreach ((array) $wpm_levels as $id => $level) {
                $days_delay      = isset($scheddays[ $id ]) ? $scheddays[ $id ] : 0;
                $hide_delay      = isset($hidedays[ $id ]) ? $hidedays[ $id ] : 0;
                $schedtoggle     = isset($sched_toggle[ $id ]) ? $sched_toggle[ $id ] : 'after';
                $showondate      = isset($show_on_date[ $id ]) ? $show_on_date[ $id ] : 0;
                $ondatehidedelay = isset($ondate_hidedays[ $id ]) ? $ondate_hidedays[ $id ] : 0;

                if (( $days_delay > 0 && 'after' === $schedtoggle ) || 'ondate' === $schedtoggle) { // Save the sched days greater than zero only.
                    $arr[ $id ] = $scheddays[ $id ];
                    $this->save_content_sched($post_ID, $id, $days_delay, $hide_delay, $schedtoggle, $showondate, $ondatehidedelay);
                } else {
                    $this->delete_content_sched($post_ID, $id);
                }
            }
            if (count($arr) < 1) { // If all levels have no value, delete all the sched value for this post.
                $this->delete_content_sched($post_ID);
            }
        }
    }

    /**
     * Add shortcode.
     *
     * @param array $shortcodes The array of shortcodes.
     */
    public function add_shortcode($shortcodes)
    {
                // Content scheduler custom post types.
                $cs_post_types = [
                    'all'  => ['label' => 'All'],
                    'post' => ['label' => 'Posts'],
                    'page' => ['label' => 'Pages'],
                ];
                foreach ((array) get_post_types(['_builtin' => false], 'objects') as $key => $value) {
                    if (isset($value->label)) {
                        $cs_post_types[ $key ] = ['label' => $value->label];
                    }
                }

                $shortcodes['scheduled-contents'] = [
                    'label'      => esc_html__('Content Scheduler', 'wishlist-member'),
                    'attributes' => [
                        'title'     => [
                            'columns' => 6,
                            'label'   => esc_html__('Title', 'wishlist-member'),
                            'default' => esc_html__('My Upcoming Posts', 'wishlist-member'),
                        ],
                        'ptype'     => [
                            'columns' => 6,
                            'label'   => esc_html__('Post Type', 'wishlist-member'),
                            'type'    => 'select',
                            'options' => $cs_post_types,
                            'default' => 'all',
                        ],
                        'showpost'  => [
                            'columns' => 4,
                            'label'   => esc_html__('Number of Content', 'wishlist-member'),
                            'type'    => 'number',
                            'default' => 10,
                        ],
                        'px'        => [
                            'columns' => 4,
                            'label'   => esc_html__('List Spacing', 'wishlist-member'),
                            'type'    => 'number',
                            'default' => 4,
                        ],
                        'separator' => [
                            'columns' => 4,
                            'label'   => esc_html__('Date Separator', 'wishlist-member'),
                            'default' => '@',
                        ],
                        'sort'      => [
                            'columns' => 4,
                            'label'   => esc_html__('Order by', 'wishlist-member'),
                            'type'    => 'select',
                            'options' => [
                                'title'      => ['label' => esc_html__('Title', 'wishlist-member')],
                                'ID'         => ['label' => esc_html__('ID', 'wishlist-member')],
                                'menu_order' => ['label' => esc_html__('Menu Order', 'wishlist-member')],
                                'date'       => ['label' => esc_html__('Schedule Date', 'wishlist-member')],
                                'days'       => ['label' => esc_html__('Days', 'wishlist-member')],
                            ],
                        ],
                        'showdate'  => [
                            'columns' => 4,
                            'label'   => '&nbsp;',
                            'type'    => 'checkbox',
                            'options' => [
                                'yes' => ['label' => esc_html__('Show Date', 'wishlist-member')],
                            ],
                            'default' => 'yes',
                        ],
                        'showtime'  => [
                            'columns' => 4,
                            'label'   => '&nbsp;',
                            'type'    => 'checkbox',
                            'options' => [
                                'yes' => ['label' => esc_html__('Show Time', 'wishlist-member')],
                            ],
                            'default' => 'yes',
                        ],
                    ],
                ];
                return $shortcodes;
    }


    /**
     * Get scheduled content.
     *
     * @param  string $ptype The post type.
     * @return array The scheduled content.
     */
    public function get_sched_content($ptype = '')
    {
        global $wlmpl_post_login;
        static $post_type   = '';
        static $sched_posts = null;
        if ($ptype === $post_type && ! is_null($sched_posts)) {
            return $sched_posts;
        }
        $post_type        = $ptype;
        $date_today       = wlm_date('Y-m-d H:i:s'); // Get date today.
        $wpm_current_user = wp_get_current_user(); // Get the current user.
        $levels           = wishlistmember_instance()->get_member_active_levels($wpm_current_user->ID); // Get users membership levels.
        $pplevel          = [];
        $user_pp_posts    = [];
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

        if (count($levels) > 0) {
            $mlevel_post = $this->get_content_sched('', $levels, 0, 0, $ptype); // Get all the scheduled contents of the levels.
        } else {
            $mlevel_post = $this->get_content_sched(); // If not logged in or dont have membership level, dont show content with sched.
        }

        $sched_posts = []; // Holds the posts that is sched.
        $has_access  = []; // Holds post that has access, temporary container.

        // Check all the post.
        foreach ((array) $mlevel_post as $lvl_post) {
            $date_diff    = '';
            $date2post    = '';
            $newpostdate  = '';
            $newpost_diff = '';
            $hidedate     = '';
            $hide_diff    = 0;
            if (count($levels) > 0) { // Skip this part if he has no membership level.
                // Get the post details.
                $post_details = get_post($lvl_post->post_id);
                // Use post_date_gmt to use GMT/UTC time of published date.
                $post_date = $post_details->post_date_gmt;
                // Get user level timestamp.
                $userlvltimestamps = wishlistmember_instance()->user_level_timestamps($wpm_current_user->ID, $lvl_post->mlevel);
                $userlvltimestamp  = $userlvltimestamps[ $lvl_post->mlevel ];
                $user_leveldate    = 0;
                if (! empty($userlvltimestamp)) {
                    // Use date_i18n and not wp_date so that we get UTC/GMT date of registration instead of localized date.
                    $user_leveldate = date_i18n('Y-m-d H:i:s', $userlvltimestamp);
                }

                // If set to "ondate" then let's use the specific date saved on the schedule as the base.
                // For when a user will have access to the scheduled content.
                if ('ondate' === $lvl_post->show_type) {
                    $newpostdate  = $lvl_post->show_on_date;
                    $post_diff    = $this->date_diff($date_today, $newpostdate, 86400);
                    $newpost_diff = $post_diff;

                    // Let's set the hide date to check how long the user will have access to it.
                    // If the hide date is set to 0, then the user will have access to it forever.
                    if ($lvl_post->on_date_hide_days) {
                        $hidedate  = $this->get_sched_date($newpostdate, $lvl_post->on_date_hide_days, 'Y-m-d H:i:s');
                        $post_diff = $this->date_diff($date_today, $hidedate, 86400);
                        $hide_diff = $post_diff;
                    }

                    $date2post = $lvl_post->show_on_date;
                } else {
                    // Get the post date diff and the level timestamp diff.
                    $post_diff  = $this->date_diff($post_date, $date_today, 86400);
                    $level_diff = $this->date_diff($user_leveldate, $date_today, 86400);

                    // Get the nearest lowest date diff... whichever the latest.
                    $date_diff = $post_diff < $level_diff ? $post_diff : $level_diff;
                    // Use the date of whoever has the lowest difference.
                    $date2post = $post_diff < $level_diff ? $post_date : $user_leveldate;

                    $newpostdate  = $this->get_sched_date($date2post, $lvl_post->num_days, 'Y-m-d H:i:s');
                    $post_diff    = $this->date_diff($date_today, $newpostdate, 86400);
                    $newpost_diff = $post_diff;

                    if ($lvl_post->hide_days) {
                        $hidedate  = $this->get_sched_date($newpostdate, $lvl_post->hide_days, 'Y-m-d H:i:s');
                        $post_diff = $this->date_diff($date_today, $hidedate, 86400);
                        $hide_diff = $post_diff;
                    }
                }
                if ($newpost_diff > 0 && ! array_key_exists($lvl_post->post_id, $has_access)) {
                    // Hide post if the calculated post date is greater than today.
                    if (array_key_exists($lvl_post->post_id, $sched_posts)) {
                        if ($sched_posts[ $lvl_post->post_id ]['newpost_diff'] > $newpost_diff) {
                            $sched_posts[ $lvl_post->post_id ] = [
                                'days'         => $lvl_post->num_days,
                                'date'         => $date2post,
                                'date_diff'    => $date_diff,
                                'new_date'     => $newpostdate,
                                'newpost_diff' => $newpost_diff,
                                'hidedate'     => $hidedate,
                                'hide_diff'    => $hide_diff,
                            ];
                        }
                    } else {
                        $sched_posts[ $lvl_post->post_id ] = [
                            'days'         => $lvl_post->num_days,
                            'date'         => $date2post,
                            'date_diff'    => $date_diff,
                            'new_date'     => $newpostdate,
                            'newpost_diff' => $newpost_diff,
                            'hidedate'     => $hidedate,
                            'hide_diff'    => $hide_diff,
                        ];
                    }
                } else {
                    // Show post if the calculated post date is less than today.
                    // And not yet hidden.
                    if (array_key_exists($lvl_post->post_id, $sched_posts)) {
                        if ($sched_posts[ $lvl_post->post_id ]['hide_diff'] >= 0 && $hide_diff >= 0) {
                            unset($sched_posts[ $lvl_post->post_id ]);
                        } elseif ($sched_posts[ $lvl_post->post_id ]['hide_diff'] > $hide_diff) {
                            $sched_posts[ $lvl_post->post_id ] = [
                                'days'         => $lvl_post->num_days,
                                'date'         => $date2post,
                                'date_diff'    => $date_diff,
                                'new_date'     => $newpostdate,
                                'newpost_diff' => $newpost_diff,
                                'hidedate'     => $hidedate,
                                'hide_diff'    => $hide_diff,
                            ];
                        }
                    } elseif ($hide_diff < 0) {
                        $sched_posts[ $lvl_post->post_id ] = [
                            'days'         => $lvl_post->num_days,
                            'date'         => $date2post,
                            'date_diff'    => $date_diff,
                            'new_date'     => $newpostdate,
                            'newpost_diff' => $newpost_diff,
                            'hidedate'     => $hidedate,
                            'hide_diff'    => $hide_diff,
                        ];
                    }

                    $has_access[ $lvl_post->post_id ] = 1;
                }
            } elseif (array_key_exists($lvl_post->post_id, $sched_posts)) {
                if ($sched_posts[ $lvl_post->post_id ]['days'] > $lvl_post->num_days) {
                    $sched_posts[ $lvl_post->post_id ] = [
                        'days'         => $lvl_post->num_days,
                        'date'         => $date2post,
                        'date_diff'    => $date_diff,
                        'new_date'     => $newpostdate,
                        'newpost_diff' => $newpost_diff,
                        'hidedate'     => $hidedate,
                        'hide_diff'    => $hide_diff,
                    ];
                }
            } else {
                $sched_posts[ $lvl_post->post_id ] = [
                    'days'         => $lvl_post->num_days,
                    'date'         => $date2post,
                    'date_diff'    => $date_diff,
                    'new_date'     => $newpostdate,
                    'newpost_diff' => $newpost_diff,
                    'hidedate'     => $hidedate,
                    'hide_diff'    => $hide_diff,
                ];
            }
        }

        // Used for WL Post Login by Erwin.
        if ($wlmpl_post_login) {
            if (wishlistmember_instance()->protect($wlmpl_post_login)) {
                unset($sched_posts[ $wlmpl_post_login ]);
            }
        }
        // End of WL Post Login Support.
        // Remove users pp post from the list.
        if (count($user_pp_posts) > 0) {
            foreach ((array) $user_pp_posts as $uppp) {
                if (isset($sched_posts[ $uppp ])) {
                    unset($sched_posts[ $uppp ]);
                }
            }
        }

        if ($this->debug) {
            echo '<!-- ';
            print_r($sched_posts);
            echo '-->';
        }
        return $sched_posts;
    }

    /**
     * Function to Save Post Sched
     *
     * @param integer/array $post_id         Post ID of the post to be saved.
     * @param integer       $mlevel          SKU of the Membership Level.
     * @param integer       $num_days        Number of days.
     * @param integer       $hide_days       Number of days before the post is shown to the user.
     * @param string        $sched_toggle    Either after/ondate. If empty then the default is after.
     * @param date          $show_on_date    If $sched_toggle is set to ondate then this is the date/time on when the post is shown to the user.
     * @param integer       $show_for_ondate If $sched_toggle is set to ondate then this is the number of days the post with be available for the user.
     */
    public function save_content_sched($post_id, $mlevel, $num_days, $hide_days, $sched_toggle = '', $show_on_date = '', $show_for_ondate = '')
    {
        global $wpdb;
        if ('ondate' === $sched_toggle) {
            $show_on_date = wlm_date('Y-m-d H:i:s', wlm_strtotime(wlm_trim($show_on_date) . ' ' . wlm_timezone_string()));
        } else {
            $sched_toggle = 'after';
        }

        if (is_array($post_id)) {
                $wpdb->query(
                    $wpdb->prepare(
                        'UPDATE ' . esc_sql($wpdb->prefix . 'wlcc_contentsched') . ' SET num_days=%s, hide_days=%s, show_on_date=%s, on_date_hide_days=%s, show_type=%s WHERE mlevel=%s AND post_id IN (' . implode(', ', array_fill(0, count($post_id), '%d')) . ')',
                        $num_days,
                        $hide_days,
                        $mlevel,
                        $show_for_ondate,
                        $show_on_date,
                        $sched_toggle,
                        ...array_values($post_id)
                    )
                );
        } elseif (count($this->get_content_sched($post_id, $mlevel, 0, 0, '', ['publish', 'draft', 'pending', 'private'])) > 0) {
            $wpdb->update(
                $wpdb->prefix . 'wlcc_contentsched',
                [
                    'num_days'          => $num_days,
                    'hide_days'         => $hide_days,
                    'show_on_date'      => $show_on_date,
                    'on_date_hide_days' => $show_for_ondate,
                    'show_type'         => $sched_toggle,
                ],
                [
                    'mlevel'  => $mlevel,
                    'post_id' => $post_id,
                ],
                ['%s', '%s', '%s', '%s', '%s'],
                ['%s', '%d']
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'wlcc_contentsched',
                [
                    'post_id'           => $post_id,
                    'mlevel'            => $mlevel,
                    'num_days'          => $num_days,
                    'hide_days'         => $hide_days,
                    'show_on_date'      => $show_on_date,
                    'on_date_hide_days' => $show_for_ondate,
                    'show_type'         => $sched_toggle,
                ]
            );
        }
    }

    /**
     * Get content schedule.
     *
     * @param  string  $post_id Post ID.
     * @param  string  $mlevel  Membership level.
     * @param  integer $start   Start index.
     * @param  integer $limit   Limit of results.
     * @param  string  $ptype   Post type.
     * @param  array   $pstatus Post status.
     * @return array Content schedule.
     */
    public function get_content_sched($post_id = '', $mlevel = '', $start = 0, $limit = 0, $ptype = '%', $pstatus = ['publish'])
    {
        global $wpdb;
        static $last_arguments = null;
        static $query_result   = null;

        // Return cached result if the request is the same.
        $current_arguments = wp_json_encode(func_get_args());
        if ($query_result && $last_arguments && $current_arguments === $last_arguments) {
            return $query_result;
        }
        $last_arguments = $current_arguments;

        $post_ids = (array) $post_id;
        $mlevels  = (array) $mlevel;
        $limit    = $limit < 1 ? [$start, PHP_INT_MAX] : [$start, $limit];
        $ptype    = wlm_or($ptype, '%');
        $pstatus  = (array) $pstatus;

        if ($post_id && $mlevel) {
            $query_result = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT sched.* FROM `' . esc_sql($wpdb->prefix . 'wlcc_contentsched') . "` as `sched` INNER JOIN `{$wpdb->posts}` as `p` ON `p`.`post_type` LIKE %s AND p.ID=sched.post_id AND `p`.`post_status` IN (" . implode(', ', array_fill(0, count($pstatus), '%s')) . ') AND sched.post_id IN (' . implode(', ', array_fill(0, count($post_ids), '%d')) . ') AND sched.mlevel IN (' . implode(', ', array_fill(0, count($mlevels), '%s')) . ') ORDER BY p.post_modified DESC LIMIT %d,%d',
                    $ptype,
                    ...array_values($pstatus),
                    ...array_values($post_ids),
                    ...array_values($mlevels),
                    ...array_values($limit)
                )
            );
        } elseif ($post_id) {
            $query_result = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT sched.* FROM `' . esc_sql($wpdb->prefix . 'wlcc_contentsched') . "` as `sched` INNER JOIN `{$wpdb->posts}` as `p` ON `p`.`post_type` LIKE %s AND p.ID=sched.post_id AND `p`.`post_status` IN (" . implode(', ', array_fill(0, count($pstatus), '%s')) . ') AND sched.post_id IN (' . implode(', ', array_fill(0, count($post_ids), '%d')) . ') ORDER BY p.post_modified DESC LIMIT %d,%d',
                    $ptype,
                    ...array_values($pstatus),
                    ...array_values($post_ids),
                    ...array_values($limit)
                )
            );
        } elseif ($mlevel) {
            $query_result = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT sched.* FROM `' . esc_sql($wpdb->prefix . 'wlcc_contentsched') . "` as `sched` INNER JOIN `{$wpdb->posts}` as `p` ON `p`.`post_type` LIKE %s AND p.ID=sched.post_id AND `p`.`post_status` IN (" . implode(', ', array_fill(0, count($pstatus), '%s')) . ') AND sched.mlevel IN (' . implode(', ', array_fill(0, count($mlevels), '%s')) . ') ORDER BY p.post_modified DESC LIMIT %d,%d',
                    $ptype,
                    ...array_values($pstatus),
                    ...array_values($mlevels),
                    ...array_values($limit)
                )
            );
        } else {
            $query_result = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT sched.* FROM `' . esc_sql($wpdb->prefix . 'wlcc_contentsched') . "` as `sched` INNER JOIN `{$wpdb->posts}` as `p` ON `p`.`post_type` LIKE %s AND p.ID=sched.post_id AND `p`.`post_status` IN (" . implode(', ', array_fill(0, count($pstatus), '%s')) . ') ORDER BY p.post_modified DESC LIMIT %d,%d',
                    $ptype,
                    ...array_values($pstatus),
                    ...array_values($limit)
                )
            );
        }

        return $query_result;
    }

    /**
     * Function to Delete Post Sched
     *
     * @param integer/array $post_ids Post ID of the post to be deleted.
     * @param integer       $mlevel   SKU of the Membership Level.
     */
    public function delete_content_sched($post_ids, $mlevel = '%')
    {
        global $wpdb;
        $mlevel   = wlm_or(wlm_trim($mlevel), '%');
        $post_ids = (array) $post_ids;
        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM `' . esc_sql($wpdb->prefix . 'wlcc_contentsched') . '` WHERE `mlevel` LIKE %s AND `post_id` IN (' . implode(', ', array_fill(0, count($post_ids), '%d')) . ')',
                $mlevel,
                ...array_values($post_ids)
            )
        );
    }

    /**
     * Function to Get Posts
     *
     * @param  string  $show_post  Show Post.
     * @param  string  $ptype      Post Type.
     * @param  string  $show_level Show Level.
     * @param  integer $start      Start.
     * @param  integer $per_page   Per Page.
     * @param  string  $sort       Sort.
     * @param  integer $asc        Ascending.
     * @return array Posts.
     */
    public function cc_getposts($show_post, $ptype, $show_level = '', $start = 0, $per_page = 0, $sort = 'ID', $asc = 1)
    {
        global $wpdb;
        $limit = $per_page < 1 ? [0, PHP_INT_MAX] : [$start, $per_page];
        $order = [$sort, $asc ? 'ASC' : 'DESC'];

        if ('all' === $show_post || empty($show_post)) {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT ID,post_author,post_status,post_date,post_modified,post_title,post_content FROM `$wpdb->posts` WHERE post_type=%s AND post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
                        $ptype,
                        ...array_values($order),
                        ...array_values($limit)
                    )
                );
        } elseif ('sched' === $show_post) {
            if (empty($show_level)) {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT DISTINCT t1.ID,t1.post_author,t1.post_status,t1.post_date,t1.post_modified,t1.post_title,t1.post_content FROM `$wpdb->posts` t1 INNER JOIN " . esc_sql($wpdb->prefix . 'wlcc_contentsched') . " t2 ON t1.ID=t2.post_id AND t1.post_type=%s AND post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
                        $ptype,
                        ...array_values($order),
                        ...array_values($limit)
                    )
                );
            } else {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT DISTINCT t1.ID,t1.post_author,t1.post_status,t1.post_date,t1.post_modified,t1.post_title,t1.post_content FROM `$wpdb->posts` t1 INNER JOIN " . esc_sql($wpdb->prefix . 'wlcc_contentsched') . " t2 ON t1.ID=t2.post_id AND t1.post_type=%s AND post_status='publish' AND t2.mlevel=%s ORDER BY %0s %0s LIMIT %d,%d",
                        $ptype,
                        $show_level,
                        ...array_values($order),
                        ...array_values($limit)
                    )
                );
            }
        } elseif ('protected' === $show_post) {
            // Get users protected post  for this level.
            // Get users unprotected content for this user.
            $wpm_levels     = wishlistmember_instance()->get_option('wpm_levels');
            $ids            = [];
            $has_all_access = false;
            // Check if the level has all access to post.
            if ($wpm_levels[ $show_level ]['allposts']) {
                $has_all_access = true;
            }
            if ($has_all_access) { // If the user has all access to posts.
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT ID,post_author,post_status,post_date,post_modified,post_title,post_content FROM `$wpdb->posts` WHERE post_type=%s AND post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
                        $ptype,
                        ...array_values($order),
                        ...array_values($limit)
                    )
                );
            } else {
                $x = wishlistmember_instance()->get_membership_content($ptype, $show_level);
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT ID,post_author,post_status,post_date,post_modified,post_title,post_content FROM `$wpdb->posts` WHERE post_type=%s AND post_status='publish' AND ID IN(" . implode(', ', array_fill(0, count($x), '%d')) . ') ORDER BY %0s %0s LIMIT %d,%d',
                        $ptype,
                        ...array_values($x),
                        ...array_values($order),
                        ...array_values($limit)
                    )
                );
            }
        }
    }

    /**
     * Modify the WHERE clause for scheduled content.
     *
     * @param  string $where The original WHERE clause.
     * @return string The modified WHERE clause.
     */
    public function wlm_sched_content_where($where)
    {
        global $wpdb;
        $wpm_current_user = wp_get_current_user();
        $table1           = $wpdb->prefix . 'posts';
        $w                = $where;
        $scheduler_hide_post_listing = !(wishlistmember_instance()->get_option('scheduler_show_post_listing') && !is_singular());
        if (! current_user_can('manage_options') && $scheduler_hide_post_listing) { // Disregard content schedule for admin.
            // Filter the post thats not to be shown.
            $arr         = $this->get_sched_content();
            $sched_posts = array_keys($arr);
            $qsched      = count($sched_posts) > 0 ? " AND $table1.ID NOT IN (" . implode(',', $sched_posts) . ')' : '';
            // Get permalink structure.
            $permalink_structure = get_option('permalink_structure');
            if (is_single() && preg_match('/year|month|day/i', $permalink_structure)) {
                // Remove the date in query.
                $w = trim(preg_replace('/\s+/', ' ', $w)); // Removes new line and extra whitespaces, it causes regex not to work properly.
                $x = preg_replace('/.*(YEAR|MONTH|DAYOFMONTH|HOUR|MINUTE|SECOND)(.*?)(\s+AND)/', '', $w);
                if ($x !== $w) {
                    $w = ' AND ' . $x;
                }
            }
            $w .= $qsched . ' ';
        }
        return $w;
    }

    /**
     * Modify the JOIN clause for scheduled content.
     *
     * @param  string $join The original JOIN clause.
     * @return string The modified JOIN clause.
     */
    public function wlm_sched_content_join($join)
    {
        global $wpdb;
        $wpm_current_user = wp_get_current_user();
        $wpm_levels       = wishlistmember_instance()->get_option('wpm_levels');
        $wpm_levels       = array_keys($wpm_levels);
        $table1           = $wpdb->prefix . 'posts';
        $table2           = $wpdb->prefix . 'wlcc_contentsched';
        $wpm_current_user = wp_get_current_user();
        $j                = $join;
        if (! wlm_arrval($wpm_current_user->caps, 'administrator')) {  // Disregard content schedule for admin.
            $levels = wishlistmember_instance()->get_member_active_levels($wpm_current_user->ID); // Get users membership levels.
            $x      = array_diff((array) $wpm_levels, (array) $levels);
            $qlevel = count($x) > 0 ? " AND ($table2.mlevel NOT IN ('" . implode('\',\'', $x) . "') OR $table2.mlevel IS NULL)" : '';
            $j     .= " LEFT JOIN $table2 ON  ($table1.ID=$table2.post_id $qlevel ) ";
        }
        return $j;
    }

    /**
     * Modify the ORDER BY clause for scheduled content.
     *
     * @param  string $order The original ORDER BY clause.
     * @return string The modified ORDER BY clause.
     */
    public function wlm_sched_content_order($order)
    {
        global $wpdb;
        $table1 = $wpdb->prefix . 'posts';
        $table2 = $wpdb->prefix . 'wlcc_contentsched';

        $wpm_current_user = wp_get_current_user();
        $o                = $order;
        if (! wlm_arrval($wpm_current_user->caps, 'administrator') && $wpm_current_user->ID > 0) {  // Disregard content schedule for admin and guest.
            $o = ' post_date DESC ';
        }
        return $o;
    }

    /**
     * Modify the GROUP BY clause for scheduled content.
     *
     * @param  string $group The original GROUP BY clause.
     * @return string The modified GROUP BY clause.
     */
    public function wlm_sched_content_group($group)
    {
        global $wpdb;
        $table1 = $wpdb->prefix . 'posts';
        $table2 = $wpdb->prefix . 'wlcc_contentsched';

        $wpm_current_user = wp_get_current_user();
        if (! wlm_arrval($wpm_current_user->caps, 'administrator')) {  // Disregard content schedule for admin.
                    $g = " $table1.ID ";
                    return $g;
        } else {
                    return $group;
        }
    }

    /**
     * Modify the WHERE clause for adjacent post.
     *
     * @param  string $where The original WHERE clause.
     * @return string The modified WHERE clause.
     */
    public function wlm_sched_adjacent_post_where($where)
    {
        global $post;
        $wpm_current_user = wp_get_current_user();
        static $sched_post;
        $w = $where;

        if (! wlm_arrval($wpm_current_user->caps, 'administrator')) {  // Disregard content schedule for admin.
            // Get the current post type, make sure its valid.
            $post_type = get_post_type();
            if (! $post_type) {
                return $w;
            }

            // Retrieve the scheduled post on this post type.
            if (! isset($sched_post[ $post_type ])) {
                $sched_post[ $post_type ] = $this->get_sched_content($post_type);
            }
            // Dont continue if no scheduled post.
            if (! is_array($sched_post[ $post_type ]) || count($sched_post[ $post_type ]) <= 0) {
                return $w;
            }

            // Get the IDs of scheduled post.
            // Make sure not to include it when getting the adjacent post.
            $sched_ids = array_keys($sched_post[ $post_type ]);
            $sched_ids = implode(',', $sched_ids);
            $w        .= "AND ( p.ID NOT IN({$sched_ids}) )";
        }
        return $w;
    }


    /**
     * Modify the list of posts and pages.
     *
     * @param  array $posts The original list of posts and pages.
     * @return array The modified list of posts and pages.
     */
    public function posts_pages_list($posts)
    {
        // Add moments.js for processing scheduled-content shortcode inside a widget.
        wp_enqueue_script('wlm_moment', wishlistmember_instance()->plugin_url3 . '/assets/js/moment.min.js', ['jquery'], WLM_PLUGIN_VERSION);

        $wpm_current_user = wp_get_current_user();
        // This part is important so that new post_date will be used by the post when displaying.
        if (! wlm_arrval($wpm_current_user->caps, 'administrator') && $wpm_current_user->ID > 0) {  // Disregard content schedule for admin and non users.
            foreach ((array) $posts as $key => $post) {
                if (isset($posts[ $key ]->new_postdate)) {
                    $posts[ $key ]->post_date = $posts[ $key ]->new_postdate;
                }
            }
        }
        return $posts;
    }


    /**
     * Modify the content of a post.
     *
     * @param  string $content The original content.
     * @return string The modified content.
     */
    public function sched_the_content($content)
    {
        $wpm_current_user = wp_get_current_user();
            // Js functions.
            $js_show_gmt = '<script type="text/javascript">
															function tag_showdateGMT(unixtime){
																	var currentDate=new Date(unixtime);
																	var day = currentDate.getDate();
																	var months = currentDate.getMonth()+1;
																	var year = currentDate.getFullYear();
																	if (day < 10){ day = "0" +day;}
																	if (months < 10){ months = "0" +months;}
																	var new_date = months +"/" +day +"/" +year;
																	document.write(new_date);
															}
															function tag_showtimeGMT(unixtime){
																	var currentDate=new Date(unixtime);
																	var hours = currentDate.getHours();
																	var minutes = currentDate.getMinutes();
																	if(hours > 12){
																		hours = hours - 12;
																		add = " p.m.";
																	}else{
																		hours = hours;
																		add = " a.m.";
																	}
																	if(hours == 12){ add = " p.m.";}
																	if(hours == 00) {hours = "12";}
																	if (minutes < 10){ minutes = "0" +minutes;}
																	var new_time = hours +":" +minutes +" " +add;
																	document.write(new_time);
															}
													</script>';
        if (preg_match_all('/\[content-scheduler.*?\]/', $content, $matches)) {
            if (( is_single() || is_page() ) && $wpm_current_user->ID) {
                $content = $js_show_gmt . $content;
            }
            foreach ($matches[0] as $key => $match) {
                $torem          = ['content-scheduler', '[', ']'];
                $str            = str_replace($torem, '', $match); // Remove content-scheduler.
                $tag_params     = explode(',', $str);
                $new_tag_params = [];
                foreach ($tag_params as $key => $param_value) {
                    $x                                   = explode('=', $param_value);
                    $new_tag_params[ wlm_trim($x[0]) ] = wlm_trim($x[1]);
                }
                if (is_single() || is_page()) {
                    $content = str_replace($match, $this->create_sched_tag_content($new_tag_params), $content);
                } else {
                    $content = str_replace($match, '', $content);
                }
            }
        }
        return $content;
    }


    /**
     * Create the content for the scheduled tag.
     *
     * @param  array $tag_params The tag parameters.
     * @return string The content for the scheduled tag.
     */
    public function create_sched_tag_content($tag_params)
    {
        $wpm_current_user = wp_get_current_user();
        $custom_types     = get_post_types(
            [
                'public'   => true,
                '_builtin' => false,
            ]
        );
        $post_type        = array_merge(['post', 'page'], $custom_types);
        $ptype            = in_array($tag_params['ptype'], $post_type, true) ? $tag_params['ptype'] : '';
        $sched_posts      = $this->get_sched_content($ptype);
        $ret              = '';
        $sortable         = ['ID', 'date', 'days', 'title', 'new_date', 'menu_order'];
        // Sort and filter(for protected posts) the post to show.
        foreach ($sched_posts as $key => $value) {
            if ('' !== $value['date'] && $value['hide_diff'] >= 0) {
                $x[ $key ] = [
                    'ID'       => $key,
                    'date'     => $value['date'],
                    'days'     => $value['days'],
                    'new_date' => $value['new_date'],
                ];
            }
        }
        if ($wpm_current_user->ID && count($x) > 0) {
            $title      = isset($tag_params['title']) ? $tag_params['title'] : '';
            $sort       = isset($tag_params['sort']) ? $tag_params['sort'] : 'new_date';
            $sort       = in_array($sort, $sortable, true) ? $sort : 'new_date';
            $px         = '' === $tag_params['px'] ? 4 : $tag_params['px'];
            $date_today = wlm_date('Y-m-d H:i:s'); // Get date today.
            if (! empty($title)) {
                $ret  = '<div class="wlcccs-tag-holder">';
                $ret .= '<p>' . $title . '</p>';
            }
            $ctr = $tag_params['showposts'];
            if (! is_numeric($ctr)) {
                $ctr = 10;
            }
            if (! $ctr) {
                $ctr = 10000000000;
            }

            foreach ($x as $key => $value) {
                if (! $ctr) {
                    break;
                }
                $post_details = get_post($value['ID']);
                if (isset($post_details->post_title) && '' !== wlm_trim($post_details->post_title)) { // Dont include posts with no title.
                    $value['title']      = $post_details->post_title;
                    $value['menu_order'] = $post_details->menu_order;
                }
                $x[ $key ] = (object) $value;
                --$ctr;
            }

            if (count($x) < 1) {
                $ret        .= 'None';
                $sched_posts = $this->subval_sort($x, $sort, false, false);
            } else {
                $sched_posts = $this->subval_sort($x, $sort, false, false);
                $ret        .= '<ul class="wlcccs-tag-ul">';
            }

            // End sorting.
            $hide_post_date      = '' === $tag_params['showdate'] ? 'yes' : $tag_params['showdate'];
            $hide_post_time      = '' === $tag_params['showtime'] ? 'no' : $tag_params['showtime'];
            $date_time_separator = '' === $tag_params['separator'] ? ' @ ' : ( ' ' . $tag_params['separator'] . ' ' );
            foreach ($sched_posts as $key => $value) {
                if (is_array($value) && count($value) > 0) {
                    $ret .= '<li class="wlm-sched-widget-post-title" style="margin-bottom:' . $px . 'px;"><span class="wlm-sched-widget-post-title">' . $value['title'] . '</span>';
                    if ('yes' === $hide_post_date) {
                        $ret .= ' on <span class="wlm-sched-widget-post-date"><script type="text/javascript">tag_showdateGMT(' . $this->get_sched_date($value['date'], $value['days']) . '000);</script></span>';
                        if ('yes' === $hide_post_time) {
                            $ret .= $date_time_separator . '<span class="wlm-sched-widget-post-time"><script type="text/javascript">tag_showtimeGMT(' . $this->get_sched_date($value['date'], $value['days']) . '000);</script></span>';
                        }
                    }
                    $ret .= '</li>';
                }
            }
            if (count($x) > 0) {
                $ret .= '</ul>';
            }
            if (! empty($title)) {
                $ret .= '</div>';
            }
        }
        return $ret;
    }

    /**
     * Process the shortcode.
     *
     * @param  array $atts The shortcode attributes.
     * @return string The processed shortcode output.
     */
    public function process_shortcode($atts)
    {
        $params = shortcode_atts(
            [
                'ptype'     => '',
                'title'     => '',
                'showpost'  => '10',
                'px'        => '4',
                'separator' => '@',
                'sort'      => 'title',
                'showdate'  => 'no',
                'showtime'  => 'no',
            ],
            $atts
        );

        // Add moments.js.
        wp_enqueue_script('wlm_moment', wishlistmember_instance()->plugin_url3 . '/assets/js/moment.min.js', ['jquery'], WLM_PLUGIN_VERSION);

        $wpm_current_user = wp_get_current_user();
        $custom_types     = get_post_types(['_builtin' => false]);
        $post_type        = array_merge(['post', 'page'], $custom_types);
        $ptype            = in_array($params['ptype'], $post_type, true) ? $params['ptype'] : '';
        $sched_posts      = $this->get_sched_content($ptype);
        $ret              = '';
        $sortable         = ['ID', 'date', 'days', 'title', 'new_date', 'menu_order'];
        $x                = [];

        // Sort and filter(for protected posts) the post to show.
        foreach ($sched_posts as $key => $value) {
            if ('' !== $value['date'] && $value['hide_diff'] >= 0) {
                $x[ $key ] = [
                    'ID'       => $key,
                    'date'     => $value['date'],
                    'days'     => $value['days'],
                    'new_date' => $value['new_date'],
                ];
            }
        }

        if ($wpm_current_user->ID && count($x) > 0) {
            $title = isset($params['title']) ? $params['title'] : '';
            $sort  = isset($params['sort']) ? $params['sort'] : 'new_date';
            $sort  = in_array($sort, $sortable, true) ? $sort : 'new_date';
            $px    = '' === $params['px'] ? 4 : $params['px'];
            if (! empty($title)) {
                $ret  = '<div class="wlcccs-tag-holder">';
                $ret .= '<p>' . $title . '</p>';
            }
            $ctr = $params['showpost'];
            if (! is_numeric($ctr)) {
                $ctr = 10;
            }
            if (! $ctr) {
                $ctr = 10000000000;
            }

            foreach ($x as $key => $value) {
                if (! $ctr) {
                    break;
                }
                $post_details = get_post($value['ID']);
                if (isset($post_details->post_title) && '' !== wlm_trim($post_details->post_title)) { // Dont include posts with no title.
                    $value['title']      = $post_details->post_title;
                    $value['menu_order'] = $post_details->menu_order;
                }
                $x[ $key ] = (object) $value;
            }

            if (count($x) < 1) {
                $ret        .= 'None';
                $sched_posts = $this->subval_sort($x, $sort, false, false);
            } else {
                $sched_posts = $this->subval_sort($x, $sort, false, false);
                $ret        .= '<ul class="wlcccs-tag-ul">';
            }

            // Splice by the number of showpost after sorting instead of before sorting.
            array_splice($sched_posts, $ctr);

            // End sorting.
            $hide_post_date      = '' === $params['showdate'] ? 'yes' : $params['showdate'];
            $hide_post_time      = '' === $params['showtime'] ? 'no' : $params['showtime'];
            $date_time_separator = '' === $params['separator'] ? ' @ ' : ( ' ' . $params['separator'] . ' ' );

            foreach ($sched_posts as $key => $value) {
                if ($value && $value->title) {
                    $ret .= '<li class="wlm-sched-widget-post-title" style="margin-bottom:' . $px . 'px;"><span class="wlm-sched-widget-post-title">' . $value->title . '</span>';
                    if ('yes' === $hide_post_date) {
                        $ret .= ' on <span class="wlm-sched-widget-post-date"><script type="text/javascript">tag_showdateGMT(' . $this->get_sched_date($value->date, $value->days) . '000);</script></span>';
                        if ('yes' === $hide_post_time) {
                            $ret .= $date_time_separator . '<span class="wlm-sched-widget-post-time"><script type="text/javascript">tag_showtimeGMT(' . $this->get_sched_date($value->date, $value->days) . '000);</script></span>';
                        }
                    }
                    $ret .= '</li>';
                }
            }
            if (count($x) > 0) {
                $ret .= '</ul>';
            }
            if (! empty($title)) {
                $ret .= '</div>';
            }
        }

        $js_show_gmt = '<script type="text/javascript">
													function tag_showdateGMT(unixtime){
															// Get WordPress date format.
															var date_format = "' . wishlistmember_instance()->js_date_format . '";

															var formatted_date = moment(unixtime).format(date_format);

															document.write(formatted_date);
													}
													function tag_showtimeGMT(unixtime){
															// Get WordPress time format.
															var time_format = "' . wishlistmember_instance()->js_time_format . '";
															var formatted_time = moment(unixtime).format(time_format);
															document.write(formatted_time);
													}
											</script>';
        $ret         = $ret ? $js_show_gmt . $ret : '';
        return $ret;
    }

    /**
     * Filter the input for the my_posts_clause filter.
     *
     * @param  string $input The input string.
     * @return string The filtered input string.
     */
    public function my_posts_clause_filter($input)
    {
        global $wpdb;
        $table1           = $wpdb->prefix . 'posts';
        $table2           = $wpdb->prefix . 'wlcc_contentsched';
        $wpm_current_user = wp_get_current_user();

        if (! wlm_arrval($wpm_current_user->caps, 'administrator')) {  // Disregard content schedule for admin.
            // Get user level timestamp.
            $levels = wishlistmember_instance()->get_member_active_levels($wpm_current_user->ID); // Get users membership levels.
            // Remove payper post membership level.
            foreach ((array) $levels as $id => $level) {
                if (false !== strpos($level, 'U')) {
                    unset($levels[ $id ]);
                }
            }
            // Get user level registration dates.
            $userlvltimestamps = wishlistmember_instance()->user_level_timestamps($wpm_current_user->ID);
            // Inject our field query.
            // Generate fields with case statement for the post date.
            $case_lvl_date[] = "{$table2}.mlevel IS NULL then '" . wlm_date('Y-m-d H:i:s') . "'";
            foreach ($levels as $ind => $lvl) {
                $userlvltimestamp = $userlvltimestamps[ $lvl ];
                if (! empty($userlvltimestamp)) {
                    $case_lvl_date[] = "{$table2}.mlevel = '{$lvl}' then '" . wlm_date('Y-m-d H:i:s', $userlvltimestamp) . "'";
                }
            }
            $case_lvl_date = implode(' WHEN ', $case_lvl_date);
            $case_lvl_date = "CASE WHEN {$case_lvl_date} ELSE '" . wlm_date('Y-m-d H:i:s') . "' END";
            $fields        = "{$input['fields']},MIN(date_add(IF(IFNULL($table2.num_days,0) > 0,if($table1.post_date < {$case_lvl_date},{$case_lvl_date},$table1.post_date),$table1.post_date), INTERVAL IFNULL($table2.num_days,0) DAY)) as new_postdate";

            if (strripos($input['join'], 'wlcc_contentsched')) {
                $input['fields'] = $fields;
                // Order the post by our  new post date field.
                $input['orderby'] = str_replace("{$table1}.post_date", 'new_postdate', $input['orderby']);
                $input['orderby'] = str_replace('post_date', 'new_postdate', $input['orderby']);
            }
        }
        return $input;
    }


    /**
     * Debugs the query.
     *
     * @param mixed $query The query to debug.
     */
    public function debug_query($query)
    {
        echo '<!-- ';
        print_r($query);
        echo '-->';
        return $query; // If not debugging,lets just return the query.
    }


    /**
     * Filter the terms based on the given taxonomies and arguments.
     *
     * @param  array $terms      The terms to filter.
     * @param  array $taxonomies The taxonomies to filter by.
     * @param  array $args       The arguments for filtering.
     * @return array The filtered terms.
     */
    public function sched_term_filter($terms, $taxonomies, $args)
    {
        global $wpdb;
        if (is_admin()) {
            return $terms;
        }
        $p = $this->get_sched_content();
        if (! $p) {
            return $terms;
        }
        $p = array_keys($p);
        // Lets get the terms with posts.
        $res = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT term_taxonomy_id,COUNT(object_id) as obj FROM `{$wpdb->term_relationships}` WHERE object_id NOT IN (" . implode(', ', array_fill(0, count($p), '%d')) . ') GROUP BY term_taxonomy_id',
                ...array_values($p)
            )
        );
        $not_empty_terms = [];
        foreach ($res as $t) {
            $not_empty_terms[ $t->term_taxonomy_id ] = $t->obj;
        }
        foreach ($terms as $key => $term) {
            if (is_object($term) && array_key_exists($term->term_id, $not_empty_terms)) {
                $terms[ $key ]->count = $not_empty_terms[ $term->term_id ];
            } elseif ($args['hide_empty']) {
                unset($terms[ $key ]);
            } elseif (is_object($terms[ $key ])) {
                $terms[ $key ]->count = 0;
            }
        }
        return $terms;
    }


    /**
     * The preget post function.
     *
     * @param WP_Query $query The query object.
     */
    public function the_preget_post($query)
    {
        global $wpdb;
        require_once ABSPATH . WPINC . '/pluggable.php';
        $is_single     = is_single() || is_page() ? true : false;
        $pid           = false;
        $name          = false;
        $user_is_admin = current_user_can('manage_options');
        if ($is_single && ! $user_is_admin) {
            if (is_page()) {
                $pid  = isset($query->query['page_id']) ? $query->query['page_id'] : false;
                $name = ! $pid && isset($query->query['pagename']) ? $query->query['pagename'] : '';
            } elseif (is_single()) {
                $pid  = isset($query->query['p']) ? $query->query['p'] : false;
                $name = isset($query->query['name']) ? $query->query['name'] : '';
            }
            $name_array = explode('/', $name);
            $name       = array_slice($name_array, -1, 1); // Get the last element.
            $name       = $name[0];
            if ($name && ! $pid) {
                if ($query->queried_object && $query->queried_object->post_parent) {
                    $pid = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT ID FROM `$wpdb->posts` WHERE post_name=%s AND post_parent=%d AND post_status = 'publish'",
                            $name,
                            $query->queried_object->post_parent
                        )
                    );
                } else {
                    $pid = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT ID FROM `$wpdb->posts` WHERE post_name=%s AND post_status = 'publish'",
                            $name
                        )
                    );
                }
            } else {
                return $query;
            }

            if ($pid) {
                $sched_content = $this->get_sched_content();
                if (isset($sched_content[ $pid ])) {
                    $wlcc_sched_error_page = wishlistmember_instance()->get_option('scheduler_error_page_type');
                    $wlcc_sched_error_page = $wlcc_sched_error_page ? $wlcc_sched_error_page : get_option('wlcc_sched_error_page');
                    $wlcc_sched_error_page = $wlcc_sched_error_page ? $wlcc_sched_error_page : 'text';

                    if ('url' === $wlcc_sched_error_page) {
                        $wlcc_sched_error_page_url = wishlistmember_instance()->get_option('scheduler_error_page_url');
                        $wlcc_sched_error_page_url = $wlcc_sched_error_page_url ? $wlcc_sched_error_page_url : get_option('wlcc_sched_error_page_url');

                        if (! empty($wlcc_sched_error_page_url)) {
                            $url   = wlm_trim($wlcc_sched_error_page_url);
                            $p_url = parse_url($url);
                            if (! isset($p_url['scheme'])) {
                                $url = 'http://' . $url;
                            }
                        }
                    } elseif ('internal' === $wlcc_sched_error_page) {
                        $wlcc_sched_error_page = wishlistmember_instance()->get_option('scheduler_error_page_internal');
                        if (! $wlcc_sched_error_page) {
                            $wlcc_sched_error_page = $wlcc_sched_error_page && 'url' !== $wlcc_sched_error_page && 'internal' !== $wlcc_sched_error_page && 'text' !== $wlcc_sched_error_page ? $wlcc_sched_error_page : false;
                        }
                        $r_pid = (int) $wlcc_sched_error_page;
                        if (is_int($r_pid) && $r_pid > 0 && ! isset($sched_content[ $r_pid ])) {
                            $url = get_permalink($r_pid);
                        }
                    } else {
                        $url = add_query_arg('sp', 'scheduler_error_page', wishlistmember_instance()->magic_page());
                        // If not set, save the default.
                        $pages_text = wishlistmember_instance()->get_option('scheduler_error_page_text');
                        if (! $pages_text) {
                            $f = wishlistmember_instance()->legacy_wlm_dir . '/resources/page_templates/scheduler_internal.php';
                            if (file_exists($f)) {
                                include $f;
                            }
                            $pages_text = $content ? nl2br($content) : '';
                            // Lets save it.
                            if ($pages_text) {
                                wishlistmember_instance()->save_option('scheduler_error_page_text', $pages_text);
                                wishlistmember_instance()->save_option('scheduler_error_page_type', 'text');
                            }
                        }
                    }
                    if (! $url) {
                        $url = add_query_arg('sp', 'scheduler_error_page', wishlistmember_instance()->magic_page());
                    }
                    wp_safe_redirect($url);
                    exit(0);
                }
            }
        }

        // Set split_the_query as false before returning the query to make sure it won't cause any 404 error for sites using Object Cache.
        add_filter('split_the_query', '__return_false');

        return $query;
    }


    /**
     * Retrieve the filtered list of pages.
     *
     * @param  array $pages The original list of pages.
     * @param  array $args  The arguments for filtering the pages.
     * @return array The filtered list of pages.
     */
    public function arc_getpages($pages, $args)
    {
        if (count((array) $pages) <= 0) {
            return $pages;
        }
        $wpm_current_user = wp_get_current_user();
        if (! wlm_arrval($wpm_current_user->caps, 'administrator')) { // Disregard content schedule for admin and if there is pages.
            $sched_posts = $this->get_sched_content($args['post_type']);
            if (count($sched_posts) > 0) {
                $sched_post_ids = array_keys($sched_posts);
                foreach ($pages as $pid => $page) {
                    if (in_array((int)$page->ID, array_map('intval', $sched_post_ids), true)) {
                        unset($pages[ $pid ]);
                    }
                }
            }
        }
        return $pages;
    }


    /**
     * Retrieve the filtered list of navigation menu items.
     *
     * @param  array $items The original list of navigation menu items.
     * @return array The filtered list of navigation menu items.
     */
    public function wp_get_nav_menu_items($items)
    {
        if (count((array) $items) <= 0) {
            return $items;
        }
        $wpm_current_user = wp_get_current_user();
        if (! wlm_arrval($wpm_current_user->caps, 'administrator')) { // Disregard content schedule for admin.
            $sched_posts = $this->get_sched_content();
            if (count($sched_posts) > 0) {
                $sched_post_ids = array_keys($sched_posts);
                foreach ($items as $pid => $item) {
                    // Only filter out post types.
                    if ('post_type' === $item->type && in_array((int)$item->object_id, array_map('intval', $sched_post_ids), true)) {
                        unset($items[ $pid ]);
                    }
                }
            }
        }
        return $items;
    }

    /**
     * Schedule widget.
     *
     * @param array $args The widget arguments.
     */
    public function sched_widget($args)
    {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract.
        extract($args);
        $wpm_current_user = wp_get_current_user();
        $ptype            = '' === wishlistmember_instance()->get_option('wlm_sched_widget_ptype') ? '' : wishlistmember_instance()->get_option('wlm_sched_widget_ptype');
        $ptype            = 'all' === $ptype ? '' : $ptype;
        $sched_posts      = $this->get_sched_content($ptype);
        $x                = [];
        // Sort and filter(for protected posts) the post to show.
        foreach ($sched_posts as $key => $value) {
            if ('' !== $value['date'] && $value['hide_diff'] >= 0) {
                $sched_posts[ $key ] = [
                    'ID'       => $key,
                    'date'     => $value['date'],
                    'days'     => $value['days'],
                    'new_date' => $value['new_date'],
                ];
                $x[ $key ]           = (object) $sched_posts[ $key ];
            }
        }

        if (1 !== wishlistmember_instance()->get_option('widget_nologinbox') && $wpm_current_user->ID && count($x) > 0) {
            $title      = '' === wishlistmember_instance()->get_option('wlm_sched_widget_title') ? 'Upcoming Posts' : wishlistmember_instance()->get_option('wlm_sched_widget_title');
            $px         = '' === wishlistmember_instance()->get_option('wlm_sched_widget_px') ? 4 : wishlistmember_instance()->get_option('wlm_sched_widget_px');
            $date_today = wlm_date('Y-m-d H:i:s'); // Get date today.
            echo wp_kses_post($before_widget . $before_title);
            echo esc_html($title);
            echo wp_kses_post($after_title);
            // Js functions.
            echo '<script type="text/javascript">
															function showGMT(unixtime){
																	var currentDate=new Date(unixtime);
																	var hours = currentDate.getHours();
																	var minutes = currentDate.getMinutes();
																	var day = currentDate.getDate();
																	var months = currentDate.getMonth()+1;
																	var year = currentDate.getFullYear();
																	if(hours > 12){
																		hours = hours - 12;
																		add = " p.m.";
																	}else{
																		hours = hours;
																		add = " a.m.";
																	}
																	if(hours == 12){ add = " p.m.";}
																	if(hours == 00) {hours = "12";}
																	if (minutes < 10){ minutes = "0" +minutes;}
																	if (day < 10){ day = "0" +day;}
																	if (months < 10){ months = "0" +months;}
																	var new_date = months +"/" +day +"/" +year +" @ " +hours +":" +minutes +" " +add;
																	document.write(new_date);
															}
													</script>';
            $ctr = wishlistmember_instance()->get_option('wlm_sched_widget_count');
            if (! is_numeric($ctr)) {
                $ctr = 10;
            }
            if (! $ctr) {
                $ctr = 10000000000;
            }

            if (count($x) < 1) {
                echo 'None';
                $sched_posts = $this->subval_sort($x, 'new_date', false, false);
            } else {
                $sched_posts = $this->subval_sort($x, 'new_date', false, false);
                echo '<ul class="wlm-sched-widget-content">';
            }
            // End sorting.
            $hide_post_time = wishlistmember_instance()->get_option('wlm_sched_hide_post_time');
            foreach ($sched_posts as $key => $value) {
                if (! $ctr) {
                    break;
                }
                if (count((array) $value) > 0) {
                    $post_details = get_post($value->ID);
                    if ('' !== wlm_trim($post_details->post_title)) { // Dont include posts with no title.
                        echo '<li class="wlm-sched-widget-post-title" style="margin-bottom:' . (int) $px . 'px;"><span class="wlm-sched-widget-post-title">' . esc_html($post_details->post_title) . '</span>';
                        if (! $hide_post_time) {
                            echo ' on <br /><span class="wlm-sched-widget-post-date">'
                                    . '<script type="text/javascript">showGMT(' . (int) $this->get_sched_date($value->date, $value->days) . '000);</script></span>';
                        }
                        echo '</li>';
                    }
                }
                --$ctr;
            }
            echo wp_kses_post($after_widget);
        }
    }

    /**
     * The admin widget.
     */
    public function sched_widget_admin()
    {
        $custom_types = get_post_types(
            [
                'public'   => true,
                '_builtin' => false,
            ],
            'objects'
        );
        $post_types   = [
            'page' => 'Pages',
            'post' => 'Posts',
        ];
        foreach ($custom_types as $t => $ctype) {
            $post_types[ $t ] = $ctype->labels->name;
        }

        $title             = wishlistmember_instance()->get_option('wlm_sched_widget_title');
        $px                = wishlistmember_instance()->get_option('wlm_sched_widget_px');
        $hide_post_time    = wishlistmember_instance()->get_option('wlm_sched_hide_post_time');
        $sched_posts_count = wishlistmember_instance()->get_option('wlm_sched_widget_count');
        $sched_ptype       = wishlistmember_instance()->get_option('wlm_sched_widget_ptype');
        $sched_ptype       = ! $sched_ptype ? 'all' : $sched_ptype;
        echo '<p><label for="wlm-sched-widget">' . esc_html__('Widget Title:', 'wishlist-member') . ' <input type="text" value="' . esc_attr($title) . '" name="wlm_sched_widget_title" id="wlm-sched-widget-title" class="widefat" /></label></p>';
        echo '<p><label for="wlm-sched-widget">' . esc_html__('List Spacing in Pixels:', 'wishlist-member') . ' <input type="text" value="' . esc_attr($px) . '" name="wlm_sched_widget_px" id="wlm-sched-widget-px" class="widefat" /></label></p>';
        $checked_yes = $hide_post_time ? '' : 'checked';
        $checked_no  = $hide_post_time ? 'checked' : '';
        echo '<p><label for="wlm-sched-widget">' . esc_html__('Display Time of Post:', 'wishlist-member') . '</label> &nbsp;
									<label><input type="radio" value="0" name="wlm_sched_hide_post_time" id="wlm-display-time-post-yes" ' . esc_attr($checked_yes) . '/> Yes</label>
									<label><input type="radio" value="1" name="wlm_sched_hide_post_time" id="wlm-display-time-post-no" ' . esc_attr($checked_no) . '/> No</label>
									</p>';
        echo '<p><label for="wlm-sched-widget">' . esc_html__('How Many Schedule Posts to Display:', 'wishlist-member') . ' <input type="text" value="' . esc_attr($sched_posts_count) . '" name="wlm_sched_widget_count" id="wlm-sched-widget-count" class="widefat" /></label></p>';

        $ptype_all_selected = 'all' === $sched_ptype ? 'selected' : '';
        echo '<p><label for="wlm-sched-ptype">' . esc_html__('Show Post/Page:', 'wishlist-member') . '</label> &nbsp;
											<select name="wlm_sched_widget_ptype" id="wlm-sched-ptype">
													<option value="all" ' . esc_attr($ptype_all_selected) . '>Show All</option>';
        foreach ($post_types as $i => $ptype) {
            $selected = $sched_ptype === $i ? 'selected' : '';
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($i),
                esc_attr($selected),
                esc_html(
                    sprintf(
                        // Translators: %s Post type.
                        __('%s Only', 'wishlist-member'),
                        $ptype
                    )
                )
            );
        }
        echo '</select>
									</p>';
        if (isset(wlm_post_data()['wlm_sched_widget_title'])) {
            if (! trim(wlm_post_data()['wlm_sched_widget_title'])) {
                wlm_post_data()['wlm_sched_widget_title'] = __('Upcoming Posts', 'wishlist-member');
            }
            wishlistmember_instance()->save_option('wlm_sched_widget_title', wlm_post_data()['wlm_sched_widget_title']);
        }
        if (isset(wlm_post_data()['wlm_sched_widget_px'])) {
            if (! is_numeric(wlm_post_data()['wlm_sched_widget_px'])) {
                wlm_post_data()['wlm_sched_widget_px'] = __('10', 'wishlist-member');
            }
            wishlistmember_instance()->save_option('wlm_sched_widget_px', wlm_post_data()['wlm_sched_widget_px']);
        }
        if (isset(wlm_post_data()['wlm_sched_widget_px'])) {
            if (! is_numeric(wlm_post_data()['wlm_sched_widget_px'])) {
                wlm_post_data()['wlm_sched_widget_px'] = __('10', 'wishlist-member');
            }
            wishlistmember_instance()->save_option('wlm_sched_widget_px', wlm_post_data()['wlm_sched_widget_px']);
        }
        if (isset(wlm_post_data()['wlm_sched_hide_post_time'])) {
            wishlistmember_instance()->save_option('wlm_sched_hide_post_time', wlm_post_data()['wlm_sched_hide_post_time']);
        }
        if (isset(wlm_post_data()['wlm_sched_widget_count'])) {
            if (! is_numeric(wlm_post_data()['wlm_sched_widget_count'])) {
                wlm_post_data()['wlm_sched_widget_count'] = __('10', 'wishlist-member');
            }
            wishlistmember_instance()->save_option('wlm_sched_widget_count', wlm_post_data()['wlm_sched_widget_count']);
        }
        if (isset(wlm_post_data()['wlm_sched_widget_ptype'])) {
            if (! trim(wlm_post_data()['wlm_sched_widget_ptype'])) {
                wlm_post_data()['wlm_sched_widget_ptype'] = __('all', 'wishlist-member');
            }
            wishlistmember_instance()->save_option('wlm_sched_widget_ptype', wlm_post_data()['wlm_sched_widget_ptype']);
        }
    }


    /**
     * Sorts a multidimensional array by a specified subkey.
     *
     * @param  array   $a      The array to be sorted.
     * @param  string  $subkey The subkey to sort by.
     * @param  boolean $sort   Whether to perform the sorting or not.
     * @param  boolean $asc    Whether to sort in ascending order or not.
     * @return array The sorted array.
     */
    public function subval_sort($a, $subkey, $sort = true, $asc = true)
    {
        // Sort the multidimensional array by key.
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
        }
            return $c;
    }

    /**
     * Get the scheduled date based on the post date and number of days.
     *
     * @param  string  $post_date The post date.
     * @param  integer $days      The number of days.
     * @param  string  $format    The date format (optional).
     * @return integer|string       The scheduled date.
     */
    public function get_sched_date($post_date, $days, $format = '')
    {
        if (empty($format)) {
            $pdate = gmdate('Y-m-d H:i:s', wlm_strtotime($post_date));
            $d1    = date_parse($pdate);
            $pdate = gmmktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], $d1['year']);
            $date  = $pdate + ( $days * 86400 );
        } else {
            $d1    = date_parse($post_date);
            $pdate = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], $d1['year']);
            $date  = wlm_date($format, $pdate + ( $days * 86400 ));
        }
        return $date;
    }


    /**
     * Calculate the difference between two dates.
     *
     * @param  string  $start   The start date.
     * @param  string  $end     The end date.
     * @param  integer $divisor The divisor (optional).
     * @return float The difference between the dates.
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
}
