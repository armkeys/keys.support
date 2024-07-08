<?php
if (!class_exists('CurlObject')) {
require_once 'curl_object.php';
}
if (!class_exists('CurlResponse')) {
require_once 'curl_response.php';
}

/**
 * OAuthServiceProvider
 *
 * Represents the service provider in the OAuth authentication model.
 * The class that implements the service provider will contain the
 * specific knowledge about the API we are interfacing with, and
 * provide useful methods for interfacing with its API.
 *
 * For example, an OAuthServiceProvider would know the URLs necessary
 * to perform specific actions, the type of data that the API calls
 * would return, and would be responsible for manipulating the results
 * into a useful manner.
 *
 * It should be noted that the methods enforced by the OAuthServiceProvider
 * interface are made so that it can interact with our OAuthApplication
 * cleanly, rather than from a general use perspective, though some
 * methods for those purposes do exists (such as getUserData).
 *
 * @package
 * @version $id$
 */
interface OAuthServiceProvider {

	public function getAccessTokenUrl();
	public function getAuthorizeUrl();
	public function getRequestTokenUrl();
	public function getAuthTokenFromUrl();
	public function getBaseUri();
	public function getUserData();

}

/**
 * OAuthApplication
 *
 * Base class to represent an OAuthConsumer application.  This class is
 * intended to be extended and modified for each ServiceProvider. Each
 * OAuthServiceProvider should have a complementary OAuthApplication
 *
 * The OAuthApplication class should contain any details on preparing
 * requires that is unique or specific to that specific service provider's
 * implementation of the OAuth model.
 *
 * This base class is based on OAuth 1.0, designed with AWeber's implementation
 * as a model.  An OAuthApplication built to work with a different service
 * provider (especially an OAuth2.0 Application) may alter or bypass portions
 * of the logic in this class to meet the needs of the service provider it
 * is designed to interface with.
 *
 * @package
 * @version $id$
 */
class OAuthApplication implements AWeberOAuthAdapter {
	public $debug = false;

	public $userAgent = 'AWeber OAuth Consumer Application 1.0 - https://labs.aweber.com/';

	public $format = false;

	public $requiresTokenSecret = true;

	public $signatureMethod = 'HMAC-SHA1';
	public $version         = '1.0';

	public $curl = false;

	/**
	 * User currently interacting with the service provider
	 *
	 * @var OAuthUser
	 */
	public $user = false;

	// Data binding this OAuthApplication to the consumer application it is acting.
	// As a proxy for.
	public $consumerKey    = false;
	public $consumerSecret = false;

	/**
	 * __construct
	 *
	 * Create a new OAuthApplication, based on an OAuthServiceProvider
	 *
	 * @return void
	 */
	public function __construct( $parentApp = false) {
		if ($parentApp) {
			if (!is_a($parentApp, 'OAuthServiceProvider')) {
				throw new Exception('Parent App must be a valid OAuthServiceProvider!');
			}
			$this->app = $parentApp;
		}
		$this->user = new OAuthUser();
		$this->curl = new CurlObject();
	}

