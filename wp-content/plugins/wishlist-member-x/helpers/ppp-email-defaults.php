<?php

/**
 * Default Pay Per Post email template values.
 *
 * @package WishListMember/Helpers
 */

$ppp_email_defaults = array_intersect_key($this->level_email_defaults, array_flip(['incomplete_notification', 'incomplete_start', 'incomplete_start_type', 'incomplete_send_every', 'incomplete_howmany', 'incomplete_sender_name', 'incomplete_sender_email', 'incomplete_subject', 'incomplete_message', 'incomplete_sender_name', 'incomplete_sender_email', 'newuser_notification_admin', 'newuser_admin_subject', 'newuser_admin_message', 'newuser_notification_user', 'newuser_user_sender_name', 'newuser_user_sender_email', 'newuser_user_subject', 'newuser_user_message', 'newuser_user_sender_name', 'newuser_user_sender_email']));
