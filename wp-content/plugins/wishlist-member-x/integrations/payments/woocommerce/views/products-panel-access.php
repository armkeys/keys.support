<div class="wishlist-member-subtabs-panel woocommerce_options_panel" id="wishlist-member-subtabs-access">
    <div class="options_group">
        <p>
            <?php esc_html_e('After purchasing this product, the customer will be added as a Member to the Membership Level(s) selected below.', 'wishlist-member'); ?>
            <input type="hidden" name="wishlist_member_woo_levels[]" value="">
        </p>
        <p class="form-field">
            <label for="wishlist_member_woo_levels"><?php esc_html_e('Membership Level(s)', 'wishlist-member'); ?></label>
            <select id="wishlist_member_woo_levels" name="wishlist_member_woo_levels[]" multiple style="width: 80%">
                <?php
                echo wp_kses(
                    implode('', $access_options),
                    [
                        'optgroup' => ['label' => true],
                        'option'   => [
                            'value'    => true,
                            'selected' => true,
                        ],
                    ]
                );
                ?>
            </select>
        </p>
    </div>
</div>
