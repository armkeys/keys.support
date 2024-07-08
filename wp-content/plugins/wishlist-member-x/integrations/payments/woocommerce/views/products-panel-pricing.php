<div class="wishlist-member-subtabs-panel woocommerce_options_panel" id="wishlist-member-subtabs-pricing" style="display:none">
    <p><?php esc_html_e('Select a level below to create special discount pricing for members.', 'wishlist-member'); ?></p>
    <div class="wishlist_member_woo_pricing_add">
        <?php
            woocommerce_wp_select(
                [
                    'id'          => 'wishlist_member_woo_pricing_levels',
                    'options'     => ['' => __('Membership Level', 'wishlist-member')]
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
        foreach ($wlmwoo_pricing as $level_id => $pricing) {
            if (! is_array($pricing)) {
                continue;
            }
            $pricing['level_id']   = $level_id;
            $pricing['level_name'] = $wlmwoo_level_names[ $level_id ];
            $pricing['product_id'] = $postid;
            do_action('wishlistmember_woocommerce_get_pricing_item_view', $pricing);
        }
        ?>
    </div>
    <p>
        <button type="button" id="wishlist-member-save-pricing" class="button button-primary"><?php esc_html_e('Save Pricing', 'wishlist-member'); ?></button>
        <span id="wishlist-member-woo-pricing-saved"><?php esc_html_e('Member Pricing Saved.', 'wishlist-member'); ?></span>
    </p>
</div>
