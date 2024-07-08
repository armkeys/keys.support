<?php

/**
 * WooCommerce Levels panel view
 *
 * @package WishListMember
 */

// Woo products.
$woo_products = [];

// Changing the way we fetch Woo Products from wc_get_products() function to.
// Manual WP_Query as the wc_get_products() has an issue with some products not being fetched until you update them.
$xproducts = new WP_Query(
    [
        'post_type' => 'product',
        'nopaging'  => true,
    ]
);

$products   = [];
if (count($xproducts->posts)) {
    foreach ($xproducts->posts as $key => $c) {
        $products[ $c->ID ] = [
            'id'    => $c->ID,
            'name' => $c->post_title,
        ];
    }
}

foreach ($products as $woo_product) {
    $woo_products[ $woo_product['id'] ] = [
        'value' => $woo_product['id'],
        'text'  => $woo_product['name'],
    ];
}

$access = wishlistmember_instance()->get_option('woocommerce_products');
if (! is_array($access)) {
    $access = [];
}
$access_products = [];
foreach ($access as $pid => $x) {
    if (in_array($level_id, (array) $x)) {
        $access_products[ $pid ] = $pid;
    }
}

// Product pricing.
$custom_pricing = wishlistmember_instance()->get_option('woocommerce_product_pricing');
if (! is_array($custom_pricing)) {
    $custom_pricing = [];
}
$pricing_products = [];
foreach ($custom_pricing as $pid => $x) {
    if (is_array($x) && isset($x[ $level_id ])) {
        $pricing_products[ $pid ] = $x[ $level_id ];
    }
}

// Category pricing.
$custom_pricing = wishlistmember_instance()->get_option('woocommerce_category_pricing');
if (! is_array($custom_pricing)) {
    $custom_pricing = [];
}
$pricing_categories = [];
foreach ($custom_pricing as $pid => $x) {
    if (is_array($x) && isset($x[ $level_id ])) {
        $pricing_categories[ $pid ] = $x[ $level_id ];
    }
}

$woo_cats = [];
foreach (get_terms('product_cat', ['hide_empty' => false]) as $woo_cat) {
    $woo_cats[ $woo_cat->term_id ] = [
        'value' => $woo_cat->term_id,
        'text'  => $woo_cat->name,
    ];
}

// Global pricing.
$global_pricing = wlm_arrval(wishlistmember_instance()->get_option('woocommerce_global_pricing'), $level_id);

?>
<div>
    <a href="#woo-access" class="woo-tab-links active"><?php esc_html_e('Access', 'wishlist-member'); ?></a>
    <a href="#woo-product-pricing" class="woo-tab-links"><?php esc_html_e('Product Pricing', 'wishlist-member'); ?></a>
    <a href="#woo-category-pricing" class="woo-tab-links"><?php esc_html_e('Category Pricing', 'wishlist-member'); ?></a>
    <a href="#woo-global-pricing" class="woo-tab-links"><?php esc_html_e('Global Pricing', 'wishlist-member'); ?></a>
</div>
<hr>
<div>
    <?php
    require __DIR__ . '/levels-panel-access.php';
    require __DIR__ . '/levels-panel-product-pricing.php';
    require __DIR__ . '/levels-panel-category-pricing.php';
    require __DIR__ . '/levels-panel-global-pricing.php';
    ?>
</div>
