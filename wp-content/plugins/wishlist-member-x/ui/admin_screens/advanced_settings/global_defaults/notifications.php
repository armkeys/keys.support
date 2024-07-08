<?php
    $notifications = [
        'registration'   => [
            'title'   => 'Registration',
            'options' => [
                // "registration_notification" => array( "label"=>"Registration"),
                // "requireemailconfirmation_notification" => array( "label"=>"Require Email Confirmation"),
                // "requireadminapproval_notification" => array( "label"=>"Require Admin Approval"),
                // "registrationadminapproval_notification" => array( "label"=>"Admin Approval Notification"),
                // "incomplete_notification" => array( "label"=>"Incomplete Registration Notification"),
            ],
        ],
        'administration' => [
            'title'   => 'Administration',
            'options' => [
                // "notify_admin_of_newuser" => array( "label"=>"New Member Notification"),
                'unsub_notification' => ['label' => 'Member Unsubscribe'],
            ],
        ],
        'maintenance'    => [
            'title'   => 'Maintenance',
            'options' => [
                // "enable_retrieve_password_override" => array( "label"=>"Lost Info"),
                // "expiring_notification" => array( "label"=>"Expiring Member Notification"),
                // "password_hinting" => array( "label"=>"Password Hint Notification"),
            ],
        ],
    ];
    ?>

<div class="content-wrapper">
    <h3 class="main-title"><?php esc_html_e('Notifications', 'wishlist-member'); ?></h3>
    <?php foreach ($notifications as $notification) : ?>
        <div class="row">
            <div class="col-lg-12">
                <h4 class="section-title"><?php echo esc_html($notification['title']); ?></h4>
            </div>
        </div>
        <?php foreach ($notification['options'] as $key => $values) : ?>
            <?php
                $option_val  = $this->get_option($key);
                $is_checked  = $option_val && '1' == $option_val ? 'true' : 'false';
                $is_disabled = $option_val && '1' == $option_val ? '' : 'disabled';
                $css_class   = $option_val && '1' == $option_val ? '-primary' : '-disable';
            ?>
            <div class="row">
                <div class="col-lg-1 col-md-2">
                    <template class="wlm3-toggle-switch">
                        {
                            name : '<?php echo esc_js($key); ?>',
                            value : '1',
                            checked: <?php echo esc_js($is_checked); ?>,
                            class : 'notification-switch',
                            uncheck_value : 0,
                        }
                    </template>
                    <input type="hidden" name="action" value="admin_actions" />
                    <input type="hidden" name="WishListMemberAction" value="save" />
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="title-label"><?php echo esc_html($values['label']); ?></h5>
                </div>
                <div class="col-lg-7 col-md-6">
                    <a href="#" id="<?php echo esc_attr($key); ?>_btn" class="btn -primary <?php echo esc_attr($css_class); ?> edit-notification <?php echo esc_attr($is_disabled); ?>">
                        <i class="wlm-icons md-18">settings</i>
                        <span class="text"><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        <br /><br />
    <?php endforeach; ?>
</div>


<!-- Modal -->

<div id="edit-notification-modal-info" data-id="edit-notification-modal" data-label="edit_notification_modal_modal" data-title="Editing Notification for '<span></span>'" style="display:none">
    <div class="body">
        <div class="content-wrapper -no-background -no-header"></div>
    </div>
    <div class="footer">
    <button type="button" class="btn -bare" data-dismiss="modal">Close</button>
    <button type="button" class="btn -primary edit-notification-save">Save</button>
    <input type="hidden" class="notification-button-id" value="" />
    </div>
</div>
<script>
    new wlm3_modal('#edit-notification-modal-info');
</script>

