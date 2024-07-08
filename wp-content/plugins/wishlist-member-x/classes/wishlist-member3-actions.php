<?php

if (! class_exists('WishListMember3_Actions')) {
    class WishListMember3_Actions extends WishListMember3_Core
    {
        public function process_admin_actions()
        {
            verify_wlm_nonces();
            if (! is_admin() || ! current_user_can('manage_options') || ! WLM_POST_NONCED) {
                return;
            }
            if (! isset(wlm_post_data()['WishListMemberAction']) || empty(wlm_post_data()['WishListMemberAction'])) {
                return;
            }
            switch (wlm_post_data()['WishListMemberAction']) {
                case 'RestoreSettingsFromFile':
                    $this->restore_settings_from_file();
                    unset(wlm_post_data()['WishListMemberAction']); // ALWAYS UNSET WishListMemberAction AFTER USE to prevent duplicate execution in old WLM.
                    break;
                default:
            }
        }

        public function process_admin_ajax_actions($action, $data)
        {
            verify_wlm_nonces();
            if (is_admin() && current_user_can('manage_options') && WLM_POST_NONCED) {
                switch ($action) {
                    case 'resend_email_confirmation_request':
                        return $this->resend_email_confirmation_request($data);
                    break;
                    case 'resend_incomplete_registration_email':
                        return $this->resend_incomplete_registration_email($data);
                    break;
                    case 'apply_email_template_to_selected_levels':
                        return $this->apply_email_template_to_selected_levels($data);
                    break;
                    case 'reset_level_sender_info_to_default':
                        return $this->reset_level_sender_info_to_default();
                    break;
                    case 'save_global_email_notifications':
                        return $this->save_global_email_notifications($data);
                    break;
                    case 'delete_rollback':
                        return $this->delete_rollback($data);
                    break;
                    case 'save':
                        $data = (array) $data;
                        if (count($data) < 0) {
                            return [
                                'success'  => false,
                                'msg'      => __('Empty request', 'wishlist-member'),
                                'msg_type' => 'danger',
                            ];
                        }
                        foreach ($data as $option => $value) {
                            if (is_scalar($value)) {
                                $value = trim(stripslashes($value));
                            }
                            $this->save_option($option, $value);
                        }
                        return [
                            'success'  => true,
                            'msg'      => __('Saved', 'wishlist-member'),
                            'msg_type' => 'success',
                            'data'     => $data,
                        ];
                        break;
                    case 'save_user_meta':
                        $data = (array) $data;
                        if (count($data) < 0) {
                            return [
                                'success'  => false,
                                'msg'      => __('Empty request', 'wishlist-member'),
                                'msg_type' => 'danger',
                            ];
                        }
                        return $this->save_user_meta($data);
                        break;
                    case 'remove_user_meta':
                        return $this->remove_user_meta($data);
                        break;
                    case 'get_password_notification':
                        return $this->get_password_notification($data);
                        break;
                    case 'get_system_page':
                        return $this->get_system_page($data);
                        break;
                    case 'create_system_page':
                        return $this->create_system_page($data);
                        break;
                    case 'reset_custom_css':
                        return $this->reset_custom_css($data);
                    case 'save_membership_level':
                        return $this->save_membership_level($data);
                        break;
                    case 'save_payperpost':
                        return $this->save_payperpost($data);
                        break;
                    case 'toggle_payperpost':
                        return $this->toggle_payperpost($data);
                        break;
                    case 'save_payperpost_settings':
                        return $this->save_payperpost_settings($data);
                        break;
                    case 'save_custom_registration_form':
                        return $this->save_custom_registration_form($data);
                        break;
                    case 'clone_custom_registration_form':
                        return $this->clone_custom_registration_form($data);
                        break;
                    case 'delete_membership_level':
                        return $this->delete_membership_level($data);
                        break;
                    case 'delete_custom_registration_form':
                        return $this->delete_custom_registration_form($data);
                        break;
                    case 'schedule_user_level':
                        return $this->schedule_user_level($data);
                        break;
                    case 'payperpost_search':
                        return $this->payperpost_search($data);
                        break;
                    case 'add_remove_payperpost':
                        return $this->add_remove_payperpost($data);
                        break;
                    case 'delete_user':
                        return $this->delete_user_action($data);
                        break;
                    case 'update_user':
                        return $this->update_user($data);
                        break;
                    case 'add_user':
                        return $this->add_user($data);
                        break;
                    case 'generate_password':
                        return $this->generate_password($data);
                        break;
                    case 'resend_reset_link':
                        return $this->resend_reset_link($data);
                        break;
                    case 'logout_everywhere':
                        return $this->logout_everywhere($data);
                        break;
                    case 'add_remove_blacklist':
                        return $this->add_remove_blacklist($data);
                        break;
                    case 'reset_limit_counter':
                        return $this->reset_limit_counter($data);
                        break;
                    case 'remove_savedsearch':
                        return $this->remove_savedsearch($data);
                        break;
                    case 'save_sequential':
                        return $this->save_sequential($data);
                        break;
                    case 'save_level_actions':
                        return $this->save_level_actions($data);
                        break;
                    case 'delete_level_action':
                        return $this->delete_level_action($data);
                        break;
                    case 'get_level_actions':
                        return $this->get_level_actions($data);
                        break;
                    case 'get_level_action_details':
                        return $this->get_level_action_details($data);
                        break;
                    case 'get_level_memberids':
                        return $this->get_level_memberids($data);
                        break;
                    case 'massmove_members':
                        return $this->massmove_members($data);
                        break;
                    case 'create_backup':
                        return $this->create_backup($data);
                        break;
                    case 'restore_backup':
                        return $this->restore_backup($data);
                        break;
                    case 'delete_backup':
                        return $this->delete_backup($data);
                        break;
                    case 'settings_reset':
                        return $this->settings_reset($data);
                        break;
                    case 'preview_broadcast':
                        return $this->preview_broadcast($data);
                        break;
                    case 'create_broadcast':
                        return $this->create_broadcast($data);
                        break;
                    case 'queue_broadcast':
                        return $this->queue_broadcast($data);
                        break;
                    case 'changestat_broadcast':
                        return $this->changestat_broadcast($data);
                        break;
                    case 'delete_broadcast':
                        return $this->delete_broadcast_action($data);
                        break;
                    case 'get_email_broadcast':
                        return $this->fetch_email_broadcast($data);
                        break;
                    case 'get_emails_in_queue':
                        return $this->get_emails_in_queue($data);
                        break;
                    case 'send_emails_in_queue':
                        return $this->send_emails_in_queue($data);
                        break;
                    case 'get_broadcast_status':
                        return $this->get_broadcast_status($data);
                        break;
                    case 'remove_failed_broadcast_emails':
                        return $this->remove_failed_broadcast_emails($data);
                        break;
                    case 'requeue_failed_broadcast_emails':
                        return $this->requeue_failed_broadcast_emails($data);
                        break;
                    case 'get_backup_queue_count':
                        return $this->get_backup_queue_count($data);
                        break;
                    case 'cancel_backup':
                        return $this->cancel_backup($data);
                        break;
                    case 'get_import_queue_count':
                        return $this->get_import_queue_count($data);
                        break;
                    case 'pause_start_import':
                        return $this->pause_start_import($data);
                        break;
                    case 'cancel_member_import':
                        return $this->cancel_member_import($data);
                        break;
                    case 'save_other_integration':
                        return $this->save_other_integration($data);
                        break;
                    case 'save_autoresponder':
                        return $this->save_autoresponder($data);
                        break;
                    case 'save_payment_provider':
                        return $this->save_payment_provider($data);
                        break;
                    case 'update_content_protection':
                        return $this->update_content_protection($data);
                        break;
                    case 'get_content_protection':
                        return $this->get_content_protection($data);
                        break;
                    case 'ppp_user_search':
                        return $this->ppp_user_search($data);
                        break;
                    case 'folder_protection_autoconfig':
                        return $this->folder_protection_autoconfig($data);
                        break;
                    case 'enable_folder_protection':
                        return $this->enable_folder_protection($data);
                        break;
                    case 'get_folders_list':
                        return $this->get_folders_list($data);
                        break;
                    case 'get_folders_files':
                        return $this->get_folders_files($data);
                        break;
                    case 'enable_custom_post_types':
                        return $this->enable_custom_post_types($data);
                        break;
                    case 'process_wizard':
                        return $this->process_wizard($data);
                        break;
                    case 'activate_license':
                        return $this->activate_license($data);
                        break;
                    case 'deactivate_license':
                        return $this->deactivate_license($data);
                        break;
                    case 'toggle_user_table':
                        return $this->toggle_user_table($data);
                    case 'toggle_file_protection':
                        return $this->toggle_file_protection($data);
                    default:
                        return apply_filters(
                            'wishlistmember_admin_action_' . $action,
                            [
                                'success'  => false,
                                'msg'      => __('Invalid request', 'wishlist-member'),
                                'msg_type' => 'danger',
                            ],
                            $data
                        );
                }
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('Unauthorized request', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
        }

        public function settings_reset($data)
        {
            $data['resetSettingConfirm'] = 1;
            $this->plugin_dir3            = $this->legacy_wlm_dir;
            $this->reset_settings($data);
            if (isset($this->err)) {
                return [
                    'success'  => false,
                    'msg'      => $this->err,
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            } else {
                return [
                    'success'  => true,
                    'msg'      => $this->msg,
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            }
        }

        public function delete_backup($data)
        {
            $details = $this->backup_details($data['name']);
            $this->backup_delete($data['name']);
            return [
                'success'  => true,
                'msg'      => $this->msg,
                'msg_type' => 'success',
                'data'     => $data,
                'details'  => $details,
            ];
        }

        public function restore_backup($data)
        {
            $ret = $this->backup_restore($data['name'], false);
            if (false === $ret) {
                return [
                    'success'  => false,
                    'msg'      => $this->err,
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            } else {
                return [
                    'success'  => true,
                    'msg'      => $this->msg,
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            }
        }

        public function create_backup($data)
        {
            $_POST                                  = $data;
            wlm_post_data()['WishListMemberAction'] = 'BackupSettings'; // Need by the function.
            $ret                                    = $this->backup_queue();

            if (false === $ret) {
                return [
                    'success'  => false,
                    'msg'      => $this->err,
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            } else {
                ob_start();
                    include $this->plugin_dir3 . '/ui/admin_screens/administration/backup/backup_files.php';
                $backup_files = ob_get_clean();
                return [
                    'success'  => true,
                    'msg'      => $this->msg,
                    'msg_type' => 'success',
                    'data'     => $data,
                    'files'    => $backup_files,
                ];
            }
        }

        public function massmove_members($data)
        {
            $wpm_levels = $this->get_option('wpm_levels');
            if (! in_array($data['operation'], ['move', 'add'])) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid action', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            if (! array_key_exists($data['to_levelid'], $wpm_levels)) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid to membership level', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            $ids = isset($data['ids']) && is_array($data['ids']) ? $data['ids'] : [];
            if ('add' === $data['operation']) {
                foreach ($ids as $id) {
                    $levels   = $this->get_membership_levels($id);
                    $levels[] = $data['to_levelid'];
                    $this->set_membership_levels($id, $levels, ['process_autoresponders' => false]);
                }
            } else {
                if (! array_key_exists($data['from_levelid'], $wpm_levels)) {
                    return [
                        'success'  => false,
                        'msg'      => __('Invalid from membership level', 'wishlist-member'),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
                foreach ($ids as $id) {
                    $levels   = $this->get_membership_levels($id);
                    $levels   = array_diff($levels, [$data['from_levelid']]);
                    $levels[] = $data['to_levelid'];
                    $this->set_membership_levels($id, $levels, ['process_autoresponders' => false]);
                }
            }
            $this->schedule_sync_membership(true);
            return [
                'success'  => true,
                'msg'      => __('Members have been processed', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function get_level_memberids($data)
        {
            $lvlid = $data['lvlid'];
            if ('NONMEMBERS' === $lvlid) {
                $ids = $this->get_nonmembers_ids();
            } else {
                $ids = $this->member_ids($lvlid);
            }
            return [
                'ids'  => $ids,
                'data' => $data,
            ];
        }

        public function save_sequential($data)
        {
            $enable_sequential = $data['enable_sequential'];
            unset($data['enable_sequential']);
            foreach (['upgradeMethod', 'upgradeTo', 'upgradeSchedule', 'upgradeAfter', 'upgradeAfterPeriod', 'upgradeOnDate', 'upgradeEmailNotification'] as $key) {
                wlm_post_data()[ $key ] = [$data['level_id'] => $data[ $key ]];
            }
            $this->save_sequential_upgrade_configuration();
            if (isset($this->err)) {
                return [
                    'success'  => false,
                    'msg'      => $this->err,
                    'msg_type' => 'danger',
                    'data'     => wlm_post_data(true),
                ];
            } else {
                return [
                    'success'  => true,
                    'msg'      => $this->msg,
                    'msg_type' => 'success',
                    'data'     => wlm_post_data(true),
                ];
            }
        }

        public function save_level_actions($data)
        {
            $lvlid           = isset($data['levelid']) ? $data['levelid'] : false;
            $level_action_id = isset($data['level_action_id']) ? (int) $data['level_action_id'] : false;
            $level_action_id = $level_action_id ? $level_action_id : false;
            if (! $lvlid) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Level', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            unset($data['levelid']);
            unset($data['level_action_id']);
            if ($level_action_id) {
                $this->level_options->update_option_by_id($level_action_id, $data);
                return [
                    'success'  => true,
                    'msg'      => 'Level action updated',
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } else {
                $this->level_options->save_option($lvlid, 'scheduled_action', $data);
                return [
                    'success'  => true,
                    'msg'      => 'Level action added',
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            }
        }

        public function get_level_actions($data)
        {
            $wpm_levels = $this->get_option('wpm_levels');
            $lvlid      = isset($data['levelid']) ? $data['levelid'] : false;
            if (! $lvlid) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Level', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            $res         = $this->level_options->get_options($lvlid, 'scheduled_action');
            $events      = [
                'added'     => __('When <strong>Added</strong> to this Level', 'wishlist-member'),
                'removed'   => __('When <strong>Removed</strong> from this Level', 'wishlist-member'),
                'cancelled' => __('When <strong>Cancelled</strong> from this Level', 'wishlist-member'),
            ];
            $methods     = [
                'add'               => __('<em>Add</em> to', 'wishlist-member'),
                'move'              => __('<em>Move</em> to', 'wishlist-member'),
                'cancel'            => __('<em>Cancel</em> from', 'wishlist-member'),
                'remove'            => __('<em>Remove</em> from', 'wishlist-member'),
                'cancel-from-same-level' => __('<em>Cancel</em> from same Level', 'wishlist-member'),
                'add-ppp'           => __('<em>Add to Pay Per Post</em>', 'wishlist-member'),
                'remove-ppp'        => __('<em>Remove from Pay Per Post</em>', 'wishlist-member'),
                'create-ppp'        => __('<em>Create Pay Per Post</em>', 'wishlist-member'),
            ];
            $event_icons = [
                'added'     => 'add_circle_outline',
                'removed'   => 'remove_circle_outline',
                'cancelled' => 'close',
            ];
            $periods_p   = [
                'days'   => __('Days', 'wishlist-member'),
                'weeks'  => __('Weeks', 'wishlist-member'),
                'months' => __('Months', 'wishlist-member'),
                'years'  => __('Years', 'wishlist-member'),
            ];
            $periods_s   = [
                'days'   => __('Day', 'wishlist-member'),
                'weeks'  => __('Week', 'wishlist-member'),
                'months' => __('Month', 'wishlist-member'),
                'years'  => __('Year', 'wishlist-member'),
            ];
            $actions     = [];

            // Get pay per post types.
            $args          = ['_builtin' => false];
            $ptypes        = get_post_types($args, 'objects');
            $enabled_types = (array) $this->get_option('protected_custom_post_types');
            $post_types    = [
                'post' => 'Posts',
                'page' => 'Pages',
            ];
            foreach ($ptypes as $key => $value) {
                if (in_array($value->name, $enabled_types)) {
                    $post_types[ $value->name ] = $value->label;
                }
            }

            foreach ($res as $key => $value) {
                $value->option_value = wlm_maybe_unserialize($value->option_value);
                $levels              = $value->option_value['action_levels'];
                foreach ($levels as $key => $lvl) {
                    if (isset($wpm_levels[ $lvl ]['name'])) {
                        $levels[ $key ] = $wpm_levels[ $lvl ]['name'];
                    } else {
                        unset($levels[ $key ]);
                    }
                }
                if ('ondate' === $value->option_value['sched_toggle']) {
                    if ('' != $value->option_value['sched_ondate']) {
                        $value->option_value['schedule'] = 'on ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($value->option_value['sched_ondate']));
                        if (strtotime($value->option_value['sched_ondate']) < strtotime('now')) {
                            $value->option_value['schedule'] = "<del>{$value->option_value["schedule"]}</del>";
                        }
                    } else {
                        $value->option_value['schedule'] = 'Immediately';
                    }
                } else {
                    if ($value->option_value['sched_after_term']) {
                        $value->option_value['schedule']  = 'after ' . $value->option_value['sched_after_term'] . ' ';
                        $value->option_value['schedule'] .= $value->option_value['sched_after_term'] > 1 ? $periods_p[ $value->option_value['sched_after_period'] ] : $periods_s[ $value->option_value['sched_after_period'] ];
                    } else {
                        $value->option_value['schedule'] = 'Immediately';
                    }
                }
                $value->option_value['schedule'] = "<span class='align-middle'>{$value->option_value["schedule"]}</span>";

                if (in_array($value->option_value['level_action_method'], ['add', 'cancel', 'move', 'cancel-from-same-level'])) {
                    $level_email = isset($value->option_value['level_email']) ? wlm_trim($value->option_value['level_email']) : 'dontsend';
                    $level_email = in_array($level_email, ['send', 'sendlevel', 'dontsend']) ? $level_email : 'dontsend';

                    if ('send' === $level_email) {
                        $value->option_value['schedule'] .= ' <i class="wlm-icons md-18 text-muted" title="Send Email Notification">email</i>';
                    } elseif ('dontsend' === $level_email) {
                        $value->option_value['schedule'] .= '';
                    } else {
                        $value->option_value['schedule'] .= ' <i class="wlm-icons md-18 text-muted" title="Use Level Notification Settings">needs_confirm</i>';
                    }
                }

                if ('add-ppp' === $value->option_value['level_action_method'] || 'create-ppp' === $value->option_value['level_action_method'] || 'remove-ppp' == $value->option_value['level_action_method']) {
                    $levels   = [];
                    $p_title  = '_Invalid Post_';
                    $p_type   = '';
                    $the_post = get_post($value->option_value['ppp_content']);
                    if ($the_post) {
                        $p_title = " \"{$the_post->post_title}\"";
                        $p_type  = $the_post->post_type;
                    }
                    $p_type = isset($post_types[ $p_type ]) ? "({$post_types[$p_type]})" : '';
                    if ('create-ppp' == $value->option_value['level_action_method']) {
                        $p_title = " \"{$value->option_value["ppp_title"]}\"" . ' copied from' . $p_title;
                    }
                    $levels[] = "{$p_type} " . $p_title;
                }

                $action_event       = isset($events[ $value->option_value['level_action_event'] ]) ? $events[ $value->option_value['level_action_event'] ] : ' - ';
                $action_method      = isset($methods[ $value->option_value['level_action_method'] ]) ? $methods[ $value->option_value['level_action_method'] ] : ' - ';
                $action_levels      = implode(', ', (array) $levels);
                $inheritparent_icon = '';
                if ('add' === $value->option_value['level_action_method']) {
                    if ('1' == $value->option_value['inheritparent']) {
                        $inheritparent_icon = '<i class="wlm-icons md-18 text-muted" title="Inherit this level status">person</i>';
                    }
                }
                $value->option_value['action_text'] = '<i class="wlm-icons md-18">' . $event_icons[ $value->option_value['level_action_event'] ] . '</i>&nbsp;&nbsp;<span class="align-middle">' . $action_event . ' then ' . $action_method . ' ' . $action_levels . '</span> ' . $inheritparent_icon;
                $actions[]                          = $value;
            }
            return [
                'success'  => true,
                'msg'      => 'Level actions',
                'msg_type' => 'success',
                'actions'  => $actions,
            ];
        }

        public function get_level_action_details($data)
        {
            $actionid = isset($data['actionid']) ? $data['actionid'] : false;
            if (! $actionid) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Level Action', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            $res = $this->level_options->get_option_by_id($actionid);
            if (! $res) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Level Action', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            $res->option_value = wlm_maybe_unserialize($res->option_value);
            if (in_array($res->option_value['level_action_method'], ['create-ppp', 'add-ppp', 'remove-ppp'])) {
                    $the_post = get_post($res->option_value['ppp_content']);
                if ($the_post) {
                    $res->option_value['ppp_post_title'] = $the_post->post_title;
                }
            }
            if (! isset($res->option_value['level_email']) || ! in_array($res->option_value['level_email'], ['send', 'sendlevel', 'dontsend'])) {
                $res->option_value['level_email'] = 'dontsend';
            }

            return [
                'success'  => true,
                'msg'      => 'Level actions',
                'msg_type' => 'success',
                'action'   => $res,
            ];
        }

        public function delete_level_action($data)
        {
            $actionid = isset($data['actionid']) ? $data['actionid'] : false;
            if (! $actionid) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Level Action', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            $res = $this->level_options->delete_option_by_id($actionid);
            if ($res) {
                return [
                    'success'  => true,
                    'msg'      => 'Level action deleted',
                    'msg_type' => 'success',
                    'action'   => $data,
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('Unable to delete level action', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
        }

        public function remove_savedsearch($data)
        {
            if (isset($data['name']) && ! empty($data['name'])) {
                $this->delete_option($data['name']);
                return [
                    'success'  => true,
                    'msg'      => __('Saved Search Removed', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Saved Search name', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
        }

        public function generate_password($data)
        {
            $passmin  = $this->get_option('min_passlength');
            $passmin += 0;
            if (! $passmin || $passmin < 14) {
                $passmin = 14;
            }

            // Always generate a strong password.
            $pass = wlm_generate_password($passmin, true);
            while (! wlm_check_password_strength($pass)) {
                $pass = wlm_generate_password($passmin, true);
            }

            if ($pass) {
                return [
                    'success'  => true,
                    'msg'      => __('Password generated', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $pass,
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('Unable to generate password', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
        }

        public function add_remove_blacklist($data)
        {
            $message = '';
            if (isset($data['blacklist_email'])) {
                $value = $this->get_option('blacklist_email');
                $value = wlm_trim($value);
                if (isset($data['add_blacklist'])) {
                    if (false === strpos($value, $data['blacklist_email'])) {
                        $value = $value . "\n" . $data['blacklist_email'];
                    }
                    $message = __('was added to blacklisted emails.', 'wishlist-member');
                } else {
                    $value   = str_replace($data['blacklist_email'], '', $value);
                    $message = __('was removed from blacklisted emails', 'wishlist-member');
                }
                $value = preg_replace('/^\h*\v+/m', '', $value); // Remove empty lines.
                $this->save_option('blacklist_email', $value);
                $value   = wlm_trim($data['blacklist_email']);
                $message = "<strong>{$value}</strong> " . $message;
            } elseif (isset($data['blacklist_ip'])) {
                $value = $this->get_option('blacklist_ip');
                $value = wlm_trim($value);
                if (isset($data['add_blacklist'])) {
                    if (false === strpos($value, $data['blacklist_ip'])) {
                        $value = $value . "\n" . $data['blacklist_ip'];
                    }
                    $message = __('was added to blacklisted IP addresses', 'wishlist-member');
                } else {
                    $value   = str_replace($data['blacklist_ip'], '', $value);
                    $message = __('was removed from blacklisted IP addresses', 'wishlist-member');
                }
                $value = preg_replace('/^\h*\v+/m', '', $value); // Remove empty lines.
                $this->save_option('blacklist_ip', $value);
                $value   = wlm_trim($data['blacklist_ip']);
                $message = "<strong>{$value}</strong> " . $message;
            }
            return [
                'success'  => true,
                'msg'      => $message,
                'msg_type' => 'success',
            ];
        }

        public function reset_limit_counter($data)
        {
            $this->Delete_UserMeta($data['user_id'], 'wpm_login_counter');
            $message = 'IP Limit Counter was reset.';
            return [
                'success'  => true,
                'msg'      => $message,
                'msg_type' => 'success',
            ];
        }

        public function save_user_meta($data)
        {
            if (! isset($data['userid'])) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Member', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
            $userid = $data['userid'];
            unset($data['userid']);
            foreach ($data as $option => $value) {
                $this->Update_UserMeta($userid, $option, $value);
            }
            return [
                'success'  => true,
                'msg'      => __('Saved', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
                'userid'   => $userid,
            ];
        }

        public function remove_user_meta($data)
        {
            if (! isset($data['userid']) || ! isset($data['metakey'])) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid Record', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
            $userid  = $data['userid'];
            $metakey = $data['metakey'];
            delete_user_meta($userid, $metakey);
            return [
                'success'  => true,
                'msg'      => __('Removed', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
                'userid'   => $userid,
            ];
        }

        public function schedule_user_level($data)
        {
            $action         = isset($data['level_action']) ? $data['level_action'] : '';
            $wpm_levels     = $this->get_option('wpm_levels');
            $return_data    = [];
            $force_sync     = true;
            $userids        = isset($data['userids']) ? $data['userids'] : '';
            $userids        = explode(',', $userids);
            $wlm_levels     = isset($data['wlm_levels']) ? (array) $data['wlm_levels'] : [];
            $wlm_level_from = isset($data['wlm_level_from']) ? $data['wlm_level_from'] : [];
            // Make sure the format in m/d/Y, or other functions wont be able to recognize it.
            $registration_date = isset($data['registration_date']) ? $data['registration_date'] : '';
            $registration_date = gmdate('m/d/Y h:i A', wlm_strtotime($registration_date));

            $schedule_date = isset($data['schedule_date']) ? $data['schedule_date'] : '';

            // Use gmdate instead of wlm_date since date time was selected with WordPress timezone already considered.
            $schedule_date = gmdate('m/d/Y h:i A', wlm_strtotime($schedule_date));
            // Email notification settings.
            $email_choices = ['sendlevel', 'send', 'dontsend'];
            $level_email   = isset($data['level_email']) ? $data['level_email'] : 'sendlevel';
            $level_email   = in_array($level_email, $email_choices) ? $level_email : 'sendlevel';

            /**
             * Require email confirmation processing
             *
             * @since 3.6
             */
            $require_confirmation_action = wlm_arrval($data, 'require_email_confirmation');
            if (! in_array($require_confirmation_action, ['uselevelsettings', 'require', 'dontrequire'])) {
                $require_confirmation_action = 'dontrequire';
            }
            $api_key = $this->GetAPIKey();

            $todays_date = strtotime(wlm_date('Y-m-d h:i A'));

            if (count($userids) <= 0) {
                return [
                    'success'  => false,
                    'msg'      => __('No Member selected', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
            if ('unschedule_user_all' === $action) {
                $action_msg = __('unscheduled from all', 'wishlist-member');
                foreach ($userids as $id) {
                    $this->Delete_User_Scheduled_LevelsMeta($id);
                }
            } elseif ('toggle_sequential' === $action) {
                $on = (bool) wlm_arrval($data, 'on');
                $this->is_sequential($userids, $on);
                $return_data = [1, 1]; // Just to trigger the refresh on js side.
                return [
                    'success'  => true,
                    // Translators: %s: Sequential upgrade status.
                    'msg'      => sprintf(__('Sequential set to %s', 'wishlist-member'), $on),
                    'msg_type' => 'success',
                    'data'     => $return_data,
                ];
            } elseif ('toggle_subscribe' === $action) {
                $subscribe = isset($data['subscribe']) ? $data['subscribe'] : 0;

                if ($subscribe) {
                    foreach ($userids as $id) {
                        $this->Delete_UserMeta($id, 'wlm_unsubscribe');
                    }
                    $sub_or_unsub = __('subscribed to', 'wishlist-member');
                } else {
                    foreach ($userids as $id) {
                        $this->Update_UserMeta($id, 'wlm_unsubscribe', 1);
                        $this->send_unsubscribe_notification_to_user($id);
                    }
                    $sub_or_unsub = __('unsubscribed from', 'wishlist-member');
                }
                $return_data = [1, 1]; // Just to trigger the refresh on js side.
                return [
                    'success'  => true,
                    // Translators: %s: "susbcribed to" / "unsubcribed from"
                    'msg'      => sprintf(__('Selected members have been %s Email Broadcast.', 'wishlist-member'), $sub_or_unsub),
                    'msg_type' => 'success',
                    'data'     => $return_data,
                ];
            } elseif ('user_addpost' === $action || 'user_removepost' === $action) {
                $post_type = get_post_type($data['wlm_payperposts']);
                if ($post_type) {
                    if ('user_addpost' === $action) {
                        foreach ($userids as $id) {
                            $this->add_post_users($post_type, $data['wlm_payperposts'], $id);
                        }
                    } else {
                        foreach ($userids as $id) {
                            $this->remove_post_users($post_type, $data['wlm_payperposts'], $id);
                        }
                    }
                    $return_data = [1, 1]; // Just to trigger the refresh on js side.
                    return [
                        'success'  => true,
                        'msg'      => __('Member Pay per post updated', 'wishlist-member'),
                        'msg_type' => 'success',
                        'data'     => $return_data,
                    ];
                } else {
                    return [
                        'success'  => false,
                        'msg'      => __('Invalid Post', 'wishlist-member'),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
            } else {
                if (count($wlm_levels) <= 0) {
                    return [
                        'success'  => false,
                        'msg'      => __('No level selected', 'wishlist-member'),
                        'msg_type' => 'danger',
                    ];
                }
                $action_msg = __('added to', 'wishlist-member');
                foreach ($wlm_levels as $level) {
                    if ('add_user_level' === $action) {
                        $action_msg  = __('added to', 'wishlist-member');
                        $cdate_array = explode('/', $registration_date);
                        $sdate       = gmmktime(gmdate('H'), gmdate('i'), gmdate('s'), (int) $cdate_array[0], (int) $cdate_array[1], (int) $cdate_array[2]);
                        $this->schedule_to_level('wpm_add_membership', $level, $userids, $registration_date);
                        if ($sdate > time()) {
                            $action_msg = __('is scheduled to be added to the', 'wishlist-member');
                        } else {
                            // Send email notification if not scheduled.
                            if ('dontsend' !== $level_email) {
                                foreach ($userids as $uid) {
                                    $email_macros = [
                                        '[password]'    => '********',
                                        '[memberlevel]' => $wpm_levels[ $level ]['name'],
                                    ];
                                    // Use global default unless it's set to sendlevel.
                                    $email_global_default = true;
                                    if ('sendlevel' === $level_email || 'send' === $level_email) {
                                        $this->email_template_level = $level;
                                        $email_global_default = false;

                                        if ('admin_actions' === $_POST['action']) {
                                            // Since this is an Admin Action and (Use Level notifaction) is selected,
                                            // Let's not send email notification for existing members if the level's setting is set "Send Email ONLY for New Members" is set.
                                            if (2 == $wpm_levels[ $level ]['newuser_notification_user']) {
                                                add_filter('wishlistmember_per_level_template_setting_newuser_notification_user_' . $level, '__return_false', 11);
                                            }

                                            if (2 == $wpm_levels[ $level ]['newuser_notification_admin']) {
                                                add_filter('wishlistmember_per_level_template_setting_newuser_notification_admin_' . $level, '__return_false', 11);
                                            }
                                        }
                                    }
                                    $this->send_email_template('registration', $uid, $email_macros, null, null, $email_global_default);
                                    $this->send_email_template('admin_new_member_notice', $uid, $email_macros, $this->get_option('email_sender_address'), null, $email_global_default);
                                }
                            }

                            /**
                             * Require email confirmation processing
                             *
                             * @since 3.6
                             */
                            switch ($require_confirmation_action) {
                                case 'uselevelsettings':
                                    $require_confirmation = (bool) $wpm_levels[ $level ]['requireemailconfirmation'];
                                    break;
                                case 'require':
                                    $require_confirmation = true;
                                    break;
                                case 'dontrequire':
                                    $require_confirmation = false;
                                    break;
                            }
                            if ($require_confirmation) {
                                add_filter('wishlistmember_per_level_template_setting_requireemailconfirmation_' . $level, '__return_true');
                                $this->email_template_level = $level;
                                $macros                     = [
                                    '[password]'    => '********',
                                    '[memberlevel]' => $wpm_levels[ $level ]['name'],
                                ];
                                foreach ($userids as $uid) {
                                    $this->level_unconfirmed($level, $uid, true);
                                    $user                   = get_userdata($uid);
                                    $macros['[confirmurl]'] = get_bloginfo('url') . '/index.php?wlmconfirm=' . $uid . '/' . md5($user->user_email . '__' . $user->user_login . '__' . $level . '__' . $api_key);
                                    $this->send_email_template('email_confirmation', $uid, $macros);
                                }
                                remove_filter('wishlistmember_per_level_template_setting_requireemailconfirmation_' . $level, '__return_true');
                            }

                            foreach ($userids as $uid) {
                                // Lets remove this transient to trigger $this->do_sequential_for_user($id, true); right away.
                                // To reflect it realtime and not wait for cron.
                                delete_transient('wlm_is_doing_sequential_for_' . $uid);
                                $this->do_sequential_for_user($uid, true);
                            }
                        }
                    } elseif ('delete_user_level' === $action) {
                        $action_msg  = __('removed from', 'wishlist-member');
                        // Convert to timestamp using strtotime() instead of gmmktime() to prevent date inconsistency.
                        $sdate       = strtotime($schedule_date);
                        $remove_level_date = ! empty($data['schedule_date']) ? $schedule_date : strtotime(gmdate('m/d/Y h:i A'));
                        if ($sdate > $remove_level_date) {
                            $action_msg = __('is scheduled to be removed from the', 'wishlist-member');
                        }
                        $this->schedule_to_level('wpm_del_membership', $level, $userids, $remove_level_date);
                    } elseif ('move_user_level' === $action) {
                        $action_msg  = __('moved to', 'wishlist-member');
                        $cdate_array = explode('/', $schedule_date);
                        $sdate       = gmmktime(gmdate('H'), gmdate('i'), gmdate('s'), (int) $cdate_array[0], (int) $cdate_array[1], (int) $cdate_array[2]);
                        if ($sdate > $todays_date) {
                            $action_msg = __('is scheduled to be moved to the', 'wishlist-member');
                        }
                        // Get unix timestamp so we can check if it's today or in the past and if so set the $schedule_date to 0.
                        $sdate = strtotime($schedule_date);
                        if ($sdate <= time()) {
                            $schedule_date = 0;
                        }
                        $this->schedule_to_level('wpm_change_membership', $level, $userids, $schedule_date, $wlm_level_from);
                    } elseif ('cancel_user_level' === $action || 'uncancel_user_level' === $action) {
                        $status           = 'cancel_user_level' === $action ? true : false;
                        $cancelled_or_not = $status ? __('Cancelled', 'wishlist-member') : __('Uncancelled', 'wishlist-member');
                        // Convert to timestamp using strtotime() but don't subtract the offset to prevent wrong schedules for timezones with negative offset.
                        $cancel_date      = strtotime($schedule_date);
                        // Use current WordPress time in UTC/GMT time for comparison because cancel date is equal to schedule date converted to UTC timestamp.
                        $todays_date      = strtotime(gmdate('m/d/Y h:i A'));

                        $action_msg       = __('cancelled from', 'wishlist-member');
                        if ($cancel_date <= $todays_date && 'Cancelled' === $cancelled_or_not) {
                            // Check email sending.
                            if ('dontsend' !== $level_email) { // If sending email.
                                if ('sendlevel' !== $level_email) { // If not per level.
                                    add_filter(
                                        'wishlistmember_per_level_templates',
                                        function ($templates) {
                                            unset($templates['membership_cancelled']);
                                            unset($templates['membership_uncancelled']);
                                            return $templates;
                                        }
                                    );
                                }
                            } else { // If not sending.
                                add_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                            }
                            $this->level_cancelled($level, $userids, $status);
                            remove_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                        } elseif ('Uncancelled' === $cancelled_or_not) {
                            // Check email sending.
                            if ('dontsend' !== $level_email) { // If sending email.
                                if ('sendlevel' !== $level_email) { // If not per level.
                                    add_filter(
                                        'wishlistmember_per_level_templates',
                                        function ($templates) {
                                            unset($templates['membership_cancelled']);
                                            unset($templates['membership_uncancelled']);
                                            return $templates;
                                        }
                                    );
                                }
                            } else { // If not sending.
                                add_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                            }
                            $this->level_cancelled($level, $userids, $status);
                            remove_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                            $action_msg = __('uncancelled from', 'wishlist-member');
                        } elseif ($cancel_date > $todays_date && 'Cancelled' === $cancelled_or_not) {
                            $action_msg = __('is scheduled to be cancelled from the', 'wishlist-member');
                            $this->schedule_level_deactivation($level, $userids, $cancel_date);
                        }
                    } elseif ('confirm_user_level' === $action || 'unconfirm_user_level' === $action) {
                        $status = 'unconfirm_user_level' === $action ? true : false;
                        $x      = $this->level_unconfirmed($level, $userids, $status);
                    } elseif ('approve_user_level' === $action || 'unapprove_user_level' === $action) {
                        $action_msg = 'unapprove_user_level' === $action ? 'unapproved on' : 'approved on';
                        $status     = 'unapprove_user_level' === $action ? true : false;

                        /*
                         * hook to wishlistmember_approve_user_levels action so we can
                         * send the approval email to the affected users
                         */
                        add_action(
                            'wishlistmember_approve_user_levels',
                            function ($uid, $level) {
                                $this->send_admin_approval_notification($uid, $level[0]);
                            },
                            10,
                            2
                        );

                        $approval = $this->level_for_approval($level, $userids, $status);
                    } elseif ('unschedule_user_level' === $action) {
                        $action_msg = __('unscheduled from', 'wishlist-member');
                        switch (wlm_post_data()['schedule_type']) {
                            case 'remove':
                                $this->Delete_UserLevelMeta($userids[0], $level, 'scheduled_remove');
                                break;
                            case 'cancel':
                                $this->Delete_UserLevelMeta($userids[0], $level, 'wlm_schedule_level_cancel');
                                $this->Delete_UserLevelMeta($userids[0], $level, 'schedule_level_cancel_reason');
                                break;
                            case 'add':
                            case 'move':
                                $lvls = array_diff((array) $this->get_membership_levels($userids[0]), [$level]);
                                $this->set_membership_levels($userids[0], $lvls);
                                break;
                        }
                    } else {
                        return [
                            'success'  => false,
                            'msg'      => __('Invalid Action', 'wishlist-member'),
                            'msg_type' => 'danger',
                        ];
                    }
                    delete_transient('user_level_action_record_' . $userids[0]);
                }
            }
            $userlevel_data  = [];
            $datetime_format = get_option('date_format') . ' ' . get_option('time_format'); // Get the date format from the WP settings.
            if (isset($data['return_user_level_data'])) {
                foreach ($wlm_levels as $level) {
                    foreach ($userids as $userid) {
                        $lvl_parent                          = $this->level_parent($level, $userid);
                        $lvl_parent                          = $lvl_parent && isset($wpm_levels[ $lvl_parent ]) ? $wpm_levels[ $lvl_parent ]['name'] : '';
                        $reg_date                            = gmdate($datetime_format, $this->user_level_timestamp($userid, $level) + $this->gmt);
                        $reg_date                            = $reg_date ? $reg_date : '';
                        $userlevel_data[ $userid ][ $level ] = [
                            'name'    => $wpm_levels[ $level ]['name'],
                            'parent'  => $lvl_parent,
                            'txnid'   => $this->get_membership_levels_txn_id($userid, $level),
                            'regdate' => $reg_date,
                        ];
                    }
                }
            }

            foreach ($userids as $userid) {
                $level_data = '';
                // We dont need to return level data since we refresh if more than 1 user.
                if (count($userids) <= 1) {
                    $wlUser       = new \WishListMember\User($userid);
                    $levels_count = count($wlUser->Levels);
                    if ($levels_count) {
                        wlm_add_metadata($wlUser->Levels);
                        $levels = $wlUser->Levels;
                        $uid    = $userid;
                        ob_start();
                            include $this->plugin_dir3 . '/ui/admin_screens/members/manage/member_levels.php';
                        $level_data = ob_get_clean();
                    }
                }
                $return_data[ $userid ] = $level_data;
            }
            $this->schedule_sync_membership($force_sync);
            if (count($userids) > 1) {
                $return_data = [
                    'success'  => true,
                    'msg'      => sprintf(
                        // Translators: %s: action (ie. removed from)
                        _n(
                            'Selected members were %s level',
                            'Selected members were %s levels',
                            count($wlm_levels),
                            'wishlist-member'
                        ),
                        $action_msg
                    ),
                    'msg_type' => 'success',
                    'data'     => $return_data,
                ];
            } else {
                $return_data = [
                    'success'     => true,
                    'msg'         => sprintf(
                        // Translators: %s: action (ie. removed from)
                        _n(
                            'Member %s level',
                            'Member %s levels',
                            count($wlm_levels),
                            'wishlist-member'
                        ),
                        $action_msg
                    ),
                    'msg_type'    => 'success',
                    'data'        => $return_data,
                    'user_levels' => array_values($wlUser->Levels),
                ];
            }
            if (count($userlevel_data) > 0) {
                $return_data['level_data'] = $userlevel_data;
            }
            $return_data['x'] = $data;
            return $return_data;
        }

        public function payperpost_search($data)
        {
            if (isset($data['ptype'])) {
                $ptype          = $data['ptype'] ? $data['ptype'] : 'post';
                $group_by_ptype = true;
            } else {
                $ptype          = '';
                $group_by_ptype = false;
            }
            $exclude_id  = ! empty($data['exclude_id']) ? $data['exclude_id'] : [];
            $return_data = [];
            $limit       = sprintf('%d,%d', $data['page'] * $data['page_limit'], $data['page_limit']);
            $search      = "%{$data['search']}%";
            $posts       = $this->get_pay_per_posts(['ID', 'post_title', 'post_type'], $group_by_ptype, $search, $limit, $total, $exclude_id);
            if ($group_by_ptype) {
                $return_data['posts'] = isset($posts[ $ptype ]) ? $posts[ $ptype ] : [];
            } else {
                $return_data['posts'] = $posts ? $posts : [];
            }
            $return_data['total']      = $total;
            $return_data['page_limit'] = $data['page_limit'];
            $return_data['page']       = $data['page'] + 1;
            $ret                       = json_encode($return_data);
            return $ret;
        }

        public function add_remove_payperpost($data)
        {
            $post = get_post($data['postid'], ARRAY_A);
            if (! $post) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid post', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }

            if ('add' === $data['operation']) {
                $return_data = [];
                $users       = $data['userid'];
                if (! is_array($users)) {
                    $users = [$users];
                }
                foreach ($users as $u) {
                    $user = get_user_by('id', $u);
                    if (! $user) {
                        continue;
                    }
                    $return_data[ $user->ID ]                 = $post;
                    $return_data[ $user->ID ]['userid']       = $user->ID;
                    $return_data[ $user->ID ]['display_name'] = $user->display_name;
                    $return_data[ $user->ID ]['user_email']   = $user->user_email;
                    $this->add_post_users($post['post_type'], $post['ID'], $u);
                }
                if (count($return_data) > 0) {
                    if (count($return_data) > 1) {
                        return [
                            'success'  => true,
                            // Translators: %s: post type.
                            'msg'      => sprintf(__('Selected members has been given access to the %s', 'wishlist-member'), $post['post_type']),
                            'msg_type' => 'success',
                            'data'     => $return_data,
                        ];
                    } else {
                        return [
                            'success'  => true,
                            // Translators: %s: post type.
                            'msg'      => sprintf(__('Selected member has been given access to the %s', 'wishlist-member'), $post['post_type']),
                            'msg_type' => 'success',
                            'data'     => $return_data,
                        ];
                    }
                } else {
                    return [
                        'success'  => false,
                        'msg'      => __('Invalid member', 'wishlist-member'),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
            } else {
                $user = get_user_by('id', $data['userid']);
                if (! $user) {
                    return [
                        'success'  => false,
                        'msg'      => __('Invalid member', 'wishlist-member'),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
                $post['userid']       = $user->ID;
                $post['display_name'] = $user->display_name;
                $post['user_email']   = $user->user_email;
                $this->remove_post_users($post['post_type'], $post['ID'], $data['userid']);
                return [
                    'success'  => true,
                    // Translators: %s: post type.
                    'msg'      => sprintf(__('Member access was removed from the %s', 'wishlist-member'), $post['post_type']),
                    'msg_type' => 'success',
                    'data'     => $post,
                ];
            }
        }

        public function get_password_notification($data)
        {
            $f = $this->plugin_dir3 . "/ui/admin_screens/advanced_settings/passwords/{$data["type"]}.php";
            if (file_exists($f)) {
                ob_start();
                include $f;
                $form = ob_get_clean();
                return [
                    'success' => true,
                    'data'    => $data,
                    'form'    => $form,
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('Unable to retrieve the settings', 'wishlist-member'),
                    'data'     => $data,
                    'msg_type' => 'danger',
                ];
            }
        }

        public function create_system_page($data)
        {
            global $wpdb;
            $post_if = $wpdb->get_var($wpdb->prepare('SELECT count(post_title) FROM ' . $wpdb->posts . ' WHERE post_title LIKE %s', $data['page_title']));
            if ($post_if > 0) {
                return [
                    'success'  => false,
                    'msg'      => __('The page you are trying to create already exists', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
            $page_data                   = [];
            $page_data['post_title']     = $data['page_title'];
            $page_data['post_content']   = isset($data['page_content']) ? $data['page_content'] : false;
            $page_data['post_type']      = 'page';
            $page_data['post_status']    = 'publish';
            $page_data['comment_status'] = 'closed';

            // No content? , lets use the template.
            if (! $page_data['post_content']) {
                $f                = $this->legacy_wlm_dir . "/resources/page_templates/{$data['page_for']}_internal.php";
                $data['template'] = $f;
                if (file_exists($f)) {
                    include $f;
                }
                $page_data['post_content'] = $content ? $content : __('Sample Content', 'wishlist-member');
            }

            $id = wp_insert_post($page_data, true);
            if ($id) {
                // Protect the after_login_internal page.
                if ('after_login' === $data['page_for']) {
                    $this->protect($id, 'Y');
                }
                return [
                    'success'    => true,
                    'post_id'    => $id,
                    'data'       => $data,
                    'post_title' => $data['page_title'],
                    'msg'        => __('Page Created', 'wishlist-member'),
                    'msg_type'   => 'success',
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('An error occured while creating the page', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
        }

        public function get_system_page($data)
        {
            $type      = $data['type'];
            $page_type = $this->get_option($type . '_type');
            $pages     = [];
            if (false === $page_type) {
                $p = $this->get_option($type . '_internal');
                if ($p) {
                    $page_type = 'internal';
                } else {
                    $page_type = 'url';
                }
            }

            $pages_text = $this->get_option($type . '_text');
            if (! $pages_text) {
                $f = $this->legacy_wlm_dir . "/resources/page_templates/{$type}_internal.php";
                if (file_exists($f)) {
                    include $f;
                }
                $pages_text = $content ? nl2br($content) : '';
            }

            $pages_url = $this->get_option($type);
            $pages_url = $pages_url ? $pages_url : '';
            $page_type = 'url' === $page_type && ! $pages_url ? 'text' : $page_type;

            $pages['text']     = $pages_text;
            $pages['internal'] = $this->get_option($type . '_internal');
            $pages['url']      = $pages_url;
            return [
                'success'   => true,
                'msg'       => __('System Page settings found', 'wishlist-member'),
                'msg_type'  => 'success',
                'page_type' => $page_type,
                'pages'     => $pages,
                'data'      => $data,
            ];
        }

        public function reset_custom_css($data)
        {
            $this->delete_option('wlm_css');
            require $this->legacy_wlm_dir . '/core/InitialValues.php';
            $wlm_css = '';
            if (isset($wishlist_member_initial_data['reg_form_css'])) {
                $wlm_css .= $wishlist_member_initial_data['reg_form_css'] . "\n";
            }
            if (isset($wishlist_member_initial_data['sidebar_widget_css'])) {
                $wlm_css .= $wishlist_member_initial_data['sidebar_widget_css'] . "\n";
            }
            if (isset($wishlist_member_initial_data['login_mergecode_css'])) {
                $wlm_css .= $wishlist_member_initial_data['login_mergecode_css'] . "\n";
            }
            $this->save_option('wlm_css', $wlm_css);
            return [
                'success'  => true,
                'msg'      => __('CSS has been reset back to Default', 'wishlist-member'),
                'msg_type' => 'success',
                'css'      => $wlm_css,
            ];
        }

        public function save_membership_level($data)
        {
            $x  = $this->get_option('wpm_levels');
            $id = $data['id'];

            if (isset($data['expire_option'])) {
                $data['noexpire'] = (int) ( ! (bool) $data['expire_option'] );
            }

            if (empty($x[ $id ])) {
                $x[ $id ] = [];
            }

            $x[ $id ] = array_merge($x[ $id ], $data);

            $x[ $id ] = array_diff($x[ $id ], [null, '']);
            foreach ($x[ $id ] as &$setting) {
                if (is_scalar($setting)) {
                    $setting = stripslashes($setting);
                }
            }
            unset($setting);

            // Reverse removeFromLevel and addToLevel.
            foreach (['removeFromLevel', 'addToLevel', 'cancelFromLevel', 'cancel_removeFromLevel', 'cancel_addToLevel', 'cancel_cancelFromLevel', 'remove_removeFromLevel', 'remove_addToLevel', 'remove_cancelFromLevel'] as $option) {
                if (isset($data[ $option ])) {
                    $x[ $id ][ $option ] = is_array($x[ $id ][ $option ]) ? array_fill_keys($x[ $id ][ $option ], 1) : [];
                }
            }

            unset($x[ $id ]['newlevel']);
            unset($x[ $id ]['clone']);

            $this->save_option('wpm_levels', $x);

            if (! empty($data['clone']) && ! empty($x[ $data['clone'] ])) {
                $this->clone_membership_content($data['clone'], $data['id']);
            }

            // Auto configure.
            if ($this->get_option('folder_protection_autoconfig')) {
                $rootOfFolders               = wlm_trim($this->get_option('rootOfFolders'));
                $folder_protection_full_path = $this->folder_protection_full_path($rootOfFolders);

                if (! is_dir($folder_protection_full_path)) {
                    // If folder does not exist, we create it.
                    if (! mkdir($folder_protection_full_path)) {
                        trigger_error('Auto-Configure: Could not create folder');
                    }
                }

                $subfolder = $folder_protection_full_path . '/' . $this->string_to_slug($data['name']);
                $folder_id = $this->folder_id($subfolder);
                if (! is_dir($subfolder)) {
                    mkdir($subfolder);
                }
                $content_lvls   = $this->get_content_levels('~FOLDER', $folder_id, true, false);
                $content_lvls   = count($content_lvls) > 0 ? array_keys($content_lvls) : [];
                $content_lvls[] = $data['id'];
                $this->set_content_levels('folders', $folder_id, $content_lvls);
                $this->folder_protected($folder_id, true);
            }

            return [
                'success'    => true,
                'msg'        => __('Saved', 'wishlist-member'),
                'msg_type'   => 'success',
                'wpm_levels' => $x,
            ];
        }

        public function save_payperpost($data)
        {
            $option_name = 'payperpost-' . (int) $data['id'];
            $value       = $this->get_option($option_name);
            $value       = array_merge(is_array($value) ? $value : [], $data);

            if (! is_array($value)) {
                 $value_strip = stripslashes($value);
            }

            $this->save_option($option_name, $value_strip);

            $this->save_option($option_name, $value);

            return [
                'success'  => true,
                'msg'      => __('Saved', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $value,
            ];
        }

        public function toggle_payperpost($data)
        {
            if (isset($data['is_ppp'])) {
                $this->pay_per_post($data['id'], (bool) $data['is_ppp']);
            }
            if (isset($data['free_ppp'])) {
                $this->free_pay_per_post($data['id'], (bool) $data['free_ppp']);
            }

            return [
                'success'  => true,
                'msg'      => __('Saved', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function save_payperpost_settings($data)
        {
            $x = $this->get_option('payperpost');
            if (! is_array($x)) {
                $x = [];
            }

            $ppp = wlm_arrval($data, 'payperpost');
            if (! is_array($ppp)) {
                $ppp = [];
            }

            $ppp = array_merge($x, $ppp);

            $login_url = wlm_arrval($ppp, 'login_url');
            if ($login_url && ! preg_match('#^(http|https)://#')) {
                $ppp['login_url'] = 'http://' . $login_url;
            }

            $afterreg_url = wlm_arrval($ppp, 'afterreg_url');
            if ($afterreg_url && ! preg_match('#^(http|https)://#')) {
                $ppp['afterreg_url'] = 'http://' . $afterreg_url;
            }

            $this->save_option('payperpost', $ppp);

            $data['payperpost'] = $ppp;
            return [
                'success'  => true,
                'msg'      => __('Saved', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function save_custom_registration_form($data)
        {
            $this->save_custom_reg_form(false, $data);
            $regforms = $this->get_custom_reg_forms();
            return [
                'success'  => true,
                'msg'      => __('Saved', 'wishlist-member'),
                'msg_type' => 'success',
                'regforms' => $regforms,
            ];
        }

        public function clone_custom_registration_form($data)
        {
            $this->clone_custom_reg_form($data['id']);
            $regforms = $this->get_custom_reg_forms();
            return [
                'success'  => true,
                'msg'      => __('Custom Registration Form Cloned', 'wishlist-member'),
                'msg_type' => 'success',
                'regforms' => $regforms,
            ];
        }

        public function delete_membership_level($data)
        {
            $x = $this->get_option('wpm_levels');
            if (empty($x[ $data['id'] ]['count'])) {
                unset($x[ $data['id'] ]);
                $this->save_option('wpm_levels', $x);
                return [
                    'success'    => true,
                    'msg'        => __('Membership Level Deleted', 'wishlist-member'),
                    'msg_type'   => 'warning',
                    'wpm_levels' => $x,
                ];
            } else {
                return [
                    'success'    => false,
                    'msg'        => __('Cannot delete the Membership Level because it has members in it', 'wishlist-member'),
                    'msg_type'   => 'danger',
                    'wpm_levels' => $x,
                ];
            }
        }

        public function delete_custom_registration_form($data)
        {
            $this->delete_custom_reg_form($data['id']);
            return [
                'success'      => true,
                'msg'          => __('Custom Registration Form Deleted', 'wishlist-member'),
                'msg_type'     => 'warning',
                'wpm_regforms' => $this->get_custom_reg_forms(),
            ];
        }

        public function add_user($data)
        {
            $wpm_errmsg   = '';
            $password_fld = $data['password_field']; // Prevents autocomplete.
            if (! isset($data[ $password_fld ]) || empty($data[ $password_fld ])) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid passsword, please reload the page and try again', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            unset($data['password_field']);
            $data['password1'] = $data[ $password_fld ];
            $data['password2'] = $data[ $password_fld ];

            switch (wlm_arrval($data, 'send_welcome_email')) {
                case 'send':
                    add_filter('wishlistmember_per_level_template_setting_newuser_notification_user_' . $data['wpm_id'], '__return_true', 11);
                    add_filter('wishlistmember_per_level_template_setting_newuser_notification_admin_' . $data['wpm_id'], '__return_true', 11);
                    // Proceed to sendlevel.
                case 'sendlevel':
                    $send_welcome_email = true;
                    break;
                default:
                    $send_welcome_email = false;
            }
            $notify_admin_of_newuser = $send_welcome_email;

            unset($data['send_welcome_email']);

            /**
             * Check whether to require email confirmation or not
             *
             * @since 3.6
             */
            switch (wlm_arrval($data, 'require_email_confirmation')) {
                case 'uselevelsettings':
                    $function = ( new \WishListMember\Level($data['wpm_id']) )->requireemailconfirmation ? '__return_true' : '__return_false';
                    break;
                case 'require':
                    $function = '__return_true';
                    add_filter('wishlistmember_per_level_template_setting_requireemailconfirmation_' . $data['wpm_id'], '__return_true');
                    break;
                default:
                    $function = '__return_false';
            }
            // $this->save_option( 'admin_add_member_require_email_confirmation', wlm_arrval( $data, 'require_email_confirmation' ) );
            unset($data['require_email_confirmation']);
            add_filter('wishlistmember3_wpmregister_send_email_confirmation', $function);

            $registered = $this->wpm_register($data, $wpm_errmsg, $send_welcome_email, $notify_admin_of_newuser);

            /**
             * Remove wishlistmember3_wpmregister_send_email_confirmation filter
             *
             * @since 3.6
             */
            remove_filter('wishlistmember3_wpmregister_send_email_confirmation', $function);
            /**
             * Remove wishlistmember_per_level_template_setting_requireemailconfirmation_[level_id] filter
             *
             * @since 3.6
             */
            remove_filter('wishlistmember_per_level_template_setting_requireemailconfirmation_' . $data['wpm_id'], '__return_true');
            /**
             * Remove wishlistmember_per_level_template_setting_newuser_notification_user_[level_id] filter
             *
             * @since 3.14.8264
             */
            remove_filter('wishlistmember_per_level_template_setting_newuser_notification_user_' . $data['wpm_id'], '__return_true', 11);
            /**
             * Remove wishlistmember_per_level_template_setting_newuser_notification_admin_[level_id] filter
             *
             * @since 3.14.8264
             */
            remove_filter('wishlistmember_per_level_template_setting_newuser_notification_admin_' . $data['wpm_id'], '__return_true', 11);

            if ($registered) {
                return [
                    'success'  => true,
                    'msg'      => __('Member has been added', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => $wpm_errmsg,
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
        }

        public function delete_user_action($data)
        {
            global $current_user;
            $userids = isset($data['userids']) ? $data['userids'] : '';
            $userids = explode(',', $userids);
            if (count($userids) <= 0) {
                return [
                    'success'  => false,
                    'msg'      => __('No member selected', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }
            $return_data = [];
            foreach ($userids as $id) {
                if (isset($current_user->ID) && $current_user->ID == $id) {
                    continue;
                }
                $x                  = wp_delete_user($id, 1);
                $return_data[ $id ] = $x;
            }
            $this->schedule_sync_membership(true);
            if (count($userids) > 1) {
                return [
                    'success'  => true,
                    'msg'      => __('Selected members have been deleted', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $return_data,
                ];
            } else {
                return [
                    'success'  => true,
                    'msg'      => __('Member has been deleted', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $return_data,
                ];
            }
        }

        public function update_user($data)
        {
            global $current_user;
            $operation   = wlm_arrval($data, 'operation');
            $wlUser      = new \WishListMember\User($data['userid'], true);
            $profileuser = $wlUser->user_info;
            if ('get_form' !== $operation) {
                // Save data.
                do_action('wishlistmember_pre_update_user', $data);
                // If display name was not changed.
                if (wlm_trim($data['display_name']) == $profileuser->display_name) {
                    // And if first name and last name is changed.
                    if ($profileuser->first_name != $data['first_name'] || $profileuser->last_name != $data['last_name']) {
                        if (! empty($data['first_name']) || ! empty($data['last_name'])) {
                            $data['display_name'] = "{$data['first_name']} {$data['last_name']}";
                        }
                    }
                }

                $user_data = [
                    'ID'           => $data['userid'],
                    'user_email'   => $data['user_email'],
                    'first_name'   => $data['first_name'],
                    'last_name'    => $data['last_name'],
                    'display_name' => $data['display_name'],
                    'role'         => $data['role'],
                ];

                if (isset($data['user_pass'])) {
                    $passmin  = $this->get_option('min_passlength');
                    $passmin += 0;
                    if (! $passmin) {
                        $passmin = 8;
                    }
                    if (strlen(wlm_trim($data['user_pass'])) < $passmin) {
                        // Translators: %d: minimum number of characters.
                        $wpm_errmsg = sprintf(__('Password has to be at least %d characters long and must not contain spaces and backslash', 'wishlist-member'), $passmin);
                        return [
                            'success'  => false,
                            'msg'      => $wpm_errmsg,
                            'msg_type' => 'danger',
                            'data'     => $data,
                        ];
                    }

                    // Check email length - cannot be more than 100 characters.
                    if (strlen($data['user_pass']) > 100) {
                        $wpm_errmsg = __('Email address cannot be more than 100 characters in length. Please enter a shorter email address', 'wishlist-member');
                        return [
                            'success'  => false,
                            'msg'      => $wpm_errmsg,
                            'msg_type' => 'danger',
                            'data'     => $data,
                        ];
                    }

                    // Make sure password does not contain \ and spaces.
                    $chars = preg_quote(' \\', '/');
                    if (preg_match('/[' . $chars . ']/', $data['user_pass'])) {
                        $wpm_errmsg = __('Password must not contain spaces and backslash(\).', 'wishlist-member');
                        return [
                            'success'  => false,
                            'msg'      => $wpm_errmsg,
                            'msg_type' => 'danger',
                            'data'     => $data,
                        ];
                    }

                    // Validate password strength (if enabled)
                    if ($this->get_option('strongpassword') && ! wlm_check_password_strength($data['user_pass'])) {
                        $wpm_errmsg = __('Please provide a strong password. Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.', 'wishlist-member');
                        return [
                            'success'  => false,
                            'msg'      => $wpm_errmsg,
                            'msg_type' => 'danger',
                            'data'     => $data,
                        ];
                    }

                    $user_data['user_pass'] = $data['user_pass'];
                    // WLMIS infusionsoft login update password.
                    wlm_post_data()['pass1'] = $data['user_pass'];
                }

                $return = wp_update_user($user_data);
                if (is_wp_error($return)) {
                    return [
                        'success'  => false,
                        'msg'      => $return->get_error_message(),
                        'msg_type' => 'danger',
                        'data'     => $data,
                    ];
                }
                $transactionids  = isset($data['txnid']) && is_array($data['txnid']) ? $data['txnid'] : [];
                $leveldate       = isset($data['lvltime']) && is_array($data['lvltime']) ? $data['lvltime'] : [];
                $wpm_login_limit = isset($data['wpm_login_limit']) ? wlm_trim($data['wpm_login_limit']) : '';
                foreach ((array) $transactionids as $lvlid => $txnid) {
                    if (preg_match('#.+[-/,:]#', $leveldate[ $lvlid ])) {
                        $gmt = get_option('gmt_offset');
                        if ($gmt >= 0) {
                            $gmt = '+' . $gmt;
                        }
                        $gmt = ' ' . $gmt . ' GMT';
                    } else {
                        $gmt = '';
                    }
                    $this->set_membership_level_txn_id($data['userid'], $lvlid, $txnid);

                    // Get the timestamp.
                    $lvl_timestamp = wlm_strtotime($leveldate[ $lvlid ] . $gmt);
                    if (! $lvl_timestamp) {
                        $lvl_timestamp = wlm_strtotime($leveldate[ $lvlid ], true);
                    }

                    $this->user_level_timestamp($data['userid'], $lvlid, $lvl_timestamp, true);
                }
                $this->Update_UserMeta($data['userid'], 'wpm_login_limit', $wpm_login_limit);
                $wlm_unsubscribe = isset($data['wlm_unsubscribe']) ? $data['wlm_unsubscribe'] : '';
                $wlm_unsubscribe = '1' == $wlm_unsubscribe ? 1 : 0;
                $this->Update_UserMeta($data['userid'], 'wlm_unsubscribe', $wlm_unsubscribe);

                foreach ((array) $data['wpm_useraddress'] as $k => $v) {
                    $data['wpm_useraddress'][ $k ] = stripslashes($v);
                }
                $this->Update_UserMeta($data['userid'], 'wpm_useraddress', $data['wpm_useraddress']);

                // Custom fields.
                $user_custom_fields = isset($data['customfields']) ? $data['customfields'] : [];
                if (! empty($user_custom_fields)) {
                    $custom_fields = $this->get_custom_fields();
                    foreach ($user_custom_fields as $field => $v) {
                        if (array_key_exists($field, $custom_fields)) {
                            $this->Update_UserMeta($data['userid'], 'custom_' . $field, $v);
                        }
                    }
                }

                $this->Update_UserMeta($data['userid'], 'privacy_disable_ip_tracking', $data['privacy_disable_ip_tracking']);
                $purge_ip_data = false;
                if ($data['privacy_disable_ip_tracking'] > 0 || ( ! $data['privacy_disable_ip_tracking'] && $this->get_option('privacy_disable_ip_tracking') )) {
                    $this->Delete_UserMeta($data['userid'], 'wpm_login_ip');
                    $this->Delete_UserMeta($data['userid'], 'wpm_login_counter');
                    $this->Delete_UserMeta($data['userid'], 'wpm_registration_ip');
                }

                do_action('wishlistmember_post_update_user', $data);

                $level_data   = '';
                $wlUser       = new \WishListMember\User($data['userid']);
                $levels_count = count($wlUser->Levels);
                if ($levels_count) {
                    wlm_add_metadata($wlUser->Levels);
                    $levels = $wlUser->Levels;
                    $uid    = $data['userid'];
                    ob_start();
                        include $this->plugin_dir3 . '/ui/admin_screens/members/manage/member_levels.php';
                    $level_data = ob_get_clean();
                }
                $return_data[ $data['userid'] ] = $level_data;

                return [
                    'success'     => true,
                    'msg'         => __('Member profile has been updated', 'wishlist-member'),
                    'msg_type'    => 'success',
                    'userdata'    => $data,
                    'user_levels' => array_values($wlUser->Levels),
                    'data'        => $return_data,
                ];
            } else {
                if (! $profileuser) {
                    return [
                        'success'  => false,
                        'msg'      => __('Invalid member', 'wishlist-member'),
                        'msg_type' => 'danger',
                    ];
                }
                $mlevels    = $this->get_membership_levels($profileuser->ID);
                $wpm_levels = $this->get_option('wpm_levels');
                ob_start();
                    include $this->plugin_dir3 . '/ui/admin_screens/members/manage/edit_user.php';
                $edit_form = ob_get_clean();
                return [
                    'success'      => true,
                    'msg'          => __('Member Found', 'wishlist-member'),
                    'msg_type'     => 'success',
                    'current_user' => $current_user->ID,
                    'data'         => $profileuser->data,
                    'form'         => $edit_form,
                ];
            }
        }

        public function resend_reset_link($data)
        {
            if (! isset($data['user_login']) || ! $data['user_login']) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid member', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            do_action('retrieve_password/wlminternal', $data['user_login']);
            return [
                'success'  => true,
                'msg'      => __('Password Reset link sent', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function logout_everywhere($data)
        {
            if (! isset($data['user_id']) || ! $data['user_id']) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid member', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
            $sessions = WP_Session_Tokens::get_instance($data['user_id']);
            // We have got the sessions, destroy them all!
            $sessions->destroy_all();
            return [
                'success'  => true,
                'msg'      => __('Member logged out', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        protected function integration_is_active($id, $option, $format = '%s')
        {
            $list = (array) $this->get_option($option);
            $item = sprintf($format, $id);
            /**
             * Filters the result of integration_is_active()
             *
             * @param boolean $status Integration active status.
             * @param string $id      Integration ID.
             * @param string $option  Integration option field. Can be used to check the type of integration.
             */
            return apply_filters('wishlistmember_integration_is_active', in_array($item, $list), $id, $option);
        }

        public function payment_integration_is_active($id)
        {
            return $this->integration_is_active($id, 'ActiveShoppingCarts', 'integration.shoppingcart.%s.php');
        }

        public function email_integration_is_active($id)
        {
            return $this->integration_is_active($id, 'active_email_integrations');
        }

        public function other_integration_is_active($id)
        {
            return $this->integration_is_active($id, 'active_other_integrations');
        }

        public function delete_rollback($data)
        {
            @unlink(WLM_ROLLBACK_PATH . $data['rollback_version']);
            wp_send_json(
                [
                    'success' => true,
                ]
            );
        }

        public function preview_broadcast($data)
        {
            global $wpdb;
            $wpm_levels = $this->get_option('wpm_levels');

            if (isset($data['send_to_admin']) && 1 == $data['send_to_admin']) {
                $current_user = wp_get_current_user();
                // Get can spam requirements.
                $address = [];
                $street1 = $this->get_option('email_sender_street1');
                $street2 = $this->get_option('email_sender_street2');
                $city    = $this->get_option('email_sender_city');
                $state   = $this->get_option('email_sender_state');
                $zip     = $this->get_option('email_sender_zipcode');
                $country = $this->get_option('email_sender_country');
                if (wlm_trim($city)) {
                    $address[] = wlm_trim($city);
                }
                if (wlm_trim($state)) {
                    $address[] = wlm_trim($state);
                }
                if (wlm_trim($zip)) {
                    $address[] = wlm_trim($zip);
                }
                if (wlm_trim($country)) {
                    $address[] = wlm_trim($country);
                }
                $canspamaddress = wlm_trim($street1) . ', ';
                if ('' != wlm_trim($street2)) {
                    $canspamaddress .= wlm_trim($street2) . ', ';
                }
                $canspamaddress .= implode(', ', $address);

                $footer    = "\n\n";
                $signature = isset($data['signature']) ? wlm_trim($data['signature']) : '';
                if (! empty($signature)) {
                    $footer .= $signature . "\n\n";
                }

                // Add unsubcribe and user details link.
                $footer .= sprintf(WLMCANSPAM, $current_user->ID . '/' . substr(md5($current_user->ID . AUTH_SALT), 0, 10)) . "\n\n";
                $footer .= $canspamaddress;

                // Prepare the message.
                $msg         = wlm_trim($data['message']);
                $header_type = ( 'html' ) ? 'plain' !== $data['sent_as'] : 'html';
                // Process shortcodes.
                $shortcode_data = $this->wlmshortcode->manual_process($current_user->ID, $msg, true);
                // Lets make sure that it is an array.
                if (! is_array($shortcode_data)) {
                    $shortcode_data = [];
                }
                // Strip tags for membership levels.
                if ($shortcode_data['wlm_memberlevel']) {
                    $shortcode_data['wlm_memberlevel'] = wp_strip_all_tags($shortcode_data['wlm_memberlevel']);
                }
                if ($shortcode_data['wlmmemberlevel']) {
                    $shortcode_data['wlmmemberlevel'] = wp_strip_all_tags($shortcode_data['wlmmemberlevel']);
                }
                if ($shortcode_data['memberlevel']) {
                    $shortcode_data['memberlevel'] = wp_strip_all_tags($shortcode_data['memberlevel']);
                }

                if ('html' === $data['sent_as']) {
                    $fullmsg = $msg . nl2br($footer);
                } else {
                    $fullmsg = $msg . $footer;
                }

                $x      = [$this->get_option('email_sender_address'), stripslashes($data['subject']), stripslashes($fullmsg), $header_type];
                $name   = 'wlmember_preview_mail_' . md5(serialize($x));
                $mailed = add_option($name, $x, '', 'no');

                $mails = $wpdb->get_results("SELECT `option_name`,`option_value` FROM {$wpdb->options} WHERE `option_name` LIKE 'wlmember\_preview\_mail\_%'");

                if ($mails) {
                    // Go through and send the emails.
                    foreach ((array) $mails as $mail) {
                        $xname = $mail->option_name;
                        $mail  = wlm_maybe_unserialize($mail->option_value);
                        if (false !== strpos($mail[3], 'html')) {
                            $result = $this->send_html_mail($mail[0], $mail[1], $mail[2], $shortcode_data, false, null, 'UTF-8');
                        } else {
                            $result = $this->send_plaintext_mail($mail[0], $mail[1], $mail[2], $shortcode_data, false, null, 'UTF-8');
                        }
                        $data['admin_email_sent'] = $result;
                        delete_option($xname);
                    }
                }
            }

            $otheroptions             = $data['otheroptions'] ?? [];
            $active_count             = $this->member_ids_by_status('active', $data['send_mlevels'], false, true);
            $data['total_recipients'] = $active_count[0] ?? 0;
            if (in_array('c', $otheroptions)) { // If canceled members should be included.
                $cancelled_count           = $this->cancelled_member_ids($data['send_mlevels'], false, true);
                $data['total_recipients'] += $cancelled_count[0] ?? 0;
            }
            if (in_array('p', $otheroptions)) { // If pending members should be included.
                $for_approval_count        = $this->for_approval_member_ids($data['send_mlevels'], false, true);
                $data['total_recipients'] += $for_approval_count[0] ?? 0;
            }

            ob_start();
                include $this->plugin_dir3 . '/ui/admin_screens/administration/broadcast/preview.php';
            $preview = ob_get_clean();
            return [
                'success' => true,
                'data'    => $data,
                'preview' => $preview,
            ];
        }

        public function create_broadcast($data)
        {
            global $wpdb;
            if (! empty($data['broadcast_use_custom_sender_info'])) {
                $this->save_option('last_broadcast_sender_name', $data['from_name']);
                $this->save_option('last_broadcast_sender_address', $data['from_email']);
            } else {
                $data['from_name']  = $this->get_option('email_sender_name');
                $data['from_email'] = $this->get_option('email_sender_address');
            }

            $this->save_option('broadcast_use_custom_sender_info', $data['broadcast_use_custom_sender_info'] ? 1 : 0);
            $from_name    = isset($data['from_name']) ? stripslashes($data['from_name']) : '';
            $from_email   = isset($data['from_email']) ? stripslashes($data['from_email']) : '';
            $subject      = isset($data['subject']) ? stripslashes($data['subject']) : '';
            $msg          = isset($data['message']) ? wlm_trim($data['message']) : '';
            $sent_as      = isset($data['sent_as']) ? wlm_trim($data['sent_as']) : '';
            $send_to      = isset($data['send_to']) ? wlm_trim($data['send_to']) : '';
            $otheroptions = isset($data['otheroptions']) ? (array) $data['otheroptions'] : [];
            $otheroptions = implode('#', $otheroptions);
            $mlevel       = [];
            $error        = '';

            if ('send_mlevels' === $send_to) {
                $mlevel = (array) $data['send_mlevels'];
            } elseif ('send_search' === $send_to) {
                $mlevel = (array) $data['save_searches'];
            } else {
                $error = __('Invalid Levels: Neither Levels or Save Searches was given', 'wishlist-member');
            }
            $mlevel = implode('#', $mlevel);

            $signature = isset($data['signature']) ? wlm_trim($data['signature']) : '';
            // Save the signature and can spam address info.
            $broadcast               = [];
            $broadcast['signature']  = $signature;
            $broadcast['from_name']  = $from_name;
            $broadcast['from_email'] = $from_email;
            $this->save_option('broadcast', $broadcast);

            $address = [];
            $street1 = $this->get_option('email_sender_street1');
            $street2 = $this->get_option('email_sender_street2');
            $city    = $this->get_option('email_sender_city');
            $state   = $this->get_option('email_sender_state');
            $zip     = $this->get_option('email_sender_zipcode');
            $country = $this->get_option('email_sender_country');
            if (wlm_trim($city)) {
                $address[] = wlm_trim($city);
            }
            if (wlm_trim($state)) {
                $address[] = wlm_trim($state);
            }
            if (wlm_trim($zip)) {
                $address[] = wlm_trim($zip);
            }
            if (wlm_trim($country)) {
                $address[] = wlm_trim($country);
            }
            $canspamaddress = wlm_trim($street1) . ', ';
            if ('' != wlm_trim($street2)) {
                $canspamaddress .= wlm_trim($street2) . ', ';
            }
            $canspamaddress .= implode(', ', $address);

            // Prepare footer as array,we will add unsub link later.
            $footer = [];
            if (! empty($signature)) {
                $footer['signature'] = $signature;
            }
            $footer['address'] = $canspamaddress;
            $footer            = serialize($footer);

            $record_id = false;
            if (empty($error)) {
                $record_id = $this->save_email_broadcast($subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions, $from_name, $from_email);
                if (! $record_id) {
                    $error = __('An error occured while saving the broadcast.', 'wishlist-member') . $wpdb->last_error;
                }
            }

            if (! empty($error)) {
                return [
                    'success'  => false,
                    'msg'      => $error,
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            } else {
                return [
                    'success'  => true,
                    'msg'      => __('Broadcast created', 'wishlist-member'),
                    'msg_type' => 'success',
                    'data'     => $data,
                    'id'       => $record_id,
                ];
            }
        }

        public function queue_broadcast($data)
        {
            $emailbroadcast = $this->get_email_broadcast($data['id']);
            if (! $emailbroadcast) {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid broadcast id', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }

            ignore_user_abort(true);
            wp_raise_memory_limit('create_broadcast');
            wlm_set_time_limit(86400); // Limit this script to run for 1 day only, I think its enough.
            $mlevel       = explode('#', $emailbroadcast->mlevel);
            $otheroptions = explode('#', $emailbroadcast->otheroptions);
            $recipients   = [];
            if ('send_mlevels' === $emailbroadcast->send_to) {
                $include_pending   = in_array('p', $otheroptions);
                $include_cancelled = in_array('c', $otheroptions);

                $members                            = $this->member_ids(null, true);
                $cancelled                          = $this->cancelled_member_ids(null, true);
                $pending                            = $this->for_approval_member_ids(null, true);
                $expiredmembers                     = $this->expired_members_id();
                                $unconfirmedmembers = $this->unconfirmed_member_ids(null, true);

                foreach ($mlevel as $level) {
                    $xmembers     = $members[ $level ];
                    $members_cnt += count($members[ $level ]);
                    // Exclude cancelled levels unless specified otherwise.
                    $cancelled_cnt += count($cancelled[ $level ]);
                    if (! $include_cancelled) {
                        $xmembers = array_diff($xmembers, $cancelled[ $level ]);
                    }
                    // Exclude pending members unless specified otherwise.
                    $pending_cnt += count($pending[ $level ]);
                    if (! $include_pending) {
                        $xmembers = array_diff($xmembers, $pending[ $level ]);
                    }
                    // Exclude Expired Members.
                    $xmembers     = array_diff($xmembers, $expiredmembers[ $level ]);
                    $expired_cnt += count($expiredmembers[ $level ]);

                                        // Exclude Unconfirmed Members.
                    $xmembers         = array_diff($xmembers, $unconfirmedmembers[ $level ]);
                    $unconfirmed_cnt += count($unconfirmedmembers[ $level ]);

                    if (is_array($xmembers)) {
                        $recipients = array_merge($recipients, $xmembers);
                    }
                }
            } elseif ('send_search' === $emailbroadcast->send_to) {
                $save_searches = $this->get_saved_search($mlevel[0]);
                if ($save_searches) {
                    $save_searches  = $save_searches[0];
                    $usersearch     = isset($save_searches['search_term']) ? $save_searches['search_term'] : '';
                    $usersearch     = isset($save_searches['usersearch']) ? $save_searches['usersearch'] : $usersearch;
                    $wp_user_search = new \WishListMember\User_Search($usersearch, '', '', '', '', '', 99999999, $save_searches);
                    $recipients     = $wp_user_search->results;
                } else {
                    $recipients = [];
                }
            }
            // Remove unsubscribed users.
            $unsubscribed_users = $this->get_unsubscribed_users();
            $recipients         = array_diff($recipients, $unsubscribed_users);
            // Get unique recipients.
            $recipients   = array_diff(array_unique($recipients), [0]);
            $total_queued = 0;
            foreach ((array) $recipients as $id) {
                if ($this->add_email_broadcast_queue($data['id'], $id)) {
                    ++$total_queued;
                }
            }
            $broadcast_data = [
                'status'       => __('Queued', 'wishlist-member'),
                'total_queued' => $total_queued,
            ];
            $this->update_email_broadcast($data['id'], $broadcast_data);
            $data['total_queued'] = $total_queued;
            return [
                'success'  => true,
                'msg'      => __('Your broadcast is already in queue', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function fetch_email_broadcast($data)
        {
            $broadcast = $this->get_email_broadcast($data['id']);
            if ($broadcast) {
                if (isset($broadcast->text_body)) {
                    $broadcast->text_body = stripslashes($broadcast->text_body);
                }
                if (isset($broadcast->footer)) {
                    $broadcast->footer = stripslashes($broadcast->footer);
                }
                if (isset($broadcast->subject)) {
                    $broadcast->subject = stripslashes($broadcast->subject);
                }
                return [
                    'success'   => true,
                    'msg'       => __('Broadcast found', 'wishlist-member'),
                    'msg_type'  => 'success',
                    'data'      => $data,
                    'broadcast' => $broadcast,
                ];
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('Invalid broadcast id', 'wishlist-member'),
                    'msg_type' => 'danger',
                    'data'     => $data,
                ];
            }
        }

        public function changestat_broadcast($data)
        {
            $broadcast_data = ['status' => $data['status']];
            $this->update_email_broadcast($data['id'], $broadcast_data);
            return [
                'success'  => true,
                'msg'      => __('Broadcast status updated', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function delete_broadcast_action($data)
        {
            $this->delete_email_broadcast($data['id']);
            return [
                'success'  => true,
                'msg'      => __('Broadcast has been deleted', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function get_emails_in_queue($data)
        {
            $email_queue = $this->get_email_broadcast_queue(null, false, false, 0);
            $data        = [];
            foreach ($email_queue as $e) {
                $data[] = $e->id;
            }
            if (count($data) > 0 && false === get_transient('wlm_is_sending_broadcast')) {
                $this->send_queued_mail();
            }

            return [
                'success'  => true,
                'msg'      => __('Emails in Queue', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
                'cnt'      => count($data),
            ];
        }

        public function send_emails_in_queue($data)
        {
            return [
                'success'  => $this->send_email_queue($data['id']),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function get_broadcast_status($data)
        {
            ob_start();
                include $this->plugin_dir3 . '/ui/admin_screens/administration/broadcast/status.php';
            $html = ob_get_clean();
            return [
                'success' => true,
                'data'    => $data,
                'html'    => $html,
            ];
        }

        public function remove_failed_broadcast_emails($data)
        {
            $this->delete_email_broadcast_queue($data['qid']);
            ob_start();
                include $this->plugin_dir3 . '/ui/admin_screens/administration/broadcast/status.php';
            $html = ob_get_clean();
            return [
                'success' => true,
                'data'    => $data,
                'html'    => $html,
            ];
        }

        public function requeue_failed_broadcast_emails($data)
        {
            $this->fail_email_broadcast_queue($data['qid'], 0);
            ob_start();
                include $this->plugin_dir3 . '/ui/admin_screens/administration/broadcast/status.php';
            $html = ob_get_clean();
            return [
                'success' => true,
                'data'    => $data,
                'html'    => $html,
            ];
        }

        public function get_backup_queue_count($data)
        {
            $api_queue  = new \WishListMember\API_Queue();
            $queue      = $api_queue->get_queue('backup_queue');
            $queue_left = 0;
            if (count($queue)) {
                $queue      = array_pop($queue);
                $queue_val  = wlm_maybe_unserialize($queue->value);
                $queue_left = count($queue_val['tables']);
            }

            if (false === get_transient('wlm_is_doing_backup')) {
                $this->process_backup_queue();
            }

            return [
                'success'        => true,
                'msg'            => __('Backup in Queue', 'wishlist-member'),
                'msg_type'       => 'success',
                'data'           => $data,
                'cnt'            => $queue_left,
                'backup_monitor' => get_transient('wlm_backup_monitor'),
            ];
        }

        public function cancel_backup($data)
        {
            $api_queue = new \WishListMember\API_Queue();
            $queue     = $api_queue->get_queue('backup_queue');
            if ($queue) {
                $ids = [];
                foreach ($queue as $q) {
                    $ids[]     = $q->ID;
                    $queue_val = wlm_maybe_unserialize($q->value);
                    $tmpname   = $queue_val['backup_name'] . '.tmp';
                    $file      = $queue_val['folder'] . $tmpname;
                    unlink($file);
                }
                $api_queue->delete_queue($ids);
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('No backup in queue to cancel', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }

            return [
                'success'  => true,
                'msg'      => __('Backup has been cancelled', 'wishlist-member'),
                'msg_type' => 'success',
            ];
        }

        public function get_import_queue_count($data)
        {
            $api_queue   = new \WishListMember\API_Queue();
            $queue_count = $api_queue->count_queue('import_member_queue', 0);

            if ($queue_count > 0 && false === get_transient('wlm_is_doing_import')) {
                $this->process_import_members();
            }

            return [
                'success'  => true,
                'msg'      => __('Import in Queue', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
                'cnt'      => $queue_count,
            ];
        }

        public function pause_start_import($data)
        {
            if ('start' === $data['import_action']) {
                $this->save_option('import_member_pause', 0);
            } else {
                $this->save_option('import_member_pause', 1);
            }
            return [
                'success'  => true,
                'msg'      => __('Import in Queue', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function cancel_member_import($data)
        {
            $api_queue = new \WishListMember\API_Queue();
            $queue     = $api_queue->get_queue('import_member_queue');
            if ($queue) {
                $ids = [];
                foreach ($queue as $value) {
                    $ids[] = $value->ID;
                }
                $api_queue->delete_queue($ids);
            } else {
                return [
                    'success'  => false,
                    'msg'      => __('No import in queue to cancel', 'wishlist-member'),
                    'msg_type' => 'danger',
                ];
            }

            return [
                'success'  => true,
                'msg'      => __('Import has  been cancelled', 'wishlist-member'),
                'msg_type' => 'success',
            ];
        }

        public function process_wizard($data)
        {
            $return      = [];
            $html        = '';
            $next_screen = $data['next'];
            $screen      = $data['screen'];
            $wpm_levels  = $this->get_option('wpm_levels');
            $levelid     = isset($data['levelid']) ? $data['levelid'] : '';
            $level_data  = isset($wpm_levels[ $levelid ]) ? $wpm_levels[ $levelid ] : $this->level_defaults;
            if ($levelid) {
                $level_data['id'] = $levelid;
            }

            $wizard_data   = [];
            $wizard_option = [];

            $return['msg_type']     = 'success';
            $return['success']      = true;
            $return['page_to_load'] = false;
            switch ($screen) {
                case 'license':
                    $license = wlm_trim(wlm_arrval($data, 'license'));
                    if ($license) {
                        $this->delete_option('LicenseLastCheck');
                        $this->save_option('LicenseKey', $license);
                        $this->WPWLKeyProcess();
                        if ('1' != $this->get_option('LicenseStatus')) {
                            $return['success']  = false;
                            $return['msg_type'] = 'danger';
                            $return['msg']      = $this->wpwl_check_response;
                        } else {
                            if (count($wpm_levels) > 0) {
                                $next_screen            = '';
                                $return['page_to_load'] = '?page=WishListMember';
                                $this->save_option('wizard_ran', 1);
                            }
                        }
                    } else {
                            $return['success']  = false;
                            $return['msg_type'] = 'danger';
                            $return['msg']      = __('Please provide your license key.', 'wishlist-member');
                    }
                    break;
                case 'license-confirm':
                    break;
                case 'start':
                    break;
                case 'thanks':
                    $next_screen            = '';
                    $return['page_to_load'] = '?page=WishListMember';
                    $this->save_option('wizard_ran', 1);
                    break;
                case 'step-5':
                    if (! isset($data['name']) || '' == $data['name']) {
                        $return['success']  = false;
                        $return['msg_type'] = 'danger';
                        $return['msg']      = 'Level name is empty';
                    } else {
                        $wizard_data['name']                     = isset($data['name']) && '' !== $data['name'] ? $data['name'] : $level_data['name'];
                        $wizard_data['expire_option']            = isset($data['expire_option']) && '' !== $data['expire_option'] ? $data['expire_option'] : $level_data['expire_option'];
                        $wizard_data['expire']                   = isset($data['expire']) && '' !== $data['expire'] ? $data['expire'] : $level_data['expire'];
                        $wizard_data['calendar']                 = isset($data['calendar']) && '' !== $data['calendar'] ? $data['calendar'] : $level_data['calendar'];
                        $wizard_data['expire_date']              = isset($data['expire_date']) && '' !== $data['expire_date'] ? $data['expire_date'] : $level_data['expire_date'];
                        $wizard_data['allposts']                 = isset($data['allposts']) && '' !== $data['allposts'] ? $data['allposts'] : false;
                        $wizard_data['allcategories']            = isset($data['allcategories']) && '' !== $data['allcategories'] ? $data['allcategories'] : false;
                        $wizard_data['allpages']                 = isset($data['allpages']) && '' !== $data['allpages'] ? $data['allpages'] : false;
                        $wizard_data['allcomments']              = isset($data['allcomments']) && '' !== $data['allcomments'] ? $data['allcomments'] : false;
                        $wizard_data['requireadminapproval']     = isset($data['requireadminapproval']) && '' !== $data['requireadminapproval'] ? $data['requireadminapproval'] : false;
                        $wizard_data['requireemailconfirmation'] = isset($data['requireemailconfirmation']) && '' !== $data['requireemailconfirmation'] ? $data['requireemailconfirmation'] : false;
                        $wizard_data['enable_tos']               = isset($data['enable_tos']) && '' !== $data['enable_tos'] ? $data['enable_tos'] : false;
                        $wizard_data['tos']                      = isset($data['tos']) && '' !== $data['tos'] ? $data['tos'] : $level_data['tos'];
                        if (! isset($wpm_levels[ $levelid ])) {
                            $wizard_data['id']         = $levelid;
                            $wizard_data['levelOrder'] = time();
                        }

                        $wizard_option['default_protect']             = isset($data['default_protect']) && '' !== $data['default_protect'] ? $data['default_protect'] : false;
                        $wizard_option['only_show_content_for_level'] = isset($data['only_show_content_for_level']) && '' !== $data['only_show_content_for_level'] ? $data['only_show_content_for_level'] : false;
                        $wizard_option['email_sender_name']           = isset($data['email_sender_name']) && '' !== $data['email_sender_name'] ? $data['email_sender_name'] : false;
                        $wizard_option['email_sender_address']        = isset($data['email_sender_address']) && '' !== $data['email_sender_address'] ? $data['email_sender_address'] : false;
                        // Payment provider.
                        if (isset($data['payment_provider']) && ! empty($data['payment_provider'])) {
                            $this->toggle_payment_provider($data['payment_provider'], true);
                        }
                        // Email provider.
                        if (isset($data['email_provider']) && ! empty($data['email_provider'])) {
                            $this->toggle_email_provider($data['email_provider'], true);
                        }
                        foreach ($wizard_data as $key => $value) {
                            $level_data[ $key ] = $value;
                        }
                        $this->save_membership_level($level_data);
                        foreach ($wizard_option as $key => $value) {
                            $this->save_option($key, $value);
                        }
                    }
                    break;
            }

            if (! empty($next_screen)) {
                ob_start();
                    include $this->plugin_dir3 . "/ui/admin_screens/setup/getting-started/{$next_screen}.php";
                $html = ob_get_clean();
            }
            $return['reload_page'] = $this->get_option('wizard_ran') ? false : true;
            return array_merge(
                $return,
                [
                    'data' => $data,
                    'html' => $html,
                ]
            );
        }

        public function activate_license($data)
        {
            $this->save_option('LicenseKey', wlm_arrval($data, 'licensekey'));
            $this->delete_option('LicenseLastCheck');
            $this->WPWLKeyProcess();
            if ('1' == $this->get_option('LicenseStatus')) {
                $return['success']  = true;
                $return['msg_type'] = 'success';
                $return['msg']      = __('Your license key has been activated for this site', 'wishlist-member');
            } else {
                $this->save_option('LicenseKey', '');
                $this->save_option('LicenseStatus', '1');
                $return['success']  = false;
                $return['msg_type'] = 'danger';
                $return['msg']      = ! empty($this->wpwl_check_response) ? $this->wpwl_check_response : __('Unable to activate your license', 'wishlist-member');
            }
            return $return;
        }

        public function deactivate_license($data)
        {
            $_POST  = $data;
            $return = [];
            $this->WPWLKeyProcess();
            if ('1' != $this->get_option('LicenseStatus')) {
                $return['success']  = true;
                $return['msg_type'] = 'success';
                $return['msg']      = __('Your license key has been deactivated for this site', 'wishlist-member');
            } else {
                $return['success']  = false;
                $return['msg_type'] = 'danger';
                $return['msg']      = ! empty($this->wpwl_check_response) ? $this->wpwl_check_response : 'Unable to deactivate your license';
            }
            return $return;
        }

        public function save_other_integration($data)
        {
            // Prevent multiple calls running at the same time in the same session.
            while (isset($_SESSION[ __FUNCTION__ ])) {
                sleep(1);
            }
            $_SESSION[ __FUNCTION__ ] = 1;

            foreach ($data as $field => $value) {
                if (is_array($value)) {
                    $orig = $this->get_option($field);
                    if (empty($orig)) {
                        $orig = [];
                    }
                    $value = wlm_replace_recursive($orig, $value);

                    // Strip slashes.
                    array_walk_recursive(
                        $value,
                        function (&$val, $key) {
                            if (is_string($val)) {
                                $val = stripslashes($val);
                            }
                        }
                    );
                }
                $this->save_option($field, $value);
            }

            do_action('wishlistmember_save_other_provider', $data);

            unset($_SESSION[ __FUNCTION__ ]);
            wp_send_json(
                [
                    'success' => true,
                    'data'    => $data,
                ]
            );
        }

        public function save_autoresponder($data)
        {
            // Prevent multiple calls running at the same time in the same session.
            while (isset($_SESSION[ __FUNCTION__ ])) {
                sleep(1);
            }
            $_SESSION[ __FUNCTION__ ] = 1;

            $id = $data['autoresponder_id'];
            unset($data['autoresponder_id']);

            /**
             * Allow relative path for data to save so instead of $data
             * being $data['something']['something2']['something3']['realdata'] = 'data'
             * we can now just do:
             *
             * $data['realdata'] = 'data';
             * $data['parent_keys'] = ['something','something2','something3'];
             *
             * the values $data['parent_keys'] will be used to created
             * an associative array and $data will be its end value
             *
             * @since 3.9
             */
            if (is_array($data['parent_keys'])) {
                $parent_keys = $data['parent_keys'];
                unset($data['parent_keys']);
                while ($key = array_pop($parent_keys)) {
                    $data = [$key => $data];
                }
            }

            $ar        = $this->get_option('Autoresponders');
            $ar[ $id ] = wlm_replace_recursive((array) $ar[ $id ], (array) $data);

            $this->save_option('Autoresponders', $ar);

            do_action('wishlistmember_save_email_provider', $data, $id, $ar[ $id ]);
            if (has_action('wishlistmember_save_email_provider') !== false) {
                $ar = $this->get_option('Autoresponders');
            }

            unset($_SESSION[ __FUNCTION__ ]);
            wp_send_json(
                [
                    'success' => true,
                    'data'    => $ar[ $id ],
                ]
            );
        }

        public function save_payment_provider($data)
        {
            // Prevent multiple calls running at the same time in the same session.
            while (isset($_SESSION[ __FUNCTION__ ])) {
                sleep(1);
            }
            $_SESSION[ __FUNCTION__ ] = 1;

            foreach ($data as $field => $value) {
                if (is_array($value)) {
                    $orig = $this->get_option($field);
                    if (empty($orig)) {
                        $orig = [];
                    }
                    $value = wlm_replace_recursive($orig, $value);
                }
                $this->save_option($field, $value);
            }

            do_action('wishlistmember_save_payment_provider', $data);

            unset($_SESSION[ __FUNCTION__ ]);
            wp_send_json(
                [
                    'success' => true,
                    'data'    => $data,
                ]
            );
        }

        public function get_content_protection($data)
        {
            $content = get_post($data['id']);
            ob_start();
                include $this->plugin_dir3 . '/ui/admin_screens/content_protection/post_page_files/content-edit.php';
            $html = ob_get_clean();
            return [
                'success' => true,
                'data'    => $data,
                'html'    => $html,
                'content' => $content,
            ];
        }

        public function update_content_protection($data)
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

            $x_content_type = isset($data['content_comment']) ? '~COMMENT' : $data['content_type'];

            $cannot_set_levels_on_inherited = 0;

            foreach ($contentids as $contentid) {
                // Content protection.
                if (isset(wlm_post_data()['protection']) && ! empty($data['protection'])) {
                    $protection = 'Unprotected' === $data['protection'] ? 'N' : 'Y';
                    switch ($data['protection']) {
                        case 'Unprotected':
                        case 'Protected':
                            switch ($data['content_type']) {
                                case 'categories':
                                    $this->special_content_level($contentid, 'Protection', $protection, '~CATEGORY');
                                    $this->special_content_level($contentid, 'Inherit', 'N', '~CATEGORY');
                                    break;
                                case 'folders':
                                    $this->folder_protected($contentid, $protection);
                                    $this->special_content_level($contentid, 'Inherit', 'N', $x_content_type);
                                    break;
                                default:
                                    $this->special_content_level($contentid, 'Protection', $protection, $x_content_type);
                                    $this->special_content_level($contentid, 'Inherit', 'N', $x_content_type);
                            }
                            break;
                        case 'Inherited':
                            $data['r'] = $this->inherit_protection($contentid, 'categories' === $data['content_type'], isset($data['content_comment']));
                            break;
                    }
                }

                if (( isset($data['wlm_levels']) && ! empty($data['wlm_levels']) ) || ( isset($data['level_action']) && 'set' === $data['level_action'] )) {
                    $action         = isset($data['level_action']) ? $data['level_action'] : 'set';
                    $wlm_levels     = isset($data['wlm_levels']) && is_array($data['wlm_levels']) ? $data['wlm_levels'] : [];
                    $content_levels = $this->get_content_levels($x_content_type, $contentid, true, false);
                    $content_levels = count($content_levels) > 0 ? array_keys($content_levels) : [];
                    if ('add' === $action) {
                        $content_levels = array_merge($content_levels, $wlm_levels);
                    } elseif ('remove' === $action) {
                        $content_levels = array_diff($content_levels, $wlm_levels);
                    } else { // Set.
                        $dummy = array_diff($wlm_levels, $content_levels);
                        if (count($dummy) > 0) {
                            $action = 'add';
                        }
                        $content_levels = $wlm_levels;
                    }

                    $lvls            = $this->get_option('wpm_levels');
                    $protect_inherit = false;
                    switch ($data['content_type']) {
                        case 'categories':
                            foreach ($content_levels as $key => $value) {
                                if (isset($lvls[ $value ]['allcategories']) && $lvls[ $value ]['allcategories']) {
                                    unset($content_levels[ $key ]);
                                }
                            }
                            $protect_inherit = $this->special_content_level($contentid, 'Inherit', null, '~CATEGORY');
                            $protected       = $this->cat_protected($contentid);
                            if (! $protect_inherit) {
                                if ('add' === $action && ! $protected) {
                                    $this->cat_protected($contentid, 'Y');
                                }
                            }
                            break;
                        case 'folders':
                            $protected = $this->folder_protected($contentid);
                            if ('add' === $action && ! $protected) {
                                $this->folder_protected($contentid, 'Y');
                            }
                            break;
                        default:
                            $all = '~COMMENT' === $x_content_type ? 'allcomments' : 'dummy';
                            $all = 'post' === $x_content_type ? 'allposts' : $all;
                            $all = 'page' === $x_content_type ? 'allpages' : $all;
                            foreach ($content_levels as $key => $value) {
                                if (isset($lvls[ $value ][ $all ]) && $lvls[ $value ][ $all ]) {
                                    unset($content_levels[ $key ]);
                                }
                            }
                            $protect_inherit = $this->special_content_level($contentid, 'Inherit', null, $x_content_type);
                            $protected       = $this->special_content_level($contentid, 'Protection', null, $x_content_type);
                            if (! $protect_inherit) {
                                if ('add' === $action && ! $protected) {
                                    $this->special_content_level($contentid, 'Protection', 'Y', $x_content_type);
                                }
                            } else {
                                ++$cannot_set_levels_on_inherited;
                            }
                    }
                    // We only process if theres a level.
                    if (! $protect_inherit) {
                        $this->set_content_levels($x_content_type, $contentid, $content_levels);
                        $this->pass_protection($contentid, 'categories' === $x_content_type);
                    }
                }

                if (isset($data['useraccess']) && ! empty($data['useraccess'])) {
                    $useraccess = isset($data['useraccess']) ? $data['useraccess'] : 'Disabled';
                    switch ($useraccess) {
                        case 'Disabled':
                            $this->pay_per_post($contentid, 'N');
                            $this->free_pay_per_post($contentid, 'N');
                            break;
                        case 'Paid':
                            $this->pay_per_post($contentid, 'Y');
                            $this->free_pay_per_post($contentid, 'N');
                            break;
                        case 'Free':
                            $this->pay_per_post($contentid, 'Y');
                            $this->free_pay_per_post($contentid, 'Y');
                            break;
                    }
                }

                if (isset($data['force_download'])) {
                    $this->folder_force_download($contentid, (bool) $data['force_download']);
                }

                if (isset($data['wlm_payperpost_users'])) {
                    // Return $data;
                    $post = get_post($contentid, ARRAY_A);
                    $user = get_user_by('id', $data['wlm_payperpost_users']);
                    if (! $post) {
                        return [
                            'success'  => false,
                            'msg'      => 'Invalid post',
                            'msg_type' => 'danger',
                            'data'     => $data,
                        ];
                    }
                    if (! $user) {
                        return [
                            'success'  => false,
                            'msg'      => 'Invalid member',
                            'msg_type' => 'danger',
                            'data'     => $data,
                        ];
                    }
                    if ('add' === $data['operation']) {
                        $this->add_post_users($post['post_type'], $post['ID'], $data['wlm_payperpost_users']);
                    } else {
                        $this->remove_post_users($post['post_type'], $post['ID'], $data['wlm_payperpost_users']);
                    }
                }
            }

            $cat_items    = [];
            if ('categories' === $data['content_type']) { // IF CATEGORY, prepare items.
                $args       = ['hide_empty' => 0];
                $taxonomies = get_taxonomies(
                    [
                        '_builtin'     => false,
                        'hierarchical' => true,
                    ],
                    'names'
                );
                array_unshift($taxonomies, 'category');
                foreach ($taxonomies as $taxonomy) {
                    $x = [];
                    foreach (get_terms($taxonomy, $args) as $item) {
                        $item                  = (array) $item;
                        $item['ID']            = $item['term_id'];
                        $item['post_title']    = $item['name'];
                        $item['taxonomy']      = ucfirst($item['taxonomy']);
                        $x[ $item['term_id'] ] = $item;
                    }
                    $cat_items[] = get_terms($taxonomy);
                    foreach ($x as $id => $item) {
                        $x[ $id ]['deep'] = 0;
                        $parents          = [];
                        $z                = $item;
                        while ($z['parent']) {
                            ++$x[ $id ]['deep'];
                            $z         = $x[ $z['parent'] ];
                            $parents[] = $z['name'];
                        }
                        $cat_items[ $id ]                = $x[ $id ];
                        $cat_items[ $id ]['parent_cats'] = $parents;
                    }
                }
            }

            $return_data = [];
            foreach ($contentids as $contentid) {
                $content_data    = '';
                $content_comment = isset($data['content_comment']);
                $content_type    = $x_content_type;
                $checkbox_check  = isset($data['checkbox_check']) ? (int) $data['checkbox_check'] : true;
                ob_start();
                if ('categories' === $data['content_type']) {
                    wlm_cache_flush(); // Find a way to only flush the categories,with out these, CatProtected returns a wrong status of protection.
                    $item = $cat_items[ $contentid ];
                    include $this->plugin_dir3 . '/ui/admin_screens/content_protection/categories/content-item.php';
                } else {
                    do_action('wishlistmember_update_content_protection_content_item', $data['content_type'], $contentid);
                    if (! ob_get_length()) {
                        $item = get_post($contentid);
                        $that = $this;
                        include $this->plugin_dir3 . '/ui/admin_screens/content_protection/post_page_files/content-item.php';
                    }
                }

                do_action('wishlistmember_update_content_protection_content_item', $data['content_type'], $contentid);

                $content_data              = ob_get_clean();
                $return_data[ $contentid ] = $content_data;
            }
            $msg = __('Content protection updated.', 'wishlist-member');
            if ($cannot_set_levels_on_inherited) {
                $msg .= '<br><br>' . sprintf(
                    // Translators: %s: quantity.
                    _n(
                        'Note: Levels were not changed for %s item because protection is set to inherited.',
                        'Note: Levels were not changed for %s items because protection is set to inherited.',
                        $cannot_set_levels_on_inherited,
                        'wishlist-member'
                    ),
                    number_format_i18n($cannot_set_levels_on_inherited)
                );
            }
            return [
                'success' => true,
                'msg'     => $msg,
                'data'    => $data,
                'content' => $return_data,
            ];
        }

        public function ppp_user_search($data)
        {
            $return_data               = [];
            $data['exclude']           = is_array($data['exclude']) && count($data['exclude']) ? $data['exclude'] : [];
            $args                      = [
                'blog_id'     => $GLOBALS['blog_id'],
                'orderby'     => 'login',
                'order'       => 'ASC',
                'offset'      => $data['page'] * $data['page_limit'],
                'search'      => '*' . $data['search'] . '*',
                'number'      => $data['page_limit'],
                'count_total' => true,
                'fields'      => ['ID', 'user_login', 'user_email', 'display_name'],
                'exclude'     => $data['exclude'],
            ];
            $wp_user_query             = new \WP_User_Query($args);
            $return_data['users']      = $wp_user_query->get_results();
            $return_data['total']      = $wp_user_query->total_users;
            $return_data['page_limit'] = $data['page_limit'];
            $return_data['page']       = $data['page'] + 1;
            $ret                       = json_encode($return_data);
            return $ret;
        }

        public function enable_custom_post_types($data)
        {
            $args          = [
                // 'public'                => true,
                // 'exclude_from_search'   => false,
                   '_builtin' => false,
            ];
            $post_types    = get_post_types($args);
            $enabled_types = (array) $this->get_option('protected_custom_post_types');
            foreach ($data as $key => $value) {
                if (! in_array($key, $post_types)) {
                    continue;
                }
                if (1 === (int) $value) {
                    $enabled_types[] = $key;
                } else {
                    $k = array_search($key, $enabled_types);
                    if (false !== $k) {
                        unset($enabled_types[ $k ]);
                    }
                }
                $enabled_types = array_unique($enabled_types);
            }
            $this->save_option('protected_custom_post_types', $enabled_types);

            return [
                'success' => true,
                'msg'     => __('Custom Post Type protection updated', 'wishlist-member'),
                'data'    => $data,
            ];
        }

        public function toggle_payment_provider($provider, $state)
        {
            $providers = $this->toggle_integration_provider('ActiveShoppingCarts', $provider, $state, 'integration.shoppingcart.%s.php');
            do_action('wishlistmember_toggle_payment_provider_' . $provider, (bool) $state);
            return $providers;
        }

        public function toggle_email_provider($provider, $state)
        {
            $providers = $this->toggle_integration_provider('active_email_integrations', $provider, $state);
            do_action('wishlistmember_toggle_email_provider_' . $provider, (bool) $state);
            return $providers;
        }

        public function toggle_other_provider($provider, $state)
        {
            $providers = $this->toggle_integration_provider('active_other_integrations', $provider, $state);
            do_action('wishlistmember_toggle_other_provider_' . $provider, (bool) $state);
            return $providers;
        }

        public function toggle_integration_provider($option, $provider, $state, $format = '%s')
        {
            $active_carts = (array) $this->get_option($option);
            $provider     = sprintf($format, $provider);
            if ($state) {
                $active_carts[] = $provider;
            } else {
                $active_carts = array_diff($active_carts, [$provider]);
            }
            $active_carts = array_unique(array_diff($active_carts, ['', 0, null]));
            $this->save_option($option, $active_carts);

            return $active_carts;
        }

        public function toggle_file_protection($data)
        {
            $file_protection = (int) wlm_arrval($data, 'file_protection');
            $this->save_option('file_protection', $file_protection);
            $this->file_protect_htaccess(! $file_protection);
            return [
                'success'  => true,
                'msg'      => __('Saved', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function save_global_email_notifications($data)
        {
            $map = [
                'require_email_confirmation_subject'  => 'confirm_email_subject',
                'require_email_confirmation_message'  => 'confirm_email_message',

                'require_email_confirmation_reminder' => 'requireemailconfirmation_notification',
                'require_email_confirmation_reminder_subject' => 'email_confirmation_reminder_subject',
                'require_email_confirmation_reminder_message' => 'email_confirmation_reminder_message',

                'email_confirmed'                     => 'email_confirmed',
                'email_confirmed_message'             => 'email_confirmed_message',
                'email_confirmed_message'             => 'email_confirmed_message',

                'require_admin_approval_free_notification_admin' => 'require_admin_approval_free_notification_admin',
                'require_admin_approval_free_admin_subject' => 'requireadminapproval_admin_subject',
                'require_admin_approval_free_admin_message' => 'requireadminapproval_admin_message',
                'require_admin_approval_free_notification_user1' => 'require_admin_approval_free_notification_user1',
                'require_admin_approval_free_user1_subject' => 'requireadminapproval_email_subject',
                'require_admin_approval_free_user1_message' => 'requireadminapproval_email_message',
                'require_admin_approval_free_notification_user2' => 'require_admin_approval_free_notification_user2',
                'require_admin_approval_free_user2_subject' => 'registrationadminapproval_email_subject',
                'require_admin_approval_free_user2_message' => 'registrationadminapproval_email_message',

                'require_admin_approval_paid_notification_admin' => 'require_admin_approval_paid_notification_admin',
                'require_admin_approval_paid_admin_subject' => 'requireadminapproval_admin_paid_subject',
                'require_admin_approval_paid_admin_message' => 'requireadminapproval_admin_paid_message',
                'require_admin_approval_paid_notification_user1' => 'require_admin_approval_paid_notification_user1',
                'require_admin_approval_paid_user1_subject' => 'requireadminapproval_email_paid_subject',
                'require_admin_approval_paid_user1_message' => 'requireadminapproval_email_paid_message',
                'require_admin_approval_paid_notification_user2' => 'require_admin_approval_paid_notification_user2',
                'require_admin_approval_paid_user2_subject' => 'registrationadminapproval_email_paid_subject',
                'require_admin_approval_paid_user2_message' => 'registrationadminapproval_email_paid_message',

                'incomplete_notification'             => 'incomplete_notification',
                'incomplete_subject'                  => 'incnotification_email_subject',
                'incomplete_message'                  => 'incnotification_email_message',

                'newuser_notification_admin'          => 'notify_admin_of_newuser',
                'newuser_admin_recipient'             => 'newmembernotice_email_recipient',
                'newuser_admin_subject'               => 'newmembernotice_email_subject',
                'newuser_admin_message'               => 'newmembernotice_email_message',
                'newuser_notification_user'           => 'newuser_notification_user',
                'newuser_user_subject'                => 'register_email_subject',
                'newuser_user_message'                => 'register_email_body',

                'expiring_notification_admin'         => 'expiring_notification_admin',
                'expiring_admin_subject'              => 'expiring_admin_subject',
                'expiring_admin_message'              => 'expiring_admin_message',
                'expiring_notification_user'          => 'expiring_notification',
                'expiring_user_subject'               => 'expiringnotification_email_subject',
                'expiring_user_message'               => 'expiringnotification_email_message',

                'cancel_notification'                 => 'cancel_notification',
                'cancel_subject'                      => 'cancel_email_subject',
                'cancel_message'                      => 'cancel_email_message',

                'uncancel_notification'               => 'uncancel_notification',
                'uncancel_subject'                    => 'uncancel_email_subject',
                'uncancel_message'                    => 'uncancel_email_message',

            ];

            foreach ($data as $k => &$v) {
                $k = wlm_arrval($map, $k);
                if ($k) {
                    $this->save_option($k, $v = stripslashes($v));
                }
            }
            unset($v);
            return [
                'success'  => true,
                'msg'      => __('Saved', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function reset_level_sender_info_to_default()
        {
            $wpm_levels = $this->get_option('wpm_levels');

            $reset_values = [
                'expiring_level_default_sender'          => 1,
                'require_admin_approval_default_sender'  => 1,
                'registration_approved_default_sender'   => 1,
                'require_admin_approval_paid_default_sender' => 1,
                'registration_approved_paid_default_sender' => 1,
                'email_confirmation_default_sender'      => 1,
                'email_confirmed_default_sender'         => 1,
                'registration_default_sender'            => 1,
                'incomplete_registration_default_sender' => 1,
                'membership_cancelled_default_sender'    => 1,
                'membership_uncancelled_default_sender'  => 1,
            ];

            foreach ($wpm_levels as &$level) {
                $level = array_merge($level, $reset_values);
            }
            unset($level);
            $this->save_option('wpm_levels', $wpm_levels);

            return [
                'success'  => true,
                'msg'      => __('Sender Info Reset for All Levels', 'wishlist-member'),
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        public function apply_email_template_to_selected_levels($data)
        {

            $allowed = [
                'require_email_confirmation_subject',
                'require_email_confirmation_message',

                'require_email_confirmation_reminder',
                'require_email_confirmation_reminder_subject',
                'require_email_confirmation_reminder_message',

                'email_confirmed_subject',
                'email_confirmed_message',

                'require_admin_approval_free_notification_admin',
                'require_admin_approval_free_admin_subject',
                'require_admin_approval_free_admin_message',
                'require_admin_approval_free_notification_user1',
                'require_admin_approval_free_user1_subject',
                'require_admin_approval_free_user1_message',
                'require_admin_approval_free_notification_user2',
                'require_admin_approval_free_user2_subject',
                'require_admin_approval_free_user2_message',

                'require_admin_approval_paid_notification_admin',
                'require_admin_approval_paid_admin_subject',
                'require_admin_approval_paid_admin_message',
                'require_admin_approval_paid_notification_user1',
                'require_admin_approval_paid_user1_subject',
                'require_admin_approval_paid_user1_message',
                'require_admin_approval_paid_notification_user2',
                'require_admin_approval_paid_user2_subject',
                'require_admin_approval_paid_user2_message',

                'incomplete_notification',
                'incomplete_subject',
                'incomplete_message',

                'newuser_notification_admin',
                'newuser_admin_recipient',
                'newuser_admin_subject',
                'newuser_admin_message',
                'newuser_notification_user',
                'newuser_user_subject',
                'newuser_user_message',

                'expiring_notification_admin',
                'expiring_admin_subject',
                'expiring_admin_message',
                'expiring_notification_user',
                'expiring_user_subject',
                'expiring_user_message',

                'cancel_notification',
                'cancel_subject',
                'cancel_message',

                'uncancel_notification',
                'uncancel_subject',
                'uncancel_message',
            ];
            $allowed = array_flip($allowed);

            $data['content'] = array_intersect_key((array) $data['content'], $allowed);

            if (empty($data['content']) || empty($data['levels'])) {
                return [
                    'success'  => false,
                    'msg'      => 'Invalid data',
                    'msg_type' => 'danger',
                ];
            }

            $levels = $this->get_option('wpm_levels');

            $data['content'] = array_map('stripslashes', $data['content']);

            foreach ($data['levels'] as $level) {
                $levels[ $level ] = array_merge($levels[ $level ], $data['content']);
            }
            $this->save_option('wpm_levels', $levels);

            $msg = sprintf(
                // Translators: number of levels.
                _n(
                    'Message saved and applied to %s level',
                    'Message saved and applied to %s levels',
                    count($data['levels']),
                    'wishlist-member'
                ),
                number_format_i18n(count($data['levels']))
            );
            return [
                'success'  => true,
                'msg'      => $msg,
                'msg_type' => 'success',
                'data'     => $data,
            ];
        }

        /**
         * Action handler to resend email confirmation request from the Members admin area
         *
         * @since 3.6
         * @param array $data
         */
        public function resend_email_confirmation_request($data)
        {
            // Numbers and comma only, then explode to array.
            $userids = explode(',', preg_replace('/[^\d,]/', '', $data['userids']));

            // Get all unconfirmed member IDs.
            $unconfirmed = $this->unconfirmed_member_ids(null, true);

            // Get the API Key, we need this to generate the confirmation URL.
            $api_key = $this->GetAPIKey();

            // Add IDs to this array so we do not send more than email to the same user.
            $sent = [];

            // Empty password macro value.
            $macros = ['[password]' => '********'];

            foreach ($unconfirmed as $level => $ids) {
                // Membership level macro valkue.
                $macros['[memberlevel]'] = ( new \WishListMember\Level($level) )->name;
                $ids                     = array_intersect($ids, $userids); // Remove ids that are not in our request.
                foreach ($ids as $id) {
                    if (in_array($id, $sent)) {
                        // Do not resend more than one email.
                        continue;
                    }
                    $sent[] = $id;

                    // Grab the user data.
                    $user = get_userdata($id);
                    // Generate confirmation URL.
                    $macros['[confirmurl]'] = get_bloginfo('url') . '/index.php?wlmconfirm=' . $id . '/' . md5($user->user_email . '__' . $user->user_login . '__' . $level . '__' . $api_key);

                    // Send the email template.
                    $this->email_template_level = $level;
                    $this->send_email_template('email_confirmation', $id, $macros);
                }
            }
        }

        /**
         * Action handler to resend incomplete registration email from the Members admin area
         *
         * @since 3.6
         * @param array $data
         */
        public function resend_incomplete_registration_email($data)
        {
            // Numbers and comma only, then explode to array.
            $userids = explode(',', preg_replace('/[^\d,]/', '', $data['userids']));

            // Get all incomplete registrations filter by our user ids.
            $incompletes = $this->get_incomplete_registrations($userids);
            foreach ($incompletes as $uid => $incomplete) {
                // Generate specific to this template macros.
                $macros = [
                    // Incomplete registration url.
                    '[incregurl]'   => $this->get_continue_registration_url($incomplete['email']),
                    // Membership level.
                    '[memberlevel]' => ( new \WishListMember\Level($incomplete['wlm_incregnotification']['level']) )->name,
                ];

                // Send the email template.
                $this->email_template_level = $incomplete['wlm_incregnotification']['level'];
                $this->send_email_template('incomplete_registration', $uid, $macros);
            }
        }
    }
}
