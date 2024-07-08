<?php

/**
 * Infusionsoft AR init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_Infusionsoft_AR_Hooks')) {
    if (! class_exists('\WLM_Infusionsoft')) {
        include_once wishlistmember_instance()->plugin_dir . '/extlib/wlm-infusionsoft.php';
    }

    /**
     * Class WLM3_Infusionsoft_AR_Hooks
     */
    class WLM3_Infusionsoft_AR_Hooks
    {
        /**
         * Key
         *
         * @var string
         */
        public $key = 'infusionsoft';
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_infusionsoft_ar_test_keys', [$this, 'test_keys']);
        }
        /**
         * Test keys
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
            ];

            $iskey   = wlm_post_data()['data']['iskey'];
            $ismname = wlm_post_data()['data']['ismname'];
            $save    = wlm_post_data()['data']['save'];

            if (class_exists('WLM_Infusionsoft')) {
                $transient_name = 'wlmisar_' . md5($iskey . $ismname);
                if ($save) {
                    $ar                          = wishlistmember_instance()->get_option('Autoresponders');
                    $ar[ $this->key ]['iskey']   = $iskey;
                    $ar[ $this->key ]['ismname'] = $ismname;
                    wishlistmember_instance()->save_option('Autoresponders', $ar);
                    // Used by infusionsoft init.
                    wishlistmember_instance()->save_option('auto_ismachine', $ismname);
                    wishlistmember_instance()->save_option('auto_isapikey', $iskey);
                } else {
                    $transient_result = get_transient($transient_name);
                    if ($transient_result) {
                        $transient_result['cached'] = 1;
                        wp_die(wp_json_encode($transient_result));
                    }
                }
                if (! $iskey || ! $ismname) {
                    $x = [];
                    if (! $ismname) {
                        $x[] = 'Machine name';
                    }
                    if (! $iskey) {
                        $x[] = 'Encrypted key';
                    }
                    $data['message'] = implode(' and ', $x) . ' not provided';
                } else {
                    $ifsdk = new WLM_Infusionsoft($ismname, $iskey);
                    if ($ifsdk->is_api_connected()) {
                        $is_tags             = $ifsdk->get_tags();
                        $is_tags_category    = $ifsdk->get_tag_categories();
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
    new WLM3_Infusionsoft_AR_Hooks();
}
