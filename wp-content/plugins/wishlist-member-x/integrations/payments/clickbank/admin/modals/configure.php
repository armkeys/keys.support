<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template" 
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('ClickBank Account Nickname	', 'wishlist-member')); ?>',
                    name : 'cbvendor',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('The ClickBank Account Nickname is the same as your ClickBank Vendor ID.', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('ClickBank Secret Key', 'wishlist-member')); ?>',
                    name : 'cbsecret',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('The Secret Key can be edited if desired. Note that this Secret Key must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member')); ?>',
                    tooltip_size : 'md',
                }
            </template>
        </div>
    </div>
</div>
