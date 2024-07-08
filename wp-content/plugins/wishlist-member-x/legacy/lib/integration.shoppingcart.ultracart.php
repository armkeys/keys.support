<?php

/*
 * UltraCart Payment Provider Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_ULTRACART';
// $__optionname__ = 'ultracartthankyou';
// $__methodname__ = 'UltraCartSC';
if (! class_exists('WLM_INTEGRATION_ULTRACART')) {

    class WLM_INTEGRATION_ULTRACART
    {
        public function UltraCartSC($that)
        {
            /**
             * This method expects the following POST data
             * cmd = CREATE / ACTIVATE / DEACTIVATE / PING
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

            // Prepare data.
            $data = wlm_post_data(true);
            unset($data['WishListMemberAction']);
            extract($data);
            unset($data['hash']);

            // Valid commands.
            $commands = ['CREATE', 'DEACTIVATE', 'ACTIVATE', 'PING'];
            // Secret key.
            $secret = $that->get_option('ultracartsecret');
            // Hash.
            $myhash = md5($cmd . '__' . $secret . '__' . strtoupper(implode('|', $data)));

            // Additional POST data for our system to work.
            wlm_post_data()['action']    = 'wpm_register';
            wlm_post_data()['wpm_id']    = $level;
            wlm_post_data()['username']  = $email;
            wlm_post_data()['password1'] = $that->pass_gen(null, true);
            wlm_post_data()['password2'] = wlm_post_data()['password1'];
            wlm_post_data()['sctxnid']   = wlm_trim($transaction_id);

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

            $registration_level = new \WishListMember\Level($level);

            if ('CREATE' === $cmd) {
                if (! $registration_level->ID && ! $that->is_ppp_level($level)) {
                    die("ERROR\nINVALID SKU");
                }
            }

            if ('' === wlm_post_data()['sctxnid'] && 'PING' !== $cmd) {
                die("ERROR\nTRANSACTION ID REQUIRED");
            }

            if ($hash == $myhash && in_array($cmd, $commands)) {
                // Add_filter('rewrite_rules_array',array(&$that,'rewrite_rules'));
                // $GLOBALS['wp_rewrite']->flush_rules();
                switch ($cmd) {
                    case 'CREATE':
                        $temp       = 1 === (int) $autocreate ? false : true;
                        $wpm_errmsg = $that->shopping_cart_registration($temp, false);
                        if ($wpm_errmsg) {
                            print( "ERROR\n" );
                            print( wp_kses_data(strtoupper($wpm_errmsg)) );
                        } else {
                            $redirect = $temp ? $that->get_continue_registration_url($email) : $that->get_after_reg_redirect($level);
                            printf("%s\n%s", esc_html($cmd), esc_url($redirect));
                        }
                        exit;
                        break;
                    case 'DEACTIVATE':
                        print( esc_html($cmd) );
                        $that->shopping_cart_deactivate();
                        exit;
                        break;
                    case 'ACTIVATE':
                        print( esc_html($cmd) );
                        $that->shopping_cart_reactivate();
                        exit;
                        break;
                    case 'PING':
                        print( esc_html($cmd) );
                        print( "\nOK" );
                        exit;
                }
            }
            print( "ERROR\n" );
            if ($hash != $myhash) {
                die('INVALID HASH');
            }
            if (! in_array($cmd, $commands)) {
                die('INVALID COMMAND');
            }
            die('UNKNOWN ERROR');
        }
    }

}
