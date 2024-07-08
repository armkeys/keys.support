<?php

/**
 * WishList Member for WooCommerce
 * Level > WooCommerce > Product Pricing screen
 *
 * @package WishListMember
 */

?>
<div class="woo-tabs" id="woo-product-pricing">
    <div class="row">
        <div class="col">
            <div class="table-wrapper table-responsive" style="max-width: 800px">
                <table class="table table-striped table-condensed woo-product-pricing">
                    <colgroup>
                        <col>
                        <col width="150">
                        <col width="150">
                        <col width="50">
                    <?php
                    echo '<tbody>';
                    foreach ($pricing_products as $pid => $pricing) {
                        if (empty($woo_products[ $pid ])) {
                            continue;
                        }
                        do_action('wishlistmember_woocommerce_get_level_pricing_item_view', $pricing, $woo_products, $pid);
                    }
                    echo '</tbody>';
                    ?>
                    <thead>
                        <tr>
                            <th>
                                <?php esc_html_e('Product', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('The name of the WooCommerce product. Click to edit the Member Price for the product. The Member Price can be a Fixed Price, Fixed Discount or Percentage Discount.', 'wishlist-member'), 'lg'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Pricing Type', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('There are three Pricing Types available. Fixed Price: The price for the selected membership level is the specific amount you enter into the Amount field. Fixed Discount: The price for the selected membership level is based on the discount you enter into the Amount field. Percentage Discount: The price for the selected membership level is based on the percentage you enter into the Amount field.', 'wishlist-member'), 'lg'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Amount', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('The Amount is based on the currency you have set in WooCommerce when using Fixed Price or Fixed Discount or the percentage you set in the field when using Percentage Discount.', 'wishlist-member'), 'lg'); ?>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
            <p>
                <button class="btn -primary -condensed new" data-toggle="modal" data-target="#wlm4woo-level-product-pricing"><?php esc_html_e('Add Member Price', 'wishlist-member'); ?></button>
            </p>
        </div>
    </div>
</div>
<?php
require_once 'levels-panel-product-pricing-modal.php';
?>
