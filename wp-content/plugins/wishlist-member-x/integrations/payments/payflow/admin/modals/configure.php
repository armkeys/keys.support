<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template" 
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <p><a href="https://manager.paypal.com" target="_blank">Click here </a> <?php esc_html_e('to get the Paypal Manager User and Password, and then go to Account Administrator > Manage Users.', 'wishlist-member'); ?></p>
            </div>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Manager Username', 'wishlist-member')); ?>',
                    name : 'payflowsettings[live][api_username]',
                    column : 'col-md-6',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Manager Password', 'wishlist-member')); ?>',
                    name : 'payflowsettings[live][api_password]',
                    column : 'col-md-6',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Manager Merchant Name', 'wishlist-member')); ?>',
                    name : 'payflowsettings[live][merchant_name]',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('To get your Merchant Name go to Paypal Manager > Account Administration > Company Information.', 'wishlist-member')); ?>',
                }
            </template>
        </div>
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Payflow Testing', 'wishlist-member')); ?>',
                    name : 'payflowsettings[sandbox_mode]',
                    id : 'payflow-enable-sandbox',
                    value : 1,
                    uncheck_value : 0,
                    type : 'checkbox',
                    column : 'col-md-12 mb-2',
                }
            </template>
        </div>
        <div class="row" id="payflow-sandbox-settings" style="display:none">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Sandbox API Username', 'wishlist-member')); ?>',
                    name : 'payflowsettings[sandbox][api_username]',
                    column : 'col-md-6',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Sandbox API Password', 'wishlist-member')); ?>',
                    name : 'payflowsettings[sandbox][api_password]',
                    column : 'col-md-6',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Sandbox API Signature', 'wishlist-member')); ?>',
                    name : 'payflowsettings[sandbox][merchant_name]',
                    column : 'col-md-12',
                }
            </template>
        </div>
    </div>
</div>
