<div role="tabpanel" class="tab-pane" id="" data-id="levels_additional_settings">
    <div class="content-wrapper">
        
        <br>
        <div class="row">
            <div class="col-xxxl-4 col-md-6">
                <h5><?php esc_html_e('Registration Date Reset', 'wishlist-member'); ?></h5>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('For Expired Level', 'wishlist-member')); ?>',
                        name  : 'registrationdatereset',
                        value : '1',
                        class : 'wlm_toggle-switch',
                        type  : 'checkbox',
                        tooltip : '<?php echo esc_js(__('If enabled, the registration date will be reset when a member re-registers for an expired level. The registration date will be reset to the new date the member registered for the level and the member will have an active status for the level.', 'wishlist-member')); ?>'
                    }
                </template>
                <br class="d-block d-sm-none">
            </div>
            <div class="col-md-4">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Member Role', 'wishlist-member')); ?>',
                        name  : 'role',
                        value : '',
                        type  : 'select',
                        options : js_roles,
                        group_class : 'no-margin',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('The WordPress role that will be applied to all members within the level. In most cases this should be set to Subscriber as that is the default WordPress role.', 'wishlist-member')); ?>'
                    }
                </template>
                <br class="d-block d-sm-none">
            </div>
            <div class="col-md-12">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('For Active Level', 'wishlist-member')); ?>',
                        name  : 'registrationdateresetactive',
                        value : '1',
                        class : 'wlm_toggle-switch',
                        type  : 'checkbox',
                        tooltip_size : 'md',
                        tooltip : '<?php echo esc_js(__('If enabled, the registration date will be reset when a member re-registers for an active level. The registration date will be reset to the new date the member registered for the level and the member will retain the active status for the level.<br><br>Note: If the level expiration option is a Fixed Term (one week, one month, etc.), the remaining duration of that fixed term will be included when calculating the registration date that will be reset.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <h5><?php esc_html_e('Redirects', 'wishlist-member'); ?></h5>
            </div>
        </div>
        <div class="row">
            <div class="col-xxxl-4 col-md-6 col-xs-8">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Custom After Registration Redirect', 'wishlist-member')); ?>',
                        name  : 'custom_afterreg_redirect',
                        value : '1',
                        type : 'toggle-adjacent-disable',
                        tooltip_size: 'md',
                        tooltip: '<?php echo esc_js(__('If enabled, a custom After Registration Redirect can be configured using the blue Configure button. The After Registration page will be displayed to members after a successful registration to this membership level. If disabled, the After Registration Redirect configured in the Advanced Options > Global Defaults > Redirects section will be used.<br><br>Note: This page will only appear one time for the member immediately after registration. The After Registration page cannot be viewed again. The After Login page will appear to the member after each login moving forward.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="col-md-4 col-xs-4">
                <button data-toggle="modal" data-target="#custom-redirects" data-notif-setting="afterreg-redirect" class="btn -primary -condensed" data-notif-title="Custom After Registration Redirect">
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                </button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xxxl-4 col-md-6 col-xs-8">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Custom After Login Redirect', 'wishlist-member')); ?>',
                        name  : 'custom_login_redirect',
                        value : '1',
                        type : 'toggle-adjacent-disable',
                        tooltip_size: 'md',
                        tooltip : '<?php echo esc_js(__('If enabled, a custom After Login Redirect can be configured using the blue Configure button. The After Login page will be displayed to members after a successful login. If disabled, the After Login Redirect configured in the Advanced Options > Global Defaults > Redirects section will be used.<br><br>Note: The After Login page that will be displayed to members in multiple membership levels is based on Level Order.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="col-md-4 col-xs-4">
                <button data-toggle="modal" data-target="#custom-redirects" data-notif-setting="login-redirect" class="btn -primary -condensed" data-notif-title="Custom After Login Redirect">
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                </button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xxxl-4 col-md-6 col-xs-8">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Custom After Logout Redirect', 'wishlist-member')); ?>',
                        name  : 'custom_logout_redirect',
                        value : '1',
                        type : 'toggle-adjacent-disable',
                        tooltip_size: 'md',
                        tooltip: '<?php echo esc_js(__('If enabled, a custom After Logout Redirect can be configured using the blue Configure button. The After Logout page will be displayed to members after a successful logout. If disabled, the After Logout Redirect configured in the Advanced Options > Global Defaults > Redirects section will be used.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="col-md-4 col-xs-4">
                <button data-toggle="modal" data-target="#custom-redirects" data-notif-setting="logout-redirect" class="btn -primary -condensed" data-notif-title="Custom After Logout Redirect">
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                </button>
            </div>
        </div>

        <div class="row legacy-feature">
            <div class="col-xxxl-4 col-md-4 col-xs-8">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Enable Sales Page URL', 'wishlist-member')); ?>',
                        name  : 'enable_salespage',
                        value : '1',
                        class : 'wlm_toggle-switch wlm_toggle-adjacent',
                        tooltip : '<?php echo esc_js(__('This is a Legacy Feature and is no longer supported.', 'wishlist-member')); ?>',
                        type  : 'checkbox'
                    }
                </template>
            </div>
            <div class="col-md-8 col-xs-4">
                <template class="wlm3-form-group">
                    {
                        group_class : 'no-margin',
                        name  : 'salespage',
                        value : '',
                        type  : 'text',
                        placeholder : '<?php echo esc_js(__('Optional Sales Page URL', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
        </div>
        <br>
        <div class="panel-footer -content-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <?php echo wp_kses_post($tab_footer); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- keeping these for the sake of rolling back to 2.9x -->
<input type="hidden" name="afterregredirect">
<input type="hidden" name="loginredirect">
