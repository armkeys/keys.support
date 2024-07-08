<?php

// Integration handler.
if (! class_exists('WLM_INTEGRATION_PLUGNPAID')) {
    /**
     * Plug&Paid Integration Handler
     */
    class WLM_INTEGRATION_PLUGNPAID
    {
        public function __construct()
        {
            add_action('wishlistmember_paymentprovider_handler', [$this, 'process_webhook']);
        }

        /**
         * Action: wishlistmember_paymentprovider_handler
         *
         * @uses WLM_INTEGRATION_PLUGNPAID::get_products
         * @uses WLM_INTEGRATION_PLUGNPAID::get_user
         * @uses WLM_INTEGRATION_PLUGNPAID::add_user_to_levels
         * @uses
         *
         * @param string $scuri The SCURI parsed by WishList Member
         */
        public function process_webhook($scuri)
        {

            // Check that the $scuri is ours.
            if (wishlistmember_instance()->get_option('plugnpaidthankyou') != $scuri) {
                return; // Not ours, pass control back to WishList Member.
            }

            // We only handle webhooks.
            if ('webhook' !== wlm_get_data()['plugnpaid_action' ]) {
                exit; // Terminate, nothing else needs to happen.
            }

            $pdata = json_decode(file_get_contents('php://input'));
            if (! $pdata) {
                exit; // Terminate, nothing else needs to happen.
            }

            // Get configured products.
            $plugnpaid_products = wishlistmember_instance()->get_option('plugnpaid_products');

            // Process the webhook.
            switch (wlm_arrval($pdata, 'type')) {
                // "create/update" types.
                case 'new_simple_sale':
                case 'recurring_subscription_sale':
                case 'mark_paid':
                    // Only create/update the user if order status is paid.
                    if (in_array($pdata->data->order->status, ['paid', 'ordered'])) {
                        // Retrieve product IDs.
                        $products = $this->get_products($pdata);
                        // Match products with levels.
                        $levels = array_unique(array_keys(array_intersect($plugnpaid_products, $products)));
                        // Retrieve the user and add levels to user if match is found.
                        $user = $this->get_user($pdata);
                        if ($user) {
                            $this->add_user_to_levels($user, $levels, $pdata->data->order->id);
                        }
                    }
                    break;

                case 'recurring_subscription_cancelled':
                case 'recurring_subscription_fail':
                case 'order_declined':
                case 'refund':
                    if ($pdata->data->order->id) {
                        wlm_post_data()['sctxnid'] = $pdata->data->order->id;
                        wishlistmember_instance()->shopping_cart_deactivate();
                    }
                    break;
            }

            exit; // Terminate, nothing else needs to happen.
        }

        /**
         * Retrieve products from $pdata
         *
         * @param  object $pdata
         * @return array array of product IDs
         */
        private function get_products($pdata)
        {
            // Return empty array if we can't find $pdata->data->order->products.
            if (empty($pdata->data) || empty($pdata->data->order) || empty($pdata->data->order->products)) {
                return [];
            }

            $products = [];
            // Add product IDs to $products.
            foreach ($pdata->data->order->products as $product) {
                if (empty($product->id)) {
                    continue;
                }
                $products[] = $product->id;
            }

            // Return array of product IDs.
            return $products;
        }

        /**
         * Retrieve user from $pdata
         * If the user's email already exists in the database then return the user's ID
         * If the user's email does not exist then return array of user info
         *
         * @param  object $pdata
         * @return integer|array
         */
        private function get_user($pdata)
        {
            // Return empty array if we can't find $pdata->data->order->customer.
            if (empty($pdata->data) || empty($pdata->data->order) || empty($pdata->data->order->customer)) {
                return false;
            }

            $customer = $pdata->data->order->customer;

            // Check if the email address is already in our database.
            $user = get_user_by('email', $customer->email);
            if (! $user) {
                // New user, create array compatible with wlm_insert_user.
                list($fname, $lname) = explode(' ', $customer->name, 2);
                $user                = [
                    'first_name' => $fname,
                    'last_name'  => $lname,
                    'user_email' => $customer->email,
                    'user_login' => $customer->email,
                    'user_pass'  => wlm_generate_password(),
                ];
            } else {
                // Existing user, we just need the ID.
                $user = $user->ID;
            }

            return $user;
        }

        /**
         * Adds user to levels
         *
         * @uses wlmapi_add_member
         * @uses wlmapi_update_member
         * @uses WishListMemberPluginMethods::ShoppingCartReactivate
         *
         * @param integer|array $user   If int then $levels are added to the user. If not, then create the user and add levels to newly created user
         * @param array         $levels Array of membership levels
         * @param string        $txnid  Transaction ID
         */
        private function add_user_to_levels($user, $levels, $txnid)
        {

            $memlevels = [];
            // Populate $memlevels if user already exists.
            if (is_int($user)) {
                $memlevels = wishlistmember_instance()->get_membership_levels($user, false, true);
            }

            // Remove duplicate levels and set transaction ID for each level.
            $levels = array_unique($levels);
            foreach ($levels as &$level) {
                $level = in_array($level, $memlevels) ? false : [$level, $txnid];
            }
            unset($level);

            // Cleanup. remove "false" entries.
            $levels = ['Levels' => array_diff($levels, [false])];

            // Check if $user is array or not. Array means we create the user, otherwise update.
            if (is_array($user)) {
                // Create the user and add the levels.
                wlmapi_add_member($user + ['SendMailPerLevel' => 1] + $levels);
            } else {
                // Update the user with new levels.
                wlmapi_update_member($user, ['SendMailPerLevel' => 1] + $levels);
            }

            // Call legacy ShoppingCartReactivate for backwards compatibility.
            global $wlm_no_cartintegrationterminate;
            $old                             = $wlm_no_cartintegrationterminate;
            $wlm_no_cartintegrationterminate = true;
            wlm_post_data()['sctxnid']                = $txnid;
            wishlistmember_instance()->shopping_cart_reactivate();
            $wlm_no_cartintegrationterminate = $old;
        }
    }

    // Initialize everything.
    new WLM_INTEGRATION_PLUGNPAID();
}
