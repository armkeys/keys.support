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
class Content_Manager
{
    /**
     * Constructor
     */
    public function load_hooks()
    {
        add_action('init', [&$this, 'apply_due_date']);
        add_action('wishlistmember_post_page_options_menu', [&$this, 'wlm3_post_options_menu']);
        add_action('wishlistmember_post_page_options_content', [&$this, 'content_manager_options']);
    }

    /**
     * Display the post options menu.
     */
    public function wlm3_post_options_menu()
    {
        echo '<li><a href="#" data-target=".wlm-inside-manager" class="wlm-inside-toggle">' . esc_html__('Manager', 'wishlist-member') . '</a></li>';
    }

    /**
     * Display the post options content.
     */
    public function content_manager_options()
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

        $support_categories = 'post' === $post_type ? true : false;
        if ('post' !== $post_type && 'page' !== $post_type) {
            $p = get_post_type_object($post_type);
            if (in_array('category', $p->taxonomies)) {
                $support_categories = true;
            }
        }

        // Default date.
        $wlccduedate = date_parse(wlm_date('Y-m-d H:i:s'));
        $wlccduedate = wlm_date('Y-m-d H:i:s', mktime(0, 0, 0, (int) $wlccduedate['month'], (int) $wlccduedate['day'], (int) $wlccduedate['year']));

