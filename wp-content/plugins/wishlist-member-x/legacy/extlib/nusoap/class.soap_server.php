<?php




/**
*
* Nusoap_server allows the user to create a SOAP server
* that is capable of receiving messages and returning responses
*
* @version  $Id: class.soap_server.php,v 1.63 2010/04/26 20:15:08 snichol Exp $
*/
class nusoap_server extends nusoap_base {
	/**
	 * HTTP headers of request
	 *
	 * @var array
	 */
	public $headers = array();
	/**
	 * HTTP request
	 *
	 * @var string
	 */
	public $request = '';
	/**
	 * SOAP headers from request (incomplete namespace resolution; special characters not escaped) (text)
	 *
	 * @var string
	 */
	public $requestHeaders = '';
	/**
	 * SOAP Headers from request (parsed)
	 *
	 * @var mixed
	 */
	public $requestHeader = null;
	/**
	 * SOAP body request portion (incomplete namespace resolution; special characters not escaped) (text)
	 *
	 * @var string
	 */
	public $document = '';
	/**
	 * SOAP payload for request (text)
	 *
	 * @var string
	 */
	public $requestSOAP = '';
	/**
	 * Requested method namespace URI
	 *
	 * @var string
	 */
	public $methodURI = '';
	/**
	 * Name of method requested
	 *
	 * @var string
	 */
	public $methodname = '';
	/**
	 * Method parameters from request
	 *
	 * @var array
	 */
	public $methodparams = array();
	/**
	 * SOAP Action from request
	 *
	 * @var string
	 */
	public $SOAPAction = '';
	/**
	 * Character set encoding of incoming (request) messages
	 *
	 * @var string
	 */
	public $xml_encoding = '';
	/**
	 * Toggles whether the parser decodes element content w/ utf8_decode()
	 *
	 * @var boolean
	 */
	public $decode_utf8 = true;

	/**
	 * HTTP headers of response
	 *
	 * @var array
	 */
	public $outgoing_headers = array();
	/**
	 * HTTP response
	 *
	 * @var string
	 */
	public $response = '';
	/**
	 * SOAP headers for response (text or array of soapval or associative array)
	 *
	 * @var mixed
	 */
	public $responseHeaders = '';
	/**
	 * SOAP payload for response (text)
	 *
	 * @var string
	 */
	public $responseSOAP = '';
	/**
	 * Method return value to place in response
	 *
	 * @var mixed
	 */
	public $methodreturn = false;
	/**
	 * Whether $methodreturn is a string of literal XML
	 *
	 * @var boolean
	 */
	public $methodreturnisliteralxml = false;
	/**
	 * SOAP fault for response (or false)
	 *
	 * @var mixed
	 */
	public $fault = false;
	/**
	 * Text indication of result (for debugging)
	 *
	 * @var string
	 */
	public $result = 'successful';

	/**
	 * Assoc array of operations => opData; operations are added by the register()
	 * method or by parsing an external WSDL definition
	 *
	 * @var array
	 */
	public $operations = array();
	/**
	 * Wsdl instance (if one)
	 *
	 * @var mixed
	 */
	public $wsdl = false;
	/**
	 * URL for WSDL (if one)
	 *
	 * @var mixed
	 */
	public $externalWSDLURL = false;
	/**
	 * Whether to append debug to response as XML comment
	 *
	 * @var boolean
	 */
	public $debug_flag = false;


	/**
	* Constructor
	* the optional parameter is a path to a WSDL file that you'd like to bind the server instance to.
	*
	* @param mixed $wsdl file path or URL (string), or wsdl instance (object)
	*/
	public function __construct( $wsdl = false) {
		parent::nusoap_base();
		// Turn on debugging?
		global $debug;

		if (isset($_SERVER)) {
			$this->debug('_SERVER is defined:');
			$this->appendDebug($this->varDump($_SERVER));
		} elseif (isset($_SERVER)) {
			$this->debug('HTTP_SERVER_VARS is defined:');
			$this->appendDebug($this->varDump($_SERVER));
		} else {
			$this->debug('Neither _SERVER nor HTTP_SERVER_VARS is defined.');
		}

		if (isset($debug)) {
			$this->debug("In nusoap_server, set debug_flag=$debug based on global flag");
			$this->debug_flag = $debug;
		} elseif (isset($_SERVER['QUERY_STRING'])) {
			$qs = explode('&', $_SERVER['QUERY_STRING']);
			foreach ($qs as $v) {
				if ('debug=' == substr($v, 0, 6)) {
					$this->debug('In nusoap_server, set debug_flag=' . substr($v, 6) . ' based on query string #1');
					$this->debug_flag = substr($v, 6);
				}
			}
		} elseif (isset($_SERVER['QUERY_STRING'])) {
			$qs = explode('&', $_SERVER['QUERY_STRING']);
			foreach ($qs as $v) {
				if ('debug=' == substr($v, 0, 6)) {
					$this->debug('In nusoap_server, set debug_flag=' . substr($v, 6) . ' based on query string #2');
					$this->debug_flag = substr($v, 6);
				}
			}
		}

		// Wsdl.
		if ($wsdl) {
			$this->debug('In nusoap_server, WSDL is specified');
			if (is_object($wsdl) && ( 'wsdl' === get_class($wsdl) )) {
				$this->wsdl            = $wsdl;
				$this->externalWSDLURL = $this->wsdl->wsdl;
				$this->debug('Use existing wsdl instance from ' . $this->externalWSDLURL);
			} else {
				$this->debug('Create wsdl from ' . $wsdl);
				$this->wsdl            = new wsdl($wsdl);
				$this->externalWSDLURL = $wsdl;
			}
			$this->appendDebug($this->wsdl->getDebug());
			$this->wsdl->clearDebug();
			$err = $this->wsdl->getError();
			if ($err) {
				die('WSDL ERROR: ' . $err);
			}
		}
	}

