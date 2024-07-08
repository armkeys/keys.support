<?php

/**
 * Constant Contact admin UI > Settings tab
 *
 * @package WishListMember/Autoresponders
 */

?>
<div class="row">
    <a type="button" class="btn -primary ml-4" href="<?php echo esc_url($constant_contact_v3->auth_url); ?>">
        <i class="wlm-icons">settings</i><span><?php esc_html_e('Authenticate', 'wishlist-member'); ?></span>
    </a>
    <?php echo wp_kses_post($api_status_markup); ?>
</div>
