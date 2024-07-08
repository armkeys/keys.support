<?php

/**
 * Sendlane init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_Sendlane_Hooks')) {
    /**
     * WLM3_Sendlane_Hooks init
     */
    class WLM3_Sendlane_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_sendlane_test_keys', [$this, 'test_keys']);
        }
        /**
         * Test keys
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
                'lists'   => [],
                'tags'    => [],
            ];

            $ar = wishlistmember_instance()->get_option('Autoresponders');

            $api_key   = wlm_post_data()['data']['api_key'];
            $save      = wlm_post_data()['data']['save'];
            $api_hash  = wlm_post_data()['data']['api_hash'];
            $subdomain = wlm_post_data()['data']['subdomain'];

            $transient_name = 'wlmsendlane_' . md5($api_key);

            if ($save) {
                $ar['sendlane']['api_key']   = $api_key;
                $ar['sendlane']['api_hash']  = $api_hash;
                $ar['sendlane']['subdomain'] = $subdomain;
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
                $api = \WishListMember\Autoresponders\SendLane::_interface()->api();

                if ($api) {
                    $lists_ret = $api->post('lists');
                    if (isset($lists_ret['error'])) {
                        $data['message'] = implode(',', $lists_ret['error']);
                        $data['status']  = false;
                        $api             = false;
                    }
                }

                if ($api) {
                    $record_limit = 20;
                    // Get the list.
                    $ret   = [1];
                    $start = 1;
					while ( count( $ret ) > 0 ) { //phpcs:ignore.
                        $ret = $api->post(
                            'lists',
                            [
                                'start' => $start,
                                'limit' => $record_limit,
                            ]
                        );
                        $ret = isset($ret['error']) ? [] : $ret;
                        if (count($ret)) {
                            foreach ($ret as $value) {
                                $data['lists'][ $value['list_id'] ] = $value['list_name'];
                            }
                            // If result is less than $record_limit, no more records left.
                            if (count($ret) < $record_limit) {
                                $ret = [];
                            }
                        }
                        ++$start;
                    }

                    // Get the tags.
                    $ret   = [1];
                    $start = 1;
					while ( count( $ret ) > 0 ) { //phpcs:ignore.
                        $ret = $api->post(
                            'tags',
                            [
                                'start' => $start,
                                'limit' => $record_limit,
                            ]
                        );
                        $ret = isset($ret['error']) ? [] : $ret;
                        if (count($ret)) {
                            foreach ($ret as $value) {
                                $data['tags'][ $value['tag_id'] ] = $value['tag_name'];
                            }
                            // If result is less than $record_limit, no more records left.
                            if (count($ret) < $record_limit) {
                                $ret = [];
                            }
                        }
                        ++$start;
                    }

                    $data['status'] = true;
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            set_transient($transient_name, $data, 60 * 15);
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_Sendlane_Hooks();
}
