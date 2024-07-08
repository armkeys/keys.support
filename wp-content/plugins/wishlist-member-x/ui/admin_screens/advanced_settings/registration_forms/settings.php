<div class="content-wrapper">
    <form action="">
        <div class="row">
            <div class="col-md-12">
                <label class="mb-3"><?php esc_html_e('Registration Form Style', 'wishlist-member'); ?></label>
                    <?php
                    $this->tooltip(__('<p>The Registration Form may take on the look of a theme and may not match with how you want it to appear. It may pull elements from the theme styling (boxes around the fields, etc.)</p>
					<p>In a case like this, the Registration Form Style can be adjusted in the Advanced Options > Registrations > Registration Settings section in WishList Member.</p>
					<p>The option to set the Registration Form Style to “WishList Member Styled” can be used.</p>', 'wishlist-member'), 'xxl');
                    ?>
            </div>
            <template class="wlm3-form-group">
                {
                    column: 'col-auto',
                    name: 'FormVersion',
                    options : [ {value : 'themestyled', text : '<?php esc_attr_e('Theme Styled', 'wishlist-member'); ?>'}, {value : 'improved', text : '<?php esc_attr_e('WishList Member Styled', 'wishlist-member'); ?>'}, {value : '', text : '<?php esc_attr_e('Legacy', 'wishlist-member'); ?>'} ],
                    value: <?php echo json_encode($this->get_option('FormVersion')); ?>,
                    'data-initial': <?php echo json_encode($this->get_option('FormVersion')); ?>,
                    type: 'select',
                    style : 'width: 200px;',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
        <div class="row">
            <div class="col-md-3">
                <label><?php esc_html_e('Registration Session Timeout', 'wishlist-member'); ?></label>
                <?php $this->tooltip(__('<p>The set time for a User to complete a registration. The default length is set at 600 seconds.</p>', 'wishlist-member'), 'xxl'); ?>
            </div>
        </div>
        <div class="row">
            <?php $initial = wlm_or((int) $this->get_option('reg_cookie_timeout'), 600); ?>
            <template class="wlm3-form-group">
                {
                    addon_right : 'Seconds',
                    column: 'col-auto',
                    name: 'reg_cookie_timeout',
                    tooltip: '<?php echo esc_js(__('This sets the length of time before the registration page session times out.', 'wishlist-member')); ?>',
                    group_class: 'reg-cookie-timeout',
                    value: '<?php echo esc_js($initial); ?>',
                    "data-initial": '<?php echo esc_js($initial); ?>',
                    "data-default": '600',
                    type: 'number',
                    style: 'width: 5em;'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </form>
    <div class="row">
        <?php $option_val = $this->get_option('enable_short_registration_links'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable short Incomplete Registration links', 'wishlist-member')); ?>',
                    name  : 'enable_short_registration_links',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip : '<?php echo esc_js(__('If this is enabled then continue registration links are automatically shortened.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('redirect_existing_member'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Redirect to Existing Member Registration', 'wishlist-member')); ?>',
                    name  : 'redirect_existing_member',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip : '<?php echo esc_js(__('Automatically redirect customer to existing member registration form if payment email is already in the database.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
</div>
