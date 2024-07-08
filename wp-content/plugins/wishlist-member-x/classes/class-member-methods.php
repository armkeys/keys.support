<?php

/**
 * Member Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Member Methods trait
 */
trait Member_Methods
{
    use Member_Methods_Deprecated;

    /**
     * Save Members Data
     *
     * This function is called when updating information in Members tab
     * Data is expected in $_POST
     */
    public function save_members_data()
    {

        // $_POST data.
        $wpm_member_id            = wlm_post_data()['wpm_member_id'];
        $wpm_action               = wlm_post_data()['wpm_action'];
        $wpm_membership_to        = wlm_post_data()['wpm_membership_to'];
        $dp_move_level            = wlm_post_data()['dp_move_level'];
        $dp_add_level             = wlm_post_data()['dp_add_level'];
        $dp_remove_level          = wlm_post_data()['dp_remove_level'];
        $wpm_cancel_membership    = wlm_post_data()['wpm_cancel_membership'];
        $wpm_uncancel_membership  = wlm_post_data()['wpm_uncancel_membership'];
        $cancel_date              = wlm_post_data()['cancel_date'];
        $wpm_unconfirm_membership = wlm_post_data()['wpm_unconfirm_membership'];
        $wpm_confirm_membership   = wlm_post_data()['wpm_confirm_membership'];
        $wpm_unapprove_membership = wlm_post_data()['wpm_unapprove_membership'];
        $wpm_approve_membership   = wlm_post_data()['wpm_approve_membership'];
        $wpm_payperposts_to       = wlm_post_data()['wpm_payperposts_to'];
        $wpm_add_payperposts      = wlm_post_data()['wpm_add_payperposts'];
        $wpm_del_payperposts      = wlm_post_data()['wpm_del_payperposts'];
        $wpm_disable_sequential   = wlm_post_data()['wpm_disable_sequential'];
        $wpm_enable_sequential    = wlm_post_data()['wpm_enable_sequential'];
        $wpm_subscribe_email      = wlm_post_data()['wpm_subscribe_email'];
        $wpm_unsubscribe_email    = wlm_post_data()['wpm_unsubscribe_email'];
        $wpm_clear_scheduled      = wlm_post_data()['wpm_clear_scheduled'];
        $wpm_delete_member        = wlm_post_data()['wpm_delete_member'];
        $status                   = wlm_post_data()['status'];

        $force_sync = false;
        if (! $wpm_member_id) {
            $this->err = __('No users selected.', 'wishlist-member');
            return;
        }

        $$wpm_action = 1; // Create a variable using the value of $wpm_action as the variable name.

        $wpm_levels = $this->get_option('wpm_levels');
        if ($wpm_member_id) {
            if ((int) $wpm_membership_to) {
                // Set or Schedule a member to a certain level.
                switch ($wpm_action) {
                    case 'wpm_change_membership':
                        $this->schedule_to_level($wpm_action, $wpm_membership_to, $wpm_member_id, $dp_move_level);
                        $force_sync = true;
                        break;
                    case 'wpm_add_membership':
                        $this->schedule_to_level($wpm_action, $wpm_membership_to, $wpm_member_id, $dp_add_level);
                        $force_sync = true;
                        break;
                    case 'wpm_del_membership':
                        $this->schedule_to_level($wpm_action, $wpm_membership_to, $wpm_member_id, $dp_remove_level);
                        $force_sync = true;
                        break;
                    default:
                        break;
                }
                // Cancel/uncancel membership level.
                if ($wpm_cancel_membership || $wpm_uncancel_membership) {
                    $cancelled_or_not = $status ? __('Cancelled', 'wishlist-member') : __('Uncancelled', 'wishlist-member');
                    $status           = $wpm_cancel_membership ? true : false;
                    $todays_date      = strtotime(wlm_date('Y-m-d'));
                    $cdate_array      = explode('/', $cancel_date);
                    $cancel_date      = gmmktime(gmdate('H'), gmdate('i'), gmdate('s'), (int) $cdate_array[0], (int) $cdate_array[1], (int) $cdate_array[2]);

                    // Translators: 1: Value of $cancelled_or_not - either 'Cancelled' or 'Uncancelled', 2: Membership level name.
                    $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Selected members %1$s from %2$s.', 'wishlist-member'), $cancelled_or_not, $wpm_levels[ $wpm_membership_to ]['name']));

                    if ($cancel_date <= $todays_date && 'Cancelled' === $cancelled_or_not) {
                        $this->level_cancelled($wpm_membership_to, $wpm_member_id, $status);
                    } elseif ('Uncancelled' === $cancelled_or_not) {
                        $this->level_cancelled($wpm_membership_to, $wpm_member_id, $status);
                    } elseif ($cancel_date > $todays_date && 'Cancelled' === $cancelled_or_not) {
                        $this->schedule_level_deactivation($wpm_membership_to, $wpm_member_id, $cancel_date);
                        // Translators: 1: Membership level name, 2: Cancellation date.
                        $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Selected members will be Cancelled from %1$s on %2$s.', 'wishlist-member'), $wpm_levels[ $wpm_membership_to ]['name'], wlm_date('m/d/y', $cancel_date)));
                    }
                }

                // Unconfirm/confirm membership level.
                if ($wpm_unconfirm_membership || $wpm_confirm_membership) {
                    $status             = $wpm_unconfirm_membership ? true : false;
                    $unconfirmed_or_not = $status ? __('Unconfirmed', 'wishlist-member') : __('Confirmed', 'wishlist-member');
                    $this->level_unconfirmed($wpm_membership_to, $wpm_member_id, $status);
                    // Translators: 1: Value of $unconfirmed_or_not - either 'UnConfirmed' or 'Confirmed', 2: Membership level name.
                    $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Selected members %1$s for %2$s.', 'wishlist-member'), $unconfirmed_or_not, $wpm_levels[ $wpm_membership_to ]['name']));
                }

                // Unapprove/approve membership level.
                if ($wpm_unapprove_membership || $wpm_approve_membership) {
                    $status            = $wpm_unapprove_membership ? true : false;
                    $unapproved_or_not = $status ? __('Unapproved', 'wishlist-member') : __('Approved', 'wishlist-member');
                    $approval          = $this->level_for_approval($wpm_membership_to, $wpm_member_id, $status);
                    if ($wpm_approve_membership) {
                        foreach ($wpm_member_id as $m_id) {
                            $this->send_admin_approval_notification($m_id, $wpm_membership_to);
                        }
                    }
                    // Translators: 1: Value of $unapproved_or_not - either 'Unapproved' or 'Approved', 2: Membership level name.
                    $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Selected members %1$s for %2$s.', 'wishlist-member'), $unapproved_or_not, $wpm_levels[ $wpm_membership_to ]['name']));
                }
            }

            if ($wpm_payperposts_to) {
                $post_type = get_post_type($wpm_payperposts_to);
                if ($post_type) {
                    if ($wpm_add_payperposts || $wpm_del_payperposts) {
                        if ($wpm_add_payperposts) {
                            $this->add_post_users($post_type, $wpm_payperposts_to, $wpm_member_id);
                            // Translators: 1: Post title.
                            $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Post "%1$s" added to selected members', 'wishlist-member'), get_the_title($wpm_payperposts_to)));
                        } else {
                            $this->remove_post_users($post_type, $wpm_payperposts_to, $wpm_member_id);
                            // Translators: 1: Post title.
                            $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Post "%1$s" removed from selected members', 'wishlist-member'), get_the_title($wpm_payperposts_to)));
                        }
                    }
                }
            }

            // Turn sequential upgrade on or off.
            if ($wpm_disable_sequential || $wpm_enable_sequential) {
                $status            = $wpm_enable_sequential ? true : false;
                $sequential_or_not = $status ? __('ENABLED', 'wishlist-member') : __('DISABLED', 'wishlist-member');
                $this->is_sequential($wpm_member_id, $status);
                // Translators: 1: Value of $sequential_or_not - either 'ENABLED' or 'DISABLED'.
                $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Sequential Upgrade %1$s for selected members.', 'wishlist-member'), $sequential_or_not));
            }

            // Subscribe/unsubscribe members.
            if ($wpm_subscribe_email || $wpm_unsubscribe_email) {
                $sub_or_unsub = '';
                if ($wpm_subscribe_email) {
                    foreach ($wpm_member_id as $id) {
                        $this->Delete_UserMeta($id, 'wlm_unsubscribe');
                    }
                    $sub_or_unsub = 'subscribed to';
                } else {
                    foreach ($wpm_member_id as $id) {
                        $this->Update_UserMeta($id, 'wlm_unsubscribe', 1);
                        $this->send_unsubscribe_notification_to_user($id);
                    }
                    $sub_or_unsub = 'unsubscribed from';
                }

                // Translators: 1: Value of $sub_or_unsub - either 'subscribed to' or 'unsubscribed from'.
                $this->msg = sprintf('<strong>%s</strong>', sprintf(__('Selected members have been %s Email Broadcast.', 'wishlist-member'), $sub_or_unsub));
            }

            if ($wpm_clear_scheduled) {
                foreach ((array) $wpm_member_id as $id) {
                    $this->Delete_User_Scheduled_LevelsMeta($id);
                }
                $this->msg = sprintf('<strong>%s</strong>', __('Scheduled Actions where cleared for the selected members.', 'wishlist-member'));
            }

            // Delete selected members.
            if ($wpm_delete_member) {
                foreach ((array) $wpm_member_id as $id) {
                    if ($id > 1) {
                        $force_sync = true;
                        wp_delete_user($id, 1);
                    }
                }
                $this->msg = sprintf('<strong>%s</strong>', __('Selected members DELETED.', 'wishlist-member'));
            }
        }
        if ($force_sync) {
            $this->nodelete_user_hook = true;
        }
        $this->schedule_sync_membership($force_sync);
    }

    /**
     * Schedule a member to a certain level
     *
     * @param string $action     'wpm_add_membership', 'wpm_change_membership', 'wpm_del_membership'.
     * @param string $level      Level ID to schedule to.
     * @param array  $member_ids Array of User IDs.
     * @param string $date       Schedule date.
     * @param string $level_from Level ID to schedule from.
     */
    public function schedule_to_level($action, $level, $member_ids, $date = '', $level_from = '')
    {
        $wpm_levels       = $this->get_option('wpm_levels');
        $todays_date      = strtotime(gmdate('Y-m-d h:i A'));
        $sched_date       = strtotime($date);
        $message          = '';
        $schedule_type    = '';
        $meta_name        = '';
        $is_current_level = false;

        if ('wpm_add_membership' === $action) {
            $message       = __('ADDED to', 'wishlist-member');
            $schedule_type = 'add';
            $meta_name     = 'scheduled_add';
        } elseif ('wpm_change_membership' === $action) {
            $message       = __('MOVED to', 'wishlist-member');
            $schedule_type = 'move';
            $meta_name     = 'scheduled_move';
        } else {
            $message       = __('REMOVED from', 'wishlist-member');
            $schedule_type = 'remove';
            $meta_name     = 'scheduled_remove';
        }

        foreach ((array) $member_ids as $id) {
            $lvl_to_check = 'wpm_change_membership' === $action ? $level_from : $level;
            if ($this->is_level_scheduled($lvl_to_check, $id)) {
                continue; // Prevent schedule if the level has already a schedule.
            }

            $levels = $this->get_membership_levels($id);
            if ('wpm_del_membership' === $action) {
                // Checks if $date is a valid timestamp otherwise it would have a date format like this 12/23/2023 02:29 AM.
                // If it is a valid timestamp, we need to check if the date is in the past or matches the current time so we can.
                // Immediately remove the level.
                if (is_numeric($date) && (int) $date === $date) {
                    if ($date <= time()) {
                        $sched_date = 0;
                    }
                }

                // Make sure $sched_date is not a negative value.
                if ($sched_date < 0) {
                    $sched_date = 0;
                }

                if ($this->Get_UserLevelIndex($id, $level) && $sched_date) {
                    $is_current_level = true;
                } else {
                    $levels = array_diff($levels, (array) $level);
                }
            } elseif ('wpm_change_membership' === $action && ! $sched_date) {
                // We need to check if hes in the level before moving.
                if ($level_from) {
                    $k = array_search($level_from, $levels, true);
                    unset($levels[ $k ]);
                } else {
                    unset($levels);
                }
                $levels[] = $level;
            } else {
                $levels[] = $level;
            }
            $will_be = __(' were ', 'wishlist-member');
            $on_date = '';

            if ($sched_date > $todays_date) {
                $schedule_data = [
                    'date'              => wlm_date('Y-m-d h:i A', $sched_date),
                    'type'              => $schedule_type,
                    'level_email_notif' => wlm_post_data()['level_email'],
                ];
                if ($level_from) {
                    $schedule_data['level_from'] = $level_from;
                }

                $schedule_data['is_current_level'] = $is_current_level;

                if (! $this->Get_UserLevelIndex($id, $level)) {
                    $x_levels = [
                        'Levels' => array_unique($levels),
                        'Metas'  => [$level => [[$meta_name, $schedule_data]]],
                    ];
                    $this->set_membership_levels($id, (object) $x_levels, ['process_autoresponders' => false]);
                } else {
                    $this->Update_UserLevelMeta($id, $level, $meta_name, $schedule_data);
                }

                $will_be = __(' will be ', 'wishlist-member');
                $on_date = wlm_date('m/d/y', $sched_date);
            } else {
                $this->set_membership_levels($id, array_unique($levels));
                if ('wpm_add_membership' === $action && $sched_date) {
                    $this->user_level_timestamp($id, $level, $sched_date);
                    $this->do_sequential($id); // Do sequential.
                }
            }
        }

        $this->msg = sprintf(
            '<strong>%s</strong>',
            sprintf(
                // Translators: 1: $will_be (' were ', ' will be '), 2: $message ('ADDED to', 'MOVED to', 'REMOVED from'), 3: Membership level name, 4: ' on ' or '' depending on $on_date, 5: date scheduled.
                __('Selected members %1$s%2$s %3$s%4$s%5$s.', 'wishlist-member'),
                $will_be,
                $message,
                $wpm_levels[ $level ]['name'],
                $on_date ? __(' on ', 'wishlist-member') : '',
                $on_date
            )
        );
    }


    /**
     * Count Non-members
     *
     * @return integer Number of non-members found
     */
    public function non_member_count()
    {
        $total_users = new \WP_User_Query(
            [
                'number'      => 1,
                'count_total' => true,
                'blog_id'     => $GLOBALS['blog_id'],
            ]
        );
        if (! $total_users || ! $total_users->total_users) {
            /*
             * As much as possible, do not use count because sometimes total users are not correct due to following reason
             * - Assumes there are neither duplicated nor orphaned capabilities meta_values.
             * - Assumes role names are unique phrases
             * - https://core.trac.wordpress.org/browser/tags/5.1.1/src/wp-includes/user.php#L859
             * - https://wordpress.stackexchange.com/questions/218181/wordpress-has-a-trash-for-users-wrong-result-for-count-user-greater-than-expe/218184#218184
             */
            $total_users = count_users();
            $total_users = $total_users['total_users'];
        } else {
            $total_users = $total_users->total_users;
        }

        // Non member count = $total_users less member count.
        return abs($total_users - $this->member_count());
    }

    /**
     * Count Members
     *
     * @return integer Number of members found
     */
    public function member_count()
    {
        global $wpdb;

        $query = new \WP_User_Query();
        $query->prepare_query(
            [
                'fields'      => 'ID',
                'count_total' => false,
                'blog_id'     => $GLOBALS['blog_id'],
            ]
        );
        $query->query_where  .= " AND {$wpdb->users}.ID IN (SELECT DISTINCT `user_id` FROM `" . esc_sql($this->table_names->userlevels) . '`)';
        $query->query_fields  = 'COUNT(DISTINCT ' . $query->query_fields . ')';
        $query->query_orderby = '';
        $query->query();
        return $query->get_results()[0];
    }

    /**
     * Get Member IDs
     *
     * @param  array   $levels       (optional) Level IDs.
     * @param  boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID.
     * @param  boolean $countonly    (optional) True to return only the number of IDs found.
     * @return array                 Array of member IDs
     */
    public function member_ids($levels = null, $groupbylevel = null, $countonly = null)
    {
        global $wpdb;
        if (is_null($groupbylevel)) {
            $groupbylevel = false;
        }
        if (is_null($countonly)) {
            $countonly = false;
        }
        if (! is_null($levels)) {
            $levels = (array) $levels;
            foreach ($levels as $k => $v) {
                $levels[ $k ] = $v;
            }
        } else {
            $levels = \WishListMember\Level::get_all_levels();
        }

        $user_query = new \WP_User_Query();
        $user_query->prepare_query(
            [
                'fields'      => 'ID',
                'count_total' => false,
                'blog_id'     => $GLOBALS['blog_id'],
                'orderby'     => 'ID',
            ]
        );
        if ($countonly) {
            $user_query->query_fields = 'COUNT(DISTINCT ' . $user_query->query_fields . ')';
        } else {
            $user_query->query_fields = 'DISTINCT ' . $user_query->query_fields;
        }

        if ($groupbylevel) {
            $ids   = [];
            $where = $user_query->query_where;
            foreach ($levels as $level) {
                $user_query->query_where = $where . sprintf(" AND {$wpdb->users}.ID IN (SELECT DISTINCT `user_id` FROM `" . esc_sql($this->table_names->userlevels) . '` WHERE `level_id` = %d)', $level);
                $user_query->query();
                $ids[ $level ] = $user_query->get_results();
                if ($countonly) {
                    $ids[ $level ] = $ids[ $level ][0];
                }
            }
        } else {
            $levels_implode           = "'" . implode("','", $levels) . "'";
            $user_query->query_where .= " AND {$wpdb->users}.ID IN (SELECT DISTINCT `user_id` FROM `" . esc_sql($this->table_names->userlevels) . "` WHERE `level_id` IN ({$levels_implode}))";
            $user_query->query();
            $ids = $user_query->get_results();
            if ($countonly) {
                $ids = $ids[0];
            }
        }

        return $ids;
    }

