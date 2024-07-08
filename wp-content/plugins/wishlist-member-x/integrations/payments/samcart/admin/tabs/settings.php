<?php
    $auth_user = esc_attr('payments/' . $config['id']);
?>
<p>
    <?php esc_html_e('WishList Member includes an integration with SamCart. You can set users to be added to a membership level or removed from a membership level in WishList Member based on actions and triggers if they purchase using the SamCart integration.', 'wishlist-member'); ?>
</p>
<p>
    <?php
    printf(
        wp_kses(
            __('More details are explained in the <a href="%s" target="_blank">SamCart integration documentation.</a>', 'wishlist-member'),
            [
                'a' => [
                    'href'   => [],
                    'target' => [],
                ],
            ]
        ),
        'https://wishlistmember.com/docs/samcart/'
    );
    ?>
</p>
<form>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Blog URL', 'wishlist-member')); ?>',
                name : '',
                column : 'col-12 col-md-6',
                help_block : '<?php echo esc_js(__('Copy the Blog URL and paste it into SamCart in the following section:', 'wishlist-member')); ?> <strong><?php echo esc_js(__('Settings > Integrations > New Integration > WishList Member.', 'wishlist-member')); ?></strong>.',
                value : '<?php echo esc_js(admin_url()); ?>',
                readonly : 'readonly',
                class : 'copyable',
                tooltip : '<?php echo esc_js(__('The Blog URL for the site. This is used to connect WishList Member and SamCart in the Settings > Integrations section when creating an Integration on the SamCart site. The Blog URL from WishList Member should be pasted into the Blog URL field in SamCart.', 'wishlist-member')); ?>',
                tooltip_size: 'lg',
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
                tooltip : '<?php echo esc_js(__('The Digest Auth Username is not needed for this integration.', 'wishlist-member')); ?>',
                tooltip_size: 'lg',
            }
        </template>
        <div class="col-12">
            <label for=""><?php esc_html_e('API Key / Digest Auth Password', 'wishlist-member'); ?></label>
            <?php wishlistmember_instance()->tooltip(__('The API Key is used to connect WishList Member and SamCart in the Settings > Integrations section when creating an Integration on the SamCart site. The API Key from WishList Member should be pasted into the API Key field in SamCart.', 'wishlist-member'), 'lg'); ?>
        </div>
        <template class="wlm3-form-group">
            {
                name : '',
                column : 'col-12 col-md-6',
                help_block : '<?php echo esc_js(__('Copy the API Key and paste it into SamCart in the following section:', 'wishlist-member')); ?> <strong><?php echo esc_js(__('Settings > Integrations > New Integration > WishList Member', 'wishlist-member')); ?></strong>.',
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
