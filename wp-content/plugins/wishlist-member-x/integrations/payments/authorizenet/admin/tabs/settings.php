<form>
    <div class="row">
        <div class="col-md-12 mb-4"><?php echo wp_kses_post($config_button); ?></div>
    </div>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Thank You URL', 'wishlist-member')); ?>',
                name : 'anthankyou',
                addon_left : '<?php echo esc_js($wpm_scregister); ?>',
                column : 'col-md-auto',
                class : 'text-center -url',
                group_class : '-url-group',
                tooltip : '<?php echo esc_js(__('The end string of the displayed Thank You URL can be edited if desired. Note that this Thank You URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member')); ?>',
                tooltip_size : 'lg',
                help_block : '<?php echo esc_js(__('Set the Thank You URL in Authorize.net to this URL.', 'wishlist-member')); ?>',
            }
        </template>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</form>
