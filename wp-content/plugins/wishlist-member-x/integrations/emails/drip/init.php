<?php

/**
 * Drip API init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM_Drip_Api')) {
    require_once wishlistmember_instance()->plugin_dir . '/extlib/wlm_drip/Drip_API.class.php';
}

if (! class_exists('WLM3_Drip_Hooks')) {
    /**
     * WLM3_Drip_Hooks class
     */
    class WLM3_Drip_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {

            add_action('wp_ajax_wlm3_drip_test_keys', [$this, 'test_keys']);
        }
        /**
         * Test API keys
         */
        public function test_keys()
        {
            $data = [
                'status'    => false,
                'message'   => '',
                'campaigns' => [],
            ];

            $apitoken = wlm_post_data()['data']['apitoken'];
            $save     = wlm_post_data()['data']['save'];

            $transient_name = 'wlmdrip_' . md5($apitoken);
            $ar             = wishlistmember_instance()->get_option('Autoresponders');

            if ($save) {
                $ar['drip']['apitoken'] = $apitoken;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
                delete_transient($transient_name);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_die(wp_json_encode($transient_result));
                }
            }

            // Connect and get info.
            try {
                $api = new WLM_Drip_Api($apitoken);

                if ($api) {
                    $accounts = $api->get_accounts();
                    if ($api->get_error_code()) {
                        $data['message'] = $api->get_error_message();
                    } else {
                        foreach ($accounts as $account) {
                            $campaigns = $api->get_campaigns(['account_id' => $account['id']]);
                            foreach ($campaigns as $campaign) {
                                $campaign            = [
                                    'value' => sprintf('%s-%s', $account['id'], $campaign['id']),
                                    'text'  => sprintf('%s - %s', $account['name'], $campaign['name']),
                                ];
                                $data['campaigns'][] = $campaign;
                            }
                        }
                        $data['status'] = true;
                    }
                } else {
                    $data['message'] = 'Invalid API Token';
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            set_transient($transient_name, $data, 60 * 15);
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_Drip_Hooks();
}
