<div
    id="data-privacy_download-fulfilled_markup" 
    data-id="data-privacy_download-fulfilled"
    data-label="data-privacy_download-fulfilled"
    data-title="Download Fulfilled Email"
    data-classes="modal-lg"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label: '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    type: 'text',
                    name: 'privacy_email_template_download_subject',
                    group_class: '-label-addon mb-2',
                    column : 'col-md-12',
                    class: 'email-subject'
                }
            </template>
            <template class="wlm3-form-group">
                {
                    name: 'privacy_email_template_download',
                    type: 'richtext',
                    group_class: 'mb-2',
                    column : 'col-md-12'
                }
            </template>
            <div class="col-md-12">
                <button class="btn -default -condensed email-reset-button" data-target="privacy_email_template_download"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
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
                        'data-target' : '[name=privacy_email_template_download]'
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>
