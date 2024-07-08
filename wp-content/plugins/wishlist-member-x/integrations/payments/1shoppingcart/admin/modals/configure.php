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
                    label : '<?php echo esc_js(__('Merchant ID', 'wishlist-member')); ?>',
                    name : 'onescmerchantid',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__(' Enter the Merchant ID found by clicking the User icon on the top right when logged into the 1ShoppingCart site.', 'wishlist-member')); ?>',
                    tooltip_size : 'md',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Key', 'wishlist-member')); ?>',
                    name : 'onescapikey',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('Enter the API Key found on the top of the My Account > API > API Settings section when logged into the 1ShoppingCart.', 'wishlist-member')); ?>',
                    tooltip_size : 'md',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Retry Grace Period', 'wishlist-member')); ?>',
                    name : 'onescgraceperiod',
                    column : 'col-md-5',
                    tooltip : '<?php echo esc_js(__('The number of days between failed recurring payment attempts. This is set for 3 days by default.', 'wishlist-member')); ?>',
                    tooltip_size : 'md',
                    type: 'number',
                }
            </template>
            <div class="col-md-6">
                <label>&nbsp;</label>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Process Upsells', 'wishlist-member')); ?>',
                        name : 'onesc_include_upsells',
                        value : 1,
                        uncheck_value : 0,
                        type : 'checkbox',
                        tooltip : '<?php echo esc_js(__(' If enabled, Upsells in 1ShoppingCart related to membership levels will be processed. If there are any Upsells configured in the 1ShoppingCart purchase process integrated with a membership level. Example: If a user purchases access to the Silver Level and purchases the Upsell to Gold Level, WishList Member will add the user to both Silver Level and Gold Level.', 'wishlist-member')); ?>',
                        tooltip_size : 'lg',
                    }
                </template>
            </div>
        </div>
    </div>
</div>
