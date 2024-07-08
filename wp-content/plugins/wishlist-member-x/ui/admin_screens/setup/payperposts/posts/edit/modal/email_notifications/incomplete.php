<div class="incomplete -holder">
    <div class="row">
        <div class="col-md-12">
            <template class="wlm3-form-group">{
            label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>', name : 'incomplete_notification',
            type : 'toggle-switch', value: 1, uncheck_value: 0, 
            tooltip : '<?php echo esc_js(__('The Incomplete Registration Email will be sent to those who do not complete the Registration Form during the registration process. This email includes a link the Member can use to complete the registration process.', 'wishlist-member')); ?>',
            tooltip_size: 'lg'
            }</template>
        </div>
        <div class="col-md-12">
            <div class="pull-left form-inline">
                <template class="wlm3-form-group">{
                    type : 'text',
                    name: 'incomplete_start',
                    addon_left : 'First Sent After',
                    style : 'width: 60px;',
                    class : 'text-center',
                    group_class : 'incomplete-start',
                }</template>
                <template class="wlm3-form-group">{
                    type : 'select',
                    name: 'incomplete_start_type',
                    class : 'text-center',
                    options : [
                        {text : 'Hour(s)', value : 60},
                        {text : 'Minute(s)', value : 1}
                    ],
                    style : 'width: 90px;',
                    group_class : 'incomplete-start-type'
                }</template>
                <template class="wlm3-form-group">{
                    addon_left : 'Send Every',
                    type : 'text',
                    name: 'incomplete_send_every',
                    addon_right: 'Hours',
                    style : 'width: 60px;',
                    class : 'text-center',
                }</template>
                <template class="wlm3-form-group">{
                    addon_left : 'Total Sent',
                    type : 'text',
                    name: 'incomplete_howmany',
                    style : 'width: 60px;',
                    class : 'text-center',
                }</template>
                <div class="np-help"><?php $this->tooltip(__('First Sent After:<br> The first Incomplete Registration Email will be sent after the set time. Example: If this is set to 1 Hour, the email would be sent 1 Hour after the member attempted to register but did not complete the registration process.<br><br>Send Every:<br>The time interval each additional Incomplete Registration Email will be sent after the first email is sent. Example: If this is set to 24 Hours, the next email(s) would be sent in 24 Hour intervals after the first Incomplete Registration Email was sent.<br><br>Total Sent:<br>The Total number of Incomplete Registration Emails to be sent. Example: If this is set to 3, a total number of 3 emails would be sent at the scheduled intervals. The initial email would be sent followed by two additional emails for a total of 3.<br><br>Note:<br> Messages are scheduled with the WP Cron built into WordPress. The WP Cron does not support specific time. For example, 1 hour may be slightly more or slightly less than 60 minutes. Decimals can be used to set a time less than 1 hour. Example: 0.5 equals 30 minutes and 0.25 equals 15 minutes.', 'wishlist-member'), 'lg'); ?></div>
            </div>
            <br style="clear:both"><hr style="margin-top:0">
        </div>
    </div>
    <div class="row">
        <template class="wlm3-form-group">{
            addon_left: 'Sender Name',
            group_class : '-label-addon mb-2',
            type: 'text',
            name: 'incomplete_sender_name',
            column : 'col-md-6'
        }</template>
        <template class="wlm3-form-group">{
            addon_left: 'Sender Email',
            group_class : '-label-addon mb-2',
            type: 'text',
            name: 'incomplete_sender_email',
            column : 'col-md-6'
        }</template>
        <template class="wlm3-form-group">{
            addon_left: 'Subject',
            group_class : '-label-addon mb-2',
            type: 'text',
            name: 'incomplete_subject',
            column : 'col-md-12',
            class: 'email-subject'
        }</template>
        <template class="wlm3-form-group">{
            name: 'incomplete_message',
            type: 'textarea',
            class : 'richtext',
            column : 'col-md-12',
            group_class : 'mb-2',
        }</template>
        <div class="col-md-12">
            <button class="btn -default -condensed email-reset-button" data-target="incomplete">Reset to Default</button>
            <template class="wlm3-form-group">{
                type : 'select',
                column : 'col-md-5 pull-right no-margin no-padding',
                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                group_class : 'shortcode_inserter mb-0',
                style : 'width: 100%',
                options : get_merge_codes([{value : '[incregurl]', text : '<?php echo esc_js(__('Incomplete Registration URL', 'wishlist-member')); ?>'}]),
                grouped: true,
                class : 'insert_text_at_caret',
                'data-target' : '[name=incomplete_message]'
            }</template>
        </div>
    </div>
</div>

<style type="text/css">
    .form-group.incomplete-start-type {
        display: none;
    }
</style>
<script type="text/javascript">
    $(function() {
        $('.incomplete-start-type').contents().appendTo('.incomplete-start .input-group');
    });
</script>
