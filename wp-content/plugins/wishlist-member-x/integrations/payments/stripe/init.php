<?php

/**
 * Stripe init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_Stripe_Hooks')) {
    /**
     * WLM3_Stripe_Hooks class
     */
    class WLM3_Stripe_Hooks
    {
        const MAX_PLAN_COUNT = 999;
        const MAX_PROD_COUNT = 999;
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_stripe_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
            ];

            // Live keys.
            $stripeapikey         = wlm_arrval(wlm_post_data()['data'], 'stripeapikey');
            $stripepublishablekey = wlm_arrval(wlm_post_data()['data'], 'stripepublishablekey');

            // Test keys.
            $test_stripeapikey         = wlm_arrval(wlm_post_data()['data'], 'test_stripeapikey');
            $test_stripepublishablekey = wlm_arrval(wlm_post_data()['data'], 'test_stripepublishablekey');

            // Test mode.
            $stripetestmode = wlm_trim(wlm_arrval(wlm_post_data()['data'], 'stripetestmode'));

            // True if saving keys.
            $save = (bool) wlm_arrval(wlm_post_data()['data'], 'save');

            // Set API key.
            $api_key = 'yes' === $stripetestmode ? $test_stripeapikey : $stripeapikey;

            if (! empty($api_key)) {
                try {
                    WishListMember\PaymentProviders\Stripe\PHPLib\WLM_Stripe::setApiKey($api_key);
                    $plans     = WishListMember\PaymentProviders\Stripe\PHPLib\Price::all(['count' => self::MAX_PLAN_COUNT]);
                    $_products = WishListMember\PaymentProviders\Stripe\PHPLib\Product::all(['count' => self::MAX_PROD_COUNT]);
                    $products  = [];
                    foreach ($_products->data as $product) {
                        $product_name = $product->name;
                        if (! $product->active) {
                            $product_name = sprintf(
                                '%1$s (%2$s)',
                                $product_name,
                                esc_html_x('archived', 'product status', 'wishlist-member')
                            );
                        }
                        $products[ $product->id ]['name'] = $product_name;
                    }

                    $api_type        = strpos($stripetestmode, 'no') ? 'LIVE' : 'TEST';
                    $data['message'] = $api_type;
                    $data['status']  = true;

                    $data['data']['plan_options'] = [];
                    foreach ($plans->data as $plan) {
                        if ($plan->recurring) {
                            $interval = $plan->recurring->interval;
                            if (1 !== (int) $plan->recurring->interval_count) {
                                $interval = sprintf('%d %ss', $plan->recurring->interval_count, $interval);
                            }
                        } else {
                            $interval = __('One time', 'wishlist-member');
                        }

                        // Let's add the (archived) text on the name of the plan/price incase the user has already archived it.
                        // So we can inform the admin in the front end.
                        $archived_text = ( $plan->active ) ? '' : '(' . __('archived', 'wishlist-member') . ')';
                        $text = sprintf('%s - %s (%s %s / %s ) %s', $products[ $plan->product ][ 'name' ], $plan->nickname ? $plan->nickname : $plan->id, strtoupper($plan->currency), number_format($plan->unit_amount / 100, 2, '.', ','), $interval, $archived_text);
                        // @since 3.6 create optgroup for select2.
                        if (! isset($data['data']['plan_options'][ $plan->product ])) {
                            $data['data']['plan_options'][ $plan->product ] = [
                                'text'     => $products[ $plan->product ]['name'],
                                'children' => [],
                            ];
                        }
                        // @since 3.6 add plans to correct group.
                        $data['data']['plan_options'][ $plan->product ]['children'][] = [
                            'value' => $plan->id,
                            'id'    => $plan->id,
                            'text'  => $text,
                        ];
                    }
                    // @since 3.6 remove keys from optgroup as select2 wants an array.
                    $data['data']['plan_options'] = array_values($data['data']['plan_options']);

                    $data['data']['plans'] = $plans;

                    if ($save) {
                        wishlistmember_instance()->save_option('stripeapikey', $stripeapikey);
                        wishlistmember_instance()->save_option('test_stripeapikey', $test_stripeapikey);
                        wishlistmember_instance()->save_option('stripepublishablekey', $stripepublishablekey);
                        wishlistmember_instance()->save_option('test_stripepublishablekey', $test_stripepublishablekey);
                    }
                } catch (\Exception $e) {
                    $data['message'] = $e->getMessage();
                }
            } else {
                $data['message'] = 'No Stripe Secret Key';
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_Stripe_Hooks();
}
