<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template"
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row -integration-keys">
            <?php echo wp_kses_post($api_status_markup); ?>
            <div class="col-md-12">
                <p>
                    <?php esc_html_e('The MailChimp API Key can be found in the Account > Extras > API Keys section when logged into the MailChimp site:', 'wishlist-member'); ?> <a href="http://admin.mailchimp.com/account/api/" target="_blank">http://admin.mailchimp.com/account/api/</a>
                </p>
            </div>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Key', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'mcapi',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('Enter the API Key found in the Account > Extras > API Keys section when logged into the MailChimp site.', 'wishlist-member')); ?>'
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Disable Double Opt-in', 'wishlist-member')); ?>',
                    name  : 'optin',
                    value : '1',
                    uncheck_value : '0',
                    type  : 'checkbox',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('Select if the MailChimp Double Opt-in option should be disabled. Note: Disabling the Double Opt-in could result in the MailChimp account being suspended if it is viewed as being abused by MailChimp.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="api_v3" value="1">
        </div>
    </div>
</div>
