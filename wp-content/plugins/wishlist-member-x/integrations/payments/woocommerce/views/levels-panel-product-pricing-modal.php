<?php

/**
 * Product Pricing Modal
 *
 * @package WishListMember
 */

?>
<script>
    var product_pricing = <?php echo wp_json_encode($pricing_products, JSON_FORCE_OBJECT); ?>;
    var woo_products = <?php echo wp_json_encode($woo_products, JSON_FORCE_OBJECT); ?>;
</script>
<div
    id="wlm4woo-level-product-pricing-modal"
    data-id="wlm4woo-level-product-pricing"
    data-label="wlm4woo-level-product-pricing"
    data-title="<?php esc_html_e('Product Member Pricing', 'wishlist-member'); ?>"
    data-show-default-footer=""
    data-classes="modal-md"
    style="display:none">
    <div class="body">
        <div id="wlm4woo-level-product-pricing-data">
            <div class="row">
                <input type="hidden" name="product_id">
                <template class="wlm3-form-group">
                    {
                        name : 'product_id',
                        type : 'select',
                        id : 'wlm4woo-product-id',
                        label : 'Product',
                        column : 'col-12',
                        style: 'width: 100%',
                        'data-allow-clear' : '1',
                        'data-placeholder' : '<?php esc_html_e('Choose a Product', 'wishlist-member'); ?>',
                        options: <?php echo wp_json_encode($woo_products); ?>,
                        tooltip : '<?php echo esc_js(__('The name of the WooCommerce product.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row" id="wlm4woo-level-product-pricing-details">
                <div class="col-12">
                    <label>
                        <?php
                            esc_html_e('Pricing Type', 'wishlist-member');
                            wishlistmember_instance()->tooltip(__('There are three Pricing Types available. Fixed Price: The price for the selected membership level is the specific amount you enter into the Amount field. Fixed Discount: The price for the selected membership level is based on the discount you enter into the Amount field. Percentage Discount: The price for the selected membership level is based on the percentage you enter into the Amount field.', 'wishlist-member'), 'lg');
                        ?>
                    </label>
                    </div>
                <template class="wlm3-form-group">
                    {
                        name : 'pricing_type',
                        type : 'radio',
                        label : 'Fixed Price',
                        value : 'fixed-price',
                        column : 'col-auto',
                        checked : 1,
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        name : 'pricing_type',
                        type : 'radio',
                        label : 'Fixed Discount',
                        value : 'fixed-discount',
                        column : 'col-auto'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        name : 'pricing_type',
                        type : 'radio',
                        label : 'Percentage Discount',
                        value : 'percentage-discount',
                        column : 'col'
                    }
                </template>
                <div class="w-100 mt-4"></div>
                <template class="wlm3-form-group">
                    {
                        name : 'pricing_amount',
                        type : 'text',
                        label : 'Amount',
                        column : 'col-auto',
                        style : 'width: 100px;',
                        addon_right : '%',
                        tooltip : '<?php echo esc_js(__('The Amount is based on the currency you have set in WooCommerce when using Fixed Price or Fixed Discount or the percentage you set in the field when using Percentage Discount.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        name : 'description',
                        type : 'text',
                        label : 'Description',
                        column : 'col-12',
                        tooltip : '<?php echo esc_js(__('The Description field will appear as the product description on the live site.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="footer">
        <a data-toggle="modal" data-target="#wlm4woo-level-product-pricing" href="#" class="btn -bare">
            <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
        </a>
        <a data-btype="save" href="" class="save-button btn -primary">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
        </a>
        <a data-btype="save" href="" class="save-button -close btn -success">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save & Close', 'wishlist-member'); ?></span>
        </a>
    </div>
</div>
