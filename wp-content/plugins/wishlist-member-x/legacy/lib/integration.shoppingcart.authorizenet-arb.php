<?php

if (! class_exists('WLM_INTEGRATION_AUTHORIZENET_ARB')) {

    class WLM_INTEGRATION_AUTHORIZENET_ARB
    {
        private $wlm;

        private $thankyou_url;
        private $settings;

        public function __construct()
        {
            global $WishListMemberInstance;
            $this->wlm           = $WishListMemberInstance;
            $this->subscriptions = $this->wlm->get_option('anetarbsubscriptions');

            $anetarbthankyou    = $this->wlm->get_option('anetarbthankyou');
            $this->thankyou_url = $this->wlm->make_thankyou_url($anetarbthankyou);

            $settings = $this->wlm->get_option('anetarbsettings');

            $this->settings = [
                'acct.api_login_id'    => $settings['api_login_id'],
                'acct.transaction_key' => $settings['api_transaction_key'],
                'mode'                 => $settings['sandbox_mode'] ? 'sandbox' : null,
                'gateway'              => $settings['sandbox_mode'] ? 'https://test.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll',
            ];

            include_once $this->wlm->plugin_dir . '/extlib/wlm_authorizenet_arb/authnet_aim.php';
            include_once $this->wlm->plugin_dir . '/extlib/wlm_authorizenet_arb/authnet_arb.php';
        }

        public function authorizenet_arb($that)
        {

            $action = strtolower(trim(wlm_request_data()['action']));
            $action = $action ? $action : '';

            $valid_actions = ['purchase-direct', 'silent-post', 'sync-arb'];

            if (! in_array($action, $valid_actions)) {
                esc_html_e('Invalid Action.', 'wishlist-member');
                die();
            }

            switch ($action) {
                case 'purchase-direct':
                    $this->purchase_direct();
                    break;
                case 'silent-post':
                    $this->silent_post();
                    break;
                case 'sync-arb':
                    $this->syn_arb();
                    break;
                default:
                    break;
            }
        }

        private function syn_arb()
        {
            $wlm_aurthorizenet_arb_init = new WLMAuthorizeNetARB();
            $ret                        = $wlm_aurthorizenet_arb_init->syn_arb();
            $end                        = isset($ret['end']) ? $ret['end'] : '-unknown-';
            $message                    = isset($ret['message']) ? $ret['message'] : 'empty';
            $count                      = isset($ret['count']) ? $ret['count'] : 0;
            echo wp_kses_data("{$end} {$message} ({$count} records)");
            die();
        }
        private function purchase_direct()
        {

            $subscriptions = $this->subscriptions;

            $id = isset(wlm_post_data()['id']) ? wlm_post_data()['id'] : '';
            if (! isset($subscriptions[ $id ])) {
                esc_html_e('Invalid Transaction ID. Transaction was not processed.', 'wishlist-member');
                die();
            }

            $nonce = isset(wlm_post_data()['nonce']) ? wlm_post_data()['nonce'] : '';
            if (! wp_verify_nonce($nonce, "purchase-direct-{$id}")) {
                esc_html_e('Permission Denied. Transaction was not processed.', 'wishlist-member');
                die();
            }

            $subscription = $subscriptions[ $id ];
            $recurring    = isset($subscription['recurring']) && $subscription['recurring'] ? true : false;
            $sctxnid      = '';
            $response     = 'Unknown error occured.';
            try {
                $login   = $this->settings['acct.api_login_id'];
                $key     = $this->settings['acct.transaction_key'];
                $sandbox = 'sandbox' === $this->settings['mode'] ? true : false;

                $first_name = isset(wlm_post_data()['first_name']) ? trim(wlm_post_data()['first_name']) : '';
                $last_name  = isset(wlm_post_data()['last_name']) ? trim(wlm_post_data()['last_name']) : '';
                $email      = isset(wlm_post_data()['email']) ? trim(wlm_post_data()['email']) : '';

                // Instanciate our AIM class.
                $aim = new WLMAuthnet\AuthnetAIM($login, $key, $sandbox);

                if ($recurring) { // If recurring, lets creat subscription.
                    // Lets validate the card first by sending a 0.01 charge.
                    $validation_data = $this->prepare_validation(wlm_post_data(true));
                    $aim->do_apicall($validation_data); // Process the payment, also throws exception errors if needed.

                    // If charge went through, card is valid.
                    // It wont go this far if the card is invalid.
                    if ($aim->isApproved()) {
                        // Lets VOID the validation transaction.
                        $transid   = $aim->getTransactionID();
                        $void_data = $this->prepare_void($transid);
                        $aim->do_apicall($void_data);
                        // Wether the transaction is voided or not, lets continue with subscription.
                        // Unvoided validation transactions will EXPIRE in 30 days.
                        // So no worries becuase it wont charge.
                        // Instanciate our ARB class.
                        $sub = $this->prepare_subscription($id, $subscription, wlm_post_data(true));
                        $arb = new WLMAuthnet\AuthnetARB($login, $key, $sandbox);
                        $arb->do_apicall('ARBCreateSubscriptionRequest', ['subscription' => $sub]); // Process the subscription, also throws exception errors if needed.
                        // If successful let's get the subscription ID.
                        if ($arb->isSuccessful()) {
                            $sctxnid = $arb->getSubscriberID();
                            $sctxnid = "arb-{$sctxnid}";
                        } else {
                            $response = $arb->getResponse();
                        }
                    }
                } else { // For non recurring.
                    $pay_details = $this->prepare_payment($id, $subscription, wlm_post_data(true));
                    $aim->do_apicall($pay_details); // Process the payment, also throws exception errors if needed.
                    // If successful let's get the transaction ID.
                    if ($aim->isApproved()) {
                        $sctxnid = $aim->getTransactionID();
                    } else {
                        $response = $arb->getResponseText();
                    }
                }

                if (! $sctxnid) { // If not transaction id.
                    $this->fail(
                        [
                            'msg' => $response,
                            'sku' => $id,
                        ]
                    );
                }
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => $id,
                    ]
                );
            }


            // For third party integrations like Easy Affiliates and Monster Insights.
            wlm_post_data()['sc_type']     = 'Authorize.Net - Automatic Recurring Billing';
            wlm_post_data()['paid_amount'] = $subscription['amount'];

            wlm_post_data()['lastname']    = $last_name;
            wlm_post_data()['firstname']   = $first_name;
            wlm_post_data()['action']      = 'wpm_register';
            wlm_post_data()['wpm_id']      = $subscription['sku'];
            wlm_post_data()['username']    = $email;
            wlm_post_data()['email']       = $email;
            wlm_post_data()['sctxnid']     = $sctxnid;
            wlm_post_data()['password1']   = $this->wlm->pass_gen();
            wlm_post_data()['password2']   = wlm_post_data()['password1'];

            $this->wlm->shopping_cart_registration();
        }

        private function silent_post()
        {
            $this->wlm->schedule_sync_membership();
            $sctxnid = false;
            if (isset(wlm_post_data()['x_subscription_id'])) { // For recurring.
                $sctxnid = wlm_post_data()['x_subscription_id'];
                $sctxnid = "arb-{$sctxnid}";
            } else {
                $sctxnid = isset(wlm_post_data()['x_trans_id']) ? wlm_post_data()['x_trans_id'] : false; // For one time purchase.
            }

            if ($sctxnid) {
                // Get the response code. 1 is success, 2 is decline, 3 is error.
                $response_code = (int) wlm_post_data()['x_response_code'];
                // Get the reason code. 8 is expired card.
                $reason_code = (int) wlm_post_data()['x_response_reason_code'];
                // Get the type of transaction.
                $x_type = isset(wlm_post_data()['x_type']) ? strtolower(wlm_post_data()['x_type']) : false;

                wlm_post_data()['sctxnid'] = $sctxnid;

                switch ($response_code) {
                    case 1:
                        switch ($x_type) {
                            case 'credit': // for refund transactions
                            case 'void': // when one time payment transaction is marked as void
                                $this->wlm->shopping_cart_deactivate();
                                break;
                        }
                        break;
                    case 2:
                    case 3:
                    case 8:
                        $this->wlm->shopping_cart_deactivate();
                        break;
                    default:
                        break;
                }
            }
        }

        private function prepare_subscription($id, $subscription, $post_data)
        {

            $first_name = isset($post_data['first_name']) ? wlm_trim($post_data['first_name']) : '';
            $last_name  = isset($post_data['last_name']) ? wlm_trim($post_data['last_name']) : '';
            $email      = isset($post_data['email']) ? wlm_trim($post_data['email']) : '';

            $address = isset($post_data['address']) ? preg_replace('/[^ \w]/', '', $post_data['address']) : '';
            $city    = isset($post_data['city']) ? preg_replace('/[^ \w]/', '', $post_data['city']) : '';
            $state   = isset($post_data['state']) ? preg_replace('/[^ \w]/', '', $post_data['state']) : '';
            $zip     = isset($post_data['zip']) ? preg_replace('/[^ \w]/', '', $post_data['zip']) : '';
            $country = isset($post_data['country']) ? preg_replace('/[^ \w]/', '', $post_data['country']) : '';

            $cc_number     = isset($post_data['cc_number']) ? wlm_trim($post_data['cc_number']) : '';
            $cc_expmonth   = isset($post_data['cc_expmonth']) ? wlm_trim($post_data['cc_expmonth']) : '';
            $cc_expyear    = isset($post_data['cc_expyear']) ? wlm_trim($post_data['cc_expyear']) : '';
            $cc_expiration = $cc_expmonth . $cc_expyear;
            $cc_cvv        = isset($post_data['cc_cvc']) ? wlm_trim($post_data['cc_cvc']) : '';

            $recurinng_amount  = isset($subscription['recur_amount']) ? (float) $subscription['recur_amount'] : 0;
            $subscription_name = isset($subscription['name']) ? $subscription['name'] : $id; // Lets use id if no name is set.

            // Format the text for interval unit, supported values are (days, weeks, months, years)
            switch ($subscription['recur_billing_period']) {
                case 'Day':
                    $interval_units = 'days';
                    $interval_unit  = 'day';
                    break;
                case 'Month':
                    $interval_units = 'months';
                    $interval_unit  = 'month';
                    break;
            }
            $frequency    = (int) $subscription['recur_billing_frequency'];
            $cycle        = $subscription['recur_billing_cycle'] ? (int) $subscription['recur_billing_cycle'] : 0;
            $trial_cycle  = $subscription['trial_billing_cycle'] ? (int) $subscription['trial_billing_cycle'] : 0;
            $trial_amount = $subscription['trial_amount'] ? $subscription['trial_amount'] : 0;
            $trial_amount = $trial_cycle ? (float) $trial_amount : 0.00;

            $cycle = $cycle > 0 ? ( $trial_cycle + $cycle ) : 9999;
            $cycle = $cycle > 9999 ? 9999 : $cycle;

            $sub = [
                'name'            => $subscription_name,
                'paymentSchedule' => [
                    'interval'         => [
                        'length' => (int) $subscription['recur_billing_frequency'],
                        'unit'   => $interval_units,
                    ],
                    'startDate'        => wlm_date('Y-m-d', strtotime('+1 day')),
                    'totalOccurrences' => $cycle, // unli 999, or cycle
                    'trialOccurrences' => $trial_cycle, // number of cycle for trial, must included in totalOccurences

                ],
                'amount'          => $recurinng_amount,
                'trialAmount'     => $trial_amount,
                'payment'         => [
                    'creditCard' => [
                        'cardNumber'     => $cc_number,
                        'expirationDate' => $cc_expiration,
                        'cardCode'       => $cc_cvv,
                    ],
                ],
                'customer'        => [
                    'email' => $email,
                ],
                'billTo'          => [
                    'firstName' => $first_name,
                    'lastName'  => $last_name,
                ],
            ];

            if (! empty($address)) {
                $sub['billTo']['address'] = $address;
            }
            if (! empty($city)) {
                $sub['billTo']['city'] = $city;
            }
            if (! empty($state)) {
                $sub['billTo']['state'] = $state;
            }
            if (! empty($zip)) {
                $sub['billTo']['zip'] = $zip;
            }
            if (! empty($country)) {
                $sub['billTo']['country'] = $country;
            }

            return $sub;
        }

        private function prepare_payment($id, $subscription, $post_data)
        {

            $first_name = isset($post_data['first_name']) ? wlm_trim($post_data['first_name']) : '';
            $last_name  = isset($post_data['last_name']) ? wlm_trim($post_data['last_name']) : '';
            $email      = isset($post_data['email']) ? wlm_trim($post_data['email']) : '';

            $address = isset($post_data['address']) ? preg_replace('/[^ \w]/', '', $post_data['address']) : '';
            $city    = isset($post_data['city']) ? preg_replace('/[^ \w]/', '', $post_data['city']) : '';
            $state   = isset($post_data['state']) ? preg_replace('/[^ \w]/', '', $post_data['state']) : '';
            $zip     = isset($post_data['zip']) ? preg_replace('/[^ \w]/', '', $post_data['zip']) : '';
            $country = isset($post_data['country']) ? preg_replace('/[^ \w]/', '', $post_data['country']) : '';

            $cc_number     = isset($post_data['cc_number']) ? wlm_trim($post_data['cc_number']) : '';
            $cc_expmonth   = isset($post_data['cc_expmonth']) ? wlm_trim($post_data['cc_expmonth']) : '';
            $cc_expyear    = isset($post_data['cc_expyear']) ? wlm_trim($post_data['cc_expyear']) : '';
            $cc_expiration = $cc_expmonth . $cc_expyear;
            $cc_cvv        = isset($post_data['cc_cvc']) ? wlm_trim($post_data['cc_cvc']) : '';

            $recurring    = isset($subscription['recurring']) && $subscription['recurring'] ? true : false;
            $product_name = isset($subscription['name']) ? $subscription['name'] : $id; // Lets use id if no name is set.
            if ($recurring) { // For recurring.
                $init_amount = isset($subscription['init_amount']) ? (float) $subscription['init_amount'] : '';
            } else { // Non recurring.
                $init_amount = isset($subscription['amount']) ? (float) $subscription['amount'] : '';
            }

            $invoice    = null;
            $tax        = null;
            $array_data = [
                'x_delim_data'     => 'TRUE',
                'x_delim_char'     => '|',
                'x_relay_response' => 'FALSE',
                'x_url'            => 'FALSE',
                'x_version'        => '3.1',
                'x_method'         => 'CC',
                'x_type'           => 'AUTH_CAPTURE',
                'x_card_num'       => $cc_number,
                'x_exp_date'       => $cc_expiration,
                'x_amount'         => $init_amount,
                'x_po_num'         => $invoice,
                'x_tax'            => $tax,
                'x_card_code'      => $cc_cvv,
                'x_description'    => $product_name,
                'x_first_name'     => $first_name,
                'x_last_name'      => $last_name,
                'x_email'          => $email,
            ];
            if (! empty($address)) {
                $array_data['x_address'] = $address;
            }
            if (! empty($city)) {
                $array_data['x_city'] = $city;
            }
            if (! empty($state)) {
                $array_data['x_state'] = $state;
            }
            if (! empty($zip)) {
                $array_data['x_zip'] = $zip;
            }
            if (! empty($country)) {
                $array_data['x_country'] = $country;
            }

            return $array_data;
        }

        private function prepare_validation($post_data)
        {
            $first_name = isset($post_data['first_name']) ? wlm_trim($post_data['first_name']) : '';
            $last_name  = isset($post_data['last_name']) ? wlm_trim($post_data['last_name']) : '';
            $email      = isset($post_data['email']) ? wlm_trim($post_data['email']) : '';

            $address = isset($post_data['address']) ? preg_replace('/[^ \w]/', '', $post_data['address']) : '';
            $city    = isset($post_data['city']) ? preg_replace('/[^ \w]/', '', $post_data['city']) : '';
            $state   = isset($post_data['state']) ? preg_replace('/[^ \w]/', '', $post_data['state']) : '';
            $zip     = isset($post_data['zip']) ? preg_replace('/[^ \w]/', '', $post_data['zip']) : '';
            $country = isset($post_data['country']) ? preg_replace('/[^ \w]/', '', $post_data['country']) : '';

            $cc_number     = isset($post_data['cc_number']) ? wlm_trim($post_data['cc_number']) : '';
            $cc_expmonth   = isset($post_data['cc_expmonth']) ? wlm_trim($post_data['cc_expmonth']) : '';
            $cc_expyear    = isset($post_data['cc_expyear']) ? wlm_trim($post_data['cc_expyear']) : '';
            $cc_expiration = $cc_expmonth . $cc_expyear;
            $cc_cvv        = isset($post_data['cc_cvc']) ? wlm_trim($post_data['cc_cvc']) : '';

            $product_name = 'WLM ARB Integration. Card Validation Transaction.';
            $amount       = 0.01;

            $invoice    = null;
            $tax        = null;
            $array_data = [
                'x_delim_data'     => 'TRUE',
                'x_delim_char'     => '|',
                'x_relay_response' => 'FALSE',
                'x_url'            => 'FALSE',
                'x_version'        => '3.1',
                'x_method'         => 'CC',
                'x_type'           => 'AUTH_ONLY',
                'x_card_num'       => $cc_number,
                'x_exp_date'       => $cc_expiration,
                'x_amount'         => $amount,
                'x_card_code'      => $cc_cvv,
                'x_description'    => $product_name,
                'x_first_name'     => $first_name,
                'x_last_name'      => $last_name,
                'x_email'          => $email,
            ];
            if (! empty($address)) {
                $array_data['x_address'] = $address;
            }
            if (! empty($city)) {
                $array_data['x_city'] = $city;
            }
            if (! empty($state)) {
                $array_data['x_state'] = $state;
            }
            if (! empty($zip)) {
                $array_data['x_zip'] = $zip;
            }
            if (! empty($country)) {
                $array_data['x_country'] = $country;
            }

            return $array_data;
        }

        private function prepare_void($transid)
        {
            $array_data = [
                'x_delim_data'     => 'TRUE',
                'x_delim_char'     => '|',
                'x_relay_response' => 'FALSE',
                'x_url'            => 'FALSE',
                'x_version'        => '3.1',
                'x_method'         => 'CC',
                'x_type'           => 'VOID',
                'x_trans_id'       => $transid,
            ];
            return $array_data;
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
    }
}
