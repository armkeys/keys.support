<?php

/**
 * Default values for level email templates
 *
 * @package WishListMember/Helpers
 */

require $this->legacy_wlm_dir . '/core/InitialValues.php';

global $wpdb;
$x_level_email_defaults_keys = [
    'cancel_email_message',
    'cancel_email_subject',
    'cancel_notification',
    'confirm_email_message',
    'confirm_email_subject',
    'requireemailconfirmation_notification',
    'email_confirmation_reminder_subject',
    'email_confirmation_reminder_message',
    'email_confirmed',
    'email_confirmed_message',
    'email_confirmed_subject',
    'email_sender_address',
    'email_sender_name',
    'expiring_admin_message',
    'expiring_admin_subject',
    'expiring_notification_admin',
    'expiring_notification',
    'expiringnotification_email_message',
    'expiringnotification_email_subject',
    'incnotification_email_message',
    'incnotification_email_subject',
    'incomplete_notification',
    'newmembernotice_email_message',
    'newmembernotice_email_subject',
    'newmembernotice_email_recipient',
    'newuser_notification_user',
    'notify_admin_of_newuser',
    'register_email_body',
    'register_email_subject',
    'registrationadminapproval_email_message',
    'registrationadminapproval_email_paid_message',
    'registrationadminapproval_email_paid_subject',
    'registrationadminapproval_email_subject',
    'require_admin_approval_free_notification_admin',
    'require_admin_approval_free_notification_user1',
    'require_admin_approval_free_notification_user2',
    'require_admin_approval_paid_notification_admin',
    'require_admin_approval_paid_notification_user1',
    'require_admin_approval_paid_notification_user2',
    'requireadminapproval_admin_message',
    'requireadminapproval_admin_paid_message',
    'requireadminapproval_admin_paid_subject',
    'requireadminapproval_admin_subject',
    'requireadminapproval_email_message',
    'requireadminapproval_email_paid_message',
    'requireadminapproval_email_paid_subject',
    'requireadminapproval_email_subject',
    'uncancel_email_message',
    'uncancel_email_subject',
    'uncancel_notification',
];

$x_level_email_defaults = array_column(
    $wpdb->get_results(
        $wpdb->prepare(
            'SELECT `option_name`,`option_value` FROM `' . esc_sql($this->options_table) . '` WHERE `option_name` IN (' . implode(', ', array_fill(0, count($x_level_email_defaults_keys), '%s')) . ')',
            ...array_values($x_level_email_defaults_keys)
        ),
        ARRAY_N
    ),
    1, // array_column 2nd column as value.
    0  // array_column 1st column as key.
) + array_fill_keys($x_level_email_defaults_keys, null);

$use_saved = empty($pristine);