        $sched_type        = ['move', 'repost', 'set'];
        $content_schedules = [];
        if ($post_id) {
            foreach ($sched_type as $key => $t) {
                $content_sched = $this->get_post_manager_date($t, $post_id);
                foreach ((array) $content_sched as $key => $value) {
                    $content_schedules[] = [
                        'type'  => $t,
                        'value' => $value,
                    ];
                }
            }
        }
        wlm_print_script(plugins_url('views/assets/js/post-page-options-manager.js', WLM_CONTENT_CONTROL_FILE));
        ?>
            <div class="wlm-inside wlm-inside-manager" style="display: none;">
                <div class="manager-form-holder">
                    <table class="widefat" id='wlcc_set' style="width:100%;text-align: left;" cellspacing="0">
                        <thead>
                            <tr style="width:100%;">
                                <th colspan="3"><?php esc_html_e('Add Schedule', 'wishlist-member'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="width:100%;">
                                    <td style="width: 20%;border-bottom: 1px solid #eeeeee;">
                                        <label for="">Action</label>
                                    <select class="form-control wlm-select wlm-select-action" name="content_action" placeholder="<?php esc_attr_e('Select Action', 'wishlist-member'); ?>" style="width: 100%">
                                        <option value="set"><?php esc_html_e('Set Content Status', 'wishlist-member'); ?></option>
                                        <?php if ($support_categories) : ?>
                                            <option value="add"><?php esc_html_e('Add Content to a Category', 'wishlist-member'); ?></option>
                                            <option value="move"><?php esc_html_e('Move Content to a Category', 'wishlist-member'); ?></option>
                                        <?php endif; ?>
                                        <option value="repost"><?php esc_html_e('Repost Content', 'wishlist-member'); ?></option>
                                    </select>
                                    </td>
                                    <td style="width: 20%; border-bottom: 1px solid #eeeeee;">
                                        <label for=""><?php esc_html_e('Schedule', 'wishlist-member'); ?></label>
                                    <input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker" value="" name="schedule_date" placeholder="<?php esc_attr_e('Schedule Date', 'wishlist-member'); ?>">
                                    </td>
                                    <td style="width: 60%; border-bottom: 1px solid #eeeeee;">
                                        <div class="form-group membership-level-select action-moveadd-holder d-none">
                                                <?php $cats = get_categories('hide_empty=0'); ?>
                                                <label for=""><?php esc_html_e('Category', 'wishlist-member'); ?></label>
                                            <select class="form-control wlm-select-cat" name="content_cat[]" multiple="multiple" placeholder="<?php esc_attr_e('Select Categories', 'wishlist-member'); ?>" style="width: 100%">
                                                <?php foreach ((array) $cats as $cats) : ?>
                                                    <option value="<?php echo esc_attr($cats->cat_ID); ?>"><?php echo esc_html($cats->name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group action-status-holder">
                                            <label for=""><?php esc_html_e('Status', 'wishlist-member'); ?></label>
                                            <select class="form-control wlm-select wlm-select-status" name="content_status" placeholder="<?php esc_attr_e('Select Status', 'wishlist-member'); ?>" style="width: 100%">
                                                <option value="publish"><?php esc_html_e('Published', 'wishlist-member'); ?></option>
                                                <option value="pending"><?php esc_html_e('Pending Review', 'wishlist-member'); ?></option>
                                                <option value="draft"><?php esc_html_e('Draft', 'wishlist-member'); ?></option>
                                                <option value="trash"><?php esc_html_e('Trash', 'wishlist-member'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group action-repost-holder d-none">
                                            <div class="row">
                                                <div style="float: left; width: 20%;">
                                                    <label for=""><?php esc_html_e('Every', 'wishlist-member'); ?></label>
                                                    <input type="number" min="1" max="999999" class="form-control" name="content_every">
                                                </div>
                                                <div style="float: left; width: 40%;">
                                                    <label for="">&nbsp;</label>
                                                    <select class="form-control wlm-select-by" name="content_by" placeholder="<?php esc_attr_e('Select Frequency', 'wishlist-member'); ?>" style="width: 100%">
                                                        <option value="day"><?php esc_html_e('Day/s', 'wishlist-member'); ?></option>
                                                        <option value="month"><?php esc_html_e('Month/s', 'wishlist-member'); ?></option>
                                                        <option value="year"><?php esc_html_e('Year/s', 'wishlist-member'); ?></option>
                                                    </select>
                                                </div>
                                                <div style="float: left; width: 40%; padding-left: 5%;">
                                                    <label for=""><?php esc_html_e('Repetition', 'wishlist-member'); ?></label>
                                                    <input type="number" min="1" max="999999" class="form-control" name="content_repeat">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="text-align: right; padding-top: 4px; padding-bottom: 8px;">
                        <div class="wlm-message" style="display: none"><?php esc_html_e('Saved', 'wishlist-member'); ?></div>
                        <a href="#" class="wlm-btn -with-icons -success -centered-span wlm-manager-save">
                            <i class="wlm-icons"><img src="<?php echo esc_url(wishlistmember_instance()->plugin_url3); ?>/ui/images/baseline-save-24px.svg" alt=""></i>
                            <span><?php esc_html_e('Save Schedule', 'wishlist-member'); ?></span>
                        </a>
                    </div>
                </div>
                <table class="widefat" id='wlcc_manager_table' style="width:100%;text-align: left;" cellspacing="0">
                    <thead>
                        <tr style="width:100%;">
                            <th style="border-bottom: 1px solid #aaaaaa;"><?php esc_html_e('Schedules', 'wishlist-member'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($content_schedules) > 0) : ?>
                            <?php foreach ($content_schedules as $sched) : ?>
                                <tr>
                                    <td style="border-bottom: 1px solid #eeeeee;">
                                        <span class='wlm-manage-sched' style="vertical-align: middle;">
                                            <?php
                                                $str = '';
                                                $v   = $sched['value'];
                                            switch ($sched['type']) {
                                                case 'move':
                                                    if ('move' === $v->action) {
                                                        $str = esc_html__('Move to ', 'wishlist-member');
                                                    } else {
                                                        $str = esc_html__('Add to ', 'wishlist-member');
                                                    }
                                                    $cat = explode('#', $v->categories);
                                                    $t   = [];
                                                    foreach ((array) $cat as $cati => $c) {
                                                        $category = get_term_by('id', $c, 'category');
                                                        $t[]      = $category->name;
                                                    }
                                                    $str .= implode(',', $t);
                                                    $str .= ' on <strong>' . wishlistmember_instance()->format_date($v->due_date, 0) . '</strong>';
                                                    break;
                                                case 'repost':
                                                    $str  = esc_html__('Repost', 'wishlist-member');
                                                    $str .= ' on <strong>' . wishlistmember_instance()->format_date($v->due_date, 0) . '</strong>.';
                                                    if ($v->rep_num > 0) {
                                                        $every      = [
                                                            'day'   => esc_html__('Day/s', 'wishlist-member'),
                                                            'month' => esc_html__('Month/s', 'wishlist-member'),
                                                            'year'  => esc_html__('Year/s', 'wishlist-member'),
                                                        ];
                                                        $str       .= ' ' . esc_html__('Repeat every', 'wishlist-member') . ' <strong>' . $v->rep_num . ' ' . $every[ $v->rep_by ] . '</strong>.';
                                                                $d1 = date_parse($v->due_date);
                                                        if ('day' === $v->rep_by) {
                                                                $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $v->rep_num ), $d1['year']);
                                                        } elseif ('month' === $v->rep_by) {
                                                                    $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], ( $d1['month'] + $v->rep_num ), $d1['day'], $d1['year']);
                                                        } elseif ('year' === $v->rep_by) {
                                                            $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], ( $d1['year'] + $v->rep_num ));
                                                        } else {
                                                            $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $v->rep_num ), $d1['year']);
                                                        }

                                                        if ($v->rep_end > 0) {
                                                            $str .= ' ' . esc_html__('Next due date is on', 'wishlist-member') . ' <strong>' . wishlistmember_instance()->format_date(wlm_date('Y-m-d H:i:s', $new_bue_date), 0) . '</strong> (' . ( $v->rep_end - 1 ) . ' repetition/s left)';
                                                        } else {
                                                            $str .= esc_html__(' No repetition limit.', 'wishlist-member');
                                                        }
                                                    }
                                                    break;
                                                case 'set':
                                                    $stats = [
                                                        'publish' => esc_html__('Published', 'wishlist-member'),
                                                        'pending' => esc_html__('Pending Review', 'wishlist-member'),
                                                        'draft'   => esc_html__('Draft', 'wishlist-member'),
                                                        'trash'   => esc_html__('Trash', 'wishlist-member'),
                                                    ];
                                                    $str   = esc_html__('Set content status to', 'wishlist-member') . ' ' . $stats[ $v->status ];
                                                    $str  .= ' on <strong>' . wishlistmember_instance()->format_date($v->due_date, 0) . '</strong>.';
                                                    break;
                                            }
                                                echo wp_kses_data($str);
                                            ?>
                                        </span>
                                        <span class="wlm-manage-actions" style="float: right; vertical-align: middle;">
                                            <a href="#" class="wlm-manager-remove" type="<?php echo esc_attr($sched['type']); ?>" id="<?php echo esc_attr($v->id); ?>">remove</a>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                                <tr class="empty-tr">
                                    <td style="border-bottom: 1px solid #eeeeee;">
                                        <span class='wlm-manage-sched' style="vertical-align: middle;">
                                            <?php esc_html_e('- No schedule -', 'wishlist-member'); ?>
                                        </span>
                                        <span class="wlm-manage-actions" style="float: right; vertical-align: middle;">
                                            <a href="#" class="wlm-manager-remove" type="" id="">remove</a>
                                        </span>
                                    </td>
                                </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php
    }


    /**
     * Update the post manager date.
     *
     * @param integer $id   The ID of the post manager.
     * @param array   $data The data to update.
     */
    public function update_post_manager_date($id, $data)
    {
        global $wpdb;
        if ('move' === $data['action']) {
            $wpdb->update(
                $wpdb->prefix . 'wlcc_contentmanager_move',
                [
                    'due_date'   => $data['date'],
                    'categories' => $data['cats'],
                    'action'     => $data['method'],
                ],
                ['id' => $id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        } elseif ('repost' === $data['action']) {
            $wpdb->update(
                $wpdb->prefix . 'wlcc_contentmanager_repost',
                [
                    'due_date' => $data['date'],
                    'rep_num'  => $data['rep_num'],
                    'rep_by'   => $data['rep_by'],
                    'rep_end'  => $data['rep_end'],
                ],
                ['id' => $id],
                ['%s', '%d', '%s', '%d'],
                ['%d']
            );
        } elseif ('set' === $data['action']) {
            $wpdb->update(
                $wpdb->prefix . 'wlcc_contentmanager_set',
                [
                    'due_date' => $data['date'],
                    'status'   => $data['status'],
                ],
                ['id' => $id],
                ['%s', '%s'],
                ['%d']
            );
        }
    }


    /**
     * Save the post manager date.
     *
     * @param integer $post_id The ID of the post.
     * @param array   $data    The data to save.
     */
    public function save_post_manager_date($post_id, $data)
    {
        global $wpdb;
        if ('move' === $data['action']) {
            if (is_array($post_id)) {
                foreach ($post_id as $key => $value) {
                    $wpdb->insert(
                        $wpdb->prefix . 'wlcc_contentmanager_move',
                        [
                            'post_id'    => $value,
                            'due_date'   => $data['date'],
                            'categories' => $data['cats'],
                            'action'     => $data['method'],
                        ],
                        ['%d', '%s', '%s', '%s']
                    );
                }
            } else {
                $wpdb->insert(
                    $wpdb->prefix . 'wlcc_contentmanager_move',
                    [
                        'post_id'    => $post_id,
                        'due_date'   => $data['date'],
                        'categories' => $data['cats'],
                        'action'     => $data['method'],
                    ],
                    ['%d', '%s', '%s', '%s']
                );
            }
        } elseif ('repost' === $data['action']) {
            if (is_array($post_id)) {
                foreach ($post_id as $key => $value) {
                    $wpdb->insert(
                        $wpdb->prefix . 'wlcc_contentmanager_repost',
                        [
                            'post_id'  => $value,
                            'due_date' => $data['date'],
                            'rep_num'  => $data['rep_num'],
                            'rep_by'   => $data['rep_by'],
                            'rep_end'  => $data['rep_end'],
                        ],
                        ['%d', '%s', '%d', '%s', '%d']
                    );
                }
            } else {
                $wpdb->insert(
                    $wpdb->prefix . 'wlcc_contentmanager_repost',
                    [
                        'post_id'  => $post_id,
                        'due_date' => $data['date'],
                        'rep_num'  => $data['rep_num'],
                        'rep_by'   => $data['rep_by'],
                        'rep_end'  => $data['rep_end'],
                    ],
                    ['%d', '%s', '%d', '%s', '%d']
                );
            }
        } elseif ('set' === $data['action']) {
            if (is_array($post_id)) {
                foreach ($post_id as $key => $value) {
                    $wpdb->insert(
                        $wpdb->prefix . 'wlcc_contentmanager_set',
                        [
                            'post_id'  => $value,
                            'due_date' => $data['date'],
                            'status'   => $data['status'],
                        ],
                        ['%d', '%s', '%s']
                    );
                }
            } else {
                $wpdb->insert(
                    $wpdb->prefix . 'wlcc_contentmanager_set',
                    [
                        'post_id'  => $post_id,
                        'due_date' => $data['date'],
                        'status'   => $data['status'],
                    ],
                    ['%d', '%s', '%s']
                );
            }
        }
        return $wpdb->insert_id;
    }

    /**
     * Get the post manager date.
     *
     * @param  string  $action  The action.
     * @param  mixed   $post_id The post ID.
     * @param  mixed   $due_id  The due ID.
     * @param  integer $start   The start index.
     * @param  integer $limit   The limit.
     * @return array The post manager date.
     */
    public function get_post_manager_date($action, $post_id = '', $due_id = '', $start = 0, $limit = 0)
    {
        global $wpdb;
        $limit = $limit < 1 ? [0, PHP_INT_MAX] : [$start, $limit];
        if (! empty($post_id)) {
            $post_id = (array) $post_id;
            return $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentmanager_' . $action) . ' WHERE post_id IN (' . implode(', ', array_fill(0, count($post_id), '%d')) . ') LIMIT %d,%d',
                    ...array_values($post_id),
                    ...array_values($limit)
                )
            );
        } elseif (! empty($due_id)) {
            $due_id = (array) $due_id;
            return $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentmanager_' . $action) . ' WHERE id IN (' . implode(', ', array_fill(0, count($due_id), '%d')) . ') LIMIT %d,%d',
                    ...array_values($due_id),
                    ...array_values($limit)
                )
            );
        } else {
            return [];
        }
    }


    /**
     * Get the due date.
     *
     * @param  string  $action The action.
     * @param  mixed   $due_id The due ID.
     * @param  integer $start  The start index.
     * @param  integer $limit  The limit.
     * @return array The due date.
     */
    public function get_due_date($action, $due_id = '', $start = 0, $limit = 0)
    {
        global $wpdb;
        $limit = $limit < 1 ? [0, PHP_INT_MAX] : [$start, $limit];

        if (is_array($due_id)) {
            $results = $wpdb->query(
                $wpdb->prepare(
                    'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentmanager_' . $action) . ' WHERE id IN (' . implode(', ', array_fill(0, count($due_id), '%d')) . ') ORDER BY due_date ASC LIMIT %d, %d',
                    ...array_values($due_id),
                    ...array_values($limit)
                )
            );
        } elseif ($due_id) {
            $results = $wpdb->query(
                $wpdb->prepare(
                    'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentmanager_' . $action) . ' WHERE id=%d ORDER BY due_date ASC LIMIT %d, %d',
                    $due_id,
                    ...array_values($limit)
                )
            );
        } else {
            $results = $wpdb->query(
                $wpdb->prepare(
                    'SELECT * FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentmanager_' . $action) . ' ORDER BY date_added DESC LIMIT %d, %d',
                    ...array_values($limit)
                )
            );
        }
        return $results;
    }


    /**
     * Delete post manager date.
     *
     * @param mixed  $ids    The IDs.
     * @param string $action The action.
     */
    public function delete_post_manager_date($ids, $action)
    {
        global $wpdb;
        if (! is_array($ids)) {
            $ids = [$ids];
        }
        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentmanager_' . $action) . ' WHERE id IN (' . implode(', ', array_fill(0, count($ids), '%d')) . ')',
                ...array_values($ids)
            )
        );
    }


    /**
     * Delete post manager date by post ID.
     *
     * @param mixed  $ids    The post IDs.
     * @param string $action The action.
     */
    public function delete_post_manager_date_by_pid($ids, $action)
    {
        global $wpdb;
        if (! is_array($ids)) {
            $ids = [$ids];
        }
        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . esc_sql($wpdb->prefix . 'wlcc_contentmanager_' . $action) . ' WHERE post_id IN (' . implode(', ', array_fill(0, count($ids), '%d')) . ')',
                ...array_values($ids)
            )
        );
    }


    /**
     * Get posts for content control.
     *
     * @param  string  $action        The action.
     * @param  boolean $show_all      Whether to show all posts or not.
     * @param  string  $show_poststat The post status to show.
     * @param  string  $ptype         The post type.
     * @param  integer $start         The start index.
     * @param  integer $per_page      The number of posts per page.
     * @param  string  $sort          The sorting field.
     * @param  boolean $asc           Whether to sort in ascending order or not.
     * @return array                The array of posts.
     */
    public function cc_getposts($action, $show_all = false, $show_poststat = 'all', $ptype = 'post', $start = 0, $per_page = 0, $sort = 'ID', $asc = true)
    {
        global $wpdb;
        $table1 = $wpdb->prefix . 'posts';

        $limit = '';
        if ($per_page < 1) {
            $start    = 0;
            $per_page = PHP_INT_MAX;
        }

        if ($show_all) {
            if ('all' === $show_poststat) {
                $query_results = $wpdb->get_results(
                    $wpdb->prepare(
                        'SELECT ID,post_author,post_date,post_status,post_modified,post_title,post_content FROM ' . esc_sql($table1) . " WHERE post_type=%s AND post_status IN ('publish','draft','trash','pending') ORDER BY %0s %0s LIMIT %d,%d",
                        $ptype,
                        $sort,
                        $asc ? 'ASC' : 'DESC',
                        $start,
                        $per_page
                    )
                );
            } else {
                $query_results = $wpdb->get_results(
                    $wpdb->prepare(
                        'SELECT ID,post_author,post_date,post_status,post_modified,post_title,post_content FROM ' . esc_sql($table1) . ' WHERE post_type=%s AND post_status=%s ORDER BY %0s %0s LIMIT %d,%d',
                        $ptype,
                        $show_poststat ? $show_poststat : 'publish',
                        $sort,
                        $asc ? 'ASC' : 'DESC',
                        $start,
                        $per_page
                    )
                );
            }
        } else {
            $table2        = $wpdb->prefix . 'wlcc_contentmanager_' . $action;
            $query_results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT DISTINCT t1.ID,t1.post_author,t1.post_date,t1.post_status,t1.post_modified,t1.post_title,t1.post_content FROM ' . esc_sql($table1) . ' t1 INNER JOIN ' . esc_sql($table2) . ' t2 ON t1.ID=t2.post_id AND t1.post_type=%s ORDER BY %0s %0s LIMIT %d,%d',
                    $ptype,
                    $sort,
                    $asc ? 'ASC' : 'DESC',
                    $start,
                    $per_page
                )
            );
        }
        return $query_results;
    }


    /**
     * Apply due date.
     */
    public function apply_due_date()
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'posts';
        $table1 = $wpdb->prefix . 'wlcc_contentmanager_repost';
        $table2 = $wpdb->prefix . 'wlcc_contentmanager_move';
        $table3 = $wpdb->prefix . 'wlcc_contentmanager_set';

        $res = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . esc_sql($table1) . ' WHERE due_date <= %s', wlm_date('Y-m-d H:i:s')));
        foreach ((array) $res as $result) {
            $wpdb->update(
                $table,
                [
                    'post_date'     => $result->due_date,
                    'post_date_gmt' => $result->due_date,
                ],
                ['ID' => $result->post_id]
            );
                    // Check for repetition.
                    $rep_num = $result->rep_num;
                    $rep_end = $result->rep_end;
            if ($rep_num > 0) {
                $d1 = date_parse($result->due_date);
                if ('day' === $result->rep_by) {
                        $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $rep_num ), $d1['year']);
                } elseif ('month' === $result->rep_by) {
                        $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], ( $d1['month'] + $rep_num ), $d1['day'], $d1['year']);
                } elseif ('year' === $result->rep_by) {
                    $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], ( $d1['year'] + $rep_num ));
                } else {
                    $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $rep_num ), $d1['year']);
                }
                if ($rep_end > 0) {
                    if (1 === (int) $rep_end) {
                            $this->delete_post_manager_date($result->id, 'repost');
                    } else {
                        --$rep_end;
                    }
                }
                $datum = [
                    'action'  => 'repost',
                    'date'    => wlm_date('Y-m-d H:i:s', $new_bue_date),
                    'rep_num' => $rep_num,
                    'rep_by'  => $result->rep_by,
                    'rep_end' => $rep_end,
                ];
                $this->update_post_manager_date($result->id, $datum);
            } else { // If not repeated then delete.
                $this->delete_post_manager_date($result->id, 'repost');
            }
        }

        $res = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . esc_sql($table2) . ' WHERE due_date <= %s', wlm_date('Y-m-d H:i:s')));
        foreach ((array) $res as $result) {
            $cat = explode('#', $result->categories);
            if ('add' === $result->action) {
                $cur_cat = wp_get_post_categories($result->post_id);
                $x       = array_merge((array) $cat, (array) $cur_cat);
                $cat     = array_unique((array) $x);
            }
            $catpost                  = [];
            $catpost['ID']            = $result->post_id;
            $catpost['post_category'] = $cat;
            $ret                      = wp_update_post($catpost);
            wishlistmember_instance()->inherit_protection($result->post_id);
            $this->delete_post_manager_date($result->id, 'move');
        }

        $res = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . esc_sql($table3) . ' WHERE due_date <= %s', wlm_date('Y-m-d H:i:s')));
        foreach ((array) $res as $result) {
            $wpdb->update(
                $table,
                ['post_status' => $result->status],
                ['ID' => $result->post_id]
            );
            $this->delete_post_manager_date($result->id, 'set');
        }
    }
}

