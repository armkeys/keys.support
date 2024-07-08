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
                <p>
                    <?php esc_html_e('The Sendlane API Key, API Hash Key and Sendlane Subdomain can be found in the Account > Settings > API section of the Sendlane site.', 'wishlist-member'); ?>
                </p>
            </div>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Key', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'api_key',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__(' Enter the API Key found in the Account > Settings > API section of the Sendlane site.', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Hash', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'api_hash',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('Enter the API Hash Key found in the Account > Settings > API section of the Sendlane site.', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Subdomain', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'subdomain',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('Enter the Sendlane Subdomain found in the Account > Settings > API section of the Sendlane site.', 'wishlist-member')); ?>',
                }
            </template>
            <!-- <div class="col-md-3">
                <label>&nbsp;</label>
                <a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect', 'wishlist-member'); ?></span></a>
            </div> -->
        </div>
    </div>
</div>
