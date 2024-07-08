<?php

/**
 * Email Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Email Methods trait
*/
trait Email_Methods
{
    /**
     * Boolean flag to indicate if WishList Member is sending an email
     *
     * @var boolean
     */
    public $sending_mail = false;

    /**
     * Email sender name
     *
     * @var string
     */
    public $wlm_mail_from_name = '';

    /**
     * Email sender email address
     *
     * @var string
     */
    public $wlm_mail_from_email = '';

    /**
     * The current email template being sent
     *
     * @var string
     */
    public $email_template;

    /**
     * The user ID for the current email template being sent
     *
     * @var integer
     */
    public $email_template_user_id;

    /**
     * The level ID for the current email template being sent
     *
     * @var string
     */
    public $email_template_level;

    /**
     * Autoresponder sender details
     *
     * @var array
     */
    public $ar_sender;

    /**
     * Email subject
     *
     * @var string
     */
    public $mail_subject;

    /**
     * Send email immediately or queue it in database for sending later.
     * This function uses the WordPress wp_mail function to send the actual email.
     *
     * @param  string  $recipient    Email address of recipient.
     * @param  string  $subject      Email subject.
     * @param  string  $body         Body of email.
     * @param  array   $data         Associative array of merge codes.
     * @param  mixed   $queue        FALSE to send immediately or timestamp to queue.
     * @param  boolean $html         TRUE to send as HTML or FALSE to send as Plain Text.
     * @param  integer $record_id    id of the email queued.
     * @param  string  $charset      Character set to use.
     * @param  string  $sender_name  Sender name.
     * @param  string  $sender_email Sender Email.
     * @return boolean
     */
    public function send_mail($recipient, $subject, $body, $data, $queue = false, $html = false, $record_id = null, $charset = null, $sender_name = null, $sender_email = null)
    {
        // Always return true when trying to send to temp account email.
        if (preg_match('/temp_[a-f0-9]{32}/', $recipient)) {
            return true;
        }

        $this->sending_mail = true; // Tell our hook that it's WishList Member sending the mail.

        /*
         * $queue should be either a timestamp or FALSE.
         * If for some reason, we receive a value of TRUE
         * then we replace its value with the current time.
         */
        if (true === $queue) {
            $queue = time();
        }

        // We add loginurl to the merge codes.
        $data['loginurl'] = wp_login_url();

        // Html or plain text?
        // The merge codes.
        $search = array_keys((array) $data);
        foreach ((array) $search as $k => $v) {
            if ('[' === substr(wlm_trim($v), 0, 1) && ']' === substr(wlm_trim($v), -1)) {
                $search[ $k ] = $v;
            } else {
                $search[ $k ] = '[' . $v . ']';
            }
        }

        // Run merge codes on subject.
        $subject = str_replace($search, $data, $subject);
        // Run merge codes on body.
        $body = str_replace($search, $data, $body);

        $header = wlm_has_html($body) ? 'Content-Type: text/html' : 'Content-Type: text/plain';
        if ($header) {
            $header .= '; charset=" ' . strtolower($charset ? $charset : $this->blog_charset) . '"';
        }

        // Convert links to clickable links for HTML emails.
        if (wlm_has_html($body)) {
            // Replace http and https links with clickable links.
            $body = preg_replace('/(^|[^"\'])\b(https?:\/\/[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])/i', '\1<a href="\2" target="_blank">\2</a>', $body);
        }

        $mailed = false;
        // Queue or not?
        if ($queue) {
            // Queue...
            // Step 1 - Put all data in an array.
            $x = [$recipient, $subject, $body, $header];
            // Step 2 - Create the variable name.
            $name = $record_id . 'wlmember_email_queue_' . ( (string) $queue ) . '_' . md5(serialize($x));
            // Step 3 - Save it to wp_options.
            $mailed = add_option($name, $x, '', 'no');
        } else {
            // Send now...
            $tries = 3; // <- number of tries before we surrender.
            // Send the email.
            while ($tries-- && ! $mailed) {
                $this->wlm_mail_from_name  = $sender_name;
                $this->wlm_mail_from_email = $sender_email;
                $mailed                    = wp_mail($recipient, $subject, $body, $header);
                unset($this->wlm_mail_from_name);
                unset($this->wlm_mail_from_email);
            }
        }
        $this->sending_mail = false; // Done sending mail.

        return $mailed; // Return the result.
    }

