<div class="row">
    <template class="wlm3-form-group">
        {
            type : 'url',
            name : 'WLMiDev[wlm_idevurl]',
            column : 'col-md-8 pr-0',
            class : 'applycancel',
            label : '<?php echo esc_js(__('iDevAffiliate URL', 'wishlist-member')); ?>',
            placeholder : '<?php echo esc_js(__('https://', 'wishlist-member')); ?>',
            help_block : '<?php echo esc_js(__('Example: https://www.yoursite.com/idevaffiliate/', 'wishlist-member')); ?>',
            tooltip : '<?php echo esc_js(__('Enter the URL iDev is installed on. The iDevAffiliate URL can be found in the System Settings > General Settings > Location Settings section of the iDev site when logged into the site.', 'wishlist-member')); ?>',
        }
    </template>
</div>
