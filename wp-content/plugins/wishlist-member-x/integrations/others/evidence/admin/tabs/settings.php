<div class="row">
    <template class="wlm3-form-group">
        {
            type : 'url',
            name : 'evidence_settings[webhook_url]',
            column : 'col-md-8 pr-0',
            class : 'applycancel',
            label : '<?php echo esc_js(__('Default Webhook URL', 'wishlist-member')); ?>',
            tooltip : '<?php echo esc_js(__('The Webhook URL can be created in the Sources section of the Evidence site.', 'wishlist-member')); ?>',
            placeholder : '<?php echo esc_js(__('https://', 'wishlist-member')); ?>',
        }
    </template>
    <div class="col-auto pr-0">
            <label>&nbsp;</label>
            <button class="btn d-block -default -condensed evidence-test-webhook"><?php esc_html_e('Test', 'wishlist-member'); ?></button>
    </div>
</div>
