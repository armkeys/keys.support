<?php

$shortcodes     = $this->wlmshortcode->shortcodes;
$wlm_shortcodes = [
    [
        'name'    => 'Merge Codes',
        'options' => [
            [
                'value' => '',
                'text'  => '',
            ],
        ],
    ],
];
for ($i = 0; $i < count($shortcodes); $i += 3) {
    $wlm_shortcodes[0]['options'][] = [
        'value' => sprintf('[%s]', $shortcodes[ $i ][0]),
        'text'  => $shortcodes[ $i + 1 ],
    ];
}

$custom_user_data = $this->wlmshortcode->custom_user_data;
if ($custom_user_data) {
    $wlm_shortcodes[] = [
        'name'    => 'Custom Registration Fields',
        'options' => [],
    ];
    foreach ($custom_user_data as $c) {
        $wlm_shortcodes[0]['options'][] = [
            'value' => sprintf('[wlm_custom %s]', $c),
            'text'  => $c,
        ];
    }
}

// Supply options for after reg and after login select.
$_pages         = get_pages('exclude=' . implode(',', $this->exclude_pages([], true)));
$afterreg_pages = [
    [
        'value' => '',
        'text'  => __('WordPress Home Page', 'wishlist-member'),
    ],
    [
        'value' => 'backtopost',
        'text'  => __('Redirect Back to Post', 'wishlist-member'),
    ],
];
if ($_pages) {
    foreach ($_pages as $_page) {
        $afterreg_pages[] = [
            'value' => $_page->ID,
            'text'  => $_page->post_title,
        ];
    }
}

$login_pages = $afterreg_pages;
unset($login_pages[1]);

printf("\n<script type='text/javascript'>\n var ppp_defaults = %s\nvar wlm_shortcodes = %s;\nvar afterreg_pages = %s;\nvar login_pages = %s;\n</script>\n", json_encode($this->ppp_defaults), json_encode($wlm_shortcodes), json_encode($afterreg_pages), json_encode($login_pages));

