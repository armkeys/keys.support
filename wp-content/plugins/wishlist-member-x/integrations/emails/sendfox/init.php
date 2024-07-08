<?php

/**
 * SendFox
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_SendFox_Hooks')) {
    /**
     * WLM3_SendFox_Hooks class
     */
    class WLM3_SendFox_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_sendfox_test_keys', [$this, 'test_keys']);
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

            $transient_name = 'wlmsendfox_' . md5($personal_access_token);
            if ($save) {
                $ar = wishlistmember_instance()->get_option('Autoresponders');

                $ar['sendfox']['personal_access_token'] = $personal_access_token;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            $response = wp_remote_get(
                'https://api.sendfox.com/lists',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $personal_access_token,
                    ],
                ]
            );

            $first_body = json_decode(wp_remote_retrieve_body($response));

            if (! empty($first_body)) {
                /*
                 * Sendfox API returns lists ten at a time via paging, this means that if there's 12 lists
                 * it will return the first 10 results and we will have to run a second API call to retrieve
                 * the remaining 2 on the second page.
                 */
                if ($first_body->total > 10) {
                    $merge_lists = $first_body->data;
                    $body_pages  = $first_body->total;
                    $pages       = (int) ( (int) $body_pages / 10 );
                    $remainder   = fmod($body_pages, 10);

                    if ($remainder) {
                        ++$pages;
                    }

                    for ($x = 2; $x <= $pages; $x++) {
                        $response    = wp_remote_get(
                            'https://api.sendfox.com/lists/?page=' . $x,
                            [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $personal_access_token,
                                ],
                            ]
                        );
                        $body        = json_decode(wp_remote_retrieve_body($response));
                        $merge_lists = array_merge($merge_lists, $body->data);
                    }
                    $first_body->data = $merge_lists;
                }

                $data['status'] = true;
                $data['lists']  = $first_body;
            } else {
                $data['message'] = 'Invalid Personal Access Token';
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_SendFox_Hooks();
}
