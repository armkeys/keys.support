<?php

/**
 * Thrive Automator admin UI.
 *
 * @package WishListMember/Integrations/Others
 */

?>
<div class="row">
    <div class="col">
        <p><?php esc_html_e('WishList Member includes an integration with Thrive Automator. This allows you to create automatic actions between WishList Member and many other systems. This is done through the use of triggers that will cause specific actions to be carried out.', 'wishlist-member'); ?></p>
        <p>
            <?php
            echo wp_kses_data(
                sprintf(
                    // Translators: 1 - Link to Thrive Automator admin area.
                    __('The settings for this integration are available when editing or creating an automation in the Automations section in <a href="%1$s">Thrive Automator</a>.', 'wishlist-member'),
                    admin_url('admin.php?page=thrive_automator'),
                ),
            );
            ?>
        </p>
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    // Translators: 1 - Link to knowledgebase article.
                    __('<a href="%1$s" target="_blank">Click Here</a> for additional information.', 'wishlist-member'),
                    'https://wishlistmember.com/docs/thrive-automator/',
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
