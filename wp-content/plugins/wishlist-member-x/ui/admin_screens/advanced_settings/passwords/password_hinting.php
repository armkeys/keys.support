<?php
    $email_address = $this->get_option('password_hint_email_address');
    $email_address = $email_address ? $email_address : $this->get_option('email_sender_address');
    $email_name    = $this->get_option('password_hint_email_name');
    $email_name    = $email_name ? $email_name : $this->get_option('email_sender_name');
    $email_subject = $this->get_option('password_hint_email_subject');
    $email_body    = $this->get_option('password_hint_email_message');
?>
<div class="row">
    <template class="wlm3-form-group">{
        addon_left : 'Sender Name',
        group_class : '-label-addon mb-2',
        type : 'text',
        name : 'password_hint_email_name',
        column: 'col-md-6',
        value: '<?php echo esc_js($email_name); ?>'
    }</template>
    <template class="wlm3-form-group">{
        addon_left : 'Sender Email',
        group_class : '-label-addon mb-2',
        type : 'text',
        name : 'password_hint_email_address',
        column: 'col-md-6',
        value: '<?php echo esc_js($email_address); ?>'
    }</template>
    <template class="wlm3-form-group">{
        addon_left : 'Subject',
        group_class : '-label-addon mb-2',
        type : 'text',
        name : 'password_hint_email_subject',
        column: 'col-md-12',
        value: '<?php echo esc_js($email_subject); ?>',
        class: 'email-subject'
    }</template>
    <div class="col-md-12">
        <div class="form-group mb-2">
            <textarea class="form-control email-editor" data-name="password_hint_email_message" name="password_hint_email_message" id="password_hint_email_message" skip-save="1"><?php echo esc_textarea($email_body); ?></textarea>
        </div>
    </div>
    <div class="col-md-12">
        <button class="btn -default -condensed email-reset-button" data-target="password_hint_email"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
        <template class="wlm3-form-group">{
            type : 'select',
            column : 'col-md-5 pull-right no-margin no-padding',
            'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
            group_class : 'shortcode_inserter mb-0',
            style : 'width: 100%',
            options : get_merge_codes([{value : '[password]', text : 'Password'}]),
            grouped: true,
            class : 'insert_text_at_caret',
            'data-target' : '[name=password_hint_email_message]',
        }</template>
    </div>
</div>
