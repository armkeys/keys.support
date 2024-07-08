<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template"
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <input type="hidden" class="-url" name="plugnpaidthankyou" />
        <div class="row">
            <?php echo wp_kses_post($api_status_markup); ?>
        </div>
        <div class="row -integration-keys">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Token', 'wishlist-member')); ?>',
                    name : 'plugnpaidapikey',
                    column : 'col-md-12',
                    tooltip: '<?php echo esc_js(__('API Token located in the Settings > API Settings section when logged into PlugnPaid site.', 'wishlist-member')); ?>',
                }
            </template>
        </div>
    </div>
</div>
