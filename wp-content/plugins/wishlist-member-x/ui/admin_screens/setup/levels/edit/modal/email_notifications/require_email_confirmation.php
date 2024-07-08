<?php

/**
 * Require Email Confirmation email template modal
 *
 * @package WishListMember
 */

?>
<div class="requireemailconfirmation -holder">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link" href="#requireemailconfirmation_notification" role="tab" data-toggle="tab"><?php esc_html_e('User Notification', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#requireemailconfirmation_notification_reminder" role="tab" data-toggle="tab"><?php esc_html_e('User Notification Reminder', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#requireemailconfirmation_confirmed_notification" role="tab" data-toggle="tab"><?php esc_html_e('Email Confirmed Notification', 'wishlist-member'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="requireemailconfirmation_notification">
            <div class="row">
                <div class="col-auto mb-2">
                    <template class="wlm3-form-group">
                        {
                        label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                        name : 'email_confirmation_default_sender',
                        value : '1',
                        uncheck_value : '0',
                        type : 'checkbox',
                        class : 'modal-input -sender-default-toggle',
                        tooltip : '<?php echo esc_js(__('The Global Default Sender Info is located in the Advanced Options > Global Defaults > Email section of WishList Member.', 'wishlist-member')); ?>'
                        }
                    </template>
                </div>
            </div>
            <div class="row level-sender-info" id="email_confirmation_default_sender">
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Sender Name', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'require_email_confirmation_sender_name',
                    column : 'col-md-6'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Sender Email', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'require_email_confirmation_sender_email',
                    column : 'col-md-6'
                    }
                </template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'require_email_confirmation_subject',
                    column : 'col-md-12',
                    class: 'email-subject'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    name: 'require_email_confirmation_message',
                    class : 'levels-richtext',
                    type: 'textarea',
                    column : 'col-md-12',
                    group_class : 'mb-2'
                    }
                </template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="require_email_confirmation"><?php esc_html_e('Reset to Global Default Message', 'wishlist-member'); ?></button>
                    <template class="wlm3-form-group">
                        {
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[confirmurl]', text : 'Confirmation URL'}, {value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=require_email_confirmation_message]'
                        }
                    </template>
                </div>

            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="requireemailconfirmation_notification_reminder">
            <div class="row">
                <div class="col-md-12">
                    <template class="wlm3-form-group">
                        {
                        label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'require_email_confirmation_reminder',
                        type : 'toggle-switch', value: 1, uncheck_value: 0,
                        }
                    </template>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 ">
                    <div class="pull-left form-inline">
                        <template class="wlm3-form-group">
                            {
                            type : 'text',
                            name: 'require_email_confirmation_start',
                            addon_left : '<?php echo esc_js(__('First Sent After', 'wishlist-member')); ?>',
                            style : 'width: 50px;',
                            class : 'text-center',
                            onchange : 'this.value=Math.abs(this.value)||WLM3VARS.level_defaults.require_email_confirmation_start',
                            group_class : 'require-confirmation-start'
                            }
                        </template>
                        <template class="wlm3-form-group">
                            {
                            type : 'select',
                            name: 'require_email_confirmation_start_type',
                            class : 'text-center',
                            options : [
                                {text : '<?php echo esc_js(__('Hour(s)', 'wishlist-member')); ?>', value : ''},
                                {text : '<?php echo esc_js(__('Minute(s)', 'wishlist-member')); ?>', value : 'minutes'}
                            ],
                            style : 'width: 100px;',
                            group_class : 'require-confirmation-start-type'
                            }
                        </template>
                        <template class="wlm3-form-group">
                            {
                            type : 'text',
                            name: 'require_email_confirmation_send_every',
                            addon_left : '<?php echo esc_js(__('Send Every', 'wishlist-member')); ?>',
                            addon_right: '<?php echo esc_js(__('Hours', 'wishlist-member')); ?>',
                            style : 'width: 60px;',
                            class : 'text-center',
                            onchange : 'this.value=Math.abs(this.value)||WLM3VARS.level_defaults.require_email_confirmation_send_every'
                            }
                        </template>
                        <template class="wlm3-form-group">
                            {
                            type : 'text',
                            name: 'require_email_confirmation_howmany',
                            addon_left : '<?php echo esc_js(__('Total Reminders Sent', 'wishlist-member')); ?>',
                            style : 'width: 60px;',
                            class : 'text-center',
                            onchange : 'this.value=Math.abs(this.value)||WLM3VARS.level_defaults.require_email_confirmation_howmany'
                            }
                        </template>
                        <div class="np-help">
                            <?php
                            $this->tooltip(
                                __(
                                    'First Sent After:<br>The first User Notification Reminder email will be sent after the set time. Example: If this is set to 1 Hour, the email would be sent 1 Hour after the member registered (if they have not yet confirmed registration).<br><br>Send Every:<br>The time interval each additional User Notification Reminder email will be sent after the first email is sent. Example: If this is set to 24 Hours, the next email(s) would be sent in 24 Hour intervals after the first User Notification Reminder email was sent.<br><br>Total Reminders Sent:<br>The Total number of User Notification Reminder emails to be sent. Example: If this is set to 3, a total number of 3 reminder emails would be sent at the scheduled intervals. The first reminder email would be sent followed by two additional reminder emails for a total of 3.<br><br>Note: Messages are scheduled with the WP Cron built into WordPress. The WP Cron does not support specific time. For example, 1 hour may be slightly more or slightly less than 60 minutes. Decimals can be used to set a time less than 1 hour. Example: 0.5 equals 30 minutes and 0.25 equals 15 minutes.',
                                    'wishlist-member'
                                ),
                                'lg'
                            );
                            ?>
                        </div>
                    </div>
                    <br style="clear:both">
                    <hr style="margin-top:0">
                </div>
            </div>
            <div class="row">
                <div class="col-auto mb-2">
                    <template class="wlm3-form-group">
                        {
                        label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                        name : 'require_email_confirmation_reminder_default_sender',
                        value : '1',
                        uncheck_value : '0',
                        type : 'checkbox',
                        class : 'modal-input -sender-default-toggle',
                        }
                    </template>
                </div>
            </div>
            <div class="row level-sender-info" id="require_email_confirmation_reminder_default_sender">
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Sender Name', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'require_email_confirmation_reminder_sender_name',
                    column : 'col-md-6'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Sender Email', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'require_email_confirmation_reminder_sender_email',
                    column : 'col-md-6'
                    }
                </template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'require_email_confirmation_reminder_subject',
                    column : 'col-md-12',
                    class: 'email-subject'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    name: 'require_email_confirmation_reminder_message',
                    class : 'levels-richtext',
                    type: 'textarea',
                    column : 'col-md-12',
                    group_class : 'mb-2'
                    }
                </template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="require_email_confirmation_reminder"><?php esc_html_e('Reset to Global Default Message', 'wishlist-member'); ?></button>
                    <template class="wlm3-form-group">
                        {
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[confirmurl]', text : 'Confirmation URL'}, {value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=require_email_confirmation_reminder_message]'
                        }
                    </template>
                </div>

            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="requireemailconfirmation_confirmed_notification">
            <div class="row">
                <div class="col-md-12">
                    <template class="wlm3-form-group">
                        {
                        label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'email_confirmed',
                        type : 'toggle-switch', value: 1, uncheck_value: 0,
                        }
                    </template>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-auto mb-2">
                    <template class="wlm3-form-group">
                        {
                        label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                        name : 'email_confirmed_default_sender',
                        value : '1',
                        uncheck_value : '0',
                        type : 'checkbox',
                        class : 'modal-input -sender-default-toggle',
                        }
                    </template>
                </div>
            </div>
            <div class="row level-sender-info" id="email_confirmed_default_sender">
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Sender Name', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'email_confirmed_sender_name',
                    column : 'col-md-6'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Sender Email', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'email_confirmed_sender_email',
                    column : 'col-md-6'
                    }
                </template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                    addon_left: '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    group_class: '-label-addon mb-2',
                    type: 'text',
                    name: 'email_confirmed_subject',
                    column : 'col-md-12',
                    class: 'email-subject'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    name: 'email_confirmed_message',
                    class : 'levels-richtext',
                    type: 'textarea',
                    column : 'col-md-12',
                    group_class : 'mb-2'
                    }
                </template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="email_confirmed"><?php esc_html_e('Reset to Global Default Message', 'wishlist-member'); ?></button>
                    <template class="wlm3-form-group">
                        {
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[confirmurl]', text : 'Confirmation URL'}, {value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=email_confirmed_message]'
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .form-group.require-confirmation-start-type {
        display: none;
    }
</style>
<script type="text/javascript">
    $(function() {
        $('.require-confirmation-start-type').contents().appendTo('.require-confirmation-start .input-group');
    });
</script>