    /**
     * Send Email as HTML
     *
     * @param  string  $recipient    Email address of recipient.
     * @param  string  $subject      Email subject.
     * @param  string  $body         Body of email.
     * @param  array   $data         Associative array of merge codes.
     * @param  mixed   $queue        FALSE to send immediately or timestamp to queue.
     * @param  integer $record_id    id of the email queued.
     * @param  string  $charset      Character set to use.
     * @param  string  $sender_name  Sender name.
     * @param  string  $sender_email Sender Email.
     * @return boolean
     */
    public function send_html_mail($recipient, $subject, $body, $data, $queue = false, $record_id = null, $charset = null, $sender_name = null, $sender_email = null)
    {
        return $this->send_mail($recipient, $subject, $body, $data, $queue, true, $record_id, $charset, $sender_name, $sender_email);
    }

    /**
     * Send Email as Plain Text
     *
     * @param  string  $recipient    Email address of recipient.
     * @param  string  $subject      Email subject.
     * @param  string  $body         Body of email.
     * @param  array   $data         Associative array of merge codes.
     * @param  mixed   $queue        FALSE to send immediately or timestamp to queue.
     * @param  integer $record_id    id of the email queued.
     * @param  string  $charset      Character set to use.
     * @param  string  $sender_name  Sender name.
     * @param  string  $sender_email Sender Email.
     * @return boolean
     */
    public function send_plaintext_mail($recipient, $subject, $body, $data, $queue = false, $record_id = null, $charset = null, $sender_name = null, $sender_email = null)
    {
        return $this->send_mail($recipient, $subject, $body, $data, $queue, false, $record_id, $charset, $sender_name, $sender_email);
    }

