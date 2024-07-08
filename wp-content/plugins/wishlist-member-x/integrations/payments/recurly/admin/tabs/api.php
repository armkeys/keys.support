<div class="row">
    <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    <?php echo wp_kses_post($api_status_markup); ?>       
</div>
<div class="row">
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('Push Notification URL', 'wishlist-member')); ?>',
            name : 'recurlythankyou',
            addon_left : '<?php echo esc_js($wpm_scregister); ?>',
            column : 'col-md-auto',
            class : 'text-center -url',
            group_class : '-url-group',
            tooltip : '<?php echo esc_js(__('The end string of the displayed Post URL can be edited if desired. Note that this Post URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member')); ?>',
            tooltip_size : 'lg',
            help_block : '<?php echo esc_js(__('Copy this link and paste it into Recurly as the Post Notification URL.', 'wishlist-member')); ?>',
        }
    </template>
</div>
