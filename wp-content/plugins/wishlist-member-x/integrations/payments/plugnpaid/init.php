<?php

/**
 * PlugNPaid init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_PlugNPaid_Hooks')) {
    /**
     * WLM3_PlugNPaid_Hooks class
     */
    class WLM3_PlugNPaid_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_plugnpaid_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         */
        public function test_keys()
        {
            $data            = [
                'status'  => false,
                'message' => '',
            ];
            $api_url         = wlm_post_data()['data']['api_url'];
            $api_key         = wlm_post_data()['data']['api_key'];
            $plugnpaidapikey = wlm_post_data()['data']['plugnpaidapikey'];
            $save            = wlm_post_data()['data']['save'];

            $transient_name = 'wlm_plugnpaid_' . md5($api_url . $api_key);
            if ($save) {
                wishlistmember_instance()->save_option('plugnpaidapikey', $plugnpaidapikey);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            // Get products from plug&paid.
            $result = Requests::request(
                'https://api.plugnpaid.com/v1/products/list',
                [
                    'Content-Type' => 'application/json',
                    'Referer'      => '', // has to be explicitly set to blank for this to work.
                ],
                wp_json_encode(['token' => $plugnpaidapikey]),
                Requests::POST,
                [
                    'useragent' => 'WishList Member/' . wishlistmember_instance()->version,
                ]
            );

            $response = json_decode($result->body);

            if (! $response || $response->status < 0) {
                $data['message'] = $response->error;
            } else {
                $data['data']['products']         = $response->products;
                $data['data']['products_options'] = [
                    '(empty)' => [
                        'id'   => '',
                        'text' => '',
                    ],
                ];
                foreach ($response->products as $product) {
                    $data['data']['products_options'][ $product->id ] = [
                        'id'   => $product->id,
                        'text' => $product->name,
                    ];
                }
                $data['status'] = 1;
            }

            set_transient($transient_name, $data, 60 * 15);

            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_PlugNPaid_Hooks();
}
