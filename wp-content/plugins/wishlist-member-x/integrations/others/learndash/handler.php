<?php

/*
 * LearnDash Integration File
 * LearnDash Site: http://learndash.com/
 * Original Integration Author : Fel Jun Palawan
 * Version: $Id$
 */
if (! class_exists('WLM_OTHER_INTEGRATION_LEARNDASH')) {

    class WLM_OTHER_INTEGRATION_LEARNDASH
    {
        private $settings     = [];
        public $plugin_active = false;

        public function __construct()
        {
            $data                            = wishlistmember_instance()->get_option('learndash_settings');
            $this->settings                  = is_array($data) ? $data : [];
            $this->settings['group_default'] = wishlistmember_instance()->get_option('wlm_ld_group_default');

            // Check if LearnDash LMS is active.
            $active_plugins = wlm_get_active_plugins();
            if (in_array('LearnDash LMS', $active_plugins) || isset($active_plugins['sfwd-lms/sfwd_lms.php']) || is_plugin_active('sfwd-lms/sfwd_lms.php')) {
                $this->plugin_active = true;
            }

            $this->load_hooks();
        }

        public function load_hooks()
        {
            if ($this->plugin_active) {
                add_action('wishlistmember_user_registered', [$this, 'NewUserRegHook'], 99, 2);
                add_action('wishlistmember_add_user_levels_shutdown', [$this, 'AddUserLevelHook'], 10, 3);

                add_action('wishlistmember_confirm_user_levels', [$this, 'ConfirmApproveUserLevelsHook'], 99, 2);
                add_action('wishlistmember_approve_user_levels', [$this, 'ConfirmApproveUserLevelsHook'], 99, 2);

                add_action('wishlistmember_remove_user_levels', [$this, 'RemoveUserLevelHook'], 99, 2);
                add_action('wishlistmember_cancel_user_levels', [$this, 'CancelUserLevelHook'], 99, 2);
                add_action('wishlistmember_uncancel_user_levels', [$this, 'ReregUserLevelHook'], 99, 2);
                add_action('wishlistmember_expire_user_levels', [$this, 'ExpiredUserLevelHook'], 99, 2);

                add_action('wishlistmember_save_other_provider', [$this, 'enroll_existing_members']);

                add_action('learndash_update_course_access', [$this, 'CourseUpdatedHook'], 99, 4);
                add_action('learndash_course_completed', [$this, 'CourseCompletedHook'], 99, 1);

                add_action('ld_removed_group_access', [$this, 'GroupRemovedHook'], 99, 2);
                add_action('ld_added_group_access', [$this, 'GroupAddedHook'], 99, 2);

                if (isset($this->settings['group_default']) && $this->settings['group_default']) {
                    add_action('save_post_groups', [$this, 'SaveGroupDefaultActions'], 10, 3);
                }
            }
        }

        /**
         * Enrolls existing members of a level to courses
         * Action: `wishlistmember_save_other_provider`
         *
         * @param array $data Save data. Expects 'enroll-existing-members' and 'learndash_settings[level][{level_id}]' in $data.
         */
        public function enroll_existing_members($data)
        {
            // Get courses to enroll to.
            $enroll = wlm_arrval($data, 'enroll-existing-members');
            // Get membership level.
            if (! empty(wlm_arrval($data, 'learndash_settings', 'level'))) {
                $level = key(wlm_arrval($data, 'learndash_settings', 'level'));
            }
            if (! is_array($enroll) || ! $enroll || ! $level) {
                // $enroll and $level are both required.
                return;
            }

            // Get active members of the level.
            $member_ids = wishlistmember_instance()->member_ids_by_status('active', $level);
            // Add members of $level to the courses in $enroll.
            foreach ($member_ids as $uid) {
                foreach ($enroll as $course_id) {
                    $this->call_ld_update_course_access($uid, $course_id, false);
                }
            }
        }

        /**
         * Calls the Learndash function ld_update_course_access with function_exists checker.
         *
         * @param integer $uid       User ID.
         * @param integer $course_id Course ID.
         * @param boolean $remove    Optional. Whether to remove course access for the user. Default false.
         *
         * @return boolean Returns true if the user course access update was successful otherwise false.
         */
        public function call_ld_update_course_access($uid, $course_id, $remove = false)
        {
            if (function_exists('ld_update_course_access')) {
                return ld_update_course_access($uid, $course_id, $remove);
            }
        }

        /**
         * Perform LearnDash actions.
         *
         * @param integer $uid      User ID.
         * @param array   $settings Settings array.
         */
        public function do_learndash_actions($uid, $settings)
        {
            $apply_course  = isset($settings['apply_course']) && is_array($settings['apply_course']) ? $settings['apply_course'] : [];
            $remove_course = isset($settings['remove_course']) && is_array($settings['remove_course']) ? $settings['remove_course'] : [];
            $apply_group   = isset($settings['apply_group']) && is_array($settings['apply_group']) ? $settings['apply_group'] : [];
            $remove_group  = isset($settings['remove_group']) && is_array($settings['remove_group']) ? $settings['remove_group'] : [];

            foreach ($apply_course as $course_id) {
                if (! empty($course_id)) {
                    $this->call_ld_update_course_access($uid, $course_id, false);
                }
            }
            foreach ($remove_course as $course_id) {
                if (! empty($course_id)) {
                    $this->call_ld_update_course_access($uid, $course_id, true);
                }
            }
            foreach ($apply_group as $group_id) {
                if (! empty($group_id)) {
                    ld_update_group_access($uid, $group_id, false);
                }
            }
            foreach ($remove_group as $group_id) {
                if (! empty($group_id)) {
                    ld_update_group_access($uid, $group_id, true);
                }
            }
        }

        public function SaveGroupDefaultActions($post_id, $post, $update)
        {
            // If revision or update, disregard.
            if ($update || wp_is_post_revision($post_id)) {
                return;
            }

            $data = wishlistmember_instance()->get_option('learndash_settings');
            if (! isset($data['group']['default'])) {
                return;
            }
            $data['group'][ $post_id ] = $data['group']['default'];
            wishlistmember_instance()->save_option('learndash_settings', $data);
        }

        public function GroupAddedHook($user_id, $group_id)
        {
            $action   = 'add';
            $settings = isset($this->settings['group'][ $group_id ][ $action ]) ? $this->settings['group'][ $group_id ][ $action ] : [];
            $this->DoCourseHook($user_id, $group_id, $action, $settings, false);
        }

        public function GroupRemovedHook($user_id, $group_id)
        {
            $action   = 'remove';
            $settings = isset($this->settings['group'][ $group_id ][ $action ]) ? $this->settings['group'][ $group_id ][ $action ] : [];
            $this->DoCourseHook($user_id, $group_id, $action, $settings, false);
        }

        public function CourseCompletedHook($data)
        {
            $user   = isset($data['user']) ? $data['user'] : false;
            $course = isset($data['course']) ? $data['course'] : false;
            if (! $user || ! $course) {
                return;
            }

            $wpuser    = isset($user->ID) ? $user->ID : false;
            $course_id = isset($course->ID) ? $course->ID : false;
            if (! $wpuser || ! $course_id) {
                return;
            }

            $action   = 'complete';
            $settings = isset($this->settings['course'][ $course_id ][ $action ]) ? $this->settings['course'][ $course_id ][ $action ] : [];
            $this->DoCourseHook($wpuser, $course_id, $action, $settings);
        }

        public function CourseUpdatedHook($wpuser, $course_id, $access_list, $remove)
        {
            $action   = $remove ? 'remove' : 'add';
            $settings = isset($this->settings['course'][ $course_id ][ $action ]) ? $this->settings['course'][ $course_id ][ $action ] : [];
            $this->DoCourseHook($wpuser, $course_id, $action, $settings);
        }

        private function DoCourseHook($wpuser, $hook_id, $action, $settings, $is_course = true)
        {

            $added_levels     = isset($settings['add_level']) && is_array($settings['add_level']) ? $settings['add_level'] : [];
            $added_levels     = array_filter($added_levels); // Remove empty values.
            $cancelled_levels = isset($settings['cancel_level']) && is_array($settings['cancel_level']) ? $settings['cancel_level'] : [];
            $cancelled_levels = array_filter($cancelled_levels); // Remove empty values.
            $removed_levels   = isset($settings['remove_level']) && is_array($settings['remove_level']) ? $settings['remove_level'] : [];
            $removed_levels   = array_filter($removed_levels); // Remove empty values.

            $current_user_mlevels = wishlistmember_instance()->get_membership_levels($wpuser);
            $wpm_levels           = wishlistmember_instance()->get_option('wpm_levels');

            $prefix = $is_course ? 'C' : 'G';

            $action = strtoupper(substr($action, 0, 1));
            $txnid  = "LearnDash-{$action}{$prefix}{$hook_id}-";
            // Add to level.
            if (count($added_levels) > 0) {
                $user_mlevels  = $current_user_mlevels;
                $add_level_arr = $added_levels;
                foreach ($add_level_arr as $id => $add_level) {
                    if (! isset($wpm_levels[ $add_level ])) {
                        continue;// check if valid level
                    }
                    if (! in_array($add_level, $user_mlevels)) {
                        $user_mlevels[] = $add_level;
                        $new_levels[]   = $add_level; // Record the new level.
                        wishlistmember_instance()->set_membership_levels($wpuser, $user_mlevels);
                        wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid

                        // Send New Member Email Notifications.
                        $macros['[password]']    = '********';
                        $macros['[memberlevel]'] = $wpm_levels[ $add_level ]['name'];
                        wishlistmember_instance()->send_email_template('registration', $wpuser, $macros);
                        wishlistmember_instance()->send_email_template('admin_new_member_notice', $wpuser, $macros, wishlistmember_instance()->get_option('email_sender_address'));
                    } else {
                        // For cancelled members.
                        $cancelled      = wishlistmember_instance()->level_cancelled($add_level, $wpuser);
                        $resetcancelled = true; // Lets make sure that old versions without this settings still works.
                        if (isset($wpm_levels[ $add_level ]['uncancelonregistration'])) {
                            $resetcancelled = 1 == $wpm_levels[ $add_level ]['uncancelonregistration'];
                        }
                        if ($cancelled && $resetcancelled) {
                            $ret = wishlistmember_instance()->level_cancelled($add_level, $wpuser, false);
                            wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid
                        }

                        // For Expired Members.
                        $expired      = wishlistmember_instance()->level_expired($add_level, $wpuser);
                        $resetexpired = 1 == $wpm_levels[ $add_level ]['registrationdatereset'];
                        if ($expired && $resetexpired) {
                            wishlistmember_instance()->user_level_timestamp($wpuser, $add_level, time());
                            wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid
                        } else {
                            // If levels has expiration and allow reregistration for active members.
                            $levelexpires     = isset($wpm_levels[ $add_level ]['expire']) ? (int) $wpm_levels[ $add_level ]['expire'] : false;
                            $levelexpires_cal = isset($wpm_levels[ $add_level ]['calendar']) ? $wpm_levels[ $add_level ]['calendar'] : false;
                            $resetactive      = 1 == $wpm_levels[ $add_level ]['registrationdateresetactive'];
                            if ($levelexpires && $resetactive) {
                                // Get the registration date before it gets updated because we will use it later.
                                $levelexpire_regdate = wishlistmember_instance()->Get_UserLevelMeta($wpuser, $add_level, 'registration_date');

                                $levelexpires_cal = in_array($levelexpires_cal, ['Days', 'Weeks', 'Months', 'Years']) ? $levelexpires_cal : false;
                                if ($levelexpires_cal && $levelexpire_regdate) {
                                    list( $xdate, $xfraction )                                 = explode('#', $levelexpire_regdate);
                                    list( $xyear, $xmonth, $xday, $xhour, $xminute, $xsecond ) = preg_split('/[- :]/', $xdate);
                                    if ('Days' === $levelexpires_cal) {
                                        $xday = $levelexpires + $xday;
                                    }
                                    if ('Weeks' === $levelexpires_cal) {
                                        $xday = ( $levelexpires * 7 ) + $xday;
                                    }
                                    if ('Months' === $levelexpires_cal) {
                                        $xmonth = $levelexpires + $xmonth;
                                    }
                                    if ('Years' === $levelexpires_cal) {
                                        $xyear = $levelexpires + $xyear;
                                    }
                                    wishlistmember_instance()->user_level_timestamp($wpuser, $add_level, mktime($xhour, $xminute, $xsecond, $xmonth, $xday, $xyear));
                                    wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid
                                }
                            }
                        }
                    }
                }
                // Refresh for possible new levels.
                $current_user_mlevels = wishlistmember_instance()->get_membership_levels($wpuser);
            }
            // Cancel from level.
            if (count($cancelled_levels) > 0) {
                $user_mlevels = $current_user_mlevels;
                foreach ($cancelled_levels as $id => $cancel_level) {
                    if (! isset($wpm_levels[ $cancel_level ])) {
                        continue;// check if valid level
                    }
                    if (in_array($cancel_level, $user_mlevels)) {
                        $ret = wishlistmember_instance()->level_cancelled($cancel_level, $wpuser, true);
                    }
                }
            }
            // Remove from level.
            if (count($removed_levels) > 0) {
                $user_mlevels = $current_user_mlevels;
                // Flag to check if any of the $remove_levels is still present in the current levels of the user.
                // If it's not then let's skip updating the levels of the member to prevent the hook looping.
                $proceed_with_remove = false;

                foreach ($removed_levels as $id => $remove_level) {
                    $arr_index = array_search($remove_level, $user_mlevels);
                    if (false !== $arr_index) {
                        unset($user_mlevels[ $arr_index ]);
                        $proceed_with_remove = true;
                    }
                }
                if ($proceed_with_remove) {
                    wishlistmember_instance()->set_membership_levels($wpuser, $user_mlevels);
                    wishlistmember_instance()->schedule_sync_membership(true);
                }
            }
        }

        public function ConfirmApproveUserLevelsHook($uid = null, $levels = null)
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            $levels = is_array($levels) ? $levels : (array) $levels;

            foreach ($levels as $key => $lvl) {
                $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($lvl, $uid);
                $level_for_approval = wishlistmember_instance()->level_for_approval($lvl, $uid);

                $settings = isset($this->settings['level'][ $lvl ]['add']) ? $this->settings['level'][ $lvl ]['add'] : [];
                if (! $level_unconfirmed && ! $level_for_approval) {
                    $this->do_learndash_actions($uid, $settings);
                }
            }
        }

        // FOR NEW USERS.
        public function NewUserRegHook($uid = null, $udata = null)
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($udata['wpm_id'], $uid);
            $level_for_approval = wishlistmember_instance()->level_for_approval($udata['wpm_id'], $uid);

            $settings = isset($this->settings['level'][ $udata['wpm_id'] ]['add']) ? $this->settings['level'][ $udata['wpm_id'] ]['add'] : [];
            if (! $level_unconfirmed && ! $level_for_approval) {
                $this->do_learndash_actions($uid, $settings);
            }
        }

        // WHEN ADDED TO LEVELS.
        public function AddUserLevelHook($uid, $addlevels = '')
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            $addlevels   = is_array($addlevels) ? $addlevels : (array) $addlevels;
            $level_added = $addlevels;
            $level_added = reset($level_added); // Get the first element.
            // If from registration then don't don't process if the $addlevels is.
            // The same level the user registered to. This is already processed by NewUserTagsQueue func.
            if (isset(wlm_post_data()['action']) && 'wpm_register' === wlm_post_data()['action']) {
                if (wlm_post_data()['wpm_id'] == $level_added) {
                    return;
                }
            }

            foreach ($addlevels as $key => $lvl) {
                $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($lvl, $uid);
                $level_for_approval = wishlistmember_instance()->level_for_approval($lvl, $uid);
                $settings = isset($this->settings['level'][ $lvl ]['add']) ? $this->settings['level'][ $lvl ]['add'] : [];
                if (! $level_unconfirmed && ! $level_for_approval) {
                    $this->do_learndash_actions($uid, $settings);
                } elseif (isset(wlm_post_data()['SendMail'])) {
                    // This elseif condition fixes the issue where members who are added via.
                    // WLM API aren't being processed.
                    $this->do_learndash_actions($uid, $settings);
                }
            }
        }

        // WHEN REREGISTERED FROM LEVELS.
        public function ReregUserLevelHook($uid, $levels = '')
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            // Lets check for PPPosts.
            $levels = (array) $levels;
            foreach ($levels as $key => $level) {
                if (false !== strrpos($level, 'U-')) {
                    unset($levels[ $key ]);
                }
            }
            if (count($levels) <= 0) {
                return;
            }

            foreach ($levels as $level) {
                $settings = isset($this->settings['level'][ $level ]['rereg']) ? $this->settings['level'][ $level ]['rereg'] : [];
                $this->do_learndash_actions($uid, $settings);
            }
        }

        // WHEN REMOVED FROM LEVELS.
        public function RemoveUserLevelHook($uid, $removedlevels = '')
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            // Lets check for PPPosts.
            $levels = (array) $removedlevels;
            foreach ($levels as $key => $level) {
                if (false !== strrpos($level, 'U-')) {
                    unset($levels[ $key ]);
                }
            }
            if (count($levels) <= 0) {
                return;
            }

            foreach ($levels as $level) {
                $settings = isset($this->settings['level'][ $level ]['remove']) ? $this->settings['level'][ $level ]['remove'] : [];
                $this->do_learndash_actions($uid, $settings);
            }
        }

        // WHEN CANCELLED FROM LEVELS.
        public function CancelUserLevelHook($uid, $cancellevels = '')
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            // Lets check for PPPosts.
            $levels = (array) $cancellevels;
            foreach ($levels as $key => $level) {
                if (false !== strrpos($level, 'U-')) {
                    unset($levels[ $key ]);
                }
            }
            if (count($levels) <= 0) {
                return;
            }

            foreach ($levels as $level) {
                $settings = isset($this->settings['level'][ $level ]['cancel']) ? $this->settings['level'][ $level ]['cancel'] : [];
                $this->do_learndash_actions($uid, $settings);
            }
        }

        // WHEN LEVEL EXPIRES.
        public function ExpiredUserLevelHook($uid, $expiredlevels = '')
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            // Lets check for PPPosts.
            $levels = (array) $expiredlevels;
            foreach ($levels as $key => $level) {
                if (false !== strrpos($level, 'U-')) {
                    unset($levels[ $key ]);
                }
            }
            if (count($levels) <= 0) {
                return;
            }

            foreach ($levels as $level) {
                $settings = isset($this->settings['level'][ $level ]['expired']) ? $this->settings['level'][ $level ]['expired'] : [];
                $this->do_learndash_actions($uid, $settings);
            }
        }
    }
}

$WLMLearnDashInstance = new WLM_OTHER_INTEGRATION_LEARNDASH();
