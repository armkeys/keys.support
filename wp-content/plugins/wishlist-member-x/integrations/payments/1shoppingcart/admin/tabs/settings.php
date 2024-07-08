
<form>
    <div class="row">
        <div class="col-md-12 mb-4"><?php echo wp_kses_post($config_button); ?></div>
    </div>
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Thank You URL / API Notification URL', 'wishlist-member')); ?>',
                name : 'scthankyou',
                addon_left : '<?php echo esc_js($wpm_scregister); ?>',
                addon_right : '.PHP',
                column : 'col-md-6',
                class : 'text-center -url',
                group_class : '-url-group mb-1',
                tooltip : '<?php echo esc_js(__('Copy the Thank You URL / API Notification URL and paste it into the corresponding field in the My Account > API > API Settings section on the 1ShoppingCart site.', 'wishlist-member')); ?>',
                tooltip_size : 'lg',
            }
        </template>
    </div>
    <div class="row">
        <div class="col-md-12 text-muted">
            <br>
            <p><?php esc_html_e('Copy the Thank You URL / API Notification URL from above and paste it into both the Destination URL and Thank You URL fields in the Edit Product > Links tab in 1ShoppingCart.', 'wishlist-member'); ?></p>
            <p>
                <?php
                printf(
                    wp_kses(
                        __('More details are available in the <a href="%s" target="_blank">1ShoppingCart integration documentation.</a>', 'wishlist-member'),
                        [
                            'a' => [
                                'href'   => [],
                                'target' => [],
                            ],
                        ]
                    ),
                    'https://wishlistmember.com/docs/1shoppingcart/'
                );
                ?>
            </p>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</form>
