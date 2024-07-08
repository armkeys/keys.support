<?php

/*
 * 1ShoppingCart Payment Provider Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */

// Information below is now loaded in integration.shoppingcarts.php.
// $__classname__ = 'WLM_INTEGRATION_1SHOPPINGCART';
// $__optionname__ = 'scthankyou';
// $__methodname__ = 'OneShoppingCart';
if (! class_exists('WLM_INTEGRATION_1SHOPPINGCART')) {

    class WLM_INTEGRATION_1SHOPPINGCART
    {
        public function OneShoppingCart($that)
        {
            global $wlm_1sc_status_map;
            if (in_array(strtolower(trim(wlm_post_data()['status'])), ['accepted', 'approved', 'authorized', 'pending'])) { // Accept even PENDING, let checkstatus handle it later.
                if (! trim(wlm_post_data()['name'])) {
                    wlm_post_data()['name'] = 'Firstname Lastname';
                }
                $name                        = explode(' ', wlm_post_data()['name']);
                wlm_post_data()['lastname']  = array_pop($name);
                wlm_post_data()['firstname'] = implode(' ', $name);
                wlm_post_data()['action']    = 'wpm_register';
                wlm_post_data()['wpm_id']    = wlm_post_data()['sku1'];
                wlm_post_data()['username']  = wlm_post_data()['email1'];
                $orig_email                  = wlm_post_data()['email1'];
                wlm_post_data()['email']     = wlm_post_data()['email1'];
                wlm_post_data()['password1'] = $that->pass_gen(null, true);
                wlm_post_data()['password2'] = wlm_post_data()['password1'];

                $address             = [];
                $address['company']  = wlm_post_data()['shipCompany'];
                $address['address1'] = wlm_post_data()['shipAddress1'];
                $address['address2'] = wlm_post_data()['shipAddress2'];
                $address['city']     = wlm_post_data()['shipCity'];
                $address['state']    = wlm_post_data()['shipState'];
                $address['zip']      = wlm_post_data()['shipZip'];
                $address['country']  = wlm_post_data()['shipCountry'];

                wlm_post_data()['sctxnid'] = '1SC-' . wlm_post_data()['orderID'];

                wlm_post_data()['wpm_useraddress'] = $address;

                // Cache the order.
                $onescmerchantid = wlm_trim($that->get_option('onescmerchantid'));
                $onescapikey     = wlm_trim($that->get_option('onescapikey'));
                if ($onescmerchantid && $onescapikey) {
                    require_once $that->plugin_dir . '/extlib/OneShopAPI.php';
                    require_once $that->plugin_dir . '/extlib/WLMOneShopAPI.php';
                    $api   = new WLMOneShopAPI($onescmerchantid, $onescapikey, 'https://www.mcssl.com');
                    $order = $api->get_order_by_id(wlm_post_data()['orderID'], true, true);
                }

                // Support 1SC upsells.
                if (wlm_trim($that->get_option('onesc_include_upsells'))) {
                    if (count($order['upsells'])) {
                        // Added this so that we can also put the ORDER ID of the upsell order as TXN ID's on the upsell levels.
                        foreach ($order['upsells'] as $ord) {
                            $order_upsells[] = $ord['sku'] . "\t" . '1SC-' . $ord['id'];
                        }

                        wlm_post_data()['additional_levels'] = $order_upsells;
                    }
                }

                $that->shopping_cart_registration();
            } else {
                // Instant notification.
                $onescmerchantid = wlm_trim($that->get_option('onescmerchantid'));
                $onescapikey     = wlm_trim($that->get_option('onescapikey'));

                if ($onescmerchantid && $onescapikey) {
                    $raw_post_data = file_get_contents('php://input');
                    require_once $that->plugin_dir . '/extlib/OneShopAPI.php';
                    $API = new OneShopAPI($that->get_option('onescmerchantid'), $that->get_option('onescapikey'), 'https://www.mcssl.com');

                    $requestBodyXML = new DOMDocument();

                    if (! empty($raw_post_data)) {
                        if (true == $requestBodyXML->loadXML($raw_post_data)) {
                            $notificationType = $requestBodyXML->documentElement->nodeName;

                            $recurring = false;
                            switch (strtolower($notificationType)) {
                                case 'neworder':
                                    $tokenNode    = $requestBodyXML->getElementsByTagName('Token')->item(0)->nodeValue;
                                    $apiResult    = $API->GetOrderById($tokenNode);
                                    $apiResultXML = new DOMDocument();
                                    if (true == $apiResultXML->loadXML($apiResult)) {
                                        $apiSuccess = $apiResultXML->getElementsByTagName('Response')->item(0)->getAttribute('success');

                                        if ('true' === $apiSuccess) {
                                            $orderXML = &$apiResultXML;
                                            $order_id = $orderXML->getElementsByTagName('OrderId')->item(0)->nodeValue;

                                            $recur_order_id = $orderXML->getElementsByTagName('RecurringOrderId')->item(0)->nodeValue;
                                            // If recurring id has value then skip.
                                            if (is_numeric(( $recur_order_id ))) {
                                                exit;
                                            }

                                            $onescmerchantid = wlm_trim($that->get_option('onescmerchantid'));
                                            $onescapikey     = wlm_trim($that->get_option('onescapikey'));
                                            require_once $that->plugin_dir . '/extlib/OneShopAPI.php';
                                            require_once $that->plugin_dir . '/extlib/WLMOneShopAPI.php';
                                            $api = new WLMOneShopAPI($onescmerchantid, $onescapikey, 'https://www.mcssl.com');

                                            // Get Order details to get the client ID.
                                            $order = $api->get_order_by_id($order_id, true, true);

                                            // Check if the SKU matches any of the levels, if it is then add the orderID to the queue.
                                            $is_sku_valid = false;
                                            $levels       = $that->get_option('wpm_levels');

                                            foreach ($levels as $key => $level) {
                                                if ($key == $order['sku']) {
                                                    $is_sku_valid = true;
                                                }
                                            }

                                            if (! $is_sku_valid) {
                                                exit;
                                            }

                                            $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
                                            $qname                    = '1sc_neworder_' . time();
                                            $data                     = $order_id;
                                            $WishlistAPIQueueInstance->add_queue($qname, $data, 'For Queueing');
                                        }
                                    }
                                    // No need to go on with the rest of the script so just terminate it...
                                    exit;

                                    break;
                                case 'orderstatuschange':
                                    $recurring = false;
                                    $apiResult = $API->GetOrderById($requestBodyXML->getElementsByTagName('Id')->item(0)->nodeValue);
                                    break;
                                case 'recurringorderstatuschange':
                                    $recurring = true;
                                    $apiResult = $API->GetRecurringOrderById($requestBodyXML->getElementsByTagName('Id')->item(0)->nodeValue);
                                    break;

                                default:
                                    // May have other types of notifications in the future.
                                    break;
                            }

                            $apiResultXML = new DOMDocument();
                            if (true == $apiResultXML->loadXML($apiResult)) {
                                // Check if the API returned an error.
                                $apiSuccess = $apiResultXML->getElementsByTagName('Response')->item(0)->getAttribute('success');
                                if ('true' === $apiSuccess) {
                                    $orderXML = &$apiResultXML;

                                    if ($recurring) {
                                        $group  = 'recurring';
                                        $status = strtolower($orderXML->getElementsByTagName('Status')->item(0)->nodeValue);
                                    } else {
                                        $group  = 'onetime';
                                        $status = strtolower($orderXML->getElementsByTagName('OrderStatusType')->item(0)->nodeValue);
                                    }
                                    wlm_post_data()['sctxnid'] = '1SC-' . $orderXML->getElementsByTagName('OrderId')->item(0)->nodeValue;

                                    // Search first if there's a user for the transaction ID..
                                    // If there's none then add -R as our cron adds -R to recurring transaction ID's.
                                    $user = $that->get_user_id_from_txn_id(wlm_post_data()['sctxnid']);
                                    if (! $user) {
                                        wlm_post_data()['sctxnid'] = wlm_post_data()['sctxnid'] . '-R';
                                    }

                                    switch ($wlm_1sc_status_map[ $group ][ $status ]) {
                                        case 'activate':
                                            $that->shopping_cart_reactivate();

                                            if ($recurring) {
                                                // Add hook for Shoppingcart reactivate so that other plugins can hook into this.
                                                wlm_post_data()['sc_type'] = '1ShoppingCart';
                                                do_action_deprecated('wlm_shoppingcart_rebill', [wlm_post_data(true)], '3.10', 'wishlistmember_shoppingcart_rebill');
                                                do_action('wishlistmember_shoppingcart_rebill', wlm_post_data(true));
                                            }
                                            break;
                                        case 'deactivate':
                                            $that->shopping_cart_deactivate();
                                            break;
                                        default:
                                            // Do nothing.
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
