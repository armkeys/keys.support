<?php

/**
 * Authorize.net ARB init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('AuthnetARB')) {
    include_once wishlistmember_instance()->legacy_wlm_dir . '/extlib/wlm_authorizenet_arb/authnet_arb.php';
}

if (! class_exists('WLM3_ANetARB_Hooks')) {
    /**
     * WLM3_ANetARB_Hooks class
     */
    class WLM3_ANetARB_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_anetarb_test_keys', [$this, 'test_keys']);
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

            $save                = wlm_post_data()['data']['save'];
            $anetarbsettings     = wlm_post_data()['data']['anetarbsettings'];
            $api_login_id        = wlm_arrval($anetarbsettings, 'api_login_id');
            $api_transaction_key = wlm_arrval($anetarbsettings, 'api_transaction_key');
            $sandbox_mode        = wlm_arrval($anetarbsettings, 'sandbox_mode');

            $transient_name = 'anetarb_' . md5(wp_json_encode($anetarbsettings));
            if ($save) {
                wishlistmember_instance()->save_option('anetarbsettings', $anetarbsettings);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }
            if (! empty($api_login_id) && ! empty($api_transaction_key)) {
                try {
                    $test = ! empty($sandbox_mode);

                    $arb = new WLMAuthnet\AuthnetARB($api_login_id, $api_transaction_key, $test);
                    $arb->do_apicall('authenticateTestRequest', []);

                    $data['status']  = true;
                    $data['message'] = 'Connected';
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
    new WLM3_ANetARB_Hooks();
}
