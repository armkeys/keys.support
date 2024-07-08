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
                    label : '<?php echo esc_js(__('Mechant ID', 'wishlist-member')); ?>',
                    name : 'pwcmerchantid',
                    column : 'col-md-12',
                    tooltip : '<p><?php echo esc_js(__('Merchant ID is located in the following section:', 'wishlist-member')); ?></p><p><?php echo esc_js(__('Account Settings > Current Status.', 'wishlist-member')); ?></p>',
                    tooltip_size: 'md',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('API Key', 'wishlist-member')); ?>',
                    name : 'pwcapikey',
                    column : 'col-md-12',
                    tooltip : '<p><?php echo esc_js(__('API Key is located in the following section:', 'wishlist-member')); ?></p><p><?php echo esc_js(__('Cart Settings > Advanced Integration > API Integration.', 'wishlist-member')); ?></p>',
                    tooltip_size: 'md',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Secret Word', 'wishlist-member')); ?>',
                    name : 'pwcsecret',
                    column : 'col-md-12',
                    tooltip : '<?php echo esc_js(__('The Secret Word is used to generate a hash key for security purposes.', 'wishlist-member')); ?>',
                    tooltip_size: 'md',
                }
            </template>
        </div>
    </div>
</div>
