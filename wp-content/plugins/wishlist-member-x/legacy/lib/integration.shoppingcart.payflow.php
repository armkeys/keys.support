<?php

if (extension_loaded('curl')) {
    global $WishListMemberInstance;
    include_once $WishListMemberInstance->plugin_dir . '/extlib/paypal/ppayflow.php';
    include_once $WishListMemberInstance->plugin_dir . '/extlib/paypal/payflow.php';
    // PPAutoloader::register();
}

if (! class_exists('WLM_INTEGRATION_PAYPALPAYFLOW')) {
    class WLM_INTEGRATION_PAYPALPAYFLOW extends PPayflow
    {
        private $settings;
        private $wlm;

        private $thankyou_url;
        private $pp_settings;
        public function __construct()
        {

            global $WishListMemberInstance;
            $this->wlm      = $WishListMemberInstance;
            $this->products = $this->wlm->get_option('paypalpayflowproducts');

            $settings              = $this->wlm->get_option('paypalpayflowthankyou_url');
            $paypalpayflowthankyou = $this->wlm->get_option('payflowthankyou');
            $this->thankyou_url    = $this->wlm->make_thankyou_url($paypalpayflowthankyou);

            $pp_settings = $this->wlm->get_option('payflowsettings');

            $index   = 'live';
            $sandbox = false;
            if ($pp_settings['sandbox_mode']) {
                $index   = 'sandbox';
                $sandbox = true;
            }

            $payflow_username  = $pp_settings[ $index ]['api_username'];
            $payflow_password  = $pp_settings[ $index ]['api_password'];
            $payflow_vendor    = $pp_settings[ $index ]['merchant_name'];
            $payflow_partner   = 'paypal';
            $payflow_signature = '';

            // Create PayPal object.
            $this->PayPalConfig = [
                'Sandbox'      => $sandbox,
                'APIUsername'  => $payflow_username,
                'APIPassword'  => $payflow_password,
                'APISignature' => $payflow_signature,
                'APIVendor'    => $payflow_vendor,
                'APIPartner'   => $payflow_partner,
                'Verbosity'    => 'HIGH',       // Detail level for API response.  Values are:  LOW, MEDIUM, HIGH
            ];
        }

        public function paypalpayflow($that)
        {
            $action = strtolower(wlm_trim(wlm_get_data()['action']));

            switch ($action) {
                case 'purchase-direct':
                    $this->purchase_direct(wlm_get_data()['id']);
                    break;
                default:
                    // Code...
                    break;
            }
        }

        public function purchase_recurring($product)
        {

            $datenow = wlm_date('mdY', time() + 86400);

            $PayPal = new PayFlow($this->PayPalConfig);

            $cc_number = str_replace(' ', '', trim(wlm_post_data()['cc_number']));

            if (1 == $product['recur_billing_cycles']) {
                $term = 0;
            } else {
                $term = $product['recur_billing_cycles'];
            }

            $frequency = '';
            if ('DAY' === $product['payflow_recur_pay_period']) {
                $frequency = $product['recur_billing_frequency'];
                $payperiod = strtoupper('DAYS');
            } else {
                $payperiod = strtoupper($product['payflow_recur_pay_period']);
            }

            // Prepare request arrays.
            $PayPalRequestData = [
                'tender'      => 'C',              // Required.  The method of payment.  Values are: A = ACH, C = Credit Card, D = Pinless Debit, K = Telecheck, P = PayPal
                'trxtype'     => 'R',                 // Required.  Indicates the type of transaction to perform.  Values are:  A = Authorization, B = Balance Inquiry, C = Credit, D = Delayed Capture, F = Voice Authorization, I = Inquiry, L = Data Upload, N = Duplicate Transaction, S = Sale, V = Void
                'ACTION'      => 'A',
                'PROFILENAME' => 'RegularSubscription',

                // Recurring payment Info.
                'amt'         => $product['recur_amount'],
                'START'       => $datenow,
                'TERM'        => $term,
                'FREQUENCY'   => $frequency,
                'PAYPERIOD'   => $payperiod,
                'CURRENCY'    => $product['currency'],

                // User info.
                'FIRSTNAME'   => wlm_post_data()['first_name'],
                'LASTNAME'    => wlm_post_data()['last_name'],
                'EMAIL'       => wlm_post_data()['email'], // This is the buyer's/customer's email
                'CITY'        => wlm_post_data()['city_name'],
                'STATE'       => wlm_post_data()['state'],
                'ZIP'         => wlm_post_data()['zip_code'],

                // Credit Card Info.
                'acct'        => $cc_number,  // Required for credit card transaction.  Credit card or purchase card number.
                'expdate'     => wlm_post_data()['cc_expmonth'] . wlm_post_data()['cc_expyear'],           // Required for credit card transaction.  Expiration date of the credit card.  Format:  MMYY
                'cvv2'        => wlm_post_data()['cc_cvc'],

                'comment1'    => 'Payment for ' . $product['name'],    // Merchant-defined value for reporting and auditing purposes.  128 char max
            ];

            try {
                // Pass data into class for processing with PayPal and load the response array into $paypal_result.
                $paypal_result = $PayPal->ProcessTransaction($PayPalRequestData);
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => wlm_post_data()['sku'],
                    ]
                );
            }

            if ($paypal_result['RESULT'] > 0) {
                return [
                    'status' => 'failed',
                    'errmsg' => $paypal_result['RESPMSG'],
                ];
            } else {
                return [
                    'status' => 'active',
                    'id'     => $paypal_result['RPREF'] . '-' . $paypal_result['PROFILEID'],
                ];
            }
        }
        public function purchase_one_time($product)
        {

            $PayPal    = new PayFlow($this->PayPalConfig);
            $cc_number = str_replace(' ', '', trim(wlm_post_data()['cc_number']));

            // Prepare request arrays.
            $PayPalRequestData = [
                'tender'      => 'C',              // Required.  The method of payment.  Values are: A = ACH, C = Credit Card, D = Pinless Debit, K = Telecheck, P = PayPal
                'trxtype'     => 'S',                 // Required.  Indicates the type of transaction to perform.  Values are:  A = Authorization, B = Balance Inquiry, C = Credit, D = Delayed Capture, F = Voice Authorization, I = Inquiry, L = Data Upload, N = Duplicate Transaction, S = Sale, V = Void
                'PROFILENAME' => 'RegularSubscription',

                // Recurring payment Info.
                'amt'         => $product['amount'],
                'recurring'   => '',
                'CURRENCY'    => $product['currency'],

                // User info.
                'FIRSTNAME'   => wlm_post_data()['first_name'],
                'LASTNAME'    => wlm_post_data()['last_name'],
                'EMAIL'       => wlm_post_data()['email'], // This is the buyer's/customer's email
                'CITY'        => wlm_post_data()['city_name'],
                'STATE'       => wlm_post_data()['state'],
                'ZIP'         => wlm_post_data()['zip_code'],

                // Credit Card Info.
                'acct'        => $cc_number,  // Required for credit card transaction.  Credit card or purchase card number.
                'expdate'     => wlm_post_data()['cc_expmonth'] . wlm_post_data()['cc_expyear'],           // Required for credit card transaction.  Expiration date of the credit card.  Format:  MMYY
                'cvv2'        => wlm_post_data()['cc_cvc'],
                'CARDTYPE'    => wlm_post_data()['cc_type'],

                'comment1'    => 'Payment for ' . $product['name'],    // Merchant-defined value for reporting and auditing purposes.  128 char max
            ];

            try {
                // Pass data into class for processing with PayPal and load the response array into $paypal_result.
                $paypal_result = $PayPal->ProcessTransaction($PayPalRequestData);
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => wlm_post_data()['sku'],
                    ]
                );
            }

            if ($paypal_result['RESULT'] > 0) {
                return [
                    'status' => 'failed',
                    'errmsg' => $paypal_result['RESPMSG'],
                ];
            } else {
                return [
                    'status' => 'active',
                    'id'     => $paypal_result['PNREF'],
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
                $result = $this->purchase_recurring($product);
            } else {
                $result = $this->purchase_one_time($product);
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
                // Translators: 1: currency, 2: trial amount, 3: frequency period, 4: "s" appended to period if frequency > 1.
                $description .= sprintf(__('%1$s %2$0.2f for the first %3$d %4$s%5$s then ', 'wishlist-member'), $product['currency'], $product['trial_amount'], $product['trial_recur_billing_frequency'], strtolower($product['trial_recur_billing_period']), $product['trial_recur_billing_frequency'] > 1 ? 's' : '');
            }
            // Translators: 1: currency, 2: recurring amount, 3: frequency period, 4: "s" appended to period if frequency > 1.
            $description .= sprintf(__('%1$s %2$0.2f every %3$d %4$s%5$s', 'wishlist-member'), $product['currency'], $product['recur_amount'], $product['recur_billing_frequency'], strtolower($product['recur_billing_period']), $product['recur_billing_frequency'] > 1 ? 's' : '');
            if ($product['recur_billing_cycles'] > 1) {
                // Translators: %d: number of installments.
                $description .= sprintf(__(' for %d installments', 'wishlist-member'), $product['recur_billing_cycles']);
            }
            $description .= ')';
            return str_replace(' 1 ', ' ', $description);
        }
    }
}
