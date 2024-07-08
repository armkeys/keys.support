<div class="content-wrapper -merge">
    <br>
    <div class="row">
        <?php $option_val = wishlistmember_instance()->get_option('anonymous_usage_tracking'); ?>
        <div class="col-md-7">
            <template class="wlm3-form-group">
                {
                    label : '<?php esc_html_e('Enable Anonymous Usage Tracking', 'wishlist-member'); ?>',
                    name  : 'anonymous_usage_tracking',
                    value : 'yes',
                    checked_value : '<?php echo 'yes' === $option_val ? 'yes' : 'no' ; ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip: '<?php esc_attr_e('Get improved features by sharing non-sensitive usage data that shows us how WishList Member is used. No personal data is tracked or stored.', 'wishlist-member'); ?>',
                    tooltip_size: 'lg'
                }   
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
</div>
