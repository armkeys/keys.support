<?php




/**
* Transport class for sending/receiving data via HTTP and HTTPS
* NOTE: PHP must be compiled with the CURL extension for HTTPS support
*
* @version  $Id: class.soap_transport_http.php,v 1.68 2010/04/26 20:15:08 snichol Exp $
*/
class soap_transport_http extends nusoap_base {

	public $url              = '';
	public $uri              = '';
	public $digest_uri       = '';
	public $scheme           = '';
	public $host             = '';
	public $port             = '';
	public $path             = '';
	public $request_method   = 'POST';
	public $protocol_version = '1.0';
	public $encoding         = '';
	public $outgoing_headers = array();
	public $incoming_headers = array();
	public $incoming_cookies = array();
	public $outgoing_payload = '';
	public $incoming_payload = '';
	public $response_status_line;	// HTTP response status line
	public $useSOAPAction        = true;
	public $persistentConnection = false;
	public $ch                   = false;	// cURL handle
	public $ch_options           = array();	// cURL custom options
	public $use_curl             = false;		// force cURL use
	public $proxy                = null;			// proxy information (associative array)
	public $username             = '';
	public $password             = '';
	public $authtype             = '';
	public $digestRequest        = array();
	public $certRequest          = array();	// keys must be cainfofile (optional), sslcertfile, sslkeyfile, passphrase, certpassword (optional), verifypeer (optional), verifyhost (optional)
								// Cainfofile: certificate authority file, e.g. '$pathToPemFiles/rootca.pem'
								// Sslcertfile: SSL certificate file, e.g. '$pathToPemFiles/mycert.pem'
								// Sslkeyfile: SSL key file, e.g. '$pathToPemFiles/mykey.pem'
								// Passphrase: SSL key password/passphrase.
								// Certpassword: SSL certificate password.
								// Verifypeer: default is 1.
								// Verifyhost: default is 1.

	/**
	* Constructor
	*
	* @param string $url The URL to which to connect
	* @param array $curl_options User-specified cURL options
	* @param boolean $use_curl Whether to try to force cURL use
	*/
	public function __construct( $url, $curl_options = null, $use_curl = false) {
		parent::nusoap_base();
		$this->debug("ctor url=$url use_curl=$use_curl curl_options:");
		$this->appendDebug($this->varDump($curl_options));
		$this->setURL($url);
		if (is_array($curl_options)) {
			$this->ch_options = $curl_options;
		}
		$this->use_curl = $use_curl;
		preg_match('/\$Revision: ([^ ]+)/', $this->revision, $rev);
		$this->setHeader('User-Agent', $this->title . '/' . $this->version . ' (' . $rev[1] . ')');
	}

	/**
	* Sets a cURL option
	*
	* @param	mixed $option The cURL option (always integer?)
	* @param	mixed $value The cURL option value
	*/
	public function setCurlOption( $option, $value) {
		$this->debug("setCurlOption option=$option, value=");
		$this->appendDebug($this->varDump($value));
		curl_setopt($this->ch, $option, $value);
	}

	/**
	* Sets an HTTP header
	*
	* @param string $name The name of the header
	* @param string $value The value of the header
	*/
	public function setHeader( $name, $value) {
		$this->outgoing_headers[$name] = $value;
		$this->debug("set header $name: $value");
	}

	/**
	* Unsets an HTTP header
	*
	* @param string $name The name of the header
	*/
	public function unsetHeader( $name) {
		if (isset($this->outgoing_headers[$name])) {
			$this->debug("unset header $name");
			unset($this->outgoing_headers[$name]);
		}
	}

	/**
	* Sets the URL to which to connect
	*
	* @param string $url The URL to which to connect
	*/
	public function setURL( $url) {
		$this->url = $url;

		$u = parse_url($url);
		foreach ($u as $k => $v) {
			$this->debug("parsed URL $k = $v");
			$this->$k = $v;
		}

		// Add any GET params to path.
		if (isset($u['query']) && '' != $u['query']) {
			$this->path .= '?' . $u['query'];
		}

		// Set default port.
		if (!isset($u['port'])) {
			if ('https' === $u['scheme']) {
				$this->port = 443;
			} else {
				$this->port = 80;
			}
		}

		$this->uri        = $this->path;
		$this->digest_uri = $this->uri;

		// Build headers.
		if (!isset($u['port'])) {
			$this->setHeader('Host', $this->host);
		} else {
			$this->setHeader('Host', $this->host . ':' . $this->port);
		}

		if (isset($u['user']) && '' != $u['user']) {
			$this->setCredentials(urldecode($u['user']), isset($u['pass']) ? urldecode($u['pass']) : '');
		}
	}

	/**
	* Gets the I/O method to use
	*
	* @return	string	I/O method to use (socket|curl|unknown)
	*/
	public function io_method() {
		if ($this->use_curl || ( 'https' === $this->scheme ) || ( 'http' === $this->scheme && 'ntlm' === $this->authtype ) || ( 'http' === $this->scheme && is_array($this->proxy) && 'ntlm' === $this->proxy['authtype'] )) {
			return 'curl';
		}
		if (( 'http' === $this->scheme || 'ssl' === $this->scheme ) && 'ntlm' !== $this->authtype && ( !is_array($this->proxy) || 'ntlm' !== $this->proxy['authtype'] )) {
			return 'socket';
		}
		return 'unknown';
	}

