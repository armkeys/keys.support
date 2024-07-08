<?php

/**
 * MailPoet init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_MailPoet_Hooks')) {
    /**
     * WLM3_MailPoet_Hooks class
     */
    class WLM3_MailPoet_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_mailpoet_test_keys', [$this, 'test_keys']);
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

            $personal_access_token = wlm_post_data()['data']['personal_access_token'];
            $save                  = wlm_post_data()['data']['save'];

            $transient_name = 'wlmmailpoet_' . md5($personal_access_token);
            if ($save) {
                $ar = wishlistmember_instance()->get_option('Autoresponders');

                $ar['mailpoet']['personal_access_token'] = $personal_access_token;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            $response = wp_remote_get(
                'https://api.mailpoet.com/lists',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $personal_access_token,
                    ],
                ]
            );

            $body = json_decode(wp_remote_retrieve_body($response));

            if (! empty($body)) {
                $data['status'] = true;
                $data['lists']  = $body;
            } else {
                $data['message'] = 'Invalid Personal Access Token';
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_MailPoet_Hooks();
}
