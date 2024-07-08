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
                <p><?php esc_html_e('The API Token is located in your Drip account in the Settings > User Settings > API Token section.', 'wishlist-member'); ?></p>
            </div>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Token', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Located in the Drip account in the Settings > User Settings > API Token section.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'apitoken',
                    column : 'col-md-12',
                }
            </template>
        </div>
    </div>
</div>
