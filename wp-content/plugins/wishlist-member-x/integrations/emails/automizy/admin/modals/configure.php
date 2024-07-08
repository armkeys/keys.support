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
                <p><?php esc_html_e('Your API Settings is located in your Account > Account Settings', 'wishlist-member'); ?></p>
            </div>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Token', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'api_key',
                    column : 'col-md-12',
                }
            </template>
        </div>
    </div>
</div>
