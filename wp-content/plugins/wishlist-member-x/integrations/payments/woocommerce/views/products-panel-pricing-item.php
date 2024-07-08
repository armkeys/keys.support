<div class="-pricing-item pricing-type-<?php echo esc_attr($pricing_type); ?> <?php echo esc_attr($open); ?>"
    data-level="<?php echo esc_attr($level_id); ?>" data-level-name="<?php echo esc_attr($level_name); ?>"
    data-regular-price-formatted="<?php echo esc_attr(wp_strip_all_tags(wc_price($regular_price))); ?>"
    data-regular-price="<?php echo esc_attr($regular_price); ?>"
    data-pricing-type="<?php echo esc_attr($pricing_type); ?>">
    <h3>
        <?php
        $level_url = add_query_arg(
            [
                'page'     => 'WishListMember',
                'wl'       => 'setup/levels',
                'level_id' => $level_id,
            ],
            admin_url('')
        ) . '#levels_access-' . $level_id;
        ?>
        <span>
            <a href="<?php echo esc_attr($level_url); ?>" title="<?php esc_html_e('Edit Level', 'wishlist-member'); ?>" target="_blank"><span class="dashicons dashicons-external"></span></a>
            <span class="move-price dashicons dashicons-menu" alt="<?php esc_html_e('Move', 'wishlist-member'); ?>"></span>
            <a href="#" class="edit-price" title="<?php esc_html_e('Edit', 'wishlist-member'); ?>"><span class="dashicons dashicons-arrow-down"></span><span class="dashicons dashicons-arrow-up"></span></a>
            <a href="#" class="delete-price" title="<?php esc_html_e('Delete', 'wishlist-member'); ?>"><span class="dashicons dashicons-trash"></span></a>
        </span>
        <a href="#" class="edit-price"><?php echo esc_html($level_name); ?></a>
    </h3>
    <div class="options_group">
        <?php
        woocommerce_wp_radio(
            [
                'id'            => 'wishlistmember_woo_pricing_type_' . $level_id,
                'wrapper_class' => 'wishlistmember_woo_pricing_type',
                'label'         => __('Type', 'wishlist-member'),
                'name'          => 'wishlistmember_woo_pricing[' . $level_id . '][pricing_type]',
                'options'       => [
                    'fixed-price'         => __('Fixed Price', 'wishlist-member'),
                    'fixed-discount'      => __('Fixed Discount', 'wishlist-member'),
                    'percentage-discount' => __('Percentage Discount', 'wishlist-member'),
                ],
                'value'         => $pricing_type,
                'desc_tip'      => true,
                'description'   => sprintf(
                    '<div style="text-align:left"><p>%1$s</p><p>%2$s</p><p>%3$s</p></div>',
                    __('Fixed Price: Description...', 'wishlist-member'),
                    __('Fixed Discount: Description...', 'wishlist-member'),
                    __('Percentage Discount: Description...', 'wishlist-member')
                ),
            ]
        );
        woocommerce_wp_text_input(
            [
                'id'            => 'wishlistmember_woo_pricing_amount_' . $level_id,
                'label'         => __('Amount', 'wishlist-member'),
                'name'          => 'wishlistmember_woo_pricing[' . $level_id . '][pricing_amount]',
                'value'         => $pricing_amount,
                'wrapper_class' => 'wishlistmember_woo_pricing_amount',
            ]
        );
        woocommerce_wp_text_input(
            [
                'id'    => 'wishlistmember_woo_pricing_description_' . $level_id,
                'label' => __('Description', 'wishlist-member'),
                'name'  => 'wishlistmember_woo_pricing[' . $level_id . '][description]',
                'value' => $description,
            ]
        );
        ?>
    </div>
</div>
