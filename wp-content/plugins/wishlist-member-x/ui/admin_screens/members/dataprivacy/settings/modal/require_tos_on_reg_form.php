<div
    id="require-tos-on-regform-markup" 
    data-id="require-tos-on-regform-modal"
    data-label="require-tos-on-regform-modal"
    data-title="Configure Terms of Service Agreement"
    data-classes="modal-md"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Terms of Service Checkbox Text', 'wishlist-member')); ?>',
                    name : 'privacy_require_tos_checkbox_text',
                    type : 'textarea',
                    value : <?php echo json_encode(htmlentities($this->get_option('privacy_require_tos_checkbox_text'))); ?>,
                    column: 'col-md-12'
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Error Message', 'wishlist-member')); ?>',
                    name : 'privacy_require_tos_error_message',
                    type : 'textarea',
                    value : <?php echo json_encode($this->get_option('privacy_require_tos_error_message')); ?>,
                    column: 'col-md-12'
                }
            </template>
        </div>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>
