<?php

/*
 * Paypal Payment Provider Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_PAYPAL';
// $__optionname__ = 'ppthankyou';
// $__methodname__ = 'Paypal';
if (! class_exists('WLM_INTEGRATION_PAYPAL')) {

    class WLM_INTEGRATION_PAYPAL
    {
        public $wlmversion;

        public function __construct()
        {
            global $wlm_paypalps_init;
            $pid = wlm_get_data()['pid'];
            if ($pid) {
                $redirect = $wlm_paypalps_init->paypal_link($pid);
                if ($redirect) {
                    wp_redirect($redirect);
                    exit;
                }

                wp_die(
                    sprintf(
                        '<div style="text-align:center">%s<br><br>%s<br><br><a href="%s" style="font-size:small;color:gray">%s</a></div>',
                        esc_html__('It appears the product you are trying to purchase no longer exists.', 'wishlist-member'),
                        esc_html__('Please contact the site owner for more information.', 'wishlist-member'),
                        esc_url(get_bloginfo('url')),
                        sprintf(
                            // Translators: %s blog name.
                            esc_html__('Go back to %s', 'wishlist-member'),
                            esc_html(get_bloginfo('name'))
                        )
                    ),
                    esc_html__('Invalid PayPal Link', 'wishlist-member')
                );
            }
        }

        public function Paypal($that)
        {
            $ppsandbox = (int) $that->get_option('ppsandbox');
            if (1 === (int) $ppsandbox) {
                $urls    = 'ssl://www.sandbox.paypal.com';
                $url     = 'www.sandbox.paypal.com';
                $pphosts = [
                    1 => "Host: www.sandbox.paypal.com\r\n",
                    2 => "Host: http://www.sandbox.paypal.com\r\n",
                ];
                wlm_post_data()['wishlistmember_test_mode'] = 1;
            } else {
                $urls    = 'ssl://www.paypal.com';
                $url     = 'www.paypal.com';
                $pphosts = [
                    1 => "Host: www.paypal.com\r\n",
                    2 => "Host: http://www.paypal.com\r\n",
                ];
            }

            $this->wlmversion = $that->plugin_latest_version();
            /*
             * Paypal Payment Data Transfer (PDT)
             * This section of the code takes care of Paypal's PDT
             * by processing the data passed to WishList Member and
             * verifying it. If the data is valid, then we create
             * a temporary account and redirect the user to the
             * registration form to let him complete his registration.
             */

            // Let's set the SC Type and the Paid Amount so 3rd party plugins like Easy Affiliate can use it.
            wlm_post_data()['paid_amount'] = $_GET['amt'];
            wlm_post_data()['sc_type']     = 'PayPal Payments Standard (Legacy)';

            // If wlm_get_data()['tx'] is passed then we do with PDT.
            if (! empty(wlm_get_data()['tx'])) { /*
                                                     start of PDT */
                /*
                 * Verify that the data received is from Paypal.
                 * Verification code is based on Paypal's sample code
                 */

                // Try without header HOST.
                $req   = 'cmd=_notify-synch';
                $token = $that->get_option($ppsandbox ? 'ppsandboxtoken' : 'pptoken');
                $req  .= '&tx=' . wlm_get_data()['tx'] . '&at=' . $token;

                $lines = $this->verify('PDT', $urls, $url, $req, $pphosts);

                if ($lines) {
                    do_action_deprecated('wlmem_paypal_pdt_response', [], '3.10', 'wishlistmember_paypal_pdt_response');
                    do_action('wishlistmember_paypal_pdt_response');
                }
                /*
                 * at this point, we're sure that the data we received
                 * is indeed from Paypal so we continue with the registration
                 */
                $data = [];
                for ($i = 1; $i < count($lines); $i++) {
                    list($key, $val)           = explode('=', $lines[ $i ], 2);
                    $data[ urldecode($key) ] = urldecode($val);
                }
                wlm_post_data()['lastname']  = $data['last_name'];
                wlm_post_data()['firstname'] = $data['first_name'];
                wlm_post_data()['wpm_id']    = $data['item_number'];
                wlm_post_data()['username']  = $data['payer_email'];
                wlm_post_data()['email']     = $data['payer_email'];
                wlm_post_data()['password1'] = $that->pass_gen(null, true);
                wlm_post_data()['password2'] = wlm_post_data()['password1'];

                /*
                 * Use the txn_id's id's by default but if we
                 * have subscr_id, then we use it instead
                 */
                wlm_post_data()['sctxnid'] = $data['parent_txn_id'] ? $data['parent_txn_id'] : $data['txn_id'];
                wlm_post_data()['sctxnid'] = $data['subscr_id'] ? $data['subscr_id'] : wlm_post_data()['sctxnid'];

                /*
                 * Assumes that this the first purchase, so we'll
                 * Only look at completion of payment
                 * No pending handling yet
                 */
                if (isset($data['payment_status']) && 'Completed' === wlm_trim($data['payment_status'])) {
                    /*
                     * create temporary account
                     */
                    $that->shopping_cart_registration();
                } elseif (isset($data['payment_status']) && 'Pending' === wlm_trim($data['payment_status'])) {
                    /*
                     * create temporary account
                     */
                    $that->shopping_cart_registration(null, null, 'Paypal Pending');
                }
                return;
            } //end if


            /*
             * Paypal Instant Payment Notification (IPN)
             *
             * This section of the code processes IPN data
             * sent by Paypal and handles the deactivation / reactivation
             * of a user's Membership Level based on the transaction ID
             * that was passed.
             *
             * IPN always send data via POST
             */
            if (! empty(wlm_post_data()['payment_status']) || ! empty(wlm_post_data()['txn_type'])) { /*
                                                                                                          start of IPN */
                /*
                 * first, we validate the data that we received to
                 * confirm that it's valid IPN information from Paypal
                 */
                $req = 'cmd=_notify-validate';
                foreach ((array) wlm_post_data(true) as $key => $value) {
                    $req .= ( '&' . $key . '=' . urlencode(stripslashes($value)) );
                }

                $verified = $this->verify('IPN', $urls, $url, $req, $pphosts);
                if ($verified) {
                    /*
                     * If Paypal returns VERIFIED then we proceed
                     */
                    // Hook for Blair Williams Affiliate Program.
                    do_action_deprecated('wlmem_paypal_ipn_response', [], '3.10', 'wishlistmember_paypal_ipn_response');
                    do_action('wishlistmember_paypal_ipn_response');

                    wlm_post_data()['lastname']  = wlm_post_data()['last_name'];
                    wlm_post_data()['firstname'] = wlm_post_data()['first_name'];
                    wlm_post_data()['action']    = 'wpm_register';

                    wlm_post_data()['wpm_id']    = wlm_post_data()['item_number'];
                    wlm_post_data()['username']  = wlm_post_data()['payer_email'];
                    wlm_post_data()['email']     = wlm_post_data()['payer_email'];
                    wlm_post_data()['password1'] = $that->pass_gen(null, true);
                    wlm_post_data()['password2'] = wlm_post_data()['password1'];

                    $address             = [];
                    $address['company']  = wlm_post_data()['payer_business_name'] ? wlm_post_data()['payer_business_name'] : wlm_post_data()['address_name'];
                    $address['address1'] = wlm_post_data()['address_street'];
                    $address['address2'] = '';
                    $address['city']     = wlm_post_data()['address_city'];
                    $address['state']    = wlm_post_data()['address_state'];
                    $address['zip']      = wlm_post_data()['address_zip'];
                    $address['country']  = wlm_post_data()['address_country'];

                    /*
                     * do we have custom variable and is it an IP address?
                     * if so, save it as transient for 8 hours
                     */
                    if (isset(wlm_post_data()['custom'])) {
                        $hash = md5(wlm_post_data()['custom']);
                        $that->set_transient_hash($hash, wlm_post_data()['payer_email']);
                    }

                    /*
                     * determine the correct transaction ID to use
                     */
                    if (wlm_post_data()['subscr_id']) {
                        wlm_post_data()['sctxnid'] = wlm_post_data()['subscr_id'];
                    } else {
                        wlm_post_data()['sctxnid'] = wlm_post_data()['parent_txn_id'] ? wlm_post_data()['parent_txn_id'] : wlm_post_data()['txn_id'];
                    }

                    $status = wlm_post_data()['payment_status'] ? wlm_post_data()['payment_status'] : wlm_post_data()['txn_type'];

                    switch ($status) {
                        case 'subscr_signup':
                            wlm_post_data()['wpm_useraddress'] = $address;
                            // We have a subscription sign-up so we register it...
                            // Check if the email is already registered and that the txn_id for the level is already active.
                            // Avoid additional incomplete registrations with the same email in case the IPN is delayed.
                            $user_data = wlmapi_get_member_by('user_email', wlm_post_data()['email']);
                            $user_id   = $user_data['members']['member'][0]['id'];

                            $member_data = wlmapi_get_level_member_data(wlm_post_data()['wpm_id'], $user_id);
                            $txn_id      = $member_data['member']['level']->TxnID;
                            if (wlm_post_data()['sctxnid'] != $txn_id) {
                                $that->shopping_cart_registration(null, false);
                                $that->cart_integration_terminate();
                            }

                            break;
                        case 'Completed':
                            if (isset(wlm_post_data()['echeck_time_processed'])) {
                                // We remove the status "N:Paypal Pending" if paypal sends notification that echeck payment has been processed.
                                $that->shopping_cart_reactivate(1);
                            } elseif ('subscr_payment' === wlm_post_data()['txn_type']) {
                                // We reactivate the account for any subscr_payment notice.
                                $that->shopping_cart_reactivate();
                            } else {
                                wlm_post_data()['wpm_useraddress'] = $address;
                                // If txn_type is not subscr_payment then it's a one-time payment so we register the user.
                                // Check if the email is already registered and that the txn_id for the level is already active.
                                // Avoid additional incomplete registrations with the same email in case the IPN is delayed.
                                if ('web_accept' === wlm_post_data()['txn_type']) {
                                    $user_data = wlmapi_get_member_by('user_email', wlm_post_data()['email']);
                                    $user_id   = $user_data['members']['member'][0]['id'];

                                    $member_data = wlmapi_get_level_member_data(wlm_post_data()['wpm_id'], $user_id);
                                    $txn_id      = $member_data['member']['level']->TxnID;
                                    if (wlm_post_data()['sctxnid'] != $txn_id) {
                                        $that->shopping_cart_registration(null, false);
                                        $that->cart_integration_terminate();
                                    }
                                } else {
                                    $that->shopping_cart_registration(null, false);
                                    $that->cart_integration_terminate();
                                }
                            }
                            break;
                        case 'Canceled-Reversal':
                            $that->shopping_cart_reactivate();
                            break;
                        case 'Processed':
                            $that->shopping_cart_reactivate('Confirm');
                            break;
                        case 'Expired':
                        case 'Failed':
                        case 'Refunded':
                            wlm_post_data()['is_wlm_sc_refund'] = true;
                            wlm_post_data()['paid_amount'] = ( wlm_post_data()['paid_amount'] ) ? wlm_post_data()['paid_amount'] : wlm_post_data()['amount'];
                            do_action('wishlistmember_shoppingcart_refund', wlm_post_data(true));
                        case 'Reversed':
                        case 'subscr_failed':
                        case 'recurring_payment_suspended_due_to_max_failed_payment': // Recurring payment suspended -- exceeded maximum number of failed payments allowed
                            $that->shopping_cart_deactivate();
                            break;
                        case 'subscr_eot':
                            // Get eot settings.
                            $eotcancel = $that->get_option('eotcancel');
                            if ($eotcancel) {
                                $eotcancel = wlm_maybe_unserialize($eotcancel);
                            } else {
                                $eotcancel = [];
                            }

                            if (isset($eotcancel[ wlm_post_data()['wpm_id'] ]) && 1 == $eotcancel[ wlm_post_data()['wpm_id'] ]) {
                                $that->shopping_cart_deactivate();
                            }
                            break;
                        case 'subscr_cancel':
                            // Lets cancel for trial subscriptions.
                            $subscrcancel = $that->get_option('subscrcancel');
                            if ($subscrcancel) {
                                $subscrcancel = wlm_maybe_unserialize($subscrcancel);
                            } else {
                                $subscrcancel = false;
                            }

                            if (isset(wlm_post_data()['amount1']) && '0.00' == wlm_post_data()['amount1']) {
                                $that->shopping_cart_deactivate();
                            } elseif (isset(wlm_post_data()['mc_amount1']) && '0.00' == wlm_post_data()['mc_amount1']) {
                                $that->shopping_cart_deactivate();
                            } elseif (false === $subscrcancel) { // Default settings.
                                $that->shopping_cart_deactivate();
                            } else {
                                // Lets get the level id first so that we know if the settings is cancelled.
                                if (! isset(wlm_post_data()['wpm_id']) || is_null(wlm_post_data()['wpm_id']) || empty(wlm_post_data()['wpm_id'])) {
                                    // Get the user of this txnid.
                                    $uid = $that->get_user_id_from_txn_id(wlm_post_data()['subscr_id']);
                                    if (! $uid) {
                                        break; // Let stop it!
                                    }
                                    // Get the levels who uses this txnid.
                                    $levels = $that->get_membership_levels_txn_ids($uid, wlm_post_data()['subscr_id']);
                                    if (! is_array($levels) || count($levels) <= 0) {
                                        break; // Let stop it!
                                    }
                                    $levels = array_keys($levels);

                                    // If multiple levels is found using the txnid.
                                    // Lets check the name and amount to get the real level.
                                    // -- needed for levels with child and parent.
                                    $p = $that->get_option('paypalpsproducts');
                                    if (count($p) >= 1 && count($levels) > 1) {
                                        // Lets get the price and name.
                                        $item_name   = isset(wlm_post_data()['item_name']) ? wlm_post_data()['item_name'] : '';
                                        $item_amount = isset(wlm_post_data()['amount3']) ? wlm_post_data()['amount3'] : null;
                                        $item_amount = ( is_null($item_amount) && isset(wlm_post_data()['mc_amount3']) ) ? wlm_post_data()['mc_amount3'] : $item_amount;

                                        // Lets check all products and make sure we process the recurring only.
                                        foreach ($p as $key => $value) {
                                            if ('1' == $value['recurring']) {
                                                // If their name and amount matches, we got our guy.
                                                if ($value['name'] == $item_name && $value['recur_amount'] == $item_amount) {
                                                    wlm_post_data()['wpm_id'] = $value['sku'];
                                                    break; // Lets end the loop (only the loop not the switch)
                                                }
                                            }
                                        }
                                    }
                                    // Still empty? lets use the first level we found.
                                    if (! isset(wlm_post_data()['wpm_id']) || is_null(wlm_post_data()['wpm_id']) || empty(wlm_post_data()['wpm_id'])) {
                                        wlm_post_data()['wpm_id'] = $levels[0];
                                    }
                                }

                                if (isset($subscrcancel[ wlm_post_data()['wpm_id'] ]) && 1 == $subscrcancel[ wlm_post_data()['wpm_id'] ]) {
                                    $that->shopping_cart_deactivate();
                                }
                            }
                            break;
                        case 'recurring_payment':
                        case 'recurring_payment_skipped':
                        case 'recurring_payment_expired':
                        case 'recurring_payment_failed':
                        case 'recurring_payment_suspended':
                            // Set transaction ID.
                            wlm_post_data()['sctxnid'] = wlm_post_data()['recurring_payment_id'];

                            switch (wlm_post_data()['profile_status']) {
                                case 'Active':
                                    // Add hook for Shoppingcart reactivate so that other plugins can hook into this.
                                    wlm_post_data()['is_wlm_sc_rebill'] = true;
                                    wlm_post_data()['paid_amount']      = $raw_data['amount'] ;
                                    do_action_deprecated('wlm_shoppingcart_rebill', [wlm_post_data(true)], '3.10', 'wishlistmember_shoppingcart_rebill');
                                    do_action('wishlistmember_shoppingcart_rebill', wlm_post_data(true));

                                    $that->shopping_cart_reactivate();
                                    break;
                                case 'Suspended':
                                case 'Cancelled':
                                    $that->shopping_cart_deactivate();
                                    break;
                                default:
                                    // Ignore.
                                    break;
                            }
                            break;
                    }
                }
                // We wont need to execute the code below for IPN notifications.
                $that->cart_integration_terminate();
            } //end if


            // 0 Trial offer goes here because it does not return tx id after payment. Also this is used for Delayed IPN.
            /*
             * Still here????
             * Let's check for a transient email address based
             * on the current user's IP address
             *
             * we try 15 times with 1 second interval per try (15 seconds)
             *
             */
            wlm_set_time_limit(120);
            $tries = 10;
            while ($tries--) {
                $email = $that->get_transient_hash();
                if ($email) {
                    $that->delete_transient_hash();
                    $url = $that->get_continue_registration_url($email);
                    header('Location:' . $url);
                    exit;
                }
                usleep(6000000);
            }

            /*
             * Wow!!! Still nothing from Paypal?
             * Final fallback: Let's ask the client for his Paypal email
             * and check if there is an incomplete registration for that
             */

            $fallback_url = $that->get_fallback_registration_url();
            $that->delete_transient_hash();
            header('Location:' . $fallback_url);
            exit;
        }

        public function verify($type, $urls, $url, $req, $pphosts)
        {

            $pphost = (array) $pphost;

            foreach ($pphosts as $pphost) {
                if (! $res) {
                    // Lets us HTTP/1.1.
                    $res = $this->process_verification($type, $urls, $url, $req, $pphost, 'HTTP/1.1');
                    if (! $res) {
                        // Lets us HTTP/1.0.
                        $res = $this->process_verification($type, $urls, $url, $req, $pphost, 'HTTP/1.0');
                    }
                }

                if ($res) {
                    break;
                }
            }

            if (! $res) {
                $res = $this->process_verification($type, $urls, $url, $req);
            }

            return $res;
        }

        public function process_verification($type, $urls, $url, $req, $header_host = '', $http = '')
        {

            if (empty($header_host)) {
                $header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= 'Content-Length: ' . strlen($req) . "\r\n";
            } elseif (! empty($http) && ! empty($header_host)) {
                $header  = "POST /cgi-bin/webscr {$http}\r\n";
                $header .= $header_host;
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= 'Content-Length: ' . strlen($req) . "\r\n";
                $header .= 'User-Agent: WishList Member/' . $this->wlmversion . "\r\n";
                $header .= "Connection: close\r\n";
            }

            if ('PDT' === $type) {
                return $this->verify_pdt($urls, $url, $header, $req);
            } elseif ('IPN' === $type) {
                return $this->verify_ipn($urls, $url, $header, $req);
            }
            return false;
        }

        public function verify_ipn($urls, $url, $header, $req)
        {

            // Let's try ssl first.
            $fp = fsockopen($urls, 443, $errno, $errstr, 30);
            if (! $fp) {
                // Now let's try unsecure.
                $fp = fsockopen($url, 80, $errno, $errstr, 30);
                if (! $fp) {
                    return false;
                }
            }

            $header = $header . "\r\n" . $req;

            fputs($fp, $header);
            while (! feof($fp)) {
                $res = fgets($fp, 1024);
            }

            fclose($fp);
            if (0 != strcmp($res, 'VERIFIED')) {
                return false;
            } else {
                return true;
            }
        }

        public function verify_pdt($urls, $url, $header, $req)
        {

            // Let's try ssl first.
            $fp = fsockopen($urls, 443, $errno, $errstr, 30);
            if (! $fp) {
                // Now let's try unsecure.
                $fp = fsockopen($url, 80, $errno, $errstr, 30);
                if (! $fp) {
                    return false;
                }
            }

            $header = $header . "\r\n" . $req;

            fputs($fp, $header);
            $res        = '';
            $headerdone = false;
            while (! feof($fp)) {
                $line = fgets($fp, 1024);
                if (0 == strcmp($line, "\r\n")) {
                    $headerdone = true;
                } elseif ($headerdone) {
                    $res .= $line;
                }
            }
            $lines = explode("\n", $res);

            /*
             * terminate if PDT verification does not say SUCCESS
             */
            fclose($fp);
            if (0 != strcmp($lines[0], 'SUCCESS')) {
                // New change in Paypal Sandbox put the SUCCESS value in the second key array so we will also check in there.
                if (0 != strcmp($lines[1], 'SUCCESS')) {
                    return false;
                } else {
                    return $lines;
                }
            } else {
                return $lines;
            }
        }
    }

}
