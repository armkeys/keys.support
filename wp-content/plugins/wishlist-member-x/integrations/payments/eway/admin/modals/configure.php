<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template" 
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <input type="hidden" class="-url" name="ewaythankyou">
        <div class="row">
            <div class="col-md-12">     
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#eway-connect"><?php esc_html_e('API', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#eway-settings"><?php esc_html_e('Settings', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#eway-form"><?php esc_html_e('Payment Form', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane active in" id="eway-connect">
                <div class="row">
                    <?php echo wp_kses_post($api_status_markup); ?>       
                </div>
                <div class="row -integration-keys">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Customer ID', 'wishlist-member')); ?>',
                            name : 'ewaysettings[eway_customer_id]',
                            id : 'eway-seller-id',
                            column : 'col-12',
                            tooltip : '<?php echo esc_js(__('Use 87654321 for testing purposes', 'wishlist-member')); ?>'
                        }
                    </template> 
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Username', 'wishlist-member')); ?>',
                            name : 'ewaysettings[eway_username]',
                            column : 'col-12',
                            tooltip : '<?php echo esc_js(__('Use test@eway.com.au for testing purposes', 'wishlist-member')); ?>'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('API Password', 'wishlist-member')); ?>',
                            name : 'ewaysettings[eway_password]',
                            type : 'password',
                            column : 'col-12',
                            tooltip : '<?php echo esc_js(__('Use test123 for testing purposes', 'wishlist-member')); ?>'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Enable Sandbox Mode', 'wishlist-member')); ?>',
                            name : 'ewaysettings[eway_sandbox]',
                            value : 1,
                            uncheck_value : 0,
                            type : 'checkbox',
                            column : 'col-12',
                        }
                    </template>
                </div>
            </div>
            <div class="tab-pane" id="eway-settings">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Support Email', 'wishlist-member')); ?>',
                            name : 'ewaysettings[supportemail]',
                            column : 'col-12',
                        }
                    </template>
                </div>
            </div>
            <div class="tab-pane" id="eway-form">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Heading', 'wishlist-member')); ?>',
                            name : 'ewaysettings[formheading]',
                            column : 'col-12',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Heading Logo', 'wishlist-member')); ?>',
                            name : 'ewaysettings[logo]',
                            column : 'col-12',
                            type : 'wlm3media'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Button Label', 'wishlist-member')); ?>',
                            name : 'ewaysettings[buttonlabel]',
                            column : 'col-6',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Panel Button Label', 'wishlist-member')); ?>',
                            name : 'ewaysettings[panelbuttonlabel]',
                            column : 'col-6',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
