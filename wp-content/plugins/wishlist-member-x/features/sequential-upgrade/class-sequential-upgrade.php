<?php

/**
 * Sequential Upgrade
 *
 * @package WishListMember\Features
 */

namespace WishListMember\Features;

/**
 * Sequential Upgrade Feature
 */
class Sequential_Upgrade
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Add `wishlistmember_sequential_upgrade` action to be triggered by cron.
        add_action('wishlistmember_sequential_upgrade', [$this, 'do_sequential']);

        // Add sequential upgrade wp-cron triggered every 15 minutes.
        if (! wp_next_scheduled('wishlistmember_sequential_upgrade')) {
            add_filter('cron_schedules', [wishlistmember_instance(), 'wlm_cron_schedules']);

            wp_schedule_event(time(), 'wlm_15minutes', 'wishlistmember_sequential_upgrade');
        }

        /**
         * Add `wishlistmember_sequential_upgrade` to the list of cron hooks
         * that are to be removed by WLM whenever it finds the need to do so
         */
        add_filter(
            'wishlistmember_remove_cron_hooks',
            function ($hooks) {
                $hooks[] = 'wishlistmember_sequential_upgrade';
                return $hooks;
            }
        );

        /**
         * Process sequential upgrade for user.
         */
        add_action('init', [$this, 'init']);

        // Add our methods to WishList Member's instance.
        add_filter(
            'wishlistmember_instance_methods',
            function ($methods) {
                $methods['DoSequential']  = [[$this, 'do_sequential'], true]; // Deprecated.
                $methods['do_sequential'] = [[$this, 'do_sequential']];

                $methods['DoSequentialForUser']    = [[$this, 'do_sequential_for_user'], true]; // Deprecated.
                $methods['do_sequential_for_user'] = [[$this, 'do_sequential_for_user']];

                $methods['SaveSequential']                        = [[$this, 'save_sequential_upgrade_configuration'], true]; // Deprecated.
                $methods['save_sequential_upgrade_configuration'] = [[$this, 'save_sequential_upgrade_configuration']];

                return $methods;
            }
        );
    }

    public function init()
    {
        $uid = get_current_user_id();
        if ($uid && empty(wlm_getcookie('wlm_user_sequential'))) {
            $this->do_sequential(get_current_user_id());
            wlm_setcookie('wlm_user_sequential', 1, time() + 3600, home_url('/', 'relative'));
            wlm_setcookie('wlm_user_sequential', 1, time() + 3600, site_url('/', 'relative'));
        }
    }

    /**
     * Execute sequential upgrade for all users if called by cron
     * Execute sequential upgrade for currently logged in user only if not called by cron
     *
     * @global object $wpdb
     * @param  integer|array $user_ids User ID or array of User IDs.
     */
    public function do_sequential($user_ids = '')
    {
        global $wpdb;
        ignore_user_abort(true);

        $wlm_is_doing_sequential_name = 'wlm_is_doing_sequential_' . wlm_server_data()['REMOTE_ADDR'];

        if ('yes' === get_transient($wlm_is_doing_sequential_name)) {
            return;
        }

        set_transient($wlm_is_doing_sequential_name, 'yes', 60 * 60 * 24);
        wlm_set_time_limit(60 * 60 * 12);

        if (is_numeric($user_ids) && ! empty($user_ids)) {
            $user_ids = [$user_ids];
        } elseif (! is_array($user_ids)) {
            $user_ids   = [];
            $wpm_levels = (array) wishlistmember_instance()->get_option('wpm_levels');

            $levels_for_sequential = [];
            foreach ($wpm_levels as $level_id => $level) {
                if (is_int($level_id) && ! empty($level['upgradeTo']) && ! empty($wpm_levels[ $level['upgradeTo'] ])) {
                    if (
                        ! (
                        ! $level['upgradeTo'] ||
                        ! $level['upgradeMethod'] ||
                        ( 'ondate' === $level['upgradeSchedule'] && $level['upgradeOnDate'] < 1 ) ||
                        ( 'MOVE' === $level['upgradeMethod'] && ! ( (int) $level['upgradeAfter'] ) && empty($level['upgradeSchedule']) )
                        )
                    ) {
                        $levels_for_sequential[] = $level_id;
                    }
                }
            }
            if ($levels_for_sequential) {
                $user_ids = $wpdb->get_col(
                    $wpdb->prepare(
                        'SELECT DISTINCT `user_id` FROM `' . esc_sql(wishlistmember_instance()->table_names->user_options) . '` WHERE `option_name`="sequential" AND `option_value`="1" AND `user_id` IN (SELECT DISTINCT `user_id` FROM `' . esc_sql(wishlistmember_instance()->table_names->userlevels) . '` WHERE `level_id` IN (' . implode(', ', array_fill(0, count($levels_for_sequential), '%s')) . '))',
                        ...array_values($levels_for_sequential)
                    )
                );
            }
        }

        if (! empty($user_ids)) {
            $force_sync = false;

            $user_ids = array_chunk($user_ids, 1000);
            while ($chunk = array_shift($user_ids)) {
                wishlistmember_instance()->PreLoad_UserLevelsMeta($chunk);
                while ($user_id = array_shift($chunk)) {
                    if (true === wishlistmember_instance()->do_sequential_for_user($user_id)) {
                        $force_sync = true;
                    }
                }
            }
            if ($force_sync) {
                wishlistmember_instance()->schedule_sync_membership();
            }
        }
        wlm_set_time_limit(ini_get('max_execution_time'));
        delete_transient($wlm_is_doing_sequential_name);
    }

    /**
     * Runs sequential upgrade for the specified user
     *
     * @param integer $id              User ID.
     * @param boolean $sync_membership True to sync membership details, default False.
     */
    public function do_sequential_for_user($id, $sync_membership = false)
    {
        static $wpm_levels = null;

        ignore_user_abort(true);

        $id = (int) $id;
        if (empty($id)) {
            return;
        }

        if (wishlistmember_instance()->is_temp_user($id)) {
            return;
        }

        /**
         * Make sure that only one instance for this user is running
         * used time to fix issues with some undesired behaviors if using 1
         */
        $fourteen_minutes = time() - ( 60 * 14 );
        $last_run         = get_transient('wlm_is_doing_sequential_for_' . $id);
        if (false && $last_run && $last_run >= $fourteen_minutes) {
            return $last_run;
        }
        set_transient('wlm_is_doing_sequential_for_' . $id, time(), 60 * 14);

        if (empty($wpm_levels)) {
            $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        }

        $user_levels = new \WishListMember\User($id);
        if (! $user_levels->sequential) {
            return;
        }
        $user_levels     = $user_levels->Levels;
        $original_levels = array_keys($user_levels);
        $processed       = [];
        $time            = time() + 1;

        $new_levels                  = [];
        $upgrade_email_notifications = [];
        do {
            $keep_going = false;
            foreach ($user_levels as $level_id => $user_level) {
                if ($user_level->Active) {
                    if (! $user_level->SequentialCancelled) {
                        if (! in_array($level_id, $processed)) {
                            $processed[ $level_id ] = $level_id;
                            $level_info             = &$wpm_levels[ $level_id ];
                            if (wlm_arrval($wpm_levels, wlm_arrval($level_info, 'upgradeTo')) || 'REMOVE' === wlm_arrval($level_info, 'upgradeMethod')) {
                                if ('ondate' === $level_info['upgradeSchedule']) {
                                    $upgrade_date = $level_info['upgradeOnDate'];
                                } else {
                                    $period       = $level_info['upgradeAfterPeriod'] ? $level_info['upgradeAfterPeriod'] : 'days';
                                    $upgrade_date = strtotime('+' . $level_info['upgradeAfter'] . ' ' . $period, $user_level->Timestamp);
                                }
                                if ($upgrade_date && $time > $upgrade_date) {

                                    /**
                                     * Start: decide whether to send welcome email or not
                                     * 0 = do not send
                                     * 1 = level settings
                                     * 2 = always send
                                     *
                                     * The logic makes sure that the higher setting is obeyed
                                     * and takes into account the possibility that the seq upgrade
                                     * went through the same level twice i.e. Levels "A" and "B" upgrades
                                     * to the same Level "C" and the upgrade happened at the same time.
                                     * If Level A is set to "always send" (value=2) and Level B is set to
                                     * "do not send" (value=0) then the email will be sent because 2 > 0
                                     */
                                    $x = (int) wlm_arrval($upgrade_email_notifications, $level_info['upgradeTo']);
                                    $y = (int) wlm_arrval($level_info, 'upgradeEmailNotification');
                                    if ($y > $x) {
                                        $upgrade_email_notifications[ $level_info['upgradeTo'] ] = $y;
                                    }
                                    // End: decide whether to send welcome email or not.


                                    /**
                                     * If the Upgrade To level was already previously processed, we skip the loop
                                     * This is to avoid the infinite loop on this scenario (Move from Level A to Level B on XXX and Move from Level B to Level A on XXX.)
                                     */
                                    if (in_array($level_info['upgradeTo'], (array) $new_levels)) {
                                        continue;
                                    }

                                    $keep_going = true;
                                    if ('MOVE' === $level_info['upgradeMethod'] || 'REMOVE' === $level_info['upgradeMethod']) {
                                        unset($processed[ $level_id ]);
                                        unset($user_levels[ $level_id ]);
                                    }
                                    if (! isset($user_levels[ $level_info['upgradeTo'] ]) && 'REMOVE' !== $level_info['upgradeMethod']) {
                                        $new_levels[]                            = $level_info['upgradeTo'];
                                        $user_levels[ $level_info['upgradeTo'] ] = (object) [
                                            'Timestamp' => $upgrade_date,
                                            'TxnID'     => $user_level->TxnID,
                                            'Active'    => true,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } while ($keep_going);

        $seqlevels     = array_keys($user_levels);
        $seqlevels_new = array_diff($seqlevels, $original_levels);

        wishlistmember_instance()->set_membership_levels(
            $id,
            $seqlevels,
            [
                'set_timestamp'      => false,
                'set_transaction_id' => false,
                'sync'               => false,
            ]
        );

        $ts = [];
        $tx = [];
        foreach ($user_levels as $level_id => $user_level) {
            $ts[ $level_id ] = $user_level->Timestamp;
            $tx[ $level_id ] = $user_level->TxnID;
        }

        wishlistmember_instance()->user_level_timestamps($id, $ts);
        wishlistmember_instance()->set_membership_level_txn_ids($id, $tx);

        /**
         * Start: send welcome email if configured in seq upgrade
         */
        // Password is always unknown.
        $macros = ['[password]' => '********'];
        foreach ($seqlevels_new as $level_id) {
            // Set the current email_template_level so send_email_template() knows what level we're dealing with.
            wishlistmember_instance()->email_template_level = $level_id;
            // Set level name macro.
            $macros['[memberlevel]'] = $wpm_levels[ $level_id ]['name'];

            switch ((int) wlm_arrval($upgrade_email_notifications, $level_id)) {
                case 2: // Always send email.
                    /**
                     * Send the per level email template even if it's off
                     * also save the Closure in $x so we can remove the action later
                     */
                    add_filter(
                        'wishlistmember_per_level_template_setting',
                        $x = function ($setting_value, $setting_name, $user_id, $level_id) {
                            if ('newuser_notification_user' === $setting_name && wishlistmember_instance()->email_template_level == $level_id) {
                                return true;
                            }
                            return $setting_value;
                        },
                        10,
                        4
                    );
                    // Continue to "1" to send the email.
                case 1: // Send email according to level setting.
                    // Send the email template.
                    wishlistmember_instance()->send_email_template('registration', $id, $macros);

                    /**
                     * Since we are in a loop we remove our 'wishlistmember_per_level_template_setting' filter
                     * because we do not want multiple filters of the same type being registered
                     */
                    if (! empty($x)) {
                        remove_filter('wishlistmember_per_level_template_setting', $x);
                        unset($x);
                    }
                    break;
                case 0: // Do nothing, sequential upgrade does not send emails by default.
                default:
                    break;
            }
        }
        unset(wishlistmember_instance()->send_email_template);
        /**
         * End: send welcome email if configured in seq upgrade
         */

        do_action_deprecated('wlm_do_sequential_upgrade', [$id, $seqlevels_new, $seqlevels], '3.10', 'wishlistmember_do_sequential_upgrade');
        do_action('wishlistmember_do_sequential_upgrade', $id, $seqlevels_new, $seqlevels);

        if ($sync_membership) {
            wishlistmember_instance()->schedule_sync_membership();
        }

        return true;
    }

    /**
     * Save Sequential Upgrade Configuration
     *
     * Data submitted via $_POST
     */
    public function save_sequential_upgrade_configuration()
    {
        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        $err        = [];
        $saved      = [];

        $post = wlm_post_data(true); // Data submitted via $_POST.

        foreach (array_keys($wpm_levels) as $key) {
            if (empty($post['upgradeMethod'][ $key ])) {
                continue;
            }

            if ('ondate' === $post['upgradeSchedule'][ $key ]) {
                $upgrade_on_date = date_create_from_format(get_option('date_format') . ' ' . get_option('time_format') . 'e', $post['upgradeOnDate'][ $key ] . wishlistmember_instance()->get_wp_timezone())->getTimestamp();
            }

            $level_name = isset($wpm_levels[ $key ]['name']) ? wlm_trim($wpm_levels[ $key ]['name']) : '_Unknown_Level_';
            $level_name = ! empty($level_name) ? $level_name : '_Unknown_Level_';

            if ('inactive' === $post['upgradeMethod'][ $key ]) {
                $wpm_levels[ $key ]['upgradeMethod'] = '';
                $wpm_levels[ $key ]['upgradeTo']     = '';

                $wpm_levels[ $key ]['upgradeSchedule'] = '';
                $wpm_levels[ $key ]['upgradeAfter']    = '';

                $wpm_levels[ $key ]['upgradeAfterPeriod'] = '';
                $wpm_levels[ $key ]['upgradeOnDate']      = '';

                $wpm_levels[ $key ]['upgradeEmailNotification'] = '';
            } else {
                if (empty($post['upgradeMethod'][ $key ]) && ! empty($post['upgradeTo'][ $key ])) {
                    // Translators: 1: Level name.
                    $err[] = sprintf(__('No "Method" was specified for Membership Level "%1$s"', 'wishlist-member'), $level_name);
                    continue;
                }
                if (empty($post['upgradeTo'][ $key ]) && ! empty($post['upgradeMethod'][ $key ]) && 'REMOVE' !== $post['upgradeMethod'][ $key ]) {
                    // Translators: 1: Level name.
                    $err[] = sprintf(__('No Membership Level to "Upgrade To" was specified for Membership Level "%1$s"', 'wishlist-member'), $level_name);
                    continue;
                }
                if ('MOVE' === $post['upgradeMethod'][ $key ] && empty($post['upgradeSchedule'][ $key ]) && ! ( (int) $post['upgradeAfter'][ $key ] )) {
                    // Translators: 1: Level name.
                    $err[] = sprintf(__('Members cannot be Moved to another Level after 0 days. Please select a number of Days or change the Sequential Upgrade Method in Membership Level "%1$s".', 'wishlist-member'), $level_name);
                    continue;
                }
                if ('REMOVE' === $post['upgradeMethod'][ $key ] && empty($post['upgradeSchedule'][ $key ]) && ! ( (int) $post['upgradeAfter'][ $key ] )) {
                    // Translators: 1: Level name.
                    $err[] = sprintf(__('Members cannot be Removed from a Level after 0 days. Please select a number of Days or change the Sequential Upgrade Method in Membership Level "%1$s".', 'wishlist-member'), $level_name);
                    continue;
                }
                if ('ondate' === $post['upgradeSchedule'][ $key ] && $upgrade_on_date < 1) {
                    // Translators: 1: Level name.
                    $err[] = sprintf(__('Invalid Date in Membership Level "%1$s".', 'wishlist-member'), $level_name);
                    continue;
                }

                if ('ondate' === $post['upgradeSchedule'][ $key ]) {
                    $date_now = time();

                    if (empty($wpm_levels[ $key ]['upgradeOnDate'])) {
                        // If there's no set ondate yet then only allow future dates.
                        if ($upgrade_on_date < $date_now) {
                            // Translators: 1: Level name.
                            $err[] = sprintf(__('The selected date for the Sequential Upgrade has passed.  Please select an upcoming date for Membership Level "%1$s".', 'wishlist-member'), $level_name);
                            continue;
                        }
                    } else {
                        // If the saved ondate is in the past then we allow user to changed it up to the set date.
                        if ($wpm_levels[ $key ]['upgradeOnDate'] < $date_now) {
                            if ($upgrade_on_date < $wpm_levels[ $key ]['upgradeOnDate']) {
                                // Translators: 1: Level name.
                                $err[] = sprintf(__('The selected date for the Sequential Upgrade has passed for Membership Level "%1$s".', 'wishlist-member'), $level_name);
                                continue;
                            }
                        } elseif ($upgrade_on_date < $date_now) {
                            // Translators: 1: Level name.
                            $err[] = sprintf(__('The selected date for the Sequential Upgrade has passed for Membership Level "%1$s".', 'wishlist-member'), $level_name);
                            continue;
                        }
                    }
                }

                if (empty($post['upgradeMethod'][ $key ]) || ( empty($post['upgradeTo'][ $key ]) && 'REMOVE' !== $post['upgradeMethod'][ $key ] )) {
                    continue;
                }

                $wpm_levels[ $key ]['upgradeMethod'] = $post['upgradeMethod'][ $key ];
                $wpm_levels[ $key ]['upgradeTo']     = $post['upgradeTo'][ $key ];

                $wpm_levels[ $key ]['upgradeSchedule'] = $post['upgradeSchedule'][ $key ];
                $wpm_levels[ $key ]['upgradeAfter']    = (int) $post['upgradeAfter'][ $key ];

                $wpm_levels[ $key ]['upgradeAfterPeriod'] = $wpm_levels[ $key ]['upgradeAfter'] ? $post['upgradeAfterPeriod'][ $key ] : '';
                $wpm_levels[ $key ]['upgradeOnDate']      = ( $upgrade_on_date < 1 ) ? '' : $upgrade_on_date;

                $wpm_levels[ $key ]['upgradeEmailNotification'] = $post['upgradeEmailNotification'][ $key ];
            }

            $saved[] = $level_name;
        }
        if (count($saved)) {
            wishlistmember_instance()->save_option('wpm_levels', $wpm_levels);
            // Translators: 1: Level or Levels, 2: List of comma-separated Membership levels.
            wishlistmember_instance()->msg = sprintf(__('Sequential Upgrade settings saved for Membership %1$s %2$s', 'wishlist-member'), _n('Level', 'Levels', count($saved), 'wishlist-member'), '"' . implode('", "', $saved) . '"');
        }
        if ($err) {
            wishlistmember_instance()->err = implode('<br>', $err);
        }
    }
}
