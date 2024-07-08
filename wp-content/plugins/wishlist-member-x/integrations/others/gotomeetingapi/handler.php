<?php

namespace WishListMember\Webinars;

const GOTO_WEBINAR_API_KEY    = 'd2cb66902668ea5bb4ddc15f052f3b66';
const GOTO_WEBINAR_API_SECRET = '7ecdc56095c1980c';

class GoToWebinarAPIIntegration
{
    public $slug = 'gotomeetingapi';

    public function __construct()
    {
        // Hook to our subscribe function.
        add_action('wishlistmember_webinar_subscribe', [$this, 'subscribe']);

        // Refresh token every day.
        add_action('wishlistmember_gtm_refreshtoken', [$this, 'refreshtoken']);
        if (! wp_next_scheduled('wishlistmember_gtm_refreshtoken_cron')) {
            wp_schedule_event(time(), 'daily', 'wishlistmember_gtm_refreshtoken_cron');
        }
    }

    /**
     * Action: wishlistmember_webinar_subscribe
     * Subscribes a user to a webinar via the GTM API
     *
     * @param array $data
     */
    public function subscribe($data)
    {
        $obj   = new GTMAPI_OAuth_En();
        $oauth = new GTMAPI_OAuth($obj);

        if (is_object($obj) && is_object($oauth)) {
            $vars['firstName'] = $data['first_name'];
            $vars['lastName']  = $data['last_name'];
            $vars['email']     = $data['email'];

            // Get settings.
            $webinars = wishlistmember_instance()->get_option('webinar');
            $settings = $webinars[ $this->slug ];

            $webinar4 = explode('---', (string) $settings[ $data['level'] ]);

            if (empty($settings)) {
                return;
            }

            $obj->setOrganizerKey($settings['organizerkey']);
            $obj->setAccessToken($settings['accesstoken']);
            $oauth->setWebinarId($webinar4[0]);
            $oauth->setRegistrantInfo($vars);

            $oauth->createRegistrant();

            if ($oauth->hasApiError()) {
                // This means that the user wasn't added to the webinar, probably because the Access TOken expired (expires in 60 minutes)
                // Let's refresh the token.
                $this->refreshtoken();
                // Let's try again subscribing the user.
                $webinars = wishlistmember_instance()->get_option('webinar');
                $settings = $webinars[ $this->slug ];

                $webinar4 = explode('---', $settings[ $data['level'] ]);

                $obj->setOrganizerKey($settings['organizerkey']);
                $obj->setAccessToken($settings['accesstoken']);
                $oauth->setWebinarId($webinar4[0]);
                $oauth->setRegistrantInfo($vars);

                $oauth->createRegistrant();
            }
        }
    }

    /**
     * Refreshes the refreshtoken and accessToken
     *
     * Reason we need this is because access token expires in 60 minutes
     * while the refreshtoken expires in 1 month, If we don't do this then
     * the integration will stop working after a month
     */
    public function refreshtoken()
    {
        $webinars = wishlistmember_instance()->get_option('webinar');

        $settings      = $webinars['gotomeetingapi'];
        $refresh_token = $settings['refreshtoken'];

        if (empty($settings)) {
            return;
        }

        // No need to refresh if they're still using the previuos auth code format.
        if (strlen($settings['authorizationcode']) < 10) {
            return;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.getgo.com/oauth/v2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=refresh_token&refresh_token=' . $refresh_token);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = [];

        $str            = GOTO_WEBINAR_API_KEY . ':' . GOTO_WEBINAR_API_SECRET;
        $id_plus_secret = base64_encode($str);

        $headers[] = 'Authorization: Basic ' . $id_plus_secret;
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . wp_kses(curl_error($ch), []);
        }
        curl_close($ch);

        $isJson        = 0;
        $decodedString = json_decode($result);
        if (is_array($decodedString) || is_object($decodedString)) {
            $isJson = 1;
        }

        if ($isJson) {
            $result = json_decode($result);
        }

        if ($result) {
            $settings['accesstoken']       = $result->access_token;
            $settings['organizerkey']      = $result->organizer_key;
            $settings['authorizationcode'] = $settings['authorizationcode'];
            $settings['refreshtoken']      = $result->refresh_token;

            $webinar_settings['gotomeetingapi'] = $settings;

            wishlistmember_instance()->save_option('webinar', $webinar_settings);
        }
    }
}

/**
 * ----------------------
 * API CLASSES
 * ----------------------
 */

