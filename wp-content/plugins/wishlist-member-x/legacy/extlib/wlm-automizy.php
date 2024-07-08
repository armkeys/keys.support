<?php

class WLM_Automizy {

	private $api_token;
	private $api_url = 'https://gateway.automizy.com/v2';

	private $request_successful   = false;
	private $last_error           = '';
	private $last_response        = array();
	private $last_response_header = array();

	public $verify_ssl = false;

	public function __construct( $api_token ) {

		$this->api_token            = $api_token;
		$this->last_response        = null;
		$this->last_response_header = null;

		if ( !function_exists( 'curl_init' ) || !function_exists( 'curl_setopt' ) ) {
			trigger_error('cURL not supported.');
			$this->last_error = 'cURL not supported.';
		} else {
			if ( empty($this->api_token) ) {
				trigger_error(esc_html( "Invalid API Token: {$this->api_token}" ));
				$this->last_error = "Invalid API Token: {$this->api_token}";
				$this->api_token  = null;
			} else {
				$this->last_error = '';
			}
		}
	}

	private function make_request( $action, $method, $args = array(), $timeout = 10 ) {

		$this->last_error           = '';
		$this->request_successful   = false;
		$this->last_response        = null;
		$this->last_response_header = null;

		$request_url = $this->api_url . '/' . $method;
		$status_code = 418;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $request_url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization: Bearer ' . $this->api_token
		));
		curl_setopt( $ch, CURLOPT_USERAGENT, 'WLM/MCIntegration-API/3.0 (wishlist-member)');
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		curl_setopt( $ch, CURLOPT_ENCODING, '' );
		curl_setopt( $ch, CURLINFO_HEADER_OUT, true );

		switch ( $action ) {
			case 'post':
				curl_setopt( $ch, CURLOPT_POST, true );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $args ) );
				break;

			case 'get':
				$query = http_build_query( $args, '', '&' );
				curl_setopt( $ch, CURLOPT_URL, $request_url . '?' . $query );
				break;

			case 'delete':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
				break;

			case 'patch':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PATCH' );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $args ) );
				break;

			case 'put':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $args ) );
				break;
		}

		$this->last_response        = curl_exec( $ch );
		$this->last_response_header = curl_getinfo( $ch );

		if ( $this->last_response ) {
			$this->last_response = json_decode( $this->last_response, true);
		} else {
			$this->last_response = false;
		}

		if ( isset( $this->last_response_header['http_code'] ) ) {
			$status_code = (int) $this->last_response_header['http_code'];
		} elseif ( isset( $this->last_response['status'] ) ) {
			$status_code = (int) $this->last_response['status'];
		}

		if ( $status_code >= 200 && $status_code <= 299 ) {
			$this->request_successful = true;
		} else {
			if ( isset( $this->last_response['detail'] ) ) {
				$this->last_error = sprintf('%d: %s', $this->last_response['status'], $this->last_response['detail'] );
			} else {
				$this->last_error = curl_error( $ch );
			}
			$this->last_response = false;
		}

		curl_close($ch);

		return $this->last_response;
	}

	public function is_success() {
		return $this->request_successful;
	}

	public function get_last_error() {
		return $this->last_error ? $this->last_error : false;
	}

	public function get_last_response() {
		return $this->last_response;
	}

	public function get_last_response_header() {
		return $this->last_response;
	}

	public function delete( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'delete', $method, $args, $timeout );
	}

	public function get( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'get', $method, $args, $timeout );
	}

	public function patch( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'patch', $method, $args, $timeout );
	}

	public function post( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'post', $method, $args, $timeout );
	}

	public function put( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'put', $method, $args, $timeout );
	}
}
