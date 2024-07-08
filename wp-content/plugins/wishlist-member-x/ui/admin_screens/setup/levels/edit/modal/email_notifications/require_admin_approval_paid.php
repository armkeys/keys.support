<div class="requireadminapproval-paid -holder">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link" href="#requireadminapproval_paid_notification_admin" role="tab" data-toggle="tab"><?php esc_html_e('Notification to Admin', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#requireadminapproval_paid_notification_user1" role="tab" data-toggle="tab"><?php esc_html_e('User Awaiting Approval', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#requireadminapproval_paid_notification_user2" role="tab" data-toggle="tab"><?php esc_html_e('User Approved', 'wishlist-member'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="requireadminapproval_paid_notification_admin">
            <div class="row">
                <div class="col-md-12">
                    <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'require_admin_approval_paid_notification_admin',
                    type : 'toggle-switch', value: 1, uncheck_value: 0,
                    }</template>
                    <hr>
                </div>
            </div>
            <div class="row">

                <template class="wlm3-form-group">{
                    addon_left : 'Subject',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'require_admin_approval_paid_admin_subject',
                    column: 'col-md-12',
                    class: 'email-subject'
                }</template>
                <template class="wlm3-form-group">{
                    name : 'require_admin_approval_paid_admin_message',
                    type : 'textarea',
                    class : 'levels-richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="require_admin_approval_paid_admin"><?php esc_html_e('Reset to Global Default Message', 'wishlist-member'); ?></button>
                    <template class="wlm3-form-group">{
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=require_admin_approval_paid_admin_message]'
                    }</template>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="requireadminapproval_paid_notification_user1">
            <div class="row">
                <div class="col-md-12">
                    <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'require_admin_approval_paid_notification_user1',
                    type : 'toggle-switch', value: 1, uncheck_value: 0,
                    }</template>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-auto mb-2">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                            name  : 'require_admin_approval_paid_default_sender',
                            value : '1',
                            uncheck_value : '0',
                            type  : 'checkbox',
                            class : 'modal-input -sender-default-toggle',
                        }
                    </template>
                </div>
            </div>
            <div class="row level-sender-info" id="require_admin_approval_paid_default_sender">
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Name',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'require_admin_approval_paid_user1_sender_name',
                    column: 'col-md-6'
                }</template>
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Email',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'require_admin_approval_paid_user1_sender_email',
                    column: 'col-md-6'
                }</template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">{
                    addon_left : 'Subject',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'require_admin_approval_paid_user1_subject',
                    column: 'col-md-12',
                    class: 'email-subject'
                }</template>
                <template class="wlm3-form-group">{
                    name : 'require_admin_approval_paid_user1_message',
                    type : 'textarea',
                    class : 'levels-richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="require_admin_approval_paid_user1">Reset to Global Default Message</button>
                    <template class="wlm3-form-group">{
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=require_admin_approval_paid_user1_message]'
                    }</template>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="requireadminapproval_paid_notification_user2">
            <div class="row">
                <div class="col-md-12">
                    <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'require_admin_approval_paid_notification_user2',
                    type : 'toggle-switch', value: 1, uncheck_value: 0,
                    }</template>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-auto mb-2">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                            name  : 'registration_approved_paid_default_sender',
                            value : '1',
                            uncheck_value : '0',
                            type  : 'checkbox',
                            class : 'modal-input -sender-default-toggle',
                        }
                    </template>
                </div>
            </div>
            <div class="row level-sender-info" id="registration_approved_paid_default_sender">
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Name',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'require_admin_approval_paid_user2_sender_name',
                    column: 'col-md-6'
                }</template>
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Email',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'require_admin_approval_paid_user2_sender_email',
                    column: 'col-md-6'
                }</template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">{
                    addon_left : 'Subject',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'require_admin_approval_paid_user2_subject',
                    column: 'col-md-12',
                    class: 'email-subject'
                }</template>
                <template class="wlm3-form-group">{
                    name : 'require_admin_approval_paid_user2_message',
                    type : 'textarea',
                    class : 'levels-richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="require_admin_approval_paid_user2"><?php esc_html_e('Reset to Global Default Message', 'wishlist-member'); ?></button>
                    <template class="wlm3-form-group">{
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=require_admin_approval_paid_user2_message]'
                    }</template>
                </div>
            </div>
        </div>
    </div>
</div>
