<?php

if (extension_loaded('curl')) {
    global $WishListMemberInstance;
    include_once $WishListMemberInstance->plugin_dir . '/extlib/paypal/PPAutoloader.php';
    PPAutoloader::register();
}

if (! class_exists('WLM_INTEGRATION_PAYPALPRO')) {
    class WLM_INTEGRATION_PAYPALPRO
    {
        private $settings;
        private $wlm;

        private $thankyou_url;
        private $pp_settings;
        public function __construct()
        {
            global $WishListMemberInstance;
            $this->wlm      = $WishListMemberInstance;
            $this->products = $this->wlm->get_option('paypalproproducts');

            $settings           = $this->wlm->get_option('paypalprothankyou_url');
            $paypalprothankyou  = $this->wlm->get_option('paypalprothankyou');
            $this->thankyou_url = $this->wlm->make_thankyou_url($paypalprothankyou);

            $pp_settings = $this->wlm->get_option('paypalprosettings');

            $index = 'live';
            if ($pp_settings['sandbox_mode']) {
                $index = 'sandbox';
            }

            $this->pp_settings = [
                'acct1.UserName'  => $pp_settings[ $index ]['api_username'],
                'acct1.Password'  => $pp_settings[ $index ]['api_password'],
                'acct1.Signature' => $pp_settings[ $index ]['api_signature'],
                'mode'            => $pp_settings['sandbox_mode'] ? 'sandbox' : 'live',
                'gateway'         => $pp_settings['sandbox_mode'] ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com',
            ];
        }
        public function paypalpro($that)
        {
            $action = strtolower(wlm_trim(wlm_get_data()['action']));

            switch ($action) {
                case 'purchase-direct':
                    $this->purchase_direct(wlm_get_data()['id']);
                    break;
                case 'ipn':
                    $this->ipn(wlm_get_data()['id']);
                    break;
            }
        }
        public function ipn($id = null)
        {
            $ipn_message = new PPIPNMessage(null, $this->pp_settings);
            $raw_data    = $ipn_message->getRawData();

            if (! $ipn_message->validate()) {
                return false;
            }

            $txn_id                    = isset($raw_data['parent_txn_id']) ? $raw_data['parent_txn_id'] : $raw_data['txn_id'];
            $txn_id                    = isset($raw_data['recurring_payment_id']) ? $raw_data['recurring_payment_id'] : $txn_id;
            wlm_post_data()['sctxnid'] = $txn_id;

            switch ($raw_data['txn_type']) {
                // Anything related to recurring, we follow.
                // The profiles status.
                case 'recurring_payment_profile_created':
                case 'subscr_signup':
                case 'recurring_payment':
                case 'recurring_payment_skipped':
                case 'subscr_modify':
                case 'subscr_payment':
                case 'recurring_payment_profile_cancel':
                case 'recurring_payment_expired':
                case 'recurring_payment_failed':
                case 'recurring_payment_suspended_due_to_max_failed_payment':
                case 'recurring_payment_suspended':
                case 'subscr_cancel':
                case 'subscr_eot':
                case 'subscr_failed':
                    switch ($raw_data['profile_status']) {
                        case 'Active':
                            $this->wlm->shopping_cart_reactivate();
                            break;
                        case 'Suspended':
                        case 'Cancelled':
                            $this->wlm->shopping_cart_deactivate();
                            break;
                        default:
                            // Ignore.
                            break;
                    }
                    // Were done.
                    return;
                break;
            }

            // This is a one time payment.
            switch ($raw_data['payment_status']) {
                case 'Completed':
                    if (isset($raw_data['echeck_time_processed'])) {
                        $this->wlm->shopping_cart_reactivate(1);
                    } else {
                        $this->wlm->shopping_cart_registration(null, false);
                        $this->wlm->cart_integration_terminate();
                    }
                    break;
                case 'Canceled-Reversal':
                    $this->wlm->shopping_cart_reactivate();
                    break;
                case 'Processed':
                    $this->wlm->shopping_cart_reactivate('Confirm');
                    break;
                case 'Expired':
                case 'Failed':
                case 'Refunded':
                case 'Reversed':
                    $this->wlm->shopping_cart_deactivate();
                    break;
            }
        }

        public function purchase_direct_recurring($product)
        {
            // Create a recurring payment profile.
            $person_name            = new PersonNameType();
            $person_name->FirstName = wlm_post_data()['first_name'];
            $person_name->LastName  = wlm_post_data()['last_name'];

            $address                  = new AddressType();
            $address->Name            = wlm_post_data()['first_name'] . ' ' . wlm_post_data()['last_name'];
            $address->Street1         = wlm_post_data()['street'];
            $address->Street2         = '';
            $address->CityName        = wlm_post_data()['city_name'];
            $address->StateOrProvince = wlm_post_data()['state'];
            $address->PostalCode      = wlm_post_data()['zip_code'];
            $address->Country         = 'US'; // Making this Static (US) for now while thinking of a way to add a dropdown to the form fields.
            $address->Phone           = '';

            $payer            = new PayerInfoType();
            $payer->Payer     = wlm_post_data()['email'];
            $payer->PayerName = $person_name;

            $payer->Address = $address;

            $card_details                   = new CreditCardDetailsType();
            $card_details->CreditCardNumber = wlm_post_data()['cc_number'];
            $card_details->CreditCardType   = wlm_post_data()['cc_type'];
            $card_details->ExpMonth         = wlm_post_data()['cc_expmonth'];
            $card_details->ExpYear          = wlm_post_data()['cc_expyear'] + 2000;
            $card_details->CVV2             = wlm_post_data()['cc_cvc'];
            $card_details->CardOwner        = $payer;

            try {
                $schedule_details = new ScheduleDetailsType();

                $payment_billing_period                   = new BillingPeriodDetailsType();
                $payment_billing_period->BillingFrequency = $product['recur_billing_frequency'];
                $payment_billing_period->BillingPeriod    = $product['recur_billing_period'];
                $payment_billing_period->Amount           = new BasicAmountType($product['currency'], $product['recur_amount']);
                if ($product['recur_billing_cycles'] > 1) {
                    $payment_billing_period->TotalBillingCycles = $product['recur_billing_cycles'];
                }
                $schedule_details->PaymentPeriod = $payment_billing_period;

                if ($product['trial'] && $product['trial_amount']) {
                    $trial_payment_billing_period                     = new BillingPeriodDetailsType();
                    $trial_payment_billing_period->BillingFrequency   = $product['trial_recur_billing_frequency'];
                    $trial_payment_billing_period->BillingPeriod      = $product['trial_recur_billing_period'];
                    $trial_payment_billing_period->Amount             = new BasicAmountType($product['currency'], $product['trial_amount']);
                    $trial_payment_billing_period->TotalBillingCycles = 1;
                    $schedule_details->TrialPeriod                    = $trial_payment_billing_period;
                }

                $schedule_details->Description = wlm_paypal_create_description($product);

                $recur_profile_details = new RecurringPaymentsProfileDetailsType();
                // $recur_profile_details->BillingStartDate = wlm_date(DATE_ATOM, strtotime(sprintf("+%s %s", $product['recur_billing_frequency'], $product['recur_billing_period'])));
                $recur_profile_details->BillingStartDate = wlm_date(DATE_ATOM);

                $create_recur_paypay_profile_details                                  = new CreateRecurringPaymentsProfileRequestDetailsType();
                $create_recur_paypay_profile_details->Token                           = $token;
                $create_recur_paypay_profile_details->ScheduleDetails                 = $schedule_details;
                $create_recur_paypay_profile_details->RecurringPaymentsProfileDetails = $recur_profile_details;
                $create_recur_paypay_profile_details->CreditCard                      = $card_details;

                $create_recur_profile = new CreateRecurringPaymentsProfileRequestType();
                $create_recur_profile->CreateRecurringPaymentsProfileRequestDetails = $create_recur_paypay_profile_details;

                $create_recur_profile_req                                        = new CreateRecurringPaymentsProfileReq();
                $create_recur_profile_req->CreateRecurringPaymentsProfileRequest = $create_recur_profile;

                $paypal_service      = new PayPalAPIInterfaceServiceService($this->pp_settings);
                $create_profile_resp = $paypal_service->CreateRecurringPaymentsProfile($create_recur_profile_req);
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => wlm_post_data()['sku'],
                    ]
                );
            }

            if ('Success' !== $create_profile_resp->Ack && 'SuccessWithWarning' !== $create_profile_resp->Ack) {
                return [
                    'status' => 'failed',
                    'errmsg' => $create_profile_resp->Errors[0]->LongMessage,
                ];
            }

            if ('ActiveProfile' === $create_profile_resp->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus) {
                return [
                    'status' => 'active',
                    'id'     => $create_profile_resp->CreateRecurringPaymentsProfileResponseDetails->ProfileID,
                ];
            }

            return [
                'status' => 'pending',
                'id'     => $create_profile_resp->CreateRecurringPaymentsProfileResponseDetails->ProfileID,
            ];
        }
        public function purchase_direct_once($product)
        {

            $item_details           = new PaymentDetailsItemType();
            $item_details->Name     = $product['name'];
            $item_details->Amount   = $product['amount'];
            $item_details->Quantity = 1;

            $payment_details                           = new PaymentDetailsType();
            $payment_details->OrderTotal               = new BasicAmountType($product['currency'], $product['amount']);
            $payment_details->NotifyURL                = $this->thankyou_url . '?action=ipn&id=' . $id;
            $payment_details->PaymentDetailsItem[ $i ] = $item_details;

            $person_name            = new PersonNameType();
            $person_name->FirstName = wlm_post_data()['first_name'];
            $person_name->LastName  = wlm_post_data()['last_name'];

            $address                  = new AddressType();
            $address->Name            = wlm_post_data()['first_name'] . ' ' . wlm_post_data()['last_name'];
            $address->Street1         = wlm_post_data()['street'];
            $address->Street2         = '';
            $address->CityName        = wlm_post_data()['city_name'];
            $address->StateOrProvince = wlm_post_data()['state'];
            $address->PostalCode      = wlm_post_data()['zip_code'];
            $address->Country         = 'US'; // Making this Static (US) for now while thinking of a way to add a dropdown to the form fields.
            $address->Phone           = '';

            $payer            = new PayerInfoType();
            $payer->Payer     = wlm_post_data()['email'];
            $payer->PayerName = $person_name;

            $payer->Address = $address;

            $card_details                   = new CreditCardDetailsType();
            $card_details->CreditCardNumber = wlm_post_data()['cc_number'];
            $card_details->CreditCardType   = wlm_post_data()['cc_type'];
            $card_details->ExpMonth         = wlm_post_data()['cc_expmonth'];
            $card_details->ExpYear          = wlm_post_data()['cc_expyear'] + 2000;
            $card_details->CVV2             = wlm_post_data()['cc_cvc'];
            $card_details->CardOwner        = $payer;

            try {
                $dd_req_details                 = new DoDirectPaymentRequestDetailsType();
                $dd_req_details->CreditCard     = $card_details;
                $dd_req_details->PaymentDetails = $payment_details;

                $do_direct_req                         = new DoDirectPaymentReq();
                $do_direct_req->DoDirectPaymentRequest = new DoDirectPaymentRequestType($dd_req_details);

                $paypal_service = new PayPalAPIInterfaceServiceService($this->pp_settings);

                $resp = $paypal_service->DoDirectPayment($do_direct_req);
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => wlm_post_data()['sku'],
                    ]
                );
            }

            if ('Success' === $resp->Ack || 'SuccessWithWarning' === $resp->Ack) {
                return [
                    'status' => 'active',
                    'id'     => $resp->TransactionID,
                ];
            } else {
                return [
                    'status' => 'failed',
                    'errmsg' => $resp->Errors[0]->LongMessage,
                ];
            }
        }
        public function purchase_direct($id)
        {

            $products = $this->products;
            $product  = $products[ $id ];

            if (empty($product)) {
                return;
            }

            if ($product['recurring']) {
                $result = $this->purchase_direct_recurring($product);
            } else {
                $result = $this->purchase_direct_once($product);
            }

            try {
                if ('failed' === $result['status']) {
                    throw new Exception($result['errmsg']);
                }
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => wlm_post_data()['sku'],
                    ]
                );
            }

            wlm_post_data()['lastname']  = wlm_post_data()['last_name'];
            wlm_post_data()['firstname'] = wlm_post_data()['first_name'];
            wlm_post_data()['action']    = 'wpm_register';
            wlm_post_data()['wpm_id']    = $product['sku'];
            wlm_post_data()['username']  = wlm_post_data()['email'];
            wlm_post_data()['email']     = wlm_post_data()['email'];
            wlm_post_data()['sctxnid']   = $result['id'];
            wlm_post_data()['password1'] = $this->wlm->pass_gen();
            wlm_post_data()['password2'] = wlm_post_data()['password1'];

            // Paypal will mark the profile as pending.
            // When there is an initial amount because the charge event is delayed.
            // We will ignore the pending status because this will cause.
            // Users to see the 'pending/forapproval' error when the ipn.
            // Get's delayed. Which is usually the case because of the delay.
            // When charging.
            $this->wlm->shopping_cart_registration();
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
            wp_redirect($uri);
            die();
        }
        public function create_description($product)
        {
            $description = $product['name'] . ' (';
            if ($product['trial'] && $product['trial_amount']) {
                // Translators: 1: trial amount, 2: currency, 3: frequency, 4: period, 5: "s" appended to period if frequency > 1.
                $description .= sprintf(__('%1$0.2f %2$s for the first %3$d %4$s%5$s then ', 'wishlist-member'), $product['trial_amount'], $product['currency'], $product['trial_recur_billing_frequency'], strtolower($product['trial_recur_billing_period']), $product['trial_recur_billing_frequency'] > 1 ? 's' : '');
            }
            // Translators: 1: recurring amount, 2: currency, 3: frequency, 4: period, 5: "s" appended to period if frequency > 1.
            $description .= sprintf(__('%1$0.2f %2$s every %3$d %4$s%5$s', 'wishlist-member'), $product['recur_amount'], $product['currency'], $product['recur_billing_frequency'], strtolower($product['recur_billing_period']), $product['recur_billing_frequency'] > 1 ? 's' : '');
            if ($product['recur_billing_cycles'] > 1) {
                // Translators: number of installments.
                $description .= sprintf(__(' for %d installments', 'wishlist-member'), $product['recur_billing_cycles']);
            }
            $description .= ')';
            return str_replace(' 1 ', ' ', $description);
        }
    }
}
