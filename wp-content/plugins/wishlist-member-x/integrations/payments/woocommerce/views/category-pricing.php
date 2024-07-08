<?php

/**
 * Category pricing form view
 *
 * @package WishListMember
 */

/**
 * Level names
 *
 * @var array
 */
$wlmwoo_level_names = [];

// Get the wlm product pricings for this product.
$wlmwoo_category_pricing = $edit ? (array) wlm_arrval(wishlistmember_instance()->get_option('woocommerce_category_pricing'), $term->term_id) : [];
?>

<?php if ($edit) : ?>
<tr class="form-field form-required term-name-wrap">
    <th scope="row"><label><?php esc_html_e('Member Pricing', 'wishlist-member'); ?></label></th>
    <td id="wishlist-member-woo-category-pricing">
<?php else : ?>
<div class="wishlist-member-woo-category-pricing" id="wishlist-member-woo-category-pricing">
    <h2><?php esc_html_e('Member Pricing', 'wishlist-member'); ?></h2>
<?php endif; ?>

    <p style="margin-bottom: .5em"><?php esc_html_e('Select a level below to create special discount pricing for members.', 'wishlist-member'); ?></p>
    <div class="wishlist_member_woo_category_pricing_add">
        <div class="form-field wishlist_member_woo_category_pricing_levels_field">
            <select id="wishlist_member_woo_category_pricing_levels" name="wishlist_member_woo_category_pricing_levels">
                <option value=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></option>
                <?php
                foreach (wishlistmember_instance()->get_option('wpm_levels') as $level_id => $level) {
                    printf(
                        '<option value="%s">%s</option>',
                        esc_attr($level_id),
                        esc_html($level['name'])
                    );
                    $wlmwoo_level_names[ $level_id ] = $level['name'];
                }
                ?>
                <option value="new"><?php esc_html_e('Add New', 'wishlist-member'); ?></option>
            </select>
            <span class="description"><button class="button add-price"><?php esc_html_e('Add', 'wishlist-member'); ?></button></span>
        </div>
    </div>
    <div class="wishlist_member_woo_category_pricing_items">
        <?php
        foreach ($wlmwoo_category_pricing as $level_id => $pricing) {
            if (! is_array($pricing)) {
                continue;
            }
            $pricing['level_id']   = $level_id;
            $pricing['level_name'] = $wlmwoo_level_names[ $level_id ];
            $pricing['term_id']    = $term->term_id;
            do_action('wishlistmember_woocommerce_get_category_pricing_item_view', $pricing);
        }
        ?>
    </div>
<?php if ($edit) : ?>
        <p>
            <button type="button" id="wishlist-member-save-pricing" class="button button-secondary"><?php esc_html_e('Save Pricing', 'wishlist-member'); ?></button>
            <span id="wishlist-member-woo-pricing-saved"><?php esc_html_e('Member Pricing Saved.', 'wishlist-member'); ?></span>
        </p>
    </td>
</tr>
<?php else : ?>
</div>
<?php endif; ?>
