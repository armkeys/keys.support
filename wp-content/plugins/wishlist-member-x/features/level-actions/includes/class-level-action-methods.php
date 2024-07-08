<?php

/**
 * Level Action Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember\Features\Level_Actions;

/**
 * Level Action Methods class
 */
class Level_Action_Methods
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        add_action('wishlistmember_do_sequential_upgrade', [$this, 'process_scheduled_level_actions_for_user'], 10, 1);
        add_filter('wishlistmember_get_user_scheduled_level_actions', [$this, 'get_user_scheduled_level_actions_filter'], 10, 2);
        add_action('wishlistmember_add_user_levels', [$this, 'add_user_levels_action'], 10, 4);
        add_action('wishlistmember_approve_user_levels', [$this, 'approve_user_levels_action'], 10, 2);
        add_action('wishlistmember_confirm_user_levels', [$this, 'confirm_user_levels_action'], 10, 2);
        add_action('wishlistmember_remove_user_levels', [$this, 'remove_user_levels_action'], 10, 2);
        add_action('wishlistmember_cancel_user_levels', [$this, 'cancel_user_levels_action'], 10, 2);

        add_action('wishlistmember_approve_user_levels', [$this, 'do_update_child_status'], 1, 2);
        add_action('wishlistmember_cancel_user_levels', [$this, 'do_update_child_status'], 1, 2);
        add_action('wishlistmember_confirm_user_levels', [$this, 'do_update_child_status'], 1, 2);
        add_action('wishlistmember_process_level_actions', [$this, 'record_user_level_actions'], 1, 3);
        add_action('wishlistmember_remove_user_levels', [$this, 'do_remove_child_levels'], 1, 3);
        add_action('wishlistmember_run_user_level_actions', [$this, 'process_scheduled_level_actions']);
        add_action('wishlistmember_unapprove_user_levels', [$this, 'do_update_child_status'], 1, 2);
        add_action('wishlistmember_uncancel_user_levels', [$this, 'do_update_child_status'], 1, 2);
        add_action('wishlistmember_unconfirm_user_levels', [$this, 'do_update_child_status'], 1, 2);
        add_filter('wishlistmember_user_expire_date', [$this, 'do_expire_child_status'], 1, 3);

        add_filter(
            'wishlistmember_instance_methods',
            function ($methods) {
                $methods['process_scheduled_level_actions']  = [[$this, 'process_scheduled_level_actions']];
                $methods['process_level_actions']            = [[$this, 'process_level_actions']];
                $methods['do_level_action']                  = [[$this, 'do_level_action']];
                $methods['get_user_scheduled_level_actions'] = [[$this, 'get_user_scheduled_level_actions']];
                $methods['record_user_level_actions']        = [[$this, 'record_user_level_actions']];
                $methods['do_auto_add_remove']               = [[$this, 'do_auto_add_remove']];
                $methods['remove_do_auto_add_remove']        = [[$this, 'remove_do_auto_add_remove']];
                $methods['cancel_do_auto_add_remove']        = [[$this, 'cancel_do_auto_add_remove']];
                $methods['do_remove_child_levels']           = [[$this, 'do_remove_child_levels']];
                $methods['do_update_child_status']           = [[$this, 'do_update_child_status']];
                $methods['do_expire_child_status']           = [[$this, 'do_expire_child_status']];
                return $methods;
            }
        );
    }

    /**
     * Get scheduled level actions
     * Called by 'wishlistmember_get_user_scheduled_level_actions' filter
     *
     * @param  array   $schedules Scheduled Actions.
     * @param  integer $user_id   User ID.
     * @return array
     */
    public function get_user_scheduled_level_actions_filter($schedules, $user_id)
    {
        $schedules = $this->get_user_scheduled_level_actions($user_id);
        return $schedules;
    }
    /**
     * Process scheduled level actions for the current user.
     * Called by 'wishlistmember_done_sequential_upgrade' hook
     *
     * @param  integer $user_id User ID.
     * @return void
     */
    public function process_scheduled_level_actions_for_user($user_id)
    {
        $this->process_scheduled_level_actions($user_id ?? get_current_user_id());
    }

    /**
     * Process scheduled level actions for user
     *
     * @param integer $uid User ID.
     */
    public function process_scheduled_level_actions($uid = null)
    {
        ignore_user_abort(true);
        $wlm_is_doing_level_actions = 'wlm_is_doing_level_actions_' . wlm_server_data()['REMOTE_ADDR'];
        if (is_null($uid)) {
            if ('yes' === get_transient($wlm_is_doing_level_actions)) {
                return;
            }
            set_transient($wlm_is_doing_level_actions, 'yes', 60 * 60 * 24);
        }
        wlm_set_time_limit(60 * 60 * 12);
        $level_actions = $this->get_user_scheduled_level_actions($uid);
        foreach ((array) $level_actions as $key => $action) {
            $meta_value = isset($action['meta_value']) ? wlm_maybe_unserialize($action['meta_value']) : [];
            if (isset($meta_value['action_timestamp'])) {
                if ($meta_value['action_timestamp'] <= time()) {
                    $this->do_level_action($action['user_id'], $meta_value['trigger_level'], $meta_value['action_id'], $meta_value['action_details']);
                    delete_user_meta($action['user_id'], $action['meta_key']);
                }
            } else {
                delete_user_meta($action['user_id'], $action['meta_key']);
            }
        }

        wlm_set_time_limit(ini_get('max_execution_time'));
        delete_transient($wlm_is_doing_level_actions);
    }

    /**
     * Add user levels action
     * Called by 'wishlistmember_approve_user_levels' hook.
     *
     * @param  integer $id                     User ID.
     * @param  array   $new_levels             New membership levels.
     * @param  array   $removed_levels         Removed membership levels.
     * @param  array   $pending_autoresponders Array of pending autoresponders.
     * @return void
     */
    public function add_user_levels_action($id, $new_levels, $removed_levels, $pending_autoresponders)
    {
        if (empty($pending_autoresponders) || wlm_admin_in_admin()) {
            $this->process_level_actions($new_levels, $id, 'added');
        }
    }

    /**
     * Approve user levels action
     * Called by 'wishlistmember_approve_user_levels' hook.
     *
     * @param  integer $id     User ID.
     * @param  array   $levels Membership Levels.
     * @return void
     */
    public function approve_user_levels_action($id, $levels)
    {
        $this->process_level_actions($levels, $id, 'added');
    }

    /**
     * Confirm user levels action
     * Called by 'wishlistmember_confirm_user_levels' hook.
     *
     * @param  integer $id     User ID.
     * @param  array   $levels Membership Levels.
     * @return void
     */
    public function confirm_user_levels_action($id, $levels)
    {
        $this->process_level_actions($levels, $id, 'added');
    }

    /**
     * Remove user levels action
     * Called by 'wishlistmember_remove_user_levels' hook.
     *
     * @param  integer $id     User ID.
     * @param  array   $levels Membership Levels.
     * @return void
     */
    public function remove_user_levels_action($id, $levels)
    {
        $this->process_level_actions($levels, $id, 'removed');
    }

    /**
     * Cancel user levels action
     * Called by 'wishlistmember_cancel_user_levels' hook.
     *
     * @param  integer $id     User ID.
     * @param  array   $levels Membership Levels.
     * @return void
     */
    public function cancel_user_levels_action($id, $levels)
    {
        remove_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
        $this->process_level_actions($levels, $id, 'cancelled');
    }

    /**
     * Process level actions
     *
     * @param array   $levels Array of level IDs.
     * @param integer $uid    User ID.
     * @param string  $event  Event.
     */
    public function process_level_actions($levels, $uid, $event)
    {
        // Let's remove pay per post.
        foreach ((array) $levels as $key => $lvl) {
            if (false !== strpos($lvl, 'U-')) {
                unset($levels[ $key ]);
            }
            if (empty($lvl)) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }
        if (! is_array($levels)) {
            return;
        }
        if (! in_array($event, ['added', 'removed', 'cancelled'], true)) {
            return;
        }

        // Let's get what actions are being processed.
        $user_level_action_record = get_transient('user_level_action_record_' . $uid);
        $user_level_action_record = is_array($user_level_action_record) ? $user_level_action_record : [];

        foreach ($levels as $key => $lvlid) {
            // Let's skip unconfirmed and pending levels.
            if ('added' === $event && ( wishlistmember_instance()->level_pending($lvlid, $uid) || wishlistmember_instance()->level_unconfirmed($lvlid, $uid) )) {
                continue;
            }
            $level_actions = wishlistmember_instance()->level_options->get_options($lvlid, 'scheduled_action');
            foreach ($level_actions as $key => $action) {
                $action_value = wlm_maybe_unserialize($action->option_value);

                /*
                 * let's check if this is the event and if this event hasnt been processes already.
                 * We do not allow the same event to be executed within the chain to prevent loop.
                 */
                if ($action_value['level_action_event'] === $event && ! isset($user_level_action_record[ $action->ID ])) {
                    // Let's process schedule actions seperately.
                    if (( 'after' === $action_value['sched_toggle'] && (int) $action_value['sched_after_term'] > 0 ) || 'ondate' === $action_value['sched_toggle']) {
                        if ('ondate' === $action_value['sched_toggle']) {
                            // Adjust schedule with timezone gmt offset.
                            $gmt = get_option('gmt_offset');
                            if ($gmt >= 0) {
                                $gmt = '+' . $gmt;
                            }
                            $gmt          = ' ' . $gmt . ' GMT';
                            $upgrade_date = strtotime($action_value['sched_ondate'] . $gmt);
                        } else {
                            $period       = $action_value['sched_after_period'] ? $action_value['sched_after_period'] : 'days';
                            $upgrade_date = strtotime('+' . $action_value['sched_after_term'] . ' ' . $period, time());
                        }
                        $meta_key   = 'incoming_level_actions_' . $action->ID;
                        $meta_value = [
                            'action_id'        => $action->ID,
                            'event'            => $action_value['level_action_event'],
                            'trigger_level'    => $lvlid,
                            'action_timestamp' => $upgrade_date,
                            'timestamp'        => time(),
                            'action_details'   => $action_value,
                        ];
                        update_user_meta($uid, $meta_key, $meta_value);
                    } else {
                        $this->do_level_action($uid, $lvlid, $action->ID, $action_value);
                    }
                }
            }
        }
    }

    /**
     * Execute level action
     *
     * @param integer $uid          User ID.
     * @param string  $trigger_lvl  Level ID.
     * @param integer $action_id    Action ID.
     * @param array   $action_value Action.
     */
    public function do_level_action($uid, $trigger_lvl, $action_id, $action_value)
    {
        $wpm_levels     = wishlistmember_instance()->get_option('wpm_levels');
        $current_levels = wishlistmember_instance()->get_membership_levels($uid, null, null, true);
        $action_levels  = isset($action_value['action_levels']) && is_array($action_value['action_levels']) ? $action_value['action_levels'] : [];

        $action_value = array_merge($action_value, ['level_action_metaid' => $action_id]);

        // If trigger level does not exist anymore, do not continue.
        if (! isset($trigger_lvl) || ! isset($wpm_levels[ $trigger_lvl ])) {
            return;
        }

        do_action('wishlistmember_process_level_actions', $uid, $trigger_lvl, $action_value);

        $level_email = isset($action_value['level_email']) ? wlm_trim($action_value['level_email']) : 'dontsend';
        $level_email = in_array($level_email, ['send', 'sendlevel', 'dontsend'], true) ? $level_email : 'dontsend';

        if (in_array($action_value['level_action_method'], ['create-ppp', 'add-ppp', 'remove-ppp'], true)) {
            $pid      = isset($action_value['ppp_content']) ? $action_value['ppp_content'] : false;
            $the_post = get_post($pid);
            if ($the_post) {
                $post_type = $the_post->post_type;
                $post_id   = $the_post->ID;

                if (in_array($action_value['level_action_method'], ['create-ppp', 'add-ppp'], true)) {
                    if ('create-ppp' === $action_value['level_action_method']) {
                        $user_info = get_userdata($uid);
                        if ($user_info) {
                            $username         = $user_info->user_login;
                            $first_name       = $user_info->first_name;
                            $last_name        = $user_info->last_name;
                            $title_shortcodes = [
                                '{fname}'     => $user_info->first_name,
                                '{lname}'     => $user_info->last_name,
                                '{name}'      => wlm_trim($user_info->first_name . ' ' . $user_info->last_name),
                                '{email}'     => $user_info->user_email,
                                '{username}'  => $user_info->user_login,
                                '{id}'        => $user_info->ID,
                                '{date}'      => date_i18n(get_option('date_format')),
                                '{time}'      => date_i18n(get_option('time_format')),
                                '{timestamp}' => date_i18n(get_option('date_format')),
                            ];

                            $ptitle = isset($action_value['ppp_title']) ? wlm_trim($action_value['ppp_title']) : '{name}-' . $the_post->post_title;
                            $ptitle = ! empty($ptitle) ? $ptitle : '{name}-' . $the_post->post_title;
                            $ptitle = str_replace(array_keys($title_shortcodes), $title_shortcodes, $ptitle);
                            // Check for duplicates.
                            $dup       = get_page_by_title($ptitle, ARRAY_A, $post_type);
                            $dup_cnt   = 1;
                            $t         = $ptitle;
                            $ppp_users = [];
                            $create_p  = true;
                            while (! is_null($dup)) {
                                // If the post exist and user has access to it, dont create.
                                $ppp_users = wishlistmember_instance()->get_post_users($dup['post_type'], $dup['ID']);
                                if (! in_array("U-{$uid}", $ppp_users, true)) {
                                        $t   = $ptitle . ' ' . $dup_cnt;
                                        $dup = get_page_by_title($t, ARRAY_A, $post_type);
                                } else {
                                    $post_id   = $dup['ID'];
                                    $post_type = $dup['post_type'];
                                    $dup       = null;
                                    $create_p  = false;
                                }
                                ++$dup_cnt;
                            }
                            // Only create if post does not exist or user has no access to it.
                            if ($create_p) {
                                // Note the original id, we will use it for postmeta.
                                $old_pid = $post_id;

                                $ptitle                    = $t;
                                $page_data                 = [];
                                $page_data['post_title']   = $ptitle;
                                $page_data['post_content'] = $the_post->post_content;
                                $page_data['post_type']    = $post_type;
                                $page_data['post_status']  = 'publish';
                                $post_id                   = wp_insert_post($page_data, true);
                                wishlistmember_instance()->special_content_level($post_id, 'Protection', 'Y', $post_type);
                                wishlistmember_instance()->special_content_level($post_id, 'Inherit', 'N', $post_type);
                                wishlistmember_instance()->pay_per_post($post_id, 'Y');

                                // Let's update the meta.
                                global $wpdb;
                                $wpdb->query(
                                    $wpdb->prepare(
                                        "INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) SELECT %d,meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d",
                                        $post_id,
                                        $old_pid
                                    )
                                );
                            }
                        }
                    }
                    wishlistmember_instance()->add_post_users($post_type, $post_id, $uid);
                } else {
                    wishlistmember_instance()->remove_post_users($post_type, $post_id, $uid);
                }
            }
        } elseif ('cancel' === $action_value['level_action_method']) {
            foreach ($action_levels as $key => $lvl) {
                if (isset($wpm_levels[ $lvl ])) { // Make sure that level is existing and active.
                    if (in_array($lvl, $current_levels)) { // Only cancel levels that this user currently have.
                        // Send email notification.
                        // It has to be here because it resets every call of LevelCancelled.
                        if ('dontsend' !== $level_email) {
                            if ('sendlevel' !== $level_email) {
                                add_filter(
                                    'wishlistmember_per_level_template_setting',
                                    function ($value, $setting, $user_id, $level_id) {
                                        return 'cancel_notification' === $setting ? 1 : $value;
                                    },
                                    10,
                                    4
                                );
                            }
                        } else {
                            // Disable cancel / uncancel email notif.
                            add_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                        }
                        wishlistmember_instance()->level_cancelled($lvl, $uid, true);
                        // Let's remove the filter.
                        remove_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                        remove_filter(
                            'wishlistmember_per_level_template_setting',
                            function ($value, $setting, $user_id, $level_id) {
                                return 'cancel_notification' === $setting ? 1 : $value;
                            },
                            10,
                            4
                        );
                    }
                }
            }
        } elseif ('cancel-from-same-level' === $action_value['level_action_method']) {
            if (isset($wpm_levels[ $trigger_lvl ])) { // Make sure that level is existing and active.
                if (in_array($trigger_lvl, $current_levels)) { // Only cancel levels that this user currently have.
                    // Send email notification.
                    // It has to be here because it resets every call of LevelCancelled.
                    if ('dontsend' !== $level_email) {
                        if ('sendlevel' !== $level_email) {
                            add_filter(
                                'wishlistmember_per_level_template_setting',
                                function ($value, $setting, $user_id, $level_id) {
                                    return 'cancel_notification' === $setting ? 1 : $value;
                                },
                                10,
                                4
                            );
                        }
                    } else {
                        // Disable cancel / uncancel email notif.
                        add_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                    }
                    wishlistmember_instance()->level_cancelled($trigger_lvl, $uid, true);
                    // Let's remove the filter.
                    remove_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                    remove_filter(
                        'wishlistmember_per_level_template_setting',
                        function ($value, $setting, $user_id, $level_id) {
                            return 'cancel_notification' === $setting ? 1 : $value;
                        },
                        10,
                        4
                    );
                }
            }
        } else {
            $levels_to_remove = [];
            $levels_to_add    = [];
            if ('add' === $action_value['level_action_method']) {
                $levels_to_add = $action_levels;
            } elseif ('remove' === $action_value['level_action_method']) {
                $levels_to_remove = $action_levels;
            } elseif ('move' === $action_value['level_action_method']) {
                // Move function will do add and remove of levels.
                $levels_to_remove[] = $trigger_lvl; // Remove from event level.
                $levels_to_add      = $action_levels;
            }

            /*
             * LET'S DO THE ADD AND REMOVE HERE
             *
             * we merge current levels with levels to be
             * automatically added and then we remove the
             * remainings levels that are to be automatically removed
             */
            $levels_for_set = array_unique(array_diff(array_merge($current_levels, $levels_to_add), $levels_to_remove));
            // We update the levels.
            $x_levels = [
                'Levels'            => array_unique($levels_for_set),
                'To_Removed_Levels' => array_unique($levels_to_remove),
                'Metas'             => [],
            ];
            if (1 === (int) $action_value['inheritparent']) { // We only add parent for ADD action.
                foreach ($levels_for_set as $key => $lvl) {
                    if (in_array($lvl, $levels_to_add)) { // If this level is newly added, we add parent meta.
                        $x_levels['Metas'][ $lvl ] = [['parent_level', $trigger_lvl]];
                    }
                }
            }
            $res = wishlistmember_instance()->set_membership_levels($uid, (object) $x_levels);
            // Fix the inaccurate time for level registration dates that was added/moved through level actions.
            if ('add' === $action_value['level_action_method'] || 'move' === $action_value['level_action_method']) {
                if (( 'after' === $action_value['sched_toggle'] && (int) $action_value['sched_after_term'] > 0 ) || 'ondate' === $action_value['sched_toggle']) {
                    // Update the registration date/time using the scheduled date/time for "scheduled on date".
                    if ('ondate' === $action_value['sched_toggle']) {
                        $gmt = get_option('gmt_offset');
                        if ($gmt >= 0) {
                            $gmt = '+' . $gmt;
                        }
                        $gmt                = ' ' . $gmt . ' GMT';
                        $update_regdatetime = strtotime($action_value['sched_ondate'] . $gmt);
                    } else {
                        /*
                         * update the registration date/time using the
                         * registration date/time of the level that triggered
                         * the action for "scheduled after period".
                         */
                        $user_levels        = new \WishListMember\User($uid);
                        $user_levels        = $user_levels->Levels;
                        $level_reg_datetime = wlm_arrval($user_levels, $trigger_lvl, 'Timestamp');
                        $update_period      = $action_value['sched_after_period'] ? $action_value['sched_after_period'] : 'days';
                        $update_regdatetime = strtotime('+' . $action_value['sched_after_term'] . ' ' . $update_period, $level_reg_datetime);
                    }
                    foreach ($action_levels as $key => $lvl) {
                        wishlistmember_instance()->user_level_timestamp($uid, $lvl, $update_regdatetime);
                    }
                }
            }
            // Send email notification.
            if (( 'add' === $action_value['level_action_method'] || 'move' === $action_value['level_action_method'] ) && count($levels_to_add) > 0 && 'dontsend' !== $level_email) {
                if ('send' === $level_email) {
                    add_filter(
                        'wishlistmember_per_level_template_setting',
                        function ($value, $setting, $user_id, $level_id) {
                            return in_array($setting, ['newuser_notification_user', 'newuser_notification_admin'], true) ? 1 : $value;
                        },
                        10,
                        4
                    );
                }
                foreach ($levels_to_add as $lvlid) {
                    $email_macros                                   = [
                        '[password]'    => '********',
                        '[memberlevel]' => $wpm_levels[ $lvlid ]['name'],
                    ];
                    wishlistmember_instance()->email_template_level = $lvlid;
                    wishlistmember_instance()->send_email_template('registration', $uid, $email_macros);

                    wishlistmember_instance()->email_template_level = $lvlid;
                    wishlistmember_instance()->send_email_template('admin_new_member_notice', $uid, $email_macros, wishlistmember_instance()->get_option('email_sender_address'));
                }
                // Let's remove the filter.
                if ('send' === $level_email) {
                    remove_filter(
                        'wishlistmember_per_level_template_setting',
                        function ($value, $setting, $user_id, $level_id) {
                            return in_array($setting, ['newuser_notification_user', 'newuser_notification_admin'], true) ? 1 : $value;
                        },
                        10,
                        4
                    );
                }
            }
        }
    }

    /**
     * Get scheduled level actions for a user
     *
     * @param  integer $uid User ID.
     * @return array    Array of scheduled level actions
     */
    public function get_user_scheduled_level_actions($uid = null)
    {
        global $wpdb;
        static $ids_with_actions = null;
        if (null === $ids_with_actions) {
            $ids_with_actions = $wpdb->get_col(
                $wpdb->prepare(
                    'SELECT DISTINCT `user_id` FROM `' . $wpdb->usermeta . '` WHERE meta_key LIKE %s',
                    'incoming_level_actions_%'
                )
            );
        }
        // No need to run the query if there are no scheduled actions.
        if (! in_array($uid, $ids_with_actions)) {
            return [];
        }

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM `' . $wpdb->usermeta . '` WHERE `user_id` LIKE %s AND meta_key LIKE %s ORDER BY meta_key DESC',
                empty($uid) ? '%' : $uid,
                'incoming_level_actions_%'
            ),
            ARRAY_A
        );
    }

    /**
     * Record user level actions
     *
     * @param integer $uid          User ID.
     * @param string  $lvlid        Level ID.
     * @param string  $action_value Action Value.
     */
    public function record_user_level_actions($uid, $lvlid, $action_value)
    {
        $wpm_levels               = wishlistmember_instance()->get_option('wpm_levels');
        $user_level_action_record = get_transient('user_level_action_record_' . $uid);
        $user_level_action_record = is_array($user_level_action_record) ? $user_level_action_record : [];
        $lvls                     = [];
        foreach ((array) $action_value['action_levels'] as $key => $lvl) {
            $lvls[] = $wpm_levels[ $lvl ]['name'];
        }
        $user_level_action_record[ $action_value['level_action_metaid'] ] = [
            'uid'         => $uid,
            'event'       => $action_value['level_action_event'],
            'event_level' => $wpm_levels[ $lvlid ]['name'],
            'method'      => $action_value['level_action_method'],
            'levels'      => implode(', ', $lvls),
        ];

        /*
         * lets save for a minute while processing
         * aside from 1 minute lifetime, we also delete this after schedule_user_level in wishlist-member3-actions.php
         */
        set_transient('user_level_action_record_' . $uid, $user_level_action_record, MINUTE_IN_SECONDS);
    }


    /**
     * Auto Remove From Feature hook for Add Action
     *
     * @param integer $uid            User ID.
     * @param array   $new_levels     New membership levels.
     * @param array   $removed_levels Removed membership levels.
     */
    public function do_auto_add_remove($uid, $new_levels = '', $removed_levels = '')
    {
        $new_levels     = (array) $new_levels;
        $removed_levels = (array) $removed_levels;
        $wlmuser        = new \WishListMember\User($uid);
        foreach ($removed_levels as $key => $value) {
            if (false !== strpos($value, 'U-')) {
                unset($removed_levels[ $key ]);
            }
        }
        foreach ($new_levels as $key => $value) {
            if (false !== strpos($value, 'U-')) {
                unset($new_levels[ $key ]);
            }
        }
        // Prevent infinite loop, dont run this for levels with parent.
        foreach ($new_levels as $key => $lvl) {
            if (isset($wlmuser->Levels[ $lvl ])) {
                if ($wlmuser->Levels[ $lvl ]->ParentLevel) {
                    unset($new_levels[ $key ]); // Dont do add remove for child levels.
                }
            } else {
                unset($new_levels[ $key ]); // Only add levels that user has.
            }
        }

        if (count($new_levels) <= 0 && count($removed_levels) <= 0) {
            return;
        }

        $wlmuser->DoAddRemove($new_levels, $removed_levels);
        wishlistmember_instance()->update_child_status($uid, $new_levels);
    }

    /**
     * Auto Remove From Feature hook for Remove action
     *
     * @param integer $uid    User ID.
     * @param array   $levels New Membership Levels.
     */
    public function remove_do_auto_add_remove($uid, $levels = '')
    {
        $levels  = (array) $levels;
        $wlmuser = new \WishListMember\User($uid);
        foreach ($levels as $key => $value) {
            if (false !== strpos($value, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        $wlmuser->DoAddRemove($levels, [], 'remove');
    }

    /**
     * Auto Remove From Feature hook for Cancel action
     *
     * @param integer $uid    User ID.
     * @param array   $levels New Membership Levels.
     */
    public function cancel_do_auto_add_remove($uid, $levels = '')
    {
        $levels  = (array) $levels;
        $wlmuser = new \WishListMember\User($uid);
        $wlmuser->DoAddRemove($levels, [], 'cancel');
    }

    /**
     * Remove Child of Parent Levels hook
     *
     * @param integer $uid            User ID.
     * @param array   $removed_levels Removed Membership Levels.
     * @param array   $new_levels     New Membership Levels.
     */
    public function do_remove_child_levels($uid, $removed_levels = [], $new_levels = [])
    {
        wishlistmember_instance()->remove_child_levels($uid, $removed_levels);
    }

    /**
     * Update Status of Child when Parent Levels changed hook
     *
     * @param integer $uid           User ID.
     * @param array   $parent_levels Removed Membership Levels.
     */
    public function do_update_child_status($uid, $parent_levels)
    {
        wishlistmember_instance()->update_child_status($uid, $parent_levels);
    }

    /**
     * Set Expire Status of Child with Parent Levels
     *
     * @param mixed   $expire_date Expire Status.
     * @param integer $uid         User ID.
     * @param array   $level       Removed Membership Level.
     */
    public function do_expire_child_status($expire_date, $uid, $level)
    {
        $p = wishlistmember_instance()->level_parent($level, $uid);
        if ($p) {
            $p_expire_date = wishlistmember_instance()->level_expire_date($p, $uid);
            if (false === $expire_date) {
                $expire_date = $p_expire_date;
            } elseif (false !== $p_expire_date && $p_expire_date < $expire_date) {
                $expire_date = $p_expire_date;
            }
        }
        return $expire_date;
    }
}
