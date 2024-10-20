<?php
/**
 * PPConfigManager loads the SDK configuration file and
 * hands out appropriate config params to other classes
 */


class PPConfigManager {

	private $config;
	
	// Default config values.
	public static $defaults = array(
		"http.ConnectionTimeOut" => "30",
		"http.Retry" => "5",
	);

	/**
	 * @var PPConfigManager
	 */
	private static $instance;

	private function __construct(){
		if(defined('PP_CONFIG_PATH')) {
			$configFile = constant('PP_CONFIG_PATH') . '/sdk_config.ini';
		} else {		
			$configFile = implode(DIRECTORY_SEPARATOR,
				array(dirname(__FILE__), "..", "config", "sdk_config.ini"));
		}
		$this->load($configFile);
	}

	// Create singleton object for PPConfigManager.
	public static function getInstance()
	{
		if ( !isset(self::$instance) ) {
			self::$instance = new PPConfigManager();
		}
		return self::$instance;
	}

	// Used to load the file.
	private function load($fileName) {

		$this->config = @parse_ini_file($fileName);
		if(NULL == $this->config || 0 == count($this->config)) {
			throw new PPConfigurationException("Config file $fileName not found","303");
		}
	}

	/**
	 * simple getter for configuration params
	 * If an exact match for key is not found,
	 * does a "contains" search on the key
	 */
	public function get($searchKey){

		if(array_key_exists($searchKey, $this->config))
		{
			return $this->config[$searchKey];
		}
		else {
			$arr = array();
			foreach ($this->config as $k => $v){
				if(strstr($k, $searchKey)){
					$arr[$k] = $v;
				}
			}
			
			return $arr;
		}

	}

	/**
	 * Utility method for handling account configuration
	 * return config key corresponding to the API userId passed in
	 *
	 * If $userId is null, returns config keys corresponding to
	 * all configured accounts
	 */
	public function getIniPrefix($userId = null) {

		if(null == $userId) {
			$arr = array();
			foreach ($this->config as $key => $value) {
				$pos = strpos($key, '.');
				if(strstr($key, "acct")){
					$arr[] = substr($key, 0, $pos);
				}
			}
			return array_unique($arr);
		} else {
			$iniPrefix = array_search($userId, $this->config);
			$pos = strpos($iniPrefix, '.');
			$acct = substr($iniPrefix, 0, $pos);
			
			return $acct;
		}
	}
	
	/**
	 * returns the config file hashmap
	 * 
	 */
	private function getConfigHashmap()
	{
		return $this->config;
	}
	
	/**
	 * use  the default configuration if it is not passed in hashmap
	 */
	public static function getConfigWithDefaults($config=null)
	{
		return array_merge(PPConfigManager::$defaults, 
				(null != $config) ? $config : PPConfigManager::getInstance()->getConfigHashmap());
	}
}

    