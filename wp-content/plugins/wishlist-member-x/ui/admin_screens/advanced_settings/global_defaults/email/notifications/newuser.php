<div class="newuser -holder">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link" href="#newuser_notification_user" role="tab" data-toggle="tab"><?php esc_html_e('User Notification', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#newuser_notification_admin" role="tab" data-toggle="tab"><?php esc_html_e('Admin Notification', 'wishlist-member'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="newuser_notification_admin">
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
                    'newuser_notification_admin'
                );
                ?>
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
                    class : 'richtextx',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="newuser_admin"><?php esc_html_e('Reset to Original Message', 'wishlist-member'); ?></button>
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
                <?php
                printf(
                    wp_kses(
                        $enable_as_default,
                        [
                            'div' => ['class' => true],
                            'template' => ['class' => true],
                        ]
                    ),
                    'newuser_notification_user'
                );
                ?>
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
                    class : 'richtextx',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="newuser_user">Reset to Original Message</button>
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
