<?php

/*
 * InfusionSoft Payment Provider Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */
// This line is already set on integration.shoppingcarts.php.
// $__classname__ = 'WLM_INTEGRATION_INFUSIONSOFT';
// $__optionname__ = 'isthankyou';
// $__methodname__ = 'InfusionSoft';
if (! class_exists('WLM_INTEGRATION_INFUSIONSOFT')) {

    class WLM_INTEGRATION_INFUSIONSOFT
    {
        private $wlm          = null;
        private $machine_name = '';
        private $api_key      = '';
        private $ifsdk        = null;
        private $log          = false;
        private $debug        = false;
        private $force        = false;
        private $invmarker    = 'InfusionSoft';

        public function __construct()
        {
            global $WishListMemberInstance;

            // Make sure that WLM active and infusiosnsoft connection is set.
            // WLM_Infusionsoft file is included in init file of this integration.
            if (! isset($WishListMemberInstance) || ! class_exists('WLM_Infusionsoft')) {
                return;
            }

            $this->wlm          = $WishListMemberInstance;
            $this->machine_name = $this->wlm->get_option('ismachine');
            $this->api_key      = $this->wlm->get_option('isapikey');
            $this->log          = $this->wlm->get_option('isenable_log');
            $this->machine_name = $this->machine_name ? $this->machine_name : '';
            $this->api_key      = $this->api_key ? $this->api_key : '';
            // Check if debugging is ON.
            $this->debug = isset(wlm_get_data()['debug']) ? true : false;
            $this->force = isset(wlm_get_data()['force']) ? true : false;

            $apilogfile = false;
            if ($this->log) {
                $date_now   = wlm_date('m-d-Y');
                $apilogfile = $this->wlm->plugin_dir . "/ifs_logs_{$date_now}.csv";
            }

            if ($this->api_key && $this->machine_name) {
                $this->ifsdk = new WLM_Infusionsoft($this->machine_name, $this->api_key, $apilogfile);
            }
        }

        // This is the function that is being called by the Thank You URL.
        public function infusionsoft($that)
        {
            if (! $this->ifsdk || ! $this->ifsdk->is_api_connected()) {
                if ($this->debug) {
                    esc_html_e('Unable to establish Infusionsoft API connection. Please check your Infusionsoft App Name and API Key.', 'wishlist-member');
                    die();
                } else {
                    return false;
                }
            }

            $action = isset(wlm_get_data()['iscron']) ? wlm_get_data()['iscron'] : '';
            $action = '1' == $action ? 'iscron' : '';
            $action = isset(wlm_post_data()['contactId']) ? 'http-post' : $action;

            switch ($action) {
                case 'http-post':
                    $this->process_http_post();
                    break;
                case 'iscron':
                    $this->process_cron();
                    break;
                default:
                    $this->process_registration();
                    break;
            }
        }

        private function process_http_post()
        {
            $contactid    = wlm_post_data()['contactId'];
            $add_level    = isset(wlm_post_data()['add']) ? wlm_post_data()['add'] : false;
            $remove_level = isset(wlm_post_data()['remove']) ? wlm_post_data()['remove'] : false;
            $cancel_level = isset(wlm_post_data()['cancel']) ? wlm_post_data()['cancel'] : false;
            $debug        = isset(wlm_get_data()['debug']) ? true : false;

            // If none of these are present, we stop.
            if (! $add_level && ! $remove_level && ! $cancel_level) {
                if ($debug) {
                    echo 'No action found. <br />';
                }
                exit;
            }
            // Check if contact exist in infusionsoft.
            $contact = $this->ifsdk->get_contact_details($contactid);
            if (! $contact) {
                if ($this->debug) {
                    esc_html_e('No Contact found.', 'wishlist-member');
                }
                die();
            }
            usleep(1000000);
            $uname      = isset(wlm_post_data()['WLMUserName']) && '' != wlm_post_data()['WLMUserName'] ? wlm_post_data()['WLMUserName'] : $contact['Email'];
            $pword      = isset(wlm_post_data()['WLMPassWord']) && '' != wlm_post_data()['WLMPassWord'] ? wlm_post_data()['WLMPassWord'] : $this->wlm->pass_gen();
            $regemail   = isset(wlm_post_data()['WLMRegEmail']) && 'no' === strtolower(wlm_post_data()['WLMRegEmail']) ? false : true;
            $sequential = isset(wlm_post_data()['WLMSequential']) && 'no' === strtolower(wlm_post_data()['WLMSequential']) ? false : true;
            // First we get check if this user exist using txnid.
            $wpm_user = $this->wlm->get_user_id_from_txn_id("IFContact-{$contactid}");
            $new_user = false;

            // If not, check if it exist using the email address.
            if (! $wpm_user) {
                if ($this->debug) {
                    esc_html_e('No User associated with this Contact.', 'wishlist-member') . '<br />';
                    esc_html_e('Checking for contact email if matches found on user.', 'wishlist-member') . '<br />';
                }

                if (function_exists('get_user_by')) {
                    $wpm_user = get_user_by('email', $contact['Email']);
                    $wpm_user = $wpm_user ? $wpm_user->ID : false;
                } else {
                    $wpm_user = email_exists($contact['Email']);
                }
            }

            // If not, check if it exist using the username.
            if (! $wpm_user) {
                if ($this->debug) {
                    esc_html_e('Checking for username if matches found on username.', 'wishlist-member') . '<br />';
                }
                if (function_exists('get_user_by')) {
                    $wpm_user = get_user_by('login', $uname);
                    $wpm_user = $wpm_user ? $wpm_user->ID : $wpm_user;
                }
            }

            // If the user does not exist yet and its adding to level.
            // Lets create a new user using api.
            if (! $wpm_user && $add_level) {
                if ($this->debug) {
                    esc_html_e('No user found. Creating user. (Available if add is present)', 'wishlist-member') . '<br />';
                }

                // If username was not specified, lets create using level setting format.
                if (! isset(wlm_post_data()['WLMUserName']) || empty(wlm_post_data()['WLMUserName'])) {
                    if ($this->debug) {
                        echo 'Generating username!<br />';
                    }
                    // Make sure that the function exists.
                    if (function_exists('wlm_generate_username')) {
                        if ($this->debug) {
                            echo '...Checking Level Settings if username format is specified.<br />';
                        }
                        $lvls = array_intersect(\WishListMember\Level::get_all_levels(), explode(',', $add_level));
                        foreach ($lvls as $lvl) {
                            $lvl = new \WishListMember\Level($lvl);
                            if ($lvl->autocreate_account_enable) {
                                $registration_level = $lvl;
                                break;
                            }
                        }
                    }
                    if ($registration_level && $registration_level->ID && $registration_level->autocreate_account_enable) {
                        if ($this->debug) {
                            echo '...Generating username using Level Setting format.<br />';
                        }
                        $userdata = [
                            'email'      => $contact['Email'],
                            'first_name' => $contact['FirstName'],
                            'last_name'  => $contact['LastName'],
                        ];

                        // Grab the username format from level settings.
                        $username_format = wlm_or(wlm_trim($registration_level->autocreate_account_username), $WishListMemberInstance->level_defaults['autocreate_account_username']);
                        $uname           = wlm_generate_username($userdata, $username_format);
                        if (false === $uname) {
                            if ($this->debug) {
                                echo '...**Unable to generate using Level Setting format.**<br />';
                            }
                            $uname = '';
                        }
                    }
                    // Still empty?
                    if (empty($uname)) {
                        if ($this->debug) {
                            echo '...Using Contact Username or Email for username.<br />';
                        }
                        $uname = isset($contact['Username']) && ! empty($contact['Username']) ? wlm_trim($contact['Username']) : $uname;
                        $uname = empty($uname) ? $contact['Email'] : $uname;
                    }
                }

                // Prepare data.
                $data                 = [];
                $data['last_name']    = $contact['LastName'];
                $data['first_name']   = $contact['FirstName'];
                $data['user_login']   = $uname;
                $data['user_email']   = $contact['Email'];
                $data['user_pass']    = $pword;
                $data['display_name'] = "{$contact['FirstName']} {$contact['LastName']}";
                $data['Sequential']   = $sequential;
                $address['address1']  = $contact['StreetAddress1'];
                $address['address2']  = $contact['StreetAddress2'];
                $address['city']      = $contact['City'];
                $address['state']     = $contact['State'];
                $address['zip']       = $contact['PostalCode'];
                $address['country']   = $contact['Country'];
                $data['SendMail']     = $regemail;
                $data['Levels']       = explode(',', $add_level); // Add the level here.
                $wpm_errmsg           = '';

                if (function_exists('wlmapi_add_member')) {
                    if ($debug) {
                        echo 'Adding using WLM internal function.<br />';
                    }
                    $ret = wlmapi_add_member($data);
                } else {
                    if ($debug) {
                        echo 'Adding sing WLM API Call.<br />';
                    }
                    $wlm_api_key                 = $this->wlm->get_option('WLMAPIKey');
                    $wlm_site_url                = home_url('/');
                    $wlm_apiclass                = new wlmapiclass($wlm_site_url, $wlm_api_key);
                    $wlm_apiclass->return_format = 'php';
                    $ret                         = unserialize($wlm_apiclass->post('/members', $data));
                }

                if ($ret['success'] && isset($ret['member'][0]['ID'])) {
                    $wpm_user = $ret['member'][0]['ID'];
                } else {
                    if ($this->debug) {
                        esc_html_e(' Adding User Failed. Returns the following:', 'wishlist-member');
                    }
                }

                if ($this->debug) {
                    echo '<pre>';
                    var_dump($ret);
                    echo '</pre><br />';
                }
                $new_user = true; // This is new user.
            }

            // Assign infusiom contact id if none is assigned to this user.
            if ($wpm_user) {
                $ifcontact = $this->wlm->Get_UserMeta($wpm_user, 'wlminfusionsoft_contactid');
                if (! $ifcontact) {
                    if ($this->debug) {
                        esc_html_e('Updating Contact ID for user.', 'wishlist-member') . '<br />';
                    }
                    $this->wlm->Update_UserMeta($wpm_user, 'wlminfusionsoft_contactid', $contactid);
                }
            }

            $current_user_mlevels = $this->wlm->get_membership_levels($wpm_user);
            $wpm_levels           = $this->wlm->get_option('wpm_levels');

            if ($this->debug) {
                esc_html_e('Performing operations. Please wait..', 'wishlist-member') . '<br />';
            }

            // Add.
            if ($wpm_user && $add_level) {
                $user_mlevels  = $current_user_mlevels;
                $add_level_arr = explode(',', $add_level);
                if (in_array('all', $add_level_arr)) {
                    $add_level_arr = array_merge($add_level_arr, array_keys($wpm_levels));
                    $add_level_arr = array_unique($add_level_arr);
                }
                if (! $new_user) {
                    if ($this->debug) {
                        esc_html_e('Adding Levels.', 'wishlist-member') . '<br />';
                    }
                    foreach ($add_level_arr as $id => $add_level) {
                        if (isset($wpm_levels[ $add_level ])) { // Check if valid level.
                            if (! in_array($add_level, $user_mlevels)) {
                                $user_mlevels[] = $add_level;
                                $this->wlm->set_membership_levels($wpm_user, $user_mlevels);
                                $this->wlm->set_membership_level_txn_id($wpm_user, $add_level, "IFContact-{$contactid}");
                            } else {
                                // Just uncancel the user.
                                $ret = $this->wlm->level_cancelled($add_level, $wpm_user, false);
                            }
                        } elseif (false !== strrpos($add_level, 'payperpost')) {
                            $this->wlm->set_pay_per_post($wpm_user, $add_level);
                        }
                    }
                    if ($this->debug) {
                        $cnt = count($add_level_arr);
                        // Translators: %d: number of levels added.
                        printf(esc_html__('%d Levels Added.', 'wishlist-member'), esc_html($cnt));
                        echo '<br>';
                    }
                } else {
                    if ($this->debug) {
                        esc_html_e('Updating Level Transaction ID.', 'wishlist-member') . '<br />';
                    }
                    foreach ($add_level_arr as $id => $add_level) {
                        if (isset($wpm_levels[ $add_level ])) { // Check if valid level.
                            $this->wlm->set_membership_level_txn_id($wpm_user, $add_level, "IFContact-{$contactid}");
                        }
                    }
                }
            }

            // Cancel.
            if ($wpm_user && $cancel_level) {
                if ($this->debug) {
                    esc_html_e('Cancelling Levels.', 'wishlist-member') . '<br />';
                }
                $user_mlevels     = $current_user_mlevels;
                $cancel_level_arr = explode(',', $cancel_level);
                if (in_array('all', $cancel_level_arr)) {
                    $cancel_level_arr = array_merge($cancel_level_arr, array_keys($wpm_levels));
                    $cancel_level_arr = array_unique($cancel_level_arr);
                }

                foreach ($cancel_level_arr as $id => $cancel_level) {
                    if (isset($wpm_levels[ $cancel_level ])) { // Check if valid level.
                        if (in_array($cancel_level, $user_mlevels)) {
                            $ret = $this->wlm->level_cancelled($cancel_level, $wpm_user, true);
                        }
                    }
                }

                if ($this->debug) {
                    $cnt = count($cancel_level_arr);
                    // Translators: %d number of levels.
                    printf(esc_html__('%d Levels Cancelled.', 'wishlist-member'), esc_html($cnt));
                    echo '<br>';
                }
            }
            // Remove.
            if ($wpm_user && $remove_level) {
                if ($this->debug) {
                    esc_html_e('Removing Levels.', 'wishlist-member') . '<br />';
                }
                $user_mlevels     = $current_user_mlevels;
                $remove_level_arr = explode(',', $remove_level);
                if (in_array('all', $remove_level_arr)) {
                    $remove_level_arr = array_merge($remove_level_arr, array_keys($wpm_levels));
                    $remove_level_arr = array_unique($remove_level_arr);
                }

                foreach ($remove_level_arr as $id => $remove_level) {
                    $arr_index = array_search($remove_level, $user_mlevels);
                    if (false !== $arr_index) {
                        unset($user_mlevels[ $arr_index ]);
                    } elseif (false !== strrpos($remove_level, 'payperpost')) {
                        list( $marker, $pid ) = explode('-', $remove_level);
                        $post_type            = get_post_type($pid);
                        $this->wlm->remove_post_users($post_type, $pid, $wpm_user);
                    }
                }
                $this->wlm->set_membership_levels($wpm_user, $user_mlevels);

                if ($debug) {
                    echo count($remove_level_arr) . ' Levels Removed.<br />';
                }
            }
            if ($this->debug) {
                esc_html_e('Done.', 'wishlist-member') . '<br />';
            }
            usleep(1000000);
            exit;
        }

        private function process_cron()
        {
            $wlm_infusionsoft_init = new WLM_INTEGRATION_INFUSIONSOFT_INIT();
            $ret                   = $wlm_infusionsoft_init->sync_ifs($this->debug, $this->force);
            $end                   = isset($ret['end']) ? $ret['end'] : '-unknown-';
            $message               = isset($ret['message']) ? $ret['message'] : 'empty';
            $count                 = isset($ret['count']) ? $ret['count'] : 0;
            echo wp_kses_data("<br />{$end} {$message} ({$count} records)");
            die();
        }

        private function process_registration()
        {
            $wlm_infusionsoft_init = new WLM_INTEGRATION_INFUSIONSOFT_INIT();
            // Get the productid to be used for free trial subscriptions, if present.
            $SubscriptionPlanProductId = isset(wlm_get_data()['SubscriptionPlanProductId']) ? wlm_get_data()['SubscriptionPlanProductId'] : false;
            // Get the subscription id, if subscription.
            $SubscriptionId = isset(wlm_get_data()['SubscriptionId']) ? wlm_get_data()['SubscriptionId'] : '00';
            // Determine if FREE TRIALS.
            $isTrial = isset(wlm_get_data()['SubscriptionPlanWait']) ? true : false;

            $job     = false;
            $orderid = '';
            // Now, lets check the orderid if passed.
            if (isset(wlm_get_data()['orderId']) && wlm_get_data()['orderId']) {
                $orderid = (int) wlm_trim(wlm_get_data()['orderId']);
                // Retrieve Job of the OrderID passed.
                $job = $this->ifsdk->get_orderid_job(wlm_get_data()['orderId'], $con, $key);
            }

            // If job(OrderID) does not exist, end.
            if (! $job) {
                if ($this->debug) {
                    // Translators: %s: order ID.
                    printf(esc_html__('Invalid OrderID passed.(%s)', 'wishlist-member'), esc_html($orderid));
                    die();
                } else {
                    return;
                }
            }

            // Get the job's contact details.
            $contactid = $job['ContactId'];
            $contact   = $this->ifsdk->get_contact_details($contactid);
            if (! $contact) {
                if ($this->debug) {
                    // Translators: %s: contact ID.
                    printf(esc_html__('Invalid Contact.(%s)', 'wishlist-member'), esc_html($contactid));
                    die();
                } else {
                    return;
                }
            }

            // Retrieve invoice using our job Id.
            $invoice = $this->ifsdk->get_jobid_invoice($job['Id']);
            if (! $invoice) {
                if ($this->debug) {
                    // Translators: %s: job ID.
                    printf(esc_html__('No Invoice found for this order.(%s)', 'wishlist-member'), esc_html($job['Id']));
                    die();
                } else {
                    return;
                }
            }

            // If its a subscription plan with free trial.
            // Populate the ProductSold field of invoice.
            if ($SubscriptionPlanProductId && $isTrial) {
                $invoice['ProductSold'] = (int) $SubscriptionPlanProductId; // Set the product id to SubscriptionPlanProductId, they have the same value.
            }

            // Set the $invoice Subscription Id.
            $invoice['SubscriptionId'] = $SubscriptionId;

            // Process the invoice and get its status.
            $invoice = $wlm_infusionsoft_init->get_invoice_status($invoice);

            // Fetch Sku for the product of the invoice.
            // Product id is used to search for the sku.
            // We loop through each product sold and break the loop if we find a sku that matches a WishList Member level ID.
            $wpm_levels = $this->wlm->get_option('wpm_levels');
            foreach (explode(',', $invoice['ProductSold']) as $psold) {
                $product = $this->ifsdk->get_product_sku($psold);

                $sku = $product && isset($product['Sku']) ? $product['Sku'] : '';
                $sku = $this->wlm->is_ppp_level($sku) || isset($wpm_levels[ $sku ]) ? $sku : false;
                if ($sku) {
                    if (! $invoice['Sku']) {
                        $invoice['Sku'] = $sku;
                    } else {
                        wlm_post_data()['additional_levels'][] = $sku;
                    }
                }
            }

            // If no product sku then lets end here.
            if (! isset($invoice['Sku']) || '' == $invoice['Sku'] || empty($invoice['Sku'])) {
                if ($this->debug) {
                    // Translators: %s: job ID.
                    printf(esc_html__('Invalid Product SKU.(%s)', 'wishlist-member'), esc_html($job['Id']));
                    die();
                } else {
                    return;
                }
            }

            // If we're active, then good.
            if ('active' !== $invoice['Status']) {
                if ($this->debug) {
                    echo wp_kses_data("Inactive Invoice.({$invoice['Id']})<br />");
                    die();
                } else {
                    return;
                }
            }

            // Prepare data.
            wlm_post_data()['lastname']  = $contact['LastName'];
            wlm_post_data()['firstname'] = $contact['FirstName'];
            wlm_post_data()['action']    = 'wpm_register';
            wlm_post_data()['wpm_id']    = $invoice['Sku'];
            wlm_post_data()['username']  = $contact['Email'];
            wlm_post_data()['email']     = $contact['Email'];
            wlm_post_data()['password1'] = $this->wlm->pass_gen();
            wlm_post_data()['password2'] = wlm_post_data()['password1'];
            wlm_post_data()['sctxnid']   = "{$this->invmarker}-" . $invoice['Id'] . "-{$SubscriptionId}";

            // Prepare the address fields using info from payment provider.
            $address['company']  = $contact['Company'];
            $address['address1'] = $contact['StreetAddress1'];
            $address['address2'] = $contact['StreetAddress2'];
            $address['city']     = $contact['City'];
            $address['state']    = $contact['State'];
            $address['zip']      = $contact['PostalCode'];
            $address['country']  = $contact['Country'];

            wlm_post_data()['wpm_useraddress'] = $address;

            if ($this->debug) {
                echo 'Integration is working fine.<br />';
                echo '<pre>';
                    var_dump(wlm_post_data(true));
                echo '</pre>';
                die();
            }
            // Do registration.
            $this->wlm->shopping_cart_registration();
        }
    }
}