    /**
     * Retrieve Member IDs by Status
     * note: 'status=active' is only accurate for calls with single level
     *
     * @param  string  $status       Any of cancelled, unconfirmed or forapproval.
     * @param  array   $levels       (optional ) Level IDs.
     * @param  boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID.
     * @param  boolean $countonly    (optional) True to return only the number of IDs found.
     * @return array                 Associative array of member ids array with status as keys
     */
    public function member_ids_by_status($status, $levels = null, $groupbylevel = null, $countonly = null)
    {
        global $wpdb;
        if (is_null($groupbylevel)) {
            $groupbylevel = false;
        }
        if (is_null($countonly)) {
            $countonly = false;
        }

        $status = trim(strtolower($status));
        if (! in_array($status, ['cancelled', 'unconfirmed', 'forapproval', 'active', 'with_active_level'], true)) {
            return false;
        }

        if (is_string($levels) && in_array($levels, ['members', 'incomplete'], true)) {
            $levels = null;
        }

        if (! is_null($levels)) {
            $levels = (array) $levels;
            foreach ($levels as $k => $v) {
                $levels[ $k ] = (int) $v;
            }
        } else {
            $levels = \WishListMember\Level::get_all_levels();
        }

        $levels_array = $levels;

        $select = 'DISTINCT `user_id`';
        if ($countonly) {
            $select = 'COUNT(DISTINCT `user_id`)';
        }

        /*
         * As of WLM 3.0 this is deprecated, "active" now refers to "with_active_level" = members are users with at least 1 active level.
         * Leaving this here for 2.9 compatibility.
         */
        // Special handling for active.
        if ('active' === $status) {
            $found = [];
            foreach (['cancelled', 'unconfirmed', 'forapproval'] as $s) {
                foreach ($levels as $level) {
                    $found = array_merge($found, $this->member_ids_by_status($s, $level, false, false));
                }
            }

            $expired = $this->expired_members_id();
            foreach ($expired as $l => $v) {
                if (in_array($l, $levels)) {
                    $found = array_merge($found, $v);
                }
            }

            $found = array_unique($found);
            if (empty($found)) {
                $found = [0];
            }

            if ($groupbylevel) {
                $ids = [];
                foreach ($levels as $level) {
                    $ids[ $level ] = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT %0s FROM `' . esc_sql($this->table_names->userlevels) . '` `ul` WHERE `ul`.`level_id`=%s AND `ul`.`user_id` NOT IN (' . implode(', ', array_fill(0, count($found), '%d')) . ')',
                            $select,
                            $level,
                            ...array_values($found)
                        )
                    );
                }
            } else {
                $ids = $wpdb->get_col(
                    $wpdb->prepare(
                        'SELECT %0s FROM `' . esc_sql($this->table_names->userlevels) . '` `ul` WHERE `ul`.`user_id` NOT IN (' . implode(', ', array_fill(0, count($found), '%d')) . ') AND `ul`.`level_id` IN (' . implode(', ', array_fill(0, count($levels), '%s')) . ')',
                        $select,
                        ...array_values($found),
                        ...array_values($levels)
                    )
                );
            }
            return $ids;
        }

        // We will be needing this on queries below for MU sites to make sure that only users of this site is returned.
        $user_query = new \WP_User_Query(
            [
                'fields'      => 'ID',
                'count_total' => false,
                'blog_id'     => $GLOBALS['blog_id'],
            ]
        );

        $not_in_query = [];

        /**
         * Query filter to:
         * - replace __user_query_request__ with the value of $user_query
         * - replace __not_in_query__ with the AND imploded value of $not_in_query
         *
         * @var    function
         * @uses   $user_query
         * @uses   $not_in_query
         * @param  string $query Query to filter.
         * @return string Filtered query.
         */
        $__user_query_fxn__ = function ($query) use (&$user_query, &$not_in_query) {
            $not_query_string = '';
            if (! empty($not_in_query)) {
                $not_query_string = ' AND ' . implode(' AND ', $not_in_query);
            }
            return str_replace(
                ['__user_query_request__', '__not_in_query__'],
                [$user_query->request, $not_query_string],
                $query
            );
        };
        // Add the filter.
        add_filter('query', $__user_query_fxn__);

        /**
         * For WLM 3.0 Active members are users with at least 1 active membership level
         */
        if ('with_active_level' === $status) {
            $wpm_levels = $this->get_option('wpm_levels');

            // Do not include those userlevel ids when getting the ids of users.
            if ($groupbylevel) {
                $ids = [];
                foreach ($levels as $levelid) {
                    if (! $levelid) {
                        // Skip for invalid level ids.
                        continue;
                    }

                    /**
                     * Lets reset the not_in_query for each level only.
                     * This will prevent a very long query caused by other levels
                     */
                    $not_in_query = [];
                    // Exclude unconfirmed, cancelled, forapproval and scheduled levels.
                    $not_in_query[] = $wpdb->prepare(
                        '`ul`.`ID` NOT IN (SELECT userlevel_id FROM `'
                        . esc_sql($this->table_names->userlevel_options)
                        . "` WHERE ( option_name IN ('unconfirmed','cancelled','forapproval') AND option_value = 1 ) OR option_name LIKE %s)",
                        'scheduled_%'
                    );

                    // Get userlevel ids with 'expired' for each.
                    if (! $wpm_levels[ $levelid ]['noexpire']) {
                        $expire_option = (int) $wpm_levels[ $levelid ]['expire_option'];
                        switch ($expire_option) {
                            case '2':
                                $date  = wlm_date('Y-m-d H:i:s', strtotime($wpm_levels[ $levelid ]['expire_date']));
                                $query = $wpdb->prepare(
                                    'SELECT lvl.ID FROM `' . esc_sql($this->table_names->userlevels) . '` AS lvl INNER JOIN `' . esc_sql($this->table_names->userlevel_options) . '` AS lvlop ON lvl.id=lvlop.userlevel_id WHERE lvl.level_id =%s AND DATEDIFF(%s, NOW()) <= 0',
                                    $levelid,
                                    $date
                                );
                                break;
                            default:
                                $calendar    = strtoupper(substr($wpm_levels[ $levelid ]['calendar'], 0, -1));
                                $expire_days = (int) $wpm_levels[ $levelid ]['expire'];
                                $query       = $wpdb->prepare(
                                    'SELECT lvl.ID FROM `' . esc_sql($this->table_names->userlevels) . '` AS lvl INNER JOIN `' . esc_sql($this->table_names->userlevel_options) . "` AS lvlop ON lvl.id=lvlop.userlevel_id WHERE lvl.level_id =%s AND lvlop.option_name = 'registration_date' AND DATE_ADD(SUBSTRING_INDEX(lvlop.option_value, '#', 1), INTERVAL %d %0s) < DATE_ADD(NOW(), INTERVAL 0 %0s)",
                                    $levelid,
                                    $expire_days,
                                    $calendar,
                                    $calendar
                                );
                        }
                        $not_in_query[] = "`ul`.`ID` NOT IN ({$query})";
                    }

                    $ids[ $levelid ] = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT %0s FROM `' . esc_sql($this->table_names->userlevels) . '` `ul` WHERE `ul`.`level_id`=%s __not_in_query__ AND `user_id` IN (__user_query_request__)',
                            $select,
                            $levelid
                        )
                    );
                }
            } else {
                $levelid_with_exp  = [];
                $level_query_where = [];
                foreach ($levels as $levelid) {
                    if (! $levelid) {
                        // Skip for invalid level ids.
                        continue;
                    }
                    if (! wlm_arrval($wpm_levels, $levelid, 'noexpire')) {
                        $expire_option = (int) wlm_arrval($wpm_levels, $levelid, 'expire_option');
                        switch ($expire_option) {
                            case '2':
                                $date = wlm_date('Y-m-d H:i:s', strtotime($wpm_levels[ $levelid ]['expire_date']));
                                $res  = $wpdb->get_col(
                                    $wpdb->prepare(
                                        'SELECT lvl.ID FROM `'
                                        . esc_sql($this->table_names->userlevels)
                                        . '` AS lvl INNER JOIN `'
                                        . esc_sql($this->table_names->userlevel_options)
                                        . '` AS lvlop ON lvl.id=lvlop.userlevel_id WHERE lvl.level_id =%s'
                                        . ' AND DATEDIFF(%s, NOW()) <= 0',
                                        $levelid,
                                        $date
                                    )
                                );

                                // WE'll be using this for the NOT IN query.
                                $level_query_where[] = "( lvl.level_id ={$levelid} AND DATEDIFF('{$date}', NOW()) <= 0 )";

                                break;
                            default:
                                $calendar    = strtoupper(substr($wpm_levels[ $levelid ]['calendar'], 0, -1));
                                $expire_days = (int) $wpm_levels[ $levelid ]['expire'];
                                $res         = $wpdb->get_col(
                                    $wpdb->prepare(
                                        'SELECT lvl.ID FROM `'
                                        . esc_sql($this->table_names->userlevels)
                                        . '` AS lvl INNER JOIN `'
                                        . esc_sql($this->table_names->userlevel_options)
                                        . "` AS lvlop ON lvl.id=lvlop.userlevel_id WHERE lvl.level_id =%s AND lvlop.option_name = 'registration_date'"
                                        . " AND DATE_ADD(SUBSTRING_INDEX(lvlop.option_value, '#', 1), INTERVAL %d %0s) < DATE_ADD(NOW(), INTERVAL 0 %0s)",
                                        $levelid,
                                        $expire_days,
                                        $calendar,
                                        $calendar
                                    )
                                );
                                // We'll be using this for the NOT IN query.
                                $level_query_where[] = "( lvl.level_id={$levelid} AND lvlop.option_name = 'registration_date' AND DATE_ADD(SUBSTRING_INDEX(lvlop.option_value, '#', 1), INTERVAL {$expire_days} {$calendar}) < DATE_ADD(NOW(), INTERVAL 0 {$calendar}) )";
                        }

                        if ($res) {
                            $levelid_with_exp = array_merge($res, $levelid_with_exp);
                        }
                    }
                }

                /*
                 * prepare query for expired ids
                 * this may have issue if it will produce a very long query due to mysql max_packet_size
                 * lets try to optimize it by first checking if there are expired IDs, if none, dont bother
                 */
                if (count($levelid_with_exp) > 0) {
                    $levelid_with_exp = array_unique($levelid_with_exp);
                    $exp_query_ids    = implode(',', $levelid_with_exp);

                    $exp_query_string = '`ul`.`ID` NOT IN ( SELECT lvl.ID FROM `'
                        . esc_sql($this->table_names->userlevels)
                        . '` AS lvl INNER JOIN `'
                        . esc_sql($this->table_names->userlevel_options)
                        . '` AS lvlop ON lvl.id=lvlop.userlevel_id WHERE '
                        . implode(' OR ', $level_query_where)
                        . ' )';

                    // Let's get the shortest query.
                    if (strlen($exp_query_ids) < strlen($exp_query_string)) {
                        $not_in_query[] = $wpdb->prepare(
                            '`ul`.`ID` NOT IN (' . implode(',', array_fill(0, count($levelid_with_exp), '%s')) . ')',
                            ...array_values($levelid_with_exp)
                        );
                    } else {
                        $not_in_query[] = $exp_query_string;
                    }
                }

                if (! $levels) {
                    $ids = [];
                } else {
                    $ids = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT %0s FROM `' . esc_sql($this->table_names->userlevels) . '` `ul` WHERE `ul`.`level_id` IN (' . implode(', ', array_fill(0, count($levels), '%s')) . ') __not_in_query__ AND `user_id` IN (__user_query_request__)',
                            $select,
                            ...array_values($levels)
                        )
                    );
                }
            }
        } elseif ($groupbylevel) {
            $ids = [];
            foreach ($levels as $level) {
                $ids[ $level ] = $wpdb->get_col(
                    $wpdb->prepare(
                        'SELECT %0s FROM `' . esc_sql($this->table_names->userlevels) . '` `ul` LEFT JOIN `' . esc_sql($this->table_names->userlevel_options) . "` `ulm` ON `ul`.`ID`=`ulm`.`userlevel_id` WHERE `ul`.`level_id` = %d AND `ulm`.`option_name`=%s AND `ulm`.`option_value`='1' AND `ul`.`user_id` IN (__user_query_request__) ORDER BY `ul`.`user_id`",
                        $select,
                        $level,
                        $status
                    )
                );
            }
        } else {
            $ids = $wpdb->get_col(
                $wpdb->prepare(
                    'SELECT %0s FROM `' . esc_sql($this->table_names->userlevels) . '` `ul` LEFT JOIN `' . esc_sql($this->table_names->userlevel_options) . '` `ulm` ON `ul`.`ID`=`ulm`.`userlevel_id` WHERE `ulm`.`option_name`=%s AND `ul`.`level_id` IN (' . implode(', ', array_fill(0, count($levels_array), '%s')) . ") AND `ulm`.`option_value`='1' AND `ul`.`user_id` IN (__user_query_request__) ORDER BY `ul`.`user_id`",
                    $select,
                    $status,
                    ...array_values($levels_array)
                )
            );
        }
        // Remove the filter.
        remove_filter('query', $__user_query_fxn__);
        return $ids;
    }

    /**
     * Return Active Member IDs (added 3.0)
     *
     * @param  array   $levels       (optional) Level IDs.
     * @param  boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID.
     * @param  boolean $countonly    (optional) True to return only the number of IDs found.
     * @return array                  Array of Active Member IDs (added 3.0)
     */
    public function active_member_ids($levels = null, $groupbylevel = null, $countonly = null)
    {
        return $this->member_ids_by_status('with_active_level', $levels, $groupbylevel, $countonly);
    }

    /**
     * Return Cancelled Member IDs
     *
     * @param  array   $levels       (optional) Level IDs.
     * @param  boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID.
     * @param  boolean $countonly    (optional) True to return only the number of IDs found.
     * @return array                 Array of Cancelled Member IDs
     */
    public function cancelled_member_ids($levels = null, $groupbylevel = null, $countonly = null)
    {
        return $this->member_ids_by_status('cancelled', $levels, $groupbylevel, $countonly);
    }

    /**
     * Return Unconfirmed Member IDs
     *
     * @param  array   $levels       (optional) Level IDs.
     * @param  boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID.
     * @param  boolean $countonly    (optional) True to return only the number of IDs found.
     * @return array                 Array of Unconfirmed Member IDs
     */
    public function unconfirmed_member_ids($levels = null, $groupbylevel = null, $countonly = null)
    {
        return $this->member_ids_by_status('unconfirmed', $levels, $groupbylevel, $countonly);
    }

    /**
     * Return For Approval Member IDs
     *
     * @param  array   $levels       (optional) Level IDs.
     * @param  boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID.
     * @param  boolean $countonly    (optional) True to return only the number of IDs found.
     * @return array                 Array of For Approval Member IDs
     */
    public function for_approval_member_ids($levels = null, $groupbylevel = null, $countonly = null)
    {
        return $this->member_ids_by_status('forapproval', $levels, $groupbylevel, $countonly);
    }

    /**
     * Synchronize Membership Data
     *
     * @param boolean $force_sync True to force a sync.
     */
    public function schedule_sync_membership($force_sync = false)
    {
        wp_schedule_single_event(time(), 'wishlistmember_syncmembership', [$force_sync, microtime()]);
    }

    /**
     * Is Pending returns true if at least one of the user's levels is for admin approval and false otherwise
     *
     * @param  integer $uid User ID.
     * @return boolean
     */
    public function is_pending($uid)
    {
        $user = new \WishListMember\User($uid);
        foreach ($user->Levels as $level) {
            if ($level->Pending) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get/Set User Sequential Upgrade status
     *
     * @param  array   $uid    User IDs.
     * @param  integer $status (optional) 0|1.
     * @return integer           0|1
     */
    public function is_sequential($uid, $status = null)
    {
        global $wpdb;
        $uid = (array) $uid;
        if (! is_null($status)) {
            $status = (int) $status;
            foreach ($uid as $id) {
                $this->Update_UserMeta((int) $id, 'sequential', $status);
            }
        }
        list($id) = $uid;
        return $this->Get_UserMeta($id, 'sequential');
    }

    /**
     * Get Active Levels of a Member
     *
     * @param  integer $id    User ID.
     * @param  boolean $names (optional) Default False. TRUE to return Level names instead of IDs.
     * @return array
     */
    public function get_member_active_levels($id, $names = false)
    {
        return (array) $this->get_membership_levels($id, $names, true);
    }

    /**
     * Get Inactive Levels of a Member
     *
     * @param  integer $id    User ID.
     * @param  boolean $names (optional) Default False. TRUE to return Level names instead of IDs.
     * @return array
     */
    public function get_member_inactive_levels($id, $names = false)
    {
        $all    = (array) $this->get_membership_levels($id, $names);
        $active = $this->get_member_active_levels($id, $names);
        return array_diff($all, $active);
    }

    /**
     * Return Member's Membership Levels
     *
     * @param  integer $id                User ID.
     * @param  boolean $names             (optional) Default False. TRUE to return Level names instead of IDs.
     * @param  boolean $active_only       (optional) Default False. TRUE to return active levels only.
     * @param  boolean $no_cache          (optional) Default False. TRUE to skip cache data.
     * @param  boolean $no_userlevels     (optional) Default False. TRUE to exclude User Level (U-xxx).
     * @param  boolean $include_scheduled (optional) Default TRUE. FALSE to exclude scheduled levels.
     * @return array                      Levels
     */
    public function get_membership_levels($id, $names = null, $active_only = null, $no_cache = null, $no_userlevels = null, $include_scheduled = null)
    {
        global $wpdb;
        $names         = is_null($names) ? false : $names;
        $active_only   = is_null($active_only) ? false : $active_only;
        $no_cache      = is_null($no_cache) ? false : $no_cache;
        $no_userlevels = is_null($no_userlevels) ? false : $no_userlevels;
        if (is_null($include_scheduled)) {
            $include_scheduled = true;
        }

        $levels = ( true === $no_cache ) ? false : wlm_cache_get($id, $this->table_names->userlevels);

        // Empty user == no membership levels.
        if (empty($id)) {
            return [];
        }

        if (false === $levels) {
            if ($include_scheduled) {
                $levels = $wpdb->get_col($wpdb->prepare('SELECT `level_id` FROM `' . esc_sql($this->table_names->userlevels) . '` WHERE `user_id`=%d ORDER BY ID', $id));
            } else {
                $levels = $wpdb->get_col($wpdb->prepare("SELECT `level_id`, MAX(`option_name`='scheduled_move' OR `option_name`='scheduled_add' OR `option_name`='scheduled_remove') `scheduled` FROM `" . esc_sql($this->table_names->userlevels) . '` `l` LEFT JOIN `' . esc_sql($this->table_names->userlevel_options) . '` `lo` ON `l`.`ID`=`lo`.`userlevel_id` WHERE `user_id`=%d GROUP BY `level_id` HAVING `scheduled`=0', $id));
            }
            // Lets make sure we have valid levels.
            foreach ((array) $levels as $key => $level) {
                if (! $level) {
                    unset($levels[ $key ]);
                }
            }
            wlm_cache_set($id, $levels, $this->table_names->userlevels);
        }

        /**
         * Filters the user's membership levels query result.
         *
         * @param array $levels Array of Level IDs.
         * @param integer $id User ID.
         */
        $levels = apply_filters('wishlistmember_get_membership_levels', $levels, $id);

        if ($names) {
            $wpm_levels = $this->get_option('wpm_levels');
            $names      = [];
            foreach ((array) $levels as $level) {
                $name = $wpm_levels[ $level ]['name'];
                if (
                    $this->level_cancelled($level, $id) || $this->level_for_approval($level, $id) || $this->level_unconfirmed($level, $id) || $this->level_expired($level, $id)
                ) {
                    $name = '<strike>' . $name . '</strike>';
                }
                $names[] = $name;
            }
            return implode(', ', $names);
        } else {
            if ($active_only) {
                foreach ((array) $levels as $key => $level) {
                    if (
                        $this->is_level_scheduled($level, $id) || $this->level_cancelled($level, $id) || $this->level_for_approval($level, $id) || $this->level_unconfirmed($level, $id) || $this->level_expired($level, $id)
                    ) {
                        unset($levels[ $key ]);
                    }
                }
                $levels = array_merge($levels, []);
            }
            if (! $no_userlevels) {
                // Force individual user level.
                $levels[] = 'U-' . $id;
            }
            return $levels;
        }
    }

    /**
     * Records User Pay Per Post History
     *
     * @param  integer      $uid    User ID.
     * @param  string       $action Add | Moved | Cancelled | Removed.
     * @param  integer      $postid Post Id.
     * @param  string       $type   Post Type.
     * @param  integer|null $time   Timestamp if needed.
     * @return boolean true on success, or false on error
     */
    public function record_user_ppp_history($uid, $action, $postid, $type, $time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        $value = [
            'action' => $action,
            'post'   => $postid,
            'type'   => $type,
        ];
        return \WishListMember\Logs::add($uid, 'ppp', $action, $value, $time);
    }

    /**
     * Records User Level History
     *
     * @param  integer      $uid     User ID.
     * @param  string       $action  Add | Moved | Cancelled | Removed.
     * @param  array        $levels  Membership Levels.
     * @param  string|null  $details Any additional details.
     * @param  integer|null $time    Timestamp if needed.
     * @return boolean true on success, or false on error
     */
    public function record_user_level_history($uid, $action, $levels, $details = null, $time = null)
    {
        $wpm_levels  = $this->get_option('wpm_levels');
        $level_names = [];
        $levels      = (array) $levels;
        // Lets remove pay per post.
        foreach ($levels as $key => $lvl) {
            if (false !== strpos($lvl, 'U-')) {
                unset($levels[ $key ]);
            }
            if (isset($wpm_levels[ $lvl ])) {
                $level_names[] = $wpm_levels[ $lvl ]['name'];
            } else {
                $level_names[] = $lvl;
            }
        }
        if (count($levels) <= 0) {
            return false;
        }

        // Sleep is added so when a level action is triggered immediately.
        // The original action is also recorded and not replaced because they have the same datetime value.
        usleep(1);

        if (is_null($time)) {
            $time = time();
        }

        $value = [
            'levels'      => $levels,
            'level_names' => implode(', ', $level_names),
            'details'     => $details,
        ];

        return \WishListMember\Logs::add($uid, 'level', $action, $value, $time);
    }

    /**
     * Sets Membership Levels for a user
     *
     * @param  integer $id      User ID.
     * @param  array   $levels  Array of Membership Levels.
     * @param  array   $options {
     *       An array of options.
     * @type   boolean $set_timestamp              Whether to set the membership level
     *                                               timestamp or not. Default true
     * @type   boolean $set_transaction_id         Whether to set the membership level
     *                                               transaction ID or not. Default true
     * @type   boolean $sync                       Whether to snyc membership level data or
     *                                               not. Default true
     * @type   boolean $keep_existing_levels       Whether to keep existing membership
     *                                               levels that are not included in the
     *                                               $levels array or not. Default false
     * @type   boolean $keep_existing_payperposts  Whether to keep existing pay per posts
     *                                               that are not not inckluded in the
     *                                               $levels array or not. Default true
     * @type   boolean $registration               Whether the call is coming from a
     *                                               registration or not. Default false
     * @type   boolean $process_webinars           Whether to process webinars or not.
     *                                               Default true
     * @type   boolean $process_other_integrations Whether to process other integrations or
     *                                               not. Default true
     * @type   boolean $process_autoresponders     Whether to process autoresponders or not.
     *                                               Default true
     * @type   boolean $pending_autoresponders     Array of pending autoresponder user level
     *                                               meta. Default array()
     * }
     * @return false|array {
     *     Associative array of Level IDs.
     * @type   array $added   Newly added membership
     * @type   array $removed Array of removed membership levels
     * }
     */
    public function set_membership_levels($id, $levels, $options = [])
    {
        global $wpdb;
        $options = wp_parse_args(
            (array) $options,
            [
                'set_timestamp'              => true,
                'set_transaction_id'         => true,
                'sync'                       => true,
                'keep_existing_levels'       => false,
                'keep_existing_payperposts'  => true,
                'registration'               => false,
                'process_webinars'           => true,
                'process_other_integrations' => true,
                'process_autoresponders'     => true,
                'pending_autoresponders'     => [],
            ]
        );

        unset($options['wpdb']);

        $set_timestamp              = wlm_arrval($options, 'set_timestamp');
        $set_transaction_id         = wlm_arrval($options, 'set_transaction_id');
        $sync                       = wlm_arrval($options, 'sync');
        $keep_existing_levels       = wlm_arrval($options, 'keep_existing_levels');
        $keep_existing_payperposts  = wlm_arrval($options, 'keep_existing_payperposts');
        $registration               = wlm_arrval($options, 'registration');
        $process_webinars           = wlm_arrval($options, 'process_webinars');
        $process_other_integrations = wlm_arrval($options, 'process_other_integrations');
        $process_autoresponders     = wlm_arrval($options, 'process_autoresponders');
        $pending_autoresponders     = wlm_arrval($options, 'pending_autoresponders');

        if (! $process_other_integrations) {
            do_action('wishlistmember_suppress_other_integrations');
        }

        $id = (int) $id;

        if (empty($id)) {
            return false;
        }

        if (is_object($levels)) {
            $levels_metas      = (array) $levels->Metas;
            $to_removed_levels = isset($levels->To_Removed_Levels) ? (array) $levels->To_Removed_Levels : [];
            $levels            = (array) $levels->Levels;
        } else {
            $levels_metas      = [];
            $to_removed_levels = [];
            $levels            = (array) $levels;
        }

        $this->set_pay_per_post($id, $levels, $keep_existing_payperposts);

        $wpm_levels = $this->get_option('wpm_levels');
        if (count($levels)) {
            // We now use the validate_levels method to clear the $levels array of invalid Level IDs.
            $validated = $this->validate_levels($levels, null, true, true, true);
            // At least one level was invalid so we stop.
            if (! $validated) {
                return false;
            }
        }

        $current_levels = $this->get_membership_levels($id, null, null, true);
        if ($keep_existing_levels) {
            $levels = array_unique(array_merge($current_levels, $levels));
        }

        $removed_levels = [];
        $new_levels     = [];
        $this->array_diff($levels, $current_levels, $removed_levels, $new_levels);
        $removed_levels = array_unique((array) $removed_levels);
        $new_levels     = array_unique((array) $new_levels);

        // Remove user levels.
        $new_levels     = array_filter(preg_replace('/^U-\d+$/', '', $new_levels));
        $removed_levels = array_filter(preg_replace('/^U-\d+$/', '', $removed_levels));

        /*
         * Check for child levels, dont remove them
         * Added this line for "Add To" feature to work.
         * Without this, "Add To" wont work on Registrations that uses temp accounts.
         * because leves resets after merge, deleting add to levels.
         * //fjpalawan
         */
        if (count($new_levels) <= 0 && $registration) {
            foreach ($current_levels as $key => $value) {
                $parent  = $this->level_parent($value, $id);
                $the_key = array_search($value, $removed_levels);
                if ($parent && false !== $the_key && in_array($parent, $levels)) {
                    unset($removed_levels[ $the_key ]); // Never removed the child of parent.
                }
            }
            $removed_levels = array_unique(array_merge($to_removed_levels, $removed_levels));
        }

        if (count($removed_levels)) {
            do_action('wishlistmember_pre_remove_user_levels', $id, $removed_levels);
            // Remove from removed_levels.
            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM `' . esc_sql($this->table_names->userlevels) . '` WHERE `user_id`=%d AND `level_id` IN (' . implode(', ', array_fill(0, count($removed_levels), '%s')) . ')',
                    $id,
                    ...array_values($removed_levels)
                )
            );

            $this->record_user_level_history($id, 'removed', $removed_levels);
        }

        // Add to new levels.
        foreach ((array) $new_levels as $level) {
            if (! is_numeric($level)) {
                // Skip non-numeric level IDs.
                continue;
            }
            $data = [
                'user_id'  => $id,
                'level_id' => $level,
            ];
            $wpdb->insert($this->table_names->userlevels, $data);
        }

        wlm_cache_delete($id, $this->table_names->userlevels);

        if (count($new_levels)) {
            $this->record_user_level_history($id, 'added', $new_levels);
            // Update timestamps.
            if ($set_timestamp) {
                $ts = array_combine($new_levels, array_fill(0, count($new_levels), time()));
                $this->user_level_timestamps($id, $ts);
            }
            // End timestamps update.
            // Set initial transaction id.
            if ($set_transaction_id) {
                $txn = array_combine($new_levels, array_fill(0, count($new_levels), ''));
                $this->set_membership_level_txn_ids($id, $txn);
            }
            // End setting initial transaction id.
            foreach ($new_levels as $new_level) {
                if (isset($levels_metas[ $new_level ])) {
                    foreach ((array) $levels_metas[ $new_level ] as $level_meta) {
                        if (is_array($level_meta) && 2 === count($level_meta)) {
                            list($meta, $value) = $level_meta;
                            $this->Update_UserLevelMeta($id, $new_level, $meta, $value);
                        }
                    }
                }
            }
        }

        // Autoresponder.
        if ($process_autoresponders) {
            $usr = $this->get_user_data($id);
            if ($usr->ID) {
                // Unsubscribe from autoresponder.
                foreach ((array) $removed_levels as $rl) {
                    $this->ar_unsubscribe($usr->first_name, $usr->last_name, $usr->user_email, $rl);
                }

                // If no flags we're set, add the member to AR list.
                if (empty($pending_autoresponders)) {
                    // Subscribe to autoresponder.
                    foreach ((array) $new_levels as $nl) {
                        if (! $this->level_cancelled($nl, $id)) {
                            $this->ar_subscribe($usr->first_name, $usr->last_name, $usr->user_email, $nl);
                        }
                    }
                } else {
                    foreach ($pending_autoresponders as $value) {
                        $this->Add_UserLevelMeta($id, $level, $value, 1);
                    }
                }
            }
        } else {
            // We now also set autoresponder on the temp account.
            foreach ((array) $pending_autoresponders as $value) {
                $this->Add_UserLevelMeta($id, $level, $value, 1);
            }
        }

        if ($process_webinars) {
            // Do webinar stuff.
            foreach ((array) $new_levels as $nl) {
                $this->webinar_subscribe($usr->first_name, $usr->last_name, $usr->user_email, $nl);
            }
        }

        wlm_cache_delete($id, $this->table_names->userlevels);

        // Trigger remove_user_levels action if a user is removed from at least one level.
        if (count($removed_levels)) {
            do_action('wishlistmember_remove_user_levels', $id, $removed_levels, $new_levels);
        }

        // Trigger add_user_levels action if a user is added to at least one level.
        if (count($new_levels)) {
            do_action('wishlistmember_add_user_levels', $id, $new_levels, $removed_levels, $pending_autoresponders);
            // Trigger wishlistmember_add_user_levels_shutdown on WordPress shutdown.
            add_action(
                'shutdown',
                function () use ($id, $new_levels, $removed_levels) {
                    /**
                     * `wishlistmember_add_user_levels_shutdown` Fires during the shutdown process
                     * This is similar to `wishlistmember_add_user_levels` except that it fires during
                     * the WP shutdown process allowing for time for further processing to be made to the
                     * level/s that were added
                     *
                     * @param integer $id              User ID
                     * @param array   $new_levels      Array of levels that were added to User ID
                     * @param array   $removed_levels  Array of levels that were removed from User ID
                     */
                    do_action('wishlistmember_add_user_levels_shutdown', $id, $new_levels, $removed_levels);
                }
            );
        }

        wlm_cache_delete($id, $this->table_names->userlevels);

        if ($sync) {
            $this->schedule_sync_membership();
        }

        return [
            'added'   => $new_levels,
            'removed' => $removed_levels,
        ];
    }

    /**
     * Get / Set User Level Timestamp
     *
     * @param  integer $id                            User ID.
     * @param  integer $level                         Level ID.
     * @param  integer $timestamp                     (optional) Timestamp.
     * @param  integer $adjust_user_registration_date (optional) Timestamp.
     * @return false|integer            Timestamp or false on failure
     */
    public function user_level_timestamp($id, $level, $timestamp = null, $adjust_user_registration_date = null)
    {
        static $uid, $ureg;

        $id = (int) $id;
        if ($uid !== $id) {
            $ureg = $this->get_user_data($id);
            $ureg = $this->user_registered($ureg, false);
            $uid  = $id;
        }
        // Moving this outside the if statement above and making $ulevels non static because it causes issue on seq upgrade build 1263.
        $ulevels = $this->get_membership_levels($id, false);

        if (! in_array($level, $ulevels)) {
            return false;
        }

        if (is_numeric($timestamp)) {
            if ($timestamp < $ureg) {
                if ($adjust_user_registration_date) {
                    $ureg = $timestamp;
                    wp_update_user(
                        [
                            'ID'              => $id,
                            'user_registered' => gmdate('Y-m-d H:i:s', $timestamp),
                        ]
                    );
                } else {
                    $timestamp = $ureg;
                }
            }
            $fraction  = $timestamp - (int) $timestamp;
            $timestamp = (int) $timestamp;
            $this->Update_UserLevelMeta($id, $level, 'registration_date', gmdate('Y-m-d H:i:s#' . $fraction, $timestamp));
        }

        list($date, $fraction) = array_pad(explode('#', (string) $this->Get_UserLevelMeta($id, $level, 'registration_date')), 2, null);
        if (empty($date)) {
            $ts = $ureg;
        } else {
            list($year, $month, $day, $hour, $minute, $second) = preg_split('/[- :]/', $date);
            $ts = gmmktime($hour, $minute, $second, $month, $day, $year) + $fraction;
            if ($ts < $ureg) {
                $ts = $ureg;
            }
        }
        return $ts;
    }

    /**
     * Get/Set Timestamps for a Member's Levels
     *
     * @param  integer $id     User ID.
     * @param  array   $levels Associative array of LevelID=>Timestamp pairs. If parameter passed is not an array then method will not set anything.
     * @return array         Associative array of LevelID=>Timestamp pairs
     */
    public function user_level_timestamps($id, $levels = null)
    {
        if (is_array($levels)) {
            foreach ($levels as $level_id => $timestamp) {
                $this->user_level_timestamp($id, $level_id, $timestamp);
            }
        }
        $levels = $this->get_membership_levels($id);
        $levels = array_flip($levels);
        foreach (array_keys($levels) as $level) {
            $ts               = $this->user_level_timestamp($id, $level);
            $levels[ $level ] = $ts;
        }
        asort($levels);
        return $levels;
    }

    /**
     * Move/Add Users from one Level to another
     *
     * Data is expected in $_POST
     */
    public function move_membership()
    {
        global $wpdb;

        // $_POST data.
        $wpm_from = wlm_post_data()['wpm_from'];
        $wpm_from = wlm_post_data()['wpm_from'];
        $wpm_move = wlm_post_data()['wpm_move'];
        $wpm_to   = wlm_post_data()['wpm_to'];
        $wpm_add  = wlm_post_data()['wpm_add'];
        $wpm_to   = wlm_post_data()['wpm_to'];
        $wpm_move = wlm_post_data()['wpm_move'];
        $wpm_add  = wlm_post_data()['wpm_add'];

        $wpm_levels = $this->get_option('wpm_levels');
        if ('NONMEMBERS' === $wpm_from) {
            $ids = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID` NOT IN (SELECT DISTINCT `user_id` FROM `" . esc_sql($this->table_names->userlevels) . '`)');
        } else {
            $ids = $this->member_ids($wpm_from);
        }
        if ($wpm_move) {
            foreach ($ids as $id) {
                $this->set_membership_levels($id, $wpm_to, ['process_autoresponders' => false]);
                echo '<!-- ' . esc_html($id) . ' -->';
            }
        } elseif ($wpm_add) {
            foreach ($ids as $id) {
                $levels   = $this->get_membership_levels($id);
                $levels[] = $wpm_to;
                $this->set_membership_levels($id, $levels, ['process_autoresponders' => false]);
                echo '<!-- ' . esc_html($id) . ' -->';
            }
        }
        if ($wpm_move || $wpm_add) {
            $force_sync = true;
            $this->schedule_sync_membership($force_sync);
            $this->msg = sprintf('<strong>%s</strong>', __('Membership level access updated.', 'wishlist-member'));
        }
    }

    /**
     * Queue members to be imported
     *
     * Data expected in $_POST
     */
    public function queue_import_members()
    {
        global $wpdb;
        ignore_user_abort(true);
        $wpm_levels = $this->get_option('wpm_levels');

        $post   = wlm_post_data(true); // Post data.
        $config = [
            'default_password'           => wlm_trim($post['password']),
            'import_membership_levels'   => ! empty($post['importmlevels']),
            'membership_levels'          => wlm_arrval($post, 'wpm_to'),
            'require_lastname'           => ! empty($post['require_lastname']),
            'require_firstname'          => ! empty($post['require_firstname']),
            'use_regdate'                => ! empty($post['use_regdate']),
            'duplicate_handling'         => wlm_arrval($post, 'duplicates'),
            'process_autoresponders'     => ! empty($post['process_autoresponders']),
            'process_other_integrations' => ! empty($post['process_other_integrations']),
            'email_notification'         => wlm_arrval($post, 'notify'),
        ];

        if (! $config['import_membership_levels'] && empty($config['membership_levels'])) {
            $this->err = __('Membership level(s) not specified.', 'wishlist-member');
            return;
        }

        if (wlm_arrval($_FILES, 'File', 'error') > 0) {
            $php_file_upload_errors = [
                0 => __('There is no error, the file uploaded with success', 'wishlist-member'),
                1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'wishlist-member'),
                2 => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'wishlist-member'),
                3 => __('The uploaded file was only partially uploaded', 'wishlist-member'),
                4 => __('No file was uploaded', 'wishlist-member'),
                6 => __('Missing a temporary folder', 'wishlist-member'),
                7 => __('Failed to write file to disk.', 'wishlist-member'),
                8 => __('A PHP extension stopped the file upload.', 'wishlist-member'),
            ];

            $this->err = $php_file_upload_errors[ wlm_arrval($_FILES, 'File', 'error') ];
            return;
        }

        if (is_uploaded_file(wlm_arrval($_FILES, 'File', 'tmp_name'))) {
            @ini_set('auto_detect_line_endings', 1);
            wlm_set_time_limit(0);

            $file            = fopen(wlm_arrval($_FILES, 'File', 'tmp_name'), 'r');
            $allowed_headers = $this->import_export_column_names(
                [
                    'with_password'            => true,
                    'with_date_added_to_level' => true,
                    'with_level'               => true,
                    'with_transaction_id'      => true,
                    'with_level_status'        => true,
                    'with_subscription_status' => true,
                    'with_address'             => true,
                ]
            );

            // Check headers.
            $separator = wlm_detect_csv_separator($file);
            $headers   = fgetcsv($file, 0, $separator);
            foreach ($headers as &$column) {
                $column = trim(str_replace('(optional)', '', $column));
            }
            unset($column);

            if (count($headers) !== count(array_unique($headers))) {
                $this->err = __('Duplicate column headers detected.', 'wishlist-member');
                return;
            }

            $main_headers         = $headers;
            $custom_fields_marker = array_search('__CUSTOM_FIELDS_MARKER__', $headers, true);
            if (false !== $custom_fields_marker) {
                $custom_fields_headers = array_diff(array_slice($headers, $custom_fields_marker + 1), ['']);
                $has_custom_fields     = count($custom_fields_headers) > 0;
                $main_headers          = array_slice($headers, 0, $custom_fields_marker);
            }

            if (false !== array_search('', $headers, true)) {
                $this->err = __('Empty column headers detected.', 'wishlist-member');
                return;
            }

            $invalid_headers = array_diff($main_headers, $allowed_headers);
            if (count($invalid_headers)) {
                $this->err = __('Invalid column header(s) detected.<ol><li>', 'wishlist-member') . implode('</li><li>', $invalid_headers) . '</li></ol>';
                return;
            }

            if (! in_array('username', $main_headers, true)) {
                $this->err = __('Required <b>username</b> column not found.', 'wishlist-member');
                return;
            }

            if (! in_array('email', $main_headers, true)) {
                $this->err = __('Required <b>email</b> column not found.', 'wishlist-member');
                return;
            }

            if (! in_array('firstname', $main_headers, true)) {
                $this->err = __('Required <b>firstname</b> column not found.', 'wishlist-member');
                return;
            }

            if (! in_array('lastname', $main_headers, true)) {
                $this->err = __('Required <b>lastname</b> column not found.', 'wishlist-member');
                return;
            }

            $index = array_flip($headers);

            // First pass - validate import file.
            $row_count         = 0;
            $valid_level_names = $this->get_option('wpm_levels');
            foreach ($valid_level_names as &$level) {
                $level = trim(strtoupper($level['name']));
            }
            unset($level);

            // Check each row.
            while ($row = fgetcsv($file, 0, $separator)) {
                ++$row_count;

                if (! wlm_trim($row[ $index['username'] ])) {
                    // Translators: 1: row number.
                    $this->err = sprintf(__('No <b>username</b> detected in row #%1$d.', 'wishlist-member'), $row_count);
                    return;
                }
                if (! wlm_trim($row[ $index['email'] ])) {
                    // Translators: 1: row number.
                    $this->err = sprintf(__('No <b>email</b> detected in row #%1$d.', 'wishlist-member'), $row_count);
                    return;
                }
                if ($config['require_firstname']) {
                    if (! wlm_trim($row[ $index['firstname'] ])) {
                        // Translators: 1: row number.
                        $this->err = sprintf(__('No <b>firstname</b> detected in row #%1$d.', 'wishlist-member'), $row_count);
                        return;
                    }
                }
                if ($config['require_lastname']) {
                    if (! wlm_trim($row[ $index['lastname'] ])) {
                        // Translators: 1: row number.
                        $this->err = sprintf(__('No <b>lastname</b> detected in row #%1$d.', 'wishlist-member'), $row_count);
                        return;
                    }
                }

                if ($config['import_membership_levels']) {
                    if (! wlm_trim($row[ $index['level'] ])) {
                        $this->err = __('You chose to auto-detect levels from the import file but not all rows in your import file have levels.', 'wishlist-member');
                        return;
                    }
                    $levels         = preg_split('/[,\r\n\t]/', preg_replace('/\s*,\s*/', ',', strtoupper($row[ $index['level'] ])));
                    $invalid_levels = array_diff($levels, $valid_level_names);
                    if ($invalid_levels) {
                        // Translators: 1: row number.
                        $this->err = sprintf(__('Invalid level(s) detected in row #%1$d.', 'wishlist-member'), $row_count) . '<ol><li>' . implode('</li><li>', $invalid_levels) . '</li></ol>';
                        return;
                    }
                }
            }

            // Validation done - let's go back to the first row and reset our row counter.
            rewind($file);
            fgetcsv($file, 0, $separator); // Skip header row.

            $api_queue   = new \WishListMember\API_Queue();
            $counter     = 0;
            $queue_count = $api_queue->count_queue('import_member_queue', 0); // Get current queue count.
            while ($row = fgetcsv($file, 0, $separator)) {
                // If $row has an array value that's not in utf8 then let's run utf8_encode function.
                // This prevents us from running utf8_encode on strings (eg. Hebrew text) that are already in utf8 format.
                if (! preg_grep('!!u', $row)) {
                    $row = array_map('utf8_encode', $row);
                }
                $data = [
                    'config'  => $config,
                    'data'    => $row,
                    'headers' => $headers,
                ];
                if ($api_queue->add_queue('import_member_queue', serialize($data))) {
                    ++$counter;
                }
            }
            $this->save_option('import_member_queue_count', $counter + $queue_count); // Old queue + new.
            $this->save_option('import_member_pause', 0);
            // Translators: 1: number of members being imported.
            $this->msg = sprintf('<p>%s</p>', sprintf(__('Importing %1$d members</p>', 'wishlist-member'), $counter));
        }
    }

    /**
     * Process members queued to be imported
     */
    public function process_import_members()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/user.php';

        $wpm_levels = $this->get_option('wpm_levels');

        /*
         * we use transient for $valid_level_names as generating it
         * can be resource intensive when there are many levels
         */
        $valid_level_names = get_transient('wlm3_valid_level_names');
        if (false === $valid_level_names) {
            $valid_level_names = $wpm_levels;
            foreach ($valid_level_names as &$level) {
                $level = trim(strtoupper($level['name']));
            }
            unset($level);
            set_transient('wlm3_valid_level_names', $valid_level_names, 300);
        }

        // Pause?
        if (1 == $this->get_option('import_member_pause')) {
            return false;
        }

        if (false !== get_transient('wlm_is_doing_import')) {
            return false;
        }

        $api_queue   = new \WishListMember\API_Queue();
        $queue_count = $api_queue->count_queue('import_member_queue', 0);
        if ($queue_count <= 0) {
            $this->save_option('import_member_queue_count', 0);
            return;
        }

        ignore_user_abort(true);
        $queue = $api_queue->get_queue('import_member_queue', 1, 0);
        $queue = count($queue) ? $queue[0] : false;
        if (! $queue) {
            return;
        } else {
            set_transient('wlm_is_doing_import', 1, MINUTE_IN_SECONDS);
        }

        $error_note = false;

        $member = unserialize($queue->value);

        if (! $member) {
            $error_note = 'Invalid Member Data';
        } else {
            $config  = $member['config'];
            $row     = $member['data'];
            $headers = $member['headers'];

            $default_password           = $config['default_password'];
            $import_membership_levels   = $config['import_membership_levels'];
            $membership_levels          = $config['membership_levels'];
            $require_lastname           = $config['require_lastname'];
            $require_firstname          = $config['require_firstname'];
            $use_regdate                = $config['use_regdate'];
            $duplicate_handling         = $config['duplicate_handling'];
            $process_autoresponders     = $config['process_autoresponders'];
            $process_other_integrations = $config['process_other_integrations'];
            $email_notification         = $config['email_notification'];

            $custom_fields_headers = [];
            $has_custom_fields     = false;
            $index                 = array_flip($headers);

            $main_headers         = $headers;
            $custom_fields_marker = array_search('__CUSTOM_FIELDS_MARKER__', $headers, true);
            if (false !== $custom_fields_marker) {
                $custom_fields_headers = array_diff(array_slice($headers, $custom_fields_marker + 1), ['']);
                $has_custom_fields     = count($custom_fields_headers) > 0;
            }

            $password_is_encrypted = false;
            $firstname             = wlm_trim($row[ $index['firstname'] ]);
            $lastname              = wlm_trim($row[ $index['lastname'] ]);
            $username              = wlm_trim($row[ $index['username'] ]);
            $email                 = wlm_trim($row[ $index['email'] ]);
            $password              = wlm_trim($row[ $index['password'] ]);
            $random_password       = false;
            if (empty($password)) {
                if ($default_password) {
                    $password = $default_password;
                } else {
                    $password        = $this->pass_gen();
                    $random_password = true;
                }
            }

            /*
             * Step 1: add or get user.
             */
            $username_exists = username_exists($username);
            $email_exists    = email_exists($email);

            $new_user   = false;
            $user       = false;
            $replace_id = 0;

            if ($username_exists || $email_exists) {
                switch ($duplicate_handling) {
                    case 'update': // Update meta and levels.
                    case 'update_levels': // Update levels.
                    case 'replace_levels': // Replace levels.
                    case 'replace': // Replace all information.
                        if ($email_exists) {
                            $user = get_user_by('email', $email);
                        } else {
                            $user = get_user_by('login', $username);
                        }

                        if ('replace' === $duplicate_handling) {
                            if (user_can($user->ID, 'manage_options')) {
                                // Translators: 1: username, 2: email.
                                $error_note = sprintf(__('Admin User not Replaced: - %1$s / %2$s', 'wishlist-member'), $username, $email);
                            } else {
                                $replace_id = $user->ID;
                                wp_delete_user($user->ID);
                                $wpdb->delete($this->table_names->userlevels, ['user_id' => $user->ID]);
                                $wpdb->delete($this->table_names->user_options, ['user_id' => $user->ID]);
                                $this->schedule_sync_membership();
                                $user = false;
                                ++$replaced_users;
                            }
                        }

                        break;
                    default: // Skip duplicates.
                        // Translators: 1: username, 2: email.
                        $error_note = sprintf(__('Duplicate Skipped: %1$s / %2$s', 'wishlist-member'), $username, $email);
                        break;
                }
            }

            if (! $error_note) {
                if (empty($user)) {
                    $user = wlm_insert_user(
                        [
                            'user_login' => $username,
                            'user_email' => $email,
                            'first_name' => $firstname,
                            'last_name'  => $lastname,
                            'user_pass'  => $password,
                        ]
                    );
                    if (is_wp_error($user)) {
                        // Translators: 1: username, 2: email.
                        $error_note = sprintf(__('Insert User Error: %1$s / %2$s', 'wishlist-member'), $username, $email);
                    } else {
                        // Update the password if it is already encrypted.
                        if (preg_match('/^___ENCPASS___(.+)?___ENCPASS___$/', $password, $match)) {
                            $password_is_encrypted = true;
                            $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->users}` SET `user_pass`=%s WHERE `ID`=%d", $match[1], $user));
                            clean_user_cache($user);
                        }

                        // Are we replacing a user?
                        if (! empty($replace_id)) {
                            $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->users}` SET `ID`=%d WHERE `ID`=%d", $replace_id, $user));
                            $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->usermeta}` SET `user_id`=%d WHERE `user_id`=%d", $replace_id, $user));
                            $user = $replace_id;
                        }

                        $new_user = true;
                        $user     = get_user_by('id', $user);
                    }
                }
            }

            if (! $error_note) {
                /*
                 * By this point, we already have $user.
                 * step 2: update user meta information if new or update.
                 */

                if ($new_user || 'update' === $duplicate_handling) {
                    // First name and last name.
                    wp_update_user(
                        [
                            'ID'         => $user->ID,
                            'first_name' => $firstname,
                            'last_name'  => $lastname,
                        ]
                    );
                    // Address.
                    $address         = $this->Get_UserMeta($user->ID, 'wpm_useraddress');
                    $address_changed = false;
                    foreach (['company', 'address1', 'address2', 'city', 'state', 'zip', 'country'] as $address_field) {
                        if (wlm_trim($row[ $index[ $address_field ] ])) {
                            $address[ $address_field ] = wlm_trim($row[ $index[ $address_field ] ]);
                            $address_changed           = true;
                        }
                    }

                    if ($address_changed) {
                        $this->Update_UserMeta($user->ID, 'wpm_useraddress', $address);
                    }

                    // Subscrption status.
                    $subscribed = wlm_boolean_value($row[ $index['subscribed'] ], true);
                    if ($subscribed) {
                        $this->Delete_UserMeta($user->ID, 'wlm_unsubscribe');
                    } else {
                        $this->Update_UserMeta($user->ID, 'wlm_unsubscribe', 1);
                    }

                    // Sequential status.
                    $sequential = wlm_boolean_value($row[ $index['sequential'] ], true);
                    $this->is_sequential($user->ID, $sequential);

                    // Custom fields.
                    if ($has_custom_fields) {
                        foreach ($custom_fields_headers as $custom_field) {
                            $custom_field = wlm_trim($custom_field);
                            $this->Update_UserMeta($user->ID, 'custom_' . $custom_field, wlm_trim(wlm_arrval($row, $index[ $custom_field ])));
                        }
                    }
                }

                /*
                 * step 3: add / update / replace membership levels
                 * also apply proper status flags, transaction ids and
                 * registration dates if specified in import file
                 */
                $keep_existing_levels = true;
                if (! $new_user) {
                    switch ($duplicate_handling) {
                        case 'replace_levels':
                            $keep_existing_levels = false;
                            break;
                        case 'update_levels':
                            $keep_existing_levels = true;
                            break;
                    }
                }

                if ($import_membership_levels || ( ! $import_membership_levels && $use_regdate )) {
                    if ($import_membership_levels) {
                        $membership_levels = preg_split('/[,\r\n\t]/', $row[ $index['level'] ]);
                        foreach ($membership_levels as &$level) {
                            $level = trim(strtoupper($level));
                            $level = array_search($level, $valid_level_names);
                        }
                    }
                    unset($level);
                    $transaction_ids = preg_split('/[,\r\n\t]/', $row[ $index['transaction_id'] ]);
                    $timestamps      = preg_split('/[,\r\n\t]/', $row[ $index['date_added_to_level'] ]);
                    $cancelled       = preg_split('/[,\r\n\t]/', $row[ $index['cancelled'] ]);
                    $cancelled_date  = preg_split('/[,\r\n\t]/', $row[ $index['cancellationdate'] ]);
                }

                $options = [
                    'process_autoresponders'     => $process_autoresponders,
                    'sync'                       => false,
                    'process_other_integrations' => $process_other_integrations,
                    'process_webinars'           => $process_other_integrations,
                    'keep_existing_levels'       => $keep_existing_levels,
                ];

                $changed_levels = $this->set_membership_levels($user->ID, $membership_levels, $options);

                if ($new_user) {
                    $role = array_intersect(array_keys($wpm_levels), wlm_or(wlm_arrval($changed_levels, 'added'), []));
                    $role = wlm_arrval($wpm_levels, array_pop($role), 'role');
                    if ($role) {
                        wp_update_user(
                            [
                                'ID'   => $user->ID,
                                'role' => $role,
                            ]
                        );
                    }
                }

                // Set transaction IDs and timestamps if we're importing levels from file.
                if ($import_membership_levels || ( ! $import_membership_levels && $use_regdate )) {
                    foreach ($membership_levels as $key => $level) {
                        $txnid = wlm_trim(wlm_arrval($transaction_ids, $key));
                        if (! empty($txnid)) {
                            $this->set_membership_level_txn_id($user->ID, $level, $txnid);
                        }
                        $ts = strtotime(wlm_trim(wlm_arrval($timestamps, $key)));
                        if ($ts > 0) {
                            $this->user_level_timestamp($user->ID, $level, $ts, true);
                            $this->record_user_level_history($user->ID, 'added', $level, null, $ts);
                        }

                        if (wlm_boolean_value(wlm_arrval($cancelled, $key), false)) {
                            $cancelled_date = strtotime(wlm_trim(wlm_arrval($cancelled_date, $key)));
                            $this->level_cancelled($level, $user->ID, true, $cancelled_date);
                        }
                    }
                }

                /*
                 * step 4: send email notifications if needed
                 */
                if ('do_not_send_email' === $email_notification) {
                    // We dont send any email as simple as that!
                    null;
                } elseif (( 'send_email_to_all_new_users' === $email_notification ) || ( $random_password )) {
                    $wpm_levels    = $this->get_option('wpm_levels');
                    $member_levels = [];
                    foreach ($changed_levels['added'] as $level) {
                        $member_levels[] = $wpm_levels[ $level ]['name'];
                    }

                    $macros = [
                        '[memberlevel]' => implode(', ', $member_levels),
                        '[password]'    => $password_is_encrypted ? '********' : $password,
                    ];

                    if (! $new_user) {
                        $macros['[password]'] = '********';
                    }

                    $this->send_email_template('registration', $user->ID, $macros);
                }
                $this->schedule_sync_membership();
            }
        }

        if ($error_note) {
            $d = [
                'notes' => $error_note,
                'tries' => $queue->tries + 1,
            ];
            $api_queue->update_queue($queue->ID, $d);
        } else {
            $api_queue->delete_queue($queue->ID);
            $queue_count = $api_queue->count_queue('import_member_queue', 0);
            if (! $queue_count || $queue_count <= 0) {
                $this->save_option('import_member_queue_count', 0);
            }
        }

        delete_transient('wlm_is_doing_import');

        // Let process her again.
        $url = get_home_url() . '?wlmprocessimport=1';
        wp_remote_get(
            $url,
            [
                'timeout'  => 10,
                'blocking' => false,
            ]
        );
    }

    /**
     * Export members into a CSV for immediate download
     *
     * Data in $_POST
     */
    public function export_members_chunked()
    {
        global $wpdb;

        wp_raise_memory_limit('export_members_chunked');

        $post = wlm_post_data(true); // phpcs:ignore WordPress.Security.NonceVerification.Missing.

        $wpm_to           = (array) wlm_arrval($post, 'wpm_to');
        $full_data_export = 1 == wlm_arrval($post, 'full_data_export');
        $include_password = 1 == wlm_arrval($post, 'include_password');
        $include_inactive = 1 == wlm_arrval($post, 'include_inactive');
        $per_page         = wlm_arrval($post, 'per_page');
        $current_page     = (int) wlm_arrval($post, 'current_page');

        $fname = 'members_' . wlm_date('Ymd_His') . '.csv';

        $search_results_count = 0;
        $search_results       = [];

        $include_nonmembers = in_array('nonmember', $wpm_to, true);

        if (count($wpm_to) <= 0) {
            echo wp_json_encode(['error' => __('Please select a membership level to export.', 'wishlist-member')]);
            die();
        }

        $wpm_to = array_diff($wpm_to, ['nonmember']);

        if ($wpm_to) {
            $ids                   = $this->member_ids($wpm_to);
            $search_results_count += count($ids);
            $search_results        = array_merge($search_results, $ids);
        }
        if ($include_nonmembers) {
            $query = new \WP_User_Query(
                [
                    'fields'      => 'ID',
                    'count_total' => false,
                    'blog_id'     => $GLOBALS['blog_id'], // For this blog only (MU fix).
                ]
            );
            $ids   = $wpdb->get_col(
                'SELECT `ID` FROM `'
                . $wpdb->users
                . '` WHERE `ID` NOT IN (SELECT DISTINCT `user_id` FROM `'
                . esc_sql($this->table_names->userlevels)
                . '`) AND `ID` IN ('
                . $wpdb->last_query
                . ')'
            );

            $search_results_count += count($ids);
            $search_results        = array_merge($search_results, $ids);
        }

        if ($search_results_count <= 0) {
            echo json_encode(['error' => __('Nothing to export', 'wishlist-member')]);
            die();
        }

        $total          = count($search_results);
        $current_page   = (int) $current_page;
        $per_page       = (int) $per_page;
        $search_results = array_slice($search_results, $current_page * $per_page, $per_page);
        $has_more       = $current_page + 1 < $total / $per_page;
        $exported       = count($search_results);
        $tmpname        = sys_get_temp_dir() . '/export-chunked-' . wlm_post_data()[get_wlm_nonce_field_name()];

        if (count($search_results)) {
            $f = fopen($tmpname, 'a');

            // Prepare column headers.
            $column_header_settings = [];
            if ($include_password) {
                $column_header_settings['with_password'] = true;
            }
            $column_header_settings['with_level'] = true;

            if ($full_data_export) {
                $column_header_settings['with_transaction_id']      = true;
                $column_header_settings['with_date_added_to_level'] = true;
                $column_header_settings['with_level_status']        = true;
                $column_header_settings['with_subscription_status'] = true;
                $column_header_settings['with_address']             = true;
                $column_header_settings['with_custom_fields']       = true;
            }
            $wl_address_fields = ['company', 'address1', 'address2', 'city', 'state', 'zip', 'country'];
            $column_headers    = $this->import_export_column_names($column_header_settings);

            if ($full_data_export) {
                $custom_fields = array_search('__CUSTOM_FIELDS_MARKER__', $column_headers, true);

                // Let's remove the duplicate wpm user address that appears after __CUSTOM_FIELDS_MARKER__.
                foreach ($column_headers as $key => $c_header) {
                    if ($key > $custom_fields) {
                        if (in_array($c_header, $wl_address_fields)) {
                            unset($column_headers[ $key ]);
                        }
                    }
                }

                if (false !== $custom_fields) {
                    $custom_fields = array_slice($column_headers, $custom_fields + 1);
                }
            }

            if (0 === (int) $current_page) {
                fputcsv($f, $column_headers, ',', '"');
            }

            $data_template = array_combine($column_headers, array_fill(0, count($column_headers), ''));

            foreach ((array) $search_results as $uid) {
                $data = $data_template;

                $wlm_user         = new \WishListMember\User($uid, null, true);
                $user             = $this->get_user_data($uid);
                $wlm_ulevelactive = false;
                $wpm_ulevel       = [];

                foreach ($wpm_to as $k => $wlm_to) {
                    if ($include_inactive || ( ! $include_inactive && $wlm_user->Levels[ $wlm_to ]->Active )) {
                        $wpm_ulevel[]     = $wlm_user->Levels[ $wlm_to ]->Name;
                        $wlm_ulevelactive = true;
                    }
                }
                $wlm_ulevel = implode("\n", array_filter($wpm_ulevel));
                unset($wpm_ulevel);
                if ($include_inactive || $wlm_ulevelactive || $include_nonmembers) {
                    $data['username']  = $user->user_login;
                    $data['firstname'] = $user->first_name;
                    $data['lastname']  = $user->last_name;
                    $data['email']     = $user->user_email;
                    $data['level']     = $wlm_ulevel;

                    if ($include_password) {
                        $data['password'] = '___ENCPASS___' . $user->user_pass . '___ENCPASS___';
                    }

                    if ($full_data_export) {
                        $wlm_txnID          = [];
                        $wlm_gmdate         = [];
                        $wlm_active         = [];
                        $wlm_active         = [];
                        $wlm_pending        = [];
                        $wlm_cancelled      = [];
                        $wlm_cancelled_date = [];
                        $wlm_unconfirmed    = [];
                        $wlm_expired        = [];
                        $wlm_expirydate     = [];

                        foreach ($wpm_to as $k => $wlm_to) {
                            if ($include_inactive || ( ! $include_inactive && $wlm_user->Levels[ $wlm_to ]->Active )) {
                                if (isset($wlm_user->Levels[ $wlm_to ])) {
                                    $date_and_time_format = get_option('date_format') . ' ' . get_option('time_format');

                                    $wlm_txnID[]          = $wlm_user->Levels[ $wlm_to ]->TxnID;
                                    $wlm_gmdate[]         = gmdate($date_and_time_format, $wlm_user->Levels[ $wlm_to ]->Timestamp);
                                    $wlm_active[]         = $wlm_user->Levels[ $wlm_to ]->Active ? 'Y' : 'N';
                                    $wlm_pending[]        = $wlm_user->Levels[ $wlm_to ]->Pending ? 'Y' : 'N';
                                    $wlm_cancelled[]      = $wlm_user->Levels[ $wlm_to ]->Cancelled ? 'Y' : 'N';
                                    $wlm_cancelled_date[] = $wlm_user->Levels[ $wlm_to ]->CancelledDate ? gmdate($date_and_time_format, $wlm_user->Levels[ $wlm_to ]->CancelledDate) : '';
                                    $wlm_unconfirmed[]    = $wlm_user->Levels[ $wlm_to ]->UnConfirmed ? 'Y' : 'N';
                                    $wlm_expired[]        = $wlm_user->Levels[ $wlm_to ]->Expired ? 'Y' : 'N';
                                    $wlm_expirydate[]     = $wlm_user->Levels[ $wlm_to ]->ExpiryDate ? gmdate($date_and_time_format, $wlm_user->Levels[ $wlm_to ]->ExpiryDate) : '';
                                }
                            }
                        }

                        $data['transaction_id']      = implode("\n", array_filter($wlm_txnID));
                        $data['date_added_to_level'] = implode("\n", array_filter($wlm_gmdate));
                        $data['active']              = implode("\n", array_filter($wlm_active));
                        $data['cancelled']           = implode("\n", array_filter($wlm_cancelled));
                        $data['cancellationdate']    = implode("\n", array_filter($wlm_cancelled_date));
                        $data['forapproval']         = implode("\n", array_filter($wlm_pending));
                        $data['forconfirmation']     = implode("\n", array_filter($wlm_unconfirmed));
                        $data['expired']             = implode("\n", array_filter($wlm_expired));
                        $data['expiry']              = implode("\n", array_filter($wlm_expirydate));
                        $data['company']             = wlm_arrval($user->wpm_useraddress, 'company');
                        $data['address1']            = wlm_arrval($user->wpm_useraddress, 'address1');
                        $data['address2']            = wlm_arrval($user->wpm_useraddress, 'address2');
                        $data['city']                = wlm_arrval($user->wpm_useraddress, 'city');
                        $data['state']               = wlm_arrval($user->wpm_useraddress, 'state');
                        $data['zip']                 = wlm_arrval($user->wpm_useraddress, 'zip');
                        $data['country']             = 'Select Country' === wlm_arrval($user->wpm_useraddress, 'country') ? '' : wlm_arrval($user->wpm_useraddress, 'country');
                        $data['subscribed']          = $user->wlm_unsubscribe ? 'N' : 'Y';

                        foreach ($custom_fields as $custom_field) {
                            // Continue if the $custom_field's name is on the array as it's overriding the.
                            // WishList Member Address Fields.
                            if (in_array($custom_field, $wl_address_fields)) {
                                continue;
                            }

                            $fld                   = 'custom_' . $custom_field;
                            if (is_string($user->$fld)) {
                                $data[ $custom_field ] = stripslashes($user->$fld);
                            } else {
                                $data[ $custom_field ] = $user->$fld;
                            }

                            if (is_array($data[ $custom_field ])) {
                                $data[ $custom_field ] = implode(',', $data[ $custom_field ]);
                            }
                        }
                    }

                    unset($wlm_txnID, $wlm_gmdate, $wlm_active, $wlm_active, $wlm_pending, $wlm_cancelled, $wlm_unconfirmed, $wlm_expired, $wlm_expirydate, $wlm_cancelled_date);

                    fputcsv($f, $data, ',', '"');
                }
            }
            fclose($f);
            echo wp_json_encode(compact('total', 'current_page', 'per_page', 'nonce', 'has_more', 'exported'));
        } else {
            header('Content-type:text/csv');
            header('Content-disposition: attachment; filename=' . $fname);
            $handle = fopen($tmpname, 'r');
            while (! feof($handle)) {
                stream_copy_to_stream($handle, WLM_STDOUT, 1024);
                flush();
            }
            fclose($handle);

            // Clear the contents.
            $handle = fopen($tmpname, 'w');
            fclose($handle);
        }
        exit;
    }

    /**
     * Generate and output Sample Import CSV for immediate download
     */
    public function sample_import_csv()
    {
        header('Content-type:text/csv');
        header('Content-disposition: attachment; filename=import_file_template.csv');

        $headers = [
            'with_password'            => true,
            'with_date_added_to_level' => true,
            'with_level'               => true,
            'with_transaction_id'      => true,
            'with_level_status'        => true,
            'with_subscription_status' => true,
            'with_address'             => true,
            'with_custom_fields'       => true,
        ];

        $comma = '';
        foreach ($this->import_export_column_names($headers, true) as &$field) {
                fwrite(WLM_STDOUT, $comma . '"' . str_replace('"', '""', $field) . '"');
            $comma = ',';
        }
        unset($field);
        exit;
    }

    /**
     * Generate column headers for member import/export file
     *
     * @param  array   $column_header_settings array specifying which extra column headers to include.
     *                                         Keys are: with_password, with_date_added_to_level, with_level,
     *                                         with_transaction_id, with_level_status, with_subscription_status,
     *                                         with_address and with_custom_fields.
     * @param  boolean $for_sample_data        (optional) default false.
     * @return array                         Array of column names for import/export CSV
     */
    public function import_export_column_names($column_header_settings = [], $for_sample_data = null)
    {

        $for_sample_data = (bool) $for_sample_data;

        $with_password            = ! empty($column_header_settings['with_password']);
        $with_date_added_to_level = ! empty($column_header_settings['with_date_added_to_level']);
        $with_level               = ! empty($column_header_settings['with_level']);
        $with_transaction_id      = ! empty($column_header_settings['with_transaction_id']);
        $with_level_status        = ! empty($column_header_settings['with_level_status']);
        $with_subscription_status = ! empty($column_header_settings['with_subscription_status']);
        $with_address             = ! empty($column_header_settings['with_address']);
        $with_custom_fields       = ! empty($column_header_settings['with_custom_fields']);

        $columns = ['username', 'firstname', 'lastname', 'email'];
        if ((bool) $with_password) {
            $columns[] = 'password' . ( $for_sample_data ? ' (optional)' : '' );
        }
        if ((bool) $with_date_added_to_level) {
            $columns[] = 'date_added_to_level' . ( $for_sample_data ? ' (optional)' : '' );
        }
        if ((bool) $with_level) {
            $columns[] = 'level' . ( $for_sample_data ? ' (optional)' : '' );
        }
        if ((bool) $with_transaction_id) {
            $columns[] = 'transaction_id' . ( $for_sample_data ? ' (optional)' : '' );
        }
        if ((bool) $with_level_status) {
            if (! $for_sample_data) {
                $columns[] = 'active';
            }
            $columns[] = 'cancelled' . ( $for_sample_data ? ' (optional)' : '' );
            if (! $for_sample_data) {
                $columns[] = 'cancellationdate';
                $columns[] = 'forapproval' . ( $for_sample_data ? ' (optional)' : '' );
                $columns[] = 'forconfirmation' . ( $for_sample_data ? ' (optional)' : '' );
                $columns[] = 'expired';
                $columns[] = 'expiry';
            }
        }
        if ((bool) $with_address) {
            $columns[] = 'company' . ( $for_sample_data ? ' (optional)' : '' );
            $columns[] = 'address1' . ( $for_sample_data ? ' (optional)' : '' );
            $columns[] = 'address2' . ( $for_sample_data ? ' (optional)' : '' );
            $columns[] = 'city' . ( $for_sample_data ? ' (optional)' : '' );
            $columns[] = 'state' . ( $for_sample_data ? ' (optional)' : '' );
            $columns[] = 'zip' . ( $for_sample_data ? ' (optional)' : '' );
            $columns[] = 'country' . ( $for_sample_data ? ' (optional)' : '' );
        }
        if ((bool) $with_subscription_status) {
            $columns[] = 'subscribed' . ( $for_sample_data ? ' (optional)' : '' );
        }
        if ((bool) $with_custom_fields) {
            $columns[] = '__CUSTOM_FIELDS_MARKER__';
            if ($for_sample_data) {
                $columns[] = 'custom_field_name_1';
                $columns[] = 'custom_field_name_2';
                $columns[] = 'custom_field_name_3';
            } else {
                $custom_fields = array_keys((array) $this->get_custom_reg_fields(null, true));
                $columns       = array_merge($columns, $custom_fields);
            }
        }
        return $columns;
    }

    /**
     * Return arrays of user ids for each level
     *
     * If $count_only is true then simply return the number of expired IDs
     *
     * @param  boolean $count_only         True to only return the number of expired IDs.
     * @param  string  $specified_level_id Level ID.
     * @return integer|array                      Number of Expired IDs or array( ...level_ids => array( ...user_ids ) )
     */
    public function expired_members_id($count_only = false, $specified_level_id = null)
    {
        global $wpdb;
        $wpm_levels = $this->get_option('wpm_levels');
        $ids        = [];

        if ($specified_level_id) {
            $ids[ $specified_level_id ] = $count_only ? 0 : [];
            if (! isset($wpm_levels[ $specified_level_id ])) {
                return $ids;
            }
            $wpm_levels = [$specified_level_id => $wpm_levels[ $specified_level_id ]];
        }

        foreach ((array) $wpm_levels as $levelid => $thelevel) {
            if (! $levelid) {
                // Skip for invalid level ids.
                continue;
            }

            $ids[ $levelid ] = $count_only ? 0 : [];

            if (! wlm_arrval($thelevel, 'noexpire')) {
                $usrlvltbl    = $this->table_names->userlevels;
                $usrlvlopttbl = $this->table_names->userlevel_options;

                $select_item = $count_only ? 'COUNT(DISTINCT `lvl`.`user_id`) AS `user_id`' : 'DISTINCT `lvl`.`user_id`';
                switch ((int) wlm_arrval($thelevel, 'expire_option')) {
                    case 2:
                        $date     = wlm_date('Y-m-d H:i:s', strtotime($thelevel['expire_date']));
                        $user_ids = $wpdb->get_col(
                            $wpdb->prepare(
                                'SELECT %0s FROM `'
                                . esc_sql($usrlvltbl)
                                . '` as `lvl` INNER JOIN `'
                                . esc_sql($usrlvlopttbl)
                                . '` as `lvlop` ON `lvl`.`id`=`lvlop`.`userlevel_id` WHERE `lvl`.`level_id`=%s AND `lvlop`.`option_name` = "registration_date" AND DATEDIFF(%s, NOW()) <= 0',
                                $select_item,
                                $levelid,
                                $date
                            )
                        );
                        break;
                    default:
                        $calendar = strtoupper(substr($thelevel['calendar'], 0, -1));
                        $user_ids = $wpdb->get_col(
                            $wpdb->prepare(
                                'SELECT %0s FROM `'
                                . esc_sql($usrlvltbl)
                                . '` as `lvl` INNER JOIN `'
                                . esc_sql($usrlvlopttbl)
                                . "` as `lvlop` ON `lvl`.`id`=`lvlop`.`userlevel_id` WHERE `lvl`.`level_id`=%s AND DATE_ADD(SUBSTRING_INDEX(`lvlop`.`option_value`, '#', 1), INTERVAL %d %0s ) < DATE_ADD(NOW(), INTERVAL 0 %0s)",
                                $select_item,
                                $levelid,
                                $thelevel['expire'],
                                $calendar,
                                $calendar
                            )
                        );
                }

                if ($count_only) {
                    $ids[ $levelid ] = $user_ids[0];
                } else {
                    $ids[ $levelid ] = array_merge($ids[ $levelid ], $user_ids);
                }
            }
        }
        return $ids;
    }

    /**
     * Get members that are to be sent expiring notification emails
     *
     * @return array  Array of array( user_id, level_id )
     */
    public function get_expiring_members()
    {
        global $wpdb;
        $wpm_levels = $this->get_option('wpm_levels');
        $ids        = [
            'admin' => [],
            'user'  => [],
        ];
        $days       = $this->get_option('expiring_notification_days');

        $usrlvltbl    = $this->table_names->userlevels;
        $usrlvlopttbl = $this->table_names->userlevel_options;

        foreach (['admin', 'user'] as $type) {
            foreach ($wpm_levels as $levelid => $thelevel) {
                if (empty($thelevel[ 'expiring_notification_' . $type ])) {
                    continue;
                }
                if (empty($thelevel['expire_option'])) {
                    continue; // No expiration.
                }
                if (1 === (int) $thelevel['expire_option'] && empty($thelevel['expire'])) {
                    continue; // Invalid.
                }

                $days = $thelevel[ 'expiring_' . $type . '_send' ];

                switch ((int) $thelevel['expire_option']) {
                    case 1: // fixed term.
                        $calendar = strtoupper(substr($thelevel['calendar'], 0, -1));
                        $expire   = $thelevel['expire'];
                        $user_ids = $wpdb->get_results(
                            $wpdb->prepare(
                                'SELECT DISTINCT `lvl`.`user_id`, `lvl`.`level_id` FROM `'
                                . esc_sql($usrlvltbl)
                                . '` as `lvl` INNER JOIN `'
                                . esc_sql($usrlvlopttbl)
                                . '` as `lvlop` ON `lvl`.`id`=`lvlop`.`userlevel_id` WHERE `lvl`.`level_id`=%s'
                                . ' AND `lvlop`.`option_name` = "registration_date"'
                                . " AND DATEDIFF(DATE_ADD(SUBSTRING_INDEX(lvlop.option_value, '#', 1), INTERVAL %d %0s), CURDATE()) <= %d"
                                . " AND DATEDIFF(DATE_ADD(SUBSTRING_INDEX(lvlop.option_value, '#', 1), INTERVAL %d %0s), CURDATE()) > 0",
                                $levelid,
                                $expire,
                                $calendar,
                                $days,
                                $expire,
                                $calendar
                            ),
                            ARRAY_A
                        );
                        break;
                    case 2: // specific date.
                        $date     = wlm_date('Y-m-d H:i:s', strtotime($thelevel['expire_date']));
                        $user_ids = $wpdb->get_results(
                            $wpdb->prepare(
                                'SELECT DISTINCT `lvl`.`user_id`, `lvl`.`level_id` FROM `'
                                . esc_sql($usrlvltbl)
                                . '` as `lvl` INNER JOIN `'
                                . esc_sql($usrlvlopttbl)
                                . '` as `lvlop` ON `lvl`.`id`=`lvlop`.`userlevel_id` WHERE `lvl`.`level_id`=%s'
                                . ' AND DATEDIFF(%s, NOW()) <= %d'
                                . ' AND DATEDIFF(%s, NOW()) > 0',
                                $levelid,
                                $date,
                                $days,
                                $date
                            ),
                            ARRAY_A
                        );
                        break;
                }
                $ids[ $type ] = array_merge($ids[ $type ], $user_ids);
            }
        }
        return $ids;
    }

    /**
     * Get User Level is Expired Status
     *
     * @param  string  $level     Level ID.
     * @param  integer $uid       User ID.
     * @param  integer $timestamp (optional) Timestamp to set.
     * @return boolean
     */
    public function level_expired($level, $uid, $timestamp = null)
    {
        $expire = $this->level_expire_date($level, $uid, $timestamp);
        $expire = apply_filters('wishlistmember_user_expires', $expire, $uid, $level);
        if (empty($expire)) {
            return false;
        } elseif ($expire < time()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve User Level Expiry Date
     *
     * @param  string  $level     Level ID.
     * @param  integer $uid       User ID.
     * @param  integer $timestamp Timestamp to use for computing fixed term expiration.
     * @return integer                Expiry Date Timestamp
     */
    public function level_expire_date($level, $uid, $timestamp = null)
    {
        $wpm_levels = $this->get_option('wpm_levels');
        $thelevel   = $wpm_levels[ $level ];

        $exp_date               = false;
        $thelevel_expire_option = 0;
        if (is_array($thelevel) && array_key_exists('expire_option', $thelevel)) {
            $thelevel_expire_option = $thelevel['expire_option'] + 0;
        }

        switch ($thelevel_expire_option) {
            case 2:
                $exp_date = strtotime(str_replace('/', '-', $thelevel['expire_date']));
                break;
            case 1:
                $expire = (int) $thelevel['expire'] + 0;
                if ($expire) {
                    $exp_date = strtotime('+' . $expire . ' ' . $thelevel['calendar'], $timestamp ? $timestamp : $this->user_level_timestamp($uid, $level));
                }
                break;
        }
        return apply_filters('wishlistmember_user_expire_date', $exp_date, $uid, $level);
    }

    /**
     * Check if a user's level is scheduled for an action
     *
     * @param  integer $level Level ID.
     * @param  integer $uid   User ID.
     * @return mixed         false or meta data
     */
    public function is_level_scheduled($level, $uid)
    {
        $schedule_types = ['scheduled_add', 'scheduled_remove', 'scheduled_move'];
        foreach ($schedule_types as $type) {
            $meta_data = wlm_maybe_unserialize($this->Get_UserLevelMeta($uid, $level, $type));
            if ($meta_data) {
                break;
            }
        }
        if ('remove' === wlm_arrval($meta_data, 'type') && wlm_arrval($meta_data, 'is_current_level')) {
            return false;
        } elseif ($meta_data) {
            return $meta_data;
        } else {
            return;
        }
    }

    /**
     * Checks if User Level has a shoppingcart pending status
     *
     * @param  integer $level Level ID.
     * @param  array   $uid   User IDs.
     * @return string         Pending reason
     */
    public function is_pending_shopping_cart_approval($level, $uid)
    {
        $uid      = (array) $uid;
        list($id) = $uid;
        return $this->Get_UserLevelMeta($id, $level, 'forapproval');
    }

    /**
     * Get/Set User Level For Approval Status
     *
     * @param  integer $level  Level ID.
     * @param  array   $uid    User IDs.
     * @param  boolean $status (optional) Status.
     * @param  integer $time   (optional) Timestamp.
     * @return integer
     */
    public function level_for_approval($level, $uid, $status = null, $time = null)
    {
        $uid = (array) $uid;
        if (! is_null($status)) {
            if (is_null($time)) {
                $time = time();
            }
            $time = gmdate('Y-m-d H:i:s', $time);
            if ($status) {
                foreach ($uid as $id) {
                    if (! $this->level_for_approval($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'forapproval', $status);
                        $this->Update_UserLevelMeta($id, $level, 'forapproval_date', $time);

                        do_action('wishlistmember_unapprove_user_levels', $id, (array) $level);
                        $this->record_user_level_history($id, 'unapproved', $level);
                    }
                }
            } else {
                foreach ($uid as $id) {
                    if ($this->level_for_approval($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'forapproval', 0);
                        $this->Update_UserLevelMeta($id, $level, 'forapproval_date', $time);

                        $this->user_level_timestamp($id, $level, $time);

                        do_action('wishlistmember_approve_user_levels', $id, (array) $level, 'autoresponder_add_pending_admin_approval');

                        $this->record_user_level_history($id, 'approved', $level);

                        // Force sequential upgrade.
                        delete_transient('wlm_is_doing_sequential_for_' . $id);
                        $this->do_sequential_for_user($id, true);
                    }
                }
            }
        }
        list($id) = $uid;
        return $this->Get_UserLevelMeta($id, $level, 'forapproval');
    }

    /**
     * Get / Set User Level for Approval Status
     *
     * @param  integer $level  Level ID.
     * @param  array   $uid    User IDs.
     * @param  boolean $status (optional) Status.
     * @param  integer $time   (optional) Timestamp.
     * @return integer
     */
    public function level_pending($level, $uid, $status = null, $time = null)
    {
        return $this->level_for_approval($level, $uid, $status, $time);
    }

    /**
     * Get / Set User Level UnConfirmed Status
     *
     * @param  integer $level  Level ID.
     * @param  array   $uid    User IDs.
     * @param  boolean $status (optional) Status.
     * @param  integer $time   (optional) Timestamp.
     * @return integer
     */
    public function level_unconfirmed($level, $uid, $status = null, $time = null)
    {
        $uid = (array) $uid;
        if (! is_null($status)) {
            if (is_null($time)) {
                $time = time();
            }
            $time = gmdate('Y-m-d H:i:s', $time);
            if ($status) {
                foreach ($uid as $id) {
                    if (! $this->level_unconfirmed($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'unconfirmed', 1);
                        $this->Update_UserLevelMeta($id, $level, 'unconfirmed_date', $time);

                        do_action('wishlistmember_unconfirm_user_levels', $id, (array) $level);
                        $this->record_user_level_history($id, 'unconfirmed', $level);
                    }
                }
            } else {
                foreach ($uid as $id) {
                    if ($this->level_unconfirmed($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'unconfirmed', 0);
                        $this->Update_UserLevelMeta($id, $level, 'unconfirmed_date', $time);

                        delete_user_meta($id, 'wlm_email_confirmation_reminder');

                        do_action('wishlistmember_confirm_user_levels', $id, (array) $level, 'autoresponder_add_pending_email_confirmation');
                        $this->record_user_level_history($id, 'confirm', $level);

                        // Force sequential upgrade.
                        delete_transient('wlm_is_doing_sequential_for_' . $id);
                        $this->do_sequential_for_user($id, true);
                    }
                }
            }
        }
        list($id) = $uid;
        return $this->Get_UserLevelMeta($id, $level, 'unconfirmed');
    }

    /**
     * Get/Set User Leval Cancellation Status
     *
     * @param  integer $level  Level ID.
     * @param  array   $uid    User IDs.
     * @param  boolean $status (optional) Status.
     * @param  integer $time   (optional) Timestamp.
     * @return integer
     */
    public function level_cancelled($level, $uid, $status = null, $time = null)
    {
        $uid = (array) $uid;
        if (! is_null($status)) {
            if (is_null($time)) {
                $time = time();
            }
            $time = gmdate('Y-m-d H:i:s', $time);
            if ($status) {
                foreach ($uid as $id) {
                    if (! $this->level_cancelled($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'cancelled', 1);
                        $this->Update_UserLevelMeta($id, $level, 'cancelled_date', $time);

                        $usr = $this->get_user_data($id);
                        if ($usr->ID) {
                            $this->ar_unsubscribe($usr->first_name, $usr->last_name, $usr->user_email, $level);
                        }
                        do_action('wishlistmember_cancel_user_levels', $id, (array) $level);

                        $this->record_user_level_history($id, 'cancelled', $level, null, $time);
                    }
                }
            } else {
                foreach ($uid as $id) {
                    if ($this->level_cancelled($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'cancelled', 0);
                        $this->Update_UserLevelMeta($id, $level, 'cancelled_date', $time);

                        $usr = $this->get_user_data($id);
                        if ($usr->ID) {
                            $this->ar_subscribe($usr->first_name, $usr->last_name, $usr->user_email, $level);
                        }
                        do_action('wishlistmember_uncancel_user_levels', $id, (array) $level);
                        // In case theres a filter that prevent sending of email.
                        remove_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);

                        $this->record_user_level_history($id, 'uncancelled', $level, null, $time);
                    }

                    if ($this->level_sequential_cancelled($level, $id)) {
                        // Also uncancel sequential_cancelled when level is manuall uncancelled or a rebill has been processed.
                        $this->level_sequential_cancelled($level, $id, false);
                    }
                }
            }
            foreach ($uid as $id) {
                $this->Delete_UserLevelMeta($id, $level, 'wlm_schedule_level_cancel');
                $this->Delete_UserLevelMeta($id, $level, 'schedule_level_cancel_reason');
            }
        }
        list($id) = $uid;
        return $this->Get_UserLevelMeta($id, $level, 'cancelled');
    }

    /**
     * Get user's upcoming cancellation date for level
     *
     * @param  string  $level Level ID.
     * @param  integer $uid   User ID.
     * @return integer           Timestamp
     */
    public function level_cancel_date($level, $uid)
    {
        $date = $this->Get_UserLevelMeta($uid, $level, 'wlm_schedule_level_cancel');
        if (empty($date)) {
            $date = false;
        }
        if (! is_numeric($date)) {
            $date = strtotime($date);
        }
        return $date;
    }

    /**
     * Get user's cancelled date for level
     *
     * @param  string  $level Level ID.
     * @param  integer $uid   User ID.
     * @return integer           Timestamp
     */
    public function level_cancelled_date($level, $uid)
    {
        $date = $this->Get_UserLevelMeta($uid, $level, 'cancelled_date');
        if (empty($date)) {
            $date = false;
        }
        if (! is_numeric($date)) {
            $date = strtotime($date);
        }
        return $date;
    }

    /**
     * Check if the transaction ID exists.
     *
     * @param  string $transaction_id Transaction ID.
     * @return boolean
     */
    public function check_member_trans_id($transaction_id)
    {
        $transactions = $this->Get_UserID_From_UserLevelsMeta('transaction_id', $transaction_id);
        if (empty($transactions)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get users with incomplete registration.
     * Original code by Fel Jun
     *
     * @since 3.6 added the $ids parameter
     *
     * @param  array $ids Optional array of IDs for filter. Return all matching IDs if empty.
     * @return array {
     *     [@type int User ID] => array {
     * @type   string $email                  Email address.
     * @type   mixed  $wlm_incregnotification Email confirmation reminder.
     *     }
     * }
     */
    public function get_incomplete_registrations($ids = [])
    {
        global $wpdb;

        // @since 3.6 generate AND user_id IN query if $ids is provided.
        if ($ids && is_array($ids)) {
            $ids = 'AND user_id IN (' . implode(',', array_map('intval', $ids)) . ')';
        } else {
            $ids = '';
        }

        $ret   = [];
        $users = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT user_id,meta_value FROM {$wpdb->usermeta} WHERE meta_key='wlm_incregnotification' %0s",
                $ids
            )
        );
        if (count($users) > 0) {
            foreach ($users as $user) {
                $user_orig_email = $this->Get_UserMeta($user->user_id, 'wlm_origemail');
                if (! empty($user_orig_email)) {
                    $ret[ $user->user_id ]['email']                  = $user_orig_email;
                    $ret[ $user->user_id ]['wlm_incregnotification'] = wlm_maybe_unserialize($user->meta_value);
                }
            }
        }
        return $ret;
    }

    /**
     * Get users that needs email confirmation
     *
     * @return array {
     *     [@type int User ID] => array {
     * @type   string $email                           Email address.
     * @type   string $username                        Username.
     * @type   mixed  $wlm_email_confirmation_reminder Email confirmation reminder.
     *     }
     * }
     */
    public function get_users_for_confirmation()
    {
        global $wpdb;
        $ret   = [];
        $users = $wpdb->get_results("SELECT {$wpdb->usermeta}.user_id, {$wpdb->usermeta}.meta_value, {$wpdb->users}.user_login, {$wpdb->users}.user_email FROM {$wpdb->usermeta} LEFT JOIN {$wpdb->users} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID WHERE meta_key='wlm_email_confirmation_reminder'");
        if (count($users) > 0) {
            foreach ($users as $user) {
                $ret[ $user->user_id ]['email']                           = $user->user_email;
                $ret[ $user->user_id ]['username']                        = $user->user_login;
                $ret[ $user->user_id ]['wlm_email_confirmation_reminder'] = wlm_maybe_unserialize($user->meta_value);
            }
        }
        return $ret;
    }

    /**
     * Get User ID from Transaction ID
     *
     * @param  string $transaction_id Transaction ID.
     * @return integer                   User ID
     */
    public function get_user_id_from_txn_id($transaction_id)
    {
        global $wpdb;
        $user = $this->Get_UserID_From_UserLevelsMeta('transaction_id', $transaction_id);
        return $user;
    }

    /**
     * Get member IDs by date range
     *
     * @param  string $userlevel_options_name Option name from the wlm_userlevel_options table.
     * @param  string $from                   From date.
     * @param  string $to                     To date.
     * @param  string $level_id               Level ID.
     * @return array                         Array of user ids
     */
    public function get_members_id_by_date_range($userlevel_options_name, $from, $to, $level_id = null)
    {
        global $wpdb;

        if ($level_id) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT DISTINCT `ul`.`user_id`, `ulm`.`option_value` AS `date` FROM `' . esc_sql($this->table_names->userlevel_options) . '` AS `ulm` LEFT JOIN `' . esc_sql($this->table_names->userlevels) . '` AS `ul` ON `ulm`.`userlevel_id`=`ul`.`ID` WHERE `ulm`.`option_name`=%s AND `ul`.`level_id`=%s',
                    $userlevel_options_name,
                    $level_id
                )
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT DISTINCT `ul`.`user_id`, `ulm`.`option_value` AS `date` FROM `' . esc_sql($this->table_names->userlevel_options) . '` AS `ulm` LEFT JOIN `' . esc_sql($this->table_names->userlevels) . '` AS `ul` ON `ulm`.`userlevel_id`=`ul`.`ID` WHERE `ulm`.`option_name`=%s',
                    $userlevel_options_name
                )
            );
        }

        $from       = wlm_date('Y-m-d', strtotime($from));
        $to         = strtotime($to);
        if ($to <= 0) {
            $to = time();
        }
        $to = wlm_date('Y-m-d', $to);

        $ids = [];
        foreach ($results as $value) {
            $data       = explode('#', $value->date);
            $date_value = wlm_date('Y-m-d', strtotime($data[0]));

            if ($date_value >= $from && $date_value <= $to) {
                array_push($ids, $value->user_id);
            }
        }
        return $ids;
    }

    /**
     * Returns the user registration date as a timestamp
     *
     * @param  object  $user            The user object.
     * @param  boolean $add_wp_timezone True to add WP Timezone. Default true.
     * @return integer                     Timestamp for current Timezone
     */
    public function user_registered($user, $add_wp_timezone = true)
    {
        // Compute timezone difference.
        $reg = false;
        if (is_numeric($user)) {
            $user = $this->get_user_data($user);
        }
        if (preg_match('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', (string) wlm_arrval($user, 'user_registered'), $matches)) {
            list( $year, $month, $day, $hour, $minute, $second ) = array_slice($matches, 1);
            $reg = gmmktime($hour, $minute, $second, $month, $day, $year);
            if ($add_wp_timezone) {
                $reg += $this->gmt;
            }
        }
        return $reg;
    }

    /**
     * Checks if a user has a temporary account (a.k.a. incomplete registration)
     *
     * @param  integer $user_id User ID.
     * @return boolean
     */
    public function is_temp_user($user_id)
    {
        $user = $this->get_user_data($user_id);
        if ($user->user_email === $user->user_login && 'temp_' . md5($user->wlm_origemail) === $user->user_login) {
            return true;
        }
        return false;
    }

    /**
     * Get/Set User Level Parent
     *
     * @param  integer $level_id Level ID.
     * @param  array   $user_ids User IDs.
     * @param  integer $parent   Parent Level ID.
     * @return string         Parent Level ID
     */
    public function level_parent($level_id, $user_ids, $parent = null)
    {
        $user_ids   = (array) $user_ids;
        $wpm_levels = $this->get_option('wpm_levels');
        if (! is_null($parent)) {
            if (isset($wpm_levels[ $parent ])) {
                foreach ($user_ids as $id) {
                    $this->Update_UserLevelMeta($id, $level_id, 'parent_level', $parent);
                }
            } elseif (empty($parent)) {
                foreach ($user_ids as $id) {
                    $this->Update_UserLevelMeta($id, $level_id, 'parent_level', null);
                }
            }
        }
        list($id)     = $user_ids;
        $parent_level = $this->Get_UserLevelMeta($id, $level_id, 'parent_level');
        return isset($wpm_levels[ $parent_level ]) && in_array($parent_level, $this->get_membership_levels($id)) ? $parent_level : false;
    }

    /**
     * Get Child Levels of Parent
     *
     * @param  integer $user_id          User ID.
     * @param  array   $parent_level_ids Array of Parent Level IDs.
     * @return array                   Child Level IDs
     */
    public function get_child_levels($user_id, $parent_level_ids)
    {
        global $wpdb;
        $levels = (array) $parent_level_ids;
        foreach ($levels as $key => $lvl) {
            if (false !== strpos($lvl, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return [];
        }

        $level_ids = $wpdb->get_col(
            $wpdb->prepare(
                'SELECT lvl.level_id FROM `' . esc_sql($this->table_names->userlevels) . '` as lvl INNER JOIN `' . esc_sql($this->table_names->userlevel_options) . "` as lvlop ON lvl.id=lvlop.userlevel_id WHERE lvlop.option_name='parent_level' AND lvl.user_id=%d AND lvlop.option_value IN (" . implode(',', array_fill(0, count($levels), '%s')) . ')',
                $user_id,
                ...array_values($levels)
            )
        );
        if (count($level_ids) <= 0) {
            return [];
        }
        $level_ids = array_unique($level_ids);
        return $level_ids;
    }

    /**
     * Removes Child Levels of the Parent
     *
     * @param integer $user_id          User ID.
     * @param array   $parent_level_ids Parent Level IDs.
     */
    public function remove_child_levels($user_id, $parent_level_ids)
    {
        $level_ids = $this->get_child_levels($user_id, $parent_level_ids);
        if (count($level_ids) <= 0) {
            return;
        }
        $wlmuser        = new \WishListMember\User($user_id);
        $current_levels = array_keys($wlmuser->Levels);
        $levels         = array_unique(array_diff($current_levels, $level_ids));

        // Update the levels.
        $this->set_membership_levels($user_id, $levels);
    }

    /**
     * Updates Child status when Parent Status Change
     *
     * @param integer $user_id          User ID.
     * @param array   $parent_level_ids Parent Level IDs.
     */
    public function update_child_status($user_id, $parent_level_ids)
    {
        $level_ids = [];
        foreach ($parent_level_ids as $p) {
            $clvls = $this->get_child_levels($user_id, $p);
            if (count($clvls) > 0) {
                $level_ids[ $p ] = $clvls;
            }
        }
        if (count($level_ids) <= 0) {
            return; // Nothing to do here.
        }

        $wlmuser = new \WishListMember\User($user_id);
        foreach ($level_ids as $plvl => $clvls) {
            if (! isset($wlmuser->Levels[ $plvl ])) {
                continue; // Next, nothing to do here.
            }
            $active = ! ( $wlmuser->Levels[ $plvl ]->Cancelled || (bool) $wlmuser->Levels[ $plvl ]->Pending || $wlmuser->Levels[ $plvl ]->UnConfirmed );
            if ($active) {
                foreach ($clvls as $clvl) {
                    if (! $wlmuser->Levels[ $clvl ]->Active) {
                        $this->Delete_UserLevelMeta($user_id, $clvl, 'forapproval');
                        $this->Delete_UserLevelMeta($user_id, $clvl, 'forapproval_date');

                        $this->Delete_UserLevelMeta($user_id, $clvl, 'unconfirmed');
                        $this->Delete_UserLevelMeta($user_id, $clvl, 'unconfirmed_date');

                        $this->Delete_UserLevelMeta($user_id, $clvl, 'cancelled');
                        $this->Delete_UserLevelMeta($user_id, $clvl, 'cancelled_date');
                    }
                }
            } else {
                foreach ($clvls as $clvl) {
                    if ($wlmuser->Levels[ $plvl ]->Cancelled) {
                        $this->level_cancelled($clvl, $user_id, true);
                    }
                    if ($wlmuser->Levels[ $plvl ]->Pending) {
                        $this->level_for_approval($clvl, $user_id, true);
                    }
                    if ($wlmuser->Levels[ $plvl ]->UnConfirmed) {
                        $this->level_unconfirmed($clvl, $user_id, true);
                    }
                }
            }
        }
    }

    /**
     * Check if IP tracking is enabled for a user
     *
     * @param  integer $user_id User ID.
     * @return boolean
     */
    public function ip_tracking_enabled($user_id = 0)
    {
        $tracking_enabled = ! $this->get_option('privacy_disable_ip_tracking');
        if ($user_id) {
            switch ($this->Get_UserMeta($user_id, 'privacy_disable_ip_tracking')) {
                case '1': // Tracking disabled.
                    $tracking_enabled = false;
                    break;
                case '-1': // Tracking enabled.
                    $tracking_enabled = true;
                    break;
            }
        }
        return $tracking_enabled;
    }

    /**
     * Checks how many times a user has logged in and redirects to an error page
     * if user has exceeded the set limit or returns TRUE otherwise
     *
     * @param  object $user WP User Object.
     * @return boolean TRUE if User has not exceeded the daily limit
     */
    public function login_counter($user)
    {
        global $wpdb;
        $id = $user->ID;
        if ($user->caps['administrator']) {
            return true;
        }
        if (! $this->ip_tracking_enabled($id)) {
            // Ip tracking not allowed so we don't count logins as well as this feature is IP based.
            return true;
        }
        $counter = $this->Get_UserMeta($id, 'wpm_login_counter');
        if (! is_array($counter)) {
            $counter = [];
        }

        // Remove counts for the previous day.
        $now = wlm_date('Ymd');
        foreach ((array) $counter as $ip => $d) {
            if ($d < $now) {
                unset($counter[ $ip ]);
            }
        }

        // Get user limit.
        $limit = (int) $this->Get_UserMeta($id, 'wpm_login_limit');
        if ($limit < 0) {
            return true; // <- no login limits.
        }

        if (! $limit) {
            $limit = (int) $this->get_option('login_limit');
        }

        $ip_address = wlm_get_client_ip();

        if (count($counter) >= $limit && $limit > 0 && ! isset($counter[ $ip_address ])) {
            if ($this->get_option('login_limit_notify')) {
                // We send notification to admin about the exceeded login.
                $adminemail = $this->get_option('email_sender_address');
                wp_mail($adminemail, 'Login Limit Exceeded', "Login limit exceeded.\n\nUsername:{$user->user_login}\nEmail:{$user->user_email}\nIP:{$ip_address}");
            }
            $this->no_logout_redirect = true;

            wp_destroy_current_session();
            wp_clear_auth_cookie();
            wp_set_current_user(0);

            header('Location:' . wp_login_url() . '?loginlimit=1');
            exit;
        }

        if (! in_array($ip_address, array_keys((array) $counter), true)) {
            $counter[ $ip_address ] = $now;
        }

        $this->Update_UserMeta($id, 'wpm_login_counter', $counter);
        return true;
    }

    /**
     * Get User Custom Fields
     *
     * @param  integer $user_id User ID.
     * @return array
     */
    public function get_user_custom_fields($user_id)
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare('SELECT * FROM `%0s` WHERE `user_id`=%d AND `option_name` LIKE %s', $this->table_names->user_options, $user_id, 'custom_%'));
        $output  = [];
        if (! empty($results)) {
            foreach ($results as $result) {
                $output[ substr($result->option_name, 7) ] = wlm_maybe_unserialize($result->option_value);
            }
        }
        return $output;
    }

    /**
     * Get custom fields merge codes
     *
     * @return array
     */
    public function get_custom_fields_merge_codes()
    {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare("SELECT CONCAT('[wlm_custom ', SUBSTRING(`option_name`,8),']') FROM `%0s` WHERE `option_name` LIKE %s GROUP BY `option_name`", $this->table_names->user_options, 'custom_%'));
    }

    /**
     * Get user information using WP_User then patching it with WishList Member user info
     *
     * @param  integer|string $id    User ID or Username.
     * @param  string         $login Optional. Username.
     * @return object|false          WP_User or false on error
     */
    public function get_user_data($id, $login = '')
    {
        global $wpdb;
        if (! function_exists('get_userdata')) {
            require_once ABSPATH . WPINC . '/pluggable.php';
        }

        if (! empty($id) && ! is_numeric($id)) {
            $login = $id;
            $id    = 0;
        }

        if ($id) {
            $user = get_user_by('id', $id);
        } else {
            $user = get_user_by('login', $login);

            if (! $user) {
                $user = get_user_by('email', $login);
            }
        }

        if (empty($user->ID) || ! $user || is_wp_error($user)) {
            return false;
        }

        if (is_object($user)) {
            if (! is_null($GLOBALS['wp_rewrite'])) {
                // Manually build the feed url for now.
                $user->wlm_feed_url = get_bloginfo('url') . '/feed/';
            }
            if (! strpos($user->wlm_feed_url, 'wpmfeedkey=')) {
                $user->wlm_feed_url = $this->feed_link($user->wlm_feed_url, $this->feed_key($user->ID, true));
            }

            $results      = $wpdb->get_results($wpdb->prepare('SELECT `option_name`,`option_value` FROM `%0s` WHERE `user_id`=%d', $this->table_names->user_options, $user->ID));
            $user->wldata = new \stdClass();
            if ($results) {
                foreach ($results as $result) {
                    $value                = wlm_maybe_unserialize($result->option_value);
                    $key                  = str_replace('-', '', $result->option_name);
                    $user->wldata->{$key} = $value;
                    $user->data->{$key}   = &$user->wldata->{$key};
                    $user->{$key}         = $value;
                }
            }
            return $user;
        }
        return false;
    }

    /**
     * Check if user has access to content
     *
     * @param  integer $uid User ID.
     * @param  integer $pid Post ID.
     * @return boolean
     */
    public function has_access($uid, $pid)
    {
        if (user_can($uid, 'manage_options')) {
            return true;
        }
        $post = get_post($pid);
        if ($this->get_option('protect_after_more') && false !== strpos($post->post_content, '<!--more-->')) {
            $protectmore = true;
        } else {
            $protectmore = false;
        }

        $protect = $protectmore || $this->protect($post->ID);
        if (! $protect) {
            return true;
        }

        $is_userpost = in_array($post->ID, $this->get_membership_content($post->post_type, 'U-' . $uid));
        if ($is_userpost) {
            return true;
        }

        // Page / post is excluded (special page) so give all.
        if (in_array($post->ID, $this->exclude_pages([]))) {
            return true;
        }

        // Not a member.
        if (empty($uid)) {
            return false;
        }

        $the_levels    = (array) $this->get_membership_levels($uid, null, null, null, true);
        $active_levels = $the_levels;
        $timestamps    = $this->user_level_timestamps($uid);
        $time          = time();

        $expired_levels     = [];
        $unconfirmed_levels = [];
        $for_aproval_levels = [];
        $cancelled_levels   = [];

        foreach ((array) $active_levels as $key => $the_level_id) {
            if ($this->level_expired($the_level_id, $uid)) {
                unset($active_levels[ $key ]);
                $expired_levels[] = $the_level_id;
            }
        }

        // Expired.
        if (! count($active_levels)) {
            return false;
        }

        // Check if any of the levels are for confirmation.
        foreach ((array) $active_levels as $key => $the_level_id) {
            if ($this->level_unconfirmed($the_level_id, $uid)) {
                unset($active_levels[ $key ]);
                $unconfirmed_levels[] = $the_level_id;
            }
        }

        // For confirmation.
        if (! count($active_levels)) {
            return false;
        }

        foreach ((array) $active_levels as $key => $the_level_id) {
            if ($this->level_for_approval($the_level_id, $uid)) {
                unset($active_levels[ $key ]);
                $for_aproval_levels[] = $the_level_id;
            }
        }

        // For approval.
        if (! count($active_levels)) {
            return false;
        }

        // Check if any of the levels are cancelled.
        foreach ((array) $active_levels as $key => $the_level_id) {
            if ($this->level_cancelled($the_level_id, $uid)) {
                unset($active_levels[ $key ]);
                $cancelled_levels[] = $the_level_id;
            }
        }
        // Cancelled.
        if (! count($active_levels)) {
            return false;
        }

        $wpm_levels    = $this->get_option('wpm_levels');
        $can_view_page = false;
        $can_view_post = false;
        foreach ((array) $the_levels as $the_level_id) {
            if (in_array($the_level_id, $active_levels)) {
                $thelevel      = $wpm_levels[ $the_level_id ];
                $can_view_page = $can_view_page | isset($thelevel['allpages']);
                $can_view_post = $can_view_post | isset($thelevel['allposts']);
            }
        }

        if (! $can_view_page && is_page()) {
            $access = array_intersect((array) $this->get_content_levels('pages', $post->ID), $active_levels);
            if (! empty($access)) {
                return true;
            }
        } elseif (! $can_view_post && is_single()) {
            $access = array_intersect((array) $this->get_content_levels('posts', $post->ID), $active_levels);
            if (! empty($access)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Attempt to fix user address data for each user
     */
    public function fix_user_address()
    {
        global $wpdb;

        if (1 === (int) get_transient('wlm_fixing_user_address') || 1 === (int) $this->get_option('FixedUserAddress')) {
            return;
        }
        wp_raise_memory_limit('fix_user_address');
        set_transient('wlm_fixing_user_address', 1, 86400);
        $page = 0;
        while (true) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT `t1`.`user_id`,`t1`.`option_value` FROM `' . esc_sql($this->table_names->user_options) . '` AS `t1` LEFT JOIN `' . esc_sql($this->table_names->user_options) . "` AS `t2` ON `t1`.`user_id`=`t2`.`user_id` AND `t2`.`option_name`='wpm_useraddress' WHERE `t1`.`option_name`='wlm_reg_post' AND (`t2`.`option_value` IS NULL OR TRIM(`t2`.`option_value`) IN ('','a:0:{}')) LIMIT %d,10000",
                    ( $page++ ) * 10000
                ),
                ARRAY_N
            );
            if (! $results) {
                break;
            }

            while ($result = array_shift($results)) {
                list($uid, $post) = $result;
                $post             = $this->WLMDecrypt($post);
                $address          = [];
                if (isset($post['status']) && isset($post['sku1'])) {
                    // 1shoppingcart.
                    $address['company']  = $post['shipCompany'];
                    $address['address1'] = $post['shipAddress1'];
                    $address['address2'] = $post['shipAddress2'];
                    $address['city']     = $post['shipCity'];
                    $address['state']    = $post['shipState'];
                    $address['zip']      = $post['shipZip'];
                    $address['country']  = $post['shipCountry'];
                } elseif (isset($post['product_sku']) && isset($post['customer_email'])) {
                    // Premium web cart.
                    $address['company']  = $post['shipping_company_name'];
                    $address['address1'] = $post['billing_address_line1'];
                    $address['address2'] = $post['billing_address_line2'];
                    $address['city']     = $post['billing_customer_city'];
                    $address['state']    = $post['billing_customer_state'];
                    $address['zip']      = $post['billing_customer_zip'];
                    $address['country']  = $post['billing_customer_country'];
                    $address['phone']    = $post['phone'];
                    $address['fax']      = $post['fax'];
                } elseif (isset($post['item_number']) && isset($post['payer_email'])) {
                    // Paypal.
                    $address['company']  = $post['payer_business_name'] ? $post['payer_business_name'] : $post['address_name'];
                    $address['address1'] = $post['address_street'];
                    $address['address2'] = '';
                    $address['city']     = $post['address_city'];
                    $address['state']    = $post['address_state'];
                    $address['zip']      = $post['address_zip'];
                    $address['country']  = $post['address_country'];
                } elseif (isset($post['level']) && isset($post['cmd']) && isset($post['hash'])) {
                    // Generic integration or generic integration plr.
                    $address['company']  = $post['company'];
                    $address['address1'] = $post['address1'];
                    $address['address2'] = $post['address2'];
                    $address['city']     = $post['city'];
                    $address['state']    = $post['state'];
                    $address['zip']      = $post['zip'];
                    $address['country']  = $post['country'];
                    $address['phone']    = $post['phone'];
                    $address['fax']      = $post['fax'];
                }
                if (trim(implode('', $address))) {
                    $this->Update_UserMeta($uid, 'wpm_useraddress', $address);
                }
            }
        }
        $this->save_option('FixedUserAddress', 1);
        delete_transient('wlm_fixing_user_address');
    }

    /**
     * Delete Save Search Entry
     */
    public function wlm_delete_saved_search_ajax()
    {
        $post_data = wlm_post_data(true);
        if (isset($post_data['option_name']) && ! empty($post_data['option_name'])) {
            $this->delete_option($post_data['option_name']);
        }
        exit;
    }

    /**
     * Unschedule a single user level
     */
    public function wlm_unschedule_single()
    {
        $level = wlm_post_data()['level'];
        $user  = wlm_post_data()['user'];
        switch (wlm_post_data()['schedule_type']) {
            case 'remove':
                $this->Delete_UserLevelMeta($user, $level, 'scheduled_remove');
                break;
            case 'cancel':
                $this->Delete_UserLevelMeta($user, $level, 'wlm_schedule_level_cancel');
                $this->Delete_UserLevelMeta($user, $level, 'schedule_level_cancel_reason');
                break;
            case 'add':
            case 'move':
                $levels = array_diff((array) $this->get_membership_levels($user), [$level]);
                $this->set_membership_levels($user, $levels);
                break;
        }
    }

    /**
     * Called by WP-Cron
     *
     * (recoded for WLM 3.0)
     */
    public function expiring_members_notification()
    {
        $lastsent = $this->get_option('expnotification_last_sent');
        $lastsent = empty($lastsent) ? 0 : wlm_date('m/d/Y', $lastsent);

        $dont_send_when_unsubscribed = $this->get_option('dont_send_reminder_email_when_unsubscribed');
        $wpm_levels                  = $this->get_option('wpm_levels');

        $expiring_users = $this->get_expiring_members();
        $users          = [];

        foreach (['admin', 'user'] as $type) {
            foreach ($expiring_users[ $type ] as $u) {
                $lid = $u['level_id'];
                $uid = $u['user_id'];

                if (empty($users[ $uid ])) {
                    $users[ $uid ] = new \WishListMember\User($uid, true);
                }

                $umeta_name = sprintf('_expiring_email_sent_%s_%s_%s', $type, $lid, $users[ $uid ]->Levels[ $lid ]->ExpiryDate);
                if ($this->Get_UserMeta($uid, $umeta_name)) {
                    continue;
                }

                $macros = [
                    '[memberlevel]' => $wpm_levels[ $lid ]['name'],
                    '[expirydate]'  => wlm_date('M d, Y', $users[ $uid ]->Levels[ $lid ]->ExpiryDate),
                ];

                if ('user' === $type) {
                    if (! $dont_send_when_unsubscribed || ! $users[ $uid ]->user_info->data->wlm_unsubscribe) {
                        $this->send_email_template('expiring_level', $u['user_id'], $macros);
                        $this->Update_UserMeta($uid, $umeta_name, time());
                    }
                } else {
                    $this->send_email_template('expiring_level_admin', $u['user_id'], $macros, $this->get_option('email_sender_address'));
                    $this->Update_UserMeta($uid, $umeta_name, time());
                }
            }
        }
        $this->save_option('expnotification_last_sent', time());
    }

    /**
     * Send Email Reminders for members that needs to confirm their email
     * Called by WP-Cron
     */
    public function email_confirmation_reminders()
    {
        $wpm_levels = $this->get_option('wpm_levels');

        // Process members for that need confirmation reminders.
        foreach ($this->get_users_for_confirmation() as $id => $user) {
            // Email confirmation reminder...
            $email_confirmation_reminder = (array) $user['wlm_email_confirmation_reminder'];

            // Skip invalid levels.
            if (empty($wpm_levels[ wlm_arrval($email_confirmation_reminder, 'wpm_id') ])) {
                delete_user_meta($id, 'wlm_email_confirmation_reminder'); // Clear user meta.
                continue;
            }
            $level = $wpm_levels[ $email_confirmation_reminder['wpm_id'] ];

            if (empty($level['requireemailconfirmation']) || empty($level['require_email_confirmation_reminder'])) {
                delete_user_meta($id, 'wlm_email_confirmation_reminder'); // We're not sending reminders.
                continue;
            }

            // Get reminder settings.
            $first_notification = abs(wlm_arrval($level, 'require_email_confirmation_start'));
            if (! $first_notification) {
                $first_notification = $this->level_defaults['require_email_confirmation_start'];
            }

            $add_notification_freq = abs(wlm_arrval($level, 'require_email_confirmation_send_every'));
            if (! $add_notification_freq) {
                $add_notification_freq = $this->level_defaults['require_email_confirmation_send_every'];
            }

            $add_notification_count = abs(wlm_arrval($level, 'require_email_confirmation_howmany'));
            if (! $add_notification_count) {
                $add_notification_count = $this->level_defaults['require_email_confirmation_howmany'];
            }

            if ('minutes' === wlm_arrval($level, 'require_email_confirmation_start_type')) {
                $first_notification = $first_notification / 60;
            }

            $send     = false;
            $count    = abs(wlm_arrval($email_confirmation_reminder, 'count'));
            $lastsend = abs(wlm_arrval($email_confirmation_reminder, 'lastsend'));
            if (! $lastsend) {
                $lastsend = time();
            }

            $t_diff = ( time() - $lastsend ) / 3600;
            $t_diff = $t_diff < 0 ? 0 : round($t_diff, 3);

            // Do not send if we've reached the max send count or if we're too late to avoid sudden bulk sending of confirmation reminders.
            if ($count >= $add_notification_count || $t_diff > $first_notification + $add_notification_freq * ( $add_notification_count - 1 ) + 24) {
                delete_user_meta($id, 'wlm_email_confirmation_reminder'); // Done sending or too late to send anymore.
                continue;
            }

            if (! $count && $t_diff >= $first_notification) {
                // First notification.
                $send = true;
            } elseif ($count < $add_notification_count && $t_diff >= $add_notification_freq) {
                // 2nd notification onwards...
                $send = true;
            }

            if ($send) {
                $macros = [
                    '[memberlevel]' => wlm_trim($wpm_levels[ $email_confirmation_reminder['wpm_id'] ]['name']),
                    '[username]'    => $user['username'],
                    '[password]'    => '********',
                    '[confirmurl]'  => get_bloginfo('url') . '/index.php?wlmconfirm=' . $id . '/' . md5(wlm_trim($user['email']) . '__' . wlm_trim($user['username']) . '__' . $email_confirmation_reminder['wpm_id'] . '__' . $this->GetAPIKey()),
                ];

                $this->send_email_template('email_confirmation_reminder', $id, $macros);
                $email_confirmation_reminder['count']    = $count + 1;
                $email_confirmation_reminder['lastsend'] = time();
                update_user_meta($id, 'wlm_email_confirmation_reminder', $email_confirmation_reminder);
            }
        }
    }

    /**
     * AJAX search for users
     */
    public function wlm_user_search_ajax()
    {
        $search    = wlm_post_data()['search'];
        $search_by = trim(wlm_post_data()['search_by']);
        $url       = trim(wlm_post_data()['url']);

        $search_results = [];
        switch ($search_by) {
            case 'by_level':
                if (empty($search)) {
                    die();
                }
                $search_results = $this->member_ids($search);
                break;
            default:
                $search = wlm_trim($search);
                if (empty($search)) {
                    die();
                }
                $search_results = new \WishListMember\User_Search($search);
                $search_results = $search_results->results;
        }

        if (wlm_post_data()['return_raw']) {
            if (count($search_results)) {
                $get_users = [
                    'include' => $search_results,
                    'fields'  => ['ID', 'user_login', 'display_name', 'user_email'],
                ];
                $data      = [
                    'success' => 1,
                    'data'    => get_users($get_users),
                ];
            } else {
                $data = [
                    'success' => 0,
                    'data'    => [],
                ];
            }
            wp_die(wp_json_encode($data));
        }

        if (count($search_results)) {
            $output    = '';
            $alternate = '.';
            foreach ($search_results as $uid) {
                $user = get_userdata($uid);
                $name = wlm_trim($user->user_firstname . ' ' . $user->user_lastname);
                if (! $name) {
                    $name = $user->user_login;
                }
                $alternate = $alternate ? '' : ' alternate';
                $output   .= sprintf(
                    '<tr class="user_%2$d' . $alternate . '">
					<td class="num">%2$d</td><td>%3$s</td><td>%4$s</td><td>%5$s</td><td class="select_link"><a href="%1$s">[select]</a></tr>',
                    $url . $uid,
                    $uid,
                    $name,
                    $user->user_login,
                    $user->user_email
                );
            }
            $output = '<table class="widefat"><thead><tr>
				<th class="num">ID</th>
				<th>Name</th>
				<th>Username</th>
				<th>Email</th>
				<th>&nbsp;</th>
				</tr></thead><tbody>' . $output . '</tbody></table>';
            echo wp_kses_post($output);
        }
        wp_die();
    }

    /**
     * Remove pending to add autoresponder
     *
     * @param integer $id    User ID.
     * @param integer $level Level ID.
     * @param array   $type  Type.
     */
    public function remove_pending_to_add_autoresponder($id, $level, $type)
    {
        foreach ($level as $levels) {
            // Checks if there's a flag for pending autoresponders.
            if ($this->Get_UserLevelMeta($id, $levels, 'autoresponder_add_pending_admin_approval') || $this->Get_UserLevelMeta($id, $levels, 'autoresponder_add_pending_email_confirmation')) {
                $this->Delete_UserLevelMeta($id, $levels, $type);

                // If all flags are clear, add the member to the autoresponder list...
                if (! $this->Get_UserLevelMeta($id, $levels, 'autoresponder_add_pending_admin_approval') && ! $this->Get_UserLevelMeta($id, $levels, 'autoresponder_add_pending_email_confirmation')) {
                    $usr = $this->get_user_data($id);
                    if ($usr->ID) {
                        $this->ar_subscribe($usr->first_name, $usr->last_name, $usr->user_email, $levels);
                    }
                }
            }
        }
    }

    /**
     * Unsubscribe expired members
     */
    public function unsubscribe_expired()
    {

        $unsubscribe_expired = $this->get_option('unsubscribe_expired_members') ? $this->get_option('unsubscribe_expired_members') : 0;
        if ($unsubscribe_expired) {
            $expired_members = $this->expired_members_id();

            foreach ($expired_members as $key => $expired_ids) {
                foreach ($expired_ids as $expired_id) {
                    $unsubscribe_expired = $this->Get_UserMeta($expired_id, 'unsubscribe_expired_members_processed');

                    if ($unsubscribe_expired) {
                        $unsubscribe_expired = wlm_maybe_unserialize($unsubscribe_expired);

                        if (! in_array($key, $unsubscribe_expired)) {
                            $user = $this->get_user_data($expired_id);
                            $this->ar_unsubscribe($user->first_name, $user->last_name, $user->user_email, $key);

                            $unsubscribe_expired[] = $key;

                            $this->Update_UserMeta($expired_id, 'unsubscribe_expired_members_processed', wlm_maybe_serialize((array) $unsubscribe_expired));
                        }
                    } else {
                        $user = $this->get_user_data($expired_id);
                        $this->ar_unsubscribe($user->first_name, $user->last_name, $user->user_email, $key);
                        $this->Update_UserMeta($expired_id, 'unsubscribe_expired_members_processed', wlm_maybe_serialize((array) $key));
                    }
                }
            }
        }
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_approve_user_levels', [$wlm, 'remove_pending_to_add_autoresponder'], 2, 3);
        add_action('wishlistmember_confirm_user_levels', [$wlm, 'remove_pending_to_add_autoresponder'], 2, 3);
        add_action('wishlistmember_email_confirmation_reminders', [$wlm, 'email_confirmation_reminders']);
        add_action('wishlistmember_expring_members_notification', [$wlm, 'expiring_members_notification']);
        add_action('wishlistmember_import_queue', [$wlm, 'process_import_members']);
        add_action('wishlistmember_unsubscribe_expired', [$wlm, 'unsubscribe_expired']);
        add_action('wp_ajax_wlm_delete_saved_search', [$wlm, 'wlm_delete_saved_search_ajax']);
        add_action('wp_ajax_wlm_unschedule_single', [$wlm, 'wlm_unschedule_single']);
        add_action('wp_ajax_wlm_user_search', [$wlm, 'wlm_user_search_ajax']);
    }
);
