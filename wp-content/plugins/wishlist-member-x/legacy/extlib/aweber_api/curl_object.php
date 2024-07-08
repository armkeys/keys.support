<?php

/**
 * CurlInterface
 *
 * An object-oriented shim that wraps the standard PHP cURL library.
 * 
 * This interface has been created so that cURL functionality can be stubbed
 * out for unit testing, or swapped for an alternative library.
 * 
 * @see curl
 * @package
 * @version $id$
 */
interface CurlInterface {

	/**
	 * ErrNo
	 *
	 * Encapsulates curl_errno - Returns the last error number
	 *
	 * @param resource $ch - A cURL handle returned by init.
	 * @return the error number or 0 if no error occured.
	 */
	public function errno( $ch);	
	
	/**
	 * Error
	 *
	 * Encapsulates curl_error - Return last error string
	 *
	 * @param resource $ch - A cURL handle returned by init.
	 * @return the error messge or '' if no error occured.
	 */	
	public function error( $ch);

	/**
	 * Execute
	 *
	 * Encapsulates curl_exec - Perform a cURL session.
	 *
	 * @param resource $ch - A cURL handle returned by init.
	 * @return TRUE on success, FALSE on failure.
	 */	
	public function execute( $ch);

	/**
	 * Init
	 *
	 * Encapsulates curl_init - Initialize a cURL session.
	 *
	 * @param string $url - url to use.
	 * @return cURL handle on success, FALSE on failure.
	 */	
	public function init( $url);
	
	/**
	 * Setopt
	 *
	 * Encapsulates curl_setopt - Set an option for cURL transfer.
	 *
	 * @param resource $ch - A cURL handle returned by init.
	 * @param int $opt - The CURLOPT to set.
	 * @param mixed $value - The value to set.
	 * @return True on success, FALSE on failure.
	 */		
	public function setopt ( $ch, $option, $value);
}


/**
 * CurlObject
 *
 * A concrete implementation of CurlInterface using the PHP cURL library.
 *
 * @package
 * @version $id$
 */
class CurlObject implements CurlInterface {
	
	public function errno( $ch) {
		return curl_errno($ch);
	}	

	public function error( $ch) {
		return curl_error($ch);
	}	
	
	public function execute( $ch) {
		return curl_exec($ch);
	}

	public function init( $url) {
		return curl_init($url); 
	}
	
	public function setopt ( $ch, $option, $value) {
		return curl_setopt($ch, $option, $value);
	}

}


