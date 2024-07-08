<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template"
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <input type="hidden" id="twoco-api-vendor-id" name="twocovendorid">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#twocoapi-connect"><?php esc_html_e('API', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#twocoapi-settings"><?php esc_html_e('Settings', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#twocoapi-form"><?php esc_html_e('Payment Form', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane active in" id="twocoapi-connect">
                <div class="row">
                    <?php echo wp_kses_post($api_status_markup); ?>
                </div>
                <div class="row -integration-keys">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Merchant Code', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[twocheckoutapi_seller_id]',
                            id : 'twoco-api-seller-id',
                            column : 'col-6',
                            tooltip : '<?php echo esc_js(__('Merchant Code located in the Integrations > Webhooks & API section when logged into 2Checkout site.', 'wishlist-member')); ?>',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Publishable Key', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[twocheckoutapi_publishable_key]',
                            column : 'col-12',
                            tooltip : '<?php echo esc_js(__('Publishable Key located in the Integrations > Webhooks & API section when logged into 2Checkout site.', 'wishlist-member')); ?>',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Private Key', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[twocheckoutapi_private_key]',
                            column : 'col-12',
                            tooltip : '<?php echo esc_js(__('Private Key located in the Integrations > Webhooks & API section when logged into 2Checkout site.', 'wishlist-member')); ?>',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Enable Sandbox Mode', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[twocheckoutapi_sandbox]',
                            value : 1,
                            uncheck_value : 0,
                            type : 'checkbox',
                            column : 'col-md-12',
                        }
                    </template>
                </div>
            </div>
            <div class="tab-pane" id="twocoapi-settings">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Primary Currency', 'wishlist-member')); ?>',
                            type : 'select',
                            name : 'twocheckoutapisettings[currency]',
                            options : WLM3ThirdPartyIntegration['twoco-api'].currencies,
                            style : 'width: 100%',
                            column : 'col-6',
                            tooltip : '<?php echo esc_js(__('Select the preferred currency type. This is the currency users will pay during registration.', 'wishlist-member')); ?>'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Support Email', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[supportemail]',
                            column : 'col-12',
                            tooltip : '<?php echo esc_js(__('Set the preferred email address for support. This is the email address to receive support requests related to purchases.', 'wishlist-member')); ?>'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Secret Key', 'wishlist-member')); ?>',
                            name : 'twocosecret',
                            column : 'col-6',
                            tooltip : '<?php echo esc_js(__('Secret Key located in the Integrations > Webhooks & API section when logged into 2Checkout site.', 'wishlist-member')); ?>',
                        }
                    </template>
                </div>
            </div>
            <div class="tab-pane" id="twocoapi-form">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Heading', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[formheading]',
                            column : 'col-12',
                            tooltip : '<?php echo esc_js(__('The top of the form will display the text in this field. The default message is: Register for %level. Note: %level will be replaced by the name of the membership level on the live site.', 'wishlist-member')); ?>',
                            tooltip_size: 'lg',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Heading Logo', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[logo]',
                            column : 'col-12',
                            type : 'wlm3media',
                            tooltip : '<?php echo esc_js(__('An image can be shown on the top of the form.', 'wishlist-member')); ?>',
                            tooltip_size: 'lg',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Button Label', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[buttonlabel]',
                            column : 'col-6',
                            tooltip : '<?php echo esc_js(__('The button that is inserted on the page. The popup payment form will appear when a user clicks this button. The default message is: Join %level. Note: %level will be replaced by the name of the membership level on the live site.', 'wishlist-member')); ?>',
                            tooltip_size: 'lg',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Panel Button Label', 'wishlist-member')); ?>',
                            name : 'twocheckoutapisettings[panelbuttonlabel]',
                            column : 'col-6',
                            tooltip : '<?php echo esc_js(__('The button within the popup payment form. A user clicks this button to pay. The default message is: Pay.', 'wishlist-member')); ?>',
                            tooltip_size: 'lg',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
