<?php

/**
 * User_Level Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * User_Level Methods trait
 */
trait User_Level_Methods
{
    /**
     * Get/Set User Leval Sequential Cancellation Status
     *
     * @param  integer $level  Level ID.
     * @param  array   $uid    User IDs.
     * @param  boolean $status (optional).
     * @param  integer $time   (optional).
     * @return integer
     */
    public function level_sequential_cancelled($level, $uid, $status = null, $time = null)
    {
        $uid = (array) $uid;
        if (! is_null($status)) {
            if (is_null($time)) {
                $time = time();
            }
            $time = gmdate('Y-m-d H:i:s', $time);
            if ($status) {
                foreach ($uid as $id) {
                    if (! $this->level_sequential_cancelled($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'sequential_cancelled', 1);
                        $this->Update_UserLevelMeta($id, $level, 'sequential_cancelled_date', $time);
                    }
                }
            } else {
                foreach ($uid as $id) {
                    if ($this->level_sequential_cancelled($level, $id)) {
                        $this->Update_UserLevelMeta($id, $level, 'sequential_cancelled', 0);
                        $this->Update_UserLevelMeta($id, $level, 'sequential_cancelled_date', $time);
                    }
                }
            }
        }
        list($id) = $uid;
        return $this->Get_UserLevelMeta($id, $level, 'sequential_cancelled');
    }

    /**
     * Schedule level deactivation for member
     *
     * @param string        $level_id    Level ID.
     * @param integer|array $user_ids    The WP User ID(s).
     * @param integer       $cancel_date Timestamp.
     * @param array         $reason      Reason.
     */
    public function schedule_level_deactivation($level_id, $user_ids, $cancel_date, $reason = [])
    {
        global $wpdb;
        $good = 0;
        $bad  = 0;
        foreach ((array) $user_ids as $user_id) {
            $time        = gmdate('Y-m-d H:i:s', $cancel_date);
            $remove_date = $this->Get_UserLeveLMeta($user_id, $level_id, 'scheduled_remove');
            // Check if serialized data, added this part because the result has '";' added to it.
            if (false !== @unserialize($remove_date)) {
                $cnt = 0;
                while (! is_array($remove_date) && $cnt < 4) {
                    $remove_date = wlm_maybe_unserialize($remove_date);
                    ++$cnt;
                }
            }
            // End -> need to double check on this issue.
            if ($remove_date && $remove_date['date'] < $time) {
                ++$bad;
            } else {
                ++$good;
                $this->Update_UserLevelMeta($user_id, $level_id, 'wlm_schedule_level_cancel', $time);
                if ($reason && ( is_array($reason) || is_object($reason) )) {
                    $this->Update_UserLevelMeta($user_id, $level_id, 'schedule_level_cancel_reason', wlm_maybe_json_encode($reason));
                }
            }
        }
        if ($bad) {
            wlm_post_data()['notice'] = sprintf(
                // Translators: 1: [n] user / [n] users.
                __('Scheduled cancellation were not processed for %s because a scheduled removal with an earlier date is already in place.', 'wishlist-member'),
                sprintf(
                    // Translators: 1: number.
                    _n('%s user', '%s users', (int) $bad, 'wishlist-member'),
                    number_format_i18n((int) $bad)
                )
            );
        }
        return true;
    }

    /**
     * Run scheduled moving, removing and adding of user to levels
     */
    public function run_scheduled_user_levels()
    {
        global $wpdb;
        $levels       = false;
        $option_names = [
            'scheduled_add',
            'scheduled_move',
            'scheduled_remove',
        ];

        // Only select members with scheduled move, remove and add.
        $results = $wpdb->get_results('SELECT DISTINCT `ul`.`user_id`  FROM `' . esc_sql($this->table_names->userlevel_options) . '` AS `ulm` LEFT JOIN `' . esc_sql($this->table_names->userlevels) . '` AS `ul` ON `ulm`.`userlevel_id`=`ul`.`ID` WHERE `ulm`.`option_name` IN ("scheduled_add", "scheduled_remove", "scheduled_move")');

        $users = [];

        foreach ($results as $value) {
            $users[] = $value->user_id;
        }

        if (! empty($users)) {
            foreach ($users as $user) {
                foreach ($option_names as $name) {
                    $this->verify_scheduled_levels($user, $name);
                }
            }
        }
    }

