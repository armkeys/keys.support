<?php

/**
 * 2CheckOutAPI init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_2CheckOutAPI_Hooks')) {
    /**
     * WLM3_2CheckOutAPI_Hooks class
     */
    class WLM3_2CheckOutAPI_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_twoco-api_test_keys', [$this, 'test_keys']);
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

            $save                           = wlm_post_data()['data']['save'];
            $twocheckoutapisettings         = wlm_post_data()['data']['twocheckoutapisettings'];
            $twocheckoutapi_seller_id       = wlm_arrval($twocheckoutapisettings, 'twocheckoutapi_seller_id');
            $twocheckoutapi_publishable_key = wlm_arrval($twocheckoutapisettings, 'twocheckoutapi_publishable_key');
            $twocheckoutapi_private_key     = wlm_arrval($twocheckoutapisettings, 'twocheckoutapi_private_key');
            $twocheckoutapi_sandbox         = wlm_arrval($twocheckoutapisettings, 'twocheckoutapi_sandbox');

            $transient_name = 'twocoapi_' . md5(wp_json_encode($twocheckoutapisettings));
            if ($save) {
                $settings = wishlistmember_instance()->get_option('twocheckoutapisettings');
                wishlistmember_instance()->save_option('twocheckoutapisettings', array_merge((array) $settings, $twocheckoutapisettings));
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }
            if (! empty($twocheckoutapi_seller_id) && ! empty($twocheckoutapi_publishable_key) && ! empty($twocheckoutapi_private_key)) {
                try {
                    $url = sprintf('https://%s.2checkout.com/checkout/api/1/%s/rs/authService', empty($twocheckoutapi_sandbox) ? 'www' : 'sandbox', $twocheckoutapi_seller_id);

                    $postdata = [
                        'sellerId'   => $twocheckoutapi_seller_id,
                        'privateKey' => $twocheckoutapi_private_key,
                        'token'      => '-',
                    ];

                    $result = wp_remote_post(
                        $url,
                        [
                            'headers' => [
                                'Content-Type' => 'application/json',
                            ],
                            'body'    => wp_json_encode($postdata),
                            'method'  => 'POST',
                        ]
                    );

                    $result = json_decode($result['body']);

                    if (is_object($result) && isset($result->exception) && is_object($result->exception)) {
                        $code = wlm_arrval($result->exception, 'errorCode');
                        if (empty($code) || 300 === (int) $code) {
                            $data['message'] = 'Unauthorized.';
                        } else {
                            $data['status']  = true;
                            $data['message'] = 'Connected';
                        }
                    } else {
                        $data['message'] = 'Connection Failed.';
                    }
                } catch (\Exception $e) {
                    $data['message'] = $e->getMessage();
                }
            } else {
                $data['message'] = 'Disconnected.';
            }
            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_2CheckOutAPI_Hooks();
}
