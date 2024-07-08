<?php

/**
 * Handler for BuddyBoss integration
 * Author: Fel Jun Palawan <feljun@wishlistproducts.com>
 */

if (! class_exists('WLM_OTHER_INTEGRATION_BUDDYBOSS')) {
    class WLM_OTHER_INTEGRATION_BUDDYBOSS
    {
        private $settings     = [];
        public $plugin_active = false;

        public function __construct()
        {

            $data                            = wishlistmember_instance()->get_option('buddyboss_settings');
            $this->settings                  = is_array($data) ? $data : [];
            $this->settings['group_default'] = wishlistmember_instance()->get_option('wlm_bb_group_default');
            $this->settings['ptype_default'] = wishlistmember_instance()->get_option('wlm_bb_ptype_default');

            // Check if BuddyBoss is active.
            $active_plugins = wlm_get_active_plugins();
            if (in_array('BuddyBoss Platform', $active_plugins) || isset($active_plugins['buddyboss-platform/bp-loader.php']) || is_plugin_active('buddyboss-platform/bp-loader.php')) {
                $this->plugin_active = true;
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
                add_action('wishlistmember_expire_user_levels', [$this, 'ExpiredUserTagsHook'], 99, 2);

                add_action('groups_leave_group', [$this, 'GroupRemovedHook'], 99, 2);
                add_action('groups_accept_invite', [$this, 'GroupAddedHook'], 99, 3);

                add_action('bp_set_member_type', [$this, 'TypeAddedHook'], 99, 3);
                add_action('bp_remove_member_type', [$this, 'TypeRemovedHook'], 99, 2);

                if (isset($this->settings['group_default']) && $this->settings['group_default']) {
                    add_action('groups_group_after_save', [$this, 'SaveGroupDefaultActions'], 1, 1);
                }

                if (isset($this->settings['ptype_default']) && $this->settings['ptype_default'] && bp_member_type_enable_disable()) {
                    add_action('save_post_bp-member-type', [$this, 'SavePTypeDefaultActions'], 10, 3);
                }
            }
        }

        public function SaveGroupDefaultActions($obj_group)
        {
            global  $wpdb;
            // The hook groups_group_after_save has no way of determining if we are saving new group or updating an existing one.
            // So we check the last insert id.
            if ($obj_group->id != $wpdb->insert_id) {
                return;
            }
            $post_id = $obj_group->id;

            $data = wishlistmember_instance()->get_option('buddyboss_settings');
            if (! isset($data['group']['default'])) {
                return;
            }
            $data['group'][ $post_id ] = $data['group']['default'];
            wishlistmember_instance()->save_option('buddyboss_settings', $data);
        }

        public function SavePTypeDefaultActions($post_id, $post, $update)
        {
            // If revision or update, disregard.
            if ($update || wp_is_post_revision($post_id)) {
                return;
            }

            $data = wishlistmember_instance()->get_option('buddyboss_settings');
            if (! isset($data['ptype']['default'])) {
                return;
            }
            // We use post id here, we will convert it later to post name.
            // We convert it for  backward compatibility since.
            // We use post name when we first release the integration.
            $data['type'][ $post_id ] = $data['ptype']['default'];
            wishlistmember_instance()->save_option('buddyboss_settings', $data);
        }

        public function TypeAddedHook($user_id, $member_type, $append)
        {
            $action   = 'add';
            $settings = isset($this->settings['type'][ $member_type ][ $action ]) ? $this->settings['type'][ $member_type ][ $action ] : [];
            $this->DoCourseHook($user_id, $member_type, $action, $settings, true);
        }

        public function TypeRemovedHook($user_id, $member_type)
        {
            $action   = 'remove';
            $settings = isset($this->settings['type'][ $member_type ][ $action ]) ? $this->settings['type'][ $member_type ][ $action ] : [];
            $this->DoCourseHook($user_id, $member_type, $action, $settings, true);
        }

        public function GroupAddedHook($user_id, $group_id, $inviter_id)
        {
            $action   = 'add';
            $settings = isset($this->settings['group'][ $group_id ][ $action ]) ? $this->settings['group'][ $group_id ][ $action ] : [];
            $this->DoCourseHook($user_id, $group_id, $action, $settings, false);
        }

        public function GroupRemovedHook($group_id, $user_id)
        {
            $action   = 'remove';
            $settings = isset($this->settings['group'][ $group_id ][ $action ]) ? $this->settings['group'][ $group_id ][ $action ] : [];
            $this->DoCourseHook($user_id, $group_id, $action, $settings, false);
        }

        private function DoCourseHook($wpuser, $hook_id, $action, $settings, $is_type = true)
        {

            $added_levels     = isset($settings['add_level']) ? $settings['add_level'] : [];
            $cancelled_levels = isset($settings['cancel_level']) ? $settings['cancel_level'] : [];
            $removed_levels   = isset($settings['remove_level']) ? $settings['remove_level'] : [];

            $current_user_mlevels = wishlistmember_instance()->get_membership_levels($wpuser);
            $wpm_levels           = wishlistmember_instance()->get_option('wpm_levels');

            $prefix = $is_type ? 'T' : 'G';

            $action = strtoupper(substr($action, 0, 1));
            $txnid  = "BBOSS-{$action}-{$prefix}{$hook_id}-";
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

            $is_member_type_enabled      = bp_member_type_enable_disable();
            $is_groups_component_enabled = bp_is_active('groups');
            if (! $is_groups_component_enabled && ! $is_member_type_enabled) {
                return;
            }

            $levels             = is_array($levels) ? $levels : (array) $levels;
            $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($levels[0], $uid);
            $level_for_approval = wishlistmember_instance()->level_for_approval($levels[0], $uid);

            $settings     = isset($this->settings['level'][ $levels[0] ]['add']) ? $this->settings['level'][ $levels[0] ]['add'] : [];
            $apply_group  = isset($settings['apply_group']) ? $settings['apply_group'] : [];
            $remove_group = isset($settings['remove_group']) ? $settings['remove_group'] : [];
            $apply_type   = isset($settings['apply_type']) ? $settings['apply_type'] : [];
            $remove_type  = isset($settings['remove_type']) ? $settings['remove_type'] : [];

            if (! $level_unconfirmed && ! $level_for_approval) {
                if ($is_groups_component_enabled) {
                    foreach ($apply_group as $group_id) {
                        if ($group_id) {
                            groups_join_group($group_id, $uid);
                        }
                    }
                    foreach ($remove_group as $group_id) {
                        if ($group_id) {
                            groups_leave_group($group_id, $uid);
                        }
                    }
                }

                if ($is_member_type_enabled) {
                    foreach ($apply_type as $type_id) {
                        bp_set_member_type($uid, $type_id, true);
                    }
                    foreach ($remove_type as $type_id) {
                        bp_remove_member_type($uid, $type_id);
                    }
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

            $is_member_type_enabled      = bp_member_type_enable_disable();
            $is_groups_component_enabled = bp_is_active('groups');
            if (! $is_groups_component_enabled && ! $is_member_type_enabled) {
                return;
            }

            $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($udata['wpm_id'], $uid);
            $level_for_approval = wishlistmember_instance()->level_for_approval($udata['wpm_id'], $uid);

            $settings     = isset($this->settings['level'][ $udata['wpm_id'] ]['add']) ? $this->settings['level'][ $udata['wpm_id'] ]['add'] : [];
            $apply_group  = isset($settings['apply_group']) ? $settings['apply_group'] : [];
            $remove_group = isset($settings['remove_group']) ? $settings['remove_group'] : [];
            $apply_type   = isset($settings['apply_type']) ? $settings['apply_type'] : [];
            $remove_type  = isset($settings['remove_type']) ? $settings['remove_type'] : [];

            if (! $level_unconfirmed && ! $level_for_approval) {
                if ($is_groups_component_enabled) {
                    foreach ($apply_group as $group_id) {
                        if ($group_id) {
                            groups_join_group($group_id, $uid);
                        }
                    }
                    foreach ($remove_group as $group_id) {
                        if ($group_id) {
                            groups_leave_group($group_id, $uid);
                        }
                    }
                }

                if ($is_member_type_enabled) {
                    foreach ($apply_type as $type_id) {
                        bp_set_member_type($uid, $type_id, true);
                    }
                    foreach ($remove_type as $type_id) {
                        bp_remove_member_type($uid, $type_id);
                    }
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

            $is_member_type_enabled      = bp_member_type_enable_disable();
            $is_groups_component_enabled = bp_is_active('groups');
            if (! $is_groups_component_enabled && ! $is_member_type_enabled) {
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

            foreach ($addlevels as $key => $lvl) {
                $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($lvl, $uid);
                $level_for_approval = wishlistmember_instance()->level_for_approval($lvl, $uid);

                $settings     = isset($this->settings['level'][ $lvl ]['add']) ? $this->settings['level'][ $lvl ]['add'] : [];
                $apply_group  = isset($settings['apply_group']) ? $settings['apply_group'] : [];
                $remove_group = isset($settings['remove_group']) ? $settings['remove_group'] : [];
                $apply_type   = isset($settings['apply_type']) ? $settings['apply_type'] : [];
                $remove_type  = isset($settings['remove_type']) ? $settings['remove_type'] : [];

                if (! $level_unconfirmed && ! $level_for_approval) {
                    if ($is_groups_component_enabled) {
                        foreach ($apply_group as $group_id) {
                            if ($group_id) {
                                groups_join_group($group_id, $uid);
                            }
                        }
                        foreach ($remove_group as $group_id) {
                            if ($group_id) {
                                groups_leave_group($group_id, $uid);
                            }
                        }
                    }

                    if ($is_member_type_enabled) {
                        foreach ($remove_type as $type_id) {
                            // Skip the function If user has no profile types.
                            if (bp_get_member_type($uid)) {
                                bp_remove_member_type($uid, $type_id);
                            }
                        }

                        foreach ($apply_type as $type_id) {
                            bp_set_member_type($uid, $type_id, true);
                        }
                    }
                } elseif (isset(wlm_post_data()['SendMail'])) {
                    // This elseif condition fixes the issue where members who are added via.
                    // WLM API aren't being processed.
                    if ($is_groups_component_enabled) {
                        foreach ($apply_group as $group_id) {
                            if ($group_id) {
                                groups_join_group($group_id, $uid);
                            }
                        }
                        foreach ($remove_group as $group_id) {
                            if ($group_id) {
                                groups_leave_group($group_id, $uid);
                            }
                        }
                    }

                    if ($is_member_type_enabled) {
                        foreach ($apply_type as $type_id) {
                            bp_set_member_type($uid, $type_id, true);
                        }
                        foreach ($remove_type as $type_id) {
                            bp_remove_member_type($uid, $type_id);
                        }
                    }
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

            $is_member_type_enabled      = bp_member_type_enable_disable();
            $is_groups_component_enabled = bp_is_active('groups');
            if (! $is_groups_component_enabled && ! $is_member_type_enabled) {
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
                $settings     = isset($this->settings['level'][ $level ]['rereg']) ? $this->settings['level'][ $level ]['rereg'] : [];
                $apply_group  = isset($settings['apply_group']) ? $settings['apply_group'] : [];
                $remove_group = isset($settings['remove_group']) ? $settings['remove_group'] : [];
                $apply_type   = isset($settings['apply_type']) ? $settings['apply_type'] : [];
                $remove_type  = isset($settings['remove_type']) ? $settings['remove_type'] : [];

                if ($is_groups_component_enabled) {
                    foreach ($apply_group as $group_id) {
                        if ($group_id) {
                            groups_join_group($group_id, $uid);
                        }
                    }
                    foreach ($remove_group as $group_id) {
                        if ($group_id) {
                            groups_leave_group($group_id, $uid);
                        }
                    }
                }

                if ($is_member_type_enabled) {
                    foreach ($apply_type as $type_id) {
                        bp_set_member_type($uid, $type_id, true);
                    }
                    foreach ($remove_type as $type_id) {
                        bp_remove_member_type($uid, $type_id);
                    }
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

            $is_member_type_enabled      = bp_member_type_enable_disable();
            $is_groups_component_enabled = bp_is_active('groups');
            if (! $is_groups_component_enabled && ! $is_member_type_enabled) {
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
                $settings     = isset($this->settings['level'][ $level ]['remove']) ? $this->settings['level'][ $level ]['remove'] : [];
                $apply_group  = isset($settings['apply_group']) ? $settings['apply_group'] : [];
                $remove_group = isset($settings['remove_group']) ? $settings['remove_group'] : [];
                $apply_type   = isset($settings['apply_type']) ? $settings['apply_type'] : [];
                $remove_type  = isset($settings['remove_type']) ? $settings['remove_type'] : [];

                if ($is_groups_component_enabled) {
                    foreach ($apply_group as $group_id) {
                        if ($group_id) {
                            groups_join_group($group_id, $uid);
                        }
                    }
                    foreach ($remove_group as $group_id) {
                        if ($group_id) {
                            groups_leave_group($group_id, $uid);
                        }
                    }
                }

                if ($is_member_type_enabled) {
                    foreach ($apply_type as $type_id) {
                        bp_set_member_type($uid, $type_id, true);
                    }
                    foreach ($remove_type as $type_id) {
                        // Skip the function If user has no profile types.
                        if (bp_get_member_type($uid)) {
                            bp_remove_member_type($uid, $type_id);
                        }
                    }
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

            $is_member_type_enabled      = bp_member_type_enable_disable();
            $is_groups_component_enabled = bp_is_active('groups');
            if (! $is_groups_component_enabled && ! $is_member_type_enabled) {
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
                $settings     = isset($this->settings['level'][ $level ]['cancel']) ? $this->settings['level'][ $level ]['cancel'] : [];
                $apply_group  = isset($settings['apply_group']) ? $settings['apply_group'] : [];
                $remove_group = isset($settings['remove_group']) ? $settings['remove_group'] : [];
                $apply_type   = isset($settings['apply_type']) ? $settings['apply_type'] : [];
                $remove_type  = isset($settings['remove_type']) ? $settings['remove_type'] : [];

                if ($is_groups_component_enabled) {
                    foreach ($apply_group as $group_id) {
                        if ($group_id) {
                            groups_join_group($group_id, $uid);
                        }
                    }
                    foreach ($remove_group as $group_id) {
                        if ($group_id) {
                            groups_leave_group($group_id, $uid);
                        }
                    }
                }

                if ($is_member_type_enabled) {
                    foreach ($apply_type as $type_id) {
                        bp_set_member_type($uid, $type_id, true);
                    }
                    foreach ($remove_type as $type_id) {
                        bp_remove_member_type($uid, $type_id);
                    }
                }
            }
        }

        // WHEN LEVEL EXPIRES.
        public function ExpiredUserTagsHook($uid, $expiredlevels = '')
        {
            $user = get_userdata($uid);
            if (! $user) {
                return;
            }
            if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
                return;
            }

            $is_member_type_enabled      = bp_member_type_enable_disable();
            $is_groups_component_enabled = bp_is_active('groups');
            if (! $is_groups_component_enabled && ! $is_member_type_enabled) {
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
                $settings     = isset($this->settings['level'][ $level ]['expired']) ? $this->settings['level'][ $level ]['expired'] : [];
                $apply_group  = isset($settings['apply_group']) ? $settings['apply_group'] : [];
                $remove_group = isset($settings['remove_group']) ? $settings['remove_group'] : [];
                $apply_type   = isset($settings['apply_type']) ? $settings['apply_type'] : [];
                $remove_type  = isset($settings['remove_type']) ? $settings['remove_type'] : [];

                if ($is_groups_component_enabled) {
                    foreach ($apply_group as $group_id) {
                        if ($group_id) {
                            groups_join_group($group_id, $uid);
                        }
                    }
                    foreach ($remove_group as $group_id) {
                        if ($group_id) {
                            groups_leave_group($group_id, $uid);
                        }
                    }
                }

                if ($is_member_type_enabled) {
                    foreach ($apply_type as $type_id) {
                        bp_set_member_type($uid, $type_id, true);
                    }
                    foreach ($remove_type as $type_id) {
                        bp_remove_member_type($uid, $type_id);
                    }
                }
            }
        }
    }
    new WLM_OTHER_INTEGRATION_BUDDYBOSS();
}
