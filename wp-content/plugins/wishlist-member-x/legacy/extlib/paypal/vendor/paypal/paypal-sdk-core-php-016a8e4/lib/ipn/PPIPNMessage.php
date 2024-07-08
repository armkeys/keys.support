<?php

/**
 *
 *
 */
class PPIPNMessage {

	const IPN_CMD = 'cmd=_notify-validate';

	/*
	 *@var boolean
	*
	*/
	private $isIpnVerified;

	/*
	 *@var config
	*
	*/
	private $config;
	/**
	 *
	 * @var array
	 */
	private $ipnData = array();

	/**
	 *
	 * @param string $postData OPTIONAL post data. If null,
	 * 				the class automatically reads incoming POST data
	 * 				from the input stream
	*/
	public function __construct($postData='', $config = null) {

		$this->config = PPConfigManager::getConfigWithDefaults($config);

		if(empty( $postData )) {
			// Reading posted data from directly from $_POST may causes serialization issues with array data in POST.
			// Reading raw POST data from input stream instead.
			$postData = file_get_contents('php://input');
		}

		$rawPostArray = explode('&', $postData);
		foreach ($rawPostArray as $keyValue) {
			$keyValue = explode ('=', $keyValue);
			if (2 == count($keyValue))
				$this->ipnData[urldecode($keyValue[0])] = urldecode($keyValue[1]);
		}
		// Var_dump($this->ipnData);
	}

	/**
	 * Returns a hashmap of raw IPN data
	 *
	 * @return array
	 */
	public function getRawData() {
		return $this->ipnData;
	}

	/**
	 * Validates a IPN message
	 *
	 * @return boolean
	 */
	public function validate() {
		if(isset($this->isIpnVerified))
		{
			return $this->isIpnVerified;
		}
		else
		{
			$request = self::IPN_CMD;
			foreach ($this->ipnData as $key => $value) {
				$value = urlencode($value);
				$key = urlencode($key);
				$request .= "&$key=$value";
			}

			$httpConfig = new PPHttpConfig($this->setEndpoint());
			$httpConfig->addCurlOption(CURLOPT_FORBID_REUSE, 1);
			$httpConfig->addCurlOption(CURLOPT_HTTPHEADER, array('Connection: Close'));

			$connection = PPConnectionManager::getInstance()->getConnection($httpConfig, $this->config);
			$response = $connection->execute($request);
			if('VERIFIED' === $response) {
				$this->isIpnVerified = true;
				return true;
			}
			$this->isIpnVerified = false;
			return false; // Value is 'INVALID'
		}
	}

	/**
	 * Returns the transaction id for which
	 * this IPN was generated, if one is available
	 *
	 * @return string
	 */
	public function getTransactionId() {
		if(isset($this->ipnData['txn_id'])) {
			return $this->ipnData['txn_id'];
		} else if(isset($this->ipnData['transaction[0].id'])) {
			$idx = 0;
			do {
				$transId[] =  $this->ipnData["transaction[$idx].id"];
				$idx++;
			} while(isset($this->ipnData["transaction[$idx].id"]));
			return $transId;
		}
	}

	/**
	 * Returns the transaction type for which
	 * this IPN was generated
	 *
	 * @return string
	 */
	public function getTransactionType() {
		// Check if transaction_type present. Otherwise, use txn_type.
		if (!isset($this->ipnData['transaction_type'])) {
			return $this->ipnData['txn_type'];
		}
		return $this->ipnData['transaction_type'];
	}

	private function setEndpoint()
	{
		if(isset($this->config['service.EndPoint.IPN']))
		{
			$url = $this->config['service.EndPoint.IPN'];
		}
		else if(isset($this->config['mode']))
		{
			if('SANDBOX' === strtoupper($this->config['mode']))
			{
				$url = PPConstants::IPN_SANDBOX_ENDPOINT;
			}
			else if ('LIVE' === strtoupper($this->config['mode']))
			{
				$url = PPConstants::IPN_LIVE_ENDPOINT;
			}
			else
			{
				throw new PPConfigurationException('mode should be LIVE or SANDBOX');
			}
		}
		else
		{
			throw new PPConfigurationException('You must set one of mode OR service.endpoint.IPN parameters');
		}
		return $url;
	}

}