    /**
     * Sends email based on email template
     * - Automatically generates macros for user information
     * - Additional macros can be passed
     * - Accepted Email Templates:
     *
     * @param  string       $email_template  Email Template. Valid templates are:
     *                                      'require_admin_approval',
     *                                      'email_confirmation',
     *                                      'registration',
     *                                      'admin_new_member_notice',
     *                                      'admin_unsubscribe_notice',
     *                                      'registration_approved',
     *                                      'password_hint',
     *                                      'expiring_level',
     *                                      'incomplete_registration',
     *                                      'lost_password'.
     * @param  integer      $user_id         User ID.
     * @param  array        $more_macros     Additional macros to pass.
     * @param  string       $recipient_email Recipient's email address.
     * @param  boolean|null $html            True to force HTML.
     *                                      False to force Plain Text.
     *                                      NULL to auto-detect based on template.
     *                                      If no HTML is detected in the template
     *                                      then get value of 'html_tags_support'
     *                                      option from the database.
     * @param  boolean      $global_default  True to force global default template.
     * @return boolean
     */
    public function send_email_template($email_template, $user_id, $more_macros = [], $recipient_email = null, $html = null, $global_default = false)
    {
        static $accepted_templates = [
            'expiring_level'              => ['expiringnotification_email_subject', 'expiringnotification_email_message'],
            'require_admin_approval'      => ['requireadminapproval_email_subject', 'requireadminapproval_email_message'],
            'registration_approved'       => ['registrationadminapproval_email_subject', 'registrationadminapproval_email_message'],
            'email_confirmation_reminder' => ['email_confirmation_reminder_subject', 'emaiL_confirmation_reminder_message'],
            'email_confirmation'          => ['confirm_email_subject', 'confirm_email_message'],
            'email_confirmed'             => ['email_confirmed_subject', 'email_confirmed_message'],
            'registration'                => ['register_email_subject', 'register_email_body'],
            'admin_new_member_notice'     => ['newmembernotice_email_subject', 'newmembernotice_email_message'],
            'incomplete_registration'     => ['incnotification_email_subject', 'incnotification_email_message'],

            'admin_unsubscribe_notice'    => ['unsubscribe_notice_email_subject', 'unsubscribe_notice_email_message'],
            'password_hint'               => ['password_hint_email_subject', 'password_hint_email_message'],
            'lost_password'               => ['lostinfo_email_subject', 'lostinfo_email_message'],
            'member_unsub_notification'   => ['member_unsub_notification_subject', 'member_unsub_notification_body'],

            'membership_cancelled'        => ['cancel_email_subject', 'cancel_email_message'],
            'membership_uncancelled'      => ['uncancel_email_subject', 'uncancel_email_message'],

            'onetime_login_link'          => ['onetime_login_link_email_subject', 'onetime_login_link_email_message'],
        ];

        if (! $global_default) {
            $template = apply_filters('wishlistmember_pre_email_template', $email_template, $user_id);
            if (false === $template) {
                return false;
            }
        }

        if (is_array($template) && 2 === count($template)) {
            list($subject, $message) = $template;
        } else {
            if (! isset($accepted_templates[ $email_template ])) {
                return false;
            }
            $subject = $this->get_option($accepted_templates[ $email_template ][0]);
            $message = $this->get_option($accepted_templates[ $email_template ][1]);
        }

        // One-time login link.
        if (preg_match_all('#\[one_time_login_link\]|\[one_time_login_link redirect="([^"]*?)"\]#im', $message, $matches, PREG_SET_ORDER)) {
            $onetime_login_link = \WishListMember\User::generate_onetime_login_link($user_id);
            foreach ($matches as $match) {
                if (wlm_trim($match[1])) {
                    $more_macros[ $match[0] ] = add_query_arg('redirect', $match[1], $onetime_login_link);
                } else {
                    $more_macros[ $match[0] ] = $onetime_login_link;
                }
            }
        }

        $macros = $this->generate_email_macros($email_template, $user_id, $more_macros);

        $subject = str_replace(array_keys($macros), $macros, $subject);
        $message = str_replace(array_keys($macros), $macros, $message);

        if (is_null($html)) {
            if (preg_match('/<(p|div|br)\b[^>]*?>/i', $message)) {
                $html = true;
            } else {
                if ($this->get_option('html_tags_support')) {
                    $html    = true;
                    $message = nl2br($message);
                }
            }
        }

        if (is_null($recipient_email)) {
            $recipient_email = $macros['[email]'];
        }

        $recipient_email = apply_filters('wishlistmember_email_template_recipient', $recipient_email, $email_template, $user_id);

        $this->email_template         = $email_template;
        $this->email_template_user_id = $user_id;
        $this->send_mail($recipient_email, $subject, $message, [], false, (bool) $html);
        unset($this->email_template);
        unset($this->email_template_user_id);
        unset($this->email_template_level);
    }

