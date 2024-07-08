<?php

    require $this->legacy_wlm_dir . '/core/InitialValues.php';
    $keys         = [
        'password_hint_email_subject',
        'password_hint_email_message',
        'lostinfo_email_subject',
        'lostinfo_email_message',
    ];
    $default_data = [];
    foreach ($keys as $key) {
        $default_data[ $key ] = $wishlist_member_initial_data[ $key ];
    }
    printf("\n<script type='text/javascript'>var default_data = %s;\nvar form_data = %s;\n</script>\n", json_encode($default_data), json_encode($form_data));
    ?>
<style type="text/css">
    .content-wrapper .shortcode_inserter {
        min-height: auto;
    }
</style>

<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Passwords', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <label for="">
                <?php esc_html_e('Minimum Password Length:', 'wishlist-member'); ?>
                <?php $this->tooltip(__('The minimum password length should be at least 4 characters.', 'wishlist-member'), 'lg'); ?>
            </label>
            <div class="row">
                <div class="col-sm-6 col-md-3 col-xxl-2 no-margin">
                    <template class="wlm3-form-group">
                        {
                            name  : 'min_passlength',
                            value : '<?php echo esc_js($this->get_option('min_passlength') + 0); ?>',
                            addon_right : 'Characters',
                            group_class : 'no-margin',
                            'data-initial' : '<?php echo esc_js($this->get_option('min_passlength') + 0); ?>',
                            'max' : '99',
                            'min' : '4',
                            'type' : 'number',
                            class : 'text-center min-passlength-apply',
                        }
                    </template>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <small class="form-text text-muted" id="helpBlock">
                        <em><?php esc_html_e('Minimum password length will be set to the entered amount when registering or importing users. Default is set to 8.', 'wishlist-member'); ?></em><br />
                        <em>The [wlm_min_passlength] merge code can be added to a page or post using the blue WishList Member code insert button found in the edit section of all pages and posts.</em>
                    </small>
                    <br />
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('strongpassword'); ?>
        <div class="col-md-6">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Require Strong Passwords', 'wishlist-member')); ?>',
                    name  : 'strongpassword',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip: '<?php echo esc_js(__('WishList Member will require passwords to have at least one lowercase letter, one uppercase letter, one number and one special character if this setting is enabled.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('password_hinting'); ?>
        <div class="col-sm-8 col-md-6 col-xxl-4 col-xxxl-3">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Password Hinting', 'wishlist-member')); ?>',
                    name  : 'password_hinting',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'toggle-adjacent-disable',
                    tooltip: '<?php echo esc_js(__('If this setting is enabled, users will be able to enter a password hint during the Registration Process. They will also have an option to update the password hint in their user profile.<br><br>This will also display the password hint on the login page after a failed login attempt.<br><br>Additionally, an option to email the password hint to the user will be available on the WordPress "Lost Password" page.', 'wishlist-member')); ?>',
                    tooltip_size: 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
        <div class="col">
            <button href="#" id="password_hinting_btn" class="btn -primary -condensed edit-notification <?php echo esc_attr($option_val && '1' == $option_val ? '' : '-disable'); ?>">
                <i class="wlm-icons">settings</i>
                <span><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('enable_retrieve_password_override'); ?>
        <div class="col-sm-8 col-md-6 col-xxl-4 col-xxxl-3">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Allow WishList Member to Handle Password Reset', 'wishlist-member')); ?>',
                    name  : 'enable_retrieve_password_override',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'toggle-adjacent-disable',
                    tooltip: '<?php echo esc_js(__('WishList Member overrides the handling of the WordPress Password Reset process from other plugins and themes if this setting is enabled.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
        <div class="col">
            <button href="#" id="enable_retrieve_password_override_btn" class="btn -primary -condensed edit-notification <?php echo esc_attr($option_val && '1' == $option_val ? '' : '-disable'); ?>">
                <i class="wlm-icons">settings</i>
                <span><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('disable_password_change_email_for_admin'); ?>
        <div class="col-md-6">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Disable "Password Changed" Admin Notification', 'wishlist-member')); ?>',
                    name  : 'disable_password_change_email_for_admin',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip: '<?php echo esc_js(__('By default, WordPress sends an email notification to the admin every time a user changes a password. Enabling this setting will turn this email notification off and the notifications will no longer be sent to the admin.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <hr />
    <div class="row">
        <?php
            $option_val = $this->get_option('mask_passwords_in_emails');
            $option_val = false === $option_val ? 1 : $option_val;
        ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Passwords in Administrator Emails', 'wishlist-member')); ?>',
                    name  : 'mask_passwords_in_emails',
                    value : '0',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '1',
                    class : 'wlm_toggle-switch notification-switch-password',
                    type  : 'checkbox',
                    tooltip: '<?php echo esc_js(__('User passwords are included in email notifications sent to the Admin if this setting is enabled. Please be aware this poses a potential security risk as noted in the warning message that appears when this setting is enabled.', 'wishlist-member')); ?>',
                    tooltip_size: 'md'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
            <!-- <br> -->
            <span class="form-text text-danger help-block <?php echo esc_attr('0' !== $option_val ? 'd-none' : ''); ?>">
                <p class="mb-0"><?php esc_html_e('By enabling this feature, I understand that I am putting my members\' passwords at risk by having them sent to me via email.', 'wishlist-member'); ?></p>
                <p class="mb-0"><?php esc_html_e('I accept this risk and I assume all liability for any damages that may occur to my members as a result of exposing their passwords to this risk.', 'wishlist-member'); ?></p>
            </span>
        </div>
    </div>
</div>

<div data-classes="modal-lg" id="edit-notification-modal-info" data-id="edit-notification-modal" data-label="edit_notification_modal_modal" data-title="Editing Notification for '<span></span>'" style="display:none">
    <div class="body no-margin">
        <div class="content-wrapper -no-background -no-header no-margin"></div>
    </div>
    <div class="footer">
    <button type="button" class="btn -bare" data-dismiss="modal">
        <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
    </button>
    <button type="button" class="btn -primary save-button">
        <i class="wlm-icons">save</i>
        <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
    </button>
    <button class="-close btn -success -modal-btn save-button">
        <i class="wlm-icons">save</i>
        <span><?php esc_html_e('Save & Close', 'wishlist-member'); ?></span>
    </button>
    </div>
</div>
