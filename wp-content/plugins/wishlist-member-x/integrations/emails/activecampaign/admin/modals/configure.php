<div data-process="modal" id="configure-<?php echo esc_attr($config['id']); ?>-template" data-id="configure-<?php echo esc_attr($config['id']); ?>" data-label="configure-<?php echo esc_attr($config['id']); ?>" data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1" style="display:none">
    <div class="body">
        <div class="row -integration-keys">
            <div class="col-md-12">
                <p><?php esc_html_e('API Credentials are located in your ActiveCampaign account under:', 'wishlist-member'); ?><br>My Settings &gt; Developer</p>
            </div>
            <?php echo wp_kses_post($api_status_markup); ?>
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('API URL', 'wishlist-member')); ?>',
                type : 'text',
                name : 'api_url',
                column : 'col-md-12'
                }
            </template>
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('API Key', 'wishlist-member')); ?>',
                type : 'text',
                name : 'api_key',
                column : 'col-md-12'
                }
            </template>
        </div>
    </div>
</div>
