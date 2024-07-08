<?php

/**
 * WishList Member for WooCommerce
 * Level > WooCommerce > Access screen
 *
 * @package WishListMember
 */

?>

<div class="woo-tabs active" id="woo-access">
    <div class="row">
        <template class="wlm3-form-group">
            {
                type: 'select',
                label: '<?php esc_html_e('Products', 'wishlist-member'); ?>',
                options: <?php echo wp_json_encode($woo_products); ?>,
                value: <?php echo wp_json_encode(array_values((array) $access_products)); ?>,
                column: 'col-6',
                id: 'woocommerce-access-products',
                style: 'width: 100%',
                multiple : 'multiple',
                'data-allow-clear' : 1,
                tooltip : '<?php echo esc_js(__('The selected WooCommerce product will be connected to the membership level. This means anyone who purchases the product(s) will be automatically added to the membership level in WishList Member. Note: Multiple products can be selected.', 'wishlist-member')); ?>'
            }
        </template>
        <div class="col-auto mx-0 px-0">
            <label>&nbsp;</label><br>
            <button class="btn -primary -condensed" id="woocommerce-access-save-button"><?php esc_html_e('Save Access', 'wishlist-member'); ?></button>
        </div>
    </div>
</div>
