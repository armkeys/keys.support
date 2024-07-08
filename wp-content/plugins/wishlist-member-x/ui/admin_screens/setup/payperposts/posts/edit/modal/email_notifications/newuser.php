<div class="newuser -holder">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link" href="#newuser_notification_user" role="tab" data-toggle="tab"><?php esc_html_e('User Notification', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#newuser_notification_admin" role="tab" data-toggle="tab"><?php esc_html_e('Admin Notification', 'wishlist-member'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="newuser_notification_admin">
            <div class="row">
                <div class="col-md-12">
                    <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('Enabling this will send a message to the admin after a member registers for this pay per post', 'wishlist-member')); ?>', name : 'newuser_notification_admin',
                    type : 'toggle-switch', value: 1, uncheck_value: 0,
                    tooltip : '<?php echo esc_js(__('The New Member Registration Email will automatically be sent to the site admin when a new Member registers for a Pay Per Post.', 'wishlist-member')); ?>'
                    }</template>
                    <hr>
                </div>
            </div>
            <div class="row">

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
                    class : 'richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="newuser_admin"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
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
                <div class="col-md-12">
                    <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('Enabling this will send a message to the member after they register for this pay per post', 'wishlist-member')); ?>', name : 'newuser_notification_user',
                    type : 'toggle-switch', value: 1, uncheck_value: 0,
                    }</template>
                    <hr>
                </div>
            </div>
            <div class="row">

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
                    class : 'richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="newuser_user">Reset to Default</button>
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
