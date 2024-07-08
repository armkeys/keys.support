<form>
    <div class="row">
        <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
        <?php echo wp_kses_post($api_status_markup); ?>       
    </div>
    <div class="row api-required">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Order Form Web Page URL', 'wishlist-member')); ?>',
                name : 'isthankyou',
                addon_left : '<?php echo esc_js($wpm_scregister); ?>',
                column : 'col-md-6',
                class : 'text-center -url',
                group_class : '-url-group',
                tooltip : '<?php echo esc_js(__('The end string of the displayed Thank You URL can be edited if desired. Note that this Thank You URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member')); ?>',
            }
        </template>
    </div>
    <div class="row api-required">
        <div class="col-md-12 text-muted">
            <p><?php esc_html_e('Create an Order Form for each product and set the Web Page URL in Infusionsoft to this URL.', 'wishlist-member'); ?></p><p>The Web Page URL field can be found by selecting Web Address in the <em><?php esc_html_e('Other Options > Thank You Page', 'wishlist-member'); ?></em> Settings section of Infusionsoft.</p><p>Note: The "Pass Persons Info to Thank You Page URL (This is for Techies)" option in Infusionsoft must be selected to ensure the integration works properly.</p>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</form>