	/**
	* Processes request and returns response
	*
	* @param    string $data usually is the value of $HTTP_RAW_POST_DATA
	*/
	public function service( $data) {

		if (isset($_SERVER['REQUEST_METHOD'])) {
			$rm = $_SERVER['REQUEST_METHOD'];
		} elseif (isset($_SERVER['REQUEST_METHOD'])) {
			$rm = $_SERVER['REQUEST_METHOD'];
		} else {
			$rm = '';
		}

		if (isset($_SERVER['QUERY_STRING'])) {
			$qs = $_SERVER['QUERY_STRING'];
		} elseif (isset($_SERVER['QUERY_STRING'])) {
			$qs = $_SERVER['QUERY_STRING'];
		} else {
			$qs = '';
		}
		$this->debug("In service, request method=$rm query string=$qs strlen(\$data)=" . strlen($data));

		if ('POST' === $rm) {
			$this->debug('In service, invoke the request');
			$this->parse_request($data);
			if (! $this->fault) {
				$this->invoke_method();
			}
			if (! $this->fault) {
				$this->serialize_return();
			}
			$this->send_response();
		} elseif (preg_match('/wsdl/', $qs) ) {
			$this->debug('In service, this is a request for WSDL');
			if ($this->externalWSDLURL) {
				if (false !== strpos($this->externalWSDLURL, 'http://')) { // Assume URL.
				  $this->debug('In service, re-direct for WSDL');
				  header('Location: ' . $this->externalWSDLURL);
				} else { // Assume file.
				  $this->debug('In service, use file passthru for WSDL');
				  header("Content-Type: text/xml\r\n");
				  $pos = strpos($this->externalWSDLURL, 'file://');
					if (false === $pos) {
						$filename = $this->externalWSDLURL;
					} else {
						$filename = substr($this->externalWSDLURL, $pos + 7);
					}
				  $fp = fopen($this->externalWSDLURL, 'r');
				  fpassthru($fp);
				}
			} elseif ($this->wsdl) {
				$this->debug('In service, serialize WSDL');
				header("Content-Type: text/xml; charset=ISO-8859-1\r\n");
				print $this->wsdl->serialize($this->debug_flag);
				if ($this->debug_flag) {
					$this->debug('wsdl:');
					$this->appendDebug($this->varDump($this->wsdl));
					print $this->getDebugAsXMLComment();
				}
			} else {
				$this->debug('In service, there is no WSDL');
				header("Content-Type: text/html; charset=ISO-8859-1\r\n");
				print 'This service does not provide WSDL';
			}
		} elseif ($this->wsdl) {
			$this->debug('In service, return Web description');
			print $this->wsdl->webDescription();
		} else {
			$this->debug('In service, no Web description');
			header("Content-Type: text/html; charset=ISO-8859-1\r\n");
			print 'This service does not provide a Web description';
		}
	}

	/**
	* Parses HTTP request headers.
	*
	* The following fields are set by this function (when successful)
	*
	* headers
	* request
	* xml_encoding
	* SOAPAction
	*
	*/
	public function parse_http_headers() {

		$this->request    = '';
		$this->SOAPAction = '';
		if (function_exists('getallheaders')) {
			$this->debug('In parse_http_headers, use getallheaders');
			$headers = getallheaders();
			foreach ($headers as $k=>$v) {
				$k                 = strtolower($k);
				$this->headers[$k] = $v;
				$this->request    .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
			// Get SOAPAction header.
			if (isset($this->headers['soapaction'])) {
				$this->SOAPAction = str_replace('"', '', $this->headers['soapaction']);
			}
			// Get the character encoding of the incoming request.
			if (isset($this->headers['content-type']) && strpos($this->headers['content-type'], '=')) {
				$enc = str_replace('"', '', substr(strstr($this->headers['content-type'], '='), 1));
				if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i', $enc)) {
					$this->xml_encoding = strtoupper($enc);
				} else {
					$this->xml_encoding = 'US-ASCII';
				}
			} else {
				// Should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1.
				$this->xml_encoding = 'ISO-8859-1';
			}
		} elseif (isset($_SERVER) && is_array($_SERVER)) {
			$this->debug('In parse_http_headers, use _SERVER');
			foreach ($_SERVER as $k => $v) {
				if ('HTTP_' === substr($k, 0, 5)) {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5))));
				} else {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', $k)));
				}
				if ('soapaction' === $k) {
					// Get SOAPAction header.
					$k                = 'SOAPAction';
					$v                = str_replace('"', '', $v);
					$v                = str_replace('\\', '', $v);
					$this->SOAPAction = $v;
				} elseif ('content-type' == $k) {
					// Get the character encoding of the incoming request.
					if (strpos($v, '=')) {
						$enc = substr(strstr($v, '='), 1);
						$enc = str_replace('"', '', $enc);
						$enc = str_replace('\\', '', $enc);
						if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i', $enc)) {
							$this->xml_encoding = strtoupper($enc);
						} else {
							$this->xml_encoding = 'US-ASCII';
						}
					} else {
						// Should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1.
						$this->xml_encoding = 'ISO-8859-1';
					}
				}
				$this->headers[$k] = $v;
				$this->request    .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
		} elseif (is_array($_SERVER)) {
			$this->debug('In parse_http_headers, use HTTP_SERVER_VARS');
			foreach ($_SERVER as $k => $v) {
				if ('HTTP_' === substr($k, 0, 5)) {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5))));