	/**
	* Establish an HTTP connection
	*
	* @param    integer $timeout set connection timeout in seconds
	* @param	integer $response_timeout set response timeout in seconds
	* @return	boolean true if connected, false if not
	*/
	public function connect( $connection_timeout = 0, $response_timeout = 30) {
		// For PHP 4.3 with OpenSSL, change https scheme to ssl, then treat like.
		// "regular" socket.
		// TODO: disabled for now because OpenSSL must be *compiled* in (not just.
		// Loaded), and until PHP5 stream_get_wrappers is not available.
// If ($this->scheme == 'https') {
// If (version_compare(phpversion(), '4.3.0') >= 0) {
// If (extension_loaded('openssl')) {
//		  			$this->scheme = 'ssl';
//		  			$this->debug('Using SSL over OpenSSL');
//		  		}
//		  	}
//		}
		$this->debug("connect connection_timeout $connection_timeout, response_timeout $response_timeout, scheme $this->scheme, host $this->host, port $this->port");
		if ('socket' === $this->io_method()) {
			if (!is_array($this->proxy)) {
				$host = $this->host;
				$port = $this->port;
			} else {
				$host = $this->proxy['host'];
				$port = $this->proxy['port'];
			}

		  // Use persistent connection.
			if ($this->persistentConnection && isset($this->fp) && is_resource($this->fp)) {
				if (!feof($this->fp)) {
					$this->debug('Re-use persistent connection');
					return true;
				}
				fclose($this->fp);
				$this->debug('Closed persistent connection at EOF');
			}

		  // Munge host if using OpenSSL.
			if ('ssl' === $this->scheme) {
				$host = 'ssl://' . $host;
			}
		  $this->debug('calling fsockopen with host ' . $host . ' connection_timeout ' . $connection_timeout);

		  // Open socket.
			if ($connection_timeout > 0) {
				$this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str, $connection_timeout);
			} else {
				$this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str);
			}

		  // Test pointer.
			if (!$this->fp) {
				$msg = 'Couldn\'t open socket connection to server ' . $this->url;
				if ($this->errno) {
					$msg .= ', Error (' . $this->errno . '): ' . $this->error_str;
				} else {
					$msg .= ' prior to connect().  This is often a problem looking up the host name.';
				}
				$this->debug($msg);
				$this->setError($msg);
				return false;
			}

		  // Set response timeout.
		  $this->debug('set response timeout to ' . $response_timeout);
		  socket_set_timeout( $this->fp, $response_timeout);

