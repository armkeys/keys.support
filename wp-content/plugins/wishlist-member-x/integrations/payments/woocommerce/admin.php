<?php

/**
 * WooCommerce Payment Integration
 *
 * @package WishListMember
 */

if (! class_exists('WooCommerce')) {
    printf('<div><p>This integration requires the <a href="%s" target="_blank">WooCommerce</a> plugin.</p></div>', esc_attr($config['link']));
    return;
}

?>
<div class="row">
    <div class="col">
        <p>
            <?php esc_html_e('The WooCommerce integration settings are available within the WishList Member and WooCommerce plugins. Note: The WooCommerce plugin will need to be installed and activated on the site.', 'wishlist-member'); ?>
        </p>

        <p>
            <?php esc_html_e('This means once the integration is enabled here, the available settings can be used in WooCommerce and WishList Member.', 'wishlist-member'); ?>
        </p>

        <p>
            <?php esc_html_e('This includes the ability to automatically add a user to a membership level when they purchase a WooCommerce product. You can also create special member pricing for products.', 'wishlist-member'); ?>
        </p>

        <p>
            <?php
            printf(
                wp_kses(
                    __('More details are available in the <a href="%s" target="_blank">WooCommerce Integration - Overview.</a>', 'wishlist-member'),
                    [
                        'a' => [
                            'href'   => [],
                            'target' => [],
                        ],
                    ]
                ),
                'https://wishlistmember.com/docs/woocommerce/'
            );
            ?>
        </p>
    </div>
</div>
