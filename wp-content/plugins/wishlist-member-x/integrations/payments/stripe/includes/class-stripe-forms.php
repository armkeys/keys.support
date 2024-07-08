<?php

/**
 * Stripe Integration Init file
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

if (! class_exists('Stripe_Forms')) {

    class Stripe_Forms
    {
        /**
         * Singleton instance
         *
         * @var object
         */
        private static $instance;

        protected $forms;

        /**
         * Public function to generate a single instance
         *
         * @return object Stripe_Forms object instance
         */
        public static function instance()
        {
            if (empty(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Get view path.
         */
        public function get_view_path($handle)
        {
            return sprintf(wishlistmember_instance()->plugin_dir . '/extlib/wlm_stripe/%s.php', $handle);
        }

        /**
         * Output javascript for Stripe in footer
         */
        public function footer()
        {
            global $current_user;

            $stripepublishablekey = wlm_trim(Gateway_Utils::get_publishablekey());
            $stripeapikey         = wlm_trim(Gateway_Utils::get_stripeapikey());
            $skus                 = array_keys($this->forms);
            $stripe_cust_id       = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');

            if (! empty($stripe_cust_id)) {
                try {
                    PHPLib\WLM_Stripe::setApiKey($stripeapikey);
                    $cust = PHPLib\Customer::retrieve($stripe_cust_id);

                    if (! empty($cust)) {
                        $data['sc_details']['stripe_payment_method_id'] = $cust->invoice_settings->default_payment_method;
                    }
                } catch (\Exception $e) {
                    null;
                }
            }

            foreach ($this->forms as $frm) {
                fwrite(WLM_STDOUT, $frm);
            }

            ?>
                <script type="text/javascript">
                    var stripe = Stripe('<?php echo esc_js($stripepublishablekey); ?>');
                    var stripe_payment_button_status = true;
                    var stripe_card_type = "existing"; // Either existing for users with saved cards or new.

                    jQuery(function($) {
                    <?php
                    foreach ($skus as $sku) {
                        $unedited_sku = $sku;
                        $sku          = str_replace('-', '', $sku);
                        ?>
                            var elements = stripe.elements();
                            var style = {
                                base: {
                                    color: '#32325d',
                                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                                    fontSmoothing: 'antialiased',
                                    fontSize: '14px',
                                    '::placeholder': {
                                        color: '#aab7c4'
                                    },
                                    padding: '5px',
                                },
                                invalid: {
                                    color: '#fa755a',
                                    iconColor: '#fa755a'
                                }
                            };

                            var card<?php echo esc_js($sku); ?> = elements.create('card', {
                                style: style
                            });
                            card<?php echo esc_js($sku); ?>.mount('#card-element-<?php echo esc_js($sku); ?>');

                            // Handle real-time validation errors from the card Element.
                            card<?php echo esc_js($sku); ?>.addEventListener('change', function(event) {
                                var displayError = document.getElementById('card-errors-<?php echo esc_js($sku); ?>');
                                if (event.error) {
                                    displayError.textContent = event.error.message;
                                    displayError.style.display = "block";
                                } else {
                                    displayError.textContent = '';
                                    displayError.style.display = "none";
                                }
                            });

                        <?php

                        if (is_user_logged_in()) {
                            if (! empty($cust->invoice_settings->default_payment_method)) {
                                // User has Payment Method ID so skip all validation..
                                ?>
                                    jQuery(document).ready(function() {
                                        $('#regform-<?php echo esc_js($unedited_sku); ?> .regform-form').PopupRegForm({
                                            validate_last_name: false,
                                            validate_first_name: false,
                                            validate_cvc: false,
                                            validate_exp: false,
                                            validate_ccnumber: false,
                                            on_validate_success: function(form, fields, ui) {

                                                if (stripe_card_type == "existing") {
                                                    stripe_payment_button_status = false;
                                                    $('.regform-button').prop('disabled', true);
                                                    $('.regform-button').html('<?php echo esc_js(__('Processing...', 'wishlist-member')); ?>');
                                                    form.submit();
                                                } else if (stripe_card_type == "new") {

                                                    var cardData = {
                                                        name: fields.first_name.val() + " " + fields.last_name.val(),
                                                        email: fields.email.val()
                                                    }

                                                    stripe.createToken(card<?php echo esc_js($sku); ?>, cardData).then(function(result) {
                                                        if (result.error) {
                                                            // Inform the user if there was an error.
                                                            var errorElement = document.getElementById('card-errors-<?php echo esc_js($sku); ?>');
                                                            errorElement.textContent = result.error.message;

                                                            // From old.
                                                            ui.find('#card-errors-<?php echo esc_js($sku); ?>').html('<p>' + result.error.message + '</p>').show();
                                                            form.find('.regform-button').prop("disabled", false);
                                                            form.find('.regform-waiting').hide();
                                                        } else {
                                                            var token = result.token.id;
                                                            // Insert the token into the form so it gets submitted to the server.
                                                            form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");

                                                            if (stripe_payment_button_status == true) {
                                                                stripe_payment_button_status = false;
                                                                $('.regform-button').prop('disabled', true);
                                                                $('.regform-button').html('<?php echo esc_js(__('Processing...', 'wishlist-member')); ?>');
                                                                form.submit();
                                                            }
                                                        }
                                                    });
                                                    return false;
                                                }

                                            }
                                        });
                                    });
                                    return false;
                                <?php
                            } else {
                                // User logged in doesn't have Stripe Payment Method ID therefore.
                                // Do card validation but skip fname, lname validation.
                                ?>
                                    $('#regform-<?php echo esc_js($unedited_sku); ?> .regform-form').PopupRegForm({
                                        validate_last_name: false,
                                        validate_first_name: false,
                                        validate_cvc: false,
                                        validate_exp: false,
                                        validate_ccnumber: false,
                                        on_validate_success: function(form, fields, ui) {
                                            ui.find('.regform-waiting').show();

                                            var cardData = {
                                                name: fields.first_name.val() + " " + fields.last_name.val(),
                                                email: fields.email.val()
                                            }

                                            stripe.createToken(card<?php echo esc_js($sku); ?>, cardData).then(function(result) {
                                                if (result.error) {
                                                    // Inform the user if there was an error.
                                                    var errorElement = document.getElementById('card-errors-<?php echo esc_js($sku); ?>');
                                                    errorElement.textContent = result.error.message;

                                                    // From old.
                                                    ui.find('#card-errors-<?php echo esc_js($sku); ?>').html('<p>' + result.error.message + '</p>').show();
                                                    form.find('.regform-button').prop("disabled", false);
                                                    form.find('.regform-waiting').hide();
                                                } else {
                                                    var token = result.token.id;
                                                    // Insert the token into the form so it gets submitted to the server.
                                                    form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");

                                                    if (stripe_payment_button_status == true) {
                                                        stripe_payment_button_status = false;
                                                        $('.regform-button').prop('disabled', true);
                                                        $('.regform-button').html('<?php echo esc_js(__('Processing...', 'wishlist-member')); ?>');
                                                        form.submit();
                                                    }
                                                }
                                            });
                                            return false;
                                        }
                                    });
                                <?php
                            }
                        } else {
                            // User is not logged in so do all validations.
                            ?>
                                $('#regform-<?php echo esc_js($unedited_sku); ?> .regform-form').PopupRegForm({
                                    validate_cvc: false,
                                    validate_exp: false,
                                    validate_ccnumber: false,
                                    on_validate_success: function(form, fields, ui) {
                                        ui.find('.regform-waiting').show();

                                        var cardData = {
                                            name: fields.first_name.val() + " " + fields.last_name.val(),
                                            email: fields.email.val()
                                        }

                                        stripe.createToken(card<?php echo esc_js($sku); ?>, cardData).then(function(result) {
                                            if (result.error) {
                                                // Inform the user if there was an error.
                                                var errorElement = document.getElementById('card-errors-<?php echo esc_js($sku); ?>');
                                                errorElement.textContent = result.error.message;

                                                // From old.
                                                ui.find('#card-errors-<?php echo esc_js($sku); ?>').html('<p>' + result.error.message + '</p>').show();
                                                form.find('.regform-button').prop("disabled", false);
                                                form.find('.regform-waiting').hide();
                                            } else {
                                                var token = result.token.id;
                                                // Insert the token into the form so it gets submitted to the server.
                                                form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");

                                                if (stripe_payment_button_status == true) {
                                                    stripe_payment_button_status = false;
                                                    $('.regform-button').prop('disabled', true);
                                                    $('.regform-button').html('<?php echo esc_js(__('Processing...', 'wishlist-member')); ?>');
                                                    form.submit();
                                                }
                                            }
                                        });
                                        return false;
                                    }
                                });
                            <?php
                        }
                    }
                    ?>
                    });
                </script>
            <?php
        }

        /**
         * Load popup scripts and styles.
         */
        public function load_popup()
        {
            wp_enqueue_script('wlm-jquery-fancybox');
            wp_enqueue_style('wlm-jquery-fancybox');

            wp_enqueue_script('wlm-popup-regform-stripev3');
            wp_enqueue_style('wlm-popup-regform-style');
        }

        /**
         * Generate Stripe Form
         *
         * @param  array  $atts    Shortcode attributes.
         * @param  string $content Shortcode content.
         * @return string          Stripe form.
         */
        public function generate_stripe_form($atts, $content)
        {
            $this->load_popup();
            add_action('wp_footer', [$this, 'footer'], 100);

            global $current_user;
            $atts = shortcode_atts(
                [
                    'sku'                  => null,
                    'amount'               => 0,
                    'currency'             => '',
                    'coupon'               => 1,
                    'showlogin'            => 1,
                    'button_label'         => '',
                    'pay_button_label'     => '',
                    'hide_button_currency' => 0,
                    'class'                => null,
                    'on_page'              => 0,
                    'hide_logo'            => 0,
                    'hide_header_text'     => 0,
                    'show_order_summary'   => 0,
                ],
                $atts
            );
            extract($atts);

            $hide_logo && add_filter('wishlistmember_payment_form_logo', '__return_false');
            $hide_header_text && add_filter('wishlistmember_payment_form_heading', '__return_false');

            if (empty($sku)) {
                return null;
            }
            $amount   = $amount ? (float) $amount : 0;
            $currency = $currency ? $currency : '';
            $coupon   = (int) $coupon;
            $btn_hash = false;

            $stripeapikey       = Gateway_Utils::get_stripeapikey();
            $stripeconnections  = wishlistmember_instance()->get_option('stripeconnections');
            $stripethankyou     = wishlistmember_instance()->get_option('stripethankyou');
            $stripethankyou_url = wishlistmember_instance()->make_thankyou_url($stripethankyou);
            $stripesettings     = wishlistmember_instance()->get_option('stripesettings');
            $wpm_levels         = wishlistmember_instance()->get_option('wpm_levels');
            wishlistmember_instance()->inject_ppp_settings($wpm_levels);

            // Settings.
            $settings = $stripeconnections[ $sku ];
            $amt      = $settings['amount'];

            if (empty($currency)) {
                $cur = empty($stripesettings['currency']) ? 'USD' : $stripesettings['currency'];
            } else {
                $cur = $currency;
            }

            if ($settings['subscription']) {
                try {
                    PHPLib\WLM_Stripe::setApiKey($stripeapikey);
                    $plan  = [PHPLib\Price::retrieve($settings['plan'])];
                    $amt   = number_format($plan->amount / 100, 2, '.', '');
                    $plans = json_decode(stripslashes((string) wlm_arrval($settings, 'plans')));
                    if (is_array($plans)) {
                        foreach ($plans as $xplan) {
                            $xplan = wlm_trim($xplan);
                            if (! $xplan) {
                                continue;
                            }
                            $plan[] = PHPLib\Price::retrieve($xplan);
                        }
                    }
                } catch (\Exception $e) {
                    // Translators: %s: error message.
                    $msg = __('Error %s', 'wishlist-member');
                    return sprintf($msg, $e->getMessage());
                }
            } else {
                // Override by shorcode attribute.
                if ($amount || $currency) {
                    $btn_hash = true; // Lets check if this need hash.
                }
                $amt      = $amount ? $amount : $amt;
                $currency = $currency ? $currency : $cur;
                if ($btn_hash) {
                    $btn_hash = "{$stripeapikey}-{$amt}-{$currency}";
                }
                $coupon = false; // Disable coupon for one time payments.
            }

            $ppp_level  = wishlistmember_instance()->is_ppp_level($sku);
            $level_name = $wpm_levels[ $sku ]['name'];

            if ($ppp_level) {
                $level_name = $ppp_level->post_title;
            }

            $heading = empty($stripesettings['formheading']) ? 'Register for %level' : $stripesettings['formheading'];
            $heading = str_replace('%level', $level_name, $heading);

            if (empty($button_label)) {
                $btn_label = empty($stripesettings['buttonlabel']) ? 'Join %level' : $stripesettings['buttonlabel'];
            } else {
                $btn_label = $button_label;
            }
            $btn_label = str_replace('%level', $level_name, $btn_label);

            if (empty($pay_button_label)) {
                $panel_btn_label = empty($stripesettings['panelbuttonlabel']) ? 'Pay' : $stripesettings['panelbuttonlabel'];
            } else {
                $panel_btn_label = $pay_button_label;
            }
            $panel_btn_label = stripslashes(str_replace('%level', $level_name, $panel_btn_label));
            $logo            = str_replace('%level', $level_name, $stripesettings['logo']);
            $content         = wlm_trim($content);
            ob_start();
            ?>

            <?php if (empty($content)) : ?>
                    <button class="regform-button go-regform <?php echo esc_attr($class); ?>" name="go_regform" value="<?php echo esc_attr($amt); ?>" style="width: auto" id="go-regform-<?php echo esc_attr($sku); ?>" class="" href="#regform-<?php echo esc_attr($sku); ?>">
                        <?php echo wp_kses_post(stripslashes($btn_label)); ?>
                    </button>
            <?php else : ?>
                    <span>
                        <a data-fancybox data-options='{"src" : "#regform-<?php echo esc_attr($sku); ?>" }' href="javascript:;" id="go-regform-<?php echo esc_attr($sku); ?>" name="href_go_regform" class="go-regform"><?php echo wp_kses_post($content); ?></a>
                        <input type="hidden" class="go-regform-hidden" name="go-regform-hidden" value="<?php echo esc_attr($amt); ?>">
                    </span>
            <?php endif; ?>

            <?php $btn = ob_get_clean(); ?>

            <?php
            $additional_class = 'regform-stripe';
            if (! $coupon) {
                $additional_class .= ' nocoupon';
            }
            if ($hide_button_currency > 0) {
                $additional_class .= ' hide-button-currency';
            }

            $data['sc']                   = 'stripe';
            $data['shortcode_attributes'] = $atts;

            // Data to be use to edit the pay button and the amount description.
            $data['sc_details'] = [
                'sku'             => $atts['sku'],
                'is_subscription' => $settings['subscription'],
                'amt'             => $amt,
                'currency'        => $cur,
                'plan_details'    => $plan,
                'panel_btn_label' => $panel_btn_label,
            ];

            // Hook so that we can define the page this shortcode is loaded as the checkout page.
            $stripeapikey = Gateway_Utils::get_stripeapikey();

            $testmode = '';
            if (false !== strpos($stripeapikey, '_test_')) {
                $testmode = 1;
            }

            $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
            $level_name = $wpm_levels[ $atts['sku'] ]['name'];

            if ($settings['subscription']) {
                $product_amount = substr($plan[0]->unit_amount, 0, -2);
            } else {
                $product_amount = $amt;
            }
            $product_detail = [
                'sku'      => $atts['sku'],
                'sc'       => 'Stripe',
                'amount'   => $product_amount,
                'name'     => 'Stripe - ' . $level_name,
                'testmode' => $testmode,
            ];
            do_action('wishlistmember_button_checkout', $product_detail);
            // End Checkout tracking hook.
            if (! is_user_logged_in()) {
                $path = sprintf(wishlistmember_instance()->plugin_dir . '/extlib/wlm_stripe/form_new_fields.php');
                include $path;
                $this->forms[ $sku ] = wlm_build_payment_form($data, $additional_class, $on_page);
            } else {
                $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');

                if (! empty($stripe_cust_id)) {
                    PHPLib\WLM_Stripe::setApiKey($stripeapikey);
                    try {
                        $cust = PHPLib\Customer::retrieve($stripe_cust_id);
                    } catch (\Exception $e) {
                    }

                    // Proration is allowed.
                    if ('yes' === $settings['allow_proration_for_level']) {
                        $additional_class .= ' allow-proration-for-level';
                    }

                    $users_levels    = wlmapi_get_member_levels($current_user->ID);
                    $existing_levels = [];

                    // Check if txn_id matches with stripe subs.
                    try {
                        if (! empty($settings['subscription'])) {
                            if (! $cust->subscriptions) {
                                $cust = PHPLib\Customer::retrieve(
                                    [
                                        'id'     => $stripe_cust_id,
                                        'expand' => ['subscriptions'],
                                    ]
                                );
                            }

                            // Get only the levels with a subscription ID that matches a STRIPE PLAN.
                            if (! empty($cust->subscriptions->data)) {
                                foreach ($cust->subscriptions->data as $d) {
                                    if (! in_array($d->status, ['trialing', 'active'], true)) {
                                        // Do not allow proration for non-active subscriptions.
                                        continue;
                                    }
                                    foreach ($users_levels as $user_level) {
                                        if ($user_level->Level_ID == $sku) {
                                            // Do not allow proration for the same level.
                                            continue;
                                        }
                                        if (preg_match('/^cus_/', $user_level->TxnID)) {
                                            list($c_id, $plan_id) = explode('-', $user_level->TxnID);
                                            if ($d->plan->id == $plan_id) {
                                                array_push($existing_levels, $user_level);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $status = 'fail&err=' . $e->getMessage();
                    }

                    try {
                        if (! empty($cust)) {
                            $data['sc_details']['stripe_customer_id']       = $stripe_cust_id;
                            $data['sc_details']['stripe_payment_method_id'] = $cust->invoice_settings->default_payment_method;

                            $payment_method                          = PHPLib\PaymentMethod::retrieve($cust->invoice_settings->default_payment_method);
                            $data['sc_details']['stripe_card_last4'] = $payment_method->card->last4;
                            $data['sc_details']['stripe_card_brand'] = $payment_method->card->brand;
                        }
                    } catch (\Exception $e) {
                        null;
                    }
                }

                $path = sprintf(wishlistmember_instance()->plugin_dir . '/extlib/wlm_stripe/form_existing_fields.php');
                include $path;
                $this->forms[ $sku ] = wlm_build_payment_form($data, $additional_class, $on_page);
            }
            if ($on_page) {
                $form                = $this->forms[ $sku ];
                $this->forms[ $sku ] = '';
                return $form;
            } else {
                return $btn;
            }
        }
    }
}
