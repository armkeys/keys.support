<?php

/**
 * GetResponse init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_GetResponseAPI_Hooks')) {
    /**
     * WLM3_GetResponseAPI_Hooks class
     */
    class WLM3_GetResponseAPI_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_getresponseAPI_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         */
        public function test_keys()
        {
            $data    = [
                'status'  => false,
                'message' => '',
            ];
            $api_url = wlm_post_data()['data']['api_url'];
            $apikey  = wlm_post_data()['data']['apikey'];
            $save    = wlm_post_data()['data']['save'];

            $transient_name = 'wlmgrapi_' . md5($api_url . $apikey);
            if ($save) {
                $ar                              = wishlistmember_instance()->get_option('Autoresponders');
                $ar['getresponseAPI']['api_url'] = $api_url;
                $ar['getresponseAPI']['apikey']  = $apikey;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_die(wp_json_encode($transient_result));
                }
            }

            if (false !== strpos($api_url, 'api2')) {
                $array   = [$apikey];
                $request = wp_json_encode(
                    [
                        'method' => 'ping',
                        'params' => $array,
                        'id'     => null,
                    ]
                );
                $res     = wp_remote_post(
                    $api_url,
                    [
                        'headers' => ['Content-type' => 'application/json'],
                        'body'    => $request,
                    ]
                );
                if (is_wp_error($res)) {
                    $data['message'] = $res->get_error_message();
                } else {
                    $res = json_decode($res['body']);
                    if ($res->result && ! $res->error) {
                        $data['status']  = true;
                        $data['message'] = $res;
                    } else {
                        $data['message'] = $res->error->message;
                    }
                }
            } else {
                require_once wishlistmember_instance()->plugin_dir . '/extlib/wlm-getresponse-v3.php';
                $api = new WLM_GETRESPONSE_V3($apikey, $api_url);
                $res = $api->ping();
                if (isset($res->accountId)) {
                    $data['status']  = true;
                    $data['message'] = $res;
                    $data['campaigns'] = $api->getCampaigns();
                } else {
                    $err = '';
                    if (isset($res->httpStatus)) {
                        $err .= $res->httpStatus;
                    }
                    if (isset($res->code)) {
                        $err .= ':' . $res->code;
                    }
                    if (isset($res->message)) {
                        $err .= ':' . $res->message;
                    }
                    $data['message'] = $err;
                }
            }
            set_transient($transient_name, $data, 60 * 15);
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_GetResponseAPI_Hooks();
}
