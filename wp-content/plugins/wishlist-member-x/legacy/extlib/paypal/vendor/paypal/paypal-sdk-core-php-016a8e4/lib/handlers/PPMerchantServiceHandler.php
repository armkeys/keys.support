<?php

/**
 *
 * Adds non-authentication headers that are specific to
 * PayPal's Merchant APIs and determines endpoint to
 * hit based on configuration parameters.
 *
 */
class PPMerchantServiceHandler extends PPGenericServiceHandler {

	private $apiUsername;

	public function __construct($apiUsername, $sdkName, $sdkVersion) {
		parent::__construct($sdkName, $sdkVersion);
		$this->apiUsername = $apiUsername;
	}

	public function handle($httpConfig, $request, $options) {
		parent::handle($httpConfig, $request, $options);
		$config = $options['config'];

		if(is_string($this->apiUsername) || is_null($this->apiUsername)) {
			// $apiUsername is optional, if null the default account in config file is taken.
			$credMgr = PPCredentialManager::getInstance($options['config']);
			$request->setCredential(clone $credMgr->getCredentialObject($this->apiUsername));
		} else {
			$request->setCredential($this->apiUsername);
		}

		$endpoint = '';
		$credential = $request->getCredential();
		if(isset($options['port']) && isset($config['service.EndPoint.'.$options['port']]))
		{
			$endpoint = $config['service.EndPoint.'.$options['port']];
		}
		// For backward compatibilty (for those who are using old config files with 'service.EndPoint')
		else if (isset($config['service.EndPoint']))
		{
			$endpoint = $config['service.EndPoint'];
		}
		else if (isset($config['mode']))
		{
			if('SANDBOX' === strtoupper($config['mode']))
			{
				if($credential instanceof PPSignatureCredential)
				{
					$endpoint = PPConstants::MERCHANT_SANDBOX_SIGNATURE_ENDPOINT;
				}
				else if($credential instanceof PPCertificateCredential)
				{
					$endpoint = PPConstants::MERCHANT_SANDBOX_CERT_ENDPOINT;
				}
			}
			else if('LIVE' === strtoupper($config['mode']))
			{
			if($credential instanceof PPSignatureCredential)
				{
					$endpoint = PPConstants::MERCHANT_LIVE_SIGNATURE_ENDPOINT;
				}
				else if($credential instanceof PPCertificateCredential)
				{
					$endpoint = PPConstants::MERCHANT_LIVE_CERT_ENDPOINT;
				}
			}
		}
		else
		{
			throw new PPConfigurationException('endpoint Not Set');
		}

		if('SOAP' === $request->getBindingType())
		{
			$httpConfig->setUrl($endpoint);
		}
		else
		{
			throw new PPConfigurationException('expecting service binding to be SOAP');
		}

		$request->addBindingInfo("namespace", "xmlns:ns=\"urn:ebay:api:PayPalAPI\" xmlns:ebl=\"urn:ebay:apis:eBLBaseComponents\" xmlns:cc=\"urn:ebay:apis:CoreComponentTypes\" xmlns:ed=\"urn:ebay:apis:EnhancedDataTypes\"");
		// Call the authentication handler to tack authentication related info.
		$handler = new PPAuthenticationHandler();
		$handler->handle($httpConfig, $request, $options);
	}
}
