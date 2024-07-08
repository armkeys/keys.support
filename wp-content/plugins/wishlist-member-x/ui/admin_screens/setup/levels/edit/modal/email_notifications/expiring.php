<div class="expiring -holder">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link" href="#expiring_notification_user" role="tab" data-toggle="tab"><?php esc_html_e('User Notification', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#expiring_notification_admin" role="tab" data-toggle="tab"><?php esc_html_e('Admin Notification', 'wishlist-member'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="expiring_notification_admin">
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-right form-inline">
                        <template class="wlm3-form-group">{
                            type : 'text',
                            name : 'expiring_admin_send',
                            addon_left : 'Send Once',
                            addon_right : 'Days Before Expiration',
                            style : 'width: 60px;',
                            class : 'text-center'
                        }</template>
                    </div>
                    <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'expiring_notification_admin',
                    type : 'toggle-switch', value: 1, uncheck_value: 0,
                    tooltip : '<?php echo esc_js(__('The Expiring Email Notification will be sent to the admin one time if enabled.', 'wishlist-member')); ?>'
                    }</template>
                    <br style="clear:both"><hr style="margin-top:0">
                </div>
            </div>
            <div class="row">
                <template class="wlm3-form-group">{
                    addon_left : 'Subject',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'expiring_admin_subject',
                    column: 'col-md-12',
                    class: 'email-subject'
                }</template>
                <template class="wlm3-form-group">{
                    name : 'expiring_admin_message',
                    type : 'textarea',
                    class : 'levels-richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="expiring_admin">Reset to Global Default Message</button>
                    <template class="wlm3-form-group">{
                        type : 'select',
                        column : 'col-md-5 pull-right no-margin no-padding',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value:'[expirydate]',text:'Expiration Date'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=expiring_admin_message]'
                    }</template>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="expiring_notification_user">
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-right form-inline">
                        <template class="wlm3-form-group">{
                            type : 'text',
                            name : 'expiring_user_send',
                            addon_left : 'Send Once',
                            addon_right : 'Days Before Expiration',
                            style : 'width: 60px;',
                            class : 'text-center'
                        }</template>
                    </div>
                    <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'expiring_notification_user',
                    type : 'toggle-switch', value: 1, uncheck_value: 0,
                    tooltip : '<?php echo esc_js(__('The Expiring Email Notification will be sent to the member one time if enabled.', 'wishlist-member')); ?>',
                    tooltip_size: 'xxl'                 
                    }</template>
                    <br style="clear:both"><hr style="margin-top:0">
                </div>
            </div>
            <div class="row">
                <div class="col-auto mb-2">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                            name  : 'expiring_level_default_sender',
                            value : '1',
                            uncheck_value : '0',
                            type  : 'checkbox',
                            class : 'modal-input -sender-default-toggle',
                        }
                    </template>
                </div>
            </div>
            <div class="row level-sender-info" id="expiring_level_default_sender">
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Name',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'expiring_user_sender_name',
                    column: 'col-md-6'
                }</template>
                <template class="wlm3-form-group">{
                    addon_left : 'Sender Email',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'expiring_user_sender_email',
                    column: 'col-md-6'
                }</template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">{
                    addon_left : 'Subject',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'expiring_user_subject',
                    column: 'col-md-12',
                    class: 'email-subject'
                }</template>
                <template class="wlm3-form-group">{
                    name : 'expiring_user_message',
                    type : 'textarea',
                    class : 'levels-richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2'
                }</template>
                <div class="col-md-12">
                    <button class="btn -default -condensed email-reset-button" data-target="expiring_user">Reset to Global Default Message</button>
                    <template class="wlm3-form-group">{
                        type : 'select',
                        column : 'col-md-5 pull-right no-margin no-padding',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : get_merge_codes([{value:'[expirydate]',text:'Expiration Date'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=expiring_user_message]'
                    }</template>
                </div>
            </div>
        </div>
    </div>
</div>
