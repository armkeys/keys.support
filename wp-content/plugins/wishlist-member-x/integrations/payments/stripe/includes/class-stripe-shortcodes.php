<?php

/**
 * Stripe integration shortcodes class.
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

if (! class_exists('Stripe_Shortcodes')) {
    /**
     * Stripe Shortcodes class
     */
    class Stripe_Shortcodes
    {
        /**
         * Singleton instance
         *
         * @var object
         */
        private static $instance;

        /**
         * TinyMCE lightbox files
         *
         * @var array
         */
        public $tinymce_lightbox_files = [];

        /**
         * Constructor
         */
        public function __construct()
        {

            // Added this as some sites where mb_detect_encoding is disabled will.
            // Produce a fatal error because /extlib/Stripe/init.php wasn't loaded.
            if (class_exists('PHPLib\WLM_Stripe')) {
                PHPLib\WLM_Stripe::setAppInfo(
                    'WishList Member',
                    wishlistmember_instance()->version,
                    'https://wishlistmember.com/',
                    'pp_partner_FlHHCjuMMJYOXI'
                );
            }

            add_action('edit_user_profile', [$this, 'profile_form']);
            add_action('show_user_profile', [$this, 'profile_form']);
            add_action('profile_update', [$this, 'update_profile'], 9, 2);

            add_action('wishlistmember_cancel_user_levels', [$this, 'cancel_stripe_subs_via_hook'], 99, 2);

            add_filter('wishlist_member_user_custom_fields', [$this, 'add_stripe_field'], 99, 2);
            add_filter('wishlistmember_post_update_user', [$this, 'save_stripe_field'], 99, 1);

            add_action('admin_notices', [$this, 'notices']);

            add_action('wp_footer', [$this, 'footer']);

            wishlistmember_instance()->tinymce_lightbox_files[] = $this->get_view_path('tinymce_lightbox');

            add_shortcode('wlm_stripe_btn', [$this, 'wlm_stripe_btn']);
            add_shortcode('wlm_stripe_profile', [$this, 'wlm_stripe_profile']);

            add_filter('wishlistmember_integration_shortcodes', [$this, 'add_stripe_shortcodes']);

            add_filter('wishlistmember_payment_form_show_credit_card_fields', [$this, 'insert_stripe_cc_fields'], 99, 2);
            add_filter('wishlistmember_payment_form_show_button', [$this, 'show_stripe_pay_button'], 99, 2);
            add_action('wishlistmember_payment_form_custom_field', [$this, 'insert_stripe_custom_fields'], 99, 2);
        }

        /**
         * Public function to generate a single instance
         *
         * @return object Stripe_Shortcodes object instance
         */
        public static function instance()
        {
            if (empty(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Display Notices
         */
        public function show_stripe_pay_button($show_button, $data)
        {
            if ('stripe' !== wlm_arrval($data, 'sc')) {
                return $show_button;
            }
            $stripe_cur = $data['sc_details']['currency'];
            if ($data['sc_details']['is_subscription']) { // If subscription then include the interval of the payment.
                // @since 3.6 display dropdown if stripe is configured to have multiple plans for a level.
                $show_options = count($data['sc_details']['plan_details']) > 1;
                if ($show_options) {
                    $plan_options = sprintf('<option value="">%s</option>', __('Select a Plan', 'wishlist-member'));
                }

                foreach ($data['sc_details']['plan_details'] as $plan_details) {
                    $stripe_cur = str_replace(
                        ['USD ', 'EUR ', 'GBP ', 'JPY ', 'INR '],
                        ['$', '€', '£', '¥', '₹'],
                        strtoupper($plan_details->currency ? $plan_details->currency : $stripe_cur) . ' '
                    );
                    $xamt       = number_format($plan_details->unit_amount / 100, 2, '.', '');
                    if ($plan_details->recurring) {
                        $interval_count = $plan_details->recurring->interval_count;
                        $interval       = ucwords(strtolower($plan_details->recurring->interval));

                        $every_text = __('Every', 'wishlist-member');
                        switch ($interval) {
                            case 'Day':
                                $interval_text_1 = __('Daily', 'wishlist-member');
                                $interval_text_2 = __('Days', 'wishlist-member');
                                break;
                            case 'Week':
                                $interval_text_1 = __('Weekly', 'wishlist-member');
                                $interval_text_2 = __('Weeks', 'wishlist-member');
                                break;
                            case 'Month':
                                $interval_text_1 = __('Monthly', 'wishlist-member');
                                $interval_text_2 = __('Months', 'wishlist-member');
                                break;
                            case 'Year':
                                $interval_text_1 = __('Yearly', 'wishlist-member');
                                $interval_text_2 = __('Years', 'wishlist-member');
                                break;
                        }

                        if (1 === (int) $interval_count) {
                            $pay_desc = sprintf('%s%s %s', $stripe_cur, $xamt, $interval_text_1);
                        } else {
                            $pay_desc = sprintf('%s%s %s %d %s', $stripe_cur, $xamt, $every_text, $interval_count, $interval_text_2);
                        }
                    } else {
                        $pay_desc = sprintf('%s%s One time', $stripe_cur, $xamt);
                    }

                    // @since 3.6 prepare dropdown options.
                    if ($show_options) {
                        $plan_options .= sprintf('<option value="%s">%s</option>', $plan_details->id, $pay_desc);
                    }
                }

                // @since 3.6 generate dropdown select input.
                if ($show_options) {
                    $pay_desc      = sprintf('<span style="display: inline">' . esc_html__('Payment Plan', 'wishlist-member') . '&nbsp; </span><select name="stripe_plan" class="regform-payment_plan">%s</select>', $plan_options);
                    $product_price = '';
                } else {
                    $product_price = $pay_desc;
                }
            } else {
                $pay_desc = $stripe_cur . number_format($data['sc_details']['amt'], 2, '.', '');
            }
            $allowed_html = [
                'span'   => ['style' => true],
                'select' => [
                    'name'  => true,
                    'class' => true,
                ],
                'option' => ['value' => true],
            ];
            ?>
            <div class="wlm-regform-order-summary-container" data-currency="<?php echo esc_attr($stripe_cur); ?>" data-amount="<?php echo esc_attr($xamt); ?>">
            <?php
            if (wlm_arrval($data, 'shortcode_attributes', 'show_order_summary')) {
                $level = new \WishListMember\Level($data['shortcode_attributes']['sku']);
                ?>
                <p class="heading-3" style="margin-left: 0"><?php esc_html_e('Order Summary', 'wishlist-member'); ?></p>
                <div class="wlm-regform-order-summary">
                    <p class="wlm-regform-order-summary-amount">
                        <strong><?php echo esc_html($level->name); ?></strong>
                        <span data-currency="<?php echo esc_attr($stripe_cur); ?>" style="float:right;"><?php echo esc_html($xamt); ?></span>
                        <span class="wlm-regform-order-summary-description">
                            <?php echo esc_html($pay_desc); ?>
                        </span>
                    </p>
                    <p class="wlm-regform-order-summary-due-now">
                        <?php esc_html_e('Due Today', 'wishlist-member'); ?>
                        <strong style="float:right; font-size:1.5em;">
                            <span data-currency="<?php echo esc_attr($stripe_cur); ?>" class="wlm-regform-order-summary-due-now-amount"><?php echo esc_html($xamt); ?></span>
                        </strong>
                    </p>
                </div>
                <button class="regform-button -wide -round" name="regform-button" data-text="<?php echo esc_attr($data['sc_details']['panel_btn_label']); ?>" data-price="<?php echo esc_attr(wlm_trim($product_price)); ?>">
                    <?php echo esc_html($data['sc_details']['panel_btn_label']); ?>
                </button>
                <?php
            } else {
                ?>
                <div style="float:right;">
                    <button class="regform-button" name="regform-button" data-currency="<?php echo esc_attr($stripe_cur); ?>" data-text="<?php echo esc_attr($data['sc_details']['panel_btn_label']); ?>" data-price="<?php echo esc_attr(wlm_trim($product_price)); ?>">
                        <?php echo esc_html($data['sc_details']['panel_btn_label']); ?>
                    </button>
                </div>
                <div class="btn-fld-info" style="float:left; text-align: left; white-space: nowrap">
                    <?php echo wp_kses($pay_desc, $allowed_html); ?>
                </div>
                <?php
            }
            ?>
            </div>
            <?php
            return false;
        }

        /**
         * Add Stripe Shortcodes to the Shortcode Generator
         * using the `wishlistmember_integration_shortcodes` filter.
         *
         * @param  array $shortcodes Shortcodes.
         * @return array                       Shortcodes.
         */
        public function add_stripe_shortcodes($shortcodes)
        {
            // Prepare membership levels.
            $levels = [];
            foreach (\WishListMember\Level::get_all_levels(true) as $level) {
                $levels[ $level->ID ] = ['label' => $level->name];
            }

            // Get stripe settings.
            $stripesettings = wishlistmember_instance()->get_option('stripesettings');

            // Add shortcodes to shortcode builder.
            $shortcodes['Stripe Integration'] = [
                // Paymemt buttons.
                'wlm_stripe_btn'     => [
                    'label'      => __('Stripe Registration Button', 'wishlist-member'),
                    'attributes' => [
                        'sku'              => [
                            'columns'     => 6,
                            'type'        => 'select',
                            'options'     => $levels,
                            'label'       => __('Membership Level', 'wishlist-member'),
                            'placeholder' => __('Select a Membership Level', 'wishlist-member'),
                        ],
                        'pay_button_label' => [
                            'columns'     => 6,
                            'type'        => 'text',
                            'label'       => __('Pay Button Label', 'wishlist-member'),
                            'placeholder' => wlm_or(wlm_arrval($stripesettings, 'panelbuttonlabel'), __('Pay', 'wishlist-member')),
                            'dependency'  => '[name="sku"] option:not([value=""]):selected',
                        ],
                        'on_page'          => [
                            'columns'    => 6,
                            'type'       => 'radio',
                            'inline'     => true,
                            'label'      => __('Show in Popup Window', 'wishlist-member'),
                            'tooltip'    => __('If Yes is selected, a payment button will be displayed and a popup payment form will appear when that button is clicked. If No is selected, a payment form will be automatically inserted directly on the page.', 'wishlist-member'),
                            'options'    => [
                                '' => [
                                    'label' => __('Yes', 'wishlist-member'),
                                ],
                                1  => [
                                    'label' => __('No', 'wishlist-member'),
                                ],
                            ],
                            'default'    => '',
                            'dependency' => '[name="sku"] option:not([value=""]):selected',
                        ],
                        'button_label'     => [
                            'columns'     => 6,
                            'type'        => 'text',
                            'label'       => __('Popup Button Label', 'wishlist-member'),
                            'placeholder' => wlm_or(wlm_arrval($stripesettings, 'buttonlabel'), __('Join %level', 'wishlist-member')),
                            'dependency'  => '[name="on_page"]:not([value=1]):checked&&[name="sku"] option:not([value=""]):selected',
                        ],
                        'coupon'           => [
                            'columns'    => 12,
                            'type'       => 'radio',
                            'inline'     => true,
                            'label'      => __('Enable Coupon Code', 'wishlist-member'),
                            'options'    => [
                                1  => [
                                    'label' => __('Yes', 'wishlist-member'),
                                ],
                                '' => [
                                    'label' => __('No', 'wishlist-member'),
                                ],
                            ],
                            'default'    => 1,
                            'dependency' => '[name="sku"] option:not([value=""]):selected',
                        ],
                    ],
                ],
                // Profile shortcode.
                'wlm_stripe_profile' => [
                    'label'      => __('Profile Page', 'wishlist-member'),
                    'attributes' => [
                        'levels'        => [
                            'columns' => 3,
                            'type'    => 'checkbox',
                            'options' => [
                                'all' => [
                                    'label'     => __('Membership Levels', 'wishlist-member'),
                                    'unchecked' => 'no',
                                ],
                            ],
                            'default' => 'all',
                        ],
                        'level-choices' => [
                            'columns'     => 9,
                            'type'        => 'select-multiple',
                            'separator'   => ',',
                            'options'     => $levels,
                            'placeholder' => __('All Levels', 'wishlist-member'),
                            'dependency'  => '[name="levels"]:checked',
                        ],
                        'include_posts' => [
                            'type'    => 'checkbox',
                            'options' => [
                                'yes' => [
                                    'label'     => __('Include Pay-Per-Posts', 'wishlist-member'),
                                    'unchecked' => 'no',
                                ],
                            ],
                            'default' => 'no,',
                        ],
                    ],
                ],
            ];

            return $shortcodes;
        }

        /**
         * Add Custom Fields to the Stripe Payment Form
         * using the `wishlistmember_payment_form_custom_field` action.
         *
         * @param array $data Form Data.
         * @param array $f    Form Field Data.
         */
        public function insert_stripe_custom_fields($data, $f)
        {
            switch (wlm_arrval($f, 'type')) {
                case 'proration':
                    if (isset($data['sc']) && 'stripe' === $data['sc']) {
                        $prorate            = $f['prorate'];
                        $sku                = str_replace('-', '', $data['sc_details']['sku']);
                        $upgrade_level_text = __('Upgrade an Existing Plan', 'wishlist-member');
                        $no_upgrade_text    = __('Buy Level as New Plan - Do not Upgrade', 'wishlist-member');
                        if ('yes' === $prorate) {
                            if (! empty($f['existing_levels'])) {
                                ?>

                                <script type="text/javascript">
                                    var form = jQuery('form#regform-form-<?php echo esc_js($sku); ?>');
                                    jQuery(function() {
                                        jQuery('.prorate-<?php echo esc_js($sku); ?>').show();
                                    });
                                    jQuery('body').on('click', '.<?php echo esc_js($sku); ?>-stripe_radio_prorate', function() {
                                        jQuery('#<?php echo esc_js($sku); ?>-prorate-level').toggle('prorate_level' === this.value);
                                        jQuery(form).trigger('stripe-prorate-change.wlm', <?php echo wp_json_encode([$sku]); ?> );
                                    });

                                    jQuery('body').on('change', '#<?php echo esc_js($sku); ?>-proration-levels', function() {
                                        var selected_level_id = jQuery(this).find('option:selected').val();
                                        var levels = <?php echo wp_json_encode($f['existing_levels']); ?>;
                                        form.find('[name="txn_id"]').length || jQuery('<input type="hidden" name="txn_id">').appendTo(form);

                                        for (var i = 0; i < levels.length; i++) {
                                            if (selected_level_id == levels[i].Level_ID) {
                                                form.find('[name="txn_id"]').val(levels[i].TxnID);
                                            }
                                        }
                                        jQuery(form).trigger('stripe-prorate-change.wlm', <?php echo wp_json_encode([$sku]); ?> );
                                    });
                                </script>

                                <div class="txt-fld col-12">
                                    <!-- current level in modal -->
                                    <input type="hidden" name="upgrade_to_level" value="<?php echo esc_attr($sku); ?>" />
                                    <label style="display: inline !important;">
                                        <input type="radio" class="<?php echo esc_attr($sku); ?>-stripe_radio_prorate wlm_stripe_radio" checked="checked" value="no_prorate" name="wlm_stripe_radio_prorate"> <?php echo esc_html($no_upgrade_text); ?>
                                    </label>
                                    <label style="display: inline !important;">
                                        <input type="radio" class="<?php echo esc_attr($sku); ?>-stripe_radio_prorate wlm_stripe_radio" value="prorate_level" name="wlm_stripe_radio_prorate"> <?php echo esc_html($upgrade_level_text); ?>

                                    </label>
                                    <div id="<?php echo esc_attr($sku); ?>-prorate-level" style="display:none;">
                                        <?php

                                        $label   = esc_html__('Upgrade Plan', 'wishlist-member');
                                        $options = [
                                            '<option value="select">Select a Level to Upgrade</option>',
                                        ];
                                        foreach ($f['existing_levels'] as $level) {
                                            $options[] = sprintf('<option value="%s">%s</option>', $level->Level_ID, $level->Name);
                                        }

                                        echo sprintf(
                                            '<div class="txt-fld %6$s %1$s"><label for="%1$s">%2$s</label><select id="%7$s-proration-levels" class="regform-%1$s %5$s" name="%1$s" placeholder="%3$s">%4$s</select></div>',
                                            esc_html(__('level_id', 'wishlist-member')),
                                            esc_html($label),
                                            esc_attr($f['placeholder'] ? $f['placeholder'] : $label),
                                            wp_kses(
                                                implode('', $options),
                                                [
                                                    'option' => [
                                                        'value'    => true,
                                                        'selected' => true,
                                                    ],
                                                ]
                                            ),
                                            esc_attr($f['type']),
                                            'col-6',
                                            $sku
                                        );

                                        ?>
                                    </div>
                                </div><br>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="txt-fld col-12">
                                <label style="display: inline !important;">
                                    <?php esc_html_e('Prorates are not allowed for this Level', 'wishlist-member'); ?>
                                </label>
                            </div>
                            <?php
                        }
                    }
                    break;
            }
        }

        /**
         * Add Credit Card Fields to the Stripe Payment Form
         * using the `wishlistmember_payment_form_show_credit_card_fields` filter.
         *
         * @param boolean $show_fields Show Fields.
         * @param array   $data        Form Data.
         */
        public function insert_stripe_cc_fields($show_fields, $data)
        {
            /**
 * STRIPE INTEGRATION START
*/
            if (isset($data['sc']) && 'stripe' === $data['sc']) {
                $sku = str_replace('-', '', $data['sc_details']['sku']);
                // For Stripe we use Stripe Elements which generates the CC input fields..
                if (! $data['sc_details']['stripe_payment_method_id']) {
                    ?>
                    <div class="wlm-stripe-form-row col-12">
                        <div id="card-element-<?php echo esc_attr($sku); ?>" class="card-element" style="height: 40px;
                            padding: 10px 12px; border: 1px solid #E4E4E4; border-radius: 1px; background-color: #ffffff;
                            margin: 0">
                            <!-- A Stripe Element will be inserted here. -->
                        </div>
                        <!-- Used to display form errors. -->
                        <div id="card-errors-<?php echo esc_attr($sku); ?>" role="alert" class="regform-error" style="display:none;"></div>
                    </div>
                    <?php
                } else {
                    $existing_card_text = __('Use existing card. ', 'wishlist-member');
                    $diff_card_text     = __('Click here to select a different card. ', 'wishlist-member');
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function() {
                            jQuery(".<?php echo esc_js($sku); ?>-stripe_radio").click(function() {
                                if (jQuery(this).val() == "wlm_stripe_new_card") {
                                    jQuery(".<?php echo esc_js($sku); ?>-stripe-new").show();
                                    stripe_card_type = "new";
                                } else if (jQuery(this).val() == "wlm_stripe_existing_card") {
                                    stripe_card_type = "existing";
                                    jQuery(".<?php echo esc_js($sku); ?>-stripe-new").hide();
                                }
                            });
                        });
                    </script>
                    <div class="txt-fld col-12">
                        <label style="display: inline !important;">
                            <input id="" type="radio" class="<?php echo esc_attr($sku); ?>-stripe_radio wlm_stripe_radio" checked="checked" value="wlm_stripe_existing_card" name="wlm_stripe_radio"> <?php echo esc_html($existing_card_text); ?>
                            <?php
                            // Translators: %1$s is the card brand, %2$s is the last 4 digits of the card number.
                            printf(esc_html__('%1$s ending in %2$s', 'wishlist-member'), esc_html(strtoupper($data['sc_details']['stripe_card_brand'])), esc_html($data['sc_details']['stripe_card_last4']));
                            ?>
                        </label>
                        <label style="display: inline !important;">
                            <input id="" type="radio" class="<?php echo esc_attr($sku); ?>-stripe_radio wlm_stripe_radio" value="wlm_stripe_new_card" name="wlm_stripe_radio"> <?php echo esc_html($diff_card_text); ?>
                        </label>
                        <div class="<?php echo esc_attr($sku); ?>-stripe-new" id="<?php echo esc_attr($sku); ?>-stripe-new" style="display:none;">
                            <div id="card-element-<?php echo esc_attr($sku); ?>" class="card-element" style="height: 40px;
                                padding: 10px 12px; border: 1px solid transparent;border-radius: 4px; background-color: white;
                                box-shadow: 0 1px 3px 0 #e6ebf1;    -webkit-transition: box-shadow 150ms ease;transition:
                                box-shadow 150ms ease;">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>
                            <!-- Used to display form errors. -->
                            <div id="card-errors-<?php echo esc_attr($sku); ?>" role="alert" class="regform-error" style="display:none;"></div>
                        </div>
                    <?php
                    echo '</div><br>';
                }
                return false;
                /**
 * STRIPE INTEGRATION END
*/
            } else {
                return $show_fields;
            }
        }

        /**
         * Cancel the user's Stripe Subscription when their membership level gets cancelled in WLM
         *
         * @param array   $level_ids -  SKUs of the membership level
         * @param integer $user_id   - User ID of the member that was cancelled.
         */
        public function cancel_stripe_subs_via_hook($user_id, $level_ids)
        {

            $stripeapikey   = Gateway_Utils::get_stripeapikey();
            $stripesettings = wishlistmember_instance()->get_option('stripesettings');
            $connections    = wishlistmember_instance()->get_option('stripeconnections');
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            foreach ($level_ids as $level_id) {
                $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($user_id, 'stripe_cust_id');

                /*
                 * Use Customer ID from transaction ID if it's different from stripe_cust_id
                 * or if stripe_cust_id is empty but the txn is still connected to a plan.
                 */
                $txn_id = wishlistmember_instance()->get_membership_levels_txn_id($user_id, $level_id);
                // Get c_id from transaction ID and compare.
                list($c_id, $plan_id) = explode('-', $txn_id);
                if ($stripe_cust_id != $c_id || empty($stripe_cust_id)) {
                    $stripe_cust_id = $c_id;
                }

                if (empty($stripe_cust_id)) {
                    continue;
                }

                try {
                    $stripe_level_settings = isset($connections[ $level_id ]) ? $connections[ $level_id ] : [];

                    // If Level is not a Subscription skip it.
                    if (empty($stripe_level_settings['subscription'])) {
                        continue;
                    }

                    if (empty($stripe_level_settings['cancel_subs_if_cancelled_in_wlm'])) {
                        continue;
                    }

                    $cust = PHPLib\Customer::retrieve($stripe_cust_id);
                    if (! $cust->subscriptions) {
                        $cust = PHPLib\Customer::retrieve(
                            [
                                'id'     => $stripe_cust_id,
                                'expand' => ['subscriptions'],
                            ]
                        );
                    }

                    $at_period_end = false;
                    if (! empty($stripesettings['endsubscriptiontiming']) && 'periodend' === $stripesettings['endsubscriptiontiming']) {
                        $at_period_end = true;
                    }

                    $txn_id = wishlistmember_instance()->get_membership_levels_txn_id($user_id, $level_id);

                    // Check if customer has more than 1 subscription, if so then get the.
                    // Subscription ID and only cancel the subscription that matches the STRIPE PLAN.
                    // Passed in the $_POST data.
                    if (count($cust->subscriptions->data) > 1) {
                        list($c_id, $plan_id) = explode('-', $txn_id);
                        foreach ($cust->subscriptions->data as $d) {
                            if ($d->plan->id == $plan_id) {
                                $sub_id = $d->id;

                                if ($at_period_end) {
                                    $update = PHPLib\Subscription::update(
                                        $sub_id,
                                        [
                                            'cancel_at_period_end' => $at_period_end,
                                        ]
                                    );
                                } else {
                                    $subscription = PHPLib\Subscription::retrieve($sub_id);
                                    $subscription->cancel();
                                }
                            }
                        }
                    } else {
                        if ($at_period_end) {
                            $sub_id = $cust->subscriptions->data[0]->id;
                            $update = PHPLib\Subscription::update(
                                $sub_id,
                                [
                                    'cancel_at_period_end' => $at_period_end,
                                ]
                            );
                        } else {
                            $sub_id       = $cust->subscriptions->data[0]->id;
                            $subscription = PHPLib\Subscription::retrieve($sub_id);
                            $subscription->cancel();
                        }
                    }
                } catch (\Exception $e) {
                    null;
                }
            }
        }

        /**
         * Get view path.
         */
        public function get_view_path($handle)
        {
            return sprintf(wishlistmember_instance()->plugin_dir . '/extlib/wlm_stripe/%s.php', $handle);
        }
        /**
         * Add Stripe Customer ID to the User's Profile
         *
         * @param  array   $custom_fields Custom Fields.
         * @param  integer $userid        User ID.
         * @return array               Custom Fields.
         */
        public function add_stripe_field($custom_fields, $userid)
        {
            if (! wishlistmember_instance()) {
                return $custom_fields;
            }
            if (! current_user_can('manage_options')) {
                return $custom_fields;
            }

            $stripeapikey         = wlm_trim(Gateway_Utils::get_stripeapikey());
            $stripepublishablekey = wlm_trim(Gateway_Utils::get_publishablekey());
            if (empty($stripeapikey) && empty($stripeapikey)) {
                return $custom_fields;
            }

            $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($userid, 'stripe_cust_id');

            $custom_fields['stripe_cust_id'] = [
                'type'       => 'text', // hidden, select, textarea, checkbox, etc.
                'label'      => 'Stripe Customer ID',
                'attributes' => [
                    'type'  => 'text', // hidden, select, textarea, checkbox, etc.
                    'name'  => 'stripe_cust_id', // same as index above.
                    // 'other attributes' => 'value',.
                    'value' => $stripe_cust_id,
                    // More attributes if needed.
                ],
            ];
            return $custom_fields;
        }

        /**
         * Save Stripe Customer ID to the User's Profile
         *
         * @param array $data Form Data.
         */
        public function save_stripe_field($data)
        {
            if (! wishlistmember_instance()) {
                return;
            }
            if (! current_user_can('manage_options')) {
                return;
            }
            if (! isset($data['userid'])) {
                return;
            }

            $user_custom_fields = isset($data['customfields']) ? $data['customfields'] : [];
            if (! isset($user_custom_fields['stripe_cust_id'])) {
                return;
            }
            $stripe_cust_id = $user_custom_fields['stripe_cust_id'] ? wlm_trim($user_custom_fields['stripe_cust_id']) : '';

            wishlistmember_instance()->Update_UserMeta($data['userid'], 'stripe_cust_id', $stripe_cust_id);
        }
        /**
         * Add Stripe Customer ID to the User's Profile
         *
         * @param integer $user User object or ID.
         */
        public function profile_form($user)
        {
            if (! current_user_can('manage_options')) {
                return;
            }

            $user_id = $user;
            if (is_object($user)) {
                $user_id = $user->ID;
            }

            global $pagenow;

            $stripeapikey         = wlm_trim(Gateway_Utils::get_stripeapikey());
            $stripepublishablekey = wlm_trim(Gateway_Utils::get_publishablekey());

            if (empty($stripeapikey) && empty($stripeapikey)) {
                return;
            }

            if ('profile.php' === $pagenow || 'user-edit.php' === $pagenow) {
                $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($user_id, 'stripe_cust_id');
                include $this->get_view_path('stripe_user_profile');
            }
        }
        /**
         * Update the Stripe Customer ID in the User's Profile
         *
         * @param integer $user User object or ID.
         */
        public function update_profile($user)
        {
            if (! current_user_can('manage_options')) {
                return;
            }

            $user_id = $user;
            if (is_object($user)) {
                $user_id = $user->ID;
            }

            if (isset(wlm_post_data()['stripe_cust_id'])) {
                wishlistmember_instance()->Update_UserMeta($user_id, 'stripe_cust_id', trim(wlm_post_data()['stripe_cust_id']));
            }
        }
        /**
         * Show error message if curl is not enabled.
         */
        public function notices()
        {
            if (extension_loaded('curl')) {
                return;
            }
            if ('WishListMember' === wlm_get_data()['page'] && 'integration' === wlm_get_data()['wl']) {
                ?>
                    <div class="error fade">
                        <p>
                        <?php echo wp_kses_data(__('<strong>WishList Member Notice:</strong> The <strong>Stripe</strong> integration will not work properly. Please enable <strong>Curl</strong>.', 'wishlist-member')); ?>
                        </p>
                    </div>
                <?php
            }
        }

        /**
         * Stripe Button Shortcode
         *
         * @param  array  $atts    Shortcode Attributes.
         * @param  string $content Shortcode Content.
         * @return string Shortcode Output.
         */
        public function wlm_stripe_btn($atts, $content)
        {
            $form = new Stripe_Forms();
            return $form->generate_stripe_form($atts, $content);
        }

        /**
         * Stripe Profile Shortcode footer javascript
         */
        public function footer()
        {
            if (wishlistmember_instance()) {
                $stripethankyou = wishlistmember_instance()->get_option('stripethankyou');
            }

            $stripethankyou_url = wishlistmember_instance()->make_thankyou_url($stripethankyou);

            $wlmstripevars['cancelmessage']      = __('Are you sure you want to cancel your subscription?', 'wishlist-member');
            $wlmstripevars['nonceinvoices']      = wp_create_nonce('stripe-do-invoices');
            $wlmstripevars['nonceinvoicedetail'] = wp_create_nonce('stripe-do-invoice');
            $wlmstripevars['noncecoupon']        = wp_create_nonce('stripe-do-check_coupon');
            $wlmstripevars['noncecoupondetail']  = wp_create_nonce('stripe-do-get_coupon');
            $wlmstripevars['nonce_prorate']      = wp_create_nonce('stripe-do-get_prorated_amount');
            $wlmstripevars['stripethankyouurl']  = $stripethankyou_url;
            ?>
                <script type="text/javascript">
                    function get_stripe_vars() {
                        return eval('(' + '<?php echo json_encode($wlmstripevars); ?>' + ')');
                    }
                    jQuery(function($) {
                        // Coupon code toggle.
                        $('.stripe-coupon').hide();
                        $('<a href="#" style="display:block;padding: 0.5em 0" onclick="jQuery(this).hide();jQuery(this.dataset.target).show().focus();return false;" data-target=".stripe-coupon">'+wp.i18n.__('Have a coupon code?','wishlist-member')+'</a>').insertAfter('.stripe-coupon');
                    });
                </script>
                <?php
        }

        /**
         * Stripe Profile Shortcode
         *
         * @param  array $atts Shortcode Attributes.
         * @return string Shortcode Output.
         */
        public function wlm_stripe_profile($atts)
        {
            ob_start();
            global $current_user;

            $stripepublishablekey = wlm_trim(Gateway_Utils::get_publishablekey());
            $stripethankyou       = wishlistmember_instance()->get_option('stripethankyou');
            $stripethankyou_url   = wishlistmember_instance()->make_thankyou_url($stripethankyou);

            if (empty($current_user->ID)) {
                return null;
            }

            $default_atts = [
                'levels'             => '',
                'include_posts'      => 'yes',
                'hide_cancel_button' => 'no',
                'level-choices' => '',
            ];
            $atts         = shortcode_atts($default_atts, $atts);
            $mlevels      = '' ? 'all' === $atts['levels'] : $atts['levels'];
            $mlevels      = 'no' !== $mlevels ? ( 'all' !== $mlevels ? explode(',', $mlevels) : $mlevels ) : 'no';
            $mlevels      = !empty($atts['level-choices']) ? explode(',', $atts['level-choices']) : $mlevels;
            $ppost        = 'no' !== $atts['include_posts'] ? 'yes' : 'no';

            wp_enqueue_style('wlm-stripe-profile-style', wishlistmember_instance()->plugin_url . '/extlib/wlm_stripe/css/stripe-profile.css', '', wishlistmember_instance()->version);
            wp_enqueue_style('stripe-paymenttag-style', wishlistmember_instance()->plugin_url . '/extlib/wlm_stripe/css/stripe-paymenttag.css', '', wishlistmember_instance()->version);
            wp_enqueue_script('stripe-paymenttag', wishlistmember_instance()->plugin_url . '/extlib/wlm_stripe/js/stripe-paymenttag.js', ['jquery'], wishlistmember_instance()->version, true);
            wp_enqueue_script('leanModal', wishlistmember_instance()->plugin_url . '/extlib/wlm_stripe/js/jquery.leanModal.min.js', ['jquery'], wishlistmember_instance()->version, true);
            wp_enqueue_script('wlm-stripe-profile', wishlistmember_instance()->plugin_url . '/extlib/wlm_stripe/js/stripe.wlmprofile.js', ['stripe-paymenttag', 'leanModal'], wishlistmember_instance()->version, true);

            $levels     = wishlistmember_instance()->get_membership_levels($current_user->ID, null, null, null, true);
            $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
            $user_posts = wishlistmember_instance()->get_user_pay_per_post('U-' . $current_user->ID);

            $stripeapikey = Gateway_Utils::get_stripeapikey();
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');
            if (! empty($stripe_cust_id)) {
                try {
                    $cust = PHPLib\Customer::retrieve($stripe_cust_id);
                    if (! $cust->subscriptions) {
                        $cust = PHPLib\Customer::retrieve(
                            [
                                'id'     => $stripe_cust_id,
                                'expand' => ['subscriptions'],
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    echo '<span class="stripe-error">' . wp_kses_post($e->getMessage()) . '</span>';
                }
            }
            $txnids = [];

            if ('no' !== $mlevels) {
                foreach ($wpm_levels as $id => $level) {
                    if ('all' !== $mlevels && ! in_array($id, (array) $mlevels)) {
                        continue;
                    }
                    $txn = wishlistmember_instance()->get_membership_levels_txn_id($current_user->ID, $id);
                    if (empty($txn)) {
                        continue;
                    }

                    $subs_end_msg         = __('Access to Level Ends: ', 'wishlist-member');
                    $payment_canceled_msg = __('Payment Subscription Cancelled:', 'wishlist-member');

                    if (false === strpos($txn, 'cus_')) {
                        $txnids[ $id ]['stripe_connected'] = false;
                    }

                    if (is_object($cust) && ! empty($cust->subscriptions)) {
                        if (count($cust->subscriptions->data) > 1) {
                            list($c_id, $plan_id) = explode('-', $txn, 2);
                            foreach ($cust->subscriptions->data as $d) {
                                if ($d->plan->id == $plan_id) {
                                    $txnids[ $id ]['stripe_connected'] = true;
                                    if ($d->cancel_at) {
                                        $txnids[ $id ]['subs_cancelled'] = true;

                                        $subs_end_date         = date_i18n(get_option('date_format'), $d->cancel_at + wishlistmember_instance()->gmt);
                                        $payment_canceled_date = date_i18n(get_option('date_format'), $d->canceled_at + wishlistmember_instance()->gmt);

                                        $txnids[ $id ]['subs_cancelled_msg']  = $payment_canceled_msg . $payment_canceled_date . '<br>';
                                        $txnids[ $id ]['subs_cancelled_msg'] .= $subs_end_msg . $subs_end_date;
                                    }
                                }
                            }
                        } else {
                            // If subscriptions is empty then this might be a one time purchase.
                            list($c_id, $plan_id) = explode('-', $txn, 2);
                            if (count($cust->subscriptions->data)) {
                                if ($cust->subscriptions->data[0]->plan->id == $plan_id) {
                                    $txnids[ $id ]['stripe_connected'] = true;
                                } else {
                                    // Subscription appears to be empty due to different stripe_cust_id so we'll check using txn cust id instead.
                                    $check_txn_cust_id_connected = '1';
                                }
                            } else {
                                // Empty stripe_cust_id but we'll still check if any txn are connected.
                                $check_txn_cust_id_connected = '1';
                            }

                            try {
                                $charge         = PHPLib\Charge::retrieve($c_id);
                                $stripe_cust_id = $charge->customer;
                            } catch (\Exception $e) {
                                $stripe_cust_id = $c_id;
                            }

                            // Check if the customer ID is different in the txn or stripe_cust_id is empty but still connected to a plan.
                            if ($check_txn_cust_id_connected) {
                                try {
                                    $cust = PHPLib\Customer::retrieve(
                                        [
                                            'id'     => $stripe_cust_id,
                                            'expand' => ['subscriptions'],
                                        ]
                                    );
                                    if (! $plan_id) {
                                        $txnids[ $id ]['stripe_connected'] = false;
                                    } else {
                                        if ($cust->subscriptions->data[0]->plan->id == $plan_id) {
                                            $txnids[ $id ]['stripe_connected'] = true;
                                        } else {
                                            $txnids[ $id ]['stripe_connected'] = false;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    if (preg_match('/^cus_\d+$/', $stripe_cust_id)) {
                                        echo '<span class="stripe-error">' . wp_kses_post($e->getMessage()) . '</span>';
                                    }
                                }
                            }

                            $sub_id = $cust->subscriptions->data[0]->cancel_at;
                            if ($cust->subscriptions->data[0]->cancel_at && $cust->subscriptions->data[0]->plan->id == $plan_id) {
                                $txnids[ $id ]['subs_cancelled'] = true;

                                $subs_end_date         = date_i18n(get_option('date_format'), $cust->subscriptions->data[0]->cancel_at + wishlistmember_instance()->gmt);
                                $payment_canceled_date = date_i18n(get_option('date_format'), $cust->subscriptions->data[0]->canceled_at + wishlistmember_instance()->gmt);

                                $txnids[ $id ]['subs_cancelled_msg']  = $payment_canceled_msg . $payment_canceled_date . '<br>';
                                $txnids[ $id ]['subs_cancelled_msg'] .= $subs_end_msg . $subs_end_date;
                            }
                        }
                    }

                    $txnids[ $id ]['hide_cancel_button'] = $atts['hide_cancel_button'];
                    $txnids[ $id ]['txn']                = $txn;
                    $txnids[ $id ]['level']              = $level;
                    $txnids[ $id ]['level_id']           = $id;
                    $txnids[ $id ]['type']               = 'membership';
                    wlm_print_script('https://js.stripe.com/v3/');
                    ?>
                        <script type="text/javascript">
                            var stripe = Stripe('<?php echo esc_js($stripepublishablekey); ?>');
                            var stripe_profile_button_status = true;
                            jQuery(function($) {
                            <?php
                            foreach ($txnids as $txnid) {
                                $txn = str_replace('-', '', $txnids[ $id ]['level_id']);
                                ?>

                                    var profile_elements = stripe.elements();
                                    var style = {
                                        base: {
                                            color: '#32325d',
                                            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                                            fontSmoothing: 'antialiased',
                                            fontSize: '16px',
                                            '::placeholder': {
                                                color: '#aab7c4'
                                            }
                                        },
                                        invalid: {
                                            color: '#fa755a',
                                            iconColor: '#fa755a'
                                        }
                                    };

                                    var card<?php echo esc_js($txn); ?> = profile_elements.create('card', {
                                        style: style
                                    });

                                    card<?php echo esc_js($txn); ?>.mount('#profile-card-element-<?php echo esc_js($txn); ?>');

                                    card<?php echo esc_js($txn); ?>.addEventListener('change', function(event) {
                                        var displayError = document.getElementById('profile-card-errors-<?php echo esc_js($txn); ?>');
                                        if (event.error) {
                                            displayError.textContent = event.error.message;
                                            displayError.style.display = "block";
                                        } else {
                                            displayError.textContent = '';
                                            displayError.style.display = "none";
                                        }
                                    });

                                    $("#profile-form-credit-<?php echo esc_js($txn); ?>").click(function(event) {

                                        var cardData = {
                                            name: "<?php echo esc_js($current_user->display_name); ?>",
                                            email: "<?php echo esc_js($current_user->user_email); ?>"
                                        };

                                        stripe.createToken(card<?php echo esc_js($txn); ?>, cardData).then(function(result) {
                                            if (result.error) {
                                                var errorElement = document.getElementById("profile-card-errors-<?php echo esc_js($txn); ?>");
                                                errorElement.textContent = result.error.message;
                                                ui.find(".profile-card-errors-<?php echo esc_js($txn); ?>").html("<span>" + result.error.message + "</span>");
                                                event.preventDefault();
                                            } else {
                                                var token = result.token.id;
                                                $("#profile-form-credit-<?php echo esc_js($txn); ?>").append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
                                                if (stripe_profile_button_status == true) {
                                                    stripe_profile_button_status = false;
                                                    $("#profile-form-credit-<?php echo esc_js($txn); ?>").submit();
                                                }
                                            }
                                        });
                                        return false;
                                    });
                                <?php
                            }
                            ?>
                            });
                        </script>
                    <?php
                }
            }

            if ('yes' === $ppost) {
                foreach ($user_posts as $u) {
                    $p                         = get_post($u->content_id);
                    $id                        = 'payperpost-' . $u->content_id;
                    $txn                       = wishlistmember_instance()->Get_ContentLevelMeta('U-' . $current_user->ID, $u->content_id, 'transaction_id');
                    $txnids[ $id ]['txn']      = $txn;
                    $txnids[ $id ]['level_id'] = $id;
                    $txnids[ $id ]['type']     = 'post';
                    $txnids[ $id ]['level']    = [
                        'name' => $p->post_title,
                    ];
                }
            }

            $wlm_user = new \WishListMember\User($current_user->ID);
            ?>
            <?php if (isset(wlm_get_data()['status'])) : ?>
                    <?php if ('ok' === wlm_get_data()['status']) : ?>
                        <p><span class="stripe-success"><?php esc_html_e('Profile Updated', 'wishlist-member'); ?></span></p>
                    <?php else : ?>
                        <span class="stripe-error"><?php esc_html_e('Unable to update your profile, please try again', 'wishlist-member'); ?></span>
                    <?php endif; ?>
            <?php endif; ?>
            <?php
            include $this->get_view_path('profile');
            $str = ob_get_clean();
            $str = preg_replace('/\s+/', ' ', $str);
            return $str;
        }
    }
}


?>
