<div <?php echo $on_page ? 'class="wlm3-onpage-paymentform"' : 'style="display: none" class="wlm3-fancybox"'; ?>>
    <div data-level-id="<?php echo esc_attr($id); ?>" id="regform-<?php echo esc_attr($id); ?>" class="wlm-regform container-rs regform <?php echo esc_attr($additional_classes); ?>">
        <div class="regform-container row-rs">
            <?php
            $logo      = apply_filters('wishlistmember_payment_form_logo', wlm_trim($logo));
            $heading   = apply_filters('wishlistmember_payment_form_heading', wlm_trim($heading));
            $showlogin = ! is_user_logged_in() && $showlogin;
            ?>
            <?php if ($logo || $heading || $showlogin) : ?>
                <div class="col-12 regform-header">
                    <?php
                    if ($logo) {
                        printf('<img class="regform-logo" src="%s">', esc_url($logo));
                    }
                    if ($heading) {
                        printf('<p class="heading-2">%s</p>', esc_html(stripslashes($heading)));
                    }
                    ?>
                    <?php if ($showlogin) : ?>
                        <p class="regform-login-link-holder">
                            <?php echo wp_kses_post(__('Existing users please <a href="" class="regform-open-login">login</a> before purchasing ', 'wishlist-member')); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ('fail' === wlm_get_data()['status']) : ?>
                <div class="regform-error">
                    <p>
                        <?php
                        if (isset(wlm_get_data()['status']) && 'fail' === wlm_get_data()['status']) {
                            esc_html_e('An error has occured while processing payment, please try again', 'wishlist-member');
                        }
                        if (! empty(wlm_get_data()['reason'])) {
                            echo '<br/>';
                            // Translators: %s is the reason for the error.
                            printf(esc_html__('Reason: %s', 'wishlist-member'), esc_html(wlm_get_data()['reason']));
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="regform-new">
                <form action="<?php echo esc_url($form_action); ?>" class="regform-form" id="regform-form-<?php echo esc_attr(str_replace('-', '', $data['sc_details']['sku'])); ?>" method="post">
                    <?php do_action('wishlistmember_payment_form_open', $data); ?>
                    <input type="hidden" name="form_timestamp" value="<?php echo time(); ?>" />

                    <?php if (! empty($panel_beforetext)) : ?>
                        <div class="col-12 regform-description">
                            <p class="regform-aftertext"><?php echo esc_html($panel_beforetext); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php
                    $fields = apply_filters('wishlistmember_payment_form_fields', $fields, $data);
                    foreach ($fields as $f) {
                        $colsize = $f['col'] ? $f['col'] : 'col-6';
                        switch ($f['type']) {
                            case 'heading':
                                printf('<p class="heading-3 %s" style="%s">%s</p>', esc_attr($f['class']), esc_attr($f['style']), esc_html($f['text']));
                                break;
                            case 'hidden':
                                echo sprintf('<input type="hidden" name="%s" value="%s"/>%s', esc_attr($f['name']), esc_attr($f['value']), "\n");
                                break;
                            case 'text':
                                echo sprintf(
                                    '<div class="txt-fld %6$s %1$s"><label for="%1$s">%2$s</label><input id=""'
                                        . ' class="regform-%1$s %5$s" name="%1$s" type="text" placeholder="%3$s" value="%4$s" /><span class="error_text">%7$s</span><span class="success_text">%8$s</span><span class="checking_text">%9$s</span></div>',
                                    esc_attr($f['name']),
                                    esc_html($f['label']),
                                    esc_attr($f['placeholder'] ? $f['placeholder'] : $f['label']),
                                    esc_attr($f['value']),
                                    esc_attr($f['class']),
                                    esc_attr($colsize),
                                    esc_html($f['error_text']),
                                    esc_html($f['success_text']),
                                    esc_html($f['checking_text'])
                                );
                                break;
                            case 'select':
                                $options = [];
                                foreach ($f['value'] as $k => $v) {
                                    if (is_numeric($k)) {
                                        $options[] = sprintf('<option>%s</option>', $v);
                                    } else {
                                        $options[] = sprintf('<option value="%s">%s</option>', htmlentities($k), $v);
                                    }
                                }

                                echo sprintf(
                                    '<div class="txt-fld %6$s %1$s"><label for="%1$s">%2$s</label><select id="" class="regform-%1$s %5$s" name="%1$s" placeholder="%3$s">%4$s</select></div>',
                                    esc_attr($f['name']),
                                    esc_html($f['label']),
                                    esc_attr($f['placeholder'] ? $f['placeholder'] : $f['label']),
                                    wp_kses(
                                        implode('', $options),
                                        [
                                            'option' => [
                                                'value'    => true,
                                                'selected' => true,
                                            ],
                                        ]
                                    ),
                                    esc_attr($f['class']),
                                    esc_attr($colsize)
                                );
                                break;
                            case 'cc_fields':
                                if (apply_filters('wishlistmember_payment_form_show_credit_card_fields', true, $data, $f)) {
                                    $cc_has = (array) $f['has'];
                                    if (in_array('cc_type', $cc_has)) {
                                        $options   = [];
                                        $options[] = sprintf('<option value="%s">%s</option>', 'Visa', __('Visa', 'wishlist-member'));
                                        $options[] = sprintf('<option value="%s">%s</option>', 'MasterCard', __('MasterCard', 'wishlist-member'));
                                        $options[] = sprintf('<option value="%s">%s</option>', 'Discover', __('Discover', 'wishlist-member'));
                                        $options[] = sprintf('<option value="%s">%s</option>', 'Amex', __('American Express', 'wishlist-member'));

                                        echo sprintf(
                                            '<div class="txt-fld col-3"><label>%1$s</label><select name="cc_type">%2$s</select></div>',
                                            esc_html__('Card Type', 'wishlist-member'),
                                            wp_kses(
                                                implode('', $options),
                                                [
                                                    'option' => [
                                                        'value'    => true,
                                                        'selected' => true,
                                                    ],
                                                ]
                                            )
                                        );
                                    }

                                    // Card number.
                                    echo sprintf(
                                        '<div class="txt-fld col-4"><label>%1$s</label><input type="text" autocomplete="false" class="regform-cardnumber" name="cc_number" placeholder="●●●● ●●●● ●●●● ●●●●"></div>',
                                        esc_html__('Card Number', 'wishlist-member')
                                    );

                                    // Card expiration.
                                    echo sprintf(
                                        '<div class="col-3 col-6-sm"><div class="txt-fld expires"><label>%1$s</label><input autocomplete="false" placeholder="MM" maxlength="2"  class="regform-expmonth floated-input" name="cc_expmonth" type="text" /><input autocomplete="false" placeholder="YY" maxlength="2"  class="regform-expyear floated-input" name="cc_expyear" type="text" /></div></div>',
                                        esc_html__('Expires', 'wishlist-member')
                                    );

                                    // Card cvc.
                                    if (in_array('cc_cvc', $cc_has)) {
                                        echo sprintf(
                                            '<div class="txt-fld code col-2 col-6-sm"><label>%1$s</label><input autocomplete="false" placeholder="CVC" maxlength="4"  class="regform-cvc" name="cc_cvc" type="text" /></div>',
                                            esc_html__('Card Code', 'wishlist-member')
                                        );
                                    }
                                }
                                break;
                            default:
                                do_action('wishlistmember_payment_form_custom_field', $data, $f);
                                break;
                        }
                    }
                    ?>

                        <?php if (! empty($panel_aftertext)) : ?>
                            <div class="col-12 regform-description">
                                <p class="regform-aftertext"><?php echo esc_html($panel_aftertext); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="btn-fld col-12">
                            <?php if (apply_filters('wishlistmember_payment_form_show_button', true, $data, $f)) : ?>
                                <div class="row-rs">
                                    <button class="regform-button col-4" name="regform-button"><?php echo esc_html($panel_button_label); ?></button>
                                    <div class="btn-fld-info col-8">

                                        <?php if ($data['payment_description']) : ?>
                                            <?php echo wp_kses_post($data['payment_description']); ?>
                                        <?php elseif ($amt || $amount) : ?>
                                            <?php echo esc_html($currency); ?> <?php echo number_format($amt ? $amt : $amount, 2, '.', ''); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button style="display:none" name="product_price" id="amount" value=""><?php echo esc_html($button_label); ?></button>
                            <?php endif; ?>
                        </div>
                        <?php do_action('wishlistmember_payment_form_close', $data); ?>
                </form>
            </div>
            <?php if (! is_user_logged_in()) : ?>
            <div class="regform-login" style="display: none;">
                <form method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>">
                    <div class="txt-fld col-12">
                        <label for="wlm-regform-username"><?php esc_html_e('Username:', 'wishlist-member'); ?></label>
                        <input id="wlm-regform-username" class="regform-username" name="log" type="text" placeholder="Username" />
                    </div>
                    <div class="txt-fld col-12">
                        <label for="wlm-regform-password"><?php esc_html_e('Password:', 'wishlist-member'); ?></label>
                        <input id="wlm-regform-password" class="regform-password" name="pwd" type="password" placeholder="************" />
                    </div>
                    <input type="hidden" name="wlm_redirect_to" value="<?php echo esc_url(get_permalink()); ?>#regform-<?php echo esc_attr($id); ?>" />
                    <div class="btn-fld col-12">
                        <a href="#" class="regform-close-login"><?php esc_html_e('Cancel', 'wishlist-member'); ?></a>
                        <button class="regform-button"><?php esc_html_e('Login', 'wishlist-member'); ?></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
