<?php
/**
 * WLM <-> GetResponse v3
 *
 * @see http://apidocs.getresponse.com/en/v3/resources
 */
class WLM_GETRESPONSE_V3 {

	private $api_key;
	private $api_url = 'https://api.getresponse.com/v3';
	private $timeout = 8;
	public $http_status;
	/**
	 * Set api key and optionally API endpoint
	 *
	 * @param      $api_key
	 * @param null $api_url
	 */
	public function __construct( $api_key, $api_url = null) {
		$this->api_key = $api_key;
		if (!empty($api_url)) {
			$this->api_url = $api_url;
		}
	}
	/**
	 * We can modify internal settings
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set( $key, $value) {
		$this->{$key} = $value;
	}
	/**
	 * Get account details
	 *
	 * @return mixed
	 */
	public function accounts() {
		return $this->call('accounts');
	}
	/**
	 * Ping
	 *
	 * @return mixed
	 */
	public function ping() {
		return $this->accounts();
	}
	/**
	 * Return all campaigns
	 *
	 * @return mixed
	 */
	public function getCampaigns() {
		return $this->call('campaigns');
	}
	/**
	 * Add single contact into your campaign
	 *
	 * @param $params
	 * @return mixed
	 */
	public function addContact( $params) {
		return $this->call('contacts', 'POST', $params);
	}
	/**
	 * Retrieving contact by params
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function getContacts( $params = array()) {
		return $this->call('contacts?' . $this->setParams($params));
	}
	/**
	 * Drop single user by ID
	 *
	 * @param string $contact_id - obtained by API
	 * @return mixed
	 */
	public function deleteContact( $contact_id, $params = array() ) {
		$q = '';
		if ( count($params) > 0 ) {
			$q = '?' . $this->setParams($params);
		}
		return $this->call('contacts/' . $contact_id . $q, 'DELETE');
	}

	/**
	 * Curl run request
	 *
	 * @param null $api_method
	 * @param string $http_method
	 * @param array $params
	 * @return mixed
	 * @throws Exception
	 */
	private function call( $api_method = null, $http_method = 'GET', $params = array()) {
		if (empty($api_method)) {
			return (object) array(
				'httpStatus' => '400',
				'code' => '1010',
				'codeDescription' => 'Error in external resources',
				'message' => 'Invalid api method'
			);
		}
		$params  = json_encode($params);
		$url     = $this->api_url . '/' . $api_method;
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_ENCODING => 'gzip,deflate',
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $this->timeout,
			CURLOPT_HEADER => false,
			CURLOPT_USERAGENT => 'PHP GetResponse client 0.0.2',
			CURLOPT_HTTPHEADER => array('X-Auth-Token: api-key ' . $this->api_key, 'Content-Type: application/json')
		);

		if ('POST' === $http_method) {
			$options[CURLOPT_POST]       = 1;
			$options[CURLOPT_POSTFIELDS] = $params;
		} elseif ('DELETE' === $http_method) {
			$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
		}
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response          = json_decode(curl_exec($curl));
		$this->http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return (object) $response;
	}
	/**
	 * Set parameters
	 *
	 * @param array $params
	 * @return string
	 */
	private function setParams( $params = array()) {
		$result = array();
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$result[$key] = $value;
			}
		}
		return http_build_query($result);
	}
}
