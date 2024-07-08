<?php

/**
 * Maropost API
 *
 * @package WishListMember/Autoresponders
 */

/**
 * WishListMember_Maropost_API class
 */
class WishListMember_Maropost_API
{
    /**
     * Account ID
     *
     * @var string
     */
    private $account_id;

    /**
     * API endpoing
     *
     * @var string
     */
    private $endpoint;
    /**
     * Constructor
     *
     * @param string $account_id Account ID.
     * @param string $auth_token Auth token.
     */
    public function __construct($account_id, $auth_token)
    {
        $this->account_id = wlm_trim($account_id);
        $this->auth_token = wlm_trim($auth_token);
    }

    /**
     * Create request body XML
     */
    private function create_req_body_xml()
    {
        // Implement.
    }

    /**
     * Create request body.
     *
     * @param  string       $format Format. Can be either 'json' or 'xml'.
     * @param  array|object $data   Data to process.
     * @return string
     */
    private function create_req_body($format, $data)
    {
        if ('json' === $format) {
            return wp_json_encode($data);
        } elseif ('xml' === $format) {
            return $this->create_req_body_xml();
        }
    }

    /**
     * Read XML response
     */
    private function read_resp_xml()
    {
        // Implement.
    }

    /**
     * Read response
     *
     * @param  string $format Format. Can be either 'json' or 'xml'.
     * @param  string $body   JSON or XML body.
     * @return object
     */
    private function read_resp($format, $body)
    {
        if ('json' === $format) {
            return json_decode($body);
        } elseif ('xml' === $format) {
            return $this->read_resp_xml();
        }
    }

    /**
     * Make API request
     *
     * @throws \Exception On API request error.
     * @param  string $method Request method.
     * @param  string $action Action.
     * @param  array  $params Parameters.
     * @param  array  $data   Data.
     * @return object
     */
    public function request($method, $action, $params = [], $data = [])
    {
        $actions = [
            'lists'    => [
                'resource' => '/accounts/{account_id}/lists{format}?auth_token={auth_token}&page={page}',
            ],
            'contact'  => [
                'resource' => '/accounts/{account_id}/lists/{list_id}/contacts/{contact_id}{format}?auth_token={auth_token}',
            ],
            'contacts' => [
                'resource' => '/accounts/{account_id}/lists/{list_id}/contacts{format}?auth_token={auth_token}',
            ],
        ];

        $gateway  = 'http://api.maropost.com';
        $resource = $actions[ $action ]['resource'];
        $format   = 'json';

        $default_params = [
            '{account_id}' => $this->account_id,
            '{format}'     => '.' . $format,
            '{auth_token}' => $this->auth_token,
        ];

        $params = ! is_array($params) ? $default_params : array_merge($default_params, $params);

        foreach ($params as $pkey => $pval) {
            $resource = str_replace($pkey, $pval, $resource);
        }

        if (! is_array($data)) {
            $data = [];
        }

        $body = $this->create_req_body($format, $data);

        switch ($method) {
            case 'GET':
                $gateway = $gateway . $resource;
                $resp    = wp_remote_get($gateway, ['timeout' => 15]);
                if (is_wp_error($resp)) {
                    throw new \Exception($resp->get_error_message());
                }
                $resp = $resp['body'];
                $resp = $this->read_resp($format, $resp);
                return $resp;
                break;
            case 'POST':
                $headers = ['Content-type' => 'application/json'];
                $gateway = $gateway . $resource;
                $resp    = wp_remote_post(
                    $gateway,
                    [
                        'sslverify' => false,
                        'timeout'   => 15,
                        'body'      => $body,
                        'headers'   => $headers,
                    ]
                );
                if (is_wp_error($resp)) {
                    throw new \Exception($resp->get_error_message());
                }
                $resp = $resp['body'];
                $resp = $this->read_resp($format, $resp);
                return $resp;
                break;
            default:
                $headers = ['Content-type' => 'application/json'];
                $gateway = $gateway . $resource;
                $resp    = wp_remote_request(
                    $gateway,
                    [
                        'method'  => $method,
                        'timeout' => 15,
                        'body'    => $body,
                        'headers' => $headers,
                        'body'    => $body,
                    ]
                );

                if (is_wp_error($resp)) {
                    throw new \Exception($resp->get_error_message());
                }
                $resp = $resp['body'];
                $resp = $this->read_resp($format, $resp);
                return $resp;
                // Code...
                break;
        }
    }
    /**
     * Create a list.
     *
     * @param  object $list List object.
     * @return object
     */
    public function create_list($list)
    {
        $list = $this->request('POST', 'lists', [], $list);
        if (empty($list->id)) {
            return false;
        }
        return $list;
    }

    /**
     * Get lists.
     *
     * @return array
     */
    public function get_lists()
    {
        $lists = false;

        $count = 0;
        while (true) {
            $list = $this->request('GET', 'lists', ['{page}' => ++$count]);
            if (is_array($list) && count($list) > 0) {
                $lists = array_merge(is_array($lists) ? $lists : [], $list);
            } else {
                break;
            }
        }

        return $lists;
    }

    /**
     * Add contact to list
     *
     * @param string $list    List ID.
     * @param array  $contact Contact data.
     */
    public function add_to_list($list, $contact)
    {
        $obj = $this->request('POST', 'contacts', ['{list_id}' => $list], $contact);
        if (empty($obj->id)) {
            return false;
        }

        $contact['subscribe'] = true;
        $this->request(
            'PUT',
            'contact',
            [
                '{list_id}'    => $list,
                '{contact_id}' => $obj->id,
            ],
            $contact
        );
        return $obj;
    }

    /**
     * Remove contact from list
     *
     * @param  string|integer $list       List ID.
     * @param  string|integer $contact_id Contact ID.
     * @return object API response.
     */
    public function remove_from_list($list, $contact_id)
    {
        $contact              = [];
        $contact['subscribe'] = false;
        $obj                  = $this->request(
            'PUT',
            'contact',
            [
                '{list_id}'    => $list,
                '{contact_id}' => $contact_id,
            ],
            $contact
        );
        return $obj;
    }
}
