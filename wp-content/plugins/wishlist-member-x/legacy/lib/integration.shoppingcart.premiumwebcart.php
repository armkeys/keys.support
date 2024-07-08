<?php

/*
 * CloudNet360 Integration Functions
 * Original Author : Glen Barnhardt, Mike Lopez, and Daniel Walrous
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_PREMIUMWEBCART';
// $__optionname__ = 'pwcthankyou';
// $__methodname__ = 'PremiumWebCartSC';
if (!class_exists('WLM_INTEGRATION_PREMIUMWEBCART')) {

    class WLM_INTEGRATION_PREMIUMWEBCART
    {
        public function PremiumWebCartSC($that)
        {
            /**
             * This method expects the following POST data
             * cmd = CREATE / ACTIVATE / DEACTIVATE
             * hash = hash - md5 of cmd + __ + secret key + __ + post data minus the hash key merged with | in uppercase
             * lastname = client's lastname
             * firstname = client's firstname
             * email = client's email address
             * level = membership level
             * transaction_id = transaction ID.  has to be the same for all related transactions
             *
             * OPTIONAL DATA are:
             * company, address1, address2, city, state, zip, country, phone, fax
             */
            // We accept both GET and POST for this interface.
            if (wlm_get_data()['cmd']) {
                $_POST = array_merge(wlm_get_data(true), wlm_post_data(true));
            }

            if (wlm_get_data()['oid']) {
                $oid = wlm_get_data()['oid'];
            }

            // Prepare data.
            $data = wlm_post_data(true);

            // Populate the data from Premium WebCart.
            unset($data['WishListMemberAction']);
            extract($data);
            unset($data['hash']);

            // Look for the return from Premium cart via the thank you page.
            if (!empty($oid)) {
                $secret     = $that->get_option('genericsecret');
                $merchantid = $that->get_option('pwcmerchantid');
                $apikey     = $that->get_option('pwcapikey');

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.secureinfossl.com/api/getOrderInfo.html');
                curl_setopt($ch, CURLOPT_POST, 1);
                $request = 'merchantid=' . urlencode($merchantid)
                        . '&signature=' . urlencode($apikey)
                        . '&orderid=' . urlencode($oid);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    die('Error in connecting with merchant system.');
                    exit;
                } else {
                    curl_close($ch);
                }

                $parser = new simpleXMLElement($response);
                foreach ($parser as $row) {
                    $email = $row->customer->email;
                }
                header('Location: ' . $that->get_continue_registration_url($email));
                exit;
            }

            // Valid Transaction Types.
            $valid_transaction_types = ['onetime', 'recurring', 'failed', 'cancelled', 'refund'];

            // Check for valid Transaction Types.
            if (!in_array($transaction_type, $valid_transaction_types)) {
                die('Invalid transaction type');
                exit;
            }

            // Set the command that is needed.
            switch ($transaction_type) {
                case 'onetime':
                    $cmd = 'CREATE';
                    break;
                case 'recurring':
                    $transaction_id = ( null == $subscription_id ) ? $transaction_id : $subscription_id;
                    $cmd            = 'ACTIVATE';
                    break;
                case 'refund':
                    $cmd = 'DEACTIVATE';
                    break;
                case 'cancelled':
                    // Added for cancellation.
                    $transaction_id = ( null == $subscription_id ) ? $transaction_id : $subscription_id;
                    $cmd            = 'DEACTIVATE';
                    break;
                case 'failed':
                    $cmd = 'DEACTIVATE';
            }

            // Valid commands.
            $commands = ['CREATE', 'DEACTIVATE', 'ACTIVATE'];
            // Secret key.
            $secret = $that->get_option('pwcsecret');
            // Hash.
            $myhash = md5($cmd . '__' . $secret . '__' . strtoupper(implode('|', $data)));

            // PWC has it's own hashing routine which we check below so we fudge our hash here.
            $wlmhash = md5($cmd . '__' . $secret . '__' . strtoupper(implode('|', $data)));

            // Check PWC Hash for Security.
            $apikey = $that->get_option('pwcapikey');

            $hashstring       = $transaction_type . $product_sku . $customer_email;
            $len              = strlen($apikey);
            $saltedhashstring = substr($apikey, 0, round($len / 2)) . $hashstring . substr($apikey, round($len / 2), $len);
            $securityhash     = md5($saltedhashstring);

            if ($hash != $securityhash) {
                die('Invalid hash. Possible hacking attempt logged.');
                exit;
            } else {
                $hash          = $wlmhash;
                wlm_post_data()['hash'] = $hash;
            }

            // Additional POST data for our system to work.
            wlm_post_data()['action']    = 'wpm_register';
            wlm_post_data()['wpm_id']    = $product_sku;
            wlm_post_data()['username']  = $customer_email;
            wlm_post_data()['password1'] = $that->pass_gen(null, true);
            wlm_post_data()['password2'] = wlm_post_data()['password1'];
            wlm_post_data()['sctxnid']   = $transaction_id;
            wlm_post_data()['firstname'] = $customer_first_name;
            wlm_post_data()['lastname']  = $customer_last_name;
            wlm_post_data()['email']     = $customer_email;

            // Save address (originally for kunaki)
            $address                  = [];
            $address['company']       = $shipping_company_name;
            $address['address1']      = $billing_address_line1;
            $address['address2']      = $billing_address_line2;
            $address['city']          = $billing_customer_city;
            $address['state']         = $billing_customer_state;
            $address['zip']           = $billing_customer_zip;
            $address['country']       = $billing_customer_country;
            $address['phone']         = $phone;
            $address['fax']           = $fax;
            wlm_post_data()['wpm_useraddress'] = $address;

            $wpm_levels = $that->get_option('wpm_levels');

            if ('CREATE' === $cmd) {
                if (!isset($wpm_levels[$level]) && !$that->is_ppp_level($level)) {
                    die("ERROR\nINVALID SKU");
                }
            }
            if ($hash == $myhash && in_array($cmd, $commands)) {
                switch ($cmd) {
                    case 'CREATE':
                        $that->shopping_cart_registration();
                        break;
                    case 'DEACTIVATE':
                        $that->shopping_cart_deactivate();
                        break;
                    case 'ACTIVATE':
                        $that->shopping_cart_reactivate();

                        // Add hook for Shoppingcart reactivate so that other plugins can hook into this.
                        wlm_post_data()['sc_type'] = 'pwc';
                        do_action_deprecated('wlm_shoppingcart_rebill', [wlm_post_data(true)], '3.10', 'wishlistmember_shoppingcart_rebill');
                        do_action('wishlistmember_shoppingcart_rebill', wlm_post_data(true));

                        break;
                }
            }
            die('ERROR');
        }
    }

}
