<?php

/**
 * Drip2 init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_Drip2_Hooks')) {
    /**
     * WLM3_Drip2_Hooks class
     */
    class WLM3_Drip2_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_drip2_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         */
        public function test_keys()
        {
            $data = [
                'status'   => false,
                'message'  => '',
                'accounts' => [],
                'tags'     => [],
            ];

            $apitoken = wlm_post_data()['data']['apitoken'];
            $save     = wlm_post_data()['data']['save'];

            $transient_name = 'wlmdrip2_' . md5($apitoken);
            $ar             = wishlistmember_instance()->get_option('Autoresponders');

            if ($save) {
                $ar['drip2']['apitoken'] = $apitoken;
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
                $api = \WishListMember\Autoresponders\Drip2::_interface()->api();

                if ($api) {
                    $accounts = $api->get_accounts();
                    if ($api->get_error_code()) {
                        $data['message'] = $api->get_error_message();
                    } else {
                        foreach ($accounts as $account) {
                            $data['accounts'][ $account['id'] ] = [
                                'value' => $account['id'],
                                'text'  => $account['name'],
                            ];
                        }
                        $data['status'] = true;
                    }

                    if (is_array($data['accounts']) && $data['accounts']) {
                        $selected_account = $ar['drip2']['account'];
                        if (empty($selected_account)) {
                            $selected_account       = $data['accounts'][0]['value'];
                            $ar['drip2']['account'] = $selected_account;
                            wishlistmember_instance()->save_option('Autoresponders', $ar);
                        }
                        foreach ($data['accounts'] as $account) {
                            $tags                              = $api->get_tags(['account_id' => $account['value']]);
                            $data['tags'][ $account['value'] ] = [];
                            foreach ($tags as $tag) {
                                $tag                                 = [
                                    'value' => $tag,
                                    'text'  => $tag,
                                ];
                                $data['tags'][ $account['value'] ][] = $tag;
                            }
                        }
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
    new WLM3_Drip2_Hooks();
}
