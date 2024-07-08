<?php
    $auth_user = esc_attr('emails/' . $config['id']);
?>
<div class="row">
    <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    <?php echo wp_kses_post($api_status_markup); ?>       
</div>
<div class="row api-required">
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('Account', 'wishlist-member')); ?>',
            tooltip : '<?php echo esc_js(__('Select the desired Drip Account.', 'wishlist-member')); ?>',
            type : 'select',
            name : 'account',
            column : 'col-12 col-md-6',
            style : 'width: 100%',
            group_class : 'no-margin'
        }
    </template>

    <div class="col-md-12">
        <hr>
        <h3><?php esc_html_e('WishList Member API Information', 'wishlist-member'); ?></h3>
        <br>
    </div>
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('WordPress URL', 'wishlist-member')); ?>',
            name : '',
            column : 'col-12 col-md-6',
            value : '<?php echo esc_js(admin_url()); ?>',
            class : 'copyable',
            readonly : 'readonly',
            tooltip : '<?php echo esc_js(__('The WordPress URL can be entered into the Website URL field in the Settings > Integrations > WishList Member section in Drip.', 'wishlist-member')); ?>',
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
            tooltip : '<?php echo esc_js(__('The Digest Auth Username option cannot be edited but is also required for developers to access the WishList Member API when using the Digest Auth method. The Digest Auth Username is not needed for this integration.', 'wishlist-member')); ?>',
        }
    </template>
    <div class="col-12">
        <label for=""><?php esc_html_e('API Key / Digest Auth Password', 'wishlist-member'); ?></label>
        <?php wishlistmember_instance()->tooltip(__('The API Key can be entered into the API Key field in the Settings > Integrations > WishList Member section in Drip.', 'wishlist-member')); ?>
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
