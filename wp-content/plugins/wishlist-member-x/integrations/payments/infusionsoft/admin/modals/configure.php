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
                    label : '<?php echo esc_js(__('App Name', 'wishlist-member')); ?>',
                    name : 'ismachine',
                    column : 'col-md-12',
                    addon_right : '.infusionsoft.com',
                    tooltip : '<?php echo esc_js(__('Example:', 'wishlist-member')); ?> <em>appname</em>.infusionsoft.com',
                    tooltip_size : 'md',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Encrypted Key', 'wishlist-member')); ?>',
                    name : 'isapikey',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('Legacy API Key is located under the Profile menu >> API Settings. <br><br> Note: Only Admin accounts can view the legacy API Key.', 'wishlist-member')); ?>',
                    tooltip_size : 'md',
                }
            </template>
            <!-- <div class="col-md-2">
                <label>&nbsp;</label>
                <a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect', 'wishlist-member'); ?></span></a>
            </div> -->
        </div>
    </div>
</div>
