<?php
/*
* +-------------------------------------------------------------------------------------+
* | NOTE: This library has been modified by WishList Products to work with reCAPTCHA v3 |
* +-------------------------------------------------------------------------------------+
*
* This is a PHP library that handles calling reCAPTCHA.
*    - Documentation and latest version
*          http://recaptcha.net/plugins/php/
*    - Get a reCAPTCHA API Key
*          https://www.google.com/recaptcha/admin/create
*    - Discussion group
*          http://groups.google.com/group/recaptcha
*
* Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
* AUTHORS:
*   Mike Crawford
*   Ben Maurer
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/

/**
 * The reCAPTCHA server URL's
 */
define( 'RECAPTCHA_API_SERVER', 'http://www.google.com/recaptcha/api' );
define( 'RECAPTCHA_API_SECURE_SERVER', 'https://www.google.com/recaptcha/api' );
define( 'RECAPTCHA_VERIFY_SERVER', 'www.google.com' );

/**
* Encodes the given data into a query string format
*
* @param $data - array of string elements to be encoded
* @return string - encoded request
*/
if ( ! function_exists( '_recaptcha_qsencode' ) ) {
	function _recaptcha_qsencode( $data ) {
		$req = '';
		foreach ( $data as $key => $value ) {
			$req .= $key . '=' . urlencode( stripslashes( $value ) ) . '&';
		}

		// Cut the last '&'
		$req = substr( $req, 0, strlen( $req ) - 1 );
		return $req;
	}
}



/**
* Submits an HTTP POST to a reCAPTCHA server
*
* @param string $host
* @param string $path
* @param array $data
* @param int port
* @return array response
*/
if ( ! function_exists( '_recaptcha_http_post' ) ) {
	function _recaptcha_http_post( $host, $path, $data, $port = 80 ) {

		$req = _recaptcha_qsencode( $data );

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= 'Content-Length: ' . strlen( $req ) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';
		$fs       = @fsockopen( $host, $port, $errno, $errstr, 10 );
		if ( false === $fs ) {
			die( 'Could not open socket' );
		}

		fwrite( $fs, $http_request );

		while ( ! feof( $fs ) ) {
			$response .= fgets( $fs, 1160 ); // One TCP-IP packet.
		}
		fclose( $fs );
		$response = explode( "\r\n\r\n", $response, 2 );

		return $response;
	}
}


/**
* Gets the challenge HTML (javascript and non-javascript version).
* This is called from the browser, and the resulting reCAPTCHA HTML widget
* is embedded within the HTML form it was called from.
*
* @param string $pubkey A public key for reCAPTCHA
* @param string $error The error given by reCAPTCHA (optional, default is null)
* @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

* @return string - The HTML to be embedded in the user's form.
*/
if ( ! function_exists( 'recaptcha_get_html' ) ) {
	function recaptcha_get_html( $pubkey, $error = null, $use_ssl = false ) {
		if ( null == $pubkey || empty( $pubkey ) ) {
			die( "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>" );
		}

		if ( $use_ssl ) {
			$server = RECAPTCHA_API_SECURE_SERVER;
		} else {
			$server = RECAPTCHA_API_SERVER;
		}

		$errorpart = '';
		if ( $error ) {
			$errorpart = '&amp;error=' . $error;
		}

		// Check if $pubkey is for v2 by trying to load api.js the way v3 would.
		// If the key is a valid v2 key then it should return a response code of 400.
		$v2 = 400 == wp_remote_retrieve_response_code( wp_remote_get( 'https://www.google.com/recaptcha/api.js?render=' . $pubkey ) );

		// If it's a REST_REQUEST, then return $html without the reCAPTCHA script because they are just editing the post in Gutenberg.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $html;
		}

		// Generate html according to key's version.
		if ( $v2 ) {
			// V2.
			$html = <<<STRING
<input type="hidden" name="recaptcha-version" value="2">
<div class="g-recaptcha" data-sitekey={$pubkey}></div>

STRING;
			$html = str_replace( '<script ', '<script async defer ', wlm_print_script( 'https://www.google.com/recaptcha/api.js', true ) ) . $html;
		} else {
			$html = <<<STRING
<input type="hidden" id="grecpatcha-{$pubkey}" name="g-recaptcha-response" value="">
<input type="hidden" name="recaptcha-version" value="3">
<script type="text/javascript">
	document.getElementById('grecpatcha-{$pubkey}').form.onsubmit=function() {
		var form = this;
		grecaptcha.ready(function() {
			grecaptcha.execute('{$pubkey}', {action: 'wishlistmember_registration_form'}).then(function(token) {
				document.getElementById('grecpatcha-{$pubkey}').value = token;
				form.submit();
			});
		});
		return false;
	}
</script>

STRING;
			$html = wlm_print_script( "https://www.google.com/recaptcha/api.js?render={$pubkey}", true ) . $html;
		}

		// Return html.
		return $html;
	}
}




