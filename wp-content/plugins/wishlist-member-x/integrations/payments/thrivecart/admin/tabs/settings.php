<?php
    $auth_user = esc_attr('payments/' . $config['id']);
?>
<form>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('WordPress URL', 'wishlist-member')); ?>',
                name : '',
                column : 'col-12 col-md-6',
                value : '<?php echo esc_js(admin_url()); ?>',
                readonly : 'readonly',
                class : 'copyable',
                tooltip : '<?php echo esc_js(__('The WordPress URL for the site. This is used to connect WishList Member and ThriveCart in the Settings > View Integrations > WishList Member Integration > View Settings section of ThriveCart.', 'wishlist-member')); ?>',
            }
        </template>
        <div class="w-100"></div>
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Digest Auth Username', 'wishlist-member')); ?>',
                name : '',
                column : 'col-12 col-md-6',
                value : '<?php echo esc_js($auth_user); ?>',
                class : 'copyable',
                readonly : 'readonly',
                tooltip : '<?php echo esc_js(__('The Digest Auth Username option cannot be edited but is also required for developers to access the WishList Member API when using the Digest Auth method.', 'wishlist-member')); ?>',
            }
        </template>
        <div class="col-12">
            <label for=""><?php esc_html_e('API Key / Digest Auth Password', 'wishlist-member'); ?></label>
            <?php wishlistmember_instance()->tooltip(__('This Key/Password is used by developers to access the WishList Member API. It is also used by certain WishList Member integrations. Note: If this Key/Password is modified, any integrations that use the key/password will need to be updated and reconnected. This is used to connect WishList Member and ThriveCart in the Settings > View Integrations > WishList Member Integration > View Settings section of ThriveCart.', 'wishlist-member'), 'md'); ?>
        </div>
        <template class="wlm3-form-group">
            {
                name : '',
                column : 'col-12 col-md-6',
                value : <?php echo wp_json_encode($wlmapikey); ?>,
                readonly : 'readonly',
                id : '<?php echo esc_js($config['id']); ?>-apikey',
                'data-keyname' : '<?php echo esc_js($auth_user); ?>',
                class : 'copyable',
                group_class : 'mb-2 mb-md-4'
            }
        </template>
        <div class="col-12 col-md-auto pl-md-0 pb-3 text-right">
            <button type="button" data-action="gen-api-key" data-target="#<?php echo esc_attr($config['id']); ?>-apikey" name="button" class="btn -default -condensed"><?php esc_html_e('Generate New Key', 'wishlist-member'); ?></button>
        </div>
    </div>
</form>
