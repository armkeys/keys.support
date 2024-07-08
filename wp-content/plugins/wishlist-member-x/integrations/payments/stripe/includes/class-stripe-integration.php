<?php

/*
 * Stripe Payment Provider Integration
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

if (! class_exists('Stripe_Integration')) {

    class Stripe_Integration
    {
        /**
         * Singleton instance
         *
         * @var object
         */
        private static $instance;

        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wishlistmember_paymentprovider_handler', [$this, 'stripe']);
            add_action('stripe_add_customer_id', [$this, 'stripe_add_customer_id']);
        }

        /**
         * Public function to generate a single instance
         *
         * @return object Stripe_Integration object instance
         */
        public static function instance()
        {
            if (empty(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function stripe($scuri)
        {
            if (wishlistmember_instance()->get_option('stripethankyou') != $scuri) {
                return; // Not ours, pass control back to WishList Member.
            }

            $stripeapikey = Gateway_Utils::get_stripeapikey();
            if (false !== strpos($stripeapikey, '_test_')) {
                wlm_post_data()['wishlistmember_test_mode'] = 1;
            }

            $action        = trim(strtolower(wlm_request_data()['stripe_action']));
            $valid_actions = ['charge', 'sync', 'update_payment', 'cancel', 'invoices', 'invoice', 'migrate', 'check_coupon', 'get_coupon', 'sca_redirect', 'get_prorated_amount'];
            if (! in_array($action, $valid_actions, true)) {
                esc_html_e('Permission Denied', 'wishlist-member');
                die();
            }
            if (( 'sync' !== $action && 'migrate' !== $action ) && ! wp_verify_nonce(wlm_request_data()['nonce'], "stripe-do-$action")) {
                esc_html_e('Permission Denied', 'wishlist-member');
                die();
            }
            switch ($action) {
                case 'migrate':
                    $this->migrate();
                    break;
                case 'charge':
                    // Code...
                    $this->charge(wlm_post_data(true));
                    break;
                case 'sync':
                    $this->sync(wlm_post_data(true));
                    break;
                case 'update_payment':
                    $this->update_payment(wlm_post_data(true));
                    break;
                case 'cancel':
                    $this->cancel(wlm_post_data(true));
                    break;
                case 'invoices':
                    $this->invoices(wlm_post_data(true));
                    break;
                case 'invoice':
                    $this->invoice(wlm_post_data(true));
                    break;
                case 'check_coupon':
                    $this->check_coupon(wlm_post_data(true));
                    break;
                case 'get_coupon':
                    $this->get_coupon(wlm_post_data(true));
                    break;
                case 'sca_redirect':
                    $this->handle_sca_redirect($_REQUEST);
                    break;
                case 'get_prorated_amount':
                    $this->get_prorated_amount(wlm_post_data(true));
                    break;
                default:
                    // Code...
                    break;
            }
        }
        public function promocode_to_pid($promocode_name)
        {
            $list_promotion_codes = PHPLib\PromotionCode::all(['code' => $promocode_name]);
            if (count($list_promotion_codes->data)) {
                return $list_promotion_codes->data[0]->id;
            }
        }
        public function coupon_to_cid($coupon_name)
        {
            $list_coupons = PHPLib\Coupon::all();
            foreach ($list_coupons as $coupon_code) {
                if ($coupon_name == $coupon_code->name) {
                        return $coupon_code->id;
                }
            }
        }
        public function check_coupon($data = [])
        {
            $stripeapikey = Gateway_Utils::get_stripeapikey();
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            try {
                $promo_code_id = $this->promocode_to_pid($data['coupon']);
                if (! $promo_code_id) {
                    $coupon_id = $this->coupon_to_cid($data['coupon']);
                }
                if ($coupon_id) {
                    $coupon = PHPLib\Coupon::retrieve(
                        [
                            'id'     => $coupon_id,
                            'expand' => ['applies_to'],
                        ]
                    );
                } elseif ($promo_code_id) {
                    $coupon = PHPLib\PromotionCode::retrieve(
                        [
                            'id' => $promo_code_id,
                            'expand' => ['coupon.applies_to'],
                        ]
                    );
                    $coupon = $coupon->coupon;
                } else {
                    $coupon = PHPLib\Coupon::retrieve(
                        [
                            'id'     => $data['coupon'],
                            'expand' => ['applies_to'],
                        ]
                    );
                }
            } catch (\Exception $e) {
                wp_send_json(
                    [
                        'success' => false,
                        'coupon'  => [],
                        'error'   => $e->getMessage(),
                    ]
                );
            }
            wp_send_json(
                [
                    'success' => 1 === (int) $coupon->valid && ( empty($coupon->applies_to) ? true : in_array(wlm_arrval($data, 'product'), $coupon->applies_to->products, true) ),
                    'coupon'  => $coupon,
                ]
            );
            die();
        }
        public function get_coupon($data = [])
        {
            $stripeapikey = Gateway_Utils::get_stripeapikey();
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            try {
                $promo_code_id = $this->promocode_to_pid($data['coupon']);
                if (! $promo_code_id) {
                    $coupon_id = $this->coupon_to_cid($data['coupon']);
                }
                if ($coupon_id) {
                    $coupon  = PHPLib\Coupon::retrieve($coupon_id);
                    $coupons = [
                        'c_type'   => $coupon->amount_off ? 'amount_off' : 'percent_off',
                        'c_amount' => $coupon->amount_off ? $coupon->amount_off : $coupon->percent_off,
                    ];
                    echo json_encode($coupons);
                } elseif ($promo_code_id) {
                    $coupon  = PHPLib\PromotionCode::retrieve($promo_code_id);
                    $coupons = [
                        'c_type'   => $coupon['coupon']->amount_off ? 'amount_off' : 'percent_off',
                        'c_amount' => $coupon['coupon']->amount_off ? $coupon['coupon']->amount_off : $coupon['coupon']->percent_off,
                    ];
                    echo json_encode($coupons);
                } else {
                    $coupon  = PHPLib\Coupon::retrieve($data['coupon']);
                    $coupons = [
                        'c_type'   => $coupon->amount_off ? 'amount_off' : 'percent_off',
                        'c_amount' => $coupon->amount_off ? $coupon->amount_off : $coupon->percent_off,
                    ];
                    echo json_encode($coupons);
                }
            } catch (\Exception $e) {
                echo json_encode('');
            }

            die();
        }
        public function migrate()
        {
            $users = get_users();
            echo sprintf("migrating %s stripe users<br/>\n", count($users));

            $live = wlm_get_data()['live'];
            foreach ($users as $u) {
                $cust_id = wishlistmember_instance()->Get_UserMeta($u->ID, 'custom_stripe_cust_id');

                printf('migrating user %s with stripe_cust_id: <br/>', esc_html($u->ID), esc_html($cust_id));
                if ($live || ! empty($cust_id)) {
                    wishlistmember_instance()->Update_UserMeta($u->ID, 'stripe_cust_id', $cust_id);
                }
            }
        }
        public function cancel($data = [])
        {
            global $current_user;
            if (empty($current_user->ID)) {
                return;
            }
            $stripeapikey   = Gateway_Utils::get_stripeapikey();
            $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');
            $stripesettings = wishlistmember_instance()->get_option('stripesettings');
            $connections    = wishlistmember_instance()->get_option('stripeconnections');
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            /*
             * Use Customer ID from transaction ID if it's different from stripe_cust_id
             * or if stripe_cust_id is empty but the txn is still connected to a plan.
             */
            if (! empty($data['txn_id'])) {
                list( $c_id, $plan_id ) = explode('-', $data['txn_id'], 2);
                if ($stripe_cust_id != $c_id || empty($stripe_cust_id)) {
                    $stripe_cust_id = $c_id;
                }
            }

            try {
                // Also handle onetime payments.
                // Wishlistmember_instance()->shopping_cart_deactivate();
                $stripe_level_settings = $connections[ wlm_post_data()['wlm_level'] ];
                if (! empty($stripe_level_settings['subscription'])) {
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
                    // Check if customer has more than 1 subscription, if so then get the.
                    // Subscription ID and only cancel the subscription that matches the STRIPE PLAN.
                    // Passed in the $_POST data.
                    if (count($cust->subscriptions->data) > 1) {
                        list($c_id, $plan_id) = explode('-', $data['txn_id']);
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
                            $sub_id = $cust->subscriptions->data[0]->id;

                            $subscription = PHPLib\Subscription::retrieve($sub_id);
                            $subscription->cancel();
                        }
                    }
                } else {
                    wlm_post_data()['sctxnid'] = wlm_request_data()['txn_id'];
                    wishlistmember_instance()->shopping_cart_deactivate();
                }
                $status = 'ok';
            } catch (\Exception $e) {
                $status = 'fail&err=' . $e->getMessage();
            }
            $uri = $data['redirect_to'];
            if (! empty($stripesettings['cancelredirect'])) {
                $uri = get_permalink($stripesettings['cancelredirect']);
            }
            if (false !== stripos($uri, '?')) {
                $uri .= "&status=$status";
            } else {
                $uri .= "?&status=$status";
            }
            wp_redirect($uri);
            die();
        }

        public function update_payment($data = [])
        {
            $stripeapikey = Gateway_Utils::get_stripeapikey();
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            try {
                global $current_user;
                if (empty($current_user->ID)) {
                    throw new \Exception(__('An error occured while processing the request, Please try again', 'wishlist-member'));
                }
                $cust_id = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');
                if (empty($cust_id)) {
                    // User is a member but not linked.
                    // Try to create this user in stripe.
                    $cust_details = [
                        'name'        => sprintf('%s %s', $current_user->first_name, $current_user->last_name),
                        'description' => sprintf('%s %s', $current_user->first_name, $current_user->last_name),
                        'email'       => $current_user->user_email,
                    ];
                    $cust         = PHPLib\Customer::create($cust_details);

                    $payment_method = PHPLib\PaymentMethod::create(
                        [
                            'type' => 'card',
                            'card' => [
                                'token' => $data['stripeToken'],
                            ],
                        ]
                    );

                    $payment_method = PHPLib\PaymentMethod::retrieve($payment_method->id);
                    $payment_method->attach(['customer' => $cust->id]);

                    $cust->invoice_settings->default_payment_method = $payment_method->id;
                    $cust->save();

                    wishlistmember_instance()->Update_UserMeta($current_user->ID, 'stripe_cust_id', $cust->id);
                } else {
                    $cust = PHPLib\Customer::retrieve($cust_id);

                    $payment_method = PHPLib\PaymentMethod::create(
                        [
                            'type' => 'card',
                            'card' => [
                                'token' => $data['stripeToken'],
                            ],
                        ]
                    );

                    $payment_method = PHPLib\PaymentMethod::retrieve($payment_method->id);
                    $payment_method->attach(['customer' => $cust->id]);

                    $cust->invoice_settings->default_payment_method = $payment_method->id;
                    $cust->save();
                }
                $status = 'ok';
            } catch (\Exception $e) {
                $err    = preg_replace('/\s+/', '+', $e->getMessage());
                $status = 'fail&err=' . $err;
            }

            $uri = $data['redirect_to'];
            if (false !== stripos($uri, '?')) {
                $uri .= "&status=$status";
            } else {
                $uri .= "?&status=$status";
            }
            wp_redirect($uri);
            die();
        }

        public function sync($data = [])
        {

            $stripesettings = wishlistmember_instance()->get_option('stripesettings');

            wishlistmember_instance()->schedule_sync_membership();
            $obj    = json_decode(file_get_contents('php://input'));
            $id     = null;
            $action = null;
            PHPLib\WLM_Stripe::setApiKey(Gateway_Utils::get_stripeapikey());
            wlm_post_data()['sc_type'] = 'Stripe';

            // If $obj is empty then just return, otherwise it will show errors when viewed in browser.
            if (empty($obj)) {
                die("\n");
            }

            // Means this came from a test web hook URL.
            // Skip sync process to avoid 500 internal server error as.
            // The Sync process will throw errors.
            if ('evt_00000000000000' === $obj->id) {
                die("\n");
            }

            // Request for the stripe event object to.
            // Make sure this is a legit stripe notification.
            $obj = PHPLib\Event::retrieve($obj->id);

            switch ($obj->type) {
                // Do not handler creates anymore.
                // Case 'customer.subscription.created':
                // $cust_id = $obj->data->object->customer;
                // $plan_id = $obj->data->object->plan->id;
                // $id = $cust_id . "-" . $plan_id;
                // $action = 'move';
                // Break;
                case 'customer.subscription.deleted':
                    $cust_id = $obj->data->object->customer;
                    $plan_id = $obj->data->object->plan->id;
                    $id      = $cust_id . '-' . $plan_id;
                    $action  = 'deactivate';
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $cust_id = $obj->data->object->customer;
                    $plan_id = $obj->data->object->plan->id;
                    $id      = $cust_id . '-' . $plan_id;

                    switch ($obj->data->object->status) {
                        case 'trialing':
                        case 'past_due':
                            $action = 'reactivate';
                            break;
                        case 'active':
                            $action = 'reactivate';
                            if (! empty($obj->data->previous_attributes->plan->id)) {
                                // We are changing subscriptions.
                                $prev_id = sprintf('%s-%s', $cust_id, $obj->data->previous_attributes->plan->id);
                                $action  = 'move';
                            }
                            break;
                        case 'unpaid':
                            if ('yes' !== $stripesettings['keep_unpaid_subs_active']) {
                                $action = 'deactivate';
                            }
                            break;
                        case 'cancelled':
                        default:
                            $action = 'deactivate';
                            break;
                    }

                    // This is an active subscription.
                    // Reactivate? No need.
                    break;
                case 'invoice.payment_failed':
                    // No need, we'll also be able to catch this under charge_failed.
                    break;

                case 'invoice.payment_succeeded':
                    if ($obj->data->object->billing_reason == 'subscription_cycle') {
                        $invoice_id = $obj->data->object->invoice;
                        if (! $obj->data->object->invoice) {
                            $invoice_id = $obj->data->object->id;
                        }
                        // Customer ID + Price ID.
                        $inv = PHPLib\Invoice::retrieve($invoice_id);
                        $id  = sprintf('%s-%s', $inv->customer, $inv->lines->data[0]->price->id);

                        wlm_post_data()['sctxnid']          = $id;
                        wlm_post_data()['stripe_charge_id'] = $obj->data->object->charge;
                        wlm_post_data()['paid_amount']      = $obj->data->object->amount_paid;
                        wlm_post_data()['is_wlm_sc_rebill'] = true;
                        $action                             = 'reactivate';
                    }
                    break;

                case 'customer.deleted':
                    $cust_id = $obj->data->object->id;
                    $user_id = wishlistmember_instance()->Get_UserID_From_UserMeta('stripe_cust_id', $cust_id);
                    $levels  = wishlistmember_instance()->get_membership_levels($user_id, null, true, null, true);
                    if (empty($levels)) {
                        die("\n");
                    }
                    $id     = wishlistmember_instance()->get_membership_levels_txn_id($user_id, $levels[0]);
                    $action = 'deactivate';
                    break;
                case 'charge.refunded':
                    if ($obj->data->object->invoice) {
                        // Customer ID + Price ID.
                        $inv = PHPLib\Invoice::retrieve($obj->data->object->invoice);
                        $id  = sprintf('%s-%s', $inv->customer, $inv->lines->data[0]->price->id);
                    } else {
                        // Charge ID.
                        $id = $obj->data->object->id;
                    }
                    wlm_post_data()['sctxnid']          = $id;
                    wlm_post_data()['paid_amount']      = $obj->data->object->amount;
                    wlm_post_data()['is_wlm_sc_refund'] = true;
                    do_action('wishlistmember_shoppingcart_refund', wlm_post_data(true));
                    $action = 'deactivate';
                    break;
                case 'charge.failed':
                    // No need to handle as failed charges are handled.
                    // In the merchant site.
                    // $cust_id = $obj->data->object->customer;
                    // $customer = PHPLib\Customer::retrieve($cust_id);
                    // If (empty($customer->plan)) {
                    // Return;
                    // }
                    // $id = sprintf("%s-%s", $cust_id, $customer->plan->id);
                    // $action = 'deactivate';
                    //
                    break;
            }

            wlm_post_data()['sctxnid'] = $id;
            switch ($action) {
                case 'deactivate':
                    printf('info(deact): deactivating subscription: %s', esc_html($id));
                    wlm_post_data()['sctxnid'] = $id;
                    wishlistmember_instance()->shopping_cart_deactivate();
                    break;
                case 'reactivate':
                    printf('info(react): reactivating subscription: %s', esc_html($id));
                    wlm_post_data()['sctxnid'] = $id;
                    do_action_deprecated('wlm_shoppingcart_rebill', [wlm_post_data(true)], '3.10', 'wishlistmember_shoppingcart_rebill');
                    do_action('wishlistmember_shoppingcart_rebill', wlm_post_data(true), $obj);

                    wishlistmember_instance()->shopping_cart_reactivate();

                    break;
                case 'move':
                    // Activate the new one.
                    $connections = wishlistmember_instance()->get_option('stripeconnections');

                    // Get the correct level.
                    $wpm_level      = $this->stripe_plan_id_to_sku($connections, $obj->data->object->plan->id);
                    $prev_wpm_level = $this->stripe_plan_id_to_sku($connections, $obj->data->previous_attributes->plan->id);

                    // Get the correct user.
                    $user_id = wishlistmember_instance()->Get_UserID_From_UserMeta('stripe_cust_id', $cust_id);

                    if (! empty($wpm_level) && ! empty($user_id)) {
                        // Remove from previous level.
                        $current_levels = wishlistmember_instance()->get_membership_levels($user_id, null, null, true);
                        $levels         = array_diff($current_levels, [$prev_wpm_level]);
                        printf('removing from %s', esc_html($prev_wpm_level));
                        wishlistmember_instance()->set_membership_levels($user_id, $levels);

                        printf('info(move): moving user:%s to new subscription:%s with tid:%s', esc_html($user_id), esc_html($wpm_level), esc_html($id));
                        $this->add_to_level($user_id, $wpm_level, $id);
                    }
                    break;
            }
            die("\n");
        }
        public function stripe_plan_id_to_sku($connections, $id)
        {
            foreach ($connections as $c) {
                if ($c['plan'] == $id) {
                    return $c['sku'];
                }
            }
        }
        public function add_to_level($user_id, $level_id, $txn_id)
        {
            $user = new \WishListMember\User($user_id);

             $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');

             wishlistmember_instance()->set_membership_levels($user_id, [$level_id], ['keep_existing_levels' => true]);

            // Send email notifications.
            if (1 == $wpm_levels[ $level_id ]['newuser_notification_user']) {
                $email_macros                                   = [
                    '[password]'    => '********',
                    '[memberlevel]' => $wpm_levels[ $level_id ]['name'],
                ];
                wishlistmember_instance()->email_template_level = $level_id;
                wishlistmember_instance()->send_email_template('registration', $user_id, $email_macros);
            }

            if (1 == $wpm_levels[ $level_id ]['newuser_notification_admin']) {
                wishlistmember_instance()->email_template_level = $level_id;
                wishlistmember_instance()->send_email_template('admin_new_member_notice', $user_id, $email_macros, wishlistmember_instance()->get_option('email_sender_address'));
            }

            if (isset($wpm_levels[ $level_id ]['registrationdatereset'])) {
                $timestamp = time();
                wishlistmember_instance()->user_level_timestamp($user_id, $level_id, $timestamp);
            }

            if (wishlistmember_instance()->is_ppp_level($level_id)) {
                list($tmp, $content_id) = explode('-', $level_id);
                wishlistmember_instance()->add_user_post_transaction_id($user_id, $content_id, $txn_id);

                if (empty($timestamp)) {
                    $timestamp = time();
                }

                wishlistmember_instance()->add_user_post_timestamp($user_id, $content_id, $timestamp);
            } else {
                wishlistmember_instance()->set_membership_level_txn_id($user_id, $level_id, $txn_id);
            }
        }
        public function charge_existing($data)
        {

            $connections               = wishlistmember_instance()->get_option('stripeconnections');
            $stripesettings            = wishlistmember_instance()->get_option('stripesettings');
            $stripe_plan               = $connections[ $data['wpm_id'] ]['plan'];
            $settings                  = $connections[ $data['wpm_id'] ];
            wlm_post_data()['sc_type'] = 'Stripe';

            PHPLib\WLM_Stripe::setApiVersion('2019-08-14');

            try {
                global $current_user;
                $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');

                if ($data['subscription']) {
                    // Since 3.6 change the plan to customer-selected plan if there is one.
                    $stripe_plan = $this->choose_plan($stripe_plan, $connections, $data);

                    if (! empty($stripe_cust_id) && 'wlm_stripe_existing_card' === wlm_post_data()['wlm_stripe_radio']) {
                        $cust = PHPLib\Customer::retrieve($stripe_cust_id);

                        $stripe_cust_payment_method_id = $cust->invoice_settings->default_payment_method;

                        // If customer has Stripe Customer ID but doesn't have a payment Method ID (they might have bought using // token before) then create a payment method ID using the new card they used on purchase and attach it.
                        if (empty($stripe_cust_payment_method_id)) {
                            $payment_method = PHPLib\PaymentMethod::create(
                                [
                                    'type' => 'card',
                                    'card' => [
                                        'token' => $data['stripeToken'],
                                    ],
                                ]
                            );
                        } else {
                            $payment_method = PHPLib\PaymentMethod::retrieve($stripe_cust_payment_method_id);
                        }

                        $payment_method = PHPLib\PaymentMethod::retrieve($payment_method->id);
                        $payment_method->attach(['customer' => $cust->id]);

                        $cust->invoice_settings->default_payment_method = $payment_method->id;
                        $cust->save();

                        if (empty($payment_method->id)) {
                            throw new \Exception('Could not verify credit card information');
                        }
                    } else {
                        if (empty($data['stripeToken'])) {
                            throw new \Exception('Could not verify credit card information');
                        }
                        $cust_details = [
                            'name'        => sprintf('%s %s', $data['firstname'], $data['lastname']),
                            'description' => sprintf('%s %s', $data['firstname'], $data['lastname']),
                            'email'       => $data['email'],
                        ];
                        $cust         = PHPLib\Customer::create($cust_details);

                        $payment_method = PHPLib\PaymentMethod::create(
                            [
                                'type' => 'card',
                                'card' => [
                                    'token' => $data['stripeToken'],
                                ],
                            ]
                        );

                        $payment_method = PHPLib\PaymentMethod::retrieve($payment_method->id);
                        $payment_method->attach(['customer' => $cust->id]);

                        $cust->invoice_settings->default_payment_method = $payment_method->id;
                        $cust->save();

                        wishlistmember_instance()->Update_UserMeta($current_user->ID, 'stripe_cust_id', $cust->id);
                    }

                    $automatic_tax = false;
                    if (! empty($stripesettings['automatictax']) && 'yes' === $stripesettings['automatictax']) {
                        $automatic_tax = true;
                    }

                    if (! empty($stripe_plan)) {
                        foreach ($cust->subscriptions->data as $sub) {
                            if ($sub->plan->id == $stripe_plan) {
                                throw new \Exception(__('Cannot purchase an active plan', 'wishlist-member'));
                            }
                        }
                    }

                    if (empty($data['coupon'])) {
                        unset($params['coupon']);
                    }

                    $txn_id = $this->charge_plan($stripe_plan, $cust, $automatic_tax, $data, 'charge_existing', $current_user->ID);
                } else {
                    if (! empty($stripe_cust_id && 'wlm_stripe_existing_card' === wlm_post_data()['wlm_stripe_radio'])) {
                        $cust = PHPLib\Customer::retrieve($stripe_cust_id);

                        $stripe_cust_payment_method_id = $cust->invoice_settings->default_payment_method;

                        // If customer has Stripe Customer ID but doesn't have a payment Method ID (they might have bought using // token before) then create a payment method ID using the new card they used on purchase and attach it.
                        if (empty($stripe_cust_payment_method_id)) {
                            $payment_method = PHPLib\PaymentMethod::create(
                                [
                                    'type' => 'card',
                                    'card' => [
                                        'token' => $data['stripeToken'],
                                    ],
                                ]
                            );
                        } else {
                            $payment_method = PHPLib\PaymentMethod::retrieve($stripe_cust_payment_method_id);
                        }

                        $payment_method = PHPLib\PaymentMethod::retrieve($payment_method->id);
                        $payment_method->attach(['customer' => $cust->id]);

                        $cust->invoice_settings->default_payment_method = $payment_method->id;
                        $cust->save();

                        if (empty($payment_method->id)) {
                            throw new \Exception('Could not verify credit card information');
                        }
                    } else {
                        // Create USER.
                        $cust = PHPLib\Customer::create(
                            [
                                'name'        => sprintf('%s %s', $data['firstname'], $data['lastname']),
                                'description' => sprintf('%s %s', $data['firstname'], $data['lastname']),
                                'email'       => $data['email'],
                            ]
                        );

                        wishlistmember_instance()->Update_UserMeta($current_user->ID, 'stripe_cust_id', $cust->id);

                        // Instead of directly using tokens to charge we now create a payment method using Stripe Tokens and then.
                        // Attach it to customer for future charges. This is a bit different from Stripe tokens.
                        $payment_method = PHPLib\PaymentMethod::create(
                            [
                                'type' => 'card',
                                'card' => [
                                    'token' => $data['stripeToken'],
                                ],
                            ]
                        );
                    }

                    $currency = empty($stripesettings['currency']) ? 'USD' : $stripesettings['currency'];

                    // Override amount and currency if set in shortcode.
                    $currency = isset($data['stripe_currency']) ? strtoupper($data['stripe_currency']) : $currency;
                    $amt      = isset($data['stripe_amount']) ? (float) $data['stripe_amount'] : $settings['amount'];
                    $amt      = number_format($amt * 100, 0, '.', '');

                    $level      = wlmapi_get_level($data['wpm_id']);
                    $level_name = $level['level']['name'];

                    if (empty($level_name)) {
                        $ppp_level  = wishlistmember_instance()->is_ppp_level($data['sku']);
                        $level_name = $ppp_level->post_title;
                    }

                    // Create the PaymentIntent.
                    $intent = PHPLib\PaymentIntent::create(
                        [
                            'customer'            => $cust->id,
                            'payment_method'      => $payment_method->id,
                            'amount'              => $amt,
                            'currency'            => $currency,
                            'confirmation_method' => 'automatic',
                            'confirm'             => true,
                            'description'         => sprintf('%s - One Time Payment', $level_name),
                        ]
                    );

                    $txn_id = $intent->charges->data[0]->id;

                    // If payment requires AUTH then let process_sca_auth handle it.
                    if ('requires_action' === $intent->status && 'use_stripe_sdk' === $intent->next_action->type) {
                        $this->process_sca_auth($data, $intent->client_secret, $cust->id, '', $intent->id, 'charge_existing', $current_user->ID);
                    }
                }

                if (! wlm_post_data()['paid_amount']) {
                    wlm_post_data()['paid_amount'] = number_format($amt / 100, 0, '.', '');
                }

                if (! wlm_post_data()['stripe_charge_id']) {
                    wlm_post_data()['stripe_charge_id'] = $txn_id;
                }

                wlm_post_data()['sctxnid'] = $txn_id;

                do_action('wishlistmember_existing_member_purchase');

                // Add user to level and redirect to the after reg url.
                $this->add_to_level($current_user->ID, $data['sku'], $txn_id);
                $url = wishlistmember_instance()->get_after_reg_redirect($data['sku']);
                wp_redirect($url);
                die();
            } catch (\Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => $data['wpm_id'],
                    ]
                );
            }
        }
        public function charge_new($data)
        {

            $connections    = wishlistmember_instance()->get_option('stripeconnections');
            $stripesettings = wishlistmember_instance()->get_option('stripesettings');
            $stripe_plan    = $connections[ $data['wpm_id'] ]['plan'];
            $settings       = $connections[ $data['wpm_id'] ];
            PHPLib\WLM_Stripe::setApiVersion('2019-08-14');
            wlm_post_data()['sc_type'] = 'Stripe';

            // Attempt to get Stripe customer if email exists in WP.
            $cust = null;
            $stripe_cust_is_fresh = false;
            $wp_user_id = email_exists($data['email']);
            if ($wp_user_id) {
                $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($wp_user_id, 'stripe_cust_id');
                if ($stripe_cust_id) {
                    try {
                        $cust = PHPLib\Customer::retrieve($stripe_cust_id);
                        if (isset($cust->deleted) && $cust->deleted) {
                            $cust = null;
                        }
                    } catch (\Exception $e) {
                        $cust = null;
                    }
                }
            }

            if (!$cust) {
                try {
                    // Create USER.
                    $cust = PHPLib\Customer::create(
                        [
                            'name'        => sprintf('%s %s', $data['firstname'], $data['lastname']),
                            'description' => sprintf('%s %s', $data['firstname'], $data['lastname']),
                            'email'       => $data['email'],
                        ]
                    );
                    $stripe_cust_is_fresh = true;
                } catch (\Exception $e) {
                    $this->fail(
                        [
                            'msg' => $e->getMessage(),
                            'sku' => $data['wpm_id'],
                        ]
                    );
                }
            }

            if ($wp_user_id) {
                // Update stripe customer ID for WP User if a matching email is found.
                wishlistmember_instance()->Update_UserMeta($wp_user_id, 'stripe_cust_id', $cust->id);
            }

            if (!$cust) {
                // No customer found or created. Let's show an error.
                $this->fail(
                    [
                        'msg' => 'Cannot create customer in Stripe.',
                        'sku' => $data['wpm_id'],
                    ]
                );
            }

            try {
                if ($data['subscription']) {
                    // Since 3.6 change the plan to customer-selected plan if there is one.
                    $stripe_plan = $this->choose_plan($stripe_plan, $connections, $data);

                    $automatic_tax = false;
                    if (! empty($stripesettings['automatictax']) && 'yes' === $stripesettings['automatictax']) {
                        $automatic_tax = true;
                    }

                    if (empty($data['coupon'])) {
                        unset($params['coupon']);
                        unset($params['promotion_code']);
                    }

                    // Instead of directly using tokens to charge we now create a payment method using Stripe Tokens and then.
                    // Attach it to customer for future charges. This is a bit different from Stripe tokens.
                    $payment_method = PHPLib\PaymentMethod::create(
                        [
                            'type' => 'card',
                            'card' => [
                                'token' => $data['stripeToken'],
                            ],
                        ]
                    );

                    $payment_method = PHPLib\PaymentMethod::retrieve($payment_method->id);
                    $payment_method->attach(['customer' => $cust->id]);

                    $cust->invoice_settings->default_payment_method = $payment_method->id;
                    $cust->save();

                    $txn_id = $this->charge_plan($stripe_plan, $cust, $automatic_tax, $data, 'charge_new');
                } else {
                    $currency = empty($stripesettings['currency']) ? 'USD' : $stripesettings['currency'];

                    // Override amount and currency if set in shortcode.
                    $currency = isset($data['stripe_currency']) ? strtoupper($data['stripe_currency']) : $currency;
                    $amt      = isset($data['stripe_amount']) ? (float) $data['stripe_amount'] : $settings['amount'];
                    $amt      = number_format($amt * 100, 0, '.', '');

                    // Instead of directly using tokens to charge we now create a payment method using Stripe Tokens and then.
                    // Attach it to customer for future charges. This is a bit different from Stripe tokens.
                    $payment_method = PHPLib\PaymentMethod::create(
                        [
                            'type' => 'card',
                            'card' => [
                                'token' => $data['stripeToken'],
                            ],
                        ]
                    );

                    $payment_method = PHPLib\PaymentMethod::retrieve($payment_method->id);
                    $payment_method->attach(['customer' => $cust->id]);

                    $cust->invoice_settings->default_payment_method = $payment_method->id;
                    $cust->save();

                    // Get the level name as using $settings['membershiplevel'] may cause issues if the admin changes the name of // the membership level.
                    $level      = wlmapi_get_level($data['wpm_id']);
                    $level_name = $level['level']['name'];

                    if (empty($level_name)) {
                        $ppp_level  = wishlistmember_instance()->is_ppp_level($data['sku']);
                        $level_name = $ppp_level->post_title;
                    }

                    // Create the PaymentIntent.
                    $intent = PHPLib\PaymentIntent::create(
                        [
                            'customer'            => $cust->id,
                            'payment_method'      => $payment_method->id,
                            'amount'              => $amt,
                            'currency'            => $currency,
                            'confirmation_method' => 'automatic',
                            'confirm'             => true,
                            'description'         => sprintf('%s - One Time Payment', $level_name),
                        ]
                    );

                    $txn_id                             = $intent->charges->data[0]->id;
                    wlm_post_data()['paid_amount']      = number_format($amt / 100, 0, '.', '');
                    wlm_post_data()['stripe_charge_id'] = $txn_id;

                    // If payment requires AUTH then let process_sca_auth handle it.
                    if ('requires_action' === $intent->status && 'use_stripe_sdk' === $intent->next_action->type) {
                        $this->process_sca_auth($data, $intent->client_secret, $cust->id, '', $intent->id, 'charge_new');
                    }
                }

                wlm_post_data()['sctxnid'] = $txn_id;
                wlm_post_data()['stripe_cust_id'] = $cust->id;
                wlm_post_data()['stripe_payment_method_id'] = $payment_method->id;
                wishlistmember_instance()->shopping_cart_registration(true, false);

                $user = get_user_by('login', 'temp_' . md5($data['email']));
                wishlistmember_instance()->Update_UserMeta($user->ID, 'stripe_cust_id', $cust->id);
                wishlistmember_instance()->Update_UserMeta($user->ID, 'stripe_payment_method_id', $payment_method->id);
                $url = wishlistmember_instance()->get_continue_registration_url($data['email']);
                wp_redirect($url);
                die();
            } catch (\Exception $e) {
                if ('requires_action' === $subs->latest_invoice->payment_intent->status) {
                    $this->fail(
                        [
                            'msg'             => $e->getMessage(),
                            'sku'             => $data['wpm_id'],
                            'p_intent_secret' => $subs->latest_invoice->payment_intent->client_secret,
                            'cus_id'          => $subs->latest_invoice->customer,
                        ]
                    );
                } else {
                    $stripe_cust_is_fresh && $cust->delete(); // Delete Stripe customer if we just created it.
                    $this->fail(
                        [
                            'msg' => $e->getMessage(),
                            'sku' => $data['wpm_id'],
                        ]
                    );
                }
            }
        }

        public function charge_plan($stripe_plan, $cust, $automatic_tax, $data, $sca_charge_type, $cuid = '')
        {
            $plan = PHPLib\Price::retrieve($stripe_plan);
            if ($data['coupon']) {
                    $coupon_id     = $this->coupon_to_cid($data['coupon']);
                    $promo_code_id = $this->promocode_to_pid($data['coupon']);
            }
            if ($plan->recurring) {
                $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
                $level_name = $wpm_levels[ $data['wpm_id'] ]['name'];
                // Lets set a description for the subscription.
                // Translators: %s - Level name.
                $subs_description = sprintf(esc_html__('Subscription to %s', 'wishlist-member'), $level_name);

                // Recurring plan.
                if ($coupon_id) {
                    $subs = PHPLib\Subscription::create(
                        [
                            'customer'        => $cust->id,
                            'description'     => $subs_description,
                            'automatic_tax'   => [
                                'enabled'        => $automatic_tax,
                            ],
                            'coupon'          => $coupon_id,
                            'trial_from_plan' => true,
                            'items'           => [
                                [
                                    'plan'          => $stripe_plan,
                                ],
                            ],
                            'expand'          => ['latest_invoice.payment_intent'],
                        ]
                    );
                } elseif ($promo_code_id) {
                    $subs = PHPLib\Subscription::create(
                        [
                            'customer'        => $cust->id,
                            'automatic_tax'   => [
                                'enabled'        => $automatic_tax,
                            ],
                            'description'     => $subs_description,
                            'promotion_code'  => $promo_code_id,
                            'trial_from_plan' => true,
                            'items'           => [
                                [
                                    'plan'          => $stripe_plan,
                                ],
                            ],
                            'expand'          => ['latest_invoice.payment_intent'],
                        ]
                    );
                } else {
                    $subs = PHPLib\Subscription::create(
                        [
                            'customer'        => $cust->id,
                            'description'     => $subs_description,
                            'automatic_tax'   => [
                                'enabled'        => $automatic_tax,
                            ],
                            'coupon'          => $data['coupon'],
                            'trial_from_plan' => true,
                            'items'           => [
                                [
                                    'plan'       => $stripe_plan,
                                ],
                            ],
                            'expand'          => ['latest_invoice.payment_intent'],
                        ]
                    );
                }
                $latest_invoice = $subs->latest_invoice;
            } else {
                // One time payment plan.
                $discount = null;
                if ($coupon_id) {
                    $discount     = $cust->discount;
                    $cust->coupon = $coupon_id;
                    $cust->save();
                } elseif ($promo_code_id) {
                    $discount             = $cust->discount;
                    $cust->promotion_code = $promo_code_id;
                    $cust->save();
                } else {
                    $discount     = $cust->discount;
                    $cust->coupon = $coupon_id;
                    $cust->save();
                }
                $invitem          = PHPLib\InvoiceItem::create(
                    [
                        'customer' => $cust->id,
                        'price'    => $stripe_plan,
                    ]
                );
                $latest_invoice   = PHPLib\Invoice::create(
                    [
                        'customer' => $cust->id,
                    ]
                );
                $finalize_invoice = $latest_invoice->finalizeInvoice($latest_invoice->id);

                try {
                    $latest_invoice->pay($latest_invoice->id);
                } catch (\Exception $e) {
                    // If there's an error with function pay() then the invoice might need SCA.
                    // Let's grab the payment_intent from $finalize_invoice and try and process the SCA for this purchase.
                    $intent = PHPLib\PaymentIntent::retrieve($finalize_invoice->payment_intent);
                    $this->process_sca_auth($data, $intent->client_secret, $cust->id, $stripe_plan, $intent->id, $sca_charge_type, $cuid);
                }
            }
            $amount_paid                        = ( $latest_invoice->amount_paid ) ? $latest_invoice->amount_paid : $latest_invoice->lines->data[0]->price->unit_amount;
            wlm_post_data()['paid_amount']      = number_format($amount_paid / 100, 0, '.', '');
            wlm_post_data()['stripe_charge_id'] = $latest_invoice->charge;

            if ('failed' === $latest_invoice->payment_intent->charges->data[0]->status) {
                throw new \Exception($latest_invoice->payment_intent->charges->data[0]->failure_message);
            }

            if ('requires_action' === $latest_invoice->payment_intent->status) {
                // If card needs authentication then let's initiate SCA popup.
                $this->process_sca_auth($data, $latest_invoice->payment_intent->client_secret, $cust->id, $stripe_plan, '', $sca_charge_type, $cuid);
            }

            if ($data['coupon']) {
                $cust->coupon = $discount ? $discount->coupon->id : null;
                $cust->save();
            }

            $txn_id = sprintf('%s-%s', $cust->id, $stripe_plan);
            return $txn_id;
        }
        /**
         * Process payments that needs SCA authentication
         * in a form of a pop up modal which Stripe handles via Stripe JS.
         *
         * @param array   $data                  array of data needed to create temp accounts for users
         * @param string  $payment_intent_secret - Needed to trigger the SCA pop up modal
         * @param string  $cust_id               - Customer ID created in STripe
         * @param string  $stripe_plan           - ID of the Stripe Plan
         * @param string  $payment_intent        - Payment Intent ID needed to get the charges->id in function handle_sca_redirect()
         * @param string  $charge_type           - (charge_new, charge_existing)
         * @param integer $user_id               - User's WordPress USER ID
         */
        public function process_sca_auth($data, $payment_intent_secret, $cust_id, $stripe_plan = '', $payment_intent = '', $charge_type = '', $user_id = '')
        {

            $stripepublishablekey = wlm_trim(Gateway_Utils::get_publishablekey());

            // Build the Success Redirect.
            $sca_redirect_nonce = wp_create_nonce('stripe-do-sca_redirect');
            $stripethankyou     = wishlistmember_instance()->get_option('stripethankyou');
            $stripethankyou_url = wishlistmember_instance()->make_thankyou_url($stripethankyou);
            $sca_params         = sprintf(
                '?stripe_action=sca_redirect&cus_id=%s&sku=%s&plan_id=%s&fn=%s&ln=%s&p_intent=%s&charge_type=%s&u_id=%s&nonce=%s',
                rawurlencode($cust_id),
                rawurlencode(wlm_arrval($data, 'wpm_id')),
                rawurlencode($stripe_plan),
                rawurlencode(wlm_post_data()['firstname']),
                rawurlencode(wlm_post_data()['lastname']),
                rawurlencode($payment_intent),
                rawurlencode($charge_type),
                rawurlencode($user_id),
                rawurlencode($sca_redirect_nonce)
            );
            $success_redirect   = $stripethankyou_url . $sca_params;

            // Build the error redirect URL so that we can redirect and tell them in case SCA.
            // Authentication Failed.
            $error_redirect = wlm_request_data()['redirect_to'];

            if (false !== stripos($error_redirect, '?')) {
                $error_redirect .= '&status=fail&reason=' . preg_replace('/\s+/', '+', 'Failed to complete the Strong Customer Authentication. The payment was not processed.');
            } else {
                $error_redirect .= '?&status=fail&reason=' . preg_replace('/\s+/', '+', 'Failed to complete the Strong Customer Authentication. The payment was not be processed.');
            }
            $error_redirect .= '#regform-' . $data['sku'];

            wlm_print_script('https://js.stripe.com/v3/');
            ?>
            <script type="text/javascript">

                var stripe = Stripe('<?php echo esc_js($stripepublishablekey); ?>');

                var paymentIntentSecret = "<?php echo esc_js($payment_intent_secret); ?>";

                    stripe.handleCardPayment(paymentIntentSecret).then(function(result) {
                      if (result.error) {
                          window.location.replace('<?php echo esc_url_raw($error_redirect); ?>');
                      } else {
                        window.location.replace('<?php echo esc_url_raw($success_redirect); ?>');
                      }
                    });
            </script>
            <?php
            $animation_image = wishlistmember_instance()->plugin_url . '/images/loadingAnimation.gif';
            ?>
            <br><br><br>
            <center>
                <?php esc_html_e('The payment will not be processed and services will not be provisioned until authentication is completed. Please complete the authentication.', 'wishlist-member'); ?>
                <br><br>
                <img src="<?php echo esc_url($animation_image); ?>">
            </center>
            <?php
            die();
        }

        /**
         * This handles creating temp account, redirecting users to reg page and adding them to levels
         *
         * @param array $data array of data from $_GET
         */
        public function handle_sca_redirect($data)
        {

            $stripeapikey = Gateway_Utils::get_stripeapikey();
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            $cust = PHPLib\Customer::retrieve($data['cus_id']);

            if (! empty($data['plan_id'])) {
                $txn_id = sprintf('%s-%s', $cust->id, $data['plan_id']);
            } else {
                // If it's one time then we get the charges ID from payment intent created for the customer.
                $payment_intent_id = $data['p_intent'];
                $intent            = PHPLib\PaymentIntent::retrieve($payment_intent_id);
                $txn_id            = $intent->charges->data[0]->id;
            }

            if ('charge_new' === $data['charge_type']) {
                wlm_post_data()['sctxnid']          = $txn_id;
                wlm_post_data()['stripe_wlm_level'] = $data['sku'];
                wlm_post_data()['lastname']         = $data['ln'];
                wlm_post_data()['firstname']        = $data['fn'];
                wlm_post_data()['wpm_id']           = $data['sku'];
                wlm_post_data()['username']         = $cust->email;
                wlm_post_data()['email']            = $cust->email;
                wlm_post_data()['password1']        = wishlistmember_instance()->pass_gen();
                wlm_post_data()['password2']        = wlm_post_data()['password1'];

                wishlistmember_instance()->shopping_cart_registration(true, false);
                $user = get_user_by('login', 'temp_' . md5($cust->email));
                wishlistmember_instance()->Update_UserMeta($user->ID, 'stripe_cust_id', $cust->id);

                // If p_intent is present then this is one time which uses PaymentMethod to make charges.
                // Let's save the payment method ID to the user ID.
                if (! empty($data['p_intent'])) {
                    wishlistmember_instance()->Update_UserMeta($user->ID, 'stripe_payment_method_id', $intent->payment_method);
                }

                $url = wishlistmember_instance()->get_continue_registration_url($cust->email);
            } elseif ('charge_existing' === $data['charge_type']) {
                // Add user to level and redirect to the after reg url.
                $this->add_to_level($data['u_id'], $data['sku'], $txn_id);
                $url = wishlistmember_instance()->get_after_reg_redirect($data['sku']);
            }
            wp_redirect($url);
            die();
        }

        public function fail($data)
        {
            $uri = wlm_request_data()['redirect_to'];
            if (false !== stripos($uri, '?')) {
                $uri .= '&status=fail&reason=' . preg_replace('/\s+/', '+', $data['msg']);
            } else {
                $uri .= '?&status=fail&reason=' . preg_replace('/\s+/', '+', $data['msg']);
            }
            $uri .= '#regform-' . $data['sku'];
            // Error_log($uri);
            wp_redirect($uri, 307);

            die();
        }

        public function charge($data = [])
        {
            $stripeconnections = wishlistmember_instance()->get_option('stripeconnections');
            $stripeapikey      = Gateway_Utils::get_stripeapikey();
            $settings          = $stripeconnections[ $data['sku'] ];
            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            try {
                $btn_hash        = isset($data['btn_hash']) ? $data['btn_hash'] : false;
                $custom_amount   = isset($data['custom_amount']) ? $data['custom_amount'] : false;
                $custom_currency = isset($data['custom_currency']) ? $data['custom_currency'] : false;
                if (false !== $custom_amount || false !== $custom_currency) {
                    if (! wp_verify_nonce($btn_hash, "{$stripeapikey}-{$custom_amount}-{$custom_currency}")) {
                        throw new \Exception('Your request is invalid or expired. Please try again.');
                    }
                }

                $last_name  = $data['last_name'];
                $first_name = $data['first_name'];
                if ('new' === $data['charge_type']) {
                    if (empty($last_name) || empty($first_name) || empty($data['email'])) {
                        throw new \Exception('All fields are required');
                    }

                    if (empty($data['stripeToken'])) {
                        throw new \Exception('Payment Processing Failed');
                    }
                }

                wlm_post_data()['stripe_wlm_level'] = $data['sku'];
                wlm_post_data()['lastname']         = $last_name;
                wlm_post_data()['firstname']        = $first_name;
                wlm_post_data()['wpm_id']           = $data['sku'];
                wlm_post_data()['username']         = $data['email'];
                wlm_post_data()['email']            = $data['email'];
                wlm_post_data()['password1']        = wishlistmember_instance()->pass_gen();
                wlm_post_data()['password2']        = wlm_post_data()['password1'];

                // Lets add custom currency and amount.
                if ($custom_amount) {
                    wlm_post_data()['stripe_amount'] = $custom_amount;
                }
                if ($custom_currency) {
                    wlm_post_data()['stripe_currency'] = wlm_trim($custom_currency);
                }

                if (! empty($data['wlm_stripe_radio_prorate']) && 'prorate_level' === $data['wlm_stripe_radio_prorate']) {
                    if (! empty($data['txn_id'])) {
                        $this->prorate_plan($data);
                    } else {
                        throw new \Exception('Please Select a Level to Upgrade');
                    }
                }
                // Throw an error if there's no valid wpm_id in the received $POST data.
                if (empty(wlm_post_data()['wpm_id'])) {
                    throw new Exception('Invalid Level SKU');
                }

                if ('new' === $data['charge_type']) {
                    $this->charge_new(wlm_post_data(true));
                } else {
                    $this->charge_existing(wlm_post_data(true));
                }
            } catch (\Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => $data['sku'],
                    ]
                );
            }
        }

        // Following functions are used to query invoices.
        // And returns content ready for display for member profile.
        public function invoice($data)
        {
            global $current_user;
            if (empty($current_user->ID)) {
                return;
            }

            try {
                $stripeapikey = Gateway_Utils::get_stripeapikey();
                PHPLib\WLM_Stripe::setApiKey($stripeapikey);

                $inv  = PHPLib\Invoice::retrieve($data['txn_id']);
                $cust = PHPLib\Customer::retrieve($inv['customer']);
                include $this->get_view_path('invoice_details');
                die();
            } catch (\Exception $e) {
                null;
            }
        }

        public function invoices($data)
        {
            global $current_user;
            if (empty($current_user->ID)) {
                return;
            }
            $cust_id = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');
            try {
                $stripeapikey = Gateway_Utils::get_stripeapikey();
                $txns         = wishlistmember_instance()->get_membership_levels_txn_ids($current_user->ID);
                PHPLib\WLM_Stripe::setApiKey($stripeapikey);

                $inv      = PHPLib\Invoice::all(
                    [
                        'count'    => 100,
                        'customer' => $cust_id,
                    ]
                );
                $invoices = [];
                if (! empty($inv['data'])) {
                    $invoices = array_merge($invoices, $inv['data']);
                }
                // Try to get manual charges.
                // $manual_charges = PHPLib\Charge::all(array("count" => 100, 'customer' => $cust_id));
                // $invoices = array_merge($invoices, $inv['data']);
                // Var_dump($manual_charges);
                include $this->get_view_path('invoice_list');
                die();
            } catch (\Exception $e) {
                printf('<p>%s</p>', esc_html__('No invoices found for this user', 'wishlist-member'));
                die();
            }
        }
        public function get_view_path($handle)
        {
            return sprintf(wishlistmember_instance()->plugin_dir . '/extlib/wlm_stripe/%s.php', $handle);
        }

        /**
         * Replace $stripe_plan with the customer-selected payment plan if the latter is valid
         *
         * @since 3.6
         *
         * @param  string $stripe_plan The original payment plan
         * @param  array  $connections Configured stripe connections
         * @param  array  $data        Post data
         * @return string                Customer-selected payment plan if valid, otherwise return $stripe_plan
         */
        private function choose_plan($stripe_plan, $connections, $data)
        {
            /**
             * Check if customer chose a plan from our payment form and if it is
             * not the same as $stripe_plan then check if it's any of the configured plans
             * and if so then change $stripe_plan to $selected_plan
             *
             * @since 3.6
             */
            $selected_plan = wlm_arrval($data, 'stripe_plan');
            if ($selected_plan && $selected_plan != $stripe_plan) {
                $plans = json_decode(stripslashes((string) wlm_arrval($connections[ $data['wpm_id'] ], 'plans')));
                if ($plans && is_array($plans) && in_array($selected_plan, $plans)) {
                    $stripe_plan = $selected_plan;
                }
            }
            return $stripe_plan;
        }

        public function prorate_plan($data)
        {
            global $current_user;

            // Prepare coupon.
            $coupon = [];
            if ($data['coupon']) {
                $coupon_id = $this->coupon_to_cid($data['coupon']);
                if ($coupon_id) {
                    $coupon['coupon'] = $coupon_id;
                } else {
                    $promo_code_id = $this->promocode_to_pid($data['coupon']);
                    if ($promo_code_id) {
                        $coupon['promotion_code'] = $promo_code_id;
                    }
                }
            }

            $stripeapikey   = Gateway_Utils::get_stripeapikey();
            $stripe_cust_id = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'stripe_cust_id');
            $connections    = wishlistmember_instance()->get_option('stripeconnections');
            $stripe_plan    = $connections[ $data['upgrade_to_level'] ]['plan'];

            PHPLib\WLM_Stripe::setApiKey($stripeapikey);

            try {
                $stripe_level_settings = $connections[ wlm_post_data()['stripe_wlm_level'] ];
                if (! empty($stripe_level_settings['subscription'])) {
                    $cust = PHPLib\Customer::retrieve($stripe_cust_id);

                    if (! $cust->subscriptions) {
                        $cust = PHPLib\Customer::retrieve(
                            [
                                'id'     => $stripe_cust_id,
                                'expand' => ['subscriptions'],
                            ]
                        );
                    }

                    if (! empty($stripe_plan)) {
                        foreach ($cust->subscriptions->data as $sub) {
                            if ($sub->plan->id == $stripe_plan) {
                                throw new \Exception(__('Cannot purchase an active plan', 'wishlist-member'));
                            }
                        }
                    }

                    // Get the subscription ID that matches the STRIPE PLAN.
                    // Passed in the $_POST data.
                    if (! empty($cust->subscriptions->data)) {
                        list($c_id, $plan_id) = explode('-', $data['txn_id']);
                        foreach ($cust->subscriptions->data as $d) {
                            if ($d->plan->id == $plan_id) {
                                $sub_id       = $d->id;
                                $subscription = PHPLib\Subscription::retrieve($sub_id);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $status = 'fail&err=' . $e->getMessage();
            }

            // Update subscription.
            $update = PHPLib\Subscription::update(
                $sub_id,
                [
                    'items'          => [
                        [
                            'id'    => $subscription->items->data[0]->id,
                            // New price.
                            'price' => $stripe_plan,
                        ],
                    ],
                    'proration_date'     => wlm_arrval($data, 'form_timestamp') ?: time(),
                    'proration_behavior' => 'always_invoice',
                    'payment_behavior'   => 'error_if_incomplete',
                ] + $coupon // add coupon code.
            );

            $settings = $connections[ $data['upgrade_to_level'] ];
            if ('yes' === $settings['remove_from_level']) {
                wlmapi_update_member($current_user->ID, ['RemoveLevels' => $data['level_id']]);
            }

            // Add to level.
            $txn_id = $stripe_cust_id . '-' . $stripe_plan;
            $this->add_to_level($current_user->ID, $data['sku'], $txn_id);
            $url = wishlistmember_instance()->get_after_reg_redirect($data['sku']);
            wp_redirect($url);
            die();
        }

        public function stripe_add_customer_id()
        {
            if (isset(wlm_post_data()['stripe_cust_id'])) {
                $user = get_user_by('login', wlm_post_data()['email']);
                wishlistmember_instance()->Update_UserMeta($user->ID, 'stripe_cust_id', wlm_post_data()['stripe_cust_id']);
                wishlistmember_instance()->Update_UserMeta($user->ID, 'stripe_payment_method_id', wlm_post_data()['stripe_payment_method_id']);
            }
        }

        public function get_prorated_amount($post)
        {
            $txn_id = wlm_trim(wlm_arrval($post, 'txn_id'));
            if (! $txn_id) {
                wp_send_json_error('Invalid transaction ID: ' . $txn_id); // Transaction id required.
            }

            list( $customer_id, $price_id ) = explode('-', $txn_id, 2);
            if (! preg_match('/^cus_.+$/', $customer_id)) {
                wp_send_json_error('Invalid Customer ID: ' . $customer_id); // Invalid customer id.
            }

            $upgrade_plan = wlm_arrval(
                wishlistmember_instance()->get_option('stripeconnections'),
                $post['upgrade_to'],
                'plan'
            );

            PHPLib\WLM_Stripe::setApiKey(Gateway_Utils::get_stripeapikey());

            $customer = PHPLib\Customer::retrieve(
                [
                    'id'     => $customer_id,
                    'expand' => ['subscriptions'],
                ]
            );

            $subscription = null;
            foreach ($customer->subscriptions->data as $sub) {
                if ($sub->plan->id === $upgrade_plan) {
                    wp_send_json_error('Already in plan'); // Can't upgrade to an existing plan.
                }
                if ($sub->plan->id === $price_id) {
                    $subscription = PHPLib\Subscription::retrieve($sub->id);
                    break;
                }
            }

            if (empty($subscription)) {
                wp_send_json_error('Nothing to prorate'); // Nothing to prorate from.
            }

            $items = [
                [
                    'id'    => $subscription->items->data[0]->id,
                    'price' => $upgrade_plan,
                ],
            ];

            try {
                $invoice = PHPLib\Invoice::upcoming(
                    [
                        'customer'                        => $customer_id,
                        'subscription'                    => $subscription->id,
                        'subscription_items'              => $items,
                        'subscription_proration_date'     => wlm_arrval($post, 'form_timestamp') ?: time(),
                        'subscription_proration_behavior' => 'always_invoice',
                    ] + ['coupon' => ''] // Pass this so it doesn't use previous plan's coupon if any.
                );
            } catch (\Exception $e) {
                wp_send_json_error($e->getMessage());
            }
            wp_send_json_success($invoice);
        }
    }
}
