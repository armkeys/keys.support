<?php

/**
 * Eway init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_Eway_Hooks')) {
    /**
     * WLM3_Eway_Hooks class
     */
    class WLM3_Eway_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_eway_test_keys', [$this, 'test_keys']);
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

            $save         = wlm_post_data()['data']['save'];
            $ewaysettings = wlm_post_data()['data']['ewaysettings'];

            $eway_customer_id = wlm_arrval($ewaysettings, 'eway_customer_id');
            $eway_username    = wlm_arrval($ewaysettings, 'eway_username');
            $eway_password    = wlm_arrval($ewaysettings, 'eway_password');
            $eway_sandbox     = wlm_arrval($ewaysettings, 'eway_sandbox');

            $transient_name = 'eway_' . md5(wp_json_encode($ewaysettings));
            if ($save) {
                $settings = (array) wishlistmember_instance()->get_option('ewaysettings');
                wishlistmember_instance()->save_option('ewaysettings', array_merge($settings, $ewaysettings));
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            if (! empty($eway_customer_id) && ! empty($eway_username) && ! empty($eway_password)) {
                try {
                    require_once wishlistmember_instance()->plugin_dir . '/extlib/eway/Eway24WebserviceClient.php';

                    $svc    = new Eway24WebserviceClient($eway_customer_id, $eway_username, $eway_password, ! empty($eway_sandbox));
                    $params = [
                        'ewayCustomerInvoiceRef' => '138433888562',
                    ];
                    $res    = $svc->call('Transaction24HourReportByInvoiceReference', $params);

                    $result = $res['Transaction24HourReportByInvoiceReferenceResult'];
                    if (empty($result['ewayTrxnStatus'])) {
                        if (! empty($result['ewayTrxnError'])) {
                            $data['message'] = $result['ewayTrxnError'];
                        }
                    } else {
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
    new WLM3_Eway_Hooks();
}