$k                     = strtolower(substr($k, 5));
				} else {
					$k = str_replace(' ', '-', strtolower(str_replace('_', ' ', $k)));
$k                     = strtolower($k);
				}
				if ('soapaction' === $k) {
					// Get SOAPAction header.
					$k                = 'SOAPAction';
					$v                = str_replace('"', '', $v);
					$v                = str_replace('\\', '', $v);
					$this->SOAPAction = $v;
				} elseif ('content-type' == $k) {
					// Get the character encoding of the incoming request.
					if (strpos($v, '=')) {
						$enc = substr(strstr($v, '='), 1);
						$enc = str_replace('"', '', $enc);
						$enc = str_replace('\\', '', $enc);
						if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i', $enc)) {
							$this->xml_encoding = strtoupper($enc);
						} else {
							$this->xml_encoding = 'US-ASCII';
						}
					} else {
						// Should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1.
						$this->xml_encoding = 'ISO-8859-1';
					}
				}
				$this->headers[$k] = $v;
				$this->request    .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
		} else {
			$this->debug('In parse_http_headers, HTTP headers not accessible');
			$this->setError('HTTP headers not accessible');
		}
	}

	/**
	* Parses a request
	*
	* The following fields are set by this function (when successful)
	*
	* headers
	* request
	* xml_encoding
	* SOAPAction
	* request
	* requestSOAP
	* methodURI
	* methodname
	* methodparams
	* requestHeaders
	* document
	*
	* This sets the fault field on error
	*
	* @param    string $data XML string
	*/
	public function parse_request( $data = '') {
		$this->debug('entering parse_request()');
		$this->parse_http_headers();
		$this->debug('got character encoding: ' . $this->xml_encoding);
		// Uncompress if necessary.
		if (isset($this->headers['content-encoding']) && '' != $this->headers['content-encoding']) {
			$this->debug('got content encoding: ' . $this->headers['content-encoding']);
			if ('deflate' === $this->headers['content-encoding'] || 'gzip' === $this->headers['content-encoding']) {
				// If decoding works, use it. else assume data wasn't gzencoded.
				if (function_exists('gzuncompress')) {
					$degzdata = false;
					if ('deflate' === $this->headers['content-encoding'] ) {
						$degzdata = @gzuncompress($data);
					} elseif ('gzip' === $this->headers['content-encoding']) {
						$degzdata = gzinflate(substr($data, 10));
					}
					if ( $degzdata ) {
						$data = $degzdata;
					} else {
						$this->fault('SOAP-ENV:Client', 'Errors occurred when trying to decode the data');
						return;
					}
				} else {
					$this->fault('SOAP-ENV:Client', 'This Server does not support compressed data');
					return;
				}
			}
		}
		$this->request    .= "\r\n" . $data;
		$data              = $this->parseRequest($this->headers, $data);
		$this->requestSOAP = $data;
		$this->debug('leaving parse_request');
	}

	/**
	* Invokes a PHP function for the requested SOAP method
	*
	* The following fields are set by this function (when successful)
	*
	* methodreturn
	*
	* Note that the PHP function that is called may also set the following
	* fields to affect the response sent to the client
	*
	* responseHeaders
	* outgoing_headers
	*
	* This sets the fault field on error
	*
	*/
	public function invoke_method() {
		$this->debug('in invoke_method, methodname=' . $this->methodname . ' methodURI=' . $this->methodURI . ' SOAPAction=' . $this->SOAPAction);

		//
		// If you are debugging in this area of the code, your service uses a class to implement methods,
		// You use SOAP RPC, and the client is .NET, please be aware of the following...
		// When the .NET wsdl.exe utility generates a proxy, it will remove the '.' or '..' from the.
		// Method name.  that is fine for naming the .NET methods.  it is not fine for properly constructing.
		// The XML request and reading the XML response.  you need to add the RequestElementName and.
		// ResponseElementName to the System.Web.Services.Protocols.SoapRpcMethodAttribute that wsdl.exe.
		// Generates for the method.  these parameters are used to specify the correct XML element names.
		// For .NET to use, i.e. the names with the '.' in them.
		//
		$orig_methodname = $this->methodname;
		if ($this->wsdl) {
			$_opdata1 = $this->wsdl->getOperationData($this->methodname);
			$_opdata2 = $this->wsdl->getOperationDataForSoapAction($this->SOAPAction);
			if ($_opdata1) {
				$this->opData = $_opdata1;
				$this->debug('in invoke_method, found WSDL operation=' . $this->methodname);
				$this->appendDebug('opData=' . $this->varDump($this->opData));
			} elseif ($_opdata2) {
				$this->opData = $_opdata2;
				// Note: hopefully this case will only be used for doc/lit, since rpc services should have wrapper element.
				$this->debug('in invoke_method, found WSDL soapAction=' . $this->SOAPAction . ' for operation=' . $this->opData['name']);
				$this->appendDebug('opData=' . $this->varDump($this->opData));
				$this->methodname = $this->opData['name'];
			} else {
				$this->debug('in invoke_method, no WSDL for operation=' . $this->methodname);
				$this->fault('SOAP-ENV:Client', "Operation '" . $this->methodname . "' is not defined in the WSDL for this service");
				return;
			}
		} else {
			$this->debug('in invoke_method, no WSDL to validate method');
		}

		// If a . is present in $this->methodname, we see if there is a class in scope,
		// Which could be referred to. We will also distinguish between two deliminators,
		// To allow methods to be called a the class or an instance.
		if (strpos($this->methodname, '..') > 0) {
			$delim = '..';
		} elseif (strpos($this->methodname, '.') > 0) {
			$delim = '.';
		} else {
			$delim = '';
		}
		$this->debug("in invoke_method, delim=$delim");

		$class  = '';
		$method = '';
		if (strlen($delim) > 0 && 1 == substr_count($this->methodname, $delim)) {
			$try_class = substr($this->methodname, 0, strpos($this->methodname, $delim));
			if (class_exists($try_class)) {
				// Get the class and method name.
				$class  = $try_class;
				$method = substr($this->methodname, strpos($this->methodname, $delim) + strlen($delim));
				$this->debug("in invoke_method, class=$class method=$method delim=$delim");
			} else {
				$this->debug("in invoke_method, class=$try_class not found");
			}
		} else {
			$try_class = '';
			$this->debug('in invoke_method, no class to try');
		}

		// Does method exist?
		if (empty( $class )) {
			if (!function_exists($this->methodname)) {
				$this->debug("in invoke_method, function '$this->methodname' not found!");
				$this->result = 'fault: method not found';
				$this->fault('SOAP-ENV:Client', "method '$this->methodname'('$orig_methodname') not defined in service('$try_class' '$delim')");
				return;
			}
		} else {
			$method_to_compare = ( '4.' == substr(phpversion(), 0, 2) ) ? strtolower($method) : $method;
			if (!in_array($method_to_compare, get_class_methods($class))) {
				$this->debug("in invoke_method, method '$this->methodname' not found in class '$class'!");
				$this->result = 'fault: method not found';
				$this->fault('SOAP-ENV:Client', "method '$this->methodname'/'$method_to_compare'('$orig_methodname') not defined in service/'$class'('$try_class' '$delim')");
				return;
			}
		}

		// Evaluate message, getting back parameters.
		// Verify that request parameters match the method's signature.
		if (! $this->verify_method($this->methodname, $this->methodparams)) {
			// Debug.
			$this->debug('ERROR: request not verified against method signature');
			$this->result = 'fault: request failed validation against method signature';
			// Return fault.
			$this->fault('SOAP-ENV:Client', "Operation '$this->methodname' not defined in service.");
			return;
		}

		// If there are parameters to pass.
		$this->debug('in invoke_method, params:');
		$this->appendDebug($this->varDump($this->methodparams));
		$this->debug("in invoke_method, calling '$this->methodname'");
		if (!function_exists('call_user_func_array')) {
			if (empty( $class )) {
				$this->debug('in invoke_method, calling function using eval()');
				$funcCall = "\$this->methodreturn = $this->methodname(";
			} else {
				if ('..' == $delim) {
					$this->debug('in invoke_method, calling class method using eval()');
					$funcCall = '$this->methodreturn = ' . $class . '::' . $method . '(';
				} else {
					$this->debug('in invoke_method, calling instance method using eval()');
					// Generate unique instance name.
					$instname  = '$inst_' . time();
					$funcCall  = $instname . ' = new ' . $class . '(); ';
					$funcCall .= '$this->methodreturn = ' . $instname . '->' . $method . '(';
				}
			}
			if ($this->methodparams) {
				foreach ($this->methodparams as $param) {
					if (is_array($param) || is_object($param)) {
						$this->fault('SOAP-ENV:Client', 'NuSOAP does not handle complexType parameters correctly when using eval; call_user_func_array must be available');
						return;
					}
					$funcCall .= "\"$param\",";
				}
				$funcCall = substr($funcCall, 0, -1);
			}
			$funcCall .= ');';
			$this->debug('in invoke_method, function call: ' . $funcCall);
			@eval($funcCall);
		} else {
			if (empty( $class )) {
				$this->debug('in invoke_method, calling function using call_user_func_array()');
				$call_arg = "$this->methodname";	// straight assignment changes $this->methodname to lower case after call_user_func_array()
			} elseif ('..' == $delim) {
				$this->debug('in invoke_method, calling class method using call_user_func_array()');
				$call_arg = array ($class, $method);
			} else {
				$this->debug('in invoke_method, calling instance method using call_user_func_array()');
				$instance = new $class();
				$call_arg = array(&$instance, $method);
			}
			if (is_array($this->methodparams)) {
				$this->methodreturn = call_user_func_array($call_arg, array_values($this->methodparams));
			} else {
				$this->methodreturn = call_user_func_array($call_arg, array());
			}
		}
		$this->debug('in invoke_method, methodreturn:');
		$this->appendDebug($this->varDump($this->methodreturn));
		$this->debug("in invoke_method, called method $this->methodname, received data of type " . gettype($this->methodreturn));
	}

	/**
	* Serializes the return value from a PHP function into a full SOAP Envelope
	*
	* The following fields are set by this function (when successful)
	*
	* responseSOAP
	*
	* This sets the fault field on error
	*
	*/
	public function serialize_return() {
		$this->debug('Entering serialize_return methodname: ' . $this->methodname . ' methodURI: ' . $this->methodURI);
		// If fault.
		if (isset($this->methodreturn) && is_object($this->methodreturn) && ( ( 'soap_fault' === get_class($this->methodreturn) ) || ( 'nusoap_fault' === get_class($this->methodreturn) ) )) {
			$this->debug('got a fault object from method');
			$this->fault = $this->methodreturn;
			return;
		} elseif ($this->methodreturnisliteralxml) {
			$return_val = $this->methodreturn;
		// Returned value(s)
		} else {
			$this->debug('got a(n) ' . gettype($this->methodreturn) . ' from method');
			$this->debug('serializing return value');
			if ($this->wsdl) {
				if (count($this->opData['output']['parts']) > 1) {
					$this->debug('more than one output part, so use the method return unchanged');
					$opParams = $this->methodreturn;
				} elseif (1 == count($this->opData['output']['parts'])) {
					$this->debug('exactly one output part, so wrap the method return in a simple array');
					// TODO: verify that it is not already wrapped!
					// Foreach ($this->opData['output']['parts'] as $name => $type) {
					//	$this->debug('wrap in element named ' . $name);
					//}
					$opParams = array($this->methodreturn);
				}
				$return_val = $this->wsdl->serializeRPCParameters($this->methodname, 'output', $opParams);
				$this->appendDebug($this->wsdl->getDebug());
				$this->wsdl->clearDebug();
				$errstr = $this->wsdl->getError();
				if ($errstr) {
					$this->debug('got wsdl error: ' . $errstr);
					$this->fault('SOAP-ENV:Server', 'unable to serialize result');
					return;
				}
			} else {
				if (isset($this->methodreturn)) {
					$return_val = $this->serialize_val($this->methodreturn, 'return');
				} else {
					$return_val = '';
					$this->debug('in absence of WSDL, assume void return for backward compatibility');
				}
			}
		}
		$this->debug('return value:');
		$this->appendDebug($this->varDump($return_val));

		$this->debug('serializing response');
		if ($this->wsdl) {
			$this->debug('have WSDL for serialization: style is ' . $this->opData['style']);
			if ('rpc' === $this->opData['style']) {
				$this->debug('style is rpc for serialization: use is ' . $this->opData['output']['use']);
				if ('literal' === $this->opData['output']['use']) {
					// Http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html R2735 says rpc/literal accessor elements should not be in a namespace.
					if ($this->methodURI) {
						$payload = '<ns1:' . $this->methodname . 'Response xmlns:ns1="' . $this->methodURI . '">' . $return_val . '</ns1:' . $this->methodname . 'Response>';
					} else {
						$payload = '<' . $this->methodname . 'Response>' . $return_val . '</' . $this->methodname . 'Response>';
					}
				} else {
					if ($this->methodURI) {
						$payload = '<ns1:' . $this->methodname . 'Response xmlns:ns1="' . $this->methodURI . '">' . $return_val . '</ns1:' . $this->methodname . 'Response>';
					} else {
						$payload = '<' . $this->methodname . 'Response>' . $return_val . '</' . $this->methodname . 'Response>';
					}
				}
			} else {
				$this->debug('style is not rpc for serialization: assume document');
				$payload = $return_val;
			}
		} else {
			$this->debug('do not have WSDL for serialization: assume rpc/encoded');
			$payload = '<ns1:' . $this->methodname . 'Response xmlns:ns1="' . $this->methodURI . '">' . $return_val . '</ns1:' . $this->methodname . 'Response>';
		}
		$this->result = 'successful';
		if ($this->wsdl) {
			// If($this->debug_flag){
				$this->appendDebug($this->wsdl->getDebug());
			//	}
			if (isset($this->opData['output']['encodingStyle'])) {
				$encodingStyle = $this->opData['output']['encodingStyle'];
			} else {
				$encodingStyle = '';
			}
			// Added: In case we use a WSDL, return a serialized env. WITH the usedNamespaces.
			$this->responseSOAP = $this->serializeEnvelope($payload, $this->responseHeaders, $this->wsdl->usedNamespaces, $this->opData['style'], $this->opData['output']['use'], $encodingStyle);
		} else {
			$this->responseSOAP = $this->serializeEnvelope($payload, $this->responseHeaders);
		}
		$this->debug('Leaving serialize_return');
	}

	/**
	* Sends an HTTP response
	*
	* The following fields are set by this function (when successful)
	*
	* outgoing_headers
	* response
	*
	*/
	public function send_response() {
		$this->debug('Enter send_response');
		if ($this->fault) {
			$payload                  = $this->fault->serialize();
			$this->outgoing_headers[] = 'HTTP/1.0 500 Internal Server Error';
			$this->outgoing_headers[] = 'Status: 500 Internal Server Error';
		} else {
			$payload = $this->responseSOAP;
			// Some combinations of PHP+Web server allow the Status.
			// To come through as a header.  Since OK is the default.
			// Just do nothing.
			// $this->outgoing_headers[] = "HTTP/1.0 200 OK";
			// $this->outgoing_headers[] = "Status: 200 OK";
		}
		// Add debug data if in debug mode.
		if (isset($this->debug_flag) && $this->debug_flag) {
			$payload .= $this->getDebugAsXMLComment();
		}
		$this->outgoing_headers[] = "Server: $this->title Server v$this->version";
		preg_match('/\$Revision: ([^ ]+)/', $this->revision, $rev);
		$this->outgoing_headers[] = "X-SOAP-Server: $this->title/$this->version (" . $rev[1] . ')';
		// Let the Web server decide about this.
		//$this->outgoing_headers[] = "Connection: Close\r\n";
		$payload                  = $this->getHTTPBody($payload);
		$type                     = $this->getHTTPContentType();
		$charset                  = $this->getHTTPContentTypeCharset();
		$this->outgoing_headers[] = "Content-Type: $type" . ( $charset ? '; charset=' . $charset : '' );
		// Begin code to compress payload - by John.
		// NOTE: there is no way to know whether the Web server will also compress.
		// This data.
		if (strlen($payload) > 1024 && isset($this->headers) && isset($this->headers['accept-encoding'])) {
			if (strstr($this->headers['accept-encoding'], 'gzip')) {
				if (function_exists('gzencode')) {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= '<!-- Content being gzipped -->';
					}
					$this->outgoing_headers[] = 'Content-Encoding: gzip';
					$payload                  = gzencode($payload);
				} else {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= '<!-- Content will not be gzipped: no gzencode -->';
					}
				}
			} elseif (strstr($this->headers['accept-encoding'], 'deflate')) {
				// Note: MSIE requires gzdeflate output (no Zlib header and checksum),
				// Instead of gzcompress output,
				// Which conflicts with HTTP 1.1 spec (http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.5)
				if (function_exists('gzdeflate')) {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= '<!-- Content being deflated -->';
					}
					$this->outgoing_headers[] = 'Content-Encoding: deflate';
					$payload                  = gzdeflate($payload);
				} else {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= '<!-- Content will not be deflated: no gzcompress -->';
					}
				}
			}
		}
		// End code.
		$this->outgoing_headers[] = 'Content-Length: ' . strlen($payload);
		reset($this->outgoing_headers);
		foreach ($this->outgoing_headers as $hdr) {
			header($hdr, false);
		}
		print $payload;
		$this->response = join("\r\n", $this->outgoing_headers) . "\r\n\r\n" . $payload;
	}

	/**
	* Takes the value that was created by parsing the request
	* and compares to the method's signature, if available.
	*
	* @param	string	$operation	The operation to be invoked
	* @param	array	$request	The array of parameter values
	* @return	boolean	Whether the operation was found
	*/
	public function verify_method( $operation, $request) {
		if (isset($this->wsdl) && is_object($this->wsdl)) {
			if ($this->wsdl->getOperationData($operation)) {
				return true;
			}
		} elseif (isset($this->operations[$operation])) {
			return true;
		}
		return false;
	}

	/**
	* Processes SOAP message received from client
	*
	* @param	array	$headers	The HTTP headers
	* @param	string	$data		unprocessed request data from client
	* @return	mixed	value of the message, decoded into a PHP type
	*/
	public function parseRequest( $headers, $data) {
		$this->debug('Entering parseRequest() for data of length ' . strlen($data) . ' headers:');
		$this->appendDebug($this->varDump($headers));
		if (!isset($headers['content-type'])) {
			$this->setError('Request not of type text/xml (no content-type header)');
			return false;
		}
		if (!strstr($headers['content-type'], 'text/xml')) {
			$this->setError('Request not of type text/xml');
			return false;
		}
		if (strpos($headers['content-type'], '=')) {
			$enc = str_replace('"', '', substr(strstr($headers['content-type'], '='), 1));
			$this->debug('Got response encoding: ' . $enc);
			if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i', $enc)) {
				$this->xml_encoding = strtoupper($enc);
			} else {
				$this->xml_encoding = 'US-ASCII';
			}
		} else {
			// Should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1.
			$this->xml_encoding = 'ISO-8859-1';
		}
		$this->debug('Use encoding: ' . $this->xml_encoding . ' when creating nusoap_parser');
		// Parse response, get soap parser obj.
		$parser = new nusoap_parser($data, $this->xml_encoding, '', $this->decode_utf8);
		// Parser debug.
		$this->debug("parser debug: \n" . $parser->getDebug());
		// If fault occurred during message parsing.
		$err = $parser->getError();
		if ($err) {
			$this->result = 'fault: error in msg parsing: ' . $err;
			$this->fault('SOAP-ENV:Client', "error in msg parsing:\n" . $err);
		// Else successfully parsed request into soapval object.
		} else {
			// Get/set methodname.
			$this->methodURI  = $parser->root_struct_namespace;
			$this->methodname = $parser->root_struct_name;
			$this->debug('methodname: ' . $this->methodname . ' methodURI: ' . $this->methodURI);
			$this->debug('calling parser->get_soapbody()');
			$this->methodparams = $parser->get_soapbody();
			// Get SOAP headers.
			$this->requestHeaders = $parser->getHeaders();
			// Get SOAP Header.
			$this->requestHeader = $parser->get_soapheader();
			// Add document for doclit support.
			$this->document = $parser->document;
		}
	}

	/**
	* Gets the HTTP body for the current response.
	*
	* @param string $soapmsg The SOAP payload
	* @return string The HTTP body, which includes the SOAP payload
	*/
	public function getHTTPBody( $soapmsg) {
		return $soapmsg;
	}

	/**
	* Gets the HTTP content type for the current response.
	*
	* Note: getHTTPBody must be called before this.
	*
	* @return string the HTTP content type for the current response.
	*/
	public function getHTTPContentType() {
		return 'text/xml';
	}

	/**
	* Gets the HTTP content type charset for the current response.
	* returns false for non-text content types.
	*
	* Note: getHTTPBody must be called before this.
	*
	* @return string the HTTP content type charset for the current response.
	*/
	public function getHTTPContentTypeCharset() {
		return $this->soap_defencoding;
	}

	/**
	* Add a method to the dispatch map (this has been replaced by the register method)
	*
	* @param    string $methodname
	* @param    string $in array of input values
	* @param    string $out array of output values
	* @deprecated
	*/
	public function add_to_map( $methodname, $in, $out) {
			$this->operations[$methodname] = array('name' => $methodname,'in' => $in,'out' => $out);
	}

	/**
	* Register a service function with the server
	*
	* @param    string $name the name of the PHP function, class.method or class..method
	* @param    array $in assoc array of input values: key = param name, value = param type
	* @param    array $out assoc array of output values: key = param name, value = param type
	* @param	mixed $namespace the element namespace for the method or false
	* @param	mixed $soapaction the soapaction for the method or false
	* @param	mixed $style optional (rpc|document) or false Note: when 'document' is specified, parameter and return wrappers are created for you automatically
	* @param	mixed $use optional (encoded|literal) or false
	* @param	string $documentation optional Description to include in WSDL
	* @param	string $encodingStyle optional (usually 'http://schemas.xmlsoap.org/soap/encoding/' for encoded)
	*/
	public function register( $name, $in = array(), $out = array(), $namespace = false, $soapaction = false, $style = false, $use = false, $documentation = '', $encodingStyle = '') {

		if ($this->externalWSDLURL) {
			die('You cannot bind to an external WSDL file, and register methods outside of it! Please choose either WSDL or no WSDL.');
		}
		if (! $name) {
			die('You must specify a name when you register an operation');
		}
		if (!is_array($in)) {
			die('You must provide an array for operation inputs');
		}
		if (!is_array($out)) {
			die('You must provide an array for operation outputs');
		}
		if (false == $soapaction) {
			if (isset($_SERVER)) {
				$SERVER_NAME = $_SERVER['SERVER_NAME'];
				$SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
				$HTTPS       = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : ( isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off' );
			} elseif (isset($_SERVER)) {
				$SERVER_NAME = $_SERVER['SERVER_NAME'];
				$SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
				$HTTPS       = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off';
			} else {
				$this->setError('Neither _SERVER nor HTTP_SERVER_VARS is available');
			}
			if ('1' == $HTTPS || 'on' === $HTTPS) {
				$SCHEME = 'https';
			} else {
				$SCHEME = 'http';
			}
			$soapaction = "$SCHEME://$SERVER_NAME$SCRIPT_NAME/$name";
		}
		if (false == $style) {
			$style = 'rpc';
		}
		if (false == $use) {
			$use = 'encoded';
		}
		if ('encoded' === $use && empty( $encodingStyle )) {
			$encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/';
		}

		$this->operations[$name] = array(
		'name' => $name,
		'in' => $in,
		'out' => $out,
		'namespace' => $namespace,
		'soapaction' => $soapaction,
		'style' => $style);
		if ($this->wsdl) {
			$this->wsdl->addOperation($name, $in, $out, $namespace, $soapaction, $style, $use, $documentation, $encodingStyle);
		}
		return true;
	}

	/**
	* Specify a fault to be returned to the client.
	* This also acts as a flag to the server that a fault has occured.
	*
	* @param	string $faultcode
	* @param	string $faultstring
	* @param	string $faultactor
	* @param	string $faultdetail
	*/
	public function fault( $faultcode, $faultstring, $faultactor = '', $faultdetail = '') {
		if (empty( $faultdetail ) && $this->debug_flag) {
			$faultdetail = $this->getDebug();
		}
		$this->fault                   = new nusoap_fault($faultcode, $faultactor, $faultstring, $faultdetail);
		$this->fault->soap_defencoding = $this->soap_defencoding;
	}

	/**
	* Sets up wsdl object.
	* Acts as a flag to enable internal WSDL generation
	*
	* @param string $serviceName, name of the service
	* @param mixed $namespace optional 'tns' service namespace or false
	* @param mixed $endpoint optional URL of service endpoint or false
	* @param string $style optional (rpc|document) WSDL style (also specified by operation)
	* @param string $transport optional SOAP transport
	* @param mixed $schemaTargetNamespace optional 'types' targetNamespace for service schema or false
	*/
	public function configureWSDL( $serviceName, $namespace = false, $endpoint = false, $style = 'rpc', $transport = 'http://schemas.xmlsoap.org/soap/http', $schemaTargetNamespace = false) {

		if (isset($_SERVER)) {
			$SERVER_NAME = $_SERVER['SERVER_NAME'];
			$SERVER_PORT = $_SERVER['SERVER_PORT'];
			$SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
			$HTTPS       = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : ( isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off' );
		} elseif (isset($_SERVER)) {
			$SERVER_NAME = $_SERVER['SERVER_NAME'];
			$SERVER_PORT = $_SERVER['SERVER_PORT'];
			$SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
			$HTTPS       = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off';
		} else {
			$this->setError('Neither _SERVER nor HTTP_SERVER_VARS is available');
		}
		// If server name has port number attached then strip it (else port number gets duplicated in WSDL output) (occurred using lighttpd and FastCGI)
		$colon = strpos($SERVER_NAME, ':');
		if ($colon) {
			$SERVER_NAME = substr($SERVER_NAME, 0, $colon);
		}
		if (80 == $SERVER_PORT) {
			$SERVER_PORT = '';
		} else {
			$SERVER_PORT = ':' . $SERVER_PORT;
		}
		if (false == $namespace) {
			$namespace = "http://$SERVER_NAME/soap/$serviceName";
		}

		if (false == $endpoint) {
			if ('1' == $HTTPS || 'on' === $HTTPS) {
				$SCHEME = 'https';
			} else {
				$SCHEME = 'http';
			}
			$endpoint = "$SCHEME://$SERVER_NAME$SERVER_PORT$SCRIPT_NAME";
		}

		if (false == $schemaTargetNamespace) {
			$schemaTargetNamespace = $namespace;
		}

		$this->wsdl                     = new wsdl();
		$this->wsdl->serviceName        = $serviceName;
		$this->wsdl->endpoint           = $endpoint;
		$this->wsdl->namespaces['tns']  = $namespace;
		$this->wsdl->namespaces['soap'] = 'http://schemas.xmlsoap.org/wsdl/soap/';
		$this->wsdl->namespaces['wsdl'] = 'http://schemas.xmlsoap.org/wsdl/';
		if ($schemaTargetNamespace != $namespace) {
			$this->wsdl->namespaces['types'] = $schemaTargetNamespace;
		}
		$this->wsdl->schemas[$schemaTargetNamespace][0] = new nusoap_xmlschema('', '', $this->wsdl->namespaces);
		if ('document' === $style) {
			$this->wsdl->schemas[$schemaTargetNamespace][0]->schemaInfo['elementFormDefault'] = 'qualified';
		}
		$this->wsdl->schemas[$schemaTargetNamespace][0]->schemaTargetNamespace                                   = $schemaTargetNamespace;
		$this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/soap/encoding/'][0] = array('location' => '', 'loaded' => true);
		$this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/wsdl/'][0]          = array('location' => '', 'loaded' => true);
		$this->wsdl->bindings[$serviceName . 'Binding'] = array(
			'name'=>$serviceName . 'Binding',
			'style'=>$style,
			'transport'=>$transport,
			'portType'=>$serviceName . 'PortType');
		$this->wsdl->ports[$serviceName . 'Port']       = array(
			'binding'=>$serviceName . 'Binding',
			'location'=>$endpoint,
			'bindingType'=>'http://schemas.xmlsoap.org/wsdl/soap/');
	}
}

/**
 * Backward compatibility
 */
class soap_server extends nusoap_server {
}



