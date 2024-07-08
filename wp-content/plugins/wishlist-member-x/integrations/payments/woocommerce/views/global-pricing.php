<?php

/**
 * Global pricing form view
 *
 * @package WishListMember
 */

?>
<div id="wishlist-member-woo-pricing">
    <p><?php esc_html_e('Select a level below to create special discount pricing for members.', 'wishlist-member'); ?></p>
    <div class="wishlist_member_woo_pricing_add">
        <?php
            woocommerce_wp_select(
                [
                    'id'          => 'wishlist_member_woo_pricing_levels',
                    'options'     => [
                        ''    => __('Membership Level', 'wishlist-member'),
                        'all' => __('All Levels', 'wishlist-member'),
                    ]
                    + $pricing_levels
                    + ['new' => __('Add New', 'wishlist-member')],
                    'description' => '<button class="button add-price">' . __('Add', 'wishlist-member') . '</button>',
                ]
            );
            ?>
    </div>
    <div class="wishlist_member_woo_pricing_items">
        <input type="hidden" name="wishlistmember_woo_pricing[]" value="">
        <?php
        // Move all levels to beginning.
        if (isset($wlmwoo_pricing['all'])) {
            $wlmwoo_pricing = ['all' => $wlmwoo_pricing['all']] + $wlmwoo_pricing;
        }
        foreach ($wlmwoo_pricing as $level_id => $pricing) {
            if (! is_array($pricing)) {
                continue;
            }
            $pricing['level_id']   = $level_id;
            $pricing['level_name'] = $wlmwoo_level_names[ $level_id ];
            do_action('wishlistmember_woocommerce_get_global_pricing_item_view', $pricing);
        }
        ?>
    </div>
</div>
