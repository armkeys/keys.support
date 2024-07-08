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
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Recurly Private API Key', 'wishlist-member')); ?>',
                    name : 'recurlyapikey',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('The Private API key can be found in Recurly under', 'wishlist-member')); ?> <em><?php echo esc_js(__('Integrations > API Credentials.', 'wishlist-member')); ?></em>',
                    tooltip_size : 'lg',
                }
            </template>
        </div>
    </div>
</div>
