<?php

/**
 * Constant Contact initialization.
 *
 * @package WishListMember/Autoresponders
 */

// Include the API v3 interface.
require_once __DIR__ . '/wlm-constantcontact-v3.php';

if (! class_exists('WLM3_ConstantContact_Hooks')) {
    /**
     * Constant Contact integration hooks.
     */
    class WLM3_ConstantContact_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_constantcontact_test_keys', [$this, 'test_connection']);
        }

        /**
         * Test integration API connection
         */
        public function test_connection()
        {
            $data = [
                'status'  => false,
                'message' => '',
                'lists'   => [],
                'tags'    => [],
            ];

            $constant_contact_v3 = new \WishListMember\Autoresponders\ConstantContact\API_V3(admin_url());

            $access_token = get_transient('wlm_constantcontact_token');
            if (! $access_token) {
                $constant_contact_v3->refresh_token();
                $access_token = get_transient('wlm_constantcontact_token');
            }

            if (! $access_token) {
                $ar             = ( new \WishListMember\Autoresponder('constantcontact') );
                $data['status'] = false;
                if (empty($ar->settings['error_description'])) {
                    $data['message'] = 'Please authenticate WishList Member Application to access your Constant Contact account';
                } else {
                    $data['message'] = $ar->settings['error_description'];
                }
                wp_die(wp_json_encode($data));
            }

            $res = $constant_contact_v3->get($access_token, 'contact_lists', ['include_count' => false]);
            if ($constant_contact_v3->is_success()) {
                $data['status'] = true;
                $data['lists']  = $res['lists'];
            }

            $res = $constant_contact_v3->get($access_token, 'contact_tags', ['limit' => 500]);
            if ($constant_contact_v3->is_success()) {
                $data['status'] = true;
                $data['tags']   = $res['tags'];
            }

            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_ConstantContact_Hooks();
}
