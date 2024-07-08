<?php

/**
 * AWeber API init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_AWeberAPI_Hooks')) {
    /**
     * WLM3_AWeberAPI_Hooks class
     */
    class WLM3_AWeberAPI_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_aweberapi_test_keys', [$this, 'test_keys']);
            add_action('admin_init', [$this, 'save_key_callback']);
        }

        /**
         * Connect to API
         *
         * @param  string $auth_key Auth key.
         * @return array
         */
        public function connect($auth_key)
        {
            $integration   = \WishListMember\Autoresponders\AweberAPI::_interface();
            $curl_exists   = function_exists('curl_init');
            $access_tokens = ['', ''];

            $data = wishlistmember_instance()->get_option('Autoresponders');
            $msg  = 'Disconnected';

            $result = false;
            $lists  = [];

            // If curl is disabled, don't run Aweber API connection and return error msg.
            if ($curl_exists) {
                // Try Connecting and if there's an error, catch it so that the page doesn't go blank.
                try {
                    $access_tokens = $integration->get_access_tokens();
                    if (! empty($access_tokens)) {
                        $connected = true;
                    }

                    /*
                     * !connected but we have an auth key
                     * let's try to connect one last time
                     */
                    if (! $connected && ! empty($auth_key)) {
                        $access_tokens = $integration->renew_access_tokens();
                        if (! empty($access_tokens)) {
                            // Save the new access tokens.
                            $data['aweberapi']['access_tokens'] = $access_tokens;
                            $connected                          = true;
                            $result                             = true;
                        } else {
                            $access_tokens = ['', ''];
                            $msg           = __('Unable to connect to your Aweber account. Please check and make sure that the Authorization Key is correct.', 'wishlist-member');
                        }
                        wishlistmember_instance()->save_option('Autoresponders', $data);
                    }

                    if ($connected) {
                        $lists = $integration->get_lists();
                        // Reformat.
                        $list_tmp = [];
                        foreach ($lists as $item) {
                            $list_tmp[ $item['id'] ] = $item;
                        }
                        $lists  = $list_tmp;
                        $result = true;
                        $msg    = '';
                    }
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                }
            } else {
                $msg = __('Aweber API integration needs the cURL PHP extension to be enabled for it to work. Please contact your host and have them enable it on your server  to continue integrating with AWeber API.', 'wishlist-member');
            }

            return [
                'status'  => $result,
                'message' => $msg,
                'lists'   => $lists,
                'data'    => $data['aweberapi'],
            ];
        }
        /**
         * Test API Keys
         *
         * @param  array   $data   Data.
         * @param  boolean $return True to return data.
         * @return array
         */
        public function test_keys($data = null, $return = false)
        {
            if (! is_array($data)) {
                $data = wlm_post_data()[ 'data' ];
            }
            $auth_key = wlm_arrval($data, 'auth_key');
            $remove   = wlm_arrval($data, 'remove');
            $save     = wlm_arrval($data, 'save');
            $data     = [
                'status'  => false,
                'message' => 'Disconnected',
            ];

            $transient_name = 'wlmawbrapi_' . md5($auth_key);

            if ($remove) {
                $ar                          = wishlistmember_instance()->get_option('Autoresponders');
                $ar['aweberapi']['auth_key'] = $auth_key;
                unset($ar['aweberapi']['auth_key']);
                unset($ar['aweberapi']['access_tokens']);
                wishlistmember_instance()->save_option('Autoresponders', $ar);
                delete_transient($transient_name);
                wp_die(wp_json_encode($data));
            }

            if ($save) {
                $ar                          = wishlistmember_instance()->get_option('Autoresponders');
                $ar['aweberapi']['auth_key'] = $auth_key;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_die(wp_json_encode($transient_result));
                }
            }

            $data = $this->connect($auth_key);

            if ($data['status']) {
                $data['data']['connected_auth_key'] = sprintf(
                    '%s|%s',
                    preg_replace('/\|$/', '', $data['data']['auth_key']),
                    implode('|', $data['data']['access_tokens'])
                );
                $data['data']['auth_key']           = $data['data']['connected_auth_key'];
            }

            set_transient($transient_name, $data, 60 * 15);
            if ($return) {
                return $data;
            }
            wp_die(wp_json_encode($data));
        }

        /**
         * Tests and saves the keys.
         */
        public function save_key_callback()
        {
            $aweberapi_connect  = wlm_get_data()[ 'aweberapi_connect' ];
            $authorization_code = wlm_get_data()[ 'authorization_code' ];
            if (! empty($aweberapi_connect) && ! empty($authorization_code)) {
                $data = [
                    'save'     => 1,
                    'auth_key' => $authorization_code,
                ];
                $this->test_keys($data, true);
                $url = remove_query_arg(['aweberapi_connect', 'authorization_code']) . '#aweberapi';
                wp_safe_redirect($url);
                exit;
            }
        }
    }
    new WLM3_AWeberAPI_Hooks();
}
