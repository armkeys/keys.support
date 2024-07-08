<?php

/*
$Id: class.nusoap_base.php,v 1.56 2010/04/26 20:15:08 snichol Exp $

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

The NuSOAP project home is:
http://sourceforge.net/projects/nusoap/

The primary support for NuSOAP is the Help forum on the project home page.

If you have any questions or comments, please email:

Dietrich Ayala
dietrich@ganx4.com
http://dietrich.ganx4.com/nusoap

NuSphere Corporation
http://www.nusphere.com

*/

/*
 *	Some of the standards implmented in whole or part by NuSOAP:
 *
 *	SOAP 1.1 (http://www.w3.org/TR/2000/NOTE-SOAP-20000508/)
 *	WSDL 1.1 (http://www.w3.org/TR/2001/NOTE-wsdl-20010315)
 *	SOAP Messages With Attachments (http://www.w3.org/TR/SOAP-attachments)
 *	XML 1.0 (http://www.w3.org/TR/2006/REC-xml-20060816/)
 *	Namespaces in XML 1.0 (http://www.w3.org/TR/2006/REC-xml-names-20060816/)
 *	XML Schema 1.0 (http://www.w3.org/TR/xmlschema-0/)
 *	RFC 2045 Multipurpose Internet Mail Extensions (MIME) Part One: Format of Internet Message Bodies
 *	RFC 2068 Hypertext Transfer Protocol -- HTTP/1.1
 *	RFC 2617 HTTP Authentication: Basic and Digest Access Authentication
 */

/* load classes

// Necessary classes.
require_once('class.soapclient.php');
require_once('class.soap_val.php');
require_once('class.soap_parser.php');
require_once('class.soap_fault.php');

// Transport classes.
require_once('class.soap_transport_http.php');

// Optional add-on classes.
require_once('class.xmlschema.php');
require_once('class.wsdl.php');

// Server class.
require_once('class.soap_server.php');*/

// Class variable emulation.
// Cf. http://www.webkreator.com/php/techniques/php-static-class-variables.html.
$GLOBALS['_transient']['static']['nusoap_base']['globalDebugLevel'] = 9;

/**
*
* Nusoap_base
*
* @version  $Id: class.nusoap_base.php,v 1.56 2010/04/26 20:15:08 snichol Exp $
*/
class nusoap_base {
	/**
	 * Identification for HTTP headers.
	 *
	 * @var string
	 */
	public $title = 'NuSOAP';
	/**
	 * Version for HTTP headers.
	 *
	 * @var string
	 */
	public $version = '0.9.5';
	/**
	 * CVS revision for HTTP headers.
	 *
	 * @var string
	 */
	public $revision = '$Revision: 1.56 $';
	/**
	 * Current error string (manipulated by getError/setError)
	 *
	 * @var string
	 */
	public $error_str = '';
	/**
	 * Current debug string (manipulated by debug/appendDebug/clearDebug/getDebug/getDebugAsXMLComment)
	 *
	 * @var string
	 */
	public $debug_str = '';
	/**
	 * Toggles automatic encoding of special characters as entities
	 * (should always be true, I think)
	 *
	 * @var boolean
	 */
	public $charencoding = true;
	/**
	 * The debug level for this instance
	 *
	 * @var	integer
	 */
	public $debugLevel;

	/**
	* Set schema version
	*
	* @var      string
	*/
	public $XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';

	/**
	* Charset encoding for outgoing messages
	*
	* @var      string
	*/
	public $soap_defencoding = 'ISO-8859-1';
	// Var $soap_defencoding = 'UTF-8';

	/**
	* Namespaces in an array of prefix => uri
	*
	* This is "seeded" by a set of constants, but it may be altered by code
	*
	* @var      array
	*/
	public $namespaces = array(
		'SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
		'xsd' => 'http://www.w3.org/2001/XMLSchema',
		'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
		'SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/'
		);