/**
* A ReCaptchaResponse is returned from recaptcha_check_answer()
*/
if ( ! class_exists( 'ReCaptchaResponse' ) ) {
	class ReCaptchaResponse {
		public $is_valid;
		public $error;
	}
}


/**
* Calls an HTTP POST function to verify if the user's guess was correct
*
* @param string $privkey
* @param string $remoteip
* @param string $challenge
* @param string $response
* @param array $extra_params an array of extra variables to post to the server
* @return ReCaptchaResponse
*/
if ( ! function_exists( 'recaptcha_check_answer' ) ) {
	function recaptcha_check_answer( $privkey, $remoteip, $challenge, $response, $extra_params = array() ) {
		if ( null == $privkey || empty( $privkey ) ) {
			die( "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>" );
		}

		if ( null == $remoteip || empty( $remoteip ) ) {
			die( 'For security reasons, you must pass the remote ip to reCAPTCHA' );
		}

		// Discard spam submissions.
		if ( null == $challenge || 0 == strlen( $challenge ) || null == $response || 0 == strlen( $response ) ) {
			$recaptcha_response           = new ReCaptchaResponse();
			$recaptcha_response->is_valid = false;
			$recaptcha_response->error    = 'incorrect-captcha-sol';
			return $recaptcha_response;
		}

		$response = _recaptcha_http_post(
			RECAPTCHA_VERIFY_SERVER,
			'/recaptcha/api/verify',
			array(
				'privatekey' => $privkey,
				'remoteip'   => $remoteip,
				'challenge'  => $challenge,
				'response'   => $response,
			) + $extra_params
		);

		$answers            = explode( "\n", $response [1] );
		$recaptcha_response = new ReCaptchaResponse();

		if ( 'true' === trim( $answers [0] ) ) {
			$recaptcha_response->is_valid = true;
		} else {
			$recaptcha_response->is_valid = false;
			$recaptcha_response->error    = $answers [1];
		}
		return $recaptcha_response;

	}
}

if ( ! function_exists( 'recaptcha_verify' ) ) {
	function recaptcha_verify( $privkey, $remoteip, $response, $extra_params = array() ) {
		if ( null == $privkey || empty( $privkey ) ) {
			die( "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>" );
		}

		if ( null == $remoteip || empty( $remoteip ) ) {
			die( 'For security reasons, you must pass the remote ip to reCAPTCHA' );
		}
		if ( null == $response || empty( $response ) || 0 == strlen( $response ) ) {
			$recaptcha_response           = new ReCaptchaResponse();
			$recaptcha_response->is_valid = false;
			$recaptcha_response->error    = 'incorrect-captcha-sol';
			return $recaptcha_response;
		}

		$recaptcha_response = new ReCaptchaResponse();
		$response           = json_decode( $GLOBALS['WishListMemberInstance']->ReadURL( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $privkey . '&response=' . $response . '&remoteip=' . $remoteip ) );
		// $response           = json_decode( file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $privkey . '&response=' . $response . '&remoteip=' . $remoteip ) );
		if ( $response && $response->success ) {
			$recaptcha_response->is_valid = true;
		} else {
			$recaptcha_response->is_valid = false;
		}

		return $recaptcha_response;
		/* end recaptcha */
	}
}
/**
* Gets a URL where the user can sign up for reCAPTCHA. If your application
* has a configuration page where you enter a key, you should provide a link
* using this function.
*
* @param string $domain The domain where the page is hosted
* @param string $appname The name of your application
*/
if ( ! function_exists( 'recaptcha_get_signup_url' ) ) {
	function recaptcha_get_signup_url( $domain = null, $appname = null ) {
		return 'https://www.google.com/recaptcha/admin/create?' . _recaptcha_qsencode(
			array(
				'domains' => $domain,
				'app'     => $appname,
			)
		);
	}
}

