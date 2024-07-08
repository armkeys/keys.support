<div
    id="additional-marketing-consent-markup"
    data-id="additional-marketing-consent-modal"
    data-label="additional-marketing-consent-modal"
    data-title="Configure Additional Marketing Consent"
    data-classes="modal-md"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Consent Checkbox Text', 'wishlist-member')); ?>',
                    name : 'privacy_consent_to_market_text',
                    type : 'textarea',
                    value : <?php echo json_encode($this->get_option('privacy_consent_to_market_text')); ?>,
                    column: 'col-md-12'
                }
            </template>
            <div class="col-md-12">
                <label><?php esc_html_e('Consent Checkbox affects the following:', 'wishlist-member'); ?></label>
            </div>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Email Broadcast', 'wishlist-member')); ?>',
                    name : 'privacy_consent_affects_emailbroadcast',
                    type : 'checkbox',
                    value : 1,
                    uncheck_value : 0,
                    checked_value : '<?php echo esc_js($this->get_option('privacy_consent_affects_emailbroadcast')); ?>',
                    column: 'col-md-12'
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Email Provider', 'wishlist-member')); ?>',
                    name : 'privacy_consent_affects_autoresponder',
                    type : 'checkbox',
                    value : 1,
                    uncheck_value : 0,
                    checked_value : '<?php echo esc_js($this->get_option('privacy_consent_affects_emailbroadcast')); ?>',
                    column: 'col-md-12'
                }
            </template>
        </div>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>
