<?php

    require $this->legacy_wlm_dir . '/core/InitialValues.php';
    $keys         = [
        'onetime_login_link_email_subject',
        'onetime_login_link_email_message',
    ];
    $default_data = [];
    foreach ($keys as $key) {
        $default_data[ $key ] = $wishlist_member_initial_data[ $key ];
    }
    printf("\n<script type='text/javascript'>var default_data = %s;\n</script>\n", json_encode($default_data));
    ?>


<div class="content-wrapper">
    <div class="row">
        <?php $option_val = $this->get_option('login_limit_notify'); ?>
        <div class="col-md-6">
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('Notify Admin of Exceeded Logins', 'wishlist-member')); ?>',
                name : 'login_limit_notify',
                value : '1',
                checked_value : '<?php echo esc_js($option_val); ?>',
                uncheck_value : '0',
                class : 'wlm_toggle-switch notification-switch',
                type : 'checkbox',
                tooltip: '<?php echo esc_js(__('An email will be sent to the site Admin if a Member exceeds the Daily Login Limit if this setting is enabled.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('enable_login_redirect_override'); ?>
        <div class="col-md-7">
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('Allow WishList Member to Handle Login Redirect', 'wishlist-member')); ?>',
                name : 'enable_login_redirect_override',
                value : '1',
                checked_value : '<?php echo esc_js($option_val); ?>',
                uncheck_value : '0',
                class : 'wlm_toggle-switch notification-switch',
                type : 'checkbox',
                tooltip: '<?php echo esc_js(__('WishList Member will override all Login Redirects from other plugins, themes, shortcodes, etc. if this setting is enabled.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('enable_logout_redirect_override'); ?>
        <div class="col-md-7">
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('Allow WishList Member to Handle Logout Redirect', 'wishlist-member')); ?>',
                name : 'enable_logout_redirect_override',
                value : '1',
                checked_value : '<?php echo esc_js($option_val); ?>',
                uncheck_value : '0',
                class : 'wlm_toggle-switch notification-switch',
                type : 'checkbox',
                tooltip: '<?php echo esc_js(__('WishList Member will override all Logout Redirects from other plugins, themes, shortcodes, etc. if this setting is enabled.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
            <br>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <label for="">
                Default Login Limit:
                <?php $this->tooltip(__('This is the default number of times a user can login from a different IP address in a single day. <br><br>To permit an unlimited number of logins per user from different IP address simply leave the field blank.<br><br>Note: Daily Login Limits can be set for individual Members in the Members > Manage > Username > Advanced section.', 'wishlist-member'), 'lg'); ?>
            </label>
            <div class="row">
                <div class="col-sm-6 col-md-3 col-xxxl-2 col-xxl-3 no-margin">
                    <template class="wlm3-form-group">
                        {
                        name : 'login_limit',
                        type : 'number',
                        min : '0',
                        value : '<?php echo esc_js((int) $this->get_option('login_limit') + 0); ?>',
                        addon_right : 'IPs per day',
                        group_class : 'no-margin',
                        'data-initial' : '<?php echo esc_js((int) $this->get_option('login_limit') + 0); ?>',
                        class : 'text-center login-limit-apply',
                        help_block : '<?php echo esc_js(__('Set the field to 0 to disable.', 'wishlist-member')); ?>',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <label for="">
                Login Limit Message
                <?php $this->tooltip(__('The Login Limit Message will appear to Members on the login page if they reach the set Daily Login Limit.', 'wishlist-member')); ?>
            </label>
            <div class="row">
                <div class="col-md-6 no-margin">
                    <template class="wlm3-form-group">
                        {
                        name : 'login_limit_error',
                        value : '<?php echo wp_kses_data($this->get_option('login_limit_error')); ?>',
                        group_class : 'no-margin',
                        'data-initial' : '<?php echo wp_kses_data($this->get_option('login_limit_error')); ?>',
                        class : 'login-limit-error-apply',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('auto_login_after_confirm'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('Auto Login Member After Clicking Confirmation Link', 'wishlist-member')); ?>',
                name : 'auto_login_after_confirm',
                value : '1',
                checked_value : '<?php echo esc_js($option_val); ?>',
                uncheck_value : '0',
                class : 'wlm_toggle-switch notification-switch',
                type : 'checkbox',
                tooltip: '<?php echo esc_js(__('Members will be automatically logged in after clicking the confirmation link if this setting is enabled.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('Disable WordPress Admin Bar for Members when Logged In', 'wishlist-member')); ?>',
                name : 'show_wp_admin_bar',
                value : '0',
                uncheck_value : '1',
                checked_value : '<?php echo esc_js($this->get_option('show_wp_admin_bar')); ?>',
                class : 'wlm_toggle-switch notification-switch',
                type : 'checkbox',
                tooltip: '<?php echo esc_js(__('The WordPress Admin bar will be hidden from logged in Members if this setting is enabled.', 'wishlist-member')); ?>',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('show_onetime_login_option'); ?>
        <div class="col-sm-8 col-md-6 col-xxl-4 col-xxxl-3">
            <template class="wlm3-form-group">
                {
                label : '<?php echo esc_js(__('Show One-Time Login Link Option on Login Page', 'wishlist-member')); ?>',
                name : 'show_onetime_login_option',
                value : '1',
                checked_value : '<?php echo esc_js($option_val); ?>',
                uncheck_value : '0',
                class : 'wlm_toggle-switch notification-switch',
                type : 'toggle-adjacent-disable',
                tooltip: '<?php echo esc_js(__('A link to request for a One-Time Login Link will be displayed on all WishList Member login forms as well as the WordPress login form if this setting is enabled.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
        <div class="col">
            <button href="#" id="" class="btn -primary -condensed edit-notification <?php echo esc_attr($option_val && '1' == $option_val ? '' : '-disable'); ?>">
                <i class="wlm-icons">settings</i>
                <span><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>
</div>

<div data-classes="modal-lg" id="edit-notification-modal-info" data-id="edit-notification-modal" data-label="edit_notification_modal_modal" data-title="Configure: One-Time Login Email Template" style="display:none">
    <div class="body no-margin">
        <div class="content-wrapper -no-background -no-header no-margin">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation"><a class="nav-link active" href="#otll-template" role="tab" data-toggle="tab"><?php esc_html_e('Email Template', 'wishlist-member'); ?></a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" href="#otll-settings" role="tab" data-toggle="tab"><?php esc_html_e('Settings', 'wishlist-member'); ?></a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="otll-template">
                    <?php
                        $email_address = wlm_or($this->get_option('onetime_login_link_email_address'), $this->get_option('email_sender_address'));
                        $email_name    = wlm_or($this->get_option('onetime_login_link_email_name'), $this->get_option('email_sender_name'));
                        $email_subject = $this->get_option('onetime_login_link_email_subject');
                        $email_body    = $this->get_option('onetime_login_link_email_message');
                    ?>
                    <div class="row">
                        <template class="wlm3-form-group">{
                            addon_left : 'Sender Name',
                            group_class : '-label-addon mb-2',
                            type : 'text',
                            name : 'onetime_login_link_email_name',
                            column: 'col-md-6',
                            value: '<?php echo esc_js($email_name); ?>'
                            }</template>
                        <template class="wlm3-form-group">{
                            addon_left : 'Sender Email',
                            group_class : '-label-addon mb-2',
                            type : 'text',
                            name : 'onetime_login_link_email_address',
                            column: 'col-md-6',
                            value: '<?php echo esc_js($email_address); ?>'
                            }</template>
                        <template class="wlm3-form-group">{
                            addon_left : 'Subject',
                            group_class : '-label-addon mb-2',
                            type : 'text',
                            name : 'onetime_login_link_email_subject',
                            column: 'col-md-12',
                            value: '<?php echo esc_js($email_subject); ?>',
                            class: 'email-subject'
                            }</template>
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <textarea class="richtext form-control email-editor" data-name="onetime_login_link_email_message" name="onetime_login_link_email_message" id="onetime_login_link_email_message" skip-save="1"><?php echo esc_textarea($email_body); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="btn -default -condensed email-reset-button" data-target="onetime_login_link_email"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
                            <template class="wlm3-form-group">{
                                type : 'select',
                                column : 'col-md-5 pull-right no-margin no-padding',
                                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                                group_class : 'shortcode_inserter mb-0',
                                style : 'width: 100%',
                                options : get_merge_codes([{value : '[password]', text : 'Password'}, {value : '[one_time_login_link redirect=""]', text : 'One-Time Login Link'}]),
                                grouped: true,
                                class : 'insert_text_at_caret',
                                'data-target' : '[name=onetime_login_link_email_message]',
                                }</template>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="otll-settings">
                    <div class="row">
                        <template class="wlm3-form-group">
                            {
                                label : '<?php echo esc_js(__('Label', 'wishlist-member')); ?>',
                                type : 'text',
                                column : 'col-12',
                                name : 'onetime_login_link_label',
                                value : <?php echo json_encode(wishlistmember_instance()->get_option('onetime_login_link_label')); ?>
                            }
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal">
            <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
        </button>
        <button type="button" class="btn -primary save-button">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
        </button>
        <button class="-close btn -success -modal-btn save-button">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save & Close', 'wishlist-member'); ?></span>
        </button>
    </div>
</div>
