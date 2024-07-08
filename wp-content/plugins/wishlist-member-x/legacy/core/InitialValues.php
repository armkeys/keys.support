<?php

/*
 * Initial data saved to database when WishList Member is first activated
 * Makes it easier to manage this
 * by Mike Lopez
 */

$wishlist_member_initial_data = [
    'dashboard_checklist_archived_closed'            => 'closed',
    'onetime_login_link_label'                       => __('Send me a One-Time Login Link', 'wishlist-member'),
    // Email notification toggles.
    'expiring_notification_admin'                    => 0,
    'expiring_notification'                          => 1,
    'requireemailconfirmation_notification'          => 1,
    'require_admin_approval_free_notification_admin' => 1,
    'require_admin_approval_free_notification_user1' => 1,
    'require_admin_approval_free_notification_user2' => 1,
    'require_admin_approval_paid_notification_admin' => 1,
    'require_admin_approval_paid_notification_user1' => 1,
    'require_admin_approval_paid_notification_user2' => 1,
    'notify_admin_of_newuser'                        => 1,
    'newuser_notification_user'                      => 1,
    'incomplete_notification'                        => 1,
    'cancel_notification'                            => 0,
    'uncancel_notification'                          => 0,

    'recaptcha_public_key'                           => '',
    'recaptcha_private_key'                          => '',
    'menu_on_top'                                    => 1,
    'auto_insert_more_at'                            => 50,
    'login_limit'                                    => 7,
    'login_limit_error'                              => __('<b>Error:</b> You have reached your daily login limit.', 'wishlist-member'),
    'min_passlength'                                 => 8,

    'privacy_require_tos_on_registration'            => 0,
    'privacy_require_tos_checkbox_text'              => 'By checking this box you confirm that you have read and agree to the Terms of Service.',
    'privacy_require_tos_error_message'              => 'In order to register for this site you must agree to the Terms of Service by checking the box next to the Terms of Service agreement.',
    'privacy_enable_consent_to_market'               => 0,
    'privacy_consent_to_market_text'                 => 'By checking this box you agree to receive additional information regarding our products/services, events, news and offers.',
    'privacy_consent_affects_emailbroadcast'         => 1,
    'privacy_consent_affects_autoresponder'          => 1,
    'privacy_display_tos_on_footer'                  => 0,
    'privacy_display_pp_on_footer'                   => 0,
    'privacy_email_template_request_subject'         => 'Confirm your request to [request]',
    'privacy_email_template_request'                 => '<p>Hi [firstname]</p><p>A request has been made to perform the following action on your account at [sitename] ([siteurl])</p><p>[request]</p><p>To confirm this, please click on the following link:<br>[confirm_url]</p><p>You can safely ignore and delete this email if you do not want to take this action.</p><p>This email has been sent to [email]</p><p>Thank you.</p>',

    'privacy_email_template_download_subject'        => 'Personal Data Export',
    'privacy_email_template_download'                => '<p>Hi [firstname]</p><p>Your request for an export of personal data has been completed. You may download your personal data by clicking on the link below. For privacy and security, we will automatically delete the file on [expiration], so please download it before then.</p><p>[link]</p><p>This email has been sent to [email].</p><p>Thank you.</p>',

    'privacy_email_template_delete_subject'          => 'Erasure Request Fulfilled',
    'privacy_email_template_delete'                  => '<p>Hi [firstname]</p><p>Your request to erase your personal data on [sitename] has been completed.</p><p>If you have any follow-up questions or concerns, please contact the site administrator at [siteurl]</p><p>Thank you.</p>',

    'member_unsub_notification'                      => 1,
    'member_unsub_notification_subject'              => '[sitename] - Unsubscribed From Email Broadcast',
    'member_unsub_notification_body'                 => '<p>You have been unsubscribed from the Email Broadcasts.</p><p>You may use the link below if you would like to subscribe again.</p><p>[resubscribeurl]</p>',

    'show_wp_admin_bar'                              => 1,
    'rss_hide_protected'                             => 1,
    'wpm_levels'                                     => [],
    'pending_period'                                 => '',
    'rss_secret_key'                                 => md5(microtime()),
    'disable_rss_enclosures'                         => 1,
    'auto_login_after_confirm'                       => 1,
    'reg_cookie_timeout'                             => 600,
    // 'admin_approval_shoppingcart_reg'            => 0,
    'payperpost_ismember'                            => '0',
    'protect_after_more'                             => '0',
    'auto_insert_more'                               => '0',
    'private_tag_protect_msg'                        => __('<i>[Content protected for [level] members only]</i>', 'wishlist-member'),
    'reverse_private_tag_protect_msg'                => __('<i>[Content not available for [level] members ]</i>', 'wishlist-member'),
    'members_can_update_info'                        => '1',
    'unsub_notification'                             => '1',
    'html_tags_support'                              => '0',
    'incomplete_notification_first'                  => '1',
    'incomplete_notification_add'                    => '3',
    'incomplete_notification_add_every'              => '24',
    'expiring_notification_days'                     => '3',
    'show_linkback'                                  => '0',
    'unsubscribe_expired_members'                    => '0',
    'dont_send_reminder_email_when_unsubscribed'     => '0',
    'redirect_existing_member'                       => '0',
    'prevent_ppp_deletion'                           => '1',
    'password_hinting'                               => '0',
    'enable_short_registration_links'                => '0',
    'enable_login_redirect_override'                 => '1',
    'enable_logout_redirect_override'                => '1',
    'login_limit_notify'                             => '1',
    'enable_retrieve_password_override'              => 0,
    'password_hinting'                               => 0,
    'strongpassword'                                 => 0,
    'disable_legacy_reg_shortcodes'                  => '0',
    'disable_legacy_private_tags'                    => '0',
    'email_per_hour'                                 => WLM_DEFAULT_EMAIL_PER_HOUR,
    'email_per_minute'                               => WLM_DEFAULT_EMAIL_PER_MINUTE,
    'WLM_ContentDrip_Option'                         => '',
    'file_protection_ignore'                         => 'jpg, jpeg, png, gif, bmp, css, js',
    'mask_passwords_in_emails'                       => '1',
    // Email confirmation.
    'email_conf_send_after'                          => '1',
    'email_conf_how_many'                            => '3',
    'email_conf_send_every'                          => '24',
    // Welcome email.
    'register_email_subject'                         => __('Congrats - You are registered!', 'wishlist-member'),
    'register_email_body'                            => __('<p>[firstname],</p><p>You have successfully registered as a [memberlevel] member.</p><p>Please keep this information safe as it contains your username and password.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>You are invited to login and check things out.</p><p>We hope to see you inside.</p>', 'wishlist-member'),
    // Lost information email.
    'lostinfo_email_subject'                         => __('Your membership password reset request', 'wishlist-member'),
    'lostinfo_email_message'                         => __('<p>Dear [firstname],</p><p>Our records show that you recently asked to reset the password for your account.</p><p>Your current information is:<br>Username: [username]<br>Membership: [memberlevel]</p><p>As a security measure all passwords are encrypted in our database and cannot be retrieved. However, you can easily reset it.</p><p>To reset your password visit the following URL, otherwise just ignore this email and your membership info will remain the same.</p><p>[reseturl]</p><p>Thanks again!</p>', 'wishlist-member'),
    // Confirmation email.
    'confirm_email_subject'                          => __('Please confirm your registration', 'wishlist-member'),
    'confirm_email_message'                          => __('<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be confirmed before it is active.</p><p>Confirm by visiting the link below:</p><p>[confirmurl]</p><p>Once your account is confirmed you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>** These login details will only give you proper access after the registration has been confirmed.</p><p>Thank You.</p>', 'wishlist-member'),
    // Confirmation reminder email.
    'email_confirmation_reminder_subject'            => __('Reminder - Please confirm your registration', 'wishlist-member'),
    'email_confirmation_reminder_message'            => __('<p>Hi [firstname]</p><p>This is a reminder that your registration for [memberlevel] requires confirmation before it is active.</p><p>You can confirm by using the link below:</p><p>[confirmurl]</p><p>Once your account is confirmed, you can login using the following link.</p><p>Login URL: [loginurl]</p><p>Thank You.</p>', 'wishlist-member'),
    // Email confirmed notification.
    'email_confirmed_subject'                        => __('Registration confirmed', 'wishlist-member'),
    'email_confirmed_message'                        => __('<p>Hi [firstname]</p><p>Your registration for [memberlevel] is confirmed.</p><p>Thank You.</p>', 'wishlist-member'),
    // Registration require admin approval email.
    'requireadminapproval_email_subject'             => __('Registration requires admin approval', 'wishlist-member'),
    'requireadminapproval_email_message'             => __('<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be approved first by the admin before your status can be active.</p><p>Once your account is approved you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>These login details will only give you proper access when the admin has approved your registration.</p><p>Thank You.</p>', 'wishlist-member'),
    // Registration admin approved email.
    'registrationadminapproval_email_subject'        => __('Registration admin approval', 'wishlist-member'),
    'registrationadminapproval_email_message'        => __('<p>Hi [firstname]</p><p>Your registration is now approved by the admin.</p><p>Please use the login details were sent in your initial registration email.</p><p>Thank You.</p>', 'wishlist-member'),
    // Registration required admin approval email sent to admin.
    'requireadminapproval_admin_subject'             => __('A New Member Requires Approval', 'wishlist-member'),
    'requireadminapproval_admin_message'             => __('<p>Approval is required for a new member with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]</p><p>Username: [username]<br>Membership Level: [memberlevel]</p><p>Thank you.</p>', 'wishlist-member'),
    // Registration require admin approval email (paid)
    'requireadminapproval_email_paid_subject'        => __('Registration requires admin approval', 'wishlist-member'),
    'requireadminapproval_email_paid_message'        => __('<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be approved first by the admin before your status can be active.</p><p>Once your account is approved you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>These login details will only give you proper access when the admin has approved your registration.</p><p>Thank You.</p>', 'wishlist-member'),
    // Registration admin approved email (paid)
    'registrationadminapproval_email_paid_subject'   => __('Registration admin approval', 'wishlist-member'),
    'registrationadminapproval_email_paid_message'   => __('<p>Hi [firstname]</p><p>Your registration is now approved by the admin.</p><p>Please use the login details were sent in your initial registration email.</p><p>Thank You.</p>', 'wishlist-member'),
    // Registration required admin approval email sent to admin (paid)
    'requireadminapproval_admin_paid_subject'        => __('A New Member Requires Approval', 'wishlist-member'),
    'requireadminapproval_admin_paid_message'        => __('<p>Approval is required for a new member with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]</p><p>Username: [username]<br>Membership Level: [memberlevel]</p><p>Thank you.</p>', 'wishlist-member'),
    // New member notification sent to admin.
    'newmembernotice_email_subject'                  => __('A New Member has Registered', 'wishlist-member'),
    'newmembernotice_email_message'                  => __('<p>A new member has registered with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]<br>Membership Level: [memberlevel]<br>Username: [username]</p><p>Thank you.</p>', 'wishlist-member'),
    // A member unsubscribe notification sent to admin.
    'unsubscribe_notice_email_subject'               => __('Member has Unsubscribed', 'wishlist-member'),
    'unsubscribe_notice_email_message'               => __('<p>A member has unsubscribed with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]<br>Username: [username]</p><p>Thank you.</p>', 'wishlist-member'),
    // A member unsubscribe notification sent to user.
    'incnotification_email_subject'                  => __('Please Complete Your Registration', 'wishlist-member'),
    'incnotification_email_message'                  => __('<p>Hi,</p><p>Thank you for registering for [memberlevel]</p><p>Complete your registration by visiting the link below:</p><p>[incregurl]</p><p>Thank you.</p>', 'wishlist-member'),
    // Expiring member email notification sent to user.
    'expiringnotification_email_subject'             => __('Expiring Membership Subscription Reminder', 'wishlist-member'),
    'expiringnotification_email_message'             => __('<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] is about to expire on [expirydate].</p><p>Thank you.</p>', 'wishlist-member'),
    // Expiring member email notification sent to admin.
    'expiring_admin_subject'                         => __('[memberlevel]: Upcoming Member Expiration', 'wishlist-member'),
    'expiring_admin_message'                         => __('<p>There is an upcoming member expiration with the following information:</p><p>Membership Level: [memberlevel]<br>Expiration: [expirydate]</p><p>Username: [username]<br>Name: [firstname] [lastname]<br>Email: [email]</p><p>Login URL: [loginurl]</p>', 'wishlist-member'),
    // Cancel email notification.
    'cancel_email_subject'                           => __('[memberlevel] Cancelled', 'wishlist-member'),
    'cancel_email_message'                           => __('<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] has been cancelled.</p><p>Thank you.</p>', 'wishlist-member'),
    // Uncancel email notification.
    'uncancel_email_subject'                         => __('[memberlevel] Uncancelled', 'wishlist-member'),
    'uncancel_email_message'                         => __('<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] has been uncancelled.</p><p>Thank you.</p>', 'wishlist-member'),
    // One-time login link email.
    'onetime_login_link_email_subject'               => __('Your One-Time Login Link', 'wishlist-member'),
    'onetime_login_link_email_message'               => __('<p>Hi [firstname],</p><p>Click the one-time login link below in order to login.</p><p>This link can only be used once.</p><p>[one_time_login_link redirect=""]</p><p>Thank you.</p>', 'wishlist-member'),
    // Password hint email notification sent.
    'password_hint_email_subject'                    => __('Your Password Hint', 'wishlist-member'),
    'password_hint_email_message'                    => __('<p>Hi [firstname] [lastname],</p><p>Your Password Hint is:</p><p>[passwordhint]</p><p>Click the link below to login<br>[loginurl]</p><p>Thank you.</p>', 'wishlist-member'),
    // Registration Instructions (New Members)
    'reg_instructions_new'                           => __(
        '<p>To complete your registration, please select one of the two options:</p>
<ol>
<li>Existing members, please <a href="[existinglink]">click here</a>.</li>
<li>New members, please fill in the form below to complete<br />your <b>[level]</b> application.</li>
</ol>',
        'wishlist-member'
    ),
    // Registration Instructions with Existing Link disabled (New Members)
    'reg_instructions_new_noexisting'                => __('<p>Please fill in the form below to complete your <b>[level]</b> registration.</p>', 'wishlist-member'),
    // Registration Instructions for Existing Members.
    'reg_instructions_existing'                      => __(
        '<p>To complete your registration, please select one of the two options:</p>
<ol>
<li>New members, please <a href="[newlink]">click here</a>.</li>
<li>Existing members, please fill in the form below to complete<br />your <b>[level]</b> application.</li>
</ol>',
        'wishlist-member'
    ),
    // Sidebar Widget CSS.
    'sidebar_widget_css'                             => '/* The Main Widget Enclosure */
.WishListMember_Widget{ }',
    // Login Merge Code CSS Enclosure.
    'login_mergecode_css'                            => '/* The Main Login Merge Code Enclosure */
.WishListMember_LoginMergeCode{ }',
    // Registration Form CSS.
    'reg_form_css'                                   => '/* CSS Code for the Registration Form */

/* The Main Registration Form Table */
.wpm_registration{
	clear:both;
	padding:0;
	margin:10px 0;
}
.wpm_registration td{
	text-align:left;
}
/*CSS for Existing Members Login Table*/
.wpm_existing{
	clear:both;
	padding:0;
	margin:10px 0;
}
/* CSS for Registration Error Messages */
p.wpm_err{
	color:#f00;
	font-weight:bold;
}

/* CSS for custom message sent to registration url */
p.wlm_reg_msg_external {
	border: 2px dotted #aaaaaa;
	padding: 10px;
	background: #fff;
	color: #000;
}

/* CSS Code for the Registration Instructions Box */

/* The Main Instructions Box */
div#wlmreginstructions{
	background:#ffffdd;
	border:1px solid #ff0000;
	padding:0 1em 1em 1em;
	margin:0 auto 1em auto;
	font-size:1em;
	width:450px;
	color:#333333;
}

/* Links displayed in the Instructions Box */
#wlmreginstructions a{
	color:#0000ff;
	text-decoration:underline;
}

/* Numbered Bullets in the Instructions Box */
#wlmreginstructions ol{
	margin:0 0 0 1em;
	padding:0 0 0 1em;
	list-style:decimal;
	background:none;
}

/* Each Bullet Entry */
#wlmreginstructions li{
	margin:0;
	padding:0;
	background:none;
}',
    'closed_comments_msg'                            => __('You are not allowed to view comments on this post.', 'wishlist-member'),

    'ActiveShoppingCarts'                            => [],
    'active_email_integrations'                      => [],
    'active_other_integrations'                      => [],
    'paypalec_spb'                                   => [
        'layout'  => 'vertical',
        'size'    => 'medium',
        'shape'   => 'pill',
        'color'   => 'gold',
        'funding' => ['CARD', 'CREDIT'],
    ],
    'login_styling_custom_template'                  => 'default',
];