$ppp_settings = $this->get_option('payperpost');
?>
<div id="ppp-global-settings">
    <div class="content-wrapper">
        <div class="row mb-4">
            <?php $option_val = (int) $this->get_option('default_ppp'); ?>
            <div class="col-md-6">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Automatically Enable Pay Per Post for New Content', 'wishlist-member')); ?>',
                        name  : 'default_ppp',
                        value : '1',
                        checked_value : '<?php echo esc_js($option_val); ?>',
                        uncheck_value : '0',
                        class : 'wlm_toggle-switch notification-switch',
                        type  : 'checkbox',
                        tooltip : '<?php echo esc_js(__('If enabled, all newly created posts, pages and custom post types will automatically have the Pay Per Post feature enabled. This means any new posts, pages and custom post types will automatically appear in the Pay Per Posts tab in this section when created and can be configured accordingly.', 'wishlist-member')); ?>',
                        tooltip_size : 'md'
                    }
                </template>
                <input type="hidden" name="action" value="admin_actions" />
                <input type="hidden" name="WishListMemberAction" value="save" />
            </div>
        </div>
        <div class="row">
            <?php $option_val = isset($ppp_settings['requirecaptcha']) ? (int) $ppp_settings['requirecaptcha'] : 0; ?>
            <div class="col-xxxl-4 col-md-7 col-sm-7">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Require reCAPTCHA', 'wishlist-member')); ?>',
                        name  : 'payperpost[requirecaptcha]',
                        value : '1',
                        checked_value : '<?php echo esc_js($option_val); ?>',
                        uncheck_value : '0',
                        type  : 'toggle-adjacent-disable',
                        class : 'notification-switch',
                        tooltip : '<?php echo esc_js(__('If enabled, a reCAPTCHA will be displayed on the registration form that must be confirmed in order to register. This can help reduce spam registrations.<br><br>Note: The Configure button can be used to configure the reCAPTCHA options for this setting.', 'wishlist-member')); ?>',
                        tooltip_size : 'md'
                    }
                </template>
                <input type="hidden" name="action" value="admin_actions" />
                <input type="hidden" name="WishListMemberAction" value="save_payperpost_settings" />
            </div>
            <div class="col-md-5 col-sm-5 mb-sm-2">
                <button data-toggle="modal" data-target="#recaptcha-settings" class="btn -primary -condensed <?php echo esc_attr($option_val ? '' : '-disable'); ?>" <?php echo $option_val ? '' : 'disabled'; ?>>
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                </button>
                <br class="d-block d-sm-none">
                <br class="d-block d-sm-none">
                <br class="d-block d-sm-none">
            </div>
        </div>

        <div class="row">
            <?php $option_val = (int) $ppp_settings['custom_afterreg_redirect']; ?>
            <div class="col-xxxl-4 col-md-7 col-sm-7">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Custom After Registration Redirect', 'wishlist-member')); ?>',
                        name  : 'payperpost[custom_afterreg_redirect]',
                        value : '1',
                        checked_value : '<?php echo esc_js($option_val); ?>',
                        uncheck_value : '0',
                        type  : 'toggle-adjacent-disable',
                        class : 'notification-switch',
                        tooltip : '<?php echo esc_js(__('If enabled, a custom After Registration Redirect can be configured using the blue Configure button. The After Registration page will be displayed to users after a successful registration to this Pay Per Post. If disabled, the After Registration Redirect configured in the Advanced Options > Global Defaults > Redirects section will be used.<br><br>Note: This page will only appear one time for the user immediately after registration. The After Registration page cannot be viewed again. The After Login page will appear to the user after each login moving forward.', 'wishlist-member')); ?>',
                        tooltip_size : 'lg'
                    }
                </template>
                <input type="hidden" name="action" value="admin_actions" />
                <input type="hidden" name="WishListMemberAction" value="save_payperpost_settings" />
            </div>
            <div class="col-md-5 col-sm-5 mb-sm-2">
                <button data-toggle="modal" data-target="#custom-redirects-afterreg" class="btn -primary -condensed <?php echo esc_attr($option_val ? '' : '-disable'); ?>" <?php echo $option_val ? '' : 'disabled'; ?>>
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                </button>
                <br class="d-block d-sm-none">
                <br class="d-block d-sm-none">
                <br class="d-block d-sm-none">
            </div>
        </div>
        <div class="row">
            <?php $option_val = (int) $ppp_settings['custom_login_redirect']; ?>
            <div class="col-xxxl-4 col-md-7 col-sm-7">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Custom After Login Redirect', 'wishlist-member')); ?>',
                        name  : 'payperpost[custom_login_redirect]',
                        value : '1',
                        checked_value : '<?php echo esc_js($option_val); ?>',
                        uncheck_value : '0',
                        type  : 'toggle-adjacent-disable',
                        class : 'notification-switch',
                        tooltip : '<?php echo esc_js(__('If enabled, a custom After Login Redirect can be configured using the blue Configure button. The After Login page will be displayed to users after a successful login. If disabled, the After Login Redirect configured in the Advanced Options > Global Defaults > Redirects section will be used.', 'wishlist-member')); ?>',
                        tooltip_size : 'md'
                    }
                </template>
                <input type="hidden" name="action" value="admin_actions" />
                <input type="hidden" name="WishListMemberAction" value="save_payperpost_settings" />
            </div>
            <div class="col-md-5 col-sm-5 mb-sm-2">
                <button data-toggle="modal" data-target="#custom-redirects-login" class="btn -primary -condensed <?php echo esc_attr($option_val ? '' : '-disable'); ?>" <?php echo $option_val ? '' : 'disabled'; ?>>
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                </button>
                <br class="d-block d-sm-none">
                <br class="d-block d-sm-none">
                <br class="d-block d-sm-none">
            </div>
        </div>
    </div>

    <?php
        require 'settings/modals/recaptcha.php';
        require 'settings/modals/custom_redirects.php';
    ?>
</div>
