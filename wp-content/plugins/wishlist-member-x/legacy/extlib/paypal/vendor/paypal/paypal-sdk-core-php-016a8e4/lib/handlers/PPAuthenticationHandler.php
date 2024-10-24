<?php

/**
 *
 * Determines which authentication handler to run based
 * on credential passed in.
 *
 * Also handles PayPal third party authentication (Permissions API).
 *
 */
class PPAuthenticationHandler implements IPPHandler {

	public function handle($httpConfig, $request, $options) {
		$credential = $request->getCredential();
		if(isset($credential)) {
			$thirdPartyAuth = $credential->getThirdPartyAuthorization();
			if($thirdPartyAuth && $thirdPartyAuth instanceof PPTokenAuthorization) {
				$authSignature = AuthSignature::generateFullAuthString($credential->getUsername(), $credential->getPassword(), $thirdPartyAuth->getAccessToken(), $thirdPartyAuth->getTokenSecret(), $httpConfig->getMethod(), $httpConfig->getUrl());
				if(isset($options['port']) &&
						('PayPalAPI' === $options['port'] || 'PayPalAPIAA' === $options['port'])) {
					$httpConfig->addHeader('X-PP-AUTHORIZATION', $authSignature);
				}
				else {
					$httpConfig->addHeader('X-PAYPAL-AUTHORIZATION', $authSignature);
				}
			}
			if($credential instanceof PPSignatureCredential) {
				$handler = new PPSignatureAuthHandler($credential);
			} else if($credential instanceof PPCertificateCredential) {
				$handler = new PPCertificateAuthHandler($credential);
			} else {
				throw new PPInvalidCredentialException();
			}
			$handler->handle($httpConfig, $request, $options);
		}
	}
}
