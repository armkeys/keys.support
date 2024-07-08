<?php

/*
 * Clickbank Payment Provider Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_JVZOO';
// $__optionname__ = 'jvzoothankyou';
// $__methodname__ = 'JVZoo';
if (! class_exists('WLM_INTEGRATION_JVZOO')) {

    class WLM_INTEGRATION_JVZOO
    {
        public function JVZoo($that)
        {
            $key = $that->get_option('jvzoosecret');

            $jvzooproducts = [];

            $jvzooupsells_ttl = $that->get_option('jvzooupsells_ttl');
            if (empty($jvzooupsells_ttl)) {
                $jvzooupsells_ttl = 60 * 60;
            }

            if ($this->ty_valid_req($key, wlm_get_data(true))) {
                // Check if product ID (item) is in cbproducts.
                // If so, return the level for that product ID.
                // If not, use wlm_get_data()['sku']
                $postedid   = wlm_get_data()['item'];
                $wpm_levels = (array) $that->get_option('wpm_levels');
                foreach ((array) $wpm_levels as $sku => $level) {
                    if (in_array($postedid, $jvzooproducts[ $sku ])) {
                        $level_id = $sku;
                        break;
                    }
                }

                if (empty($level_id)) {
                    $xposts = $that->get_pay_per_posts(['post_title', 'post_type']);
                    foreach ($xposts as $post_type => $posts) {
                        foreach ($posts as $post) {
                            if (in_array($postedid, $jvzooproducts[ 'payperpost-' . $post->ID ])) {
                                $level_id = 'payperpost-' . $post->ID;
                                break;
                            }
                        }
                    }
                }
                wlm_post_data()['wpm_id'] = empty($level_id) ? wlm_get_data()['sku'] : $level_id;

                $user_id = $that->get_user_id_from_txn_id(wlm_get_data()['cbreceipt']);
                if ($user_id) {
                    if (! $that->is_temp_user($user_id)) {
                        header('Location:' . $that->get_registration_url(wlm_post_data()['wpm_id'], true, $dummy) . '&registered=1');
                        exit;
                    }
                }
                if (! trim(wlm_get_data()['cname'])) {
                    wlm_get_data()['cname'] = 'Firstname Lastname';
                }
                $name                        = explode(' ', wlm_get_data()['cname']);
                wlm_post_data()['lastname']  = array_pop($name);
                wlm_post_data()['firstname'] = implode(' ', $name);
                wlm_post_data()['action']    = 'wpm_register';

                wlm_post_data()['username']  = wlm_get_data()['cemail'];
                wlm_post_data()['email']     = wlm_get_data()['cemail'];
                wlm_post_data()['password1'] = $that->pass_gen(null, true);
                wlm_post_data()['password2'] = wlm_post_data()['password1'];
                wlm_post_data()['sctxnid']   = wlm_get_data()['cbreceipt'];

                /*
                 * send upsells as additional levels
                 */
                $receipt           = empty(wlm_get_data()['cupsellreceipt']) ? wlm_get_data()['cbreceipt'] : wlm_get_data()['cupsellreceipt'];
                $transient_name    = 'jvzoo_upsells_' . $receipt;
                $registered_levels = get_transient($transient_name);
                if (! empty($registered_levels)) {
                    wlm_post_data()['additional_levels'] = $registered_levels;
                }

                $that->shopping_cart_registration();
            } else {
                $post_vars = $this->extract_cb_postvars(wlm_post_data(true));
                error_log('status ' . serialize($this->ipn_verified($key, $post_vars)));
                if ($this->ipn_verified($key, $post_vars)) {
                    // Is this necessary??
                    if ($this->is_v2($post_vars)) {
                        wlm_post_data()['lastname']  = $post_vars['ccustlastname'];
                        wlm_post_data()['firstname'] = $post_vars['ccustfirstname'];
                    } else {
                        if (! wlm_trim($post_vars['ccustname'])) {
                            $post_vars['ccustname'] = 'Firstname Lastname';
                        }
                        $name                        = explode(' ', wlm_request_data()['ccustname']);
                        wlm_post_data()['lastname']  = array_pop($name);
                        wlm_post_data()['firstname'] = implode(' ', $name);
                    }
                    wlm_post_data()['action'] = 'wpm_register';

                    // The passed sku...
                    parse_str($post_vars['cvendthru'], $passedparams);

                    // Check if product ID (cproditem) is in cbproducts.
                    // If so, return the level for that product ID.
                    // If not, use $passedparams['sku']
                    $postedid   = $post_vars['cproditem'];
                    $wpm_levels = (array) $that->get_option('wpm_levels');
                    foreach ((array) $wpm_levels as $sku => $level) {
                        if (in_array($postedid, $jvzooproducts[ $sku ])) {
                            $level_id = $sku;
                            break;
                        }
                    }

                    if (empty($level_id)) {
                        $xposts = $that->get_pay_per_posts(['post_title', 'post_type']);
                        foreach ($xposts as $post_type => $posts) {
                            foreach ($posts as $post) {
                                if (in_array($postedid, $jvzooproducts[ 'payperpost-' . $post->ID ])) {
                                    $level_id = 'payperpost-' . $post->ID;
                                    break;
                                }
                            }
                        }
                    }
                    wlm_post_data()['wpm_id'] = empty($level_id) ? $passedparams['sku'] : $level_id;

                    wlm_post_data()['username'] = $post_vars['ccustemail'];
                    wlm_post_data()['email']    = $post_vars['ccustemail'];
                    wlm_post_data()['sctxnid']  = $post_vars['ctransreceipt'];

                    switch ($post_vars['ctransaction']) {
                        case 'SALE':
                        case 'TEST_SALE':
                            // We only save upsell info on sale in INS.
                            $receipt           = empty($post_vars['cupsellreceipt']) ? $post_vars['ctransreceipt'] : $post_vars['cupsellreceipt'];
                            $transient_name    = 'jvzoo_upsells_' . $receipt;
                            $registered_levels = get_transient($transient_name);
                            if (empty($registered_levels)) {
                                $registered_levels = [];
                            }

                            $registered_levels[] = wlm_post_data()['wpm_id'] . "\t" . $post_vars['ctransreceipt'];
                            set_transient($transient_name, $registered_levels, $jvzooupsells_ttl);
                            break;
                        case 'BILL': // we do nothing because registration is handled by the regular thank you url...
                        case 'UNCANCEL-REBILL':
                            $txn                       = wlm_post_data()['sctxnid'];
                            $items                     = explode('-', $txn);
                            wlm_post_data()['sctxnid'] = $items[0];

                            // Add hook for Shoppingcart reactivate so that other plugins can hook into this.
                            wlm_post_data()['sc_type'] = 'jvzoo';
                            do_action_deprecated('wlm_shoppingcart_rebill', [wlm_post_data(true)], '3.10', 'wishlistmember_shoppingcart_rebill');
                            do_action('wishlistmember_shoppingcart_rebill', wlm_post_data(true));

                            $that->shopping_cart_reactivate();

                            break;

                        case 'RFND':
                        case 'CGBK':
                        case 'INSF':
                        case 'CANCEL-REBILL':
                        case 'CANCEL-TEST-REBILL':
                            $that->shopping_cart_deactivate();
                            break;
                    }
                }
            }
        }

        public function extract_cb_postvars($post)
        {
            $fields_v4 = [
                'cprodtitle',
                'ctranspaymentmethod',
                'cfuturepayments',
                'ccustzip',
                'ccustshippingzip',
                'ccustemail',
                'crebillfrequency',
                'crebillstatus',
                'ctransaffiliate',
                'cupsellreceipt',
                'corderamount',
                'ccustcounty',
                'ccurrency',
                'ccustfirstname',
                'crebillamnt',
                'ctransaction',
                'ccuststate',
                'corderlanguage',
                'caccountamount',
                'ctid',
                'ccustshippingcountry',
                'cnextpaymentdate',
                'cverify',
                'cprocessedpayments',
                'cnoticeversion',
                'cprodtype',
                'ccustcc',
                'ccustshippingstate',
                'ctransreceipt',
                'ccustfullname',
                'cbf',
                'cbfid',
                'cshippingamount',
                'cvendthru',
                'ctransvendor',
                'ctransrole',
                'ctaxamount',
                'cbfpath',
                'ccustaddr2',
                'ccustaddr1',
                'ccustcity',
                'ccustlastname',
                'ctranstime',
                'cproditem',
            ];
            $fields_v2 = [
                'ccustfullname',
                'ccustfirstname',
                'ccustlastname',
                'ccuststate',
                'ccustzip',
                'ccustcc',
                'ccustaddr1',
                'ccustaddr2',
                'ccustcity',
                'ccustcounty',
                'ccustshippingstate',
                'ccustshippingzip',
                'ccustshippingcountry',
                'ccustemail',
                'cproditem',
                'cprodtitle',
                'cprodtype',
                'ctransaction',
                'ctransaffiliate',
                'caccountamount',
                'corderamount',
                'ctranspaymentmethod',
                'ccurrency',
                'ctranspublisher',
                'ctransreceipt',
                'ctransrole',
                'cupsellreceipt',
                'crebillamnt',
                'cprocessedpayments',
                'cfuturepayments',
                'cnextpaymentdate',
                'crebillstatus',
                'ctid',
                'cvendthru',
                'cverify',
                'ctranstime',
            ];
            sort($fields_v2);
            sort($fields_v4);

            $fields_v1 = [
                'ccustname',
                'ccustemail',
                'ccustcc',
                'ccuststate',
                'ctransreceipt',
                'cproditem',
                'ctransaction',
                'ctransaffiliate',
                'ctranspublisher',
                'cprodtype',
                'cprodtitle',
                'ctranspaymentmethod',
                'ctransamount',
                'caffitid',
                'cvendthru',
                'cverify',
            ];
            // Support physical medias.
            if (false !== strpos($cprodtype, 'PHYSICAL')) {
                array_push($fields_v1, 'ccustaddr1', 'ccustaddrd', 'ccustcity', 'ccustcounty', 'ccustzip');
            }
            $version_fields = [
                1 => $fields_v1,
                2 => $fields_v2,
                4 => $fields_v4,
            ];
            $f              = $this->get_fields_for_version($version_fields, $post);

            $jvzoo_req = [];
            foreach ($f as $k) {
                // Ignore missing fields.
                if (isset($post[ $k ])) {
                    $jvzoo_req[ $k ] = $post[ $k ];
                }
            }
            return $jvzoo_req;
        }

        public function ipn_verified($secret_key, $post_vars)
        {
            $pop        = '';
            $ipn_fields = [];
            foreach ($post_vars as $key => $value) {
                if ('cverify' === $key) {
                    continue;
                }
                $ipn_fields[] = $key;
            }
            // No more field sorting, this assumes that fields.
            // Are already properly sorted.
            foreach ($ipn_fields as $field) {
                $pop = $pop . $post_vars[ $field ] . '|';
            }
            $pop           = $pop . $secret_key;
            $calced_verify = sha1(mb_convert_encoding($pop, 'UTF-8'));
            $calced_verify = strtoupper(substr($calced_verify, 0, 8));
            return $calced_verify == $post_vars['cverify'];
        }

        public function ty_valid_req($secret_key, $get_vars)
        {
            $rcpt     = $get_vars['cbreceipt'];
            $time     = $get_vars['time'];
            $item     = $get_vars['item'];
            $jvzoopop = $get_vars['cbpop'];

            $xxpop = sha1("$secret_key|$rcpt|$time|$item");
            $xxpop = strtoupper(substr($xxpop, 0, 8));
            return $jvzoopop == $xxpop;
        }

        public function is_v2($post_vars = [])
        {
            return isset($post_vars['ccustfullname']);
        }

        public function get_fields_for_version($fields, $post)
        {
            if ('4.0' == $post['cnoticeversion']) {
                return $fields[4];
            }

            if (isset($post['ccustfullname'])) {
                return $fields[2];
            }
            return $fields[1];
        }
    }

}