    /**
     * Verify scheduled user levels
     *
     * @param integer $user_id     User ID.
     * @param string  $option_name Option name.
     */
    public function verify_scheduled_levels($user_id, $option_name)
    {
        $current_date = wp_date('Y-m-d h:i A');
        $levels       = $this->Get_Levels_From_UserLevelsMeta($user_id, $option_name);
        if (empty($levels)) {
            return;
        }
        $usr            = $this->get_user_data($user_id);
        $current_levels = $this->get_membership_levels($user_id);

        foreach ($levels as $level) {
            $meta = wlm_maybe_unserialize(wlm_maybe_unserialize($this->Get_UserLeveLMeta($user_id, $level, $option_name)));

            if (strtotime($meta['date']) <= strtotime($current_date)) {
                switch ($meta['type']) {
                    case 'move':
                        $current_levels = array_intersect($current_levels, [$level]);
                        $this->set_membership_levels($user_id, $current_levels);
                        // Continue to 'add' for adding timestamp and txn id.
                    case 'add':
                        $this->Delete_UserLevelMeta($user_id, $level, $option_name);
                        $this->user_level_timestamp($user_id, $level, time());
                        $this->set_membership_level_txn_id($user_id, $level, '');
                        $this->ar_subscribe($usr->first_name, $usr->last_name, $usr->user_email, $level);

                        // Send email notification.
                        $levels_email_notif = $meta['level_email_notif'];
                        if (!empty($levels_email_notif)) {
                            if ('dontsend' !== $levels_email_notif) {
                                $wpm_levels     = $this->get_option('wpm_levels');
                                $email_macros = [
                                    '[password]'    => '********',
                                    '[memberlevel]' => $wpm_levels[ $level ]['name'],
                                ];
                                // Use global default unless it's set to sendlevel.
                                $email_global_default = true;
                                if ('sendlevel' === $level_email) {
                                    $this->email_template_level = $level;
                                    $email_global_default = false;
                                }
                                $this->send_email_template('registration', $user_id, $email_macros, null, null, $email_global_default);
                                $this->send_email_template('admin_new_member_notice', $user_id, $email_macros, $this->get_option('email_sender_address'), null, $email_global_default);
                            }
                        }
                        break;
                    case 'remove':
                        $current_levels = array_diff($current_levels, [$level]);
                        $this->set_membership_levels($user_id, array_unique($current_levels));
                        break;
                }
            }
        }
    }

