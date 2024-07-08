<?php

/**
 * Email Broadcast Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Email Broadcast Methods trait
 */
trait Email_Broadcast_Methods
{
    use Email_Broadcast_Methods_Deprecated;

    /**
     * Email Broadcast Routine
     */
    public function email_broadcast()
    {
        global $wpdb;
        $post_data = wlm_post_data(true);

        // Save Email Broadcast.
        if ('Save' === wlm_arrval($post_data, 'EmailBroadcastAction')) {
            $save         = true;
            $subject      = stripslashes((string) wlm_arrval($post_data, 'subject'));
            $msg          = wlm_trim(wlm_arrval($post_data, 'message'));
            $sent_as      = wlm_trim(wlm_arrval($post_data, 'sent_as'));
            $send_to      = wlm_arrval($post_data, 'send_to');
            $otheroptions = isset($post_data['otheroptions']) ? (array) $post_data['otheroptions'] : [];
            $otheroptions = implode('#', $otheroptions);
            $mlevel       = [];
            $error        = 'none';

            if ('send_mlevels' === $send_to) {
                $mlevel = (array) $post_data['send_mlevels'];
            } elseif ('send_search' === $send_to) {
                $mlevel = (array) $post_data['save_searches'];
            } else {
                $save  = false;
                $error = __('Invalid Levels: Neither Levels or Save Searches was given', 'wishlist-member');
            }

            $mlevel = implode('#', $mlevel);

            $signature = wlm_trim(wlm_arrval($post_data, 'signature'));
            // Save the signature and can spam address info.
            $broadcast = [];
            foreach ((array) $post_data as $k => $v) {
                if ('canspam' === substr($k, 0, 7)) {
                    $broadcast[ $k ] = $v;
                }
            }
            $broadcast['signature'] = $signature;
            $this->save_option('broadcast', $broadcast);

            // Create can spam.
            $canspamaddress = wlm_trim(wlm_arrval($post_data, 'canspamaddr1')) . "\n";
            if (wlm_trim(wlm_arrval($post_data, 'canspamaddr2'))) {
                $canspamaddress .= wlm_trim(wlm_arrval($post_data, 'canspamaddr2')) . "\n";
            }
            $canspamaddress .= wlm_trim(wlm_arrval($post_data, 'canspamcity')) . ', ';
            $canspamaddress .= wlm_trim(wlm_arrval($post_data, 'canspamstate')) . "\n";
            $canspamaddress .= wlm_trim(wlm_arrval($post_data, 'canspamzip')) . "\n";
            $canspamaddress .= wlm_trim(wlm_arrval($post_data, 'canspamcountry'));

            $footer = "\n\n" . $signature . "\n\n" . $canspamaddress;

            if ($save) {
                $record_id = $this->save_email_broadcast($subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions);
                if (! $record_id) {
                    $error = __('An error occured while saving the broadcast.', 'wishlist-member') . $wpdb->last_error;
                }
            } else {
                $record_id = $save;
            }

            $log = '#NEW EMAIL BROADCAST#=> ' . $record_id . " #Error: {$error}";
            $ret = $this->log_email_broadcast_activity($log);

            $post_data['broadcast_id']    = $record_id;
            $post_data['broadcast_error'] = $error;
        } elseif ('ProcessBroadcast' === wlm_arrval($post_data, 'EmailBroadcastAction')) {
            $broadcast_id   = wlm_arrval($post_data, 'BroadcastID');
            $emailbroadcast = $this->get_email_broadcast($broadcast_id);
            if (! $emailbroadcast) {
                exit(0);
            }

            ignore_user_abort(true);
            wp_raise_memory_limit('email_broadcast');
            wlm_set_time_limit(86400); // Limit this script to run for 1 day only, I think its enough.
            $mlevel       = explode('#', $emailbroadcast->mlevel);
            $otheroptions = explode('#', $emailbroadcast->otheroptions);
            $recipients   = [];
            if ('send_mlevels' === $emailbroadcast->send_to) {
                $include_pending   = in_array('p', $otheroptions, true);
                $include_cancelled = in_array('c', $otheroptions, true);

                $members        = $this->member_ids(null, true);
                $cancelled      = $this->cancelled_member_ids(null, true);
                $pending        = $this->for_approval_member_ids(null, true);
                $expiredmembers = $this->expired_members_id();

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
                if ($this->add_email_broadcast_queue($broadcast_id, $id)) {
                    ++$total_queued;
                }
            }
            $data = [
                'status'       => 'Queued',
                'total_queued' => $total_queued,
            ];
            $this->update_email_broadcast($broadcast_id, $data);
            echo (int) $total_queued;
            exit(0);
        } elseif ('GetEmailQueue' === wlm_arrval($post_data, 'EmailBroadcastAction')) {
            $email_queue = $this->get_email_broadcast_queue(null, false, false, 0);
            $data        = [];
            foreach ($email_queue as $e) {
                $data[] = $e->id;
            }
            echo json_encode($data);
            exit(0);
        } elseif ('GetFailedEmails' === wlm_arrval($post_data, 'EmailBroadcastAction')) {
            $broadcastid = wlm_arrval($post_data, 'BroadcastID');
            $broadcastid = $broadcastid ? $broadcastid : 0;

            $email_queue = $this->get_failed_queue($broadcastid);
            $data        = [];
            foreach ($email_queue as $e) {
                $data[ $e->id ] = $e->user_email;
            }
            echo json_encode($data);
            exit(0);
        } elseif ('RequeueFailedEmails' === wlm_arrval($post_data, 'EmailBroadcastAction')) {
            $failedids = wlm_arrval($post_data, 'FailedIDs');
            $failedids = $failedids ? explode(',', $failedids) : [];
            if (count($failedids) > 0) {
                if (! $this->fail_email_broadcast_queue($failedids, 0)) {
                    echo 'A database error occured while queueing the selected emails. Please try again.';
                }
            } else {
                echo 'No emails to queue. Please try again.';
            }
            exit(0);
        } elseif ('SendEmailQueue' === wlm_arrval($post_data, 'EmailBroadcastAction')) {
            echo json_encode($this->send_email_queue(wlm_arrval($post_data, 'QueueID')));
            exit(0);
        }
    }

