<div class="uncancel -holder">
    <div class="row">
        <div class="col-md-12">
            <template class="wlm3-form-group">{
            label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'uncancel_notification',
            type : 'toggle-switch', value: 1, uncheck_value: 0, 
            tooltip : '<?php echo esc_js(__('The Membership Uncancelled Notification will be sent to those who are uncancelled from a level', 'wishlist-member')); ?>',
            tooltip_size: 'lg'
            }</template>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-auto mb-2">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Use Global Default Sender Info', 'wishlist-member')); ?>',
                    name  : 'membership_uncancelled_default_sender',
                    value : '1',
                    uncheck_value : '0',
                    type  : 'checkbox',
                    class : 'modal-input -sender-default-toggle',
                }
            </template>
        </div>
    </div>
    <div class="row level-sender-info" id="membership_uncancelled_default_sender">
        <template class="wlm3-form-group">{
            addon_left: 'Sender Name',
            group_class : '-label-addon mb-2',
            type: 'text',
            name: 'uncancel_sender_name',
            column : 'col-md-6'
        }</template>
        <template class="wlm3-form-group">{
            addon_left: 'Sender Email',
            group_class : '-label-addon mb-2',
            type: 'text',
            name: 'uncancel_sender_email',
            column : 'col-md-6'
        }</template>
    </div>
    <div class="row">
        <template class="wlm3-form-group">{
            addon_left: 'Subject',
            group_class : '-label-addon mb-2',
            type: 'text',
            name: 'uncancel_subject',
            column : 'col-md-12',
            class: 'email-subject'
        }</template>
        <template class="wlm3-form-group">{
            name: 'uncancel_message',
            type: 'textarea',
            class : 'levels-richtext',
            column : 'col-md-12',
            group_class : 'mb-2',
        }</template>
        <div class="col-md-12">
            <button class="btn -default -condensed email-reset-button" data-target="uncancel">Reset to Global Default Message</button>
            <template class="wlm3-form-group">{
                type : 'select',
                column : 'col-md-5 pull-right no-margin no-padding',
                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                group_class : 'shortcode_inserter mb-0',
                style : 'width: 100%',
                options : get_merge_codes(),
                grouped: true,
                class : 'insert_text_at_caret',
                'data-target' : '[name=uncancel_message]'
            }</template>
        </div>
    </div>
</div>
