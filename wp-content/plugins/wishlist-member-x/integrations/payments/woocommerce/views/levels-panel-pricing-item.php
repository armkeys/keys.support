<?php

/**
 * Pricing Item Row
 *
 * @package WishListMember
 */

$__category_or_product = empty($pricing['catview']) ? 'product' : 'category';

if ('percentage-discount' === $pricing['pricing_type']) {
    $pricing_amount = number_format(
        $pricing['pricing_amount'],
        wc_get_price_decimals(),
        wc_get_price_decimal_separator(),
        wc_get_price_thousand_separator()
    ) . '%';
} else {
    $pricing_amount = wp_strip_all_tags(
        wc_price($pricing['pricing_amount'])
    );
}

switch ($pricing['pricing_type']) {
    case 'percentage-discount':
        $pricing_type = __('Percentage Discount', 'wishlist-member');
        break;
    case 'fixed-discount':
        $pricing_type = __('Fixed Price Discount', 'wishlist-member');
        break;
    case 'fixed-price':
    default:
        $pricing_type = __('Fixed Price', 'wishlist-member');
        break;
}

printf(
    '<tr class="button-hover" id="%4$s-pricing-%6$s" data-pricing="%5$s"><td>%1$s</td><td>%2$s</td><td>%3$s</td><td><div class="btn-group-action align-right" style="width: 60px"><a data-toggle="modal" data-target="#wlm4woo-level-%4$s-pricing" href="#" title="Edit Pricing" class="btn woo-edit-price"><span class="wlm-icons md-24 -icon-only">edit</span></a><a href="#" title="Delete Pricing" class="btn woo-delete-%4$s-price"><span class="wlm-icons md-24 -icon-only">delete</span></a></div></td></tr>',
    esc_html($items[ $pid ]['text']),
    esc_html($pricing_type),
    esc_html($pricing_amount),
    esc_attr($__category_or_product),
    esc_attr(wp_json_encode($pricing + [$__category_or_product . '_id' => $pid])),
    esc_attr($pid)
);
