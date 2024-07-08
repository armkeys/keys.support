<div class="col-md-12">
    <p>
        <a href="#<?php echo $config['id']; ?>-upgrade" class="hide-show"><?php esc_html_e('Upgrade Instructions for PayPal Personal Account Users.', 'wishlist-member'); ?>
        </a> 
        <?php wishlistmember_instance()->tooltip(__('A PayPal Business account is required to setup the integration with WishList Member. If you have a PayPal Personal account, you will just need to upgrade your account accordingly.', 'wishlist-member'), 'lg'); ?>
    </p>
    <div class="d-none" id="<?php echo $config['id']; ?>-upgrade">
        <div class="panel">
            <div class="panel-body">
                <p>
                    <?php esc_html_e('Note: You can ignore these instructions if you already have a PayPal Business account.', 'wishlist-member'); ?>
                </p>
                <ul style="list-style: '- '" class="pl-2">
                    <li>
                        <p class="mb-0">
                        <?php
                        echo sprintf(
                            wp_kses(
                                __('PayPal provides the option to <a href="%s" target="_blank">Upgrade from a PayPal Personal account to a PayPal Business account.</a>', 'wishlist-member'),
                                [
                                    'a' => [
                                        'href'   => [],
                                        'target' => [],
                                    ],
                                ]
                            ),
                            'https://www.paypal.com/us/cshelp/article/how-do-i-change-the-type-of-paypal-account-i-have-help339'
                        );
                        ?>
                        </p>
                    </li>
                    <li>
                        <p class="mb-0">
                        <?php
                        echo sprintf(
                            wp_kses(
                                __('If you have a PayPal Personal account, go to the <a href="%s" target="_blank">Account > Profile</a> section in your PayPal Personal account.', 'wishlist-member'),
                                [
                                    'a' => [
                                        'href'   => [],
                                        'target' => [],
                                    ],
                                ]
                            ),
                            'https://www.paypal.com/myaccount/profile/'
                        );
                        ?>
                        </p>
                    </li>
                    <li><p class="mb-0"><?php esc_html_e('Click the "Upgrade to a Business account" link.', 'wishlist-member'); ?></p></li>
                    <li><p class="mb-0"><?php esc_html_e('Enter the information requested by PayPal and follow the steps PayPal shows on screen to upgrade to a PayPal Business account.', 'wishlist-member'); ?></p></li>
                </ul>
            </div>
        </div>
    </div>
</div>