	/**
	* Namespaces used in the current context, e.g. during serialization
	*
	* @var      array
	*/
	public $usedNamespaces = array();

	/**
	* XML Schema types in an array of uri => (array of xml type => php type)
	* is this legacy yet?
	* no, this is used by the nusoap_xmlschema class to verify type => namespace mappings.
	 *
	* @var      array
	*/
	public $typemap = array(
	'http://www.w3.org/2001/XMLSchema' => array(
		'string'=>'string','boolean'=>'boolean','float'=>'double','double'=>'double','decimal'=>'double',
		'duration'=>'','dateTime'=>'string','time'=>'string','date'=>'string','gYearMonth'=>'',
		'gYear'=>'','gMonthDay'=>'','gDay'=>'','gMonth'=>'','hexBinary'=>'string','base64Binary'=>'string',
		// Abstract "any" types.
		'anyType'=>'string','anySimpleType'=>'string',
		// Derived datatypes.
		'normalizedString'=>'string','token'=>'string','language'=>'','NMTOKEN'=>'','NMTOKENS'=>'','Name'=>'','NCName'=>'','ID'=>'',
		'IDREF'=>'','IDREFS'=>'','ENTITY'=>'','ENTITIES'=>'','integer'=>'integer','nonPositiveInteger'=>'integer',
		'negativeInteger'=>'integer','long'=>'integer','int'=>'integer','short'=>'integer','byte'=>'integer','nonNegativeInteger'=>'integer',
		'unsignedLong'=>'','unsignedInt'=>'','unsignedShort'=>'','unsignedByte'=>'','positiveInteger'=>''),
	'http://www.w3.org/2000/10/XMLSchema' => array(
		'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
		'float'=>'double','dateTime'=>'string',
		'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
	'http://www.w3.org/1999/XMLSchema' => array(
		'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
		'float'=>'double','dateTime'=>'string',
		'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
	'http://soapinterop.org/xsd' => array('SOAPStruct'=>'struct'),
	'http://schemas.xmlsoap.org/soap/encoding/' => array('base64'=>'string','array'=>'array','Array'=>'array'),
	'http://xml.apache.org/xml-soap' => array('Map')
	);

	/**
	* XML entities to convert
	*
	* @var      array
	* @deprecated
	* @see	expandEntities
	*/
	public $xmlEntities = array('quot' => '"','amp' => '&',
		'lt' => '<','gt' => '>','apos' => "'");

	/**
	* Constructor
	*
	*/
	public function __construct() {
		$this->debugLevel = $GLOBALS['_transient']['static']['nusoap_base']['globalDebugLevel'];
	}

	/**
	* Gets the global debug level, which applies to future instances
	*
	* @return	integer	Debug level 0-9, where 0 turns off
	*/
	public function getGlobalDebugLevel() {
		return $GLOBALS['_transient']['static']['nusoap_base']['globalDebugLevel'];
	}

	/**
	* Sets the global debug level, which applies to future instances
	*
	* @param	int	$level	Debug level 0-9, where 0 turns off
	*/
	public function setGlobalDebugLevel( $level) {
		$GLOBALS['_transient']['static']['nusoap_base']['globalDebugLevel'] = $level;
	}

	/**
	* Gets the debug level for this instance
	*
	* @return	int	Debug level 0-9, where 0 turns off
	*/
	public function getDebugLevel() {
		return $this->debugLevel;
	}

	/**
	* Sets the debug level for this instance
	*
	* @param	int	$level	Debug level 0-9, where 0 turns off
	*/
	public function setDebugLevel( $level) {
		$this->debugLevel = $level;
	}

	/**
	* Adds debug data to the instance debug string with formatting
	*
	* @param    string $string debug data
	*/
	public function debug( $string) {
		if ($this->debugLevel > 0) {
			$this->appendDebug($this->getmicrotime() . ' ' . get_class($this) . ": $string\n");
		}
	}

	/**
	* Adds debug data to the instance debug string without formatting
	*
	* @param    string $string debug data
	*/
	public function appendDebug( $string) {
		if ($this->debugLevel > 0) {
			// It would be nice to use a memory stream here to use.
			// Memory more efficiently.
			$this->debug_str .= $string;
		}
	}

	/**
	* Clears the current debug data for this instance
	*
	*/
	public function clearDebug() {
		// It would be nice to use a memory stream here to use.
		// Memory more efficiently.
		$this->debug_str = '';
	}

	/**
	* Gets the current debug data for this instance
	*
	* @return   debug data
	*/
	public function &getDebug() {
		// It would be nice to use a memory stream here to use.
		// Memory more efficiently.
		return $this->debug_str;
	}

	/**
	* Gets the current debug data for this instance as an XML comment
	* this may change the contents of the debug data
	*
	* @return   debug data as an XML comment
	*/
	public function &getDebugAsXMLComment() {
		// It would be nice to use a memory stream here to use.
		// Memory more efficiently.
		while (strpos($this->debug_str, '--')) {
			$this->debug_str = str_replace('--', '- -', $this->debug_str);
		}
		$ret = "<!--\n" . $this->debug_str . "\n-->";
		return $ret;
	}

	/**
	* Expands entities, e.g. changes '<' to '&lt;'.
	*
	* @param	string	$val	The string in which to expand entities.
	*/
	public function expandEntities( $val) {
		if ($this->charencoding) {
			$val = str_replace('&', '&amp;', $val);
			$val = str_replace("'", '&apos;', $val);
			$val = str_replace('"', '&quot;', $val);
			$val = str_replace('<', '&lt;', $val);
			$val = str_replace('>', '&gt;', $val);
		}
		return $val;
	}

	/**
	* Returns error string if present
	*
	* @return   mixed error string or false
	*/
	public function getError() {
		if ('' != $this->error_str) {
			return $this->error_str;
		}
		return false;
	}

	/**
	* Sets error string
	*
	* @return   boolean $string error string
	*/
	public function setError( $str) {
		$this->error_str = $str;
	}

	/**
	* Detect if array is a simple array or a struct (associative array)
	*
	* @param	mixed	$val	The PHP array
	* @return	string	(arraySimple|arrayStruct)
	*/
	public function isArraySimpleOrStruct( $val) {
		$keyList = array_keys($val);
		foreach ($keyList as $keyListValue) {
			if (!is_int($keyListValue)) {
				return 'arrayStruct';
			}
		}
		return 'arraySimple';
	}

	/**
	* Serializes PHP values in accordance w/ section 5. Type information is
	* not serialized if $use == 'literal'.
	*
	* @param	mixed	$val	The value to serialize
	* @param	string	$name	The name (local part) of the XML element
	* @param	string	$type	The XML schema type (local part) for the element
	* @param	string	$name_ns	The namespace for the name of the XML element
	* @param	string	$type_ns	The namespace for the type of the element
	* @param	array	$attributes	The attributes to serialize as name=>value pairs
	* @param	string	$use	The WSDL "use" (encoded|literal)
	* @param	boolean	$soapval	Whether this is called from soapval.
	* @return	string	The serialized element, possibly with child elements
	*/
	public function serialize_val( $val, $name = false, $type = false, $name_ns = false, $type_ns = false, $attributes = false, $use = 'encoded', $soapval = false) {
		$this->debug("in serialize_val: name=$name, type=$type, name_ns=$name_ns, type_ns=$type_ns, use=$use, soapval=$soapval");
		$this->appendDebug('value=' . $this->varDump($val));
		$this->appendDebug('attributes=' . $this->varDump($attributes));

		if (is_object($val) && 'soapval' === get_class($val) && ( ! $soapval )) {
			$this->debug('serialize_val: serialize soapval');
			$xml = $val->serialize($use);
			$this->appendDebug($val->getDebug());
			$val->clearDebug();
			$this->debug("serialize_val of soapval returning $xml");
			return $xml;
		}
		// Force valid name if necessary.
		if (is_numeric($name)) {
			$name = '__numeric_' . $name;
		} elseif (! $name) {
			$name = 'noname';
		}
		// If name has ns, add ns prefix to name.
		$xmlns = '';
		if ($name_ns) {
			$prefix = 'nu' . rand(1000, 9999);
			$name   = $prefix . ':' . $name;
			$xmlns .= " xmlns:$prefix=\"$name_ns\"";
		}
		// If type is prefixed, create type prefix.
		if ('' != $type_ns && $type_ns == $this->namespaces['xsd']) {
			// Need to fix this. shouldn't default to xsd if no ns specified.
			// W/o checking against typemap.
			$type_prefix = 'xsd';
		} elseif ($type_ns) {
			$type_prefix = 'ns' . rand(1000, 9999);
			$xmlns      .= " xmlns:$type_prefix=\"$type_ns\"";
		}
		// Serialize attributes if present.
		$atts = '';
		if ($attributes) {
			foreach ($attributes as $k => $v) {
				$atts .= " $k=\"" . $this->expandEntities($v) . '"';
			}
		}
		// Serialize null value.
		if (is_null($val)) {
			$this->debug('serialize_val: serialize null');
			if ('literal' === $use) {
				// TODO: depends on minOccurs.
				$xml = "<$name$xmlns$atts/>";
				$this->debug("serialize_val returning $xml");
				return $xml;
			} else {
				if (isset($type) && isset($type_prefix)) {
					$type_str = " xsi:type=\"$type_prefix:$type\"";
				} else {
					$type_str = '';
				}
				$xml = "<$name$xmlns$type_str$atts xsi:nil=\"true\"/>";
				$this->debug("serialize_val returning $xml");
				return $xml;
			}
		}
		// Serialize if an xsd built-in primitive type.
		if ('' != $type && isset($this->typemap[$this->XMLSchemaVersion][$type])) {
			$this->debug('serialize_val: serialize xsd built-in primitive type');
			if (is_bool($val)) {
				if ('boolean' === $type) {
					$val = $val ? 'true' : 'false';
				} elseif (! $val) {
					$val = 0;
				}
			} elseif (is_string($val)) {
				$val = $this->expandEntities($val);
			}
			if ('literal' === $use) {
				$xml = "<$name$xmlns$atts>$val</$name>";
				$this->debug("serialize_val returning $xml");
				return $xml;
			} else {
				$xml = "<$name$xmlns xsi:type=\"xsd:$type\"$atts>$val</$name>";
				$this->debug("serialize_val returning $xml");
				return $xml;
			}
		}
		// Detect type and serialize.
		$xml = '';
		switch (true) {
			case ( is_bool($val) || 'boolean' === $type ):
				$this->debug('serialize_val: serialize boolean');
				if ('boolean' === $type) {
					$val = $val ? 'true' : 'false';
				} elseif (! $val) {
					$val = 0;
				}
				if ('literal' === $use) {
					$xml .= "<$name$xmlns$atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:boolean\"$atts>$val</$name>";
				}
				break;
			case ( is_int($val) || is_long($val) || 'int' === $type ):
				$this->debug('serialize_val: serialize int');
				if ('literal' === $use) {
					$xml .= "<$name$xmlns$atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:int\"$atts>$val</$name>";
				}
				break;
			case ( is_float($val)|| is_double($val) || 'float' === $type ):
				$this->debug('serialize_val: serialize float');
				if ('literal' === $use) {
					$xml .= "<$name$xmlns$atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:float\"$atts>$val</$name>";
				}
				break;
			case ( is_string($val) || 'string' === $type ):
				$this->debug('serialize_val: serialize string');
				$val = $this->expandEntities($val);
				if ('literal' === $use) {
					$xml .= "<$name$xmlns$atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:string\"$atts>$val</$name>";
				}
				break;
			case is_object($val):
				$this->debug('serialize_val: serialize object');
				if ('soapval' === get_class($val)) {
					$this->debug('serialize_val: serialize soapval object');
					$pXml = $val->serialize($use);
					$this->appendDebug($val->getDebug());
					$val->clearDebug();
				} else {
					if (! $name) {
						$name = get_class($val);
						$this->debug("In serialize_val, used class name $name as element name");
					} else {
						$this->debug("In serialize_val, do not override name $name for element name for class " . get_class($val));
					}
					foreach (get_object_vars($val) as $k => $v) {
						$pXml = isset($pXml) ? $pXml . $this->serialize_val($v, $k, false, false, false, false, $use) : $this->serialize_val($v, $k, false, false, false, false, $use);
					}
				}
				if (isset($type) && isset($type_prefix)) {
					$type_str = " xsi:type=\"$type_prefix:$type\"";
				} else {
					$type_str = '';
				}
				if ('literal' === $use) {
					$xml .= "<$name$xmlns$atts>$pXml</$name>";
				} else {
					$xml .= "<$name$xmlns$type_str$atts>$pXml</$name>";
				}
				break;
			break;
			case ( is_array($val) || $type ):
				// Detect if struct or array.
				$valueType = $this->isArraySimpleOrStruct($val);
				if ('arraySimple'==$valueType || preg_match('/^ArrayOf/', $type)) {
					$this->debug('serialize_val: serialize array');
					$i = 0;
					if (is_array($val) && count($val)> 0) {
						foreach ($val as $v) {
							if (is_object($v) && 'soapval' ===  get_class($v)) {
								$tt_ns = $v->type_ns;
								$tt    = $v->type;
							} elseif (is_array($v)) {
								$tt = $this->isArraySimpleOrStruct($v);
							} else {
								$tt = gettype($v);
							}
							$array_types[$tt] = 1;
							// TODO: for literal, the name should be $name.
							$xml .= $this->serialize_val($v, 'item', false, false, false, false, $use);
							++$i;
						}
						if (count($array_types) > 1) {
							$array_typename = 'xsd:anyType';
						} elseif (isset($tt) && isset($this->typemap[$this->XMLSchemaVersion][$tt])) {
							if ('integer' === $tt) {
								$tt = 'int';
							}
							$array_typename = 'xsd:' . $tt;
						} elseif (isset($tt) && 'arraySimple' === $tt) {
							$array_typename = 'SOAP-ENC:Array';
						} elseif (isset($tt) && 'arrayStruct' === $tt) {
							$array_typename = 'unnamed_struct_use_soapval';
						} else {
							// If type is prefixed, create type prefix.
							if ('' != $tt_ns && $tt_ns == $this->namespaces['xsd']) {
								 $array_typename = 'xsd:' . $tt;
							} elseif ($tt_ns) {
								$tt_prefix      = 'ns' . rand(1000, 9999);
								$array_typename = "$tt_prefix:$tt";
								$xmlns         .= " xmlns:$tt_prefix=\"$tt_ns\"";
							} else {
								$array_typename = $tt;
							}
						}
						$array_type = $i;
						if ('literal' === $use) {
							$type_str = '';
						} elseif (isset($type) && isset($type_prefix)) {
							$type_str = " xsi:type=\"$type_prefix:$type\"";
						} else {
							$type_str = ' xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="' . $array_typename . "[$array_type]\"";
						}
					// Empty array.
					} else {
						if ('literal' === $use) {
							$type_str = '';
						} elseif (isset($type) && isset($type_prefix)) {
							$type_str = " xsi:type=\"$type_prefix:$type\"";
						} else {
							$type_str = ' xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:anyType[0]"';
						}
					}
					// TODO: for array in literal, there is no wrapper here.
					$xml = "<$name$xmlns$type_str$atts>" . $xml . "</$name>";
				} else {
					// Got a struct.
					$this->debug('serialize_val: serialize struct');
					if (isset($type) && isset($type_prefix)) {
						$type_str = " xsi:type=\"$type_prefix:$type\"";
					} else {
						$type_str = '';
					}
					if ('literal' === $use) {
						$xml .= "<$name$xmlns$atts>";
					} else {
						$xml .= "<$name$xmlns$type_str$atts>";
					}
					foreach ($val as $k => $v) {
						// Apache Map.
						if ('Map' === $type && 'http://xml.apache.org/xml-soap' == $type_ns) {
							$xml .= '<item>';
							$xml .= $this->serialize_val($k, 'key', false, false, false, false, $use);
							$xml .= $this->serialize_val($v, 'value', false, false, false, false, $use);
							$xml .= '</item>';
						} else {
							$xml .= $this->serialize_val($v, $k, false, false, false, false, $use);
						}
					}
					$xml .= "</$name>";
				}
				break;
			default:
				$this->debug('serialize_val: serialize unknown');
				$xml .= 'not detected, got ' . gettype($val) . ' for ' . $val;
				break;
		}
		$this->debug("serialize_val returning $xml");
		return $xml;
	}

	/**
	* Serializes a message
	*
	* @param string $body the XML of the SOAP body
	* @param mixed $headers optional string of XML with SOAP header content, or array of soapval objects for SOAP headers, or associative array
	* @param array $namespaces optional the namespaces used in generating the body and headers
	* @param string $style optional (rpc|document)
	* @param string $use optional (encoded|literal)
	* @param string $encodingStyle optional (usually 'http://schemas.xmlsoap.org/soap/encoding/' for encoded)
	* @return string the message
	*/
	public function serializeEnvelope( $body, $headers = false, $namespaces = array(), $style = 'rpc', $use = 'encoded', $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/') {
	// TODO: add an option to automatically run utf8_encode on $body and $headers.
	// If $this->soap_defencoding is UTF-8.  Not doing this automatically allows.
	// One to send arbitrary UTF-8 characters, not just characters that map to ISO-8859-1.

	$this->debug('In serializeEnvelope length=' . strlen($body) . ' body (max 1000 characters)=' . substr($body, 0, 1000) . " style=$style use=$use encodingStyle=$encodingStyle");
	$this->debug('headers:');
	$this->appendDebug($this->varDump($headers));
	$this->debug('namespaces:');
	$this->appendDebug($this->varDump($namespaces));

	// Serialize namespaces.
	$ns_string = '';
		foreach (array_merge($this->namespaces, $namespaces) as $k => $v) {
			$ns_string .= " xmlns:$k=\"$v\"";
		}
		if ($encodingStyle) {
			$ns_string = " SOAP-ENV:encodingStyle=\"$encodingStyle\"$ns_string";
		}

	// Serialize headers.
		if ($headers) {
			if (is_array($headers)) {
				$xml = '';
				foreach ($headers as $k => $v) {
					if (is_object($v) && 'soapval' === get_class($v)) {
						$xml .= $this->serialize_val($v, false, false, false, false, false, $use);
					} else {
						$xml .= $this->serialize_val($v, $k, false, false, false, false, $use);
					}
				}
				$headers = $xml;
				$this->debug("In serializeEnvelope, serialized array of headers to $headers");
			}
			$headers = '<SOAP-ENV:Header>' . $headers . '</SOAP-ENV:Header>';
		}
	// Serialize envelope.
	return
	'<?xml version="1.0" encoding="' . $this->soap_defencoding . '"?' . '>' .
	'<SOAP-ENV:Envelope' . $ns_string . '>' .
	$headers .
	'<SOAP-ENV:Body>' .
		$body .
	'</SOAP-ENV:Body>' .
	'</SOAP-ENV:Envelope>';
	}

	/**
	 * Formats a string to be inserted into an HTML stream
	 *
	 * @param string $str The string to format
	 * @return string The formatted string
	 * @deprecated
	 */
	public function formatDump( $str) {
		$str = htmlspecialchars($str);
		return nl2br($str);
	}

	/**
	* Contracts (changes namespace to prefix) a qualified name
	*
	* @param    string $qname qname
	* @return	string contracted qname
	*/
	public function contractQname( $qname) {
		// Get element namespace.
		//$this->xdebug("Contract $qname");
		if (strrpos($qname, ':')) {
			// Get unqualified name.
			$name = substr($qname, strrpos($qname, ':') + 1);
			// Get ns.
			$ns = substr($qname, 0, strrpos($qname, ':'));
			$p  = $this->getPrefixFromNamespace($ns);
			if ($p) {
				return $p . ':' . $name;
			}
			return $qname;
		} else {
			return $qname;
		}
	}

	/**
	* Expands (changes prefix to namespace) a qualified name
	*
	* @param    string $qname qname
	* @return	string expanded qname
	*/
	public function expandQname( $qname) {
		// Get element prefix.
		if (strpos($qname, ':') && !preg_match('/^http:\/\//', $qname)) {
			// Get unqualified name.
			$name = substr(strstr($qname, ':'), 1);
			// Get ns prefix.
			$prefix = substr($qname, 0, strpos($qname, ':'));
			if (isset($this->namespaces[$prefix])) {
				return $this->namespaces[$prefix] . ':' . $name;
			} else {
				return $qname;
			}
		} else {
			return $qname;
		}
	}

	/**
	* Returns the local part of a prefixed string
	* returns the original string, if not prefixed
	*
	* @param string $str The prefixed string
	* @return string The local part
	*/
	public function getLocalPart( $str) {
		$sstr = strrchr( $str, ':' );
		if ( $sstr ) {
			// Get unqualified name.
			return substr( $sstr, 1 );
		} else {
			return $str;
		}
	}

	/**
	* Returns the prefix part of a prefixed string
	* returns false, if not prefixed
	*
	* @param string $str The prefixed string
	* @return mixed The prefix or false if there is no prefix
	*/
	public function getPrefix( $str) {
		$pos = strrpos( $str, ':' );
		if ( $pos ) {
			// Get prefix.
			return substr($str, 0, $pos);
		}
		return false;
	}

	/**
	* Pass it a prefix, it returns a namespace
	*
	* @param string $prefix The prefix
	* @return mixed The namespace, false if no namespace has the specified prefix
	*/
	public function getNamespaceFromPrefix( $prefix) {
		if (isset($this->namespaces[$prefix])) {
			return $this->namespaces[$prefix];
		}
		//$this->setError("No namespace registered for prefix '$prefix'");
		return false;
	}

	/**
	* Returns the prefix for a given namespace (or prefix)
	* or false if no prefixes registered for the given namespace
	*
	* @param string $ns The namespace
	* @return mixed The prefix, false if the namespace has no prefixes
	*/
	public function getPrefixFromNamespace( $ns) {
		foreach ($this->namespaces as $p => $n) {
			if ($ns == $n || $ns == $p) {
				$this->usedNamespaces[$p] = $n;
				return $p;
			}
		}
		return false;
	}

	/**
	* Returns the time in ODBC canonical form with microseconds
	*
	* @return string The time in ODBC canonical form with microseconds
	*/
	public function getmicrotime() {
		if (function_exists('gettimeofday')) {
			$tod  = gettimeofday();
			$sec  = $tod['sec'];
			$usec = $tod['usec'];
		} else {
			$sec  = time();
			$usec = 0;
		}
		return strftime('%Y-%m-%d %H:%M:%S', $sec) . '.' . sprintf('%06d', $usec);
	}

	/**
	 * Returns a string with the output of var_dump
	 *
	 * @param mixed $data The variable to var_dump
	 * @return string The output of var_dump
	 */
	public function varDump( $data) {
		ob_start();
		var_dump($data);
		$ret_val = ob_get_contents();
		ob_end_clean();
		return $ret_val;
	}

	/**
	* Represents the object as a string
	*
	* @return	string
	*/
	public function __toString() {
		return $this->varDump($this);
	}
}

// XML Schema Datatype Helper Functions.

// Xsd:dateTime helpers.

/**
* Convert unix timestamp to ISO 8601 compliant date string
*
* @param    int $timestamp Unix time stamp
* @param	boolean $utc Whether the time stamp is UTC or local
* @return	mixed ISO 8601 date string or false
*/
function timestamp_to_iso8601( $timestamp, $utc = true) {
	$datestr = wlm_date('Y-m-d\TH:i:sO', $timestamp);
	$pos     = strrpos($datestr, '+');
	if (false === $pos) {
		$pos = strrpos($datestr, '-');
	}
	if (false !== $pos) {
		if (strlen($datestr) == $pos + 5) {
			$datestr = substr($datestr, 0, $pos + 3) . ':' . substr($datestr, -2);
		}
	}
	if ($utc) {
		$pattern = '/' .
		'([0-9]{4})-' . // centuries & years CCYY-
		'([0-9]{2})-' . // months MM-
		'([0-9]{2})' . // days DD
		'T' . // separator T
		'([0-9]{2}):' . // hours hh:
		'([0-9]{2}):' . // minutes mm:
		'([0-9]{2})(\.[0-9]*)?' . // seconds ss.ss...
		'(Z|[+\-][0-9]{2}:?[0-9]{2})?' . // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's
		'/';

		if (preg_match($pattern, $datestr, $regs)) {
			return sprintf('%04d-%02d-%02dT%02d:%02d:%02dZ', $regs[1], $regs[2], $regs[3], $regs[4], $regs[5], $regs[6]);
		}
		return false;
	} else {
		return $datestr;
	}
}

/**
* Convert ISO 8601 compliant date string to unix timestamp
*
* @param    string $datestr ISO 8601 compliant date string
* @return	mixed Unix timestamp (int) or false
*/
function iso8601_to_timestamp( $datestr) {
	$pattern = '/' .
	'([0-9]{4})-' . // centuries & years CCYY-
	'([0-9]{2})-' . // months MM-
	'([0-9]{2})' . // days DD
	'T' . // separator T
	'([0-9]{2}):' . // hours hh:
	'([0-9]{2}):' . // minutes mm:
	'([0-9]{2})(\.[0-9]+)?' . // seconds ss.ss...
	'(Z|[+\-][0-9]{2}:?[0-9]{2})?' . // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's
	'/';
	if (preg_match($pattern, $datestr, $regs)) {
		// Not utc.
		if ('Z' != $regs[8]) {
			$op = substr($regs[8], 0, 1);
			$h  = substr($regs[8], 1, 2);
			$m  = substr($regs[8], strlen($regs[8])-2, 2);
			if ('-' == $op) {
				$regs[4] = $regs[4] + $h;
				$regs[5] = $regs[5] + $m;
			} elseif ('+' == $op) {
				$regs[4] = $regs[4] - $h;
				$regs[5] = $regs[5] - $m;
			}
		}
		return gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
// Return strtotime("$regs[1]-$regs[2]-$regs[3] $regs[4]:$regs[5]:$regs[6]Z");
	} else {
		return false;
	}
}

/**
* Sleeps some number of microseconds
*
* @param    string $usec the number of microseconds to sleep
* @deprecated
*/
function usleepWindows( $usec) {
	$start = gettimeofday();

	do {
		$stop       = gettimeofday();
		$timePassed = 1000000 * ( $stop['sec'] - $start['sec'] )
		+ $stop['usec'] - $start['usec'];
	} while ($timePassed < $usec);
}