    /**
     * Generate macros for email template
     *
     * @param  string  $email_template Email Template.
     * @param  integer $user_id        User ID.
     * @param  array   $more_macros    Optional associative array
     *                                 of additional macros.
     * @return array                    Associative array of macros
     */
    public function generate_email_macros($email_template, $user_id, $more_macros = [])
    {
        static $levels = null, $previous_user_id = null, $user_macros = [];

        if (! is_array($more_macros)) {
            $more_macros = [];
        }

        if (is_null($levels)) {
            $levels = $this->get_option('wpm_levels');
        }

        $user_id = (int) $user_id;
        if ($previous_user_id !== $user_id) {
            $previous_user_id = $user_id;
            $user             = $this->get_user_data($user_id);
            $current_user     = wp_get_current_user();

            if (! $user) {
                return false;
            }

            $user_macros['[firstname]'] = $user->first_name;
            $user_macros['[lastname]']  = $user->last_name;
            $user_macros['[username]']  = $user->user_login;
            $user_macros['[nickname]']  = $user->nickname;
            if ('incomplete_registration' === $email_template) {
                $user_macros['[email]'] = $user->wlm_origemail;
            } else {
                $user_macros['[email]'] = $user->user_email;
            }
            $user_macros['[loginurl]'] = wp_login_url();

            $custom_fields = (array) $this->get_user_custom_fields($user->ID);
            foreach ($custom_fields as $key => $value) {
                if (is_array($value)) {
                    $user_macros[ "[wlm_custom {$key}]" ] = implode("\n", $value);
                } else {
                    $user_macros[ "[wlm_custom {$key}]" ] = $value;
                }
            }
            $user_macros['[wlm_website]'] = $user->user_url;

            // Support for user address mergecodes.
            $user_macros['[wlm_company]']  = wlm_arrval($user->wpm_useraddress, 'company');
            $user_macros['[wlm_address1]'] = wlm_arrval($user->wpm_useraddress, 'address1');
            $user_macros['[wlm_address2]'] = wlm_arrval($user->wpm_useraddress, 'address2');
            $user_macros['[wlm_city]']     = wlm_arrval($user->wpm_useraddress, 'city');
            $user_macros['[wlm_state]']    = wlm_arrval($user->wpm_useraddress, 'state');
            $user_macros['[wlm_zip]']      = wlm_arrval($user->wpm_useraddress, 'zip');
            $user_macros['[wlm_country]']  = wlm_arrval($user->wpm_useraddress, 'country');
        }

        $macros = $user_macros + $more_macros;

        return $macros;
    }

    /**
     * Send admin approval email notification
     *
     * @param integer $user_id  User ID.
     * @param string  $level_id Level ID.
     */
    public function send_admin_approval_notification($user_id, $level_id)
    {
        $macros = [
            '[memberlevel]' => $this->get_membership_levels($user_id, true),
            '[password]'    => '********',
        ];
        wishlistmember_instance()->email_template_level = $level_id;
        $txnid  = $this->get_membership_levels_txn_id($user_id, $level_id);
        if (sprintf('WL-%s-%s', $user_id, $level_id) === $txnid) { // Free registration.
            $this->send_email_template('registration_approved', $user_id, $macros);
        } else { // Paid registration.
            $this->send_email_template('registration_approved_paid', $user_id, $macros);
        }
    }

    /**
     * Change wp_mail()'s sender address to our settings.
     * Overridden by the AR
     *
     * Called by 'wp_mail_from' and 'wp_mail_from_name' hooks.
     *
     * @param  string $c Sender address.
     * @return string
     */
    public function mail_from($c)
    {
        if (! isset($this->sending_mail)) {
            $this->sending_mail = false;
        }

        if (true !== $this->sending_mail) {
            return $c; // We don't change anything if mail's not being sent by WishList Member.
        }

        $current_action = current_action();
        $email_sender_  = 'wp_mail_from' === $current_action ? 'email_sender_address' : 'email_sender_name';
        $wlm_mail_from_ = 'wp_mail_from' === $current_action ? 'wlm_mail_from_email' : 'wlm_mail_from_name';

        if (isset($this->ar_sender)) {
            if (is_array($this->ar_sender)) {
                $x = $this->ar_sender['email'];
            } else {
                $x = $this->get_option($email_sender_);
            }
        } else {
            $x = wlm_arrval($this, $wlm_mail_from_) ? wlm_arrval('lastresult') : $this->get_option($email_sender_);
        }
        if (! $x) {
            $x = $c;
        }

        // Allow further filtering of sender email if sending a template.
        if (! empty($this->email_template) && ! empty($this->email_template_user_id)) {
            $x = apply_filters(
                'wp_mail_from' === $current_action ? 'wishlistmember_template_mail_from_email' : 'wishlistmember_template_mail_from_name',
                $x,
                $this->email_template,
                $this->email_template_user_id
            );
        }
        return $x;
    }

    /**
     * Set wp_mail() email subject.
     * Called by 'wp_mail' hook.
     *
     * @param  array $wp_mail Array of wp_mail() arguments.
     * @return array
     */
    public function mail_subject($wp_mail)
    {
        $this->mail_subject = wlm_trim(wlm_arrval($this, 'mail_subject'));
        if (! empty($this->mail_subject)) {
            $wp_mail['subject'] = $this->mail_subject;
        }
        $this->mail_subject = '';
        return $wp_mail;
    }