    /**
     * Send email queue
     *
     * @param  integer $id Broadcast ID.
     * @return boolean
     */
    public function send_email_queue($id)
    {
        $mail   = $this->get_email_queue($id);
        $mailed = false;
        if ($mail) {
            $user = $this->get_user_data($mail->userid);
            if ($user) { // If user exists.
                $subject = $mail->subject;
                $message = $mail->text_body;
                $footer  = $mail->footer;
                $sent_as = $mail->sent_as;

                $footer = @unserialize($footer);
                if (is_array($footer)) {
                    $footer_array = [];
                    if (isset($footer['signature'])) {
                        $footer_array[] = $footer['signature'];
                    }
                    $footer_array[] = sprintf(WLMCANSPAM, $user->ID . '/' . substr(md5($user->ID . AUTH_SALT), 0, 10));
                    if (isset($footer['address'])) {
                        $footer_array[] = $footer['address'];
                    }
                    $footer = "\n\n" . implode("\n\n", $footer_array);
                } else {
                    // Add unsubcribe and user details link.
                    $footer = $mail->footer . "\n\n" . sprintf(WLMCANSPAM, $user->ID . '/' . substr(md5($user->ID . AUTH_SALT), 0, 10));
                }

                // Process shortcodes.
                $shortcode_data = $this->wlmshortcode->manual_process($user->ID, $message, true);
                // Lets make sure that it is an array.
                if (! is_array($shortcode_data)) {
                    $shortcode_data = [];
                }
                // Strip tags for membership levels.
                if ($shortcode_data['wlm_memberlevel']) {
                    $shortcode_data['wlm_memberlevel'] = strip_tags($shortcode_data['wlm_memberlevel']);
                }
                if ($shortcode_data['wlmmemberlevel']) {
                    $shortcode_data['wlmmemberlevel'] = strip_tags($shortcode_data['wlmmemberlevel']);
                }
                if ($shortcode_data['memberlevel']) {
                    $shortcode_data['memberlevel'] = strip_tags($shortcode_data['memberlevel']);
                }

                if (! empty(wlm_trim($user->user_email))) {
                    if ('html' === $sent_as) {
                        $fullmsg = $message . nl2br($footer);
                        $mailed  = $this->send_html_mail($user->user_email, $subject, stripslashes($fullmsg), $shortcode_data, false, null, 'UTF-8');
                    } else {
                        $fullmsg = $message . $footer;
                        $mailed  = $this->send_plaintext_mail($user->user_email, $subject, stripslashes($fullmsg), $shortcode_data, false, null, 'UTF-8');
                    }
                } else {
                    // This will skip users with no email address and remove them from queue.
                    $mailed = true;
                }
            }
            // Update total count of emails processed.
            if ($mailed) { // If sent.
                $this->delete_email_broadcast_queue($mail->id);
            } else { // If failed.
                $this->fail_email_broadcast_queue($mail->id);
            }

            // Save last send date.
            $date_sent = wlm_date('F j, Y, h:i:s A', current_time('timestamp'));
            $this->save_option('WLM_Last_Queue_Sent', $date_sent);
        }
        return $mailed;
    }

    /**
     * Requeue broadcast email
     *
     * @param  integer $broadcast_id Broadcast ID
     * @return integer|boolean
     */
    public function requeue_email($broadcastid)
    {
        return $this->email_broadcast->requeue_email($broadcastid);
    }

