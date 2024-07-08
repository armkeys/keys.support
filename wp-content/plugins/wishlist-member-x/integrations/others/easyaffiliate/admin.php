<div class="row">
    <div class="col">
        <p><?php esc_html_e('While this integration is enabled, commissions for membership level sales, recurring payments and refund transactions for the supported payment providers will be automatically recorded in the Easy Affiliate plugin.', 'wishlist-member'); ?></p>
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    // Translators: 1 - Link to knowledgebase article.
                    __('<a href="%1$s" target="_blank">Click Here</a> for additional information.', 'wishlist-member'),
                    'https://wishlistmember.com/docs/easyaffiliate/',
                ),
                [
                    'a' => [
                        'href'   => [],
                        'target' => [],
                    ],
                ]
            );
            ?>
        </p>
    </div>
</div>
