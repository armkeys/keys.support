<?php

require_once dirname(__FILE__) . '/../extlib/eway/EwayWebServiceClient.php';
require_once dirname(__FILE__) . '/../extlib/eway/EwayRecurWebserviceClient.php';

if (! class_exists('WLM_INTEGRATION_EWAY')) {

    class WLM_INTEGRATION_EWAY
    {
        public $wlm;
        public $eway_ws;

        protected $sandbox = true;
        public function __construct()
        {
            add_action('admin_notices', [$this, 'notices']);

            global $WishListMemberInstance;
            $settings      = $WishListMemberInstance->get_option('ewaysettings');
            $this->eway_ws = new EwayRecurWebserviceClient($settings['eway_customer_id'], $settings['eway_username'], $settings['eway_password'], $settings['eway_sandbox']);
        }
        public function eway_process($wlm)
        {
            $this->wlm     = $wlm;
            $action        = trim(strtolower(wlm_request_data()['regform_action']));
            $valid_actions = ['charge', 'sync', 'update_payment', 'cancel', 'invoices', 'invoice', 'migrate'];
            switch ($action) {
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
                default:
                    // Code...
                    break;
            }
        }
        public function charge($data = [])
        {
            $settings = $this->wlm->get_option('ewaysettings');

            try {
                $last_name  = $data['last_name'];
                $first_name = $data['first_name'];

                if ('new' === $data['charge_type']) {
                    if (empty($last_name) || empty($first_name) || empty($data['email'])) {
                        throw new Exception('All fields are required');
                    }
                }

                if (empty($data['cc_number']) || empty($data['cc_expmonth']) || empty($data['cc_expyear'])) {
                    throw new Exception('All fields are required');
                }

                wlm_post_data()['level']     = $data['sku'];
                wlm_post_data()['lastname']  = $last_name;
                wlm_post_data()['firstname'] = $first_name;
                wlm_post_data()['action']    = 'wpm_register';
                wlm_post_data()['wpm_id']    = $data['sku'];
                wlm_post_data()['username']  = $data['email'];
                wlm_post_data()['email']     = $data['email'];
                wlm_post_data()['password1'] = $this->wlm->pass_gen();
                wlm_post_data()['password2'] = wlm_post_data()['password1'];
                if ('new' === $data['charge_type']) {
                    $this->charge_new(wlm_post_data(true));
                } else {
                    $this->charge_existing(wlm_post_data(true));
                }
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => $data['sku'],
                    ]
                );
            }
        }
        public function cancel($data = [])
        {
            // Not implemented.
        }

        public function update_payment($data = [])
        {
            // Not implemented.
        }

        public static function sync($data = [])
        {
            global $wpdb;
            global $WishListMemberInstance;

            $settings = $WishListMemberInstance->get_option('ewaysettings');
            $eway_ws  = new EwayRecurWebserviceClient($settings['eway_customer_id'], $settings['eway_username'], $settings['eway_password'], $settings['eway_sandbox']);

            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT * FROM `' . esc_sql(wishlistmember_instance()->table_names->userlevel_options) . '` WHERE option_value like %s',
                    'EWAYRB-%-%-%'
                )
            );
            foreach ($results as $row) {
                list($tmp, $rebill_id, $invoice_ref, $cust_id) = explode('-', $row->option_value);
                if (empty($rebill_id) || empty($cust_id) || empty($invoice_ref)) {
                    continue;
                }

                $params = [
                    'RebillCustomerID' => $cust_id,
                    'RebillID'         => $rebill_id,
                ];

                $res = $eway_ws->call('QueryTransactions', $params);
                if (empty($res)) {
                    continue;
                }

                                $QueryTransactionsResult = $res['QueryTransactionsResult'];
                if (is_array($QueryTransactionsResult)) {
                    if (array_key_exists('rebillTransaction', $QueryTransactionsResult)) {
                        $rebills    = $res['QueryTransactionsResult']['rebillTransaction'];
                        $last_trans = $rebills[0];
                    }
                }

                if (is_array($rebills) && isset($rebills)) {
                    foreach ($rebills as $r) {
                        if (is_array($r)) {
                            if (array_key_exists('Status', $r)) {
                                if ('Future' === $r['Status']) {
                                     break;
                                }
                            }
                        }

                        $last_trans = $r;
                    }
                }

                wlm_post_data()['sctxnid'] = $row->option_value;
                if (( isset($last_trans['Status']) ) && ( 'Failed' === $last_trans['Status'] )) {
                    $WishListMemberInstance->shopping_cart_deactivate();
                } else {
                    $WishListMemberInstance->shopping_cart_reactivate();
                }
            }
        }
        public function add_to_level($user_id, $level_id, $txn_id)
        {
            $user   = new \WishListMember\User($user_id);
            $levels = $user->Levels;

            $remaining_levels = [$level_id];
            foreach ($levels as $i => $l) {
                $remaining_levels[] = $i;
            }

            $this->wlm->set_membership_levels($user_id, $remaining_levels);
            if ($this->wlm->is_ppp_level($level_id)) {
                list($tmp, $content_id) = explode('-', $level_id);
                $this->wlm->add_user_post_transaction_id($user_id, $content_id, $txn_id);
                $this->wlm->add_user_post_timestamp($user_id, $content_id);
            } else {
                $this->wlm->set_membership_level_txn_id($user_id, $level_id, $txn_id);
            }
        }
        public function charge_existing($data)
        {
            try {
                global $current_user;
                $cust_id = $this->wlm->Get_UserMeta($current_user->ID, 'eway_cust_id');
                // $cust_id = null;
                if (empty($cust_id)) {
                    $cust_id = $this->create_customer($data);
                    $this->wlm->Update_UserMeta($current_user->ID, 'eway_cust_id', $cust_id);
                }

                $txn_id = $this->charge_customer($cust_id, $data, $data['sku']);

                // Add user to level and redirect to the after reg url.
                $this->add_to_level($current_user->ID, $data['sku'], $txn_id);
                $url = $this->wlm->get_after_reg_redirect($data['sku']);
                wp_redirect($url);
                die();
            } catch (Exception $e) {
                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => $data['wpm_id'],
                    ]
                );
            }
        }
        private function create_customer($cust)
        {
            // Create the cust.
            $data['customerFirstName'] = $cust['first_name'];
            $data['customerLastName']  = $cust['last_name'];
            $data['customerEmail']     = $cust['email'];

            $res = $this->eway_ws->call('CreateRebillCustomer', $data);
            if ('success' !== strtolower($res['CreateRebillCustomerResult']['Result'])) {
                throw new Exception('Could not create customer');
            }
            return $res['CreateRebillCustomerResult']['RebillCustomerID'];
        }
        private function charge_customer($cust_id, $cc_data, $sku)
        {
            // Now create the rebill.
            // Create an invoiceRef in order to track this txn.
            // We need a txn id when querying the 24 hour txn api.
            // Note: eway gateway doesn't like non-numeric invoice ref.
            $wpm_levels = $this->wlm->get_option('wpm_levels');

            $settings       = $this->wlm->get_option('ewaysettings');
            $level_settings = $settings['connections'][ $sku ];
            $invoice_ref    = microtime(true);
            $invoice_ref    = preg_replace('/\D/', '', $invoice_ref);
            $end_date       = strtotime($level_settings['rebill_end_date']);
            $start_date     = time() + 3600 * 24;
            $init_date      = $start_date;

            // Computation table for the correct start-date.
            $next_interval_types = [
                1 => time() + 3600 * 24 * 2, // start next day instead of the same day as initdate
                2 => time() + 3600 * 24 * 7,
                3 => strtotime('+1 month', time()),
                4 => strtotime('+1 year', time()),
            ];

            $rebill_init_amount  = (int) $level_settings['rebill_init_amount'] * 100;
            $rebill_recur_amount = (int) $level_settings['rebill_recur_amount'] * 100;
            $start_date          = $next_interval_types[ $level_settings['rebill_interval_type'] ];

            if (1 != $level_settings['subscription']) {
                // We will create a subscription with a single rebill event.
                $level_settings['rebill_interval']      = 1;
                $level_settings['rebill_interval_type'] = 3;// 1 month subscription
                // We will use the init amount instead of the the recur amount.
                $rebill_recur_amount = $rebill_init_amount;
                $rebill_init_amount  = 0;
                // Set the start date to next day and end date to the following day.
                // We set interval_type to month beforehand so this will ensure.
                // There will only be a single rebill.
                $start_date = $init_date;
                $end_date   = strtotime('+1 day', $start_date);
            }

            $data                       = [];
            $data['RebillCustomerID']   = $cust_id;
            $data['RebillInvRef']       = $invoice_ref;
            $data['RebillInvDes']       = $wpm_levels[ $sku ]['name'] . ' Subscription';
            $data['RebillCCName']       = $cc_data['first_name'] . ' ' . $cc_data['last_name'];
            $data['RebillCCNumber']     = $cc_data['cc_number'];
            $data['RebillCCExpMonth']   = $cc_data['cc_expmonth'];
            $data['RebillCCExpYear']    = $cc_data['cc_expyear'];
            $data['RebillInitAmt']      = $rebill_init_amount;
            $data['RebillInitDate']     = wlm_date('d/m/Y', $init_date);
            $data['RebillRecurAmt']     = $rebill_recur_amount;
            $data['RebillStartDate']    = wlm_date('d/m/Y', $start_date);
            $data['RebillInterval']     = (int) $level_settings['rebill_interval'];
            $data['RebillIntervalType'] = (int) $level_settings['rebill_interval_type'];
            $data['RebillEndDate']      = wlm_date('d/m/Y', $end_date);
            $res                        = $this->eway_ws->call('CreateRebillEvent', $data);

            $txn_id = sprintf('EWAYRB-%s-%s-%s', $res['CreateRebillEventResult']['RebillID'], $invoice_ref, $cust_id);
            if ('success' !== strtolower($res['CreateRebillEventResult']['Result'])) {
                throw new Exception('Payment processing failed');
            }
            return $txn_id;
        }
        public function charge_new($data)
        {
            try {
                // Create the customer.
                $cust_id          = $this->create_customer($data);
                $txn_id           = $this->charge_customer($cust_id, $data, $data['sku']);
                wlm_post_data()['sctxnid'] = $txn_id;
                $this->wlm->shopping_cart_registration(true, false);

                $user = get_user_by('login', 'temp_' . md5($data['email']));
                $this->wlm->Update_UserMeta($user->ID, 'eway_cust_id', $cust_id);
                $url = $this->wlm->get_continue_registration_url($data['email']);
                wp_redirect($url);
                die();
            } catch (Exception $e) {
                if (! empty($cust_id)) {
                    // Something went wrong while charging.
                    // Delete the stripe customer so we don't get cluttered.
                    // With unlinked customers.
                    $resp = $this->eway_ws->call(
                        'DeleteRebillCustomer',
                        [
                            'RebillCustomerID' => $cust_id,
                        ]
                    );
                }

                $this->fail(
                    [
                        'msg' => $e->getMessage(),
                        'sku' => $data['wpm_id'],
                    ]
                );
            }
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
