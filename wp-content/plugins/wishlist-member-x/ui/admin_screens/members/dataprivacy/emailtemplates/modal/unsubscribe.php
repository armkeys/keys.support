<div
    id="data-privacy_unsubscribe_markup" 
    data-id="data-privacy_unsubscribe"
    data-label="data-privacy_unsubscribe"
    data-title="Unsubscribe Notification Email"
    data-classes="modal-lg"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Notify a member when they are unsubscribed from Email Broadcast mailing list', 'wishlist-member')); ?>',
                        name : 'member_unsub_notification',
                        checked_value : '<?php echo esc_js($this->get_option('member_unsub_notification')); ?>',
                        type : 'toggle-switch',
                        value: 1,
                        uncheck_value: 0,
                    }
                </template>
                <br style="clear:both"><hr style="margin-top:0">
            </div>
        </div>
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label: '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    type: 'text',
                    name: 'member_unsub_notification_subject',
                    group_class: '-label-addon mb-2',
                    column : 'col-md-12',
                    class: 'email-subject'
                }
            </template>
            <template class="wlm3-form-group">
                {
                    name: 'member_unsub_notification_body',
                    type: 'richtext',
                    group_class: 'mb-2',
                    column : 'col-md-12'            
                }
            </template>
            <div class="col-md-12">
                <button class="btn -default -condensed email-reset-button" data-target="member_unsub_notification"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
                <template class="wlm3-form-group">
                    {
                        type : 'select',
                        column : 'col-md-5 pull-right no-margin no-padding',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter',
                        style : 'width: 100%',
                        options : get_merge_codes([{value : '[incregurl]', text : '<?php echo esc_js(__('Incomplete Registration URL', 'wishlist-member')); ?>'}]),
                        grouped: true,
                        class : 'insert_text_at_caret',
                        'data-target' : '[name=member_unsub_notification_body]'
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>