		  $this->debug('socket connected');
		  return true;
		} elseif ('curl' === $this->io_method()) {
			if (!extension_loaded('curl')) {
//          $this->setError('cURL Extension, or OpenSSL extension w/ PHP version >= 4.3 is required for HTTPS');
				$this->setError('The PHP cURL Extension is required for HTTPS or NLTM.  You will need to re-build or update your PHP to include cURL or change php.ini to load the PHP cURL extension.');
				return false;
			}
		  // Avoid warnings when PHP does not have these options.
			if (defined('CURLOPT_CONNECTIONTIMEOUT')) {
			  $CURLOPT_CONNECTIONTIMEOUT = CURLOPT_CONNECTIONTIMEOUT;
			} else {
$CURLOPT_CONNECTIONTIMEOUT = 78;
			}
			if (defined('CURLOPT_HTTPAUTH')) {
			  $CURLOPT_HTTPAUTH = CURLOPT_HTTPAUTH;
			} else {
$CURLOPT_HTTPAUTH = 107;
			}
			if (defined('CURLOPT_PROXYAUTH')) {
			  $CURLOPT_PROXYAUTH = CURLOPT_PROXYAUTH;
			} else {
$CURLOPT_PROXYAUTH = 111;
			}
			if (defined('CURLAUTH_BASIC')) {
			  $CURLAUTH_BASIC = CURLAUTH_BASIC;
			} else {
$CURLAUTH_BASIC = 1;
			}
			if (defined('CURLAUTH_DIGEST')) {
			  $CURLAUTH_DIGEST = CURLAUTH_DIGEST;
			} else {
$CURLAUTH_DIGEST = 2;
			}
			if (defined('CURLAUTH_NTLM')) {
			  $CURLAUTH_NTLM = CURLAUTH_NTLM;
			} else {
$CURLAUTH_NTLM = 8;
			}

		  $this->debug('connect using cURL');
		  // Init CURL.
		  $this->ch = curl_init();
		  // Set url.
		  $hostURL = ( '' != $this->port ) ? "$this->scheme://$this->host:$this->port" : "$this->scheme://$this->host";
		  // Add path.
		  $hostURL .= $this->path;
		  $this->setCurlOption(CURLOPT_URL, $hostURL);
		  // Follow location headers (re-directs)
			if (ini_get('safe_mode') || ini_get('open_basedir')) {
				$this->debug('safe_mode or open_basedir set, so do not set CURLOPT_FOLLOWLOCATION');
				$this->debug('safe_mode = ');
				$this->appendDebug($this->varDump(ini_get('safe_mode')));
				$this->debug('open_basedir = ');
				$this->appendDebug($this->varDump(ini_get('open_basedir')));
			} else {
				$this->setCurlOption(CURLOPT_FOLLOWLOCATION, 1);
			}
		  // Ask for headers in the response output.
		  $this->setCurlOption(CURLOPT_HEADER, 1);
		  // Ask for the response output as the return value.
		  $this->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
		  // Encode.
		  // We manage this ourselves through headers and encoding.
// If(function_exists('gzuncompress')){
//          $this->setCurlOption(CURLOPT_ENCODING, 'deflate');
//      }
		  // Persistent connection.
			if ($this->persistentConnection) {
				// I believe the following comment is now bogus, having applied to.
				// The code when it used CURLOPT_CUSTOMREQUEST to send the request.
				// The way we send data, we cannot use persistent connections, since.
				// There will be some "junk" at the end of our request.
				//$this->setCurlOption(CURL_HTTP_VERSION_1_1, true);
				$this->persistentConnection = false;
				$this->setHeader('Connection', 'close');
			}
		  // Set timeouts.
			if (0 != $connection_timeout) {
				$this->setCurlOption($CURLOPT_CONNECTIONTIMEOUT, $connection_timeout);
			}
			if (0 != $response_timeout) {
				$this->setCurlOption(CURLOPT_TIMEOUT, $response_timeout);
			}

			if ('https' === $this->scheme) {
				$this->debug('set cURL SSL verify options');
				// Recent versions of cURL turn on peer/host checking by default,
				// While PHP binaries are not compiled with a default location for the.
				// CA cert bundle, so disable peer/host checking.
				//$this->setCurlOption(CURLOPT_CAINFO, 'f:\php-4.3.2-win32\extensions\curl-ca-bundle.crt');
				$this->setCurlOption(CURLOPT_SSL_VERIFYPEER, 0);
				$this->setCurlOption(CURLOPT_SSL_VERIFYHOST, 0);

				// Support client certificates (thanks Tobias Boes, Doug Anarino, Eryan Ariobowo)
				if ('certificate' === $this->authtype) {
					$this->debug('set cURL certificate options');
					if (isset($this->certRequest['cainfofile'])) {
						$this->setCurlOption(CURLOPT_CAINFO, $this->certRequest['cainfofile']);
					}
					if (isset($this->certRequest['verifypeer'])) {
						$this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->certRequest['verifypeer']);
					} else {
						$this->setCurlOption(CURLOPT_SSL_VERIFYPEER, 1);
					}
					if (isset($this->certRequest['verifyhost'])) {
						$this->setCurlOption(CURLOPT_SSL_VERIFYHOST, $this->certRequest['verifyhost']);
					} else {
						$this->setCurlOption(CURLOPT_SSL_VERIFYHOST, 1);
					}
					if (isset($this->certRequest['sslcertfile'])) {
						$this->setCurlOption(CURLOPT_SSLCERT, $this->certRequest['sslcertfile']);
					}
					if (isset($this->certRequest['sslkeyfile'])) {
						$this->setCurlOption(CURLOPT_SSLKEY, $this->certRequest['sslkeyfile']);
					}
					if (isset($this->certRequest['passphrase'])) {
						$this->setCurlOption(CURLOPT_SSLKEYPASSWD, $this->certRequest['passphrase']);
					}
					if (isset($this->certRequest['certpassword'])) {
						$this->setCurlOption(CURLOPT_SSLCERTPASSWD, $this->certRequest['certpassword']);
					}
				}
			}
			if ($this->authtype && ( 'certificate' !== $this->authtype )) {
				if ($this->username) {
					$this->debug('set cURL username/password');
					$this->setCurlOption(CURLOPT_USERPWD, "$this->username:$this->password");
				}
				if ('basic' === $this->authtype) {
					$this->debug('set cURL for Basic authentication');
					$this->setCurlOption($CURLOPT_HTTPAUTH, $CURLAUTH_BASIC);
				}
				if ('digest' === $this->authtype) {
					$this->debug('set cURL for digest authentication');
					$this->setCurlOption($CURLOPT_HTTPAUTH, $CURLAUTH_DIGEST);
				}
				if ('ntlm' === $this->authtype) {
					$this->debug('set cURL for NTLM authentication');
					$this->setCurlOption($CURLOPT_HTTPAUTH, $CURLAUTH_NTLM);
				}
			}
			if (is_array($this->proxy)) {
				$this->debug('set cURL proxy options');
				if ('' != $this->proxy['port']) {
					$this->setCurlOption(CURLOPT_PROXY, $this->proxy['host'] . ':' . $this->proxy['port']);
				} else {
					$this->setCurlOption(CURLOPT_PROXY, $this->proxy['host']);
				}
				if ($this->proxy['username'] || $this->proxy['password']) {
					$this->debug('set cURL proxy authentication options');
					$this->setCurlOption(CURLOPT_PROXYUSERPWD, $this->proxy['username'] . ':' . $this->proxy['password']);
					if ('basic' === $this->proxy['authtype']) {
						$this->setCurlOption($CURLOPT_PROXYAUTH, $CURLAUTH_BASIC);
					}
					if ('ntlm' === $this->proxy['authtype']) {
						$this->setCurlOption($CURLOPT_PROXYAUTH, $CURLAUTH_NTLM);
					}
				}
			}
		  $this->debug('cURL connection set up');
		  return true;
		} else {
		  $this->setError('Unknown scheme ' . $this->scheme);
		  $this->debug('Unknown scheme ' . $this->scheme);
		  return false;
		}
	}

	/**
	* Sends the SOAP request and gets the SOAP response via HTTP[S]
	*
	* @param    string $data message data
	* @param    integer $timeout set connection timeout in seconds
	* @param	integer $response_timeout set response timeout in seconds
	* @param	array $cookies cookies to send
	* @return	string data
	*/
	public function send( $data, $timeout = 0, $response_timeout = 30, $cookies = null) {

		$this->debug('entered send() with data of length: ' . strlen($data));

		$this->tryagain = true;
		$tries          = 0;
		while ($this->tryagain) {
			$this->tryagain = false;
			if ($tries++ < 2) {
				// Make connnection.
				if (!$this->connect($timeout, $response_timeout)) {
					return false;
				}

				// Send request.
				if (!$this->sendRequest($data, $cookies)) {
					return false;
				}

				// Get response.
				$respdata = $this->getResponse();
			} else {
				$this->setError("Too many tries to get an OK response ($this->response_status_line)");
			}
		}
		$this->debug('end of send()');
		return $respdata;
	}


	/**
	* Sends the SOAP request and gets the SOAP response via HTTPS using CURL
	*
	* @param    string $data message data
	* @param    integer $timeout set connection timeout in seconds
	* @param	integer $response_timeout set response timeout in seconds
	* @param	array $cookies cookies to send
	* @return	string data
	* @deprecated
	*/
	public function sendHTTPS( $data, $timeout = 0, $response_timeout = 30, $cookies) {
		return $this->send($data, $timeout, $response_timeout, $cookies);
	}

	/**
	* If authenticating, set user credentials here
	*
	* @param    string $username
	* @param    string $password
	* @param	string $authtype (basic|digest|certificate|ntlm)
	* @param	array $digestRequest (keys must be nonce, nc, realm, qop)
	* @param	array $certRequest (keys must be cainfofile (optional), sslcertfile, sslkeyfile, passphrase, certpassword (optional), verifypeer (optional), verifyhost (optional): see corresponding options in cURL docs)
	*/
	public function setCredentials( $username, $password, $authtype = 'basic', $digestRequest = array(), $certRequest = array()) {
		$this->debug("setCredentials username=$username authtype=$authtype digestRequest=");
		$this->appendDebug($this->varDump($digestRequest));
		$this->debug('certRequest=');
		$this->appendDebug($this->varDump($certRequest));
		// Cf. RFC 2617.
		if ('basic' === $authtype) {
			$this->setHeader('Authorization', 'Basic ' . base64_encode(str_replace(':', '', $username) . ':' . $password));
		} elseif ('digest' === $authtype) {
			if (isset($digestRequest['nonce'])) {
				$digestRequest['nc'] = isset($digestRequest['nc']) ? $digestRequest['nc']++ : 1;

				// Calculate the Digest hashes (calculate code based on digest implementation found at: http://www.rassoc.com/gregr/weblog/stories/2002/07/09/webServicesSecurityHttpDigestAuthenticationWithoutActiveDirectory.html)

				// A1 = unq(username-value) ":" unq(realm-value) ":" passwd.
				$A1 = $username . ':' . ( isset($digestRequest['realm']) ? $digestRequest['realm'] : '' ) . ':' . $password;

				// H(A1) = MD5(A1)
				$HA1 = md5($A1);

				// A2 = Method ":" digest-uri-value.
				$A2 = $this->request_method . ':' . $this->digest_uri;

				// H(A2)
				$HA2 =  md5($A2);

				// KD(secret, data) = H(concat(secret, ":", data))
				// If qop == auth:
				// Request-digest  = <"> < KD ( H(A1),     unq(nonce-value)
				//                              ":" nc-value.
				//                              ":" unq(cnonce-value)
				//                              ":" unq(qop-value)
				//                              ":" H(A2)
				//                            ) <">
				// If qop is missing,
				// Request-digest  = <"> < KD ( H(A1), unq(nonce-value) ":" H(A2) ) > <">

				$unhashedDigest = '';
				$nonce          = isset($digestRequest['nonce']) ? $digestRequest['nonce'] : '';
				$cnonce         = $nonce;
				if ('' != $digestRequest['qop']) {
					$unhashedDigest = $HA1 . ':' . $nonce . ':' . sprintf('%08d', $digestRequest['nc']) . ':' . $cnonce . ':' . $digestRequest['qop'] . ':' . $HA2;
				} else {
					$unhashedDigest = $HA1 . ':' . $nonce . ':' . $HA2;
				}

				$hashedDigest = md5($unhashedDigest);

				$opaque = '';
				if (isset($digestRequest['opaque'])) {
					$opaque = ', opaque="' . $digestRequest['opaque'] . '"';
				}

				$this->setHeader('Authorization', 'Digest username="' . $username . '", realm="' . $digestRequest['realm'] . '", nonce="' . $nonce . '", uri="' . $this->digest_uri . $opaque . '", cnonce="' . $cnonce . '", nc=' . sprintf('%08x', $digestRequest['nc']) . ', qop="' . $digestRequest['qop'] . '", response="' . $hashedDigest . '"');
			}
		} elseif ('certificate' === $authtype) {
			$this->certRequest = $certRequest;
			$this->debug('Authorization header not set for certificate');
		} elseif ('ntlm' === $authtype) {
			// Do nothing.
			$this->debug('Authorization header not set for ntlm');
		}
		$this->username      = $username;
		$this->password      = $password;
		$this->authtype      = $authtype;
		$this->digestRequest = $digestRequest;
	}

	/**
	* Set the soapaction value
	*
	* @param    string $soapaction
	*/
	public function setSOAPAction( $soapaction) {
		$this->setHeader('SOAPAction', '"' . $soapaction . '"');
	}

	/**
	* Use http encoding
	*
	* @param    string $enc encoding style. supported values: gzip, deflate, or both
	*/
	public function setEncoding( $enc = 'gzip, deflate') {
		if (function_exists('gzdeflate')) {
			$this->protocol_version = '1.1';
			$this->setHeader('Accept-Encoding', $enc);
			if (!isset($this->outgoing_headers['Connection'])) {
				$this->setHeader('Connection', 'close');
				$this->persistentConnection = false;
			}
			// Deprecated as of PHP 5.3.0.
			// Set_magic_quotes_runtime(0);
			$this->encoding = $enc;
		}
	}

	/**
	* Set proxy info here
	*
	* @param    string $proxyhost use an empty string to remove proxy
	* @param    string $proxyport
	* @param	string $proxyusername
	* @param	string $proxypassword
	* @param	string $proxyauthtype (basic|ntlm)
	*/
	public function setProxy( $proxyhost, $proxyport, $proxyusername = '', $proxypassword = '', $proxyauthtype = 'basic') {
		if ($proxyhost) {
			$this->proxy = array(
				'host' => $proxyhost,
				'port' => $proxyport,
				'username' => $proxyusername,
				'password' => $proxypassword,
				'authtype' => $proxyauthtype
			);
			if ('' != $proxyusername && '' != $proxypassword && 'basic' === $proxyauthtype) {
				$this->setHeader('Proxy-Authorization', ' Basic ' . base64_encode($proxyusername . ':' . $proxypassword));
			}
		} else {
			$this->debug('remove proxy');
			$proxy = null;
			unsetHeader('Proxy-Authorization');
		}
	}


	/**
	 * Test if the given string starts with a header that is to be skipped.
	 * Skippable headers result from chunked transfer and proxy requests.
	 *
	 * @param	string $data The string to check.
	 * @returns	boolean	Whether a skippable header was found.
	 */
	public function isSkippableCurlHeader( &$data) {
		$skipHeaders = array(	'HTTP/1.1 100',
								'HTTP/1.0 301',
								'HTTP/1.1 301',
								'HTTP/1.0 302',
								'HTTP/1.1 302',
								'HTTP/1.0 401',
								'HTTP/1.1 401',
								'HTTP/1.0 200 Connection established');
		foreach ($skipHeaders as $hd) {
			$prefix = substr($data, 0, strlen($hd));
			if ($prefix == $hd) {
return true;
			}
		}

		return false;
	}

	/**
	* Decode a string that is encoded w/ "chunked' transfer encoding
	* as defined in RFC2068 19.4.6
	*
	* @param    string $buffer
	* @param    string $lb
	* @returns	string
	* @deprecated
	*/
	public function decodeChunked( $buffer, $lb) {
		// Length := 0.
		$length = 0;
		$new    = '';

		// Read chunk-size, chunk-extension (if any) and CRLF.
		// Get the position of the linebreak.
		$chunkend = strpos($buffer, $lb);
		if (false == $chunkend) {
			$this->debug('no linebreak found in decodeChunked');
			return $new;
		}
		$temp       = substr($buffer, 0, $chunkend);
		$chunk_size = hexdec( trim($temp) );
		$chunkstart = $chunkend + strlen($lb);
		// While (chunk-size > 0) {
		while ($chunk_size > 0) {
			$this->debug("chunkstart: $chunkstart chunk_size: $chunk_size");
			$chunkend = strpos( $buffer, $lb, $chunkstart + $chunk_size);

			// Just in case we got a broken connection.
			if (false == $chunkend) {
				$chunk = substr($buffer, $chunkstart);
				// Append chunk-data to entity-body.
				$new    .= $chunk;
				$length += strlen($chunk);
				break;
			}

			// Read chunk-data and CRLF.
			$chunk = substr($buffer, $chunkstart, $chunkend-$chunkstart);
			// Append chunk-data to entity-body.
			$new .= $chunk;
			// Length := length + chunk-size.
			$length += strlen($chunk);
			// Read chunk-size and CRLF.
			$chunkstart = $chunkend + strlen($lb);

			$chunkend = strpos($buffer, $lb, $chunkstart) + strlen($lb);
			if (false == $chunkend) {
				break; //Just in case we got a broken connection.
			}
			$temp       = substr($buffer, $chunkstart, $chunkend-$chunkstart);
			$chunk_size = hexdec( trim($temp) );
			$chunkstart = $chunkend;
		}
		return $new;
	}

	/**
	 * Writes the payload, including HTTP headers, to $this->outgoing_payload.
	 *
	 * @param	string $data HTTP body
	 * @param	string $cookie_str data for HTTP Cookie header
	 * @return	void
	 */
	public function buildPayload( $data, $cookie_str = '') {
		// Note: for cURL connections, $this->outgoing_payload is ignored,
		// As is the Content-Length header, but these are still created as.
		// Debugging guides.

		// Add content-length header.
		if ('GET' !== $this->request_method) {
			$this->setHeader('Content-Length', strlen($data));
		}

		// Start building outgoing payload:
		if ($this->proxy) {
			$uri = $this->url;
		} else {
			$uri = $this->uri;
		}
		$req = "$this->request_method $uri HTTP/$this->protocol_version";
		$this->debug("HTTP request: $req");
		$this->outgoing_payload = "$req\r\n";

		// Loop thru headers, serializing.
		foreach ($this->outgoing_headers as $k => $v) {
			$hdr = $k . ': ' . $v;
			$this->debug("HTTP header: $hdr");
			$this->outgoing_payload .= "$hdr\r\n";
		}

		// Add any cookies.
		if ('' != $cookie_str) {
			$hdr = 'Cookie: ' . $cookie_str;
			$this->debug("HTTP header: $hdr");
			$this->outgoing_payload .= "$hdr\r\n";
		}

		// Header/body separator.
		$this->outgoing_payload .= "\r\n";

		// Add data.
		$this->outgoing_payload .= $data;
	}

	/**
	* Sends the SOAP request via HTTP[S]
	*
	* @param    string $data message data
	* @param	array $cookies cookies to send
	* @return	boolean	true if OK, false if problem
	*/
	public function sendRequest( $data, $cookies = null) {
		// Build cookie string.
		$cookie_str = $this->getCookiesForRequest($cookies, ( ( 'ssl' === $this->scheme ) || ( 'https' === $this->scheme ) ));

		// Build payload.
		$this->buildPayload($data, $cookie_str);

		if ('socket' === $this->io_method()) {
		  // Send payload.
			if (!fputs($this->fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
				$this->setError('couldn\'t write message data to socket');
				$this->debug('couldn\'t write message data to socket');
				return false;
			}
		  $this->debug('wrote data to socket, length = ' . strlen($this->outgoing_payload));
		  return true;
		} elseif ('curl' === $this->io_method()) {
		  // Set payload.
		  // CURL does say this should only be the verb, and in fact it.
		  // Turns out that the URI and HTTP version are appended to this, which.
		  // Some servers refuse to work with (so we no longer use this method!)
		  //$this->setCurlOption(CURLOPT_CUSTOMREQUEST, $this->outgoing_payload);
		  $curl_headers = array();
			foreach ($this->outgoing_headers as $k => $v) {
				if ('Connection' === $k || 'Content-Length' == $k || 'Host' === $k || 'Authorization' === $k || 'Proxy-Authorization' == $k) {
					$this->debug("Skip cURL header $k: $v");
				} else {
					$curl_headers[] = "$k: $v";
				}
			}
			if ('' != $cookie_str) {
				$curl_headers[] = 'Cookie: ' . $cookie_str;
			}
		  $this->setCurlOption(CURLOPT_HTTPHEADER, $curl_headers);
		  $this->debug('set cURL HTTP headers');
			if ('POST' === $this->request_method) {
				$this->setCurlOption(CURLOPT_POST, 1);
				$this->setCurlOption(CURLOPT_POSTFIELDS, $data);
				$this->debug('set cURL POST data');
			}
		  // Insert custom user-set cURL options.
			foreach ($this->ch_options as $key => $val) {
				$this->setCurlOption($key, $val);
			}

		  $this->debug('set cURL payload');
		  return true;
		}
	}

	/**
	* Gets the SOAP response via HTTP[S]
	*
	* @return	string the response (also sets member variables like incoming_payload)
	*/
	public function getResponse() {
		$this->incoming_payload = '';

		if ('socket' === $this->io_method()) {
		  // Loop until headers have been retrieved.
		  $data = '';
			while (!isset($lb)) {

				// We might EOF during header read.
				if (feof($this->fp)) {
					$this->incoming_payload = $data;
					$this->debug('found no headers before EOF after length ' . strlen($data));
					$this->debug("received before EOF:\n" . $data);
					$this->setError('server failed to send headers');
					return false;
				}

				$tmp    = fgets($this->fp, 256);
				$tmplen = strlen($tmp);
				$this->debug("read line of $tmplen bytes: " . trim($tmp));

				if (0 == $tmplen) {
					$this->incoming_payload = $data;
					$this->debug('socket read of headers timed out after length ' . strlen($data));
					$this->debug('read before timeout: ' . $data);
					$this->setError('socket read of headers timed out');
					return false;
				}

				$data .= $tmp;
				$pos   = strpos($data, "\r\n\r\n");
				if ($pos > 1) {
					$lb = "\r\n";
				} else {
					$pos = strpos($data, "\n\n");
					if ($pos > 1) {
						$lb = "\n";
					}
				}
				// Remove 100 headers.
				if (isset($lb) && preg_match('/^HTTP\/1.1 100/', $data)) {
					unset($lb);
					$data = '';
				}//
			}
		  // Store header data.
		  $this->incoming_payload .= $data;
		  $this->debug('found end of headers after length ' . strlen($data));
		  // Process headers.
		  $header_data            = trim(substr($data, 0, $pos));
		  $header_array           = explode($lb, $header_data);
		  $this->incoming_headers = array();
		  $this->incoming_cookies = array();
			foreach ($header_array as $header_line) {
				$arr = explode(':', $header_line, 2);
				if (count($arr) > 1) {
					$header_name                          = strtolower(trim($arr[0]));
					$this->incoming_headers[$header_name] = trim($arr[1]);
					if ('set-cookie' == $header_name) {
						// TODO: allow multiple cookies from parseCookie.
						$cookie = $this->parseCookie(trim($arr[1]));
						if ($cookie) {
							$this->incoming_cookies[] = $cookie;
							$this->debug('found cookie: ' . $cookie['name'] . ' = ' . $cookie['value']);
						} else {
							$this->debug('did not find cookie in ' . trim($arr[1]));
						}
					}
				} elseif (isset($header_name)) {
					// Append continuation line to previous header.
					$this->incoming_headers[$header_name] .= $lb . ' ' . $header_line;
				}
			}

		  // Loop until msg has been received.
			if (isset($this->incoming_headers['transfer-encoding']) && 'chunked' === strtolower($this->incoming_headers['transfer-encoding'])) {
				$content_length =  2147483647;	// ignore any content-length header
				$chunked        = true;
				$this->debug('want to read chunked content');
			} elseif (isset($this->incoming_headers['content-length'])) {
				$content_length = $this->incoming_headers['content-length'];
				$chunked        = false;
				$this->debug("want to read content of length $content_length");
			} else {
				$content_length =  2147483647;
				$chunked        = false;
				$this->debug('want to read content to EOF');
			}
		  $data = '';
			do {
				if ($chunked) {
					$tmp    = fgets($this->fp, 256);
					$tmplen = strlen($tmp);
					$this->debug("read chunk line of $tmplen bytes");
					if (0 == $tmplen) {
						$this->incoming_payload = $data;
						$this->debug('socket read of chunk length timed out after length ' . strlen($data));
						$this->debug("read before timeout:\n" . $data);
						$this->setError('socket read of chunk length timed out');
						return false;
					}
					$content_length = hexdec(trim($tmp));
					$this->debug("chunk length $content_length");
				}
				$strlen = 0;
				while (( $strlen < $content_length ) && ( !feof($this->fp) )) {
					$readlen = min(8192, $content_length - $strlen);
					$tmp     = fread($this->fp, $readlen);
					$tmplen  = strlen($tmp);
					$this->debug("read buffer of $tmplen bytes");
					if (( 0 == $tmplen ) && ( !feof($this->fp) )) {
						$this->incoming_payload = $data;
						$this->debug('socket read of body timed out after length ' . strlen($data));
						$this->debug("read before timeout:\n" . $data);
						$this->setError('socket read of body timed out');
						return false;
					}
					$strlen += $tmplen;
					$data   .= $tmp;
				}
				if ($chunked && ( $content_length > 0 )) {
					$tmp    = fgets($this->fp, 256);
					$tmplen = strlen($tmp);
					$this->debug("read chunk terminator of $tmplen bytes");
					if (0 == $tmplen) {
						$this->incoming_payload = $data;
						$this->debug('socket read of chunk terminator timed out after length ' . strlen($data));
						$this->debug("read before timeout:\n" . $data);
						$this->setError('socket read of chunk terminator timed out');
						return false;
					}
				}
			} while ($chunked && ( $content_length > 0 ) && ( !feof($this->fp) ));
			if (feof($this->fp)) {
				$this->debug('read to EOF');
			}
		  $this->debug('read body of length ' . strlen($data));
		  $this->incoming_payload .= $data;
		  $this->debug('received a total of ' . strlen($this->incoming_payload) . ' bytes of data from server');

		  // Close filepointer.
			if (
			( isset($this->incoming_headers['connection']) && 'close' === strtolower($this->incoming_headers['connection']) ) ||
			( ! $this->persistentConnection ) || feof($this->fp)) {
				fclose($this->fp);
				$this->fp = false;
				$this->debug('closed socket');
			}

		  // Connection was closed unexpectedly.
			if ('' == $this->incoming_payload) {
				$this->setError('no response from server');
				return false;
			}

		  // Decode transfer-encoding.
// If(isset($this->incoming_headers['transfer-encoding']) && strtolower($this->incoming_headers['transfer-encoding']) == 'chunked'){
// If(!$data = $this->decodeChunked($data, $lb)){
//              $this->setError('Decoding of chunked data failed');
// Return false;
//          }
			// Print "<pre>\nde-chunked:\n---------------\n$data\n\n---------------\n</pre>";
			// Set decoded payload.
//          $this->incoming_payload = $header_data.$lb.$lb.$data;
//      }

		} elseif ('curl' === $this->io_method()) {
		  // Send and receive.
		  $this->debug('send and receive with cURL');
		  $this->incoming_payload = curl_exec($this->ch);
		  $data                   = $this->incoming_payload;

		  $cErr = curl_error($this->ch);
			if ('' != $cErr) {
				$err = 'cURL ERROR: ' . curl_errno($this->ch) . ': ' . $cErr . '<br>';
				// TODO: there is a PHP bug that can cause this to SEGV for CURLINFO_CONTENT_TYPE.
				foreach (curl_getinfo($this->ch) as $k => $v) {
					$err .= "$k: $v<br>";
				}
				$this->debug($err);
				$this->setError($err);
				curl_close($this->ch);
				return false;
			}
		  // Close curl.
		  $this->debug('No cURL error, closing cURL');
		  curl_close($this->ch);

		  // Try removing skippable headers.
		  $savedata = $data;
			while ($this->isSkippableCurlHeader($data)) {
				$this->debug('Found HTTP header to skip');
				$_rn = strpos( $data, "\r\n\r\n" );
				$_n  = strpos( $data, "\n\n" );
				if ( $_rn ) {
					$pos  = $_rn;
					$data = ltrim(substr($data, $pos));
				} elseif ( $_n ) {
					$pos  = $_n;
					$data = ltrim(substr($data, $pos));
				}
			}

			if (empty( $data )) {
				// Have nothing left; just remove 100 header(s)
				$data = $savedata;
				while (preg_match('/^HTTP\/1.1 100/', $data)) {
					$_rn = strpos( $data, "\r\n\r\n" );
				  $_n    = strpos( $data, "\n\n" );
					if ( $_rn ) {
						$pos  = $_rn;
						$data = ltrim(substr($data, $pos));
					} elseif ( $_n ) {
						$pos  = $_n;
						$data = ltrim(substr($data, $pos));
					}
				}
			}

		  // Separate content from HTTP headers.
		  $_rn = strpos( $data, "\r\n\r\n" );
		  $_n  = strpos( $data, "\n\n" );
			if ( $_rn ) {
				$pos = $_rn;
				$lb  = "\r\n";
			} elseif ( $_n ) {
				$pos = $_n;
				$lb  = "\n";
			} else {
				$this->debug('no proper separation of headers and document');
				$this->setError('no proper separation of headers and document');
				return false;
			}
		  $header_data  = trim(substr($data, 0, $pos));
		  $header_array = explode($lb, $header_data);
		  $data         = ltrim(substr($data, $pos));
		  $this->debug('found proper separation of headers and document');
		  $this->debug('cleaned data, stringlen: ' . strlen($data));
		  // Clean headers.
			foreach ($header_array as $header_line) {
				$arr = explode(':', $header_line, 2);
				if (count($arr) > 1) {
					$header_name                          = strtolower(trim($arr[0]));
					$this->incoming_headers[$header_name] = trim($arr[1]);
					if ('set-cookie' == $header_name) {
						// TODO: allow multiple cookies from parseCookie.
						$cookie = $this->parseCookie(trim($arr[1]));
						if ($cookie) {
							$this->incoming_cookies[] = $cookie;
							$this->debug('found cookie: ' . $cookie['name'] . ' = ' . $cookie['value']);
						} else {
							$this->debug('did not find cookie in ' . trim($arr[1]));
						}
					}
				} elseif (isset($header_name)) {
					// Append continuation line to previous header.
					$this->incoming_headers[$header_name] .= $lb . ' ' . $header_line;
				}
			}
		}

		$this->response_status_line = $header_array[0];
		$arr                        = explode(' ', $this->response_status_line, 3);
		$http_version               = $arr[0];
		$http_status                = intval($arr[1]);
		$http_reason                = count($arr) > 2 ? $arr[2] : '';

		// See if we need to resend the request with http digest authentication.
		if (isset($this->incoming_headers['location']) && ( 301 == $http_status || 302 == $http_status )) {
			$this->debug("Got $http_status $http_reason with Location: " . $this->incoming_headers['location']);
			$this->setURL($this->incoming_headers['location']);
			$this->tryagain = true;
			return false;
		}

		// See if we need to resend the request with http digest authentication.
		if (isset($this->incoming_headers['www-authenticate']) && 401 == $http_status) {
			$this->debug("Got 401 $http_reason with WWW-Authenticate: " . $this->incoming_headers['www-authenticate']);
			if (strstr($this->incoming_headers['www-authenticate'], 'Digest ')) {
				$this->debug('Server wants digest authentication');
				// Remove "Digest " from our elements.
				$digestString = str_replace('Digest ', '', $this->incoming_headers['www-authenticate']);

				// Parse elements into array.
				$digestElements = explode(',', $digestString);
				foreach ($digestElements as $val) {
					$tempElement                    = explode('=', trim($val), 2);
					$digestRequest[$tempElement[0]] = str_replace('"', '', $tempElement[1]);
				}

				// Should have (at least) qop, realm, nonce.
				if (isset($digestRequest['nonce'])) {
					$this->setCredentials($this->username, $this->password, 'digest', $digestRequest);
					$this->tryagain = true;
					return false;
				}
			}
			$this->debug('HTTP authentication failed');
			$this->setError('HTTP authentication failed');
			return false;
		}

		if (
			( $http_status >= 300 && $http_status <= 307 ) ||
			( $http_status >= 400 && $http_status <= 417 ) ||
			( $http_status >= 501 && $http_status <= 505 )
		   ) {
			$this->setError("Unsupported HTTP response status $http_status $http_reason (soapclient->response has contents of the response)");
			return false;
		}

		// Decode content-encoding.
		if (isset($this->incoming_headers['content-encoding']) && '' != $this->incoming_headers['content-encoding']) {
			if ('deflate' === strtolower($this->incoming_headers['content-encoding']) || 'gzip' === strtolower($this->incoming_headers['content-encoding'])) {
				// If decoding works, use it. else assume data wasn't gzencoded.
				if (function_exists('gzinflate')) {
					//$timer->setMarker('starting decoding of gzip/deflated content');
					// IIS 5 requires gzinflate instead of gzuncompress (similar to IE 5 and gzdeflate v. gzcompress)
					// This means there are no Zlib headers, although there should be.
					$this->debug('The gzinflate function exists');
					$datalen = strlen($data);
					if ('deflate' === $this->incoming_headers['content-encoding']) {
						$degzdata = @gzinflate($data);
						if ($degzdata) {
							$data = $degzdata;
							$this->debug('The payload has been inflated to ' . strlen($data) . ' bytes');
							if (strlen($data) < $datalen) {
								// Test for the case that the payload has been compressed twice.
								$this->debug('The inflated payload is smaller than the gzipped one; try again');
								$degzdata = @gzinflate($data);
								if ($degzdata) {
									$data = $degzdata;
									$this->debug('The payload has been inflated again to ' . strlen($data) . ' bytes');
								}
							}
						} else {
							$this->debug('Error using gzinflate to inflate the payload');
							$this->setError('Error using gzinflate to inflate the payload');
						}
					} elseif ('gzip' === $this->incoming_headers['content-encoding']) {
						$degzdata = @gzinflate(substr($data, 10));
						if ($degzdata) {	// do our best
							$data = $degzdata;
							$this->debug('The payload has been un-gzipped to ' . strlen($data) . ' bytes');
							if (strlen($data) < $datalen) {
								// Test for the case that the payload has been compressed twice.
								$this->debug('The un-gzipped payload is smaller than the gzipped one; try again');
								$degzdata = @gzinflate(substr($data, 10));
								if ($degzdata) {
									$data = $degzdata;
									$this->debug('The payload has been un-gzipped again to ' . strlen($data) . ' bytes');
								}
							}
						} else {
							$this->debug('Error using gzinflate to un-gzip the payload');
							$this->setError('Error using gzinflate to un-gzip the payload');
						}
					}
					//$timer->setMarker('finished decoding of gzip/deflated content');
					// Print "<xmp>\nde-inflated:\n---------------\n$data\n-------------\n</xmp>";
					// Set decoded payload.
					$this->incoming_payload = $header_data . $lb . $lb . $data;
				} else {
					$this->debug('The server sent compressed data. Your php install must have the Zlib extension compiled in to support this.');
					$this->setError('The server sent compressed data. Your php install must have the Zlib extension compiled in to support this.');
				}
			} else {
				$this->debug('Unsupported Content-Encoding ' . $this->incoming_headers['content-encoding']);
				$this->setError('Unsupported Content-Encoding ' . $this->incoming_headers['content-encoding']);
			}
		} else {
			$this->debug('No Content-Encoding header');
		}

		if (0 == strlen($data)) {
			$this->debug('no data after headers!');
			$this->setError('no data present after HTTP headers');
			return false;
		}

		return $data;
	}

	/**
	 * Sets the content-type for the SOAP message to be sent
	 *
	 * @param	string $type the content type, MIME style
	 * @param	mixed $charset character set used for encoding (or false)
	 */
	public function setContentType( $type, $charset = false) {
		$this->setHeader('Content-Type', $type . ( $charset ? '; charset=' . $charset : '' ));
	}

	/**
	 * Specifies that an HTTP persistent connection should be used
	 *
	 * @return	boolean whether the request was honored by this method.
	 */
	public function usePersistentConnection() {
		if (isset($this->outgoing_headers['Accept-Encoding'])) {
			return false;
		}
		$this->protocol_version     = '1.1';
		$this->persistentConnection = true;
		$this->setHeader('Connection', 'Keep-Alive');
		return true;
	}

	/**
	 * Parse an incoming Cookie into it's parts
	 *
	 * @param	string $cookie_str content of cookie
	 * @return	array with data of that cookie
	 */
	/*
	 * TODO: allow a Set-Cookie string to be parsed into multiple cookies
	 */
	public function parseCookie( $cookie_str) {
		$cookie_str = str_replace('; ', ';', $cookie_str) . ';';
		$data       = preg_split('/;/', $cookie_str);
		$value_str  = $data[0];

		$cookie_param = 'domain=';
		$start        = strpos($cookie_str, $cookie_param);
		if ($start > 0) {
			$domain = substr($cookie_str, $start + strlen($cookie_param));
			$domain = substr($domain, 0, strpos($domain, ';'));
		} else {
			$domain = '';
		}

		$cookie_param = 'expires=';
		$start        = strpos($cookie_str, $cookie_param);
		if ($start > 0) {
			$expires = substr($cookie_str, $start + strlen($cookie_param));
			$expires = substr($expires, 0, strpos($expires, ';'));
		} else {
			$expires = '';
		}

		$cookie_param = 'path=';
		$start        = strpos($cookie_str, $cookie_param);
		if ( $start > 0 ) {
			$path = substr($cookie_str, $start + strlen($cookie_param));
			$path = substr($path, 0, strpos($path, ';'));
		} else {
			$path = '/';
		}

		$cookie_param = ';secure;';
		if (false !== strpos($cookie_str, $cookie_param)) {
			$secure = true;
		} else {
			$secure = false;
		}

		$sep_pos = strpos($value_str, '=');

		if ($sep_pos) {
			$name   = substr($value_str, 0, $sep_pos);
			$value  = substr($value_str, $sep_pos + 1);
			$cookie = array(	'name' => $name,
							'value' => $value,
							'domain' => $domain,
							'path' => $path,
							'expires' => $expires,
							'secure' => $secure
							);
			return $cookie;
		}
		return false;
	}

	/**
	 * Sort out cookies for the current request
	 *
	 * @param	array $cookies array with all cookies
	 * @param	boolean $secure is the send-content secure or not?
	 * @return	string for Cookie-HTTP-Header
	 */
	public function getCookiesForRequest( $cookies, $secure = false) {
		$cookie_str = '';
		if (( ! is_null($cookies) ) && ( is_array($cookies) )) {
			foreach ($cookies as $cookie) {
				if (! is_array($cookie)) {
					continue;
				}
				$this->debug('check cookie for validity: ' . $cookie['name'] . '=' . $cookie['value']);
				if (( isset($cookie['expires']) ) && ( ! empty($cookie['expires']) )) {
					if (strtotime($cookie['expires']) <= time()) {
						$this->debug('cookie has expired');
						continue;
					}
				}
				if (( isset($cookie['domain']) ) && ( ! empty($cookie['domain']) )) {
					$domain = preg_quote($cookie['domain']);
					if (! preg_match("'.*$domain$'i", $this->host)) {
						$this->debug('cookie has different domain');
						continue;
					}
				}
				if (( isset($cookie['path']) ) && ( ! empty($cookie['path']) )) {
					$path = preg_quote($cookie['path']);
					if (! preg_match("'^$path.*'i", $this->path)) {
						$this->debug('cookie is for a different path');
						continue;
					}
				}
				if (( ! $secure ) && ( isset($cookie['secure']) ) && ( $cookie['secure'] )) {
					$this->debug('cookie is secure, transport is not');
					continue;
				}
				$cookie_str .= $cookie['name'] . '=' . $cookie['value'] . '; ';
				$this->debug('add cookie to Cookie-String: ' . $cookie['name'] . '=' . $cookie['value']);
			}
		}
		return $cookie_str;
	}
}



