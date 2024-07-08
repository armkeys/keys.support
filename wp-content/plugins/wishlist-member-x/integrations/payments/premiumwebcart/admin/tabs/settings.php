<form>
    <div class="row">
        <div class="col-md-12 mb-4"><?php echo wp_kses_post($config_button); ?></div>
    </div>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Post / Callback URL', 'wishlist-member')); ?>',
                name : 'pwcthankyou',
                addon_left : '<?php echo esc_js($wpm_scregister); ?>',
                column : 'col-md-auto',
                class : 'text-center -url',
                group_class : '-url-group',
                help_block : '<?php echo esc_js(__('The Post/Callback URL is used in the CloudNet360 integraton and thank you pages.', 'wishlist-member')); ?>',
                tooltip : '<?php echo esc_js(__('The end string of the displayed Post URL can be edited if desired. Note that this Post URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member')); ?>',
                tooltip_size : 'lg',
            }
        </template>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</form>
