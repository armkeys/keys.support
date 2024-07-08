<?php

/**
 * Constant Contact admin UI > Configure modal
 *
 * @package WishListMember/Autoresponders
 */

?>
<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template" 
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row -integration-keys">
            <?php echo wp_kses_post($api_status_markup); ?>       
            <div class="col-md-12">
                <p><?php esc_html_e('Enter your Constant Contact Login Information.', 'wishlist-member'); ?></p>
            </div>
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('Username', 'wishlist-member')); ?>', type : 'text', name : 'ccusername', column : 'col-md-12'}</template>
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('Password', 'wishlist-member')); ?>', type : 'password', name : 'ccpassword', column : 'col-md-12'}</template>
        </div>
    </div>
</div>
