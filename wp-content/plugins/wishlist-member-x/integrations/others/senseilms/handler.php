<?php

/*
 * SenseiLMS Integration File
 * LearnDash Site: https://senseilms.com/
 * Original Integration Author : Fel Jun Palawan
 * Version: $Id$
 */
if (! class_exists('WLM_OTHER_INTEGRATION_SenseiLMS')) {

    class WLM_OTHER_INTEGRATION_SenseiLMS
    {
        private $settings     = [];
        public $plugin_active = false;

        public function __construct()
        {

            $data           = wishlistmember_instance()->get_option('senseilms_settings');
            $this->settings = is_array($data) ? $data : [];

            // Check if LearnDash LMS is active.
            $active_plugins = wlm_get_active_plugins();
            if (in_array('Sensei LMS', $active_plugins) || isset($active_plugins['sensei-lms/sensei-lms.php']) || is_plugin_active('sensei-lms/sensei-lms.php')) {
                $this->plugin_active = class_exists('Sensei_Utils');
            }

            $this->load_hooks();
        }

        public function load_hooks()
        {

            if ($this->plugin_active) {
                add_action('wishlistmember_user_registered', [$this, 'NewUserTagsHook'], 99, 2);
                add_action('wishlistmember_add_user_levels_shutdown', [$this, 'AddUserTagsHook'], 10, 3);

                add_action('wishlistmember_confirm_user_levels', [$this, 'ConfirmApproveLevelsTagsHook'], 99, 2);
                add_action('wishlistmember_approve_user_levels', [$this, 'ConfirmApproveLevelsTagsHook'], 99, 2);

                add_action('wishlistmember_pre_remove_user_levels', [$this, 'RemoveUserTagsHook'], 99, 2);
                add_action('wishlistmember_cancel_user_levels', [$this, 'CancelUserTagsHook'], 99, 2);
                add_action('wishlistmember_uncancel_user_levels', [$this, 'ReregUserTagsHook'], 99, 2);

                add_action('wishlistmember_save_other_provider', [$this, 'enroll_existing_members']);

                add_action('sensei_user_course_start', [$this, 'CourseStartedHook'], 99, 2);
                add_action('sensei_user_course_reset', [$this, 'CourseResetHook'], 99, 2);
                add_action('sensei_user_course_end', [$this, 'CourseCompletedHook'], 99, 2);

                // Sensei actions.
                // Do_action( 'sensei_user_course_reset', $user_id, $course_id );
                // Do_action( 'sensei_user_course_start', $user_id, $course_id );
                // Do_action( 'sensei_user_course_end', $user_id, $course_id );
            }
        }

            /**
             * Enrolls existing members of a level to courses
             * Action: `wishlistmember_save_other_provider`
             *
             * @param array $data Save data. Expects 'enroll-existing-members' and 'senseilms_settings[level][{level_id}]' in $data
             */
        public function enroll_existing_members($data)
        {
            // Get courses to enroll to.
            $enroll = wlm_arrval($data, 'enroll-existing-members');
            // Get membership level.
            $level = key(wlm_arrval($data, 'senseilms_settings', 'level'));
            if (! is_array($enroll) || ! $enroll || ! $level) {
                // $enroll and $level are both required.
                return;
            }

            // Get active members of the level.
            $member_ids = wishlistmember_instance()->member_ids_by_status('active', $level);
            // Add members of $level to the courses in $enroll.
            $mep = Sensei_Course_Enrolment_Manager::instance()->get_manual_enrolment_provider();
            foreach ($member_ids as $uid) {
                foreach ($enroll as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
            }
        }

        public function CourseStartedHook($user_id, $course_id)
        {
            $action   = 'add';
            $settings = isset($this->settings['course'][ $course_id ][ $action ]) ? $this->settings['course'][ $course_id ][ $action ] : [];
            $this->DoCourseHook($user_id, $course_id, $action, $settings);
        }

        public function CourseResetHook($user_id, $course_id)
        {
            $action   = 'remove';
            $settings = isset($this->settings['course'][ $course_id ][ $action ]) ? $this->settings['course'][ $course_id ][ $action ] : [];
            $this->DoCourseHook($user_id, $course_id, $action, $settings);
        }

        public function CourseCompletedHook($user_id, $course_id)
        {
            $action   = 'complete';
            $settings = isset($this->settings['course'][ $course_id ][ $action ]) ? $this->settings['course'][ $course_id ][ $action ] : [];
            $this->DoCourseHook($user_id, $course_id, $action, $settings);
        }

        private function DoCourseHook($wpuser, $hook_id, $action, $settings, $is_course = true)
        {

            $added_levels     = isset($settings['add_level']) ? $settings['add_level'] : [];
            $cancelled_levels = isset($settings['cancel_level']) ? $settings['cancel_level'] : [];
            $removed_levels   = isset($settings['remove_level']) ? $settings['remove_level'] : [];

            $current_user_mlevels = wishlistmember_instance()->get_membership_levels($wpuser);
            $wpm_levels           = wishlistmember_instance()->get_option('wpm_levels');

            $prefix = $is_course ? 'C' : 'G';

            $action = strtoupper(substr($action, 0, 1));
            $txnid  = "SENSEI-{$action}-{$prefix}{$hook_id}-";
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
                        // Wishlistmember_instance()->set_membership_level_txn_id( $wpuser, $cancel_level, "{$txnid}".time() );//update txnid.
                    }
                }
            }
            // Remove from level.
            if (count($removed_levels) > 0) {
                $user_mlevels = $current_user_mlevels;
                foreach ($removed_levels as $id => $remove_level) {
                    $arr_index = array_search($remove_level, $user_mlevels);
                    if (false !== $arr_index) {
                        unset($user_mlevels[ $arr_index ]);
                    }
                }
                wishlistmember_instance()->set_membership_levels($wpuser, $user_mlevels);
                wishlistmember_instance()->schedule_sync_membership(true);
            }
        }

        public function ConfirmApproveLevelsTagsHook($uid = null, $levels = null)
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            $levels             = is_array($levels) ? $levels : (array) $levels;
            $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($levels[0], $uid);
            $level_for_approval = wishlistmember_instance()->level_for_approval($levels[0], $uid);

            $settings      = isset($this->settings['level'][ $levels[0] ]['add']) ? $this->settings['level'][ $levels[0] ]['add'] : [];
            $apply_course  = isset($settings['apply_course']) ? $settings['apply_course'] : [];
            $remove_course = isset($settings['remove_course']) ? $settings['remove_course'] : [];

            $mep = Sensei_Course_Enrolment_Manager::instance()->get_manual_enrolment_provider();
            if (! $level_unconfirmed && ! $level_for_approval) {
                foreach ($apply_course as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
                foreach ($remove_course as $course_id) {
                    Sensei_Utils::sensei_remove_user_from_course($course_id, $uid);
                }
            }
        }

        // FOR NEW USERS.
        public function NewUserTagsHook($uid = null, $udata = null)
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

            $settings      = isset($this->settings['level'][ $udata['wpm_id'] ]['add']) ? $this->settings['level'][ $udata['wpm_id'] ]['add'] : [];
            $apply_course  = isset($settings['apply_course']) ? $settings['apply_course'] : [];
            $remove_course = isset($settings['remove_course']) ? $settings['remove_course'] : [];

            $mep = Sensei_Course_Enrolment_Manager::instance()->get_manual_enrolment_provider();

            if (! $level_unconfirmed && ! $level_for_approval) {
                foreach ($apply_course as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
                foreach ($remove_course as $course_id) {
                    Sensei_Utils::sensei_remove_user_from_course($course_id, $uid);
                }
            }
        }

        // WHEN ADDED TO LEVELS.
        public function AddUserTagsHook($uid, $addlevels = '')
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            $level_added = reset($addlevels); // Get the first element.
            // If from registration then don't don't process if the $addlevels is.
            // The same level the user registered to. This is already processed by NewUserTagsQueue func.
            if (isset(wlm_post_data()['action']) && 'wpm_register' === wlm_post_data()['action']) {
                if (wlm_post_data()['wpm_id'] == $level_added) {
                    return;
                }
            }

            $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($level_added, $uid);
            $level_for_approval = wishlistmember_instance()->level_for_approval($level_added, $uid);

            $settings      = isset($this->settings['level'][ $level_added ]['add']) ? $this->settings['level'][ $level_added ]['add'] : [];
            $apply_course  = isset($settings['apply_course']) ? $settings['apply_course'] : [];
            $remove_course = isset($settings['remove_course']) ? $settings['remove_course'] : [];

            $mep = Sensei_Course_Enrolment_Manager::instance()->get_manual_enrolment_provider();

            if (! $level_unconfirmed && ! $level_for_approval) {
                foreach ($apply_course as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
                foreach ($remove_course as $course_id) {
                    Sensei_Utils::sensei_remove_user_from_course($course_id, $uid);
                }
            } elseif (isset(wlm_post_data()['SendMail'])) {
                // This elseif condition fixes the issue where members who are added via.
                // WLM API aren't being processed.
                foreach ($apply_course as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
                foreach ($remove_course as $course_id) {
                    Sensei_Utils::sensei_remove_user_from_course($course_id, $uid);
                }
            }
        }

        // WHEN REREGISTERED FROM LEVELS.
        public function ReregUserTagsHook($uid, $levels = '')
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

            $mep = Sensei_Course_Enrolment_Manager::instance()->get_manual_enrolment_provider();

            foreach ($levels as $level) {
                $settings      = isset($this->settings['level'][ $level ]['rereg']) ? $this->settings['level'][ $level ]['rereg'] : [];
                $apply_course  = isset($settings['apply_course']) ? $settings['apply_course'] : [];
                $remove_course = isset($settings['remove_course']) ? $settings['remove_course'] : [];

                foreach ($apply_course as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
                foreach ($remove_course as $course_id) {
                    Sensei_Utils::sensei_remove_user_from_course($course_id, $uid);
                }
            }
        }

        // WHEN REMOVED FROM LEVELS.
        public function RemoveUserTagsHook($uid, $removedlevels = '')
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

            $mep = Sensei_Course_Enrolment_Manager::instance()->get_manual_enrolment_provider();

            foreach ($levels as $level) {
                $settings      = isset($this->settings['level'][ $level ]['remove']) ? $this->settings['level'][ $level ]['remove'] : [];
                $apply_course  = isset($settings['apply_course']) ? $settings['apply_course'] : [];
                $remove_course = isset($settings['remove_course']) ? $settings['remove_course'] : [];

                foreach ($apply_course as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
                foreach ($remove_course as $course_id) {
                    Sensei_Utils::sensei_remove_user_from_course($course_id, $uid);
                }
            }
        }

        // WHEN CANCELLED FROM LEVELS.
        public function CancelUserTagsHook($uid, $cancellevels = '')
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

            $mep = Sensei_Course_Enrolment_Manager::instance()->get_manual_enrolment_provider();

            foreach ($levels as $level) {
                $settings      = isset($this->settings['level'][ $level ]['cancel']) ? $this->settings['level'][ $level ]['cancel'] : [];
                $apply_course  = isset($settings['apply_course']) ? $settings['apply_course'] : [];
                $remove_course = isset($settings['remove_course']) ? $settings['remove_course'] : [];

                foreach ($apply_course as $course_id) {
                    $mep->enrol_learner($uid, $course_id);
                }
                foreach ($remove_course as $course_id) {
                    Sensei_Utils::sensei_remove_user_from_course($course_id, $uid);
                }
            }
        }
    }
    new WLM_OTHER_INTEGRATION_SenseiLMS();
}
