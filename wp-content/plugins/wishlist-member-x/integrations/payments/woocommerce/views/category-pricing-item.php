<?php

/**
 * Category pricing item view
 *
 * @package WishListMember
 */

?>

<div class="-pricing-item pricing-type-<?php echo esc_attr($pricing_type); ?> <?php echo esc_attr($open); ?>"
    data-level="<?php echo esc_attr($level_id); ?>" data-level-name="<?php echo esc_attr($level_name); ?>"
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
        <div class="form-field">
            <label><?php esc_html_e('Type', 'wishlist-member'); ?></label>
            <div class="radios wishlistmember_woo_category_pricing_type">
                <label>
                    <input name="wishlistmember_woo_category_pricing[<?php echo esc_attr($level_id); ?>][pricing_type]" value="fixed-price" type="radio" <?php echo 'fixed-price' === $pricing_type ? 'checked="checked"' : ''; ?>>
                    <?php esc_html_e('Fixed Price', 'wishlist-member'); ?>
                </label>
                <label>
                    <input name="wishlistmember_woo_category_pricing[<?php echo esc_attr($level_id); ?>][pricing_type]" value="fixed-discount" type="radio" <?php echo 'fixed-discount' === $pricing_type ? 'checked="checked"' : ''; ?>>
                    <?php esc_html_e('Fixed Discount', 'wishlist-member'); ?>
                </label>
                <label>
                    <input name="wishlistmember_woo_category_pricing[<?php echo esc_attr($level_id); ?>][pricing_type]" value="percentage-discount" type="radio" <?php echo 'percentage-discount' === $pricing_type ? 'checked="checked"' : ''; ?>>
                    <?php esc_html_e('Percentage Discount', 'wishlist-member'); ?>
                </label>
            </div>
        </div>
        <div class="form-field wishlistmember_woo_category_pricing_amount_<?php echo esc_attr($level_id); ?>_field wishlistmember_woo_category_pricing_amount">
            <label for="wishlistmember_woo_category_pricing_amount_<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Amount', 'wishlist-member'); ?></label>
            <div>
                <input type="text" style="width: 100px;" name="wishlistmember_woo_category_pricing[<?php echo esc_attr($level_id); ?>][pricing_amount]" id="wishlistmember_woo_category_pricing_amount_<?php echo esc_attr($level_id); ?>" value="<?php echo esc_attr($pricing_amount); ?>">
            </div>
        </div>
        <div class="form-field wishlistmember_woo_category_pricing_description_<?php echo esc_attr($level_id); ?>_field ">
            <label for="wishlistmember_woo_category_pricing_description_<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Description', 'wishlist-member'); ?></label>
            <input type="text" class="short" name="wishlistmember_woo_category_pricing[<?php echo esc_attr($level_id); ?>][description]" id="wishlistmember_woo_category_pricing_description_<?php echo esc_attr($level_id); ?>"
                value="<?php echo esc_attr($description); ?>" placeholder="">
        </div>
    </div>
</div>