    /**
     * Fail/Unfail Email Broadcast Queue
     *
     * @param  array  $ids   Array of queue IDs.
     * @param  string $value Value.
     * @return boolean
     */
    public function fail_email_broadcast_queue($ids, $value = 1)
    {
        return $this->email_broadcast->fail_email_queue($ids, $value);
    }

    /**
     * Delete Email Broadcast Queue
     *
     * @param  array $ids Array of queue IDs.
     * @return boolean
     */
    public function delete_email_broadcast_queue($ids)
    {
        return $this->email_broadcast->delete_email_queue($ids);
    }

    /**
     * Delete Email Broadcast Queue
     *
     * @param  integer $broadcast_id Broadcast ID
     * @return boolean
     */
    public function purge_broadcast_queue($broadcast_id)
    {
        return $this->email_broadcast->purge_broadcast_queues($broadcast_id);
    }

    /**
     * Get Failed Email Queue
     *
     * @param  integer $broadcast_id Broadcast ID.
     * @param  boolean $count        True to only count. Default false.
     * @return integer|array
     */
    public function get_failed_queue($broadcast_id = null, $count = false)
    {
        if ($count) {
            return $this->email_broadcast->count_failed_queue($broadcast_id);
        } else {
            return $this->email_broadcast->get_failed_queue($broadcast_id);
        }
    }

    /**
     * Get Email Queue
     *
     * @param  integer $broadcast_id   Broadcast ID.
     * @param  boolean $include_fail   Include failed.
     * @param  boolean $include_paused Include paused.
     * @param  integer $limit          Limit.
     * @param  boolean $count          True to count only. Default false.
     * @return integer|array
     */
    public function get_email_broadcast_queue($broadcast_id = null, $include_fail = false, $include_paused = false, $limit = 0, $count = false)
    {
        if ($count) {
            return $this->email_broadcast->count_email_queue($broadcast_id, $include_fail, $include_paused);
        } else {
            return $this->email_broadcast->get_email_queue($broadcast_id, $include_fail, $include_paused, $limit);
        }
    }

    /**
     * Get Email Queue by ID
     *
     * @param  integer $id ID.
     * @return array
     */
    public function get_email_queue($id)
    {
        return $this->email_broadcast->get_email_queue_by_id($id);
    }

    /**
     * Add To Email Queue
     *
     * @param  integer $broadcast_id Broadcast ID.
     * @param  integer $user_id      User ID.
     * @return boolean
     */
    public function add_email_broadcast_queue($broadcast_id, $user_id)
    {
        return $this->email_broadcast->add_email_queue($broadcast_id, $user_id);
    }

    /**
     * Get Email Broadcast
     *
     * @param integer $broadcast_id Broadcast ID.
     * @param array   $data         Broadcast data.
     */
    public function update_email_broadcast($broadcast_id, $data)
    {
        return $this->email_broadcast->update_broadcast($broadcast_id, $data);
    }

    /**
     * Get Email Broadcast
     *
     * @param integer $broadcast_id Broadcast ID.
     */
    public function get_email_broadcast($broadcast_id)
    {
        return $this->email_broadcast->get_broadcast($broadcast_id);
    }

    /**
     * Get All Email Broadcast
     *
     * @param boolean $count    True to count only. Default false.
     * @param string  $start    Starting row.
     * @param string  $per_page Rows per page.
     * @param string  $order    Order.
     */
    public function get_all_email_broadcast($count = false, $start = '', $per_page = '', $order = '')
    {
        if (! $count) {
            return $this->email_broadcast->get_all_broadcast($start, $per_page, $order);
        } else {
            return $this->email_broadcast->count_broadcast();
        }
    }

    /**
     * Save Email Broadcast
     *
     * @param  string $subject      Subject.
     * @param  string $msg          Message.
     * @param  string $footer       Footer.
     * @param  string $send_to      Recipient.
     * @param  string $mlevel       Membership Level.
     * @param  string $sent_as      Sent as.
     * @param  string $otheroptions Other options.
     * @param  string $from_name    From name.
     * @param  string $from_email   From email.
     * @return integer|boolean
     */
    public function save_email_broadcast($subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions, $from_name = '', $from_email = '')
    {
        return $this->email_broadcast->save_broadcast($subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions, $from_name, $from_email);
    }

    /**
     * Delete Email Broadcasts
     *
     * @param  array $broadcast_ids Array of broadcast IDs.
     * @return boolean
     */
    public function delete_email_broadcast($broadcast_ids)
    {
        return $this->email_broadcast->delete_broadcast($broadcast_ids);
    }

    /**
     * Check if email broadcast stats missing
     *
     * @return boolean
     */
    public function is_email_broadcast_missing_stats()
    {
        return $this->email_broadcast->check_stats_missing();
    }

