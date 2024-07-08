<?php

/**
 * Content Control Class for WishList Member
 *
 * @package WishListMember/Features/ContentControl
 */

namespace WishListMember\Features;

defined('ABSPATH') || die();

/**
 * WishList Member Level Class
 *
 * @package    wishlistmember
 * @subpackage classes
 */
class Content_Control
{
    /**
     * The scheduler object.
     *
     * @var null
     */
    public $scheduler = null;

    /**
     * The archiver object.
     *
     * @var null
     */
    public $archiver = null;

    /**
     * The content manager object.
     *
     * @var null
     */
    public $manager = null;

    /**
     * Indicates if the old content control is active.
     *
     * @var boolean
     */
    public $old_contentcontrol_active = false;

    /**
     * Class constructor.
     *
     * @param mixed $that The parameter description.
     */
    public function __construct($that)
    {
        if (is_plugin_active('wishlist-content-control/wishlist-content-control.php') || isset($GLOBALS['WishListContentControl'])) {
            $this->old_contentcontrol_active = true;
            return;
        }

        if ($that->get_option('enable_content_scheduler')) {
            $this->scheduler = new Content_Scheduler();
        }
        if ($that->get_option('enable_content_archiver')) {
            $this->archiver = new Content_Archiver();
        }
        if ($that->get_option('enable_content_manager')) {
            $this->manager = new Content_Manager();
        }

        add_filter('wishlistmember_admin_action_get_contentcontrol_settings', [$this, 'get_contentcontrol_settings'], 10, 2);
        add_filter('wishlistmember_admin_action_set_content_schedule', [$this, 'set_content_schedule'], 10, 2);
        add_filter('wishlistmember_admin_action_set_content_archive', [$this, 'set_content_archive'], 10, 2);
        add_filter('wishlistmember_admin_action_set_content_manager', [$this, 'set_content_manager'], 10, 2);
    }


    /**
     * Activate the content control.
     */
    public function activate()
    {
        if ($this->scheduler) {
            $this->scheduler->activate();
        }
    }

    /**
     * Load hooks.
     */
    public function load_hooks()
    {
        if ($this->scheduler) {
            $this->scheduler->load_hooks();
        }
        if ($this->archiver) {
            $this->archiver->load_hooks();
        }
        if ($this->manager) {
            $this->manager->load_hooks();
        }
    }

    /**
     * Get Content Control Settings
     * Called by 'wishlistmember_admin_action_get_contentcontrol_settings' hook.
     *
     * @param  array $result Result to filter.
     * @param  array $data   Action data.
     * @return array
     */
    public function get_contentcontrol_settings($result, $data)
    {
        $type = $data['type'];

        $settings = [];

        if ('scheduler' === $type) {
            $page_type = wishlistmember_instance()->get_option($type . '_error_page_type');
            $page_type = $page_type ? $page_type : get_option('wlcc_sched_error_page');

            $pages_url = wishlistmember_instance()->get_option($type . '_error_page_url');
            $pages_url = $pages_url ? $pages_url : get_option('wlcc_sched_error_page_url');
        } elseif ('archiver' === $type) {
            $page_type = wishlistmember_instance()->get_option($type . '_error_page_type');
            $page_type = $page_type ? $page_type : get_option('wlcc_archived_error_page');

            $pages_url = wishlistmember_instance()->get_option($type . '_error_page_url');
            $pages_url = $pages_url ? $pages_url : get_option('wlcc_archived_error_page_url');

            $wlcc_non_users_access      = wishlistmember_instance()->get_option($type . '_content_access');
            $wlcc_non_users_access      = $wlcc_non_users_access ? $wlcc_non_users_access : get_option('wlcc_non_users_access');
            $settings['content_access'] = $wlcc_non_users_access ? $wlcc_non_users_access : 0;

            $wlcc_archived_post_visibility  = wishlistmember_instance()->get_option($type . '_content_visibility');
            $wlcc_archived_post_visibility  = $wlcc_archived_post_visibility ? $wlcc_archived_post_visibility : get_option('wlcc_archived_post_visibility');
            $settings['content_visibility'] = $wlcc_archived_post_visibility ? (array) $wlcc_archived_post_visibility : [];
        } else {
            return [
                'success'  => false,
                'msg'      => __('Invalid settings', 'wishlist-member'),
                'msg_type' => 'danger',
                'data'     => $data,
            ];
        }

        $page_internal = wishlistmember_instance()->get_option($type . '_error_page_internal');
        if (! $page_internal) {
            $page_internal = $page_type && 'url' !== $page_type && 'internal' !== $page_type && 'text' !== $page_type ? $page_type : false;
        }

        $page_type = $page_type ? $page_type : 'text';

        $pages_text = wishlistmember_instance()->get_option($type . '_error_page_text');
        if (! $pages_text) {
            $f = wishlistmember_instance()->legacy_wlm_dir . "/resources/page_templates/{$type}_internal.php";
            if (file_exists($f)) {
                include $f;
            }
            $pages_text = $content ? nl2br($content) : '';
            if ('text' === $page_type) {
                wishlistmember_instance()->save_option($type . '_error_page_text', $pages_text);
                wishlistmember_instance()->save_option($type . '_error_page_type', 'text');
            }
        }

        $pages_url = $pages_url ? $pages_url : '';

        $settings['type']     = $page_type;
        $settings['text']     = $pages_text;
        $settings['internal'] = $page_internal;
        $settings['url']      = $pages_url;
        return [
            'success'  => true,
            'msg'      => __('Content Scheduler settings found', 'wishlist-member'),
            'msg_type' => 'success',
            'settings' => $settings,
            'data'     => $data,
        ];
    }

