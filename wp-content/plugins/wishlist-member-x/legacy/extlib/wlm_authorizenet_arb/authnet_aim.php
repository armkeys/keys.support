<?php

namespace WLMAuthnet;

class AuthnetAIM {
	private $login    = '';
	private $transkey = '';
	private $params   = array();
	private $results  = array();

	private $approved = false;
	private $declined = false;
	private $error    = true;

	private $test;
	private $fields;
	private $response;

	public static $instances = 0;

	public function __construct( $login, $key, $test = false ) {
		if ( 0 == self::$instances ) {
			$this->test = wlm_trim( $test );
			if ( $this->test ) {
				$this->url = 'https://test.authorize.net/gateway/transact.dll';
			} else {
				$this->url = 'https://secure.authorize.net/gateway/transact.dll';
			}
			$this->login    = $login;
			$this->transkey = $key;
			self::$instances++;
		} else {
			return false;
		}
	}

	public function do_apicall( $data ) {
		if ( ! $this->login || ! $this->transkey ) {
			throw new \Exception( 'You have not configured your Authnet login credentials.' );
		}
		$this->fields       = '';
		$this->results      = '';
		$data['x_login']    = $this->login;
		$data['x_tran_key'] = $this->transkey;

		$this->params = $data;
		$this->_prepareParameters();
		$this->process();
	}

	private function process( $retries = 3 ) {
		$ch    = curl_init( $this->url );
		$count = 0;
		while ( $count < $retries ) {
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, rtrim( $this->fields, '& ' ) );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$this->response = curl_exec( $ch );
			$this->_parseResults();

			if ( 'Approved' === $this->getResultResponseFull() ) {
				$this->approved = true;
				$this->declined = false;
				$this->error    = false;
				break;
			} elseif ( 'Declined' === $this->getResultResponseFull() ) {
				$this->approved = false;
				$this->declined = true;
				throw new \Exception( $this->getResponseText() );
				break;
			} else {
				$this->approved = false;
				$this->declined = true;
				throw new \Exception( $this->getResponseText() );
				break;
			}
			$count++;
		}
		curl_close( $ch );
	}

	private function _parseResults() {
		$this->results = explode( '|', $this->response );
	}

	public function setParameter( $param, $value ) {
		$param                  = wlm_trim( $param );
		$value                  = wlm_trim( $value );
		$this->params[ $param ] = $value;
	}

	public function setTransactionType( $type ) {
		$this->params['x_type'] = strtoupper( wlm_trim( $type ) );
	}

	private function _prepareParameters() {
		foreach ( $this->params as $key => $value ) {
			$this->fields .= "$key=" . urlencode( $value ) . '&';
		}
	}

	public function getResultResponse() {
		return $this->results[0];
	}

	public function getResultResponseFull() {
		$response = array( '', 'Approved', 'Declined', 'Error' );
		return $response[ $this->results[0] ];
	}

	public function isApproved() {
		return $this->approved;
	}

	public function isDeclined() {
		return $this->declined;
	}

	public function isError() {
		return $this->error;
	}

	public function getResponseText() {
		return $this->results[3];
	}

	public function getAuthCode() {
		return $this->results[4];
	}

	public function getAVSResponse() {
		return $this->results[5];
	}

	public function getTransactionID() {
		return $this->results[6];
	}
}
