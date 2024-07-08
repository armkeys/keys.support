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
                <p><a href="#icontact-api-instructions" class="hide-show"><?php esc_html_e('How to Setup Your iContact API', 'wishlist-member'); ?></a></p>
                <div class="panel d-none" id="icontact-api-instructions">
                    <div class="panel-body">
                        <ol style="list-style: decimal">
                            <li><p class="mb-0"><?php esc_html_e('Copy and paste the following into a new tab:', 'wishlist-member'); ?> <a href="https://app.icontact.com/icp/core/externallogin" target="_blank">https://app.icontact.com/icp/core/externallogin</a></p></li>
                            <li><p class="mb-0"><?php esc_html_e('Login with the iContact account Username and Password.', 'wishlist-member'); ?></p></li>
                            <li><p class="mb-0"><?php esc_html_e('Enter', 'wishlist-member'); ?> <mark><?php echo esc_html($data['icapiid']); ?></mark> <?php esc_html_e('as the Application ID field.', 'wishlist-member'); ?></p></li>
                            <li><p class="mb-0"><?php esc_html_e('Enter the desired Application Password.', 'wishlist-member'); ?></p></li>
                            <li><p class="mb-0"><?php esc_html_e('Click Save.', 'wishlist-member'); ?></p></li>
                        </ol>
                    </div>
                </div>
            </div>
            <input type="hidden" name="icapiid" />
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('iContact Username', 'wishlist-member')); ?>', type : 'text', name : 'icusername', column : 'col-md-12'}</template>
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('Application Password', 'wishlist-member')); ?>', type : 'text', name : 'icapipassword', column : 'col-md-12', tooltip : '<?php esc_attr_e('This is the password you created from Step 1 and not your iContact Password.', 'wishlist-member'); ?>', tooltip_size : 'lg'}</template>
            <!-- <div class="col-md-2">
                <label>&nbsp;</label>
                <a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect', 'wishlist-member'); ?></span></a>
            </div> -->
        </div>
    </div>
</div>
