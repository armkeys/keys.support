<?php

/**
 * Thrive Architect admin UI.
 *
 * @package WishListMember/Integrations/Others
 */

?>
<div class="row">
    <div class="col">
        <?php
        if (PHP_MAJOR_VERSION < 7) {
            printf('<p>%s</p>', esc_html__('This integration requires PHP version 7 or higher.', 'wishlist-member'));
            return;
        }
        if (! function_exists('tve_global_options_init')) {
            printf('<p>%s</p>', esc_html__('This integration requires the Thrive Architect plugin.', 'wishlist-member'));
            return;
        }
        ?>
        <p>
            <?php esc_html_e('WishList Member includes an integration with Thrive Architect. This allows you to set protection and access for elements within Thrive Architect. Content can be set to only be viewable by specific membership levels. You can also set content to only be viewed by members who are not in a selected level.', 'wishlist-member');
            ?>
        </p>
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    // Translators: 1 - Link to knowledgebase article.
                    __('The setting can be accessed when using the Conditional Display while editing an element in Thrive Architect. <a href="%1$s" target="_blank">Click Here</a> for additional information.', 'wishlist-member'),
                    'https://wishlistmember.com/docs/thrive-architect/',
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
