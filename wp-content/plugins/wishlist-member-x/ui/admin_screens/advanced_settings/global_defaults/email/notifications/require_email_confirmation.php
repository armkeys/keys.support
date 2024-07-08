<?php

/**
 * GLobal defaults for require email confirmation email template.
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
                    class : 'richtextx',
                    type: 'textarea',
                    column : 'col-md-12',
                    group_class : 'mb-2'
                    }
                </template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="require_email_confirmation"><?php esc_html_e('Reset to Original Message', 'wishlist-member'); ?></button>
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
                <?php
                printf(
                    wp_kses(
                        $enable_as_default,
                        [
                            'div' => ['class' => true],
                            'template' => ['class' => true],
                        ]
                    ),
                    'require_email_confirmation_reminder'
                );
                ?>
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
                    class : 'richtextx',
                    type: 'textarea',
                    column : 'col-md-12',
                    group_class : 'mb-2'
                    }
                </template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="require_email_confirmation_reminder"><?php esc_html_e('Reset to Original Message', 'wishlist-member'); ?></button>
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
                <?php
                printf(
                    wp_kses(
                        $enable_as_default,
                        [
                            'div' => ['class' => true],
                            'template' => ['class' => true],
                        ]
                    ),
                    'email_confirmed'
                );
                ?>
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
                    class : 'richtextx',
                    type: 'textarea',
                    column : 'col-md-12',
                    group_class : 'mb-2'
                    }
                </template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="email_confirmed"><?php esc_html_e('Reset to Original Message', 'wishlist-member'); ?></button>
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
