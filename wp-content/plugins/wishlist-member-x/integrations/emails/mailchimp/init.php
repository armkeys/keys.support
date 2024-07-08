<?php

/**
 * MailChimp
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_MailChimp_Hooks')) {
    /**
     * WLM3_MailChimp_Hooks class
     */
    class WLM3_MailChimp_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_mailchimp_test_keys', [$this, 'test_keys']);
            add_action('wp_ajax_wlm3_mailchimp_get_list_groups', [$this, 'get_list_groups']);
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
            ];

            $mcapi = wlm_post_data()['data']['mcapi'];
            $save  = wlm_post_data()['data']['save'];
            $optin = wlm_post_data()['data']['optin'];

            $transient_name = 'wlmmchmp_' . md5($mcapi);
            $ar             = wishlistmember_instance()->get_option('Autoresponders');
            if ($save) {
                $ar['mailchimp']['mcapi'] = $mcapi;
                $ar['mailchimp']['optin'] = $optin;
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
            $lists = [];
            $mc    = \WishListMember\Autoresponders\MailChimp::_interface()->api($mcapi);

            if ($mc->get_last_error()) {
                $data['message'] = $mc->get_last_error();
            } else {
                $data['status'] = true;
                $rec_count      = 100;
                $offset         = 0;
                do {
                    $lists = $mc->get(
                        'lists',
                        [
                            'count'  => $rec_count,
                            'offset' => $offset * $rec_count,
                        ]
                    );
                    if (! $mc->get_last_error()) {
                        $data['lists'] = array_merge($data['lists'], $lists['lists']);
                        ++$offset;
                    } else {
                        $lists           = false;
                        $data['status']  = false;
                        $data['message'] = $mc->get_last_error();
                    }
                } while (isset($lists['lists']) && $lists['lists']);
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }

        /**
         * Get list groups.
         */
        public function get_list_groups()
        {
            $mcapi   = wlm_post_data()['data']['mcapi'];
            $list_id = wlm_post_data()['data']['list_id'];
            $groups  = \WishListMember\Autoresponders\MailChimp::_interface()->mc_get_lists_groups($mcapi, $list_id);
            wp_send_json(['groups' => $groups]);
        }
    }
    new WLM3_MailChimp_Hooks();
}