	/**
	 * Request
	 *
	 * Implemented for a standard OAuth adapter interface
	 *
	 * @param mixed $method
	 * @param mixed $uri
	 * @param array $data
	 * @param array $options
	 * @return void
	 */
	public function request( $method, $uri, $data = array(), $options = array()) {
		$uri = $this->app->removeBaseUri($uri);
		$url = $this->app->getBaseUri() . $uri;

		# WARNING: non-primative items in data must be json serialized in GET and POST.
		if ('POST' === $method || 'GET' === $method) {
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$data[$key] = json_encode($value);
				}
			}
		}

		$response = $this->makeRequest($method, $url, $data);
		if (!empty($options['return'])) {
			if ('status' === $options['return']) {
				return $response->headers['Status-Code'];
			}
			if ('headers' === $options['return']) {
				return $response->headers;
			}
			if ('integer' === $options['return']) {
				return intval($response->body);
			}
		}

		$data = json_decode($response->body, true);

		if (empty($options['allow_empty']) && !isset($data)) {
			throw new AWeberResponseError($uri);
		}
		return $data;
	}

	/**
	 * GetRequestToken
	 *
	 * Gets a new request token / secret for this user.
	 *
	 * @return void
	 */
	public function getRequestToken( $callbackUrl = false) {
		$data = ( $callbackUrl )? array('oauth_callback' => $callbackUrl) : array();
		$resp = $this->makeRequest('POST', $this->app->getRequestTokenUrl(), $data);
		$data = $this->parseResponse($resp);
		$this->requiredFromResponse($data, array('oauth_token', 'oauth_token_secret'));
		$this->user->requestToken = $data['oauth_token'];
		$this->user->tokenSecret  = $data['oauth_token_secret'];
		return $data['oauth_token'];
	}

	/**
	 * GetAccessToken
	 *
	 * Makes a request for access tokens.  Requires that the current user has an authorized
	 * token and token secret.
	 *
	 * @return void
	 */
	public function getAccessToken() {
		$resp = $this->makeRequest('POST', $this->app->getAccessTokenUrl(),
			array('oauth_verifier' => $this->user->verifier)
		);
		$data = $this->parseResponse($resp);
		$this->requiredFromResponse($data, array('oauth_token', 'oauth_token_secret'));

		if (empty($data['oauth_token'])) {
			throw new AWeberOAuthDataMissing('oauth_token');
		}

		$this->user->accessToken = $data['oauth_token'];
		$this->user->tokenSecret = $data['oauth_token_secret'];
		return array($data['oauth_token'], $data['oauth_token_secret']);
	}

	/**
	 * ParseAsError
	 *
	 * Checks if response is an error.  If it is, raise an appropriately
	 * configured exception.
	 *
	 * @param mixed $response   Data returned from the server, in array form
	 * @throws AWeberOAuthException
	 * @return void
	 */
	public function parseAsError( $response) {
		if (!empty($response['error'])) {
			throw new AWeberOAuthException($response['error']['type'],
				$response['error']['message']);
		}
	}

	/**
	 * RequiredFromResponse
	 *
	 * Enforce that all the fields in requiredFields are present and not
	 * empty in data.  If a required field is empty, throw an exception.
	 *
	 * @param mixed $data               Array of data
	 * @param mixed $requiredFields     Array of required field names.
	 * @return void
	 */
	protected function requiredFromResponse( $data, $requiredFields) {
		foreach ($requiredFields as $field) {
			if (empty($data[$field])) {
				throw new AWeberOAuthDataMissing($field);
			}
		}
	}

	/**
	 * Get
	 *
	 * Make a get request.  Used to exchange user tokens with serice provider.
	 *
	 * @param mixed $url        URL to make a get request from.
	 * @param array $data       Data for the request.
	 * @return void
	 */
	protected function get( $url, $data) {
		$url    = $this->_addParametersToUrl($url, $data);
		$handle = $this->curl->init($url);
		$resp   = $this->_sendRequest($handle);
		return $resp;
	}

	/**
	 * _addParametersToUrl
	 *
	 * Adds the parameters in associative array $data to the
	 * given URL
	 *
	 * @param String $url       URL
	 * @param array $data       Parameters to be added as a query string to
	 *      the URL provided
	 * @return void
	 */
	protected function _addParametersToUrl( $url, $data) {
		if (!empty($data)) {
			if (false === strpos($url, '?')) {
				$url .= '?' . $this->buildData($data);
			} else {
				$url .= '&' . $this->buildData($data);
			}
		}
		return $url;
	}

	/**
	 * GenerateNonce
	 *
	 * Generates a 'nonce', which is a unique request id based on the
	 * timestamp.  If no timestamp is provided, generate one.
	 *
	 * @param mixed $timestamp Either a timestamp (epoch seconds) or false,
	 *  in which case it will generate a timestamp.
	 * @return string   Returns a unique nonce
	 */
	public function generateNonce( $timestamp = false) {
		if (!$timestamp) {
$timestamp = $this->generateTimestamp();
		}
		return md5($timestamp . '-' . rand(10000, 99999) . '-' . uniqid());
	}

	/**
	 * GenerateTimestamp
	 *
	 * Generates a timestamp, in seconds
	 *
	 * @return int Timestamp, in epoch seconds
	 */
	public function generateTimestamp() {
		return time();
	}

	/**
	 * CreateSignature
	 *
	 * Creates a signature on the signature base and the signature key
	 *
	 * @param mixed $sigBase    Base string of data to sign
	 * @param mixed $sigKey     Key to sign the data with
	 * @return string   The signature
	 */
	public function createSignature( $sigBase, $sigKey) {
		switch ($this->signatureMethod) {
			case 'HMAC-SHA1':
			default:
				return base64_encode(hash_hmac('sha1', $sigBase, $sigKey, true));
		}
	}

	/**
	 * Encode
	 *
	 * Short-cut for utf8_encode / rawurlencode
	 *
	 * @param mixed $data   Data to encode
	 * @return void         Encoded data
	 */
	protected function encode( $data) {
		return rawurlencode($data);
	}

	/**
	 * CreateSignatureKey
	 *
	 * Creates a key that will be used to sign our signature.  Signatures
	 * are signed with the consumerSecret for this consumer application and
	 * the token secret of the user that the application is acting on behalf
	 * of.
	 *
	 * @return void
	 */
	public function createSignatureKey() {
		return $this->consumerSecret . '&' . $this->user->tokenSecret;
	}

	/**
	 * GetOAuthRequestData
	 *
	 * Get all the pre-signature, OAuth specific parameters for a request.
	 *
	 * @return void
	 */
	public function getOAuthRequestData() {
		$token = $this->user->getHighestPriorityToken();
		$ts    = $this->generateTimestamp();
		$nonce = $this->generateNonce($ts);
		return array(
			'oauth_token' => $token,
			'oauth_consumer_key' => $this->consumerKey,
			'oauth_version' => $this->version,
			'oauth_timestamp' => $ts,
			'oauth_signature_method' => $this->signatureMethod,
			'oauth_nonce' => $nonce);
	}


	/**
	 * MergeOAuthData
	 *
	 * @param mixed $requestData
	 * @return void
	 */
	public function mergeOAuthData( $requestData) {
		$oauthData = $this->getOAuthRequestData();
		return array_merge($requestData, $oauthData);
	}

	/**
	 * CreateSignatureBase
	 *
	 * @param mixed $method     String name of HTTP method, such as "GET"
	 * @param mixed $url        URL where this request will go
	 * @param mixed $data       Array of params for this request. This should
	 *      include ALL oauth properties except for the signature.
	 * @return void
	 */
	public function createSignatureBase( $method, $url, $data) {
		$method = $this->encode(strtoupper($method));
		$query  = parse_url($url, PHP_URL_QUERY);
		if ($query) {
			$parts = explode('?', $url, 2);
			$url   = array_shift($parts);
			$items = explode('&', $query);
			foreach ($items as $item) {
				list($key, $value)        = explode('=', $item);
				$data[rawurldecode($key)] = rawurldecode($value);
			}
		}
		$url  = $this->encode($url);
		$data = $this->encode($this->collapseDataForSignature($data));

		return $method . '&' . $url . '&' . $data;
	}

	/**
	 * CollapseDataForSignature
	 *
	 * Turns an array of request data into a string, as used by the oauth
	 * signature
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function collapseDataForSignature( $data) {
		ksort($data);
		$collapse = '';
		foreach ($data as $key => $val) {
			if (!empty($collapse)) {
$collapse .= '&';
			}
			$collapse .= $key . '=' . $this->encode($val);
		}
		return $collapse;
	}

	/**
	 * SignRequest
	 *
	 * Signs the request.
	 *
	 * @param mixed $method     HTTP method
	 * @param mixed $url        URL for the request
	 * @param mixed $data       The data to be signed
	 * @return array            The data, with the signature.
	 */
	public function signRequest( $method, $url, $data) {
		$base                    = $this->createSignatureBase($method, $url, $data);
		$key                     = $this->createSignatureKey();
		$data['oauth_signature'] = $this->createSignature($base, $key);
		ksort($data);
		return $data;
	}


	/**
	 * MakeRequest
	 *
	 * Public facing function to make a request
	 *
	 * @param mixed $method
	 * @param mixed $url  - Reserved characters in query params MUST be escaped
	 * @param mixed $data - Reserved characters in values MUST NOT be escaped
	 * @return void
	 */
	public function makeRequest( $method, $url, $data = array()) {

		// If ($this->debug) {
		// Echo "\n** {$method}: $url\n";
		// }

		switch (strtoupper($method)) {
			case 'POST':
				$oauth = $this->prepareRequest($method, $url, $data);
				$resp  = $this->post($url, $oauth);
				break;

			case 'GET':
				$oauth = $this->prepareRequest($method, $url, $data);
				$resp  = $this->get($url, $oauth, $data);
				break;

			case 'DELETE':
				$oauth = $this->prepareRequest($method, $url, $data);
				$resp  = $this->delete($url, $oauth);
				break;

			case 'PATCH':
				$oauth = $this->prepareRequest($method, $url, array());
				$resp  = $this->patch($url, $oauth, $data);
				break;
		}

		// Enable debug output.
		// If ($this->debug) {
		// Echo '<pre>';
		// Print_r($oauth);
		// Echo " --> Status: {$resp->headers['Status-Code']}\n";
		// Echo " --> Body: {$resp->body}";
		// Echo '</pre>';
		// }

		if (!$resp) {
			$msg   = 'Unable to connect to the AWeber API.  (' . $this->error . ')';
			$error = array('message' => $msg, 'type' => 'APIUnreachableError',
						   'documentation_url' => 'https://labs.aweber.com/docs/troubleshooting');
			throw new AWeberAPIException($error, $url);
		}

		if ($resp->headers['Status-Code'] >= 400) {
			$data = json_decode($resp->body, true);
			throw new AWeberAPIException($data['error'], $url);
		}

		return $resp;
	}

	/**
	 * Put
	 *
	 * Prepare an OAuth put method.
	 *
	 * @param mixed $url    URL where we are making the request to
	 * @param mixed $data   Data that is used to make the request
	 * @return void
	 */
	protected function patch( $url, $oauth, $data) {
		$url    = $this->_addParametersToUrl($url, $oauth);
		$handle = $this->curl->init($url);
		$this->curl->setopt($handle, CURLOPT_CUSTOMREQUEST, 'PATCH');
		$this->curl->setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));
		$resp = $this->_sendRequest($handle, array('Expect:', 'Content-Type: application/json'));
		return $resp;
	}

	/**
	 * Post
	 *
	 * Prepare an OAuth post method.
	 *
	 * @param mixed $url    URL where we are making the request to
	 * @param mixed $data   Data that is used to make the request
	 * @return void
	 */
	protected function post( $url, $oauth) {
		$handle   = $this->curl->init($url);
		$postData = $this->buildData($oauth);
		$this->curl->setopt($handle, CURLOPT_POST, true);
		$this->curl->setopt($handle, CURLOPT_POSTFIELDS, $postData);
		$resp = $this->_sendRequest($handle);
		return $resp;
	}

	/**
	 * Delete
	 *
	 * Makes a DELETE request
	 *
	 * @param mixed $url        URL where we are making the request to
	 * @param mixed $data       Data that is used in the request
	 * @return void
	 */
	protected function delete( $url, $data) {
		$url    = $this->_addParametersToUrl($url, $data);
		$handle = $this->curl->init($url);
		$this->curl->setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
		$resp = $this->_sendRequest($handle);
		return $resp;
	}

	/**
	 * BuildData
	 *
	 * Creates a string of data for either post or get requests.
	 *
	 * @param mixed $data       Array of key value pairs
	 * @return void
	 */
	public function buildData( $data) {
		ksort($data);
		$params = array();
		foreach ($data as $key => $value) {
			$params[] = $key . '=' . $this->encode($value);
		}
		return implode('&', $params);
	}

	/**
	 * _sendRequest
	 *
	 * Actually makes a request.
	 *
	 * @param mixed $handle     Curl handle
	 * @param array $headers    Additional headers needed for request
	 * @return void
	 */
	private function _sendRequest( $handle, $headers = array('Expect:')) {
		$this->curl->setopt($handle, CURLOPT_RETURNTRANSFER, true);
		$this->curl->setopt($handle, CURLOPT_HEADER, true);
		$this->curl->setopt($handle, CURLOPT_HTTPHEADER, $headers);
		$this->curl->setopt($handle, CURLOPT_USERAGENT, $this->userAgent);
		$this->curl->setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		$this->curl->setopt($handle, CURLOPT_VERBOSE, false);
		$this->curl->setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
		$this->curl->setopt($handle, CURLOPT_TIMEOUT, 90);
		$resp = $this->curl->execute($handle);
		if ($resp) {
			return new CurlResponse($resp);
		}
		$this->error = $this->curl->errno($handle) . ' - ' .
			$this->curl->error($handle);
		return false;
	}

	/**
	 * PrepareRequest
	 *
	 * @param mixed $method     HTTP method
	 * @param mixed $url        URL for the request
	 * @param mixed $data       The data to generate oauth data and be signed
	 * @return void             The data, with all its OAuth variables and signature
	 */
	public function prepareRequest( $method, $url, $data) {
		$data = $this->mergeOAuthData($data);
		$data = $this->signRequest($method, $url, $data);
		return $data;
	}

	/**
	 * ParseResponse
	 *
	 * Parses the body of the response into an array
	 *
	 * @param mixed $string     The body of a response
	 * @return void
	 */
	public function parseResponse( $resp) {
		$data = array();

		if (!$resp) {
return $data; }
		if (empty($resp)) {
return $data; }
		if (empty($resp->body)) {
return $data; }

		switch ($this->format) {
			case 'json':
				$data = json_decode($resp->body);
				break;
			default:
				parse_str($resp->body, $data);
		}
		$this->parseAsError($data);
		return $data;
	}

}

/**
 * OAuthUser
 *
 * Simple data class representing the user in an OAuth application.
 *
 * @package
 * @version $id$
 */
class OAuthUser {

	public $authorizedToken = false;
	public $requestToken    = false;
	public $verifier        = false;
	public $tokenSecret     = false;
	public $accessToken     = false;

	/**
	 * IsAuthorized
	 *
	 * Checks if this user is authorized.
	 *
	 * @return void
	 */
	public function isAuthorized() {
		if (empty($this->authorizedToken) && empty($this->accessToken)) {
			return false;
		}
		return true;
	}


	/**
	 * GetHighestPriorityToken
	 *
	 * Returns highest priority token - used to define authorization
	 * state for a given OAuthUser
	 *
	 * @return void
	 */
	public function getHighestPriorityToken() {
		if (!empty($this->accessToken)) {
return $this->accessToken;
		}
		if (!empty($this->authorizedToken)) {
return $this->authorizedToken;
		}
		if (!empty($this->requestToken)) {
return $this->requestToken;
		}

		// Return no token, new user.
		return '';
	}

}


