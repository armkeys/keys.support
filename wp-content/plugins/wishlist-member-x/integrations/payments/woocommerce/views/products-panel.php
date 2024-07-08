<?php

/**
 * WooCommerce integration products panel.
 *
 * @package WishListMember
 */

// Post ID.
$postid = wlm_arrval($GLOBALS, 'post', 'ID');
// Get the levels linked to this product.
$wlmwoo = (array) wlm_arrval(wishlistmember_instance()->get_option('woocommerce_products'), $postid);

$access_options     = [sprintf('<optgroup label="%s"> </option>', __('Membership Levels', 'wishlist-member'))];
$pricing_levels     = [];
$wlmwoo_level_names = [];
// Generate options for membership level related dropdowns and grab level names as well.
foreach (\WishListMember\Level::get_all_levels(true) as $level) {
    // Access options.
    $selected         = in_array($level->ID, $wlmwoo) ? 'selected' : '';
    $access_options[] = sprintf('<option value="%s" %s>%s</option>', $level->ID, $selected, $level->name);
    // Pricing options.
    $pricing_levels[ $level->ID ] = sprintf('<option value="%s">%s</option>', $level->ID, $level->name);
    $pricing_levels[ $level->ID ] = $level->name;
    // Level names.
    $wlmwoo_level_names[ $level->ID ] = $level->name;
}
// Add payperposts to membership level dropdown options.
foreach (wishlistmember_instance()->get_pay_per_posts(['post_title', 'post_type']) as $payperpost_post_type => $payperposts) {
    if (! count($payperposts)) {
        continue;
    }
    $access_options[] = sprintf('<optgroup label="%s"> </option>', ucfirst($payperpost_post_type));
    foreach ($payperposts as $payperpost) {
        $selected         = in_array('payperpost-' . $payperpost->ID, $wlmwoo, true) ? 'selected' : '';
        $access_options[] = sprintf('<option value="%s" %s>%s</option>', 'payperpost-' . $payperpost->ID, $selected, esc_html($payperpost->post_title));
    }
}

// Get the wlm product pricings for this product.
$wlmwoo_pricing = (array) wlm_arrval(wishlistmember_instance()->get_option('woocommerce_product_pricing'), $postid);
?>
<div id="wishlist_member_woo" class="wishlist_member_woo panel hidden">
    <div>
        <ul class="wishlist-member-subtabs">
            <li><a href="#wishlist-member-subtabs-access" class="active"><?php esc_html_e('Access', 'wishlist-member'); ?></a>
            <li>
            <li><a href="#wishlist-member-subtabs-pricing"><?php esc_html_e('Pricing', 'wishlist-member'); ?></a>
            <li>
        </ul>
    </div>
    <?php
        require_once __DIR__ . '/products-panel-access.php';
        require_once __DIR__ . '/products-panel-pricing.php';
    ?>
</div>
