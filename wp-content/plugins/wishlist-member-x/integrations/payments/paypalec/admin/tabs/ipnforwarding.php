<form>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Enter the URL(s) to be Forwarded to the IPN (one URL per line) ', 'wishlist-member')); ?>',
                column : 'col-md-9',
                value : <?php echo json_encode($data->paypalec_ipnforwarding); ?>,
                type : 'textarea',
                name : 'paypalec_ipnforwarding',
                placeholder : '<?php echo esc_js(__('https://...', 'wishlist-member')); ?>',
                group_class : 'mb-2',
                tooltip : '<p><?php echo esc_js(__(' IPN Forwarding is an optional feature for those who want to use one PayPal account on multiple WishList Member sites. You can paste one or more IPN URLs into the field below if you want to use your PayPal account on more than one WishList Member site. This option can be ignored if you are only using your PayPal account on one WishList Member site.', 'wishlist-member')); ?></p>',
                tooltip_size : 'lg',
            }
        </template>
        <div class="col-md-12 mb-4">
            <button type="button" class="save-button btn -primary" data-lpignore="true">
                <i class="wlm-icons">save</i>
                <span>Save</span>
            </button>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save_payment_provider" />
</form>
