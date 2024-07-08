<?php

/**
 * Infusionsoft init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_Infusionsoft_Hooks')) {
    /**
     * WLM3_Infusionsoft_Hooks class
     */
    class WLM3_Infusionsoft_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_infusionsoft_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         */
        public function test_keys()
        {
            $data      = [
                'status'  => false,
                'message' => '',
            ];
            $save      = wlm_post_data()['data']['save'];
            $isapikey  = wlm_post_data()['data']['isapikey'];
            $ismachine = wlm_post_data()['data']['ismachine'];

            if (class_exists('WLM_Infusionsoft')) {
                if ($save) {
                    wishlistmember_instance()->save_option('isapikey', $isapikey);
                    wishlistmember_instance()->save_option('ismachine', $ismachine);
                } else {
                    $transient_name   = 'wlmis_' . md5($isapikey . $ismachine);
                    $transient_result = get_transient($transient_name);
                    if ($transient_result) {
                        $transient_result['cached'] = 1;
                        wp_die(wp_json_encode($transient_result));
                    }
                }
                if (! $isapikey || ! $ismachine) {
                    $x = [];
                    if (! $ismachine) {
                        $x[] = 'Machine name';
                    }
                    if (! $isapikey) {
                        $x[] = 'Encrypted key';
                    }
                    $data['message'] = implode(' and ', $x) . ' not provided';
                } else {
                    $ifsdk = new WLM_Infusionsoft($ismachine, $isapikey);
                    if ($ifsdk->is_api_connected()) {
                        $is_tags_category    = $ifsdk->get_tag_categories();
                        $is_tags             = $ifsdk->get_tags();
                        $is_tags_category    = (array) $is_tags_category;
                        $is_tags_category[0] = __('- No Category -', 'wishlist-member');
                        asort($is_tags_category);

                        $data['tagscategory'] = $is_tags_category;
                        $data['tags']         = $is_tags;

                        $data['status']  = true;
                        $data['message'] = 'OK';

                        set_transient($transient_name, $data, 60 * 15);
                    } else {
                        $data['message'] = 'WishList Member could not establish a connection to Infusionsoft using the App Name and Encrypted Key that you entered. Please make sure that the information you entered are correct and Infusionsoft is not blocked by your server.';
                    }
                }
            } else {
                $data['message'] = 'WLM_Infusionsoft not found';
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_Infusionsoft_Hooks();
}
