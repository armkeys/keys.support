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
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Login ID', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Enter the API Login ID found in the the Account > Settings > API Credentials & Keys section when logged into the Merchant Profile in Authorize.net', 'wishlist-member')); ?>',
                    name : 'anloginid',
                    column : 'col-md-12',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Trasaction Key', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Enter the Transaction Key found in the Account > Settings > API Credentials & Keys section when logged into the Merchant Profile in Authorize.net', 'wishlist-member')); ?>',
                    name : 'antransid',
                    column : 'col-md-12',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Signature Key', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Enter the Signature Key found in the Account > Settings > API Credentials & Keys section when logged into the Merchant Profile in Authorize.net', 'wishlist-member')); ?>',
                    name : 'anmd5hash',
                    column : 'col-md-12',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Sandbox Testing', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('If enabled, Sandbox Testing can be used for the integration.', 'wishlist-member')); ?>',
                    name : 'anetsandbox',
                    value : 1,
                    uncheck_value : 0,
                    type : 'checkbox',
                    column : 'col-md-12',
                }
            </template>
        </div>
    </div>
</div>