    /**
     * Sync email broadcast stats
     */
    public function email_broadcast_sync_stat()
    {
        $res = $this->email_broadcast->get_unsync_broadcast();
        if ($res) {
            foreach ($res as $eb) {
                // Update total queue.
                $r_count = 0;
                if (! empty($eb->recipients) && $eb->total_queued <= 0) {
                    $recipients = explode(',', $eb->recipients);
                    if (is_array($recipients)) {
                        $r_count = count($recipients);
                    }
                }
                if ($r_count > 0) {
                    $this->email_broadcast->update_broadcast(
                        $eb->id,
                        [
                            'recipients'   => '',
                            'total_queued' => $r_count,
                        ]
                    );
                }

                $f_address = [];
                if (! is_null($eb->failed_address) && ! empty($eb->failed_address)) {
                    $f_address = explode(',', $eb->failed_address);
                }

                // Update the failed.
                $failed_insert = [];
                if (is_array($f_address)) {
                    foreach ($f_address as $email_add) {
                        $user = get_user_by('email', $email_add);
                        if ($user && isset($user->ID)) {
                                $failed_insert[] = [
                                    'broadcastid' => $eb->id,
                                    'userid'      => $user->ID,
                                    'failed'      => 1,
                                ];
                        }
                    }
                }

                if (count($failed_insert)) {
                    $ret = $this->email_broadcast->bulk_add_email_queue(['broadcastid', 'userid', 'failed'], $failed_insert);
                    if ($ret) {
                        $this->email_broadcast->update_broadcast($eb->id, ['failed_address' => '']);
                    }
                }
            }
        }
    }

    /**
     * Log Broadcast Email
     *
     * @param  string  $txt   Log message.
     * @param  boolean $clear True to clear. Default false.
     * @return boolean
     */
    public function log_email_broadcast_activity($txt, $clear = false)
    {
        if (1 == $this->get_option('WLM_BroadcastLog')) {
            $date      = wlm_date('F j, Y, h:i:s A');
            $logfolder = WLM_BACKUP_PATH;
            $logfile   = $logfolder . 'broadcast.txt';
            if (! file_exists($logfolder)) {
                @mkdir($logfolder, 0755, true);
            }
            if (! file_exists($logfile)) {
                return false;
            }
            if ($clear) {
                $logfilehandler = fopen($logfile, 'w');
            } else {
                $logfilehandler = fopen($logfile, 'a');
            }
            if (! $logfilehandler) {
                return false;
            }
            $log = '[' . $date . '] ' . $txt . "\n------------------------------------------------------------\n";
            fwrite($logfilehandler, $log);
            fclose($logfilehandler);
        }
        return true;
    }

    /**
     * Get IDs of unsubscribed users
     *
     * @return array of User IDs
     */
    public function get_unsubscribed_users()
    {
        global $wpdb;

        if ($this->get_option('privacy_enable_consent_to_market') && $this->get_option('privacy_consent_affects_emailbroadcast')) {
            return $wpdb->get_col('SELECT DISTINCT `user_id` FROM `' . esc_sql($this->table_names->user_options) . "` WHERE (`option_name` = 'wlm_unsubscribe' AND `option_value`='1')  OR (`option_name`='wlm_consent_to_market' AND `option_value`='0')");
        } else {
            return $wpdb->get_col('SELECT DISTINCT `user_id` FROM `' . esc_sql($this->table_names->user_options) . "` WHERE (`option_name` = 'wlm_unsubscribe' AND `option_value`='1')");
        }
    }

    /**
     * Send Unsubscribe notification to User if configured
     *
     * @param integer|WP_User $user User ID or User Object
     */
    public function send_unsubscribe_notification_to_user($user)
    {
        if (1 == $this->get_option('member_unsub_notification')) {
            $user = is_object($user) ? $user : $this->get_user_data($user);
            if ($user) {
                $resubscribe_url = get_bloginfo('url') . '/?wlmresub=' . $user->ID . '/' . substr(md5($user->ID . AUTH_SALT), 0, 10);
                $mergecodes      = [
                    '[sitename]'       => get_option('blogname'),
                    '[siteurl]'        => home_url(),
                    '[resubscribeurl]' => $resubscribe_url,
                ];
                $this->send_email_template('member_unsub_notification', $user->ID, $mergecodes, $user->user_email);
            }
        }
    }
    // -----------------------------------------
    // Init Hook.
    public function unsub_javascript()
    {
        echo '<script type="text/javascript">alert("';
        esc_html_e('You have been unsubscribed from our mailing list.', 'wishlist-member');
        echo '");</script>';
    }

    public function resub_javascript()
    {
        echo '<script type="text/javascript">alert("';
        esc_html_e('You have been resubscribed to our mailing list.', 'wishlist-member');
        echo '");</script>';
    }
}
