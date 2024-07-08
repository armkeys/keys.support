<div class="newuser -holder">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link" href="#newuser_notification_user" role="tab" data-toggle="tab"><?php esc_html_e('User Notification', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#newuser_notification_admin" role="tab" data-toggle="tab"><?php esc_html_e('Admin Notification', 'wishlist-member'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="newuser_notification_admin">
            <div class="row">
                <div class="col-md-12">
                    <template class="wlm3-form-group">
                        {
                            addon_left : '<?php echo esc_js(__('When a Member is Added to This Level', 'wishlist-member')); ?>',
                            type : 'select',
                            options : [
                                { value: '1', text : '<?php esc_attr_e('Send Email', 'wishlist-member'); ?>' },
                                { value: '2', text : '<?php esc_attr_e('Send Email ONLY for New Members', 'wishlist-member'); ?>' },
                                { value: '0', text : '<?php esc_attr_e('Do NOT Send Email', 'wishlist-member'); ?>' },
                            ],
                            name : 'newuser_notification_admin',
                            tooltip : '<?php echo esc_js(__('The New Member Registration Email is sent to the site admin.', 'wishlist-member')); ?>',
                        }
                    </template>
                </div>
            </div>
            <div class="row">
                <template class="wlm3-form-group">{
                    addon_left : 'Admin Email Address',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'newuser_admin_recipient',
                    column: 'col-md-12'
                }</template>
                <template class="wlm3-form-group">{
                    addon_left : 'Subject',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'newuser_admin_subject',
                    column: 'col-md-12',
                    class: 'email-subject'
                }</template>
                <template class="wlm3-form-group">{
                    name : 'newuser_admin_message',
                    type : 'textarea',
                    class : 'levels-richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="newuser_admin"><?php esc_html_e('Reset to Global Default Message', 'wishlist-member'); ?></button>
                    <template class="wlm3-form-group">{
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=newuser_admin_message]'
                    }</template>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="newuser_notification_user">
            <div class="row">
                <div class="col">
                    <template class="wlm3-form-group">
                        {
                            type : 'select',
                            addon_left : '<?php echo esc_js(__('When a Member is Added to This Level', 'wishlist-member')); ?>',
                            options : [
                                { value: '1', text : '<?php esc_attr_e('Send Email', 'wishlist-member'); ?>' },
                                { value: '2', text : '<?php esc_attr_e('Send Email ONLY to New Members', 'wishlist-member'); ?>' },
                                { value: '0', text : '<?php esc_attr_e('Do NOT Send Email', 'wishlist-member'); ?>' },
                            ],
                            name : 'newuser_notification_user',
                            tooltip : '<?php echo esc_js(__('The New Member Registration Email is sent to the site admin.', 'wishlist-member')); ?>',
                        }
                    </template>
                </div>
            </div>
            <div class="row">
                <div class="col-auto mb-2">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                            name  : 'registration_default_sender',
                            value : '1',
                            uncheck_value : '0',
                            type  : 'checkbox',
                            class : 'modal-input -sender-default-toggle',
                        }
                    </template>
                </div>
            </div>
            <div class="row level-sender-info" id="registration_default_sender">
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Name',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'newuser_user_sender_name',
                    column: 'col-md-6'
                }</template>
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Email',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'newuser_user_sender_email',
                    column: 'col-md-6'
                }</template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">{
                    addon_left : 'Subject',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'newuser_user_subject',
                    column: 'col-md-12',
                    class: 'email-subject'
                }</template>
                <template class="wlm3-form-group">{
                    name : 'newuser_user_message',
                    type : 'textarea',
                    class : 'levels-richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="newuser_user">Reset to Global Default Message</button>
                    <template class="wlm3-form-group">{
                        type : 'select',
                        column : 'col-md-5 pull-right no-padding no-margin',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=newuser_user_message]'
                    }</template>
                </div>
            </div>
        </div>
    </div>
</div>
