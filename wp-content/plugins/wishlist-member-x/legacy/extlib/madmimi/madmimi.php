<?php

/**
 * MadMimi API Interface
 */
class WPMadMimi {
	/**
	 * API Username.
	 *
	 * @var string
	 */
	 private $username;

	/**
	 * API Key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor
	 *
	 * @param string $username Username.
	 * @param string $api_key  API Key.
	 */
	public function __construct( $username, $api_key ) {
		$this->username = $username;
		$this->api_key  = $api_key;
	}

	/**
	 * Make API request
	 *
	 * @param  string $request Request to make.
	 * @param  array  $data    Data to pass.
	 * @throws \Exception
	 * @return mixed Request response.
	 */
	public function request( $request, $data = array() ) {
		$requests = array(
			'/audience_lists/lists.json'         => array( 'type' => 'GET' ),
			'/audience_lists/{name_of_list}/add' => array( 'type' => 'POST' ),
			'/audience_lists/{name_of_list}/remove?email={email_to_remove}' => array( 'type' => 'POST' ),
		);

		$url = 'https://api.madmimi.com';

		$defaults = array(
			'username' => $this->username,
			'api_key'  => $this->api_key,
		);

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		$method = $requests[ $request ]['type'];

		foreach ( $data as $k => $v ) {
			if ( false !== stripos( $request, sprintf( '{%s}', $k ) ) ) {
				$request = str_replace( sprintf( '{%s}', $k ), rawurlencode( $v ), $request );
			}
		}

		switch ( $method ) {
			case 'GET':
				$defaults = array_merge( $defaults, $data );
				$url      = add_query_arg( $defaults, $url . $request );
				$resp     = wp_remote_get( $url, array( 'timeout' => 5 ) );
				if ( is_wp_error( $resp ) ) {
					throw new \Exception( $resp->get_error_message() );
				}
				$this->check_code_is_2xx( $resp );
				return json_decode( wp_remote_retrieve_body( $resp ) );
				break;
			case 'POST':
				$url .= $request;
				$data = array_merge( $defaults, $data );
				$resp = wp_remote_post(
					$url,
					array(
						'timeout' => 5,
						'body'    => http_build_query( $data ),
					)
				);
				if ( is_wp_error( $resp ) ) {
					throw new \Exception( $resp->get_error_message() );
				}
				$this->check_code_is_2xx( $resp );
				return json_decode( wp_remote_retrieve_body( $resp ) );
				return $resp;
				break;
			default:
				throw new \Exception( __( 'Invalid request', 'wishlist-member' ) );
				break;
		}

	}

	/**
	 * Checks if response code is in the 2xx range
	 *
	 * @param  array $resp Request response.
	 * @throws \Exception
	 */
	private function check_code_is_2xx( $resp ) {
		$code = wp_remote_retrieve_response_code( $resp );
		if ( $code < 200 || $code >= 300 ) {
			throw new \Exception( wp_remote_retrieve_body( $resp ) );
		}
	}

	/**
	 * Retrieve lists
	 *
	 * @return array
	 */
	public function get_lists() {
		$lists = $this->request( '/audience_lists/lists.json' );
		return is_array( $lists ) ? $lists : array();
	}

	/**
	 * Add contact to lists
	 *
	 * @param array  $lists Array of lists ids.
	 * @param string $email Email address.
	 * @param string $fName First name.
	 * @param string $lName Last name.
	 */
	public function add_to_lists( $lists, $email, $fName, $lName ) {
		foreach ( $lists as $l ) {
			$this->add_to_list( $l, $email, $fName, $lName );
		}
	}

	/**
	 * Add contact to single list
	 *
	 * @param array  $list  List id.
	 * @param string $email Email address.
	 * @param string $fName First name.
	 * @param string $lName Last name.
	 * @return mixed Request response.
	 */
	public function add_to_list( $list, $email, $fName, $lName ) {
		return $this->request(
			'/audience_lists/{name_of_list}/add',
			array(
				'name_of_list' => $list,
				'email'        => $email,
				'firstName'    => $fName,
				'lastName'     => $lName,
			)
		);
	}

	/**
	 * Remove contact from lists
	 *
	 * @param  array  $lists Array of list ids.
	 * @param  string $email Email address.
	 */
	public function remove_from_lists( $lists, $email ) {
		foreach ( $lists as $l ) {
			$this->remove_from_list( $l, $email );
		}
	}

	/**
	 * Remove contact from single list
	 *
	 * @param  string $list  List id.
	 * @param  string $email Email address.
	 * @return mixed  Request response.
	 */
	public function remove_from_list( $list, $email ) {
		return $this->request(
			'/audience_lists/{name_of_list}/remove?email={email_to_remove}',
			array(
				'name_of_list'    => $list,
				'email_to_remove' => $email,
			)
		);
	}
}
