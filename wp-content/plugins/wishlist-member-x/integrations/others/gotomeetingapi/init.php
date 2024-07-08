<?php

/**
 * GotoMeeting API init
 *
 * @package WishListMember/OtherProviders
 */

if (! class_exists('WLM3_GoToMeetingAPI_Hooks')) {
    /**
     * WLM3_GoToMeetingAPI_Hooks class
     */
    class WLM3_GoToMeetingAPI_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_gotomeetingapi_test_keys', [$this, 'test_keys']);
        }

        /**
         * Test keys
         *
         * @throws \Exception When authorization code is invalid or not provided.
         */
        public function test_keys()
        {
            $data = [
                'status'  => false,
                'message' => '',
            ];

            $save              = wlm_post_data()['data']['save'];
            $gotomeetingapi    = wlm_post_data()['data']['webinar']['gotomeetingapi'];
            $authorizationcode = wlm_arrval($gotomeetingapi, 'authorizationcode');
            $accesstoken       = wlm_arrval($gotomeetingapi, 'accesstoken');
            $organizerkey      = wlm_arrval($gotomeetingapi, 'organizerkey');

            $obj   = new \WishListMember\Webinars\GTMAPI_OAuth_En();
            $oauth = new \WishListMember\Webinars\GTMAPI_OAuth($obj);

            $authorizationcode = wlm_trim($authorizationcode);

            $webinar        = wishlistmember_instance()->get_option('webinar');
            $transient_name = 'wlmgtmapi_' . md5(wp_json_encode(wlm_post_data()['data']));
            $webinar        = wishlistmember_instance()->get_option('webinar');

            if ($save && $webinar['gotomeetingapi']['authorizationcode'] !== $authorizationcode) {
                $webinar['gotomeetingapi']                 = array_merge($webinar['gotomeetingapi'], $gotomeetingapi);
                $webinar['gotomeetingapi']['accesstoken']  = '';
                $webinar['gotomeetingapi']['organizerkey'] = '';
                $webinar['gotomeetingapi']['refreshtoken'] = '';

                wishlistmember_instance()->save_option('webinar', $webinar);
            } else {
                $transient_result = get_transient($transient_name);
                if ($transient_result) {
                    $transient_result['cached'] = 1;
                    wp_send_json($transient_result);
                }

                if ($webinar['gotomeetingapi']) {
                    $authorizationcode = wlm_arrval($webinar, 'gotomeetingapi', 'authorizationcode');
                    $accesstoken       = wlm_arrval($webinar, 'gotomeetingapi', 'accesstoken');
                    $organizerkey      = wlm_arrval($webinar, 'gotomeetingapi', 'organizerkey');
                }
                $save = false;
            }

            try {
                $authorizationcode = wlm_trim($authorizationcode);

                if (empty($authorizationcode)) {
                    throw new \Exception('Authorization Code Required');
                }

                $save = wlm_post_data()['data']['save'];
                if ($save) {
                    $oauth_data = $oauth->getAccessTokenv2($authorizationcode);

                    if (! empty($oauth_data->error)) {
                        throw new \Exception('Invalid Authorization Code');
                    }

                    $webinar['gotomeetingapi']['accesstoken']       = $oauth_data['access_token'];
                    $webinar['gotomeetingapi']['organizerkey']      = $oauth_data['organizer_key'];
                    $webinar['gotomeetingapi']['authorizationcode'] = $authorizationcode;
                    $webinar['gotomeetingapi']['refreshtoken']      = $oauth_data['refresh_token'];

                    wishlistmember_instance()->save_option('webinar', $webinar);
                } else {
                    $gtm_api = new \WishListMember\Webinars\GoToWebinarAPIIntegration();
                    $gtm_api->refreshtoken();

                    $webinar = wishlistmember_instance()->get_option('webinar');
                }
                $authorizationcode = wlm_arrval($webinar, 'gotomeetingapi', 'authorizationcode');
                $accesstoken       = wlm_arrval($webinar, 'gotomeetingapi', 'accesstoken');
                $organizerkey      = wlm_arrval($webinar, 'gotomeetingapi', 'organizerkey');

                $obj->setAccessToken($accesstoken);
                $obj->setOrganizerKey($organizerkey);

                $data['webinars'] = (array) $oauth->getWebinars();
                if ($oauth->hasApiError()) {
                    throw new \Exception('Invalid Authorization Code');
                }

                foreach ($data['webinars'] as &$webinar) {
                    $webinar = [
                        'id'    => sprintf('%s---%s', $webinar->webinarKey, $webinar->subject), //phpcs:ignore WordPress.NamingConventions
                        'value' => sprintf('%s---%s', $webinar->webinarKey, $webinar->subject), //phpcs:ignore WordPress.NamingConventions
                        'name'  => $webinar->subject,
                        'text'  => $webinar->subject,
                    ];
                }
                unset($webinar);
                $data['status'] = true;
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }

            set_transient($transient_name, $data, 60 * 15);
            wp_send_json($data);
        }
    }
    new WLM3_GoToMeetingAPI_Hooks();
}
