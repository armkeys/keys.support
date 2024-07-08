<?php

/**
 * Recurly init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_Recurly_Hooks')) {
    /**
     * WLM3_Recurly_Hooks class
     */
    class WLM3_Recurly_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_recurly_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => 'Disconnected',
            ];

            $recurlyapikey = wlm_post_data()['data']['recurlyapikey'];
            $save          = wlm_post_data()['data']['save'];

            $transient_name = 'recurly_' . md5(wp_json_encode($recurlyapikey));
            if ($save) {
                wishlistmember_instance()->save_option('recurlyapikey', $recurlyapikey);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            if (! empty($recurlyapikey)) {
                try {
                    require_once wishlistmember_instance()->plugin_dir . '/extlib/WP_RecurlyClient.php';
                    $recurly = new WP_RecurlyClient($recurlyapikey);

                    $recurly->request();
                    $error = $recurly->last_error();
                    if ($error) {
                        $data['message'] = $error['message'];
                    } else {
                        $plans = $recurly->get_plans();
                        foreach ($plans as &$plan) {
                            $plan = [
                                'text'  => $plan['name'],
                                'value' => $plan['plan_code'],
                                'id'    => $plan['plan_code'],
                            ];
                        }
                        unset($plan);
                        array_unshift(
                            $plans,
                            [
                                'text'  => '',
                                'value' => '',
                            ]
                        );
                        $data['plans'] = $plans;

                        $data['message'] = 'Connected';
                        $data['status']  = true;
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
    new WLM3_Recurly_Hooks();
}
