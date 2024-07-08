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
                <p><?php esc_html_e('API Credentials are located in the Mad Mimi account under Account > API', 'wishlist-member'); ?></p>
            </div>
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('Username/Email', 'wishlist-member')); ?>', type : 'text', name : 'username', column : 'col-md-12'}</template>
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('API Key', 'wishlist-member')); ?>', type : 'text', name : 'api_key', column : 'col-md-12'}</template>
            <!-- <div class="col-md-2">
                <label>&nbsp;</label>
                <a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect', 'wishlist-member'); ?></span></a>
            </div> -->
        </div>
    </div>
</div>
