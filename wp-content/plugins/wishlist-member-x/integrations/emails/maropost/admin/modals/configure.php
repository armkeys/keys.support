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
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('Account ID', 'wishlist-member')); ?>', type : 'text', name : 'account_id', column : 'col-md-12', tooltip : '<?php esc_attr_e('Account ID is located in Account > API Documentation', 'wishlist-member'); ?>'}</template>
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('Auth Token', 'wishlist-member')); ?>', type : 'text', name : 'auth_token', column : 'col-md-12', tooltip : '<?php esc_attr_e('Auth Token is located in Account > Accounts Page', 'wishlist-member'); ?>'}</template>
            <!-- <div class="col-md-2">
                <label>&nbsp;</label>
                <a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect', 'wishlist-member'); ?></span></a>
            </div> -->
        </div>
    </div>
</div>