    /**
     * Set Content Schedule
     * Called by 'wishlistmember_admin_action_set_content_schedule' hook.
     *
     * @param  array $result Result to filter.
     * @param  array $data   Action data.
     * @return array
     */
    public function set_content_schedule($result, $data)
    {
        $contentids = isset($data['contentids']) ? $data['contentids'] : '';
        $contentids = explode(',', $contentids);
        if (count($contentids) <= 0) {
            return [
                'success'  => false,
                'msg'      => __('No content selected', 'wishlist-member'),
                'msg_type' => 'danger',
            ];
        }

        $scheduler = wishlistmember_instance()->content_control->scheduler;
        if (isset($data['post_option'])) {
            if ('' !== $contentids[0] && ( isset($data['scheddays']) || isset($data['hidedays']) )) {
                $wpm_levels     = wishlistmember_instance()->get_option('wpm_levels');
                $scheddays      = isset($data['scheddays']) ? $data['scheddays'] : [];
                $hidedays       = isset($data['hidedays']) ? $data['hidedays'] : [];
                $schedtoggle    = isset($data['sched_toggle']) ? $data['sched_toggle'] : [];
                $showondate     = isset($data['show_on_date']) ? $data['show_on_date'] : [];
                $ondatehidedays = isset($data['ondate_hidedays']) ? $data['ondate_hidedays'] : [];

                $lvl_arr = [];
                foreach ((array) $wpm_levels as $id => $level) {
                    $days_delay      = isset($scheddays[ $id ]) ? $scheddays[ $id ] : 0;
                    $hide_delay      = isset($hidedays[ $id ]) ? $hidedays[ $id ] : 0;
                    $sched_toggle    = isset($schedtoggle[ $id ]) ? $schedtoggle[ $id ] : 'after';
                    $show_on_date    = isset($showondate[ $id ]) ? $showondate[ $id ] : null;
                    $ondate_hidedays = isset($ondatehidedays[ $id ]) ? $ondatehidedays[ $id ] : 0;
                    if ($days_delay <= 0 && 'after' === $sched_toggle) { // Save the sched days greater than zero only.
                        $scheduler->delete_content_sched($contentids[0], $id);
                    } else {
                        if (86400 > wlm_strtotime(wlm_trim($show_on_date))) {
                            return [
                                'success'  => false,
                                'msg'      => esc_html__('Invalid date.', 'wishlist-member') . ' (' . esc_html($show_on_date) . ')',
                                'msg_type' => 'danger',
                            ];
                        }
                        $lvl_arr[ $id ] = $scheddays[ $id ];
                        $scheduler->save_content_sched($contentids[0], $id, $days_delay, $hide_delay, $sched_toggle, $show_on_date, $ondate_hidedays);
                    }
                }
                if (count($lvl_arr) < 1) {
                    // If all levels have no value, delete all the sched value for this post.
                    $scheduler->delete_content_sched($contentids[0]);
                } else {
                    // Protect content if there are levels with protection.
                    $lvl_arr        = array_keys($lvl_arr);
                    $type           = get_post_type($contentids[0]);
                    $current_levels = wishlistmember_instance()->get_content_levels($type, $contentids[0], true, false);
                    $current_levels = is_array($current_levels) ? $current_levels : [];
                    $current_levels = array_keys($current_levels);
                    $current_levels = array_merge((array) $lvl_arr, (array) $current_levels);
                    wishlistmember_instance()->special_content_level($contentids[0], 'Protection', 'Y', $type);
                    wishlistmember_instance()->special_content_level($contentids[0], 'Inherit', 'N', $type);
                    wishlistmember_instance()->set_content_levels($type, $contentids[0], $current_levels);
                }
                return [
                    'success'  => true,
                    'msg'      => esc_html__('Content schedule has been updated', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => esc_html__('Content schedule was not updated', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            }
        } else {
            $wlm_levels = isset($data['wlm_levels']) ? (array) $data['wlm_levels'] : [];
            if (count($wlm_levels) <= 0) {
                return [
                    'success'  => false,
                    'msg'      => esc_html__('No level selected', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }

            if ('set' === $data['sched_action']) {
                $sched_toggle    = isset($data['sched_toggle']) ? $data['sched_toggle'] : '';
                $show_on_date    = isset($data['show_on_date']) ? $data['show_on_date'] : '';
                $show_for_ondate = isset($data['show_for_ondate']) ? $data['show_for_ondate'] : '';

                if (86400 > wlm_strtotime(wlm_trim($show_on_date))) {
                    return [
                        'success'  => false,
                        'msg'      => esc_html__('Invalid date.', 'wishlist-member') . ' (' . esc_html($show_on_date) . ')',
                        'msg_type' => 'danger',
                    ];
                }

                foreach ($wlm_levels as $key => $lvl) {
                    foreach ($contentids as $id) {
                        $scheduler->save_content_sched($id, $lvl, $data['show_after'], $data['show_for'], $sched_toggle, $show_on_date, $show_for_ondate);
                    }
                }
                foreach ($contentids as $key => $contentid) {
                    $type           = get_post_type($contentid);
                    $current_levels = wishlistmember_instance()->get_content_levels($type, $contentid, true, false);
                    $current_levels = is_array($current_levels) ? $current_levels : [];
                    $current_levels = array_keys($current_levels);
                    $current_levels = array_merge((array) $wlm_levels, (array) $current_levels);
                    wishlistmember_instance()->special_content_level($contentid, 'Protection', 'Y', $type);
                    wishlistmember_instance()->special_content_level($contentid, 'Inherit', 'N', $type);
                    wishlistmember_instance()->set_content_levels($type, $contentid, $current_levels);
                }
                return [
                    'success'  => true,
                    'msg'      => esc_html__('Content schedule set', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } if ('remove' === $data['sched_action']) {
                foreach ($wlm_levels as $key => $lvl) {
                    $scheduler->delete_content_sched($contentids, $lvl);
                }
                return [
                    'success'  => true,
                    'msg'      => esc_html__('Content schedule has been removed', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            }
        }
    }

    /**
     * Set Content Archive
     * Called by 'wishlistmember_admin_action_set_content_archive' hook.
     *
     * @param  array $result Result to filter.
     * @param  array $data   Action data.
     * @return array
     */
    public function set_content_archive($result, $data)
    {
        $contentids = isset($data['contentids']) ? $data['contentids'] : '';
        $contentids = explode(',', $contentids);
        if (count($contentids) <= 0) {
            return [
                'success'  => false,
                'msg'      => esc_html__('No content selected', 'wishlist-member'),
                'msg_type' => 'danger',
            ];
        }

        $archiver = wishlistmember_instance()->content_control->archiver;

        if (isset($data['post_option'])) {
            $wpm_levels  = wishlistmember_instance()->get_option('wpm_levels');
            $wlcc_expiry = $data['wlcc_expiry'];
            $wlccexpdate = date_parse(wlm_date('Y-m-d H:i:s'));
            $datenow     = wlm_date('Y-m-d H:i:s', mktime(0, 0, 0, (int) $wlccexpdate['month'], (int) $wlccexpdate['day'], (int) $wlccexpdate['year']));
            $lvl_arr     = [];
            foreach ((array) $wpm_levels as $id => $level) {
                $wlccexpiry = isset($wlcc_expiry[ $id ]) ? $wlcc_expiry[ $id ] : '';
                if (empty($wlccexpiry)) {
                    $archiver->delete_post_expiry_date($contentids[0], $id);
                } else {
                    $date = wlm_date('Y-m-d H:i:s', wlm_strtotime(wlm_trim($wlccexpiry) . ' ' . wlm_timezone_string()));
                    if (86400 > wlm_strtotime(wlm_trim($date))) {
                        return [
                            'success'  => false,
                            'msg'      => esc_html__('Invalid archive date.', 'wishlist-member') . ' (' . esc_html($wlccexpiry) . ')',
                            'msg_type' => 'danger',
                        ];
                    }
                    $lvl_arr[ $id ] = $date;
                    $archiver->save_post_expiry_date($contentids[0], $id, $date);
                }
            }

            if (count($lvl_arr) > 0) {
                // Protect content if there are levels with protection.
                $lvl_arr        = array_keys($lvl_arr);
                $type           = get_post_type($contentids[0]);
                $current_levels = wishlistmember_instance()->get_content_levels($type, $contentids[0], true, false);
                $current_levels = is_array($current_levels) ? $current_levels : [];
                $current_levels = array_keys($current_levels);
                $current_levels = array_merge((array) $lvl_arr, (array) $current_levels);
                wishlistmember_instance()->special_content_level($contentids[0], 'Protection', 'Y', $type);
                wishlistmember_instance()->special_content_level($contentids[0], 'Inherit', 'N', $type);
                wishlistmember_instance()->set_content_levels($type, $contentids[0], $current_levels);
            }
            return [
                'success'  => true,
                'msg'      => esc_html__('Content archive date has been updated', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        } else {
            $wlm_levels = isset($data['wlm_levels']) ? (array) $data['wlm_levels'] : [];
            if (count($wlm_levels) <= 0) {
                return [
                    'success'  => false,
                    'msg'      => esc_html__('No level selected', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }

            if ('set' === $data['sched_action']) {
                $wlccexpdate = date_parse(wlm_date('Y-m-d H:i:s'));
                $datenow     = wlm_date('Y-m-d H:i:s', mktime(0, 0, 0, (int) $wlccexpdate['month'], (int) $wlccexpdate['day'], (int) $wlccexpdate['year']));
                $wlccexpiry  = '' === $data['archive_date'] ? $datenow : $data['archive_date'];
                $date        = wlm_date('Y-m-d H:i:s', wlm_strtotime(wlm_trim($wlccexpiry) . ' ' . wlm_timezone_string()));
                if (86400 > wlm_strtotime(wlm_trim($date))) {
                    return [
                        'success'  => false,
                        'msg'      => esc_html__('Invalid archive date.', 'wishlist-member') . ' (' . esc_html($data['archive_date']) . ')',
                        'msg_type' => 'danger',
                    ];
                }
                foreach ($wlm_levels as $key => $lvl) {
                    foreach ($contentids as $id) {
                        $archiver->save_post_expiry_date($id, $lvl, $date);
                    }
                }
                foreach ($contentids as $key => $contentid) {
                    $type           = get_post_type($contentid);
                    $current_levels = wishlistmember_instance()->get_content_levels($type, $contentid, true, false);
                    $current_levels = is_array($current_levels) ? $current_levels : [];
                    $current_levels = array_keys($current_levels);
                    $current_levels = array_merge((array) $wlm_levels, (array) $current_levels);
                    wishlistmember_instance()->special_content_level($contentid, 'Protection', 'Y', $type);
                    wishlistmember_instance()->special_content_level($contentid, 'Inherit', 'N', $type);
                    wishlistmember_instance()->set_content_levels($type, $contentid, $current_levels);
                }
                return [
                    'success'  => true,
                    'msg'      => esc_html__('Content archive date set', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } elseif ('remove' === $data['sched_action']) {
                foreach ($wlm_levels as $key => $lvl) {
                    $archiver->delete_post_expiry_date($contentids, $lvl);
                }
                return [
                    'success'  => true,
                    'msg'      => esc_html__('Content archive date has been removed', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            }
        }
    }

    /**
     * Set Content Manager
     * Called by 'wishlistmember_admin_action_set_content_manager' hook.
     *
     * @param  array $result Result to filter.
     * @param  array $data   Action data.
     * @return array
     */
    public function set_content_manager($result, $data)
    {
        $schedid    = isset($data['schedid']) && $data['schedid'] ? $data['schedid'] : false;
        $contentids = isset($data['contentids']) ? $data['contentids'] : '';
        if (! $schedid) {
            $contentids = explode(',', $contentids);
            if (count($contentids) <= 0) {
                return [
                    'success'  => false,
                    'msg'      => esc_html__('No content selected', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
        }

        $manager = wishlistmember_instance()->content_control->manager;

        if ('set' === $data['sched_action']) {
            $wlccexpdate   = date_parse(wlm_date('Y-m-d H:i:s'));
            $datenow       = wlm_date('Y-m-d H:i:s', mktime(0, 0, 0, (int) $wlccexpdate['month'], (int) $wlccexpdate['day'], (int) $wlccexpdate['year']));
            $schedule_date = '' ? '' === $data['schedule_date'] : $data['schedule_date'];
            $schedule_date = wlm_date('Y-m-d H:i:s', wlm_strtotime(wlm_trim($schedule_date) . ' ' . wlm_timezone_string()));

            if ($datenow > $schedule_date) {
                return [
                    'success'  => false,
                    'msg'      => esc_html__('Schedule date must be in the future.', 'wishlist-member') . ' (' . $data['schedule_date'] . ')',
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }

            $d = [];
            if ('move' === $data['content_action'] || 'add' === $data['content_action']) {
                $cats = isset($data['content_cat']) ? (array) $data['content_cat'] : [];
                if (count($cats) <= 0) {
                    return [
                        'success'  => false,
                        'msg'      => __('Please select a category', 'wishlist-member'),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
                $cats = implode('#', $cats);
                $d    = [
                    'action' => 'move',
                    'method' => $data['content_action'],
                    'date'   => $schedule_date,
                    'cats'   => $cats,
                ];
            } elseif ('set' === $data['content_action']) {
                if (empty($data['content_status'])) {
                    return [
                        'success'  => false,
                        'msg'      => esc_html__('Invalid post status.', 'wishlist-member'),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
                $d = [
                    'action' => 'set',
                    'method' => $data['content_action'],
                    'date'   => $schedule_date,
                    'status' => $data['content_status'],
                ];
            } elseif ('repost' === $data['content_action']) {
                $d = [
                    'action'  => 'repost',
                    'method'  => $data['content_action'],
                    'date'    => $schedule_date,
                    'rep_num' => $data['content_every'],
                    'rep_by'  => $data['content_by'],
                    'rep_end' => $data['content_repeat'],
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => esc_html__('Invalid Action', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }

            $str_sched = '';
            if (isset($data['post_option'])) {
                switch ($d['action']) {
                    case 'move':
                        if ('move' === $d['method']) {
                            $str_sched = esc_html__('Move to ', 'wishlist-member');
                        } else {
                            $str_sched = esc_html__('Add to ', 'wishlist-member');
                        }
                        $cat = explode('#', $d['cats']);
                        $t   = [];
                        foreach ((array) $cat as $cati => $c) {
                            $category = get_term_by('id', $c, 'category');
                            $t[]      = $category->name;
                        }
                        $str_sched .= implode(',', $t);
                        $str_sched .= ' on <strong>' . wishlistmember_instance()->format_date($d['date'], 0) . '</strong>';
                        break;
                    case 'repost':
                        $str_sched  = esc_html__('Repost', 'wishlist-member');
                        $str_sched .= ' on <strong>' . wishlistmember_instance()->format_date($d['date'], 0) . '</strong>.';
                        if ($d['rep_num'] > 0) {
                            $every      = [
                                'day'   => 'Day/s',
                                'month' => 'Month/s',
                                'year'  => 'Year/s',
                            ];
                            $str_sched .= ' ' . esc_html__('Repeat every', 'wishlist-member') . ' <strong>' . $d['rep_num'] . ' ' . $every[ $d['rep_by'] ] . '</strong>.';
                                $d1     = date_parse($d['date']);
                            if ('day' === $d['rep_by']) {
                                $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $d['rep_num'] ), $d1['year']);
                            } elseif ('month' === $d['rep_by']) {
                                $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], ( $d1['month'] + $d['rep_num'] ), $d1['day'], $d1['year']);
                            } elseif ('year' === $d['rep_by']) {
                                $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], ( $d1['year'] + $d['rep_num'] ));
                            } else {
                                $new_bue_date = mktime($d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $d['rep_num'] ), $d1['year']);
                            }

                            if ($d['rep_end'] > 0) {
                                $str_sched .= ' ' . esc_html__('Next due date is on', 'wishlist-member') . ' <strong>' . wishlistmember_instance()->format_date(wlm_date('Y-m-d H:i:s', $new_bue_date), 0) . '</strong> (' . ( $d['rep_end'] - 1 ) . ' repetition/s left)';
                            } else {
                                $str_sched .= esc_html__(' No repetition limit.', 'wishlist-member');
                            }
                        }
                        break;
                    case 'set':
                        $stats      = [
                            'publish' => esc_html__('Published', 'wishlist-member'),
                            'pending' => esc_html__('Pending Review', 'wishlist-member'),
                            'draft'   => esc_html__('Draft', 'wishlist-member'),
                            'trash'   => esc_html__('Trash', 'wishlist-member'),
                        ];
                        $str_sched  = esc_html__('Set content status to ', 'wishlist-member') . $stats[ $d['status'] ];
                        $str_sched .= ' on <strong>' . wishlistmember_instance()->format_date($d['date'], 0) . '</strong>.';
                        break;
                }
            }

            if ($schedid) {
                $manager->update_post_manager_date($schedid, $d);
                return [
                    'success'     => true,
                    'msg'         => esc_html__('Content schedule has been updated', 'wishlist-member'),
                    'msg_type'    => 'success',
                    'data'        => $data,
                    'action_type' => $d['action'],
                    'str_sched'   => $str_sched,
                    'insertid'    => $schedid,
                ];
            } else {
                $insertid = $manager->save_post_manager_date($contentids, $d);
                return [
                    'success'     => true,
                    'msg'         => esc_html__('Content schedule date set', 'wishlist-member'),
                    'msg_type'    => 'success',
                    'data'        => $data,
                    'action_type' => $d['action'],
                    'str_sched'   => $str_sched,
                    'insertid'    => $insertid,
                ];
            }
        } elseif ('remove' === $data['sched_action']) {
            $sched_type = ['move', 'repost', 'set'];
            if (isset($data['post_option'])) {
                $id   = $data['id'];
                $type = $data['type'];
                $manager->delete_post_manager_date($id, $type);
                return [
                    'success'  => true,
                    'msg'      => esc_html__('Schedule of the content has been removed', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } elseif ($schedid) {
                $manager->delete_post_manager_date($schedid, $data['content_action']);
                return [
                    'success'  => true,
                    'msg'      => esc_html__('The schedule has been removed', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } else {
                $content_action = 'add' === $data['content_action'] ? 'move' : $data['content_action'];
                if (! in_array($content_action, $sched_type, true)) {
                    return [
                        'success'  => false,
                        'msg'      => esc_html__('Invalid action to remove', 'wishlist-member'),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
                $manager->delete_post_manager_date_byPostId($contentids, $content_action);
                return [
                    'success'  => true,
                    'msg'      => esc_html__('Content schedule has been removed', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            }
        }
    }
}
