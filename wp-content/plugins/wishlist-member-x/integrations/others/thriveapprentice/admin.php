<div class="row">
    <div class="col">
        <p><?php esc_html_e('While this integration is enabled, WishList Member will add settings within Thrive Apprentice related to the display of Thrive Apprentice Courses.', 'wishlist-member'); ?></p>
        <p>
            <?php
            esc_html_e('The setting is available in the Products > Access Requirements section within Thrive Apprentice.', 'wishlist-member');
            ?>
        </p>
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    // Translators: 1 - Link to knowledgebase article.
                    __('<a href="%1$s" target="_blank">Click Here</a> for additional information.', 'wishlist-member'),
                    'https://wishlistmember.com/docs/thrive-apprentice/',
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
