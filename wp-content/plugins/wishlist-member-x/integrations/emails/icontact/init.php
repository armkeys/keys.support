<?php

/**
 * IContact init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_IContact_Hooks')) {
    /**
     * WLM3_IContact_Hooks class
     */
    class WLM3_IContact_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_icontact_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         */
        public function test_keys()
        {
            $data          = [
                'status'  => false,
                'message' => '',
            ];
            $icusername    = wlm_post_data()['data']['icusername'];
            $icapipassword = wlm_post_data()['data']['icapipassword'];
            $icapiid       = wlm_post_data()['data']['icapiid'];
            $save          = wlm_post_data()['data']['save'];

            $transient_name = 'wlmicntct_' . md5($icusername . $icapipassword . $icapiid);
            $ar             = wishlistmember_instance()->get_option('Autoresponders');
            if ($save) {
                $ar['icontact']['icusername']    = $icusername;
                $ar['icontact']['icapipassword'] = $icapipassword;
                $ar['icontact']['icapiid']       = $icapiid;
                wishlistmember_instance()->save_option('Autoresponders', $ar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }
            }

            $headers = [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'API-Version'  => '2.0',
                    'API-AppId'    => $icapiid,
                    'API-Username' => $icusername,
                    'API-Password' => $icapipassword,
                ],
            ];
            $url     = 'https://app.icontact.com/icp/a/';

            $result = json_decode(wp_remote_retrieve_body(wp_remote_get($url, $headers)));
            if (isset($result->errors)) {
                $data['message'] = implode('<br>', $result->errors);
            } elseif (isset($result->accounts)) {
                $acct_id             = $result->accounts[0]->accountId;
                $data['icaccountid'] = $acct_id;

                $ar['icontact']['icaccountid'] = $acct_id;
                wishlistmember_instance()->save_option('Autoresponders', $ar);

                $url     = "https://app.icontact.com/icp/a/{$acct_id}/c";
                $result  = json_decode(wp_remote_retrieve_body(wp_remote_get($url, $headers)));
                $folders = [];
                if (! empty($result->clientfolders)) {
                    foreach ((array) $result->clientfolders as $clientfolder) {
                        $folder         = ['id' => $clientfolder->clientFolderId];
                        $folder['name'] = $clientfolder->clientFolderId;
                        if (! empty($clientfolder->name)) {
                            $folder['name'] = $clientfolder->name;
                        }
                        $folder['text']  = $folder['name'];
                        $folder['value'] = $folder['id'];
                        $folders[]       = $folder;
                    }
                }
                $data['folders'] = $folders;

                if (! ( $ar['icontact']['icfolderid'] + 0 )) {
                    $ar['icontact']['icfolderid'] = $folders[0]['id'];
                    wishlistmember_instance()->save_option('Autoresponders', $ar);
                }

                $folder_id          = $ar['icontact']['icfolderid'];
                $data['icfolderid'] = $folder_id;

                $url    = "https://app.icontact.com/icp/a/{$acct_id}/c/{$folder_id}/lists";
                $result = json_decode(wp_remote_retrieve_body(wp_remote_get($url, $headers)));

                $lists = [];
                if (! empty($result->lists)) {
                    foreach ((array) $result->lists as $list) {
                        $l         = ['id' => $list->listId];
                        $l['name'] = $list->listId;
                        if (! empty($list->name)) {
                            $l['name'] = $list->name;
                        }
                        $l['text']  = $l['name'];
                        $l['value'] = $l['id'];
                        $lists[]    = $l;
                    }
                }

                $data['lists'] = $lists;

                $data['status'] = true;
            } else {
                $data['message'] = 'An unknown error occured. Please double check your API credentials.';
            }
            $data['message'] = str_ireplace('api', 'API', $data['message']);

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_IContact_Hooks();
}
