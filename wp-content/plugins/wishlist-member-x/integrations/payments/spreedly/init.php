<?php

/**
 * Spreedly init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_Spreedly_Hooks')) {
    /**
     * WLM3_Spreedly_Hooks class
     */
    class WLM3_Spreedly_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            require_once wishlistmember_instance()->plugin_dir . '/extlib/class.spreedly.inc';
            add_action('wp_ajax_wlm3_spreedly_test_keys', [$this, 'test_keys']);
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

            $spreedlyname  = wlm_post_data()['data']['spreedlyname'];
            $spreedlytoken = wlm_post_data()['data']['spreedlytoken'];
            $save          = wlm_post_data()['data']['save'];

            $transient_name = 'spreedly_' . md5(wp_json_encode(wlm_post_data(true)));
            if ($save) {
                wishlistmember_instance()->save_option('spreedlyname', $spreedlyname);
                wishlistmember_instance()->save_option('spreedlytoken', $spreedlytoken);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }
            if (! empty($spreedlyname) && ! empty($spreedlytoken)) {
                try {
                    Spreedly::configure($spreedlyname, $spreedlytoken);
                    $r = SpreedlySubscriptionPlan::get_all();
                    if (isset($r['ErrorCode'])) {
                        if (401 === (int) $r['ErrorCode']) {
                            $data['message'] = 'Invalid Pin Payments API Credentials';
                        } else {
                            $data['message'] = $r['Response'];
                        }
                    } else {
                        $data['status']        = true;
                        $data['message']       = 'Connected';
                        $data['subscriptions'] = $r;
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
    new WLM3_Spreedly_Hooks();
}
