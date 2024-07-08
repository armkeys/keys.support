<?php

/**
 * Moosend init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_Moosend_Hooks')) {
    /**
     * WLM3_Moosend_Hooks class
     */
    class WLM3_Moosend_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_moosend_test_keys', [$this, 'test_keys']);
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

            $api_url = wlm_post_data()['data']['api_url'];
            $api_key = wlm_post_data()['data']['api_key'];
            $save    = wlm_post_data()['data']['save'];

            $transient_name = 'wlmmoosend_' . md5($api_url . $api_key);
            if ($save) {
                $ar = wishlistmember_instance()->get_option('Autoresponders');

                $ar['moosend']['api_key'] = $api_key;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_die(wp_json_encode($transient_result));
                }
            }

            $response = wp_remote_get(
                sprintf('https://api.moosend.com/v3/lists.json?apikey=%s&WithStatistics=false&PageSize=1000', $api_key)
            );

            $body = json_decode(wp_remote_retrieve_body($response));

            if (! empty($body) && empty($body->Error)) {
                $data['status'] = true;
                $data['lists']  = $body->Context->MailingLists;
            } else {
                $data['message'] = 'Invalid API Key';
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_Moosend_Hooks();
}
