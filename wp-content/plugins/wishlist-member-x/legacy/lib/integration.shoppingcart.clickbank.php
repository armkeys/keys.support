<?php

/*
 * Clickbank Payment Provider Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_CLICKBANK';
// $__optionname__ = 'cbthankyou';
// $__methodname__ = 'ClickBank';
if (! class_exists('WLM_INTEGRATION_CLICKBANK')) {

    class WLM_INTEGRATION_CLICKBANK
    {
        public function __construct()
        {
            add_action('wishlistmember_clickbank_process_order_bumps', [$this, 'process_order_bumps']);
        }

        public function ClickBank($that)
        {
            wlm_post_data()['sc_type'] = 'Clickbank';
            $key                       = $that->get_option('cbsecret');

            $cbproducts = $that->get_option('cbproducts');
            if (empty($cbproducts)) {
                $cbproducts = [];
            }

            $cbupsells_ttl = $that->get_option('cbupsells_ttl');
            if (empty($cbupsells_ttl)) {
                $cbupsells_ttl = 60 * 60;
            }

            /*
             * Fix for upsells showing IR001 error when confirmed pages are off.
             * If the pipe character is found, it contains data with upsells.
             * So, explode the values and use main product's data for registration
             * and set transient for other items as additional levels.
             */
            if (false !== strpos(wlm_get_data()['item'], '|')) {
                $item      = explode('|', wlm_get_data()['item']);
                $cbreceipt = explode('|', wlm_get_data()['cbreceipt']);
                $time      = explode('|', wlm_get_data()['time']);
                $cbpop     = explode('|', wlm_get_data()['cbpop']);

                wlm_get_data()['item']      = $item[0];
                wlm_get_data()['cbreceipt'] = $cbreceipt[0];
                wlm_get_data()['time']      = $time[0];
                wlm_get_data()['cbpop']     = $cbpop[0];

                $transient_name    = 'cb_upsells_' . $cbreceipt[0];
                $count_items       = count($cbreceipt);
                $wpm_levels        = (array) $that->get_option('wpm_levels');
                $registered_levels = get_transient($transient_name);
                if (! is_array($registered_levels)) {
                    $registered_levels = [];
                }

                for ($i = 0; $i < $count_items; $i++) {
                    // Use product ID ($item) to find the levels or post connected to the Clickbank products.
                    $postedid = $item[ $i ];
                    foreach ((array) $wpm_levels as $sku => $level) {
                        if (in_array($postedid, (array) $cbproducts[ $sku ])) {
                            $level_id = $sku;
                            break;
                        }
                    }

                    if (empty($level_id)) {
                        $xposts = $that->get_pay_per_posts(['post_title', 'post_type']);
                        foreach ($xposts as $post_type => $posts) {
                            foreach ($posts as $post) {
                                if (in_array($postedid, (array) $cbproducts[ 'payperpost-' . $post->ID ])) {
                                    $level_id = 'payperpost-' . $post->ID;
                                    break;
                                }
                            }
                        }
                    }
                    $lvlid = empty($level_id) ? wlm_get_data()['sku'] : $level_id;

                    // Use receipt ID ($cbreceipt) as transaction IDs of additional levels.
                    $registered_levels[] = $lvlid . "\t" . $cbreceipt[ $i ];
                }

                set_transient($transient_name, $registered_levels, $cbupsells_ttl);
            }

            $is_valid_reg = $this->ty_valid_req($key, wlm_get_data(true));

            if ($is_valid_reg) {
                // If wlm_get_data()['iv'] is set then the params in the URL probably is decripted.
                // Let's put the items returned from $is_valid_reg to the $_GET variable.
                if (isset(wlm_get_data()['iv'])) {
                    wlm_get_data()['item']      = ( isset($is_valid_reg->item) ? $is_valid_reg->item : wlm_get_data()['item'] );
                    wlm_get_data()['cbreceipt'] = ( isset($is_valid_reg->cbreceipt) ? $is_valid_reg->cbreceipt : wlm_get_data()['cbreceipt'] );
                    wlm_get_data()['time']      = ( isset($is_valid_reg->time) ? $is_valid_reg->time : wlm_get_data()['time'] );
                    wlm_get_data()['cbpop']     = ( isset($is_valid_reg->cbpop) ? $is_valid_reg->cbpop : wlm_get_data()['cbpop'] );
                    wlm_get_data()['cbaffi']    = ( isset($is_valid_reg->cbaffi) ? $is_valid_reg->cbaffi : wlm_get_data()['cbaffi'] );
                    wlm_get_data()['cname']     = ( isset($is_valid_reg->cname) ? $is_valid_reg->cname : wlm_get_data()['cname'] );
                    wlm_get_data()['cemail']    = ( isset($is_valid_reg->cemail) ? $is_valid_reg->cemail : wlm_get_data()['cemail'] );
                    wlm_get_data()['ccountry']  = ( isset($is_valid_reg->ccountry) ? $is_valid_reg->ccountry : wlm_get_data()['ccountry'] );
                    wlm_get_data()['czip']      = ( isset($is_valid_reg->czip) ? $is_valid_reg->czip : wlm_get_data()['czip'] );
                }

                // Check if product ID (item) is in cbproducts.
                // If so, return the level for that product ID.
                // If not, use wlm_get_data()['sku']
                $postedid   = wlm_get_data()['item'];
                $wpm_levels = (array) $that->get_option('wpm_levels');
                foreach ((array) $wpm_levels as $sku => $level) {
                    if (in_array($postedid, (array) $cbproducts[ $sku ])) {
                        $level_id = $sku;
                        break;
                    }
                }

                if (empty($level_id)) {
                    $xposts = $that->get_pay_per_posts(['post_title', 'post_type']);
                    foreach ($xposts as $post_type => $posts) {
                        foreach ($posts as $post) {
                            if (in_array($postedid, (array) $cbproducts[ 'payperpost-' . $post->ID ])) {
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
                if (! wlm_trim(wlm_get_data()['cname'])) {
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
                $transient_name    = 'cb_upsells_' . $receipt;
                $registered_levels = get_transient($transient_name);
                if (! empty($registered_levels)) {
                    wlm_post_data()['additional_levels'] = $registered_levels;
                }

                $that->shopping_cart_registration();
            } else {
                $post_vars = $this->extract_cb_postvars(wlm_post_data(true));

                $ipn_verified = $this->ipn_verified($key, $post_vars);

                // $ipn_verified was false? Try version 6.0.
                if (! $ipn_verified) {
                    // Get JSON from raw body...
                    $message = json_decode(file_get_contents('php://input'));

                    $ipn_verified  = $this->verify_ipn_6($key, $message);
                    $ipn_version_6 = true;
                }

                if ($ipn_verified) {
                    // Is this necessary??
                    if ($this->is_v2($post_vars)) {
                        wlm_post_data()['lastname']  = $post_vars['ccustlastname'];
                        wlm_post_data()['firstname'] = $post_vars['ccustfirstname'];
                    } elseif (6 == $ipn_verified->version || 7 == $ipn_verified->version) {
                        wlm_post_data()['lastname']  = $ipn_verified->customer->billing->lastName;
                        wlm_post_data()['firstname'] = $ipn_verified->customer->billing->firstName;
                        $post_vars['ccustemail']     = $ipn_verified->customer->billing->email;
                        $post_vars['ctransreceipt']  = $ipn_verified->receipt;
                        $post_vars['ctransaction']   = $ipn_verified->transactionType;
                        $post_vars['cproditem']      = $ipn_verified->lineItems[0]->itemNo;
                        $post_vars['cupsellreceipt'] = $ipn_verified->upsell->upsellOriginalReceipt;
                    } else {
                        if (! wlm_trim($post_vars['ccustname'])) {
                            $post_vars['ccustname'] = 'Firstname Lastname';
                        }
                        $name                        = explode(' ', wlm_request_data()['ccustname']);
                        wlm_post_data()['lastname']  = array_pop($name);
                        wlm_post_data()['firstname'] = implode(' ', $name);
                    }
                    wlm_post_data()['action'] = 'wpm_register';

                    $this->order_bumps($ipn_verified, $cbproducts, $cbupsells_ttl);

                    // The passed sku...
                    parse_str($post_vars['cvendthru'], $passedparams);

                    // Check if product ID (cproditem) is in cbproducts.
                    // If so, return the level for that product ID.
                    // If not, use $passedparams['sku']
                    $postedid   = $post_vars['cproditem'];
                    $wpm_levels = (array) $that->get_option('wpm_levels');
                    foreach ((array) $wpm_levels as $sku => $level) {
                        if (in_array($postedid, (array) $cbproducts[ $sku ])) {
                            $level_id = $sku;
                            break;
                        }
                    }

                    if (empty($level_id)) {
                        $xposts = $that->get_pay_per_posts(['post_title', 'post_type']);
                        foreach ($xposts as $post_type => $posts) {
                            foreach ($posts as $post) {
                                if (in_array($postedid, (array) $cbproducts[ 'payperpost-' . $post->ID ])) {
                                    $level_id = 'payperpost-' . $post->ID;
                                    break;
                                }
                            }
                        }
                    }
                    wlm_post_data()['wpm_id'] = empty($level_id) ? $passedparams['sku'] : $level_id;

                    wlm_post_data()['username']  = $post_vars['ccustemail'];
                    wlm_post_data()['email']     = $post_vars['ccustemail'];
                    wlm_post_data()['sctxnid']   = $post_vars['ctransreceipt'];
                    wlm_post_data()['password1'] = $that->pass_gen(null, true);
                    wlm_post_data()['password2'] = wlm_post_data()['password1'];

                    switch ($post_vars['ctransaction']) {
                        case 'SALE':
                        case 'TEST_SALE':
                            // We only save upsell info on sale in INS.
                            $receipt           = empty($post_vars['cupsellreceipt']) ? $post_vars['ctransreceipt'] : $post_vars['cupsellreceipt'];
                            $transient_name    = 'cb_upsells_' . $receipt;
                            $registered_levels = get_transient($transient_name);
                            if (empty($registered_levels)) {
                                $registered_levels = [];
                            }

                            $registered_levels[] = wlm_post_data()['wpm_id'] . "\t" . $post_vars['ctransreceipt'];
                            set_transient($transient_name, $registered_levels, $cbupsells_ttl);

                            // Check if the email is already registered and that the txn_id for the level is already active.
                            $user_data = wlmapi_get_member_by('user_email', wlm_post_data()['email']);
                            $user_id   = $user_data['members']['member'][0]['id'];

                            // THIS IS FOR UPSELLS.
                            // Check if this is an Upsell, if it is then just add the additional levels in the user meta.
                            if (! empty($post_vars['cupsellreceipt'])) {
                                sleep(5);
                                // Check if the originating receipt number already exists on the site, if it is then get.
                                // The user_id of the member who have that receipt as transaction ID and add the upsell to him/her.
                                $user_txn_id = $that->get_user_id_from_txn_id($post_vars['cupsellreceipt']);
                                if ($user_txn_id) {
                                    // Check if the user already completed the registration. $user_data should return false if the email is still in incomplete reg state.
                                    if ($user_data) {
                                        // If user already completed the registration then add the level to the user.
                                        $args = [
                                            'Users'   => [$user_txn_id],
                                            'Pending' => false,
                                            'TxnID'   => $post_vars['ctransreceipt'],
                                        ];
                                        wlmapi_add_member_to_level(wlm_post_data()['wpm_id'], $args);
                                    } else {
                                        // IF user hasn't completed the registration then add the upsell level in the user_meta which will be added once they complete the registration.
                                        $that->Update_UserMeta($user_txn_id, 'additional_levels', $registered_levels);
                                    }
                                } else {
                                    // We create an incomplete reg.
                                    // For upsells.
                                    $user_info    = wlmapi_get_member_by('user_email', wlm_post_data()['email']);
                                    $user_info_id = $user_info['members']['member'][0]['id'];

                                    $member_info    = wlmapi_get_level_member_data(wlm_post_data()['wpm_id'], $user_info_id);
                                    $transaction_id = $member_info['member']['level']->TxnID;
                                    if (wlm_post_data()['sctxnid'] != $transaction_id) {
                                        $that->shopping_cart_registration(true, false);
                                        $that->cart_integration_terminate();
                                    }
                                }
                                die();
                            }

                            // Make sure additional levels are processed.
                            wlm_post_data()['additional_levels'] = $registered_levels;

                            // Means the email already exist on the site.
                            if ($user_data) {
                                // Check if user is already a member of the level.
                                // If the transaction ID doesn't match the clickbank receipt then this is a different purchase.
                                // We create an incomplete reg for this transaction.
                                $member_data = wlmapi_get_level_member_data(wlm_post_data()['wpm_id'], $user_id);
                                $txn_id      = $member_data['member']['level']->TxnID;

                                if (wlm_post_data()['sctxnid'] != $txn_id) {
                                    $that->shopping_cart_registration(true, false);
                                    $that->cart_integration_terminate();
                                }
                            } else {
                                // User doesn't exist yet. Means no incomplete reg has been created.
                                // We create an incomplete reg.
                                $that->shopping_cart_registration(true, false);
                                $that->cart_integration_terminate();
                            }

                            break;
                        case 'BILL': // we do nothing because registration is handled by the regular thank you url...
                        case 'TEST_BILL':
                        case 'UNCANCEL-REBILL':
                            // Add hook for Shoppingcart reactivate so that other plugins can hook into this.
                            wlm_post_data()['cbitem'] = $post_vars['cproditem'];
                            $that->shopping_cart_reactivate();
                            break;

                        case 'REBILL':
                            wlm_post_data()['is_wlm_sc_rebill'] = true;
                            wlm_post_data()['sc_type']          = 'Clickbank';
                            do_action_deprecated('wlm_shoppingcart_rebill', [wlm_post_data(true)], '3.10', 'wishlistmember_shoppingcart_rebill');
                            do_action('wishlistmember_shoppingcart_rebill', wlm_post_data(true));
                            $that->shopping_cart_reactivate();
                        case 'RFND':
                        case 'TEST_RFND':
                        case 'CGBK':
                        case 'INSF':
                            $that->shopping_cart_deactivate();
                            break;

                        case 'CANCEL-REBILL':
                        case 'CANCEL-TEST-REBILL':
                            // If cancel immediately is enabled for the level's cancellation settings then we cancel the level immediately once.
                            // WLM receives the cancel IPN.
                            $subscrcancel = $that->get_option('cb_scrcancel');
                            if ($subscrcancel) {
                                $subscrcancel = wlm_maybe_unserialize($subscrcancel);
                            } else {
                                $subscrcancel = false;
                            }

                            if (isset($subscrcancel[ wlm_post_data()['wpm_id'] ]) && 1 == $subscrcancel[ wlm_post_data()['wpm_id'] ]) {
                                $that->shopping_cart_deactivate();
                            } else {
                                // If Cancel immediately is disabeld then check if EOT is enabled for the level.
                                // If it is then we set a future cancellation,
                                // Else the level won't be cancelled at all.
                                $eotcancel = $that->get_option('cb_eot_cancel');
                                if ($eotcancel) {
                                    $eotcancel = wlm_maybe_unserialize($eotcancel);
                                } else {
                                    $eotcancel = [];
                                }

                                if (isset($eotcancel[ wlm_post_data()['wpm_id'] ]) && 1 == $eotcancel[ wlm_post_data()['wpm_id'] ]) {
                                    $user  = $that->get_user_id_from_txn_id(wlm_post_data()['sctxnid']);
                                    $uids  = [$user];
                                    $level = wlm_post_data()['wpm_id'];

                                    $cancel_date = time();
                                    foreach ($ipn_verified->lineItems as $lineitem) {
                                        $cancel_date = strtotime($lineitem->paymentPlan->nextPaymentDate);
                                    }
                                    $that->schedule_level_deactivation($level, $uids, $cancel_date);
                                }
                            }
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

            $cb_req = [];
            foreach ($f as $k) {
                // Ignore missing fields.
                if (isset($post[ $k ])) {
                    $cb_req[ $k ] = $post[ $k ];
                }
            }
            return $cb_req;
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

        public function verify_ipn_6($key, $message)
        {

            // Pull out the encrypted notification and the initialization vector for.
            // AES/CBC/PKCS5Padding decryption.
            $encrypted = $message->{'notification'};

            $iv = $message->{'iv'};

            // Decrypt the body...
            if (function_exists('openssl_decrypt')) {
                $decrypted = trim(
                    openssl_decrypt(
                        base64_decode($encrypted),
                        'AES-256-CBC',
                        substr(sha1($key), 0, 32),
                        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                        base64_decode($iv)
                    ),
                    "\0..\32"
                );

                $decrypted = utf8_encode(stripslashes($decrypted));
            }

            $order = json_decode($decrypted);

            return $order;
        }

        public function ty_valid_req($secret_key, $get_vars)
        {
            $rcpt  = $get_vars['cbreceipt'];
            $time  = $get_vars['time'];
            $item  = $get_vars['item'];
            $cbpop = $get_vars['cbpop'];

            $xxpop = sha1("$secret_key|$rcpt|$time|$item");
            $xxpop = strtoupper(substr($xxpop, 0, 8));

            if ($cbpop == $xxpop) {
                return true;
            }

            // If $cbpop != $xxpop then let's try to decrypt it in case the client's CB account has enabled encrypting URLs.
            $encrypted = rawurldecode($get_vars['params']);
            $iv        = rawurldecode($get_vars['iv']);

            $decrypted = trim(
                openssl_decrypt(
                    base64_decode($encrypted),
                    'AES-256-CBC',
                    substr(sha1($secret_key), 0, 32),
                    OPENSSL_RAW_DATA,
                    base64_decode($iv)
                ),
                "\0..\32"
            );

            $decrypted = json_decode($decrypted);

            $rcpt  = $decrypted->cbreceipt;
            $time  = $decrypted->time;
            $item  = $decrypted->item;
            $cbpop = $decrypted->cbpop;

            $xxpop = sha1("$secret_key|$rcpt|$time|$item");
            $xxpop = strtoupper(substr($xxpop, 0, 8));

            if ($cbpop == $xxpop) {
                return $decrypted;
            }

            return false;
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

        public function order_bumps($ipn_verified, $cbproducts, $cbupsells_ttl)
        {
            // Handle order bumps.
            $transient_name    = 'cb_upsells_' . $ipn_verified->receipt;
            $registered_levels = wlm_or((array) get_transient($transient_name), []);
            foreach ($ipn_verified->lineItems as $line_item) {
                if ('BUMP' === $line_item->lineItemType) {
                    foreach ($cbproducts as $sku => $prodids) {
                        if (in_array($line_item->itemNo, $prodids)) {
                            wlm_post_data()['cb_bump'] = true;
                            $registered_levels[]       = $sku . "\t" . $ipn_verified->receipt;
                        }
                    }
                }
            }
            set_transient($transient_name, $registered_levels, $cbupsells_ttl);
        }

        /*
         * Process order bumps when account is auto-created
         */
        public function process_order_bumps()
        {
            $user_info         = wlmapi_get_member_by('user_email', wlm_post_data()['email']);
            $user_id           = $user_info['members']['member'][0]['id'];
            $active_levels     = $user_info['member']['member'][0]['active_levels'];
            $additional_levels = wlm_post_data()['additional_levels'];

            foreach ($additional_levels as $level) {
                $level_info = explode("\t", $level);
                $lvl_id     = $level_info[0];
                $lvl_txnid  = $level_info[1];

                // Check if already added to order bump level.
                if (! in_array($lvl_id, $active_levels) && ! empty($lvl_id)) {
                    // Add to level.
                    $args = [
                        'Users'   => [$user_id],
                        'Pending' => false,
                        'TxnID'   => $lvl_txnid,
                    ];
                    wlmapi_add_member_to_level($lvl_id, $args);
                }
            }
        }
    }

}
