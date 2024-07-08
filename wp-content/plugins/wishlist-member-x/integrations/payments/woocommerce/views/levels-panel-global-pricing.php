<?php

/**
 * WishList Member for WooCommerce
 * Level > WooCommerce > Global Pricing screen
 *
 * @package WishListMember
 */

$global_pricing_type = wlm_or(wlm_arrval($global_pricing, 'pricing_type'), 'fixed-price');

?>

<div class="woo-tabs" id="woo-global-pricing">
    <div class="row">
        <div class="col-12">
            <template class="wlm3-form-group">
                {
                    type: 'toggle-adjacent',
                    name: 'woocommerce_enable_global_pricing',
                    value: '1',
                    checked_value: '<?php echo (int) $global_pricing; ?>',
                    uncheck_value: '0',
                    label: '<?php esc_html_e('Enable', 'wishlist-member'); ?>',
                    tooltip : '<?php echo esc_js(__('If enabled, the Global Pricing option is applied to ALL products for the specified membership level.', 'wishlist-member')); ?>'
                }
            </template>
            <hr>
        </div>
        <div class="col-12">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>
                            <?php
                            esc_html_e('Type', 'wishlist-member');
                            wishlistmember_instance()->tooltip(__('There are three Pricing Types available. Fixed Price: The price for the selected membership level is the specific amount you enter into the Amount field. Fixed Discount: The price for the selected membership level is based on the discount you enter into the Amount field. Percentage Discount: The price for the selected membership level is based on the percentage you enter into the Amount field.', 'wishlist-member'), 'lg');
                            ?>
                        </label>
                        <div class="row">
                            <template class="wlm3-form-group">
                                {
                                    type: 'radio',
                                    name: 'woocommerce_global_pricing_type',
                                    value : 'fixed-price',
                                    label : '<?php esc_html_e('Fixed Price', 'wishlist-member'); ?>',
                                    column : 'col-auto',
                                    checked_value : '<?php echo esc_attr($global_pricing_type); ?>',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    type: 'radio',
                                    name: 'woocommerce_global_pricing_type',
                                    value : 'fixed-discount',
                                    label : '<?php esc_html_e('Fixed Discount', 'wishlist-member'); ?>',
                                    column : 'col-auto',
                                    checked_value : '<?php echo esc_attr($global_pricing_type); ?>',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    type: 'radio',
                                    name: 'woocommerce_global_pricing_type',
                                    value : 'percentage-discount',
                                    label : '<?php esc_html_e('Percentage Discount', 'wishlist-member'); ?>',
                                    column : 'col-auto',
                                    checked_value : '<?php echo esc_attr($global_pricing_type); ?>',
                                }
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        type: 'text',
                        name: 'woocommerce_global_pricing_amount',
                        style: 'max-width: 150px',
                        value: <?php echo wp_json_encode(wlm_arrval($global_pricing, 'pricing_amount')); ?>,
                        label: '<?php esc_html_e('Amount', 'wishlist-member'); ?>',
                        column : 'col',
                        tooltip : '<?php echo esc_js(__('The Amount is based on the currency you have set in WooCommerce when using Fixed Price or Fixed Discount or the percentage you set in the field when using Percentage Discount.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        type: 'text',
                        name: 'woocommerce_global_pricing_description',
                        style: 'max-width: 500px',
                        value: <?php
                        echo wp_json_encode(
                            wlm_or(
                                wlm_arrval($global_pricing, 'description'),
                                // Translators: 1 - Level Name.
                                sprintf(__('%1$s global member pricing', 'wishlist-member'), ( new \WishListMember\Level($level_id) )->name)
                            )
                        );
                        ?>,
                        label: '<?php esc_html_e('Description', 'wishlist-member'); ?>',
                        column : 'col',
                        tooltip : '<?php echo esc_js(__('The Description field will appear as the product description on the live site.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button class="btn -primary -condensed" id="woocommerce-levels-global-pricing-save-button"><?php esc_html_e('Save Global Pricing', 'wishlist-member'); ?></button>
        </div>
    </div>
</div>
