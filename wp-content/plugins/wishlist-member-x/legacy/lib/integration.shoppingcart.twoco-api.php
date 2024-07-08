<?php

/**
 * 2Checkout Payment API references:
 * Library - https://github.com/2Checkout/php-examples
 * Documentation - https://www.2checkout.com/documentation/payment-api/create-sale
 */

if (! class_exists('WLM_INTEGRATION_TWOCO_API')) {

    class WLM_INTEGRATION_TWOCO_API
    {
        public $wlm;
        public $twoco_ws;

        protected $sandbox = true;
        public function __construct()
        {
            add_action('admin_notices', [$this, 'notices']);

            global $WishListMemberInstance;
            $settings = $WishListMemberInstance->get_option('twocheckoutapisettings');
        }
        public function twoco_api_process($wlm)
        {

            if (! isset(wlm_request_data()['regform_action'])) {
                exit;
            }

            $this->wlm     = $wlm;
            $action        = trim(strtolower(wlm_request_data()['regform_action']));
            $valid_actions = ['charge', 'sync', 'update_payment', 'cancel', 'invoices', 'invoice', 'migrate'];
            // If (!in_array($action, $valid_actions)) {
            // _e("Permission Denied", "wishlist-member");
            // Die();
            // }
            // If (('sync' !== $action && 'migrate' !== $action) && !wp_verify_nonce(wlm_request_data()['nonce'], "eway-do-$action")) {
            // _e("Permission Denied", "wishlist-member");
            // Die();
            // }
            switch ($action) {
                case 'charge':
                    // Code...
                    $this->charge(wlm_post_data(true));
                    break;
                case 'failed':
                    throw new Exception('There was an error processing your Credit Card.');
                    break;
                default:
                    // Code...
                    break;
            }
        }
        public function charge($data = [])
        {

            $settings = $this->wlm->get_option('twocheckoutapisettings');

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
                wlm_post_data()['address']   = $data['address'];
                wlm_post_data()['zipCode']   = $data['zipCode'];
                wlm_post_data()['city']      = $data['city'];
                wlm_post_data()['state']     = $data['state'];
                wlm_post_data()['country']   = $data['country'];
                wlm_post_data()['password1'] = $this->wlm->pass_gen();
                wlm_post_data()['password2'] = wlm_post_data()['password1'];

                wlm_post_data()['token'] = wlm_post_data()['token'];
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

                $cust_id = $current_user->ID;

                $txn_id = $this->charge_customer($cust_id, $data, $data);

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

        private function charge_customer($cust_id, $cc_data, $var)
        {

            $wpm_levels = $this->wlm->get_option('wpm_levels');

            $twocheckoutapisettings = $this->wlm->get_option('twocheckoutapisettings');
            $level_settings         = $twocheckoutapisettings['connections'][ $var['wpm_id'] ];

            $price       = $level_settings['rebill_init_amount'];
            $startup_fee = '';

            if (1 == $level_settings['subscription']) {
                // If it's rebill type then set the price as the rebill amount.
                $price = $level_settings['rebill_recur_amount'];

                // Also set the start up fee.
                $startup_fee = $level_settings['rebill_init_amount'];

                switch ($level_settings['rebill_interval_type']) {
                    case 1:
                        $interval = $level_settings['rebill_interval'] . ' Days';
                        break;
                    case 2:
                        $interval = $level_settings['rebill_interval'] . ' Week';
                        break;
                    case 3:
                        $interval = $level_settings['rebill_interval'] . ' Month';
                        break;
                    case 4:
                        $interval = $level_settings['rebill_interval'] . ' Year';
                        break;
                }
            }

            if (extension_loaded('curl') && ! class_exists('Twocheckout', false)) {
                include_once $this->wlm->plugin_dir . '/extlib/wlm_twoco_api/lib/Twocheckout.php';
            }

            $private_key = $twocheckoutapisettings['twocheckoutapi_private_key'];
            $seller_id   = $twocheckoutapisettings['twocheckoutapi_seller_id'];

            Twocheckout::privateKey($private_key);
            Twocheckout::sellerId($seller_id);
            if ($twocheckoutapisettings['twocheckoutapi_sandbox']) {
                Twocheckout::sandbox(true);
            } else {
                Twocheckout::sandbox(false);
            }

            try {
                $charge = Twocheckout_Charge::auth(
                    [
                        'merchantOrderId' => wlm_post_data()['sku'],
                        'token'           => $var['token'],
                        'currency'        => empty($twocheckoutapisettings['currency']) ? 'USD' : $twocheckoutapisettings['currency'],
                        'lineItems'       => [
                            0 =>
                                                                            [
                                                                                'name'     => $var['wpm_id'],
                                                                                'price'    => $price,
                                                                                'type'     => 'product',
                                                                                'quantity' => '1',
                                                                                'productId' => wlm_post_data()['sku'],
                                                                                'recurrence' => $interval,
                                                                                'startupFee' => '',
                                                                                'duration' => '',
                                                                            ],
                        ],
                        'billingAddr'     => [
                            'name'        => $var['firstname'] . ' ' . $var['lastname'],
                            'addrLine1'   => empty($var['address']) ? 'N/A' : $var['address'],
                            'city'        => empty($var['city']) ? 'N/A' : $var['city'],
                            'state'       => empty($var['state']) ? 'N/A' : $var['state'],
                            'zipCode'     => empty($var['zipCode']) ? 'N/A' : $var['zipCode'],
                            'country'     => empty($var['country']) ? 'N/A' : $var['country'],
                            'email'       => $var['email'],
                            'phoneNumber' => 'NA',
                        ],
                    ]
                );

                if ('APPROVED' === $charge['response']['responseCode']) {
                    return $result['response']['transactionId'];
                } else {
                    throw new Exception('There was an error processing your request, Please try again.');
                }
            } catch (Twocheckout_Error $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function charge_new($data)
        {
            try {
                // Create the customer.
                $txn_id = $this->charge_customer('', $data, $data);

                wlm_post_data()['sctxnid'] = $txn_id;
                $this->wlm->shopping_cart_registration(true, false);

                $user = get_user_by('login', 'temp_' . md5($data['email']));
                $url  = $this->wlm->get_continue_registration_url($data['email']);
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

            if ('success' !== strtolower($res['CreateRebillCustomerResult']['Result'])) {
                throw new Exception('Could not create customer');
            }
            return $res['CreateRebillCustomerResult']['RebillCustomerID'];
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