class GTMAPI_OAuth_En
{
    protected $_accessToken;
    protected $_userId;
    protected $_organizerKey;
    protected $_refreshToken;
    protected $_expiresIn;

    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    public function setAccessToken($token)
    {
        $this->_accessToken = $token;
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    public function setUserId($id)
    {
        $this->_userId = $id;
    }

    public function getOrganizerKey()
    {
        return $this->_organizerKey;
    }

    public function setOrganizerKey($key)
    {
        $this->_organizerKey = $key;
    }

    public function getRefreshToken()
    {
        return $this->_refreshToken;
    }

    public function setRefreshToken($token)
    {
        $this->_refreshToken = $token;
    }

    public function getExpiresIn()
    {
        return $this->_expiresIn;
    }

    public function setExpiresIn($expiresIn)
    {
        $this->_expiresIn = $expiresIn;
    }
}

class GTMAPI_OAuth
{
    protected $_redirectUrl;
    protected $_OAuthEnObj;
    protected $_curlHeader = [];
    protected $_apiResponse;
    protected $_apiError;
    protected $_apiErrorCode;
    protected $_apiRequestUrl;
    protected $_apiResponseKey;
    protected $_accessTokenUrl;
    protected $_webinarId;
    protected $_registrantInfo = [];
    protected $_apiRequestType;
    protected $_apiPostData;

    public function __construct(GTMAPI_OAuth_En $oAuthEn)
    {
        $this->_OAuthEnObj = $oAuthEn;
    }

    public function getOAuthEntityClone()
    {
        return clone $this->_OAuthEnObj;
    }

    public function getWebinarId()
    {
        return $this->_webinarId;
    }

    public function setWebinarId($id)
    {
        $id               = (int) $id;
        $this->_webinarId = empty($id) ? 0 : $id;
    }

    public function setApiErrorCode($code)
    {
        $this->_apiErrorCode = $code;
    }

    public function getApiErrorCode()
    {
        return $this->_apiErrorCode;
    }

    public function getApiAuthorizationUrl()
    {
        return 'https://api.getgo.com/oauth/v2/authorize?client_id=' . GOTO_WEBINAR_API_KEY . '&response_type=code';
    }

    public function getApiKey()
    {
        return GOTO_WEBINAR_API_KEY;
    }

    public function getApiRequestUrl()
    {
        return $this->_apiRequestUrl;
    }

    public function setApiRequestUrl($url)
    {
        $this->_apiRequestUrl = $url;
    }

    public function setRedirectUrl($url)
    {
        $this->_redirectUrl = urlencode($url);
    }

    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    public function setCurlHeader($header)
    {
        $this->_curlHeader = $header;
    }

    public function getCurlHeader()
    {
        return $this->_curlHeader;
    }

    public function setApiResponseKey($key)
    {
        $this->_apiResponseKey = $key;
    }

    public function getApiResponseKey()
    {
        return $this->_apiResponseKey;
    }

    public function setRegistrantInfo($arrInfo)
    {
        $this->_registrantInfo = $arrInfo;
    }

    public function getRegistrantInfo()
    {
        return $this->_registrantInfo;
    }

    public function authorizeUsingResponseKey($responseKey)
    {
        $this->setApiResponseKey($responseKey);
        $this->setApiTokenUsingResponseKey();
    }

    protected function setAccessTokenUrl()
    {
        $url                   = 'https://api.getgo.com/oauth/access_token?grant_type=authorization_code&code={responseKey}&client_id={api_key}';
        $url                   = str_replace('{api_key}', $this->getApiKey(), $url);
        $url                   = str_replace('{responseKey}', $this->getApiResponseKey(), $url);
        $this->_accessTokenUrl = $url;
    }

    protected function getAccessTokenUrl()
    {
        return $this->_accessTokenUrl;
    }

    protected function resetApiError()
    {
        $this->_apiError = '';
    }

    public function setApiTokenUsingResponseKey()
    {
        // Set the access token url.
        $this->setAccessTokenUrl();

        // Set the url where api should go for request.
        $this->setApiRequestUrl($this->getAccessTokenUrl());

        // Make request.
        $this->makeApiRequest();

        if (! $this->hasApiError()) {
            // If api does not have any error set the token.
            // Echo $this->getResponseData();
            $responseData = json_decode($this->getResponseData());
            $this->_OAuthEnObj->setAccessToken($responseData->access_token);
            $this->_OAuthEnObj->setOrganizerKey($responseData->organizer_key);
            $this->_OAuthEnObj->setRefreshToken($responseData->refresh_token);
            $this->_OAuthEnObj->setExpiresIn($responseData->expires_in);
        }
    }

