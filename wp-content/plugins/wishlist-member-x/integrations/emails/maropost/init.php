<?php

/**
 * Maropost init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_Maropost_Hooks')) {
    /**
     * WLM3_Maropost_Hooks class
     */
    class WLM3_Maropost_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_maropost_test_keys', [$this, 'test_keys']);
        }
        /**
         * Test keys
         *
         * @throws \Exception On invalid API credentials.
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => 'Disconnected',
            ];

            $account_id = wlm_post_data()['data']['account_id'];
            $auth_token = wlm_post_data()['data']['auth_token'];
            $save       = wlm_post_data()['data']['save'];

            $transient_name = 'wlmmrpst_' . md5($account_id . $auth_token);
            if ($save) {
                $ar                           = wishlistmember_instance()->get_option('Autoresponders');
                $ar['maropost']['account_id'] = $account_id;
                $ar['maropost']['auth_token'] = $auth_token;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            if ($account_id && $auth_token) {
                try {
                    require_once __DIR__ . '/api.php';
                    $api   = new \WishListMember_Maropost_API($account_id, $auth_token);
                    $lists = $api->get_lists();

                    if (! is_array($lists)) {
                        throw new \Exception('Invalid API Credentials', 1);
                    }
                    foreach ($lists as &$list) {
                        $list->text  = $list->name;
                        $list->value = $list->id;
                    }
                    unset($list);

                    $data['status'] = true;
                    $data['lists']  = $lists;
                } catch (\Exception $e) {
                    $data['message'] = $e->getMessage();
                }
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_Maropost_Hooks();
}
