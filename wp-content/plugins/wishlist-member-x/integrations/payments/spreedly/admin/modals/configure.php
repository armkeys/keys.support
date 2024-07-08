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
                    label : '<?php echo esc_js(__('Short Site Name:', 'wishlist-member')); ?>',
                    name : 'spreedlyname',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('Short Site Name is located in your Pin Payments account under', 'wishlist-member')); ?> <em><?php echo esc_js(__('Pin Payments Site Configuration > Short Site Name', 'wishlist-member')); ?></em>',
                    tooltip_size : 'lg',
                }
            </template> 
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Authentication Token', 'wishlist-member')); ?>',
                    name : 'spreedlytoken',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('API Authentication Token is located in your Pin Payments account under', 'wishlist-member')); ?> <em><?php echo esc_js(__('Pin Payments Site Configuration > API Authentication Token', 'wishlist-member')); ?></em>',
                    tooltip_size : 'lg',
                }
            </template>
            <!-- <div class="col-md-2">
                <label>&nbsp;</label>
                <a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect', 'wishlist-member'); ?></span></a>
            </div> -->
        </div>
    </div>
</div>