    /**
     * Performs cancellation of levels scheduled for cancellation.
     */
    public function cancel_scheduled_levels()
    {
        global $wpdb;
        $today = gmdate('Y-m-d H:i:s');

        // Only select members with wlm_schedule_level_cancel.
        $users   = [];
        $results = $wpdb->get_results('SELECT DISTINCT `ul`.`user_id`  FROM `' . esc_sql($this->table_names->userlevel_options) . '` AS `ulm` LEFT JOIN `' . esc_sql($this->table_names->userlevels) . '` AS `ul` ON `ulm`.`userlevel_id`=`ul`.`ID` WHERE `ulm`.`option_name` = "wlm_schedule_level_cancel"');

        foreach ($results as $value) {
            $users[] = $value->user_id;
        }

        if (! empty($users)) {
            foreach ($users as $user) {
                $levels = $this->Get_Levels_From_UserLevelsMeta($user, 'wlm_schedule_level_cancel');
                if (! empty($levels)) {
                    foreach ($levels as $level) {
                        $cancel_date = $this->Get_UserLevelMeta($user, $level, 'wlm_schedule_level_cancel');
                        if (! empty($cancel_date)) {
                            if (strtotime($cancel_date) <= strtotime($today)) {
                                $this->level_cancelled($level, $user, true);
                                $this->Delete_UserLevelMeta($user, $level, 'wlm_schedule_level_cancel');
                                $this->Delete_UserLevelMeta($user, $level, 'schedule_level_cancel_reason');
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Cancels upcoming scheduled cancellations
     * Note: Will cancel scheduled cancellations with past dates
     */
    public function cancel_scheduled_cancelations()
    {
        global $wpdb;
        $today = wlm_date('Y-m-d');
        $users = $wpdb->get_results("SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key`='wlm_schedule_member_cancel'");
        if (! empty($users)) {
            foreach ($users as $user) {
                $user_id      = $user->user_id;
                $cancel_array = $this->Get_UserMeta($user_id, 'wlm_schedule_member_cancel');
                if (! empty($cancel_array)) {
                    foreach ($cancel_array as $level => $cancel_date) {
                        if ($cancel_date <= $today) {
                            $this->level_cancelled($level, $user_id, true);
                            $this->remove_cancelled_schedule($level, $user_id);
                        }
                    }
                }
            }
        }
    }

    /**
     * Remove scheduled cancellations for a specific user-level pair
     *
     * @param string  $level_id Level ID.
     * @param integer $user_id  User ID.
     */
    public function remove_cancelled_schedule($level_id, $user_id)
    {
        $cancel_array = $this->Get_UserMeta($user_id, 'wlm_schedule_member_cancel');
        if (! empty($cancel_array)) {
            foreach ($cancel_array as $key => $value) {
                if ($key != $level_id) {
                    $new_array[ $key ] = $value;
                }
            }

            if (! empty($new_array)) {
                $this->Update_UserMeta($user_id, 'wlm_schedule_member_cancel', $new_array);
            } else {
                $this->Delete_UserMeta($user_id, 'wlm_schedule_member_cancel');
            }
        }
    }

    /**
     * Set Transaction ID of a Single Membership Level
     *
     * @param integer $user_id  User ID.
     * @param string  $level_id Level ID.
     * @param string  $txn_id   Transaction ID.
     */
    public function set_membership_level_txn_id($user_id, $level_id, $txn_id)
    {
        if (empty($txn_id)) {
            $txn_id = "WL-{$user_id}-{$level_id}";
        }
        if (preg_match('/^payperpost-(\d+)$/', $level_id, $match)) {
            $this->Update_ContentLevelMeta('U-' . $user_id, $match[1], 'transaction_id', $txn_id);
        } else {
            $this->Update_UserLevelMeta($user_id, $level_id, 'transaction_id', $txn_id);
        }
    }

    /**
     * Set Transaction IDs of Multiple Membership Levels
     *
     * @param integer $user_id User ID.
     * @param array   $levels  Associative array level_id=>txn_id pairs.
     */
    public function set_membership_level_txn_ids($user_id, $levels)
    {
        foreach ((array) $levels as $level => $txnid) {
            $this->set_membership_level_txn_id($user_id, $level, $txnid);
        }
    }

    /**
     * Get Transaction IDs
     *
     * @param  integer $user_id User ID.
     * @param  string  $txn_id  (optional) Transaction ID.
     * @return array Associative array level_id=>txn_id pairs.
     */
    public function get_membership_levels_txn_ids($user_id, $txn_id = '')
    {
        $levels = $this->get_membership_levels($user_id);
        $txns   = [];
        foreach ($levels as $level_id) {
            $txns[ $level_id ] = $this->Get_UserLevelMeta($user_id, $level_id, 'transaction_id');
        }
        if ($txn_id) {
            $txns = array_intersect($txns, (array) $txn_id);
        }
        return $txns;
    }

    /**
     * Get Transaction ID of a single Membership Level
     *
     * @param  integer $user_id  User ID.
     * @param  string  $level_id Membership Level.
     * @return string Transaction ID
     */
    public function get_membership_levels_txn_id($user_id, $level_id)
    {
        return $this->Get_UserLevelMeta($user_id, $level_id, 'transaction_id');
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_check_level_cancelations', [$wlm, 'cancel_scheduled_levels']);
        add_action('wishlistmember_check_scheduled_cancelations', [$wlm, 'cancel_scheduled_cancelations']);
        add_action('wishlistmember_run_scheduled_user_levels', [$wlm, 'run_scheduled_user_levels']);
    }
);