if ( ! function_exists( '_recaptcha_aes_pad' ) ) {
	function _recaptcha_aes_pad( $val ) {
		$block_size = 16;
		$numpad     = $block_size - ( strlen( $val ) % $block_size );
		return str_pad( $val, strlen( $val ) + $numpad, chr( $numpad ) );
	}
}
/* Mailhide related code */

if ( ! function_exists( '_recaptcha_aes_encrypt' ) ) {
	function _recaptcha_aes_encrypt( $val, $ky, $mcrypt = true ) {

		if ( ! function_exists( 'openssl_encrypt' ) ) {
			die( 'To use reCAPTCHA Mailhide, you need to have the openssl php module installed.' );
		}
		$mode = OPENSSL_RAW_DATA;
		$enc  = 'AES-128-CBC';
		$val  = _recaptcha_aes_pad( $val );
		return openssl_encrypt( $val, $enc, $ky, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0" );
	}
}

if ( ! function_exists( '_recaptcha_mailhide_urlbase64' ) ) {
	function _recaptcha_mailhide_urlbase64( $x ) {
		return strtr( base64_encode( $x ), '+/', '-_' );
	}
}

/* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
if ( ! function_exists( 'recaptcha_mailhide_url' ) ) {
	function recaptcha_mailhide_url( $pubkey, $privkey, $email ) {
		if ( empty( $pubkey ) || null == $pubkey || empty( $privkey ) || null == $privkey ) {
			die(
				'To use reCAPTCHA Mailhide, you have to sign up for a public and private key, ' .
				"you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>"
			);
		}

		$ky        = pack( 'H*', $privkey );
		$cryptmail = _recaptcha_aes_encrypt( $email, $ky );

		return 'http://www.google.com/recaptcha/mailhide/d?k=' . $pubkey . '&c=' . _recaptcha_mailhide_urlbase64( $cryptmail );
	}
}

/**
* Gets the parts of the email to expose to the user.
* eg, given johndoe@example,com return ["john", "example.com"].
* the email is then displayed as john...@example.com
*/
if ( ! function_exists( '_recaptcha_mailhide_email_parts' ) ) {
	function _recaptcha_mailhide_email_parts( $email ) {
		$arr = preg_split( '/@/', $email );

		if ( strlen( $arr[0] ) <= 4 ) {
			$arr[0] = substr( $arr[0], 0, 1 );
		} elseif ( strlen( $arr[0] ) <= 6 ) {
			$arr[0] = substr( $arr[0], 0, 3 );
		} else {
			$arr[0] = substr( $arr[0], 0, 4 );
		}
		return $arr;
	}
}

/**
* Gets html to display an email address given a public an private key.
* to get a key, go to:
*
* Http://www.google.com/recaptcha/mailhide/apikey
*/
if ( ! function_exists( 'recaptcha_mailhide_html' ) ) {
	function recaptcha_mailhide_html( $pubkey, $privkey, $email ) {
		$emailparts = _recaptcha_mailhide_email_parts( $email );
		$url        = recaptcha_mailhide_url( $pubkey, $privkey, $email );

		return htmlentities( $emailparts[0] ) . "<a href='" . htmlentities( $url ) .
		"' onclick=\"window.open('" . htmlentities( $url ) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this email address\">...</a>@" . htmlentities( $emailparts [1] );

	}
}