<!-- Holders -->
<div id="registration_notification_holder" class="d-none">
    <?php
        $email_address = $this->get_option('register_email_addres');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('register_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('register_email_subject');
        $email_body    = $this->get_option('register_email_body');
    ?>
    <div class="row">
        <div class="col-md-6">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Name', 'wishlist-member')); ?>',
                    name : 'register_email_name',
                    value : <?php echo json_encode($email_name); ?>,
                    placeholder : "Sender's name",
                    required : 'required'
                }
            </template>
        </div>
        <div class="col-md-6">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Email', 'wishlist-member')); ?>',
                    name : 'register_email_addres',
                    value : <?php echo json_encode($email_address); ?>,
                    placeholder : "Sender's email",
                    required : 'required'
                }
            </template>
        </div>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    name : 'register_email_subject',
                    value : <?php echo json_encode($email_subject); ?>,
                    placeholder : '<?php echo esc_js(__('Email subject', 'wishlist-member')); ?>',
                    required : 'required'
                }
            </template>
        </div>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    type : 'textarea',
                    name : 'register_email_body',
                    value : <?php echo json_encode($email_body); ?>,
                    placeholder : '<?php echo esc_js(__('Your message', 'wishlist-member')); ?>',
                    required : 'required',
                    rows : 10,
                }
            </template>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="requireemailconfirmation_notification_holder" class="d-none">
    <?php
        $email_address = $this->get_option('confirm_email_address');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('confirm_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('confirm_email_subject');
        $email_body    = $this->get_option('confirm_email_message');

        $email_conf_send_after = $this->get_option('email_conf_send_after');
        $email_conf_how_many   = $this->get_option('email_conf_how_many');
        $email_conf_send_every = $this->get_option('email_conf_send_every');
    ?>
    <div class="row">
        <div class="col-md-6">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Name', 'wishlist-member')); ?>',
                    name : 'confirm_email_name',
                    value : <?php echo json_encode($email_name); ?>,
                    placeholder : "Sender's name",
                    required : 'required'
                }
            </template>
        </div>
        <div class="col-md-6">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Email', 'wishlist-member')); ?>',
                    name : 'confirm_email_address',
                    value : <?php echo json_encode($email_address); ?>,
                    placeholder : "Sender's email",
                    required : 'required'
                }
            </template>
        </div>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    name : 'confirm_email_subject',
                    value : <?php echo json_encode($email_subject); ?>,
                    placeholder : '<?php echo esc_js(__('Email subject', 'wishlist-member')); ?>',
                    required : 'required'
                }
            </template>
        </div>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    type : 'textarea',
                    name : 'confirm_email_message',
                    value : <?php echo json_encode($email_body); ?>,
                    placeholder : '<?php echo esc_js(__('Your message', 'wishlist-member')); ?>',
                    required : 'required',
                    rows : 10,
                }
            </template>
        </div>
    </div>
    <h4><?php esc_html_e('Email Confirmation Reminder', 'wishlist-member'); ?></h4>
    <div class="row">
        <div class="col-md-4">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Start Reminder After', 'wishlist-member')); ?>',
                    name : 'email_conf_send_after',
                    value : <?php echo json_encode($email_conf_send_after); ?>,
                    placeholder : "Remind after",
                }
            </template>
        </div>
        <div class="col-md-4">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('How Many Reminders', 'wishlist-member')); ?>',
                    name : 'email_conf_how_many',
                    value : <?php echo json_encode($email_conf_how_many); ?>,
                    placeholder : "Reminders to send",
                }
            </template>
        </div>
        <div class="col-md-4">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Send Every', 'wishlist-member')); ?>',
                    name : 'email_conf_send_every',
                    value : <?php echo json_encode($email_conf_send_every); ?>,
                    placeholder : "Send every",
                }
            </template>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="requireadminapproval_notification_holder" class="d-none">
    <?php
        $email_address = $this->get_option('requireadminapproval_email_address');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('requireadminapproval_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('requireadminapproval_email_subject');
        $email_body    = $this->get_option('requireadminapproval_email_message');
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Name', 'wishlist-member'); ?></label>
                <input type="text" name="requireadminapproval_email_name" value="<?php echo esc_attr($email_name); ?>" class="form-control" placeholder="Sender's name" required />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                <input type="text" name="requireadminapproval_email_address" value="<?php echo esc_attr($email_address); ?>" class="form-control" placeholder="Sender's email" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="requireadminapproval_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="requireadminapproval_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="registrationadminapproval_notification_holder" class="d-none">
    <?php
        $email_address = $this->get_option('registrationadminapproval_email_address');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('registrationadminapproval_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('registrationadminapproval_email_subject');
        $email_body    = $this->get_option('registrationadminapproval_email_message');
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Name', 'wishlist-member'); ?></label>
                <input type="text" name="registrationadminapproval_email_name" value="<?php echo esc_attr($email_name); ?>" class="form-control" placeholder="Sender's name" required />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                <input type="text" name="registrationadminapproval_email_address" value="<?php echo esc_attr($email_address); ?>" class="form-control" placeholder="Sender's email" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="registrationadminapproval_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="registrationadminapproval_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="incomplete_notification_holder" class="d-none">
    <?php
        $email_address = $this->get_option('incnotification_email_address');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('incnotification_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('incnotification_email_subject');
        $email_body    = $this->get_option('incnotification_email_message');

        $email_conf_send_after = $this->get_option('incomplete_notification_first');
        $email_conf_how_many   = $this->get_option('incomplete_notification_add');
        $email_conf_send_every = $this->get_option('incomplete_notification_add_every');
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Name', 'wishlist-member'); ?></label>
                <input type="text" name="incnotification_email_name" value="<?php echo esc_attr($email_name); ?>" class="form-control" placeholder="Sender's name" required />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                <input type="text" name="incnotification_email_address" value="<?php echo esc_attr($email_address); ?>" class="form-control" placeholder="Sender's email" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="incnotification_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="incnotification_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <h4><?php esc_html_e('Incomplete Registration Reminder', 'wishlist-member'); ?></h4>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for=""><?php esc_html_e('Start Reminder After', 'wishlist-member'); ?></label>
                <input type="text" name="incomplete_notification_first" value="<?php echo esc_attr($email_conf_send_after); ?>" class="form-control" placeholder="Remind after" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="">How Many Reminders</label>
                <input type="text" name="incomplete_notification_add" value="<?php echo esc_attr($email_conf_how_many); ?>" class="form-control" placeholder="Reminders to send" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for=""><?php esc_html_e('Send Every', 'wishlist-member'); ?></label>
                <input type="text" name="incomplete_notification_add_every" value="<?php echo esc_attr($email_conf_send_every); ?>" class="form-control" placeholder="Send every" />
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="notify_admin_of_newuser_holder" class="d-none">
    <?php
        $email_subject = $this->get_option('newmembernotice_email_subject');
        $email_body    = $this->get_option('newmembernotice_email_message');
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="newmembernotice_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="newmembernotice_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="unsub_notification_holder" class="d-none">
    <?php
        $email_subject = $this->get_option('unsubscribe_notice_email_subject');
        $email_body    = $this->get_option('unsubscribe_notice_email_message');
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="unsubscribe_notice_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="unsubscribe_notice_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="enable_retrieve_password_override_holder" class="d-none">
    <?php
        $email_address = $this->get_option('lostinfo_email_address');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('lostinfo_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('lostinfo_email_subject');
        $email_body    = $this->get_option('lostinfo_email_message');
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Name', 'wishlist-member'); ?></label>
                <input type="text" name="lostinfo_email_name" value="<?php echo esc_attr($email_name); ?>" class="form-control" placeholder="Sender's name" required />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                <input type="text" name="lostinfo_email_address" value="<?php echo esc_attr($email_address); ?>" class="form-control" placeholder="Sender's email" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="lostinfo_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="lostinfo_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="expiring_notification_holder" class="d-none">
    <?php
        $email_address = $this->get_option('expiringnotification_email_address');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('expiringnotification_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('expiringnotification_email_subject');
        $email_body    = $this->get_option('expiringnotification_email_message');

        $expiring_notification_days = $this->get_option('expiring_notification_days');
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Name', 'wishlist-member'); ?></label>
                <input type="text" name="expiringnotification_email_name" value="<?php echo esc_attr($email_name); ?>" class="form-control" placeholder="Sender's name" required />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                <input type="text" name="expiringnotification_email_address" value="<?php echo esc_attr($email_address); ?>" class="form-control" placeholder="Sender's email" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="expiringnotification_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="expiringnotification_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <h4><?php esc_html_e('Expiring Membership Reminder', 'wishlist-member'); ?></h4>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">Number of Days Before Expiration Date</label>
                <!-- start: v4 -->
                <input style="width: 50%;" type="text" name="expiring_notification_days" value="<?php echo esc_attr($expiring_notification_days); ?>" class="form-control" placeholder="Days before expiration" />
                <small class="form-text text-muted" id="helpBlock">An Email Notification will be sent once each day based on the number entered into this field.</small>
                <!-- end: v4 -->
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>

<div id="password_hinting_holder" class="d-none">
    <?php
        $email_address = $this->get_option('password_hint_email_address');
        $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
        $email_name    = $this->get_option('password_hint_email_name');
        $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
        $email_subject = $this->get_option('password_hint_email_subject');
        $email_body    = $this->get_option('password_hint_email_message');
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Name', 'wishlist-member'); ?></label>
                <input type="text" name="password_hint_email_name" value="<?php echo esc_attr($email_name); ?>" class="form-control" placeholder="Sender's name" required />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for=""><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                <input type="text" name="password_hint_email_address" value="<?php echo esc_attr($email_address); ?>" class="form-control" placeholder="Sender's email" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for=""><?php esc_html_e('Subject', 'wishlist-member'); ?></label>
                <input type="text" name="password_hint_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="form-control" placeholder="Email subject" required />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" name="password_hint_email_message" rows="10" placeholder="Your message" required><?php echo esc_textarea($email_body); ?></textarea>
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</div>
