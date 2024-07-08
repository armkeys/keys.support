<?php

/*
 * Authorize.net Payment Provider Integration Functions
 * Original Author : Peter Indiola
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_AuthorizeNet';
// $__optionname__ = 'anthankyou';
// $__methodname__ = 'AuthorizeNet';
if (! class_exists('WLM_INTEGRATION_AuthorizeNet')) {

    class WLM_INTEGRATION_AuthorizeNet
    {
        public function AuthorizeNet($that)
        {

            require_once $that->plugin_dir . '/extlib/anet_sdk/AuthorizeNet.php';
            define('AUTHORIZENET_API_LOGIN_ID', $that->get_option('anloginid'));
            define('AUTHORIZENET_TRANSACTION_KEY', $that->get_option('antransid'));
            define('AUTHORIZENET_SIGNATURE_KEY', $that->get_option('anmd5hash'));
                        $anetsandbox = $that->get_option('anetsandbox');

            $request = new AuthorizeNetTD();
            if ((int) 1 !== (int) $anetsandbox) {
                $request->setSandbox(false);
            }
            $response = $request->getTransactionDetails(wlm_get_data()['x_trans_id']);

            // Check if transaction response, transaction_id and authCode if present.
            if (
                ! isset($response->xml->transaction->responseCode) || ! isset($response->xml->transaction->authCode) ||
                    ! isset($response->xml->transaction->transId)
            ) {
                return;
            }

            // Check if transaction code is approved.
            if (1 != $response->xml->transaction->responseCode) {
                return;
            }

            foreach ($response->xml->transaction->lineItems->lineItem as $lineItem) {
                wlm_post_data()['wpm_id'] = (string) $lineItem->itemId;
            }

            foreach ($response->xml->transaction->billTo as $billTo) {
                wlm_post_data()['lastname']  = (string) $billTo->lastName;
                wlm_post_data()['firstname'] = (string) $billTo->firstName;
                wlm_post_data()['password1'] = $that->pass_gen(null, true);
                wlm_post_data()['password2'] = wlm_post_data()['password1'];
            }

            foreach ($response->xml->transaction->customer as $customer) {
                wlm_post_data()['username'] = (string) $customer->email;
                wlm_post_data()['email']    = (string) $customer->email;
            }

            wlm_post_data()['action']  = 'wpm_register';
            wlm_post_data()['sctxnid'] = (string) $response->xml->transaction->transId;

            // For third party integrations like Easy Affiliates and Monster Insights.
            wlm_post_data()['sc_type']     = 'Authorize.Net - Simple Checkout';
            wlm_post_data()['paid_amount'] = wlm_get_data()['x_amount'];

            // Generate hash for checking with authorize.net submitted hash value.
            $hash   = (string) wlm_get_data()['x_SHA2_Hash'];
            $string = '^' . implode(
                '^',
                [
                    wlm_request_data()['x_trans_id'],
                    wlm_request_data()['x_test_request'],
                    wlm_request_data()['x_response_code'],
                    wlm_request_data()['x_auth_code'],
                    wlm_request_data()['x_cvv2_resp_code'],
                    wlm_request_data()['x_cavv_response'],
                    wlm_request_data()['x_avs_code'],
                    wlm_request_data()['x_method'],
                    wlm_request_data()['x_account_number'],
                    wlm_request_data()['x_amount'],
                    wlm_request_data()['x_company'],
                    wlm_request_data()['x_first_name'],
                    wlm_request_data()['x_last_name'],
                    wlm_request_data()['x_address'],
                    wlm_request_data()['x_city'],
                    wlm_request_data()['x_state'],
                    wlm_request_data()['x_zip'],
                    wlm_request_data()['x_country'],
                    wlm_request_data()['x_phone'],
                    wlm_request_data()['x_fax'],
                    wlm_request_data()['x_email'],
                    wlm_request_data()['x_ship_to_company'],
                    wlm_request_data()['x_ship_to_first_name'],
                    wlm_request_data()['x_ship_to_last_name'],
                    wlm_request_data()['x_ship_to_address'],
                    wlm_request_data()['x_ship_to_city'],
                    wlm_request_data()['x_ship_to_state'],
                    wlm_request_data()['x_ship_to_zip'],
                    wlm_request_data()['x_ship_to_country'],
                    wlm_request_data()['x_invoice_num'],
                ]
            ) . '^';
            $digest = strtoupper(hash_hmac('sha512', $string, pack('H*', AUTHORIZENET_SIGNATURE_KEY)));

            if (function_exists('hash_equals')) {
                $equals = hash_equals($digest, $hash);
            } else {
                $equals = $digest === $hash;
            }

            if ($equals) {
                $that->shopping_cart_registration();
            } else {
                // Check if there's an MD5 hash.
                $x_md5_hash = (string) wlm_get_data()['x_MD5_Hash'];

                if ($x_md5_hash) {
                    $amount         = (string) $response->xml->transaction->authAmount;
                    $transaction_id = (string) $response->xml->transaction->transId;

                    $amount = isset($amount) ? $amount : '0.00';

                    // Generate hash for checking with authorize.net submitted hash value.
                    $generated_hash = strtoupper(md5(AUTHORIZENET_SIGNATURE_KEY . AUTHORIZENET_API_LOGIN_ID . $transaction_id . $amount));

                    // Let's verify is authorize.net and generate hash is valid.
                    if ($x_md5_hash === $generated_hash) {
                        $that->shopping_cart_registration();
                    } else {
                        $that->shopping_cart_deactivate();
                    }
                } else {
                    $that->shopping_cart_deactivate();
                }
            }
        }
    }

}
