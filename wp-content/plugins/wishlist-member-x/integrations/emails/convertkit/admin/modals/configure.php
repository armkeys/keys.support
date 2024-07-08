<div data-process="modal" id="configure-<?php echo esc_attr($config['id']); ?>-template" data-id="configure-<?php echo esc_attr($config['id']); ?>" data-label="configure-<?php echo esc_attr($config['id']); ?>" data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1" style="display:none">
    <div class="body">
        <div class="row -integration-keys">
            <?php echo wp_kses_post($api_status_markup); ?>
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('API Secret', 'wishlist-member')); ?>',
                type : 'text',
                name : 'ckapi',
                column : 'col-md-12',
                help_block : '<?php printf(esc_js(/* Translators: %s = API Secret link */ __('Copy the %s from the Settings > Advanced > API section of ConvertKit and paste it into the field.', 'wishlist-member')), '<a href="https://app.convertkit.com/account_settings/advanced_settings" target="_blank">' . esc_js(__('API Secret', 'wishlist-member')) . '</a>'); ?>',
                tooltip : '<?php echo esc_js(__('Note: The API Secret is required (Not the API Key). The API Secret is located in Settings > Advanced > API section when logged into ConvertKit site.', 'wishlist-member')); ?>'
                }
            </template>
        </div>
    </div>
</div>
