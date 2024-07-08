<div class="row">
    <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    <?php echo wp_kses_post($api_status_markup); ?>       
</div>
<div class="row">
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('Subscribers Changed Notification URL', 'wishlist-member')); ?>',
            readonly : 'readonly',
            column : 'col-md-12',
            class : 'copyable',
            value : WLM3ThirdPartyIntegration['spreedly'].spreedlythankyou_url,
            tooltip : '<?php echo esc_js(__('Subscribers Changed Notification URL is located in your Pin Payments account under', 'wishlist-member')); ?> <em><?php echo esc_js(__('Site Configuration > Subscribers Changed Notification URL', 'wishlist-member')); ?></em>',
            tooltip_size : 'lg',
            help_block : '<?php echo esc_js(__('Set the Subscribers Changed Notification URL in Pin Payments to this URL', 'wishlist-member')); ?>'
        }
    </template> 
</div>
