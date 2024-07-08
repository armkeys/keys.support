<?php

/**
 * Madmimi init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_MadMimi_Hooks')) {
    /**
     * WLM3_MadMimi_Hooks class
     */
    class WLM3_MadMimi_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_madmimi_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test Keys
         *
         * @throws \Exception On invalid API credentials.
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
            ];

            $username = wlm_post_data()['data']['username'];
            $api_key  = wlm_post_data()['data']['api_key'];
            $save     = wlm_post_data()['data']['save'];

            $transient_name = 'wlmmdmimi_' . md5($username . $api_key);
            if ($save) {
                $ar                        = wishlistmember_instance()->get_option('Autoresponders');
                $ar['madmimi']['username'] = $username;
                $ar['madmimi']['api_key']  = $api_key;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            try {
                require_once wishlistmember_instance()->plugin_dir . '/extlib/madmimi/madmimi.php';
                $api   = new WPMadMimi($username, $api_key);
                $lists = $api->get_lists();

                if (null === $lists) {
                    throw new \Exception(__('Invalid API Credentials', 'wishlist-member'), 1);
                }
                foreach ($lists as &$list) {
                    $list->id    = $list->name;
                    $list->text  = $list->name;
                    $list->value = $list->id;
                }
                unset($list);

                $data['status'] = true;
                $data['lists']  = $lists;
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_MadMimi_Hooks();
}