    /**
     * Send Queued Mail.
     * Called by 'wishlistmember_email_queue' hook via WP Cron
     *
     * @param integer $limit Number of items to process.
     */
    public function send_queued_mail($limit = null)
    {
        global $wpdb;

        // Is still sending? return.
        if (false !== get_transient('wlm_is_sending_broadcast')) {
            return false;
        }

        ignore_user_abort(true);
        wp_raise_memory_limit('send_queued_mail '); // Request for more memory.

        // Is $limit specified? if so, use it. if not, read from email_per_minute setting.
        if (is_null($limit)) {
            $limit = $this->get_option('email_per_minute');
        }
        $limit += 0;
        // No limit yet? let's set it to the default setting.
        if ($limit < 1) {
            $limit = WLM_DEFAULT_EMAIL_PER_MINUTE;
        }

        // Retrieve queued mails.
        $mails     = $this->get_email_broadcast_queue(null, false, false, $limit);
        $totalcnt  = 0;
        $failedcnt = 0;
        $mailcnt   = count($mails);
        $date_sent = '';

        if ($mailcnt > 0) {
            set_transient('wlm_is_sending_broadcast', 1, MINUTE_IN_SECONDS);
        } else {
            return false;
        }

        if ($mails) {
            // Go through and send the emails.
            foreach ((array) $mails as $mail) {
                $user   = $this->get_user_data($mail->userid);
                $mailed = false;
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
                        $shortcode_data['wlm_memberlevel'] = wp_strip_all_tags($shortcode_data['wlm_memberlevel']);
                    }
                    if ($shortcode_data['wlmmemberlevel']) {
                        $shortcode_data['wlmmemberlevel'] = wp_strip_all_tags($shortcode_data['wlmmemberlevel']);
                    }
                    if ($shortcode_data['memberlevel']) {
                        $shortcode_data['memberlevel'] = wp_strip_all_tags($shortcode_data['memberlevel']);
                    }

                    if ('html' === $sent_as) {
                        $fullmsg = $message . nl2br($footer);
                        $mailed  = $this->send_html_mail($user->user_email, $subject, stripslashes($fullmsg), $shortcode_data, false, null, 'UTF-8', $mail->from_name, $mail->from_email);
                    } else {
                        $fullmsg = $message . $footer;
                        $mailed  = $this->send_plaintext_mail($user->user_email, $subject, stripslashes($fullmsg), $shortcode_data, false, null, 'UTF-8', $mail->from_name, $mail->from_email);
                    }
                }

                // Update total count of emails processed.
                if ($mailed) { // If sent.
                    ++$totalcnt;
                    // Delete from the queue record.
                    $this->delete_email_broadcast_queue($mail->id);
                } else { // If failed.
                    ++$failedcnt;
                    // Update the queue record as failed.
                    $this->fail_email_broadcast_queue($mail->id);
                }
            }

            // Save last send date.
            $date_sent = wlm_date('F j, Y, h:i:s A');
            $this->save_option('WLM_Last_Queue_Sent', $date_sent);
        }

        $log = sprintf('#SENDING QUEUE#=> #Limit:%s #Query Count:%s #Sent:%s #Failed:%s #Last Queue Sent:%s', $limit, $mailcnt, $totalcnt, $failedcnt, $date_sent);
        $this->log_email_broadcast_activity($log);

        // Let delete the transient.
        delete_transient('wlm_is_sending_broadcast');

        // Let process her again.
        $url = get_home_url() . '?wlmprocessbroadcast=1';
        wp_remote_get(
            $url,
            [
                'timeout'  => 10,
                'blocking' => false,
            ]
        );

        return $totalcnt;
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_email_queue', [$wlm, 'send_queued_mail']);
        add_filter('wp_mail_from_name', [$wlm, 'mail_from'], 9999999);
        add_filter('wp_mail_from', [$wlm, 'mail_from'], 9999999);
        add_filter('wp_mail', [$wlm, 'mail_subject'], 9999999);
    }
);