$level_email_defaults = [
    'require_email_confirmation_sender_name'           => $x_level_email_defaults['email_sender_name'],
    'require_email_confirmation_sender_email'          => $x_level_email_defaults['email_sender_address'],
    'require_email_confirmation_subject'               => $x_level_email_defaults['confirm_email_subject'] && $use_saved ? $x_level_email_defaults['confirm_email_subject'] : $wishlist_member_initial_data['confirm_email_subject'],
    'require_email_confirmation_message'               => $x_level_email_defaults['confirm_email_message'] && $use_saved ? $x_level_email_defaults['confirm_email_message'] : $wishlist_member_initial_data['confirm_email_message'],

    'require_email_confirmation_reminder'              => $x_level_email_defaults['requireemailconfirmation_notification'],
    'require_email_confirmation_start'                 => $wishlist_member_initial_data['email_conf_send_after'],
    'require_email_confirmation_start_type'            => '',
    'require_email_confirmation_send_every'            => $wishlist_member_initial_data['email_conf_send_every'],
    'require_email_confirmation_howmany'               => $wishlist_member_initial_data['email_conf_how_many'],
    'require_email_confirmation_reminder_sender_name'  => $x_level_email_defaults['email_sender_name'],
    'require_email_confirmation_reminder_sender_email' => $x_level_email_defaults['email_sender_address'],
    'require_email_confirmation_reminder_subject'      => $x_level_email_defaults['email_confirmation_reminder_subject'] && $use_saved ? $x_level_email_defaults['email_confirmation_reminder_subject'] : $wishlist_member_initial_data['email_confirmation_reminder_subject'],
    'require_email_confirmation_reminder_message'      => $x_level_email_defaults['email_confirmation_reminder_message'] && $use_saved ? $x_level_email_defaults['email_confirmation_reminder_message'] : $wishlist_member_initial_data['email_confirmation_reminder_message'],

    'email_confirmed'                                  => empty($x_level_email_defaults['email_confirmed']) ? 0 : 1,
    'email_confirmed_sender_name'                      => $x_level_email_defaults['email_sender_name'],
    'email_confirmed_sender_email'                     => $x_level_email_defaults['email_sender_address'],
    'email_confirmed_subject'                          => $x_level_email_defaults['email_confirmed_subject'] && $use_saved ? $x_level_email_defaults['email_confirmed_subject'] : $wishlist_member_initial_data['email_confirmed_subject'],
    'email_confirmed_message'                          => $x_level_email_defaults['email_confirmed_message'] && $use_saved ? $x_level_email_defaults['email_confirmed_message'] : $wishlist_member_initial_data['email_confirmed_message'],

    'require_admin_approval_free_notification_admin'   => $x_level_email_defaults['require_admin_approval_free_notification_admin'],
    'require_admin_approval_free_admin_subject'        => $x_level_email_defaults['requireadminapproval_admin_subject'] && $use_saved ? $x_level_email_defaults['requireadminapproval_admin_subject'] : $wishlist_member_initial_data['requireadminapproval_admin_subject'],
    'require_admin_approval_free_admin_message'        => $x_level_email_defaults['requireadminapproval_admin_message'] && $use_saved ? $x_level_email_defaults['requireadminapproval_admin_message'] : $wishlist_member_initial_data['requireadminapproval_admin_message'],

    'require_admin_approval_free_notification_user1'   => $x_level_email_defaults['require_admin_approval_free_notification_user1'],
    'require_admin_approval_free_user1_sender_name'    => $x_level_email_defaults['email_sender_name'],
    'require_admin_approval_free_user1_sender_email'   => $x_level_email_defaults['email_sender_address'],
    'require_admin_approval_free_user1_subject'        => $x_level_email_defaults['requireadminapproval_email_subject'] && $use_saved ? $x_level_email_defaults['requireadminapproval_email_subject'] : $wishlist_member_initial_data['requireadminapproval_email_subject'],
    'require_admin_approval_free_user1_message'        => $x_level_email_defaults['requireadminapproval_email_message'] && $use_saved ? $x_level_email_defaults['requireadminapproval_email_message'] : $wishlist_member_initial_data['requireadminapproval_email_message'],

    'require_admin_approval_free_notification_user2'   => $x_level_email_defaults['require_admin_approval_free_notification_user2'],
    'require_admin_approval_free_user2_sender_name'    => $x_level_email_defaults['email_sender_name'],
    'require_admin_approval_free_user2_sender_email'   => $x_level_email_defaults['email_sender_address'],
    'require_admin_approval_free_user2_subject'        => $x_level_email_defaults['registrationadminapproval_email_subject'] && $use_saved ? $x_level_email_defaults['registrationadminapproval_email_subject'] : $wishlist_member_initial_data['registrationadminapproval_email_subject'],
    'require_admin_approval_free_user2_message'        => $x_level_email_defaults['registrationadminapproval_email_message'] && $use_saved ? $x_level_email_defaults['registrationadminapproval_email_message'] : $wishlist_member_initial_data['registrationadminapproval_email_message'],

    'require_admin_approval_paid_notification_admin'   => $x_level_email_defaults['require_admin_approval_paid_notification_admin'],
    'require_admin_approval_paid_admin_subject'        => $x_level_email_defaults['requireadminapproval_admin_paid_subject'] && $use_saved ? $x_level_email_defaults['requireadminapproval_admin_paid_subject'] : $wishlist_member_initial_data['requireadminapproval_admin_paid_subject'],
    'require_admin_approval_paid_admin_message'        => $x_level_email_defaults['requireadminapproval_admin_paid_message'] && $use_saved ? $x_level_email_defaults['requireadminapproval_admin_paid_message'] : $wishlist_member_initial_data['requireadminapproval_admin_paid_message'],

    'require_admin_approval_paid_notification_user1'   => $x_level_email_defaults['require_admin_approval_paid_notification_user1'],
    'require_admin_approval_paid_user1_sender_name'    => $x_level_email_defaults['email_sender_name'],
    'require_admin_approval_paid_user1_sender_email'   => $x_level_email_defaults['email_sender_address'],
    'require_admin_approval_paid_user1_subject'        => $x_level_email_defaults['requireadminapproval_email_paid_subject'] && $use_saved ? $x_level_email_defaults['requireadminapproval_email_paid_subject'] : $wishlist_member_initial_data['requireadminapproval_email_paid_subject'],
    'require_admin_approval_paid_user1_message'        => $x_level_email_defaults['requireadminapproval_email_paid_message'] && $use_saved ? $x_level_email_defaults['requireadminapproval_email_paid_message'] : $wishlist_member_initial_data['requireadminapproval_email_paid_message'],

    'require_admin_approval_paid_notification_user2'   => $x_level_email_defaults['require_admin_approval_paid_notification_user2'],
    'require_admin_approval_paid_user2_sender_name'    => $x_level_email_defaults['email_sender_name'],
    'require_admin_approval_paid_user2_sender_email'   => $x_level_email_defaults['email_sender_address'],
    'require_admin_approval_paid_user2_subject'        => $x_level_email_defaults['registrationadminapproval_email_paid_subject'] && $use_saved ? $x_level_email_defaults['registrationadminapproval_email_paid_subject'] : $wishlist_member_initial_data['registrationadminapproval_email_paid_subject'],
    'require_admin_approval_paid_user2_message'        => $x_level_email_defaults['registrationadminapproval_email_paid_message'] && $use_saved ? $x_level_email_defaults['registrationadminapproval_email_paid_message'] : $wishlist_member_initial_data['registrationadminapproval_email_paid_message'],

    'incomplete_notification'                          => $x_level_email_defaults['incomplete_notification'],
    'incomplete_start'                                 => $wishlist_member_initial_data['incomplete_notification_first'],
    'incomplete_start_type'                            => null,
    'incomplete_send_every'                            => $wishlist_member_initial_data['incomplete_notification_add_every'],
    'incomplete_howmany'                               => $wishlist_member_initial_data['incomplete_notification_add'],
    'incomplete_sender_name'                           => $x_level_email_defaults['email_sender_name'],
    'incomplete_sender_email'                          => $x_level_email_defaults['email_sender_address'],
    'incomplete_subject'                               => $x_level_email_defaults['incnotification_email_subject'] && $use_saved ? $x_level_email_defaults['incnotification_email_subject'] : $wishlist_member_initial_data['incnotification_email_subject'],
    'incomplete_message'                               => $x_level_email_defaults['incnotification_email_message'] && $use_saved ? $x_level_email_defaults['incnotification_email_message'] : $wishlist_member_initial_data['incnotification_email_message'],

    'newuser_notification_admin'                       => $x_level_email_defaults['notify_admin_of_newuser'],
    'newuser_admin_recipient'                          => $x_level_email_defaults['newmembernotice_email_recipient'] && $use_saved ? $x_level_email_defaults['newmembernotice_email_recipient'] : $x_level_email_defaults['email_sender_address'],
    'newuser_admin_subject'                            => $x_level_email_defaults['newmembernotice_email_subject'] && $use_saved ? $x_level_email_defaults['newmembernotice_email_subject'] : $wishlist_member_initial_data['newmembernotice_email_subject'],
    'newuser_admin_message'                            => $x_level_email_defaults['newmembernotice_email_message'] && $use_saved ? $x_level_email_defaults['newmembernotice_email_message'] : $wishlist_member_initial_data['newmembernotice_email_message'],

    'newuser_notification_user'                        => $x_level_email_defaults['newuser_notification_user'],
    'newuser_user_sender_name'                         => $x_level_email_defaults['email_sender_name'],
    'newuser_user_sender_email'                        => $x_level_email_defaults['email_sender_address'],
    'newuser_user_subject'                             => $x_level_email_defaults['register_email_subject'] && $use_saved ? $x_level_email_defaults['register_email_subject'] : $wishlist_member_initial_data['register_email_subject'],
    'newuser_user_message'                             => $x_level_email_defaults['register_email_body'] && $use_saved ? $x_level_email_defaults['register_email_body'] : $wishlist_member_initial_data['register_email_body'],

    'expiring_notification_admin'                      => $x_level_email_defaults['expiring_notification_admin'],
    'expiring_admin_send'                              => $wishlist_member_initial_data['expiring_notification_days'],
    'expiring_admin_subject'                           => $x_level_email_defaults['expiring_admin_subject'] && $use_saved ? $x_level_email_defaults['expiring_admin_subject'] : $wishlist_member_initial_data['expiring_admin_subject'],
    'expiring_admin_message'                           => $x_level_email_defaults['expiring_admin_message'] && $use_saved ? $x_level_email_defaults['expiring_admin_message'] : $wishlist_member_initial_data['expiring_admin_message'],

    'expiring_notification_user'                       => $x_level_email_defaults['expiring_notification'],
    'expiring_user_send'                               => $wishlist_member_initial_data['expiring_notification_days'],
    'expiring_user_sender_name'                        => $x_level_email_defaults['email_sender_name'],
    'expiring_user_sender_email'                       => $x_level_email_defaults['email_sender_address'],
    'expiring_user_subject'                            => $x_level_email_defaults['expiringnotification_email_subject'] && $use_saved ? $x_level_email_defaults['expiringnotification_email_subject'] : $wishlist_member_initial_data['expiringnotification_email_subject'],
    'expiring_user_message'                            => $x_level_email_defaults['expiringnotification_email_message'] && $use_saved ? $x_level_email_defaults['expiringnotification_email_message'] : $wishlist_member_initial_data['expiringnotification_email_message'],

    'cancel_notification'                              => $x_level_email_defaults['cancel_notification'],
    'cancel_sender_name'                               => $x_level_email_defaults['email_sender_name'],
    'cancel_sender_email'                              => $x_level_email_defaults['email_sender_address'],
    'cancel_subject'                                   => $x_level_email_defaults['cancel_email_subject'] && $use_saved ? $x_level_email_defaults['cancel_email_subject'] : $wishlist_member_initial_data['cancel_email_subject'],
    'cancel_message'                                   => $x_level_email_defaults['cancel_email_message'] && $use_saved ? $x_level_email_defaults['cancel_email_message'] : $wishlist_member_initial_data['cancel_email_message'],

    'uncancel_notification'                            => $x_level_email_defaults['uncancel_notification'],
    'uncancel_sender_name'                             => $x_level_email_defaults['email_sender_name'],
    'uncancel_sender_email'                            => $x_level_email_defaults['email_sender_address'],
    'uncancel_subject'                                 => $x_level_email_defaults['uncancel_email_subject'] && $use_saved ? $x_level_email_defaults['uncancel_email_subject'] : $wishlist_member_initial_data['uncancel_email_subject'],
    'uncancel_message'                                 => $x_level_email_defaults['uncancel_email_message'] && $use_saved ? $x_level_email_defaults['uncancel_email_message'] : $wishlist_member_initial_data['uncancel_email_message'],
];
