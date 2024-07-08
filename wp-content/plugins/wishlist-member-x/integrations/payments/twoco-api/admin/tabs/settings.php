<div class="row">
    <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    <?php echo wp_kses_post($api_status_markup); ?>       
</div>
<div class="row api-required">
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('Thank You / Notification URL', 'wishlist-member')); ?>',
            name : 'twocheckoutapithankyouurl',
            addon_left : '<?php echo esc_js($wpm_scregister); ?>',
            column : 'col-md-auto',
            class : 'text-center -url',
            group_class : '-url-group',
            help_block : '<?php echo esc_js(__('Use this URL as the Instant Notification URL for 2Checkout.', 'wishlist-member')); ?>',
            tooltip : '<?php echo esc_js(__('The end string of the displayed Post URL can be edited if desired. Note that this Post URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member')); ?>',
            tooltip_size : 'lg',
        }
    </template>
</div>
