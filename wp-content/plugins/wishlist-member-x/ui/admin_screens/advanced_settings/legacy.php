<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Legacy', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="row">
        <?php $option_val = $this->get_option('disable_legacy_reg_shortcodes'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Allow Legacy Registration Shortcodes', 'wishlist-member')); ?>',
                    name  : 'disable_legacy_reg_shortcodes',
                    value : '0',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '1',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip: '<?php echo esc_js(__('If enabled, this will allow the use of the older non-WordPress standard shortcodes. It is recommended to leave this disabled. The current WishList Member shortcodes are recommended. The older style legacy Registration Shortcodes (Example: wlm_register_levelname) will continue to function if this setting is enabled.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('disable_legacy_private_tags'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Allow Legacy Private Tags Mergecodes', 'wishlist-member')); ?>',
                    name  : 'disable_legacy_private_tags',
                    value : '0',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '1',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip : '<?php echo esc_js(__('If enabled, this will allow the use of the older non-WordPress standard mergecodes. It is recommended to leave this disabled. The current WishList Member mergecodes are recommended. The older style legacy Private Tags (Example: wlm_private_levename) will continue to function if this setting is enabled.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('show_legacy_integrations'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Legacy Integrations', 'wishlist-member')); ?>',
                    name  : 'show_legacy_integrations',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip: '<?php echo esc_js(__(' If enabled, this will show a small selection of older versions of integrations in the Setup > Integrations section of WishList Member. It is recommended to leave this disabled. The current integrations with WishList Member are improved and recommended.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row" style="margin-bottom: 6px;">
        <?php $option_val = $this->get_option('show_legacy_features'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Legacy Features', 'wishlist-member')); ?>',
                    name  : 'show_legacy_features',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip: '<?php echo esc_js(__('If enabled, a small selection of older retired WishList Member features will be enabled in the plugin. It is recommended to leave this disabled as legacy features are not typically used and are not supported.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
</div>
