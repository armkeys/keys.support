<?php

/**
 * Automizy init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_Automizy_Hooks')) {
    /**
     * WLM3_Automizy_Hooks class
     */
    class WLM3_Automizy_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_automizy_test_keys', [$this, 'test_keys']);
        }
        /**
         * Test API Keys
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
                'lists'   => [],
                'tags'    => [],
            ];

            $api_key        = wlm_post_data()['data']['api_key'];
            $transient_name = 'wlmautomizy_' . md5($api_key);
            $ar             = wishlistmember_instance()->get_option('Autoresponders');

            if (wlm_post_data()['data']['save']) {
                $ar['automizy']['api_key'] = $api_key;
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
                $api            = \WishListMember\Autoresponders\Automizy::_interface()->api();
                $data['status'] = $api ? true : false;

                if ($api) {
                    $api->get('smart-lists');
                    if (! $api->is_success()) {
                        $data['message'] = $api->get_last_error();
                        $data['status']  = false;
                        $api             = false;
                    }
                }

                if ($api) {
                    $rec_count = 100;
                    $lists     = $api->get(
                        'smart-lists',
                        [
                            'limit'  => $rec_count,
                            'fields' => 'id,name,contactsCount',
                        ]
                    );
                    if ($api->is_success()) {
                        $start  = ceil($lists['totalItems'] / $rec_count);
                        $offset = 1;
                        $lists  = $lists['smartLists'];
                        while ($offset <= $start) {
                            $args   = [
                                'limit'  => $rec_count,
                                'page'   => $offset * $rec_count,
                                'fields' => 'id,name,contactsCount',
                            ];
                            $lists2 = $api->get('smart-lists', $args);
                            if ($lists2) {
                                $lists = array_merge_recursive($lists, $lists2['smartLists']);
                            }
                            ++$offset;
                        }

                        foreach ($lists as $value) {
                            $data['lists'][ $value['id'] ] = $value['name'];
                        }
                    }

                    $rec_count = 100;
                    $tags      = $api->get('contacts/tag-manager', ['limit' => $rec_count]);
                    if ($api->is_success()) {
                        $start  = ceil($tags['totalItems'] / $rec_count);
                        $offset = 1;
                        $tags   = $tags['contactTags'];
                        while ($offset <= $start) {
                            $args  = [
                                'limit' => $rec_count,
                                'page'  => $offset * $rec_count,
                            ];
                            $tags2 = $api->get('contacts/tag-manager', $args);
                            if ($tags2) {
                                $tags = array_merge_recursive($tags, $tags2['contactTags']);
                            }
                            ++$offset;
                        }
                        foreach ($tags as $value) {
                            $data['tags'][ $value['name'] ] = $value['name'];
                        }
                    }

                    if (count($data['lists']) <= 0) {
                        $data['status']  = false;
                        $data['message'] = 'You have no List in your Automizy account';
                    } else {
                        $data['status'] = true;
                    }
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            set_transient($transient_name, $data, 60 * 15);
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_Automizy_Hooks();
}
