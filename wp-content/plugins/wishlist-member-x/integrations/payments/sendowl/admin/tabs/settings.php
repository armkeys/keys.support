<?php
    $auth_user = esc_attr('payments/' . $config['id']);
?>
<p><?php esc_html_e('The API Endpoint and API Key are required to connect WishList Member and SendOwl.', 'wishlist-member'); ?></p>
<p><?php esc_html_e('The API Endpoint and API Key can be copied from below and pasted into the corresponding fields in the Settings > SendOwl Checkout > Membership Plugins > WishList Member section on the SendOwl site.', 'wishlist-member'); ?></p>
<br>
<form>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('API Endpoint', 'wishlist-member')); ?>',
                name : '',
                column : 'col-12 col-md-6',
                value : '<?php echo esc_js(admin_url() . '?/wlmapi/2.0/'); ?>',
                readonly : 'readonly',
                class : 'copyable',
                tooltip : '<?php echo esc_js(__('The API Endpoint can be pasted into the corresponding field in the Settings > SendOwl Checkout > Membership Plugins > WishList Member section on the SendOwl site.', 'wishlist-member')); ?>',
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
                tooltip : '<?php echo esc_js(__('The Digest Auth Username is not needed for the WishList Member integration with SendOwl.', 'wishlist-member')); ?>',
            }
        </template>
        <div class="col-12">
            <label for=""><?php esc_html_e('API Key / Digest Auth Password', 'wishlist-member'); ?></label>
            <?php wishlistmember_instance()->tooltip(__('The API Key can be pasted into the corresponding field in the Settings > SendOwl Checkout > Membership Plugins > WishList Member section on the SendOwl site.', 'wishlist-member'), 'lg'); ?>
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
