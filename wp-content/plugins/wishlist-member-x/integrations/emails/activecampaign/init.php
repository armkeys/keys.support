<?php

/**
 * ActiveCampaign initialization
 *
 * @package WishListMember/AutoResponders
 */

if (! class_exists('WLM3_ActiveCampaign_Hooks')) {
    /**
     * WLM3_ActiveCampaign_Hooks class
     */
    class WLM3_ActiveCampaign_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_activecampaign_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test API Keys
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
            ];

            $api_url = wlm_post_data()['data']['api_url'];
            $api_key = wlm_post_data()['data']['api_key'];

            $transient_name = 'wlmactvcmpn_' . md5($api_url . $api_key);

            if (wlm_post_data()['data']['save']) {
                $ar                              = wishlistmember_instance()->get_option('Autoresponders');
                $ar['activecampaign']['api_url'] = $api_url;
                $ar['activecampaign']['api_key'] = $api_key;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            try {
                require_once __DIR__ . '/lib/sdk.php';
                $api = new \WishListMember\Autoresponders\ActiveCampaign\SDK($api_url, $api_key);

                // Get lists and mirror values of 'name' and 'id' to 'text' and 'value' respectively for select2.
                $lists = wlm_or($api->get_lists(), []);
                $keys  = [];
                foreach ($lists as &$list) {
                    $list->text  = $list->name;
                    $list->value = $list->id;
                    $keys[]      = $list->id;
                }
                unset($list);
                $data['lists'] = array_combine($keys, $lists);

                // Get tags and mirror values of 'name' and 'id' to 'text' and 'value' respectively for select2.
                $tags = wlm_or($api->get_tags(), []);
                $keys = [];
                foreach ($tags as &$tag) {
                    $tag->text  = $tag->name;
                    $tag->value = $tag->name;
                    $keys[]     = $tag->id;
                }
                unset($tag);
                $data['tags'] = array_combine($keys, $tags);

                $data['status']  = true;
                $data['message'] = 'Connected';
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
                $data['lists']   = [];
                $data['tags']    = [];
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_ActiveCampaign_Hooks();
}
