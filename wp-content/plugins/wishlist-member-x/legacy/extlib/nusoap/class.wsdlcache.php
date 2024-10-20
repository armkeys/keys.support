<?php
/*
The NuSOAP project home is:
http://sourceforge.net/projects/nusoap/

The primary support for NuSOAP is the mailing list:
nusoap-general@lists.sourceforge.net
*/

/**
* Caches instances of the wsdl class
* 
* @version  $Id: class.wsdlcache.php,v 1.7 2007/04/17 16:34:03 snichol Exp $
*/
class nusoap_wsdlcache {
	/**
	 * fplock
	 *
	 * @var resource
	 */
	public $fplock;
	/**
	 * cache_lifetime
	 *
	 * @var integer
	 */
	public $cache_lifetime;
	/**
	 * cache_dir
	 *
	 * @var string
	 */
	public $cache_dir;
	/**
	 * debug_str
	 *
	 * @var string
	 */
	public $debug_str = '';

	/**
	* Constructor
	*
	* @param string $cache_dir directory for cache-files
	* @param integer $cache_lifetime lifetime for caching-files in seconds or 0 for unlimited
	*/
	public function __construct( $cache_dir = '.', $cache_lifetime = 0) {
		$this->fplock         = array();
		$this->cache_dir      = '' != $cache_dir ? $cache_dir : '.';
		$this->cache_lifetime = $cache_lifetime;
	}

	/**
	* Creates the filename used to cache a wsdl instance
	*
	* @param string $wsdl The URL of the wsdl instance
	* @return string The filename used to cache the instance
	*/
	public function createFilename( $wsdl) {
		return $this->cache_dir . '/wsdlcache-' . md5($wsdl);
	}

	/**
	* Adds debug data to the class level debug string
	*
	* @param    string $string debug data
	*/
	public function debug( $string) {
		$this->debug_str .= get_class($this) . ": $string\n";
	}

	/**
	* Gets a wsdl instance from the cache
	*
	* @param string $wsdl The URL of the wsdl instance
	* @return object wsdl The cached wsdl instance, null if the instance is not in the cache
	*/
	public function get( $wsdl) {
		$filename = $this->createFilename($wsdl);
		if ($this->obtainMutex($filename, 'r')) {
			// Check for expired WSDL that must be removed from the cache.
			if ($this->cache_lifetime > 0) {
				if (file_exists($filename) && ( time() - filemtime($filename) > $this->cache_lifetime )) {
					unlink($filename);
					$this->debug("Expired $wsdl ($filename) from cache");
					$this->releaseMutex($filename);
					return null;
				}
			}
			// See what there is to return.
			if (!file_exists($filename)) {
				$this->debug("$wsdl ($filename) not in cache (1)");
				$this->releaseMutex($filename);
				return null;
			}
			$fp = @fopen($filename, 'r');
			if ($fp) {
				$s = implode('', @file($filename));
				fclose($fp);
				$this->debug("Got $wsdl ($filename) from cache");
			} else {
				$s = null;
				$this->debug("$wsdl ($filename) not in cache (2)");
			}
			$this->releaseMutex($filename);
			return ( !is_null($s) ) ? unserialize($s) : null;
		} else {
			$this->debug("Unable to obtain mutex for $filename in get");
		}
		return null;
	}

	/**
	* Obtains the local mutex
	*
	* @param string $filename The Filename of the Cache to lock
	* @param string $mode The open-mode ("r" or "w") or the file - affects lock-mode
	* @return boolean Lock successfully obtained ?!
	*/
	public function obtainMutex( $filename, $mode) {
		if (isset($this->fplock[md5($filename)])) {
			$this->debug("Lock for $filename already exists");
			return false;
		}
		$this->fplock[md5($filename)] = fopen($filename . '.lock', 'w');
		if ('r' == $mode) {
			return flock($this->fplock[md5($filename)], LOCK_SH);
		} else {
			return flock($this->fplock[md5($filename)], LOCK_EX);
		}
	}

	/**
	* Adds a wsdl instance to the cache
	*
	* @param object wsdl $wsdl_instance The wsdl instance to add
	* @return boolean WSDL successfully cached
	*/
	public function put( $wsdl_instance) {
		$filename = $this->createFilename($wsdl_instance->wsdl);
		$s        = serialize($wsdl_instance);
		if ($this->obtainMutex($filename, 'w')) {
			$fp = fopen($filename, 'w');
			if (! $fp) {
				$this->debug("Cannot write $wsdl_instance->wsdl ($filename) in cache");
				$this->releaseMutex($filename);
				return false;
			}
			fputs($fp, $s);
			fclose($fp);
			$this->debug("Put $wsdl_instance->wsdl ($filename) in cache");
			$this->releaseMutex($filename);
			return true;
		} else {
			$this->debug("Unable to obtain mutex for $filename in put");
		}
		return false;
	}

	/**
	* Releases the local mutex
	*
	* @param string $filename The Filename of the Cache to lock
	* @return boolean Lock successfully released
	*/
	public function releaseMutex( $filename) {
		$ret = flock($this->fplock[md5($filename)], LOCK_UN);
		fclose($this->fplock[md5($filename)]);
		unset($this->fplock[md5($filename)]);
		if (! $ret) {
			$this->debug("Not able to release lock for $filename");
		}
		return $ret;
	}

	/**
	* Removes a wsdl instance from the cache
	*
	* @param string $wsdl The URL of the wsdl instance
	* @return boolean Whether there was an instance to remove
	*/
	public function remove( $wsdl) {
		$filename = $this->createFilename($wsdl);
		if (!file_exists($filename)) {
			$this->debug("$wsdl ($filename) not in cache to be removed");
			return false;
		}
		// Ignore errors obtaining mutex.
		$this->obtainMutex($filename, 'w');
		$ret = unlink($filename);
		$this->debug("Removed ($ret) $wsdl ($filename) from cache");
		$this->releaseMutex($filename);
		return $ret;
	}
}

/**
 * For backward compatibility
 */
class wsdlcache extends nusoap_wsdlcache {
}

