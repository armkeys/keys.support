<?php

/**
 * ConvertKit API init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_ConvertKit_Hooks')) {
    /**
     * WLM3_ConvertKit_Hooks class
     */
    class WLM3_ConvertKit_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_convertkit_test_keys', [$this, 'test_keys']);
        }
        /**
         * Test API keys
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
            ];

            $ckapi = wlm_post_data()['data']['ckapi'];
            $save  = wlm_post_data()['data']['save'];

            $transient_name = 'wlmckapi_' . md5($ckapi);
            if ($save) {
                $ar                        = wishlistmember_instance()->get_option('Autoresponders');
                $ar['convertkit']['ckapi'] = $ckapi;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_die(wp_json_encode($transient_result));
                }
            }

            // Connect and get lists.
            $ck = \WishListMember\Autoresponders\ConvertKit::_interface()->cksdk($ckapi);
            if (! empty($ck->last_error)) {
                $data['message'] = $ck->last_error;
            } else {
                $f = $ck->get_forms();
                $t = $ck->get_tags();
                if (false === $f || false === $t) {
                    $data['message'] = $ck->last_error;
                } else {
                    $data['status'] = true;
                    $data['lists']  = [];
                    if (isset($f['forms']) && is_array($f['forms'])) {
                        foreach ($f['forms'] as $value) {
                            $data['lists'][ $value['id'] ] = [
                                'value' => $value['id'],
                                'text'  => $value['name'],
                            ];
                        }
                    }
                    $data['tags'] = [];
                    if (isset($t['tags']) && is_array($t['tags'])) {
                        foreach ($t['tags'] as $value) {
                            $data['tags'][ $value['id'] ] = [
                                'value' => $value['id'],
                                'text'  => $value['name'],
                            ];
                        }
                    }
                }
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_ConvertKit_Hooks();
}
