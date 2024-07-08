<?php

/*
 * 2Checkout Payment Provider Integration Functions
 * Original Author : Glen Barnhardt
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_TWOCO';
// $__optionname__ = 'twocothankyou';
// $__methodname__ = 'TwocoSC';
if (! class_exists('WLM_INTEGRATION_TWOCO')) {

    class WLM_INTEGRATION_TWOCO
    {
        public function TwocoSC($that)
        {
            // We accept both GET and POST for this interface.
            if (wlm_get_data()['cmd']) {
                $_POST = array_merge(wlm_get_data(true), wlm_post_data(true));
            }

            // Prepare data.
            // $data = wlm_post_data( true );
            $data = $_REQUEST; // We now use $_REQUEST to support 2CO's header return.
            unset($data['WishListMemberAction']);
            extract($data);
            unset($data['md5_hash']);
            // Grab the message type.
            $cmd = $message_type;
            // Valid commands.
            $commands = ['ORDER_CREATED', 'REFUND_ISSUED', 'RECURRING_INSTALLMENT_SUCCESS', 'RECURRING_INSTALLMENT_FAILED', 'RECURRING_STOPPED', 'RECURRING_RESTARTED'];

            // Secret key.
            $secret = $that->get_option('twocosecret');

            // Vendor id.
            $vendor_id = $that->get_option('twocovendorid');

            // Level.
            if (! empty($item_id_1)) {
                $level = $item_id_1;
            } else {
                $level = $merchant_product_id;
            }

            // Hash md5 ( sale_id + vendor_id + invoice_id + Secret Word )
            if (empty($md5_hash)) {
                // Non INS transactions have a different hash secret word + vendor number + order number + total.
                $md5_hash = $key;
                if ('Y' === $demo && 1 == $that->get_option('twocodemo')) {
                    $myhash = strtoupper(md5($secret . $sid . '1' . $total));
                } else {
                    $myhash = strtoupper(md5($secret . $sid . $order_number . $total));
                }
                $customer_email = $email;
                $sale_id        = $order_number;
                $cmd            = 'ORDER_CREATED';
            } else {
                $myhash = strtoupper(md5($sale_id . $vendor_id . $invoice_id . $secret));
            }

            // Additional POST data for our system to work.
            wlm_post_data()['action']    = 'wpm_register';
            wlm_post_data()['wpm_id']    = $level;
            wlm_post_data()['lastname']  = $customer_last_name ? $customer_last_name : $last_name;
            wlm_post_data()['firstname'] = $customer_first_name ? $customer_first_name : $first_name;
            wlm_post_data()['username']  = $customer_email;
            wlm_post_data()['email']     = $customer_email;
            wlm_post_data()['password1'] = $that->pass_gen(null, true);
            wlm_post_data()['password2'] = wlm_post_data()['password1'];
            wlm_post_data()['sctxnid']   = $sale_id;

            // Save address (originally for kunaki)
            $address                           = [];
            $address['company']                = $company;
            $address['address1']               = $address1;
            $address['address2']               = $address2;
            $address['city']                   = $city;
            $address['state']                  = $state;
            $address['zip']                    = $zip;
            $address['country']                = $country;
            $address['phone']                  = $phone;
            $address['fax']                    = $fax;
            wlm_post_data()['wpm_useraddress'] = $address;

            $wpm_levels = $that->get_option('wpm_levels');

            if ('ORDER_CREATED' === $cmd) {
                if (! isset($wpm_levels[ $level ]) && ! $that->is_ppp_level($level)) {
                    die("ERROR\nINVALID SKU");
                }
            }

            if ('' === wlm_post_data()['sctxnid']) {
                die("ERROR\nSALE ID REQUIRED");
            }

            if ($md5_hash == $myhash && in_array($cmd, $commands)) {
                switch ($cmd) {
                    case 'ORDER_CREATED':
                        $that->shopping_cart_registration();
                        exit;
                        break;
                    case 'REFUND_ISSUED':
                    case 'RECURRING_STOPPED':
                    case 'RECURRING_INSTALLMENT_FAILED':
                        $that->shopping_cart_deactivate();
                        exit;
                        break;
                    case 'RECURRING_RESTARTED':
                    case 'RECURRING_INSTALLMENT_SUCCESS':
                        $that->shopping_cart_reactivate();

                        // Add hook for Shoppingcart reactivate so that other plugins can hook into this.
                        wlm_post_data()['sc_type'] = '2Checkout';
                        do_action_deprecated('wlm_shoppingcart_rebill', [wlm_post_data(true)], '3.10', 'wishlistmember_shoppingcart_rebill');
                        do_action('wishlistmember_shoppingcart_rebill', wlm_post_data(true));

                        exit;
                        break;
                }
            }
            print( "ERROR\n" );
            if ($myhash != $md5_hash) {
                die('INVALID HASH');
            }
            if (! in_array($cmd, $commands)) {
                die('INVALID COMMAND');
            }
            die('UNKNOWN ERROR');
        }
    }

}
