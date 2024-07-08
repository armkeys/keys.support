<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template"
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    data-classes="modal-lg"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#paypalec-connect"><?php esc_html_e('API', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#paypalec-spb"><?php esc_html_e('Smart Payment Buttons', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane active in" id="paypalec-connect">
                <div class="row">
                    <div class="col-md-12">
                        <p><a href="https://www.paypal.com/businessprofile/mytools/apiaccess/firstparty/signature" target="paypal-api-get-signature" onclick="window.open(this.href, 'paypal-api-get-signature', 'height=500,width=360')"><?php esc_html_e('Click here to get your live PayPal API credentials', 'wishlist-member'); ?></a></p>
                    </div>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('API Username', 'wishlist-member')); ?>',
                            name : 'paypalecsettings[live][api_username]',
                            column : 'col-md-6',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('API Password', 'wishlist-member')); ?>',
                            name : 'paypalecsettings[live][api_password]',
                            column : 'col-md-6',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('API Signature', 'wishlist-member')); ?>',
                            name : 'paypalecsettings[live][api_signature]',
                            column : 'col-md-12',
                        }
                    </template>
                </div>
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Enable Sandbox Testing', 'wishlist-member')); ?>',
                            name : 'paypalecsettings[sandbox_mode]',
                            id : 'paypalec-enable-sandbox',
                            value : 1,
                            uncheck_value : 0,
                            type : 'checkbox',
                            column : 'col-md-12 mb-2',
                            tooltip : '<p><?php echo esc_js(__('The Sandbox Testing option should be disabled unless you are using a PayPal Sandbox account for testing. If you have the PayPal Sandbox Testing option enabled, the purchase/registration process will not work on the live site. PayPal Sandbox Testing is typically only used to test the purchase/registration process before making it live. More details on using PayPal Sandbox are available on their site.', 'wishlist-member')); ?></p>',
                            tooltip_size : 'lg',
                        }
                    </template>
                </div>
                <div class="col-md-12">
                    <p><a href="https://www.sandbox.paypal.com/businessprofile/mytools/apiaccess/firstparty/signature" target="sandbox-api-get-signature" onclick="window.open(this.href, 'sandbox-api-get-signature', 'height=500,width=360');"><?php esc_html_e('Click here to get your Sandbox PayPal API credentials', 'wishlist-member'); ?></a></p>
                </div>
                <div class="row" id="paypalec-sandbox-settings" style="display:none">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Sandbox API Username', 'wishlist-member')); ?>',
                            name : 'paypalecsettings[sandbox][api_username]',
                            column : 'col-md-6',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Sandbox API Password', 'wishlist-member')); ?>',
                            name : 'paypalecsettings[sandbox][api_password]',
                            column : 'col-md-6',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Sandbox API Signature', 'wishlist-member')); ?>',
                            name : 'paypalecsettings[sandbox][api_signature]',
                            column : 'col-md-12',
                        }
                    </template>
                </div>
            </div>
            <div class="tab-pane" id="paypalec-spb">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Use Smart Payment Buttons', 'wishlist-member')); ?>',
                            name : 'paypalec_spb[enable]',
                            column : 'col-md-12',
                            type : 'checkbox',
                            value : '1',
                            uncheck_value : '0',
                            tooltip : '<?php echo esc_js(__('PayPal Smart Payment Buttons provides a simplified checkout by automatically presenting the most relevant payment types to Users.The payment methods presented can depend on your location.', 'wishlist-member')); ?>',
                        }
                    </template>
                </div>
                <div class="row mt-4" id="paypalec-spb-settings" style="display: none">
                    <div class="col-6">
                        <div class="row">
                            <template class="wlm3-form-group">
                                {
                                    type : 'select',
                                    options : [
                                        { value : 'vertical', text : '<?php esc_attr_e('Vertical', 'wishlist-member'); ?>' },
                                        { value : 'horizontal', text : '<?php esc_attr_e('Horizontal', 'wishlist-member'); ?>' },
                                    ],
                                    name : 'paypalec_spb[layout]',
                                    label : '<?php echo esc_js(__('Layout', 'wishlist-member')); ?>',
                                    column : 'col-md-6',
                                    style : 'width: 100%',
                                    group_class : 'mb-2',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    type : 'select',
                                    options : [
                                        { value : 'medium', text : '<?php esc_attr_e('Medium', 'wishlist-member'); ?>' },
                                        { value : 'large', text : '<?php esc_attr_e('Large', 'wishlist-member'); ?>' },
                                        { value : 'responsive', text : '<?php esc_attr_e('Responsive', 'wishlist-member'); ?>' },
                                    ],
                                    name : 'paypalec_spb[size]',
                                    label : '<?php echo esc_js(__('Size', 'wishlist-member')); ?>',
                                    column : 'col-md-6',
                                    style : 'width: 100%',
                                    group_class : 'mb-2',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    type : 'select',
                                    options : [
                                        { value : 'pill', text : '<?php esc_attr_e('Pill', 'wishlist-member'); ?>' },
                                        { value : 'rect', text : '<?php esc_attr_e('Rectangle', 'wishlist-member'); ?>' },
                                    ],
                                    name : 'paypalec_spb[shape]',
                                    label : '<?php echo esc_js(__('Shape', 'wishlist-member')); ?>',
                                    column : 'col-md-6',
                                    style : 'width: 100%',
                                    group_class : 'mb-2',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    type : 'select',
                                    options : [
                                        { value : 'gold', text : '<?php esc_attr_e('Gold', 'wishlist-member'); ?>' },
                                        { value : 'blue', text : '<?php esc_attr_e('Blue', 'wishlist-member'); ?>' },
                                        { value : 'silver', text : '<?php esc_attr_e('Silver', 'wishlist-member'); ?>' },
                                        { value : 'white', text : '<?php esc_attr_e('White', 'wishlist-member'); ?>' },
                                        { value : 'black', text : '<?php esc_attr_e('Black', 'wishlist-member'); ?>' },
                                    ],
                                    name : 'paypalec_spb[color]',
                                    label : '<?php echo esc_js(__('Color', 'wishlist-member')); ?>',
                                    column : 'col-md-6',
                                    style : 'width: 100%',
                                    group_class : 'mb-2',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    type : 'select',
                                    options : [
                                        { value : 'CARD', text : '<?php esc_attr_e('Card', 'wishlist-member'); ?>' },
                                        { value : 'CREDIT', text : '<?php esc_attr_e('Credit', 'wishlist-member'); ?>' },
                                        { value : 'ELV', text : '<?php esc_attr_e('ELV', 'wishlist-member'); ?>' },
                                    ],
                                    name : 'paypalec_spb[funding]',
                                    label : '<?php echo esc_js(__('Allowed Funding Source', 'wishlist-member')); ?>',
                                    column : 'col-md-12',
                                    style : 'width: 100%',
                                    multiple : 'multiple',
                                }
                            </template>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div id="paypalec-spb-preview" class="d-inline-block mt-4"></div>
                        <div style="position:absolute;top:0;left:0;right:0;bottom:0;z-index:99999999"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
