<div class="row">
    <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    <?php echo wp_kses_post($api_status_markup); ?>       
</div>

<div class="row api-required">
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('Thank You URL / API Notification URL', 'wishlist-member')); ?>',
            name : 'plugnpaidthankyou',
            addon_left : '<?php echo esc_js($wpm_scregister); ?>',
            addon_right : '<?php echo false === strpos($wpm_scregister, '?') ? '?' : '&'; ?>plugnpaid_action=webhook',
            column : 'col-md-auto',
            class : 'text-center -url',
            group_class : '-url-group mb-1',
            help_block : '<?php printf(esc_js(/* Translators: %s link to plug&paid webhooks */ __('Copy and paste this URL into plug&paid at %s', 'wishlist-member')), '<a href="https://www.plugnpaid.com/app/settings/webhooks" target="_blank">https://www.plugnpaid.com/app/settings/webhooks</a>'); ?>',
        }
    </template>
`</div>

