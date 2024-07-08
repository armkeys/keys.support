<form>
    <div class="row">
        <?php include_once WLM_PLUGIN_DIR . '/integrations/payments/paypalec/assets/common.php'; ?>
        <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    </div>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Instant Payment Notification URL', 'wishlist-member')); ?>',
                column : 'col-md-6',
                class : 'copyable',
                readonly : 'readonly',
                value : '<?php echo esc_js(add_query_arg('action', 'ipn', $data->paypalecthankyou_url)); ?>',
                help_block : '<?php echo esc_js(__('Set the above URL as the Instant Payment Notification URL in the Account Settings > Website Payments > Instant Payment Notifications > Update section when logged into your PayPal account.', 'wishlist-member')); ?>',
                tooltip : '<p><?php echo esc_js(__('The Instant Payment Notification URL (IPN URL) is used to create the connection between WishList Member and PayPal.', 'wishlist-member')); ?></p>',
                tooltip_size : 'lg',
            }
        </template>
    </div>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Cancellation URL', 'wishlist-member')); ?>',
                name : 'paypalec_cancel_url',
                column : 'col-md-6',
                class : 'applycancel',
                tooltip : '<p><?php echo esc_js(__('The URL a member will be redirected to if they cancel their purchase on the PayPal Checkout Page.', 'wishlist-member')); ?></p><p><?php echo esc_js(__('The member will be redirected to the home page by default if no URL is set here.', 'wishlist-member')); ?></p>',
                tooltip_size : 'lg',
            }
        </template>
    </div>
    <input type="hidden" class="-url" name="paypalecthankyou" />
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</form>