    public function hasApiError()
    {
        return $this->getApiError() ? 1 : 0;
    }

    public function getApiError()
    {
        return $this->_apiError;
    }

    public function setApiError($errors)
    {
        $this->_apiError = $errors;
        return $this->_apiError;
    }

    public function getApiRequestType()
    {
        return $this->_apiRequestType;
    }

    public function setApiRequestType($type)
    {
        $this->_apiRequestType = $type;
        return $this->_apiRequestType;
    }

    public function getResponseData()
    {
        return $this->_apiResponse;
    }

    public function setApiPostData($data)
    {
        $this->_apiPostData = $data;
        return $this->_apiPostData;
    }

    public function getApiPostData()
    {
        return $this->_apiPostData;
    }

    public function makeApiRequest()
    {
        $header = [];

        $this->getApiRequestUrl();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->getApiRequestUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ('POST' === $this->getApiRequestType()) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getApiPostData());
        }

        if ($this->getCurlHeader()) {
            $headers = $this->getCurlHeader();
        } else {
            $headers = [
                'HTTP/1.1',
                'Content-type: application/json',
                'Accept: application/json',
                'Authorization: OAuth oauth_token=' . $this->_OAuthEnObj->getAccessToken(),
            ];
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data               = curl_exec($ch);
        $validResponseCodes = [200, 201, 409];
        $responseCode       = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->resetApiError();

        if (curl_errno($ch)) {
            $this->setApiError([curl_error($ch)]);
        } elseif (! in_array($responseCode, $validResponseCodes)) {
            if ($this->isJsonString($data)) {
                $data = json_decode($data);
            }

            $this->setApiError($data);
            $this->setApiErrorCode($responseCode);
        } else {
            $this->_apiResponse          = $data;
            $_SESSION['gotoApiResponse'] = $this->getResponseData();
            curl_close($ch);
        }
    }



    public function getWebinars()
    {
        $url = 'https://api.getgo.com/G2W/rest/organizers/' . $this->_OAuthEnObj->getOrganizerKey() . '/webinars';
        $this->setApiRequestUrl($url);
        $this->setApiRequestType('GET');
        $this->makeApiRequest();

        if ($this->hasApiError()) {
            return null;
        }
        $webinars = json_decode($this->getResponseData());

        return $webinars;
    }

    public function createRegistrant()
    {
        if (! $this->getWebinarId()) {
            $this->setApiError(['Webinar id not provided']);
            return null;
        }

        if (! $this->getRegistrantInfo()) {
            $this->setApiError(['Registrant info not provided']);
            return null;
        }

        $this->setApiRequestType('POST');
        $this->setApiPostData(json_encode($this->getRegistrantInfo()));
        $url = 'https://api.getgo.com/G2W/rest/organizers/' . $this->_OAuthEnObj->getOrganizerKey() . '/webinars/' . $this->getWebinarId() . '/registrants';

        $this->setApiRequestUrl($url);
        $this->makeApiRequest();

        if ($this->hasApiError()) {
            return null;
        }

        $webinar = json_decode($this->getResponseData());

        return $webinar;
    }



    public function isJsonString($string)
    {
        $isJson        = 0;
        $decodedString = json_decode($string);
        if (is_array($decodedString) || is_object($decodedString)) {
            $isJson = 1;
        }

        return $isJson;
    }

    public function getAccessTokenv2($auth_code)
    {

        $str            = GOTO_WEBINAR_API_KEY . ':' . GOTO_WEBINAR_API_SECRET;
        $id_plus_secret = base64_encode($str);

        $response = wp_remote_post(
            'https://api.getgo.com/oauth/v2/token',
            [
                'headers' => [
                    'Content-Type'  => 'application/x-www-form-urlencoded; charset=utf-8',
                    'Authorization' => 'Basic ' . $id_plus_secret,
                    'Accept'        => 'application/json',
                ],
                'body'    => [
                    'code'       => $auth_code,
                    'grant_type' => 'authorization_code',
                ],
            ]
        );

        if (! is_wp_error($response)) {
            if (200 == wp_remote_retrieve_response_code($response)) {
                  // Lets get the response and decode it.
                  $jsondata = json_decode($response['body'], true);
                  return $jsondata;
            }
        } else {
            if ($this->isJsonString($response)) {
                $response = json_decode($response);
            }

            return $response;
        }
    }
}

// Initialize.
new GoToWebinarAPIIntegration();
