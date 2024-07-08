<?php

class PPSOAPFormatter implements IPPFormatter {
	
	private static $SOAP_NAMESPACE = 'xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"';
	
	public function toString($request, $options=array()) {
				
		$customNamespace = ( null != $request->getBindingInfo('namespace') ) ?  $request->getBindingInfo('namespace') : "";
		$soapEnvelope = '<soapenv:Envelope ' . self::$SOAP_NAMESPACE . " $customNamespace >";

		$soapHeader = '<soapenv:Header>';
		if(null != $request->getBindingInfo('securityHeader')) {
			$soapHeader .= $request->getBindingInfo('securityHeader');
		}		
		$soapHeader .= '</soapenv:Header>';
				
		$soapBody = '<soapenv:Body>';
		$soapBody .= $request->getRequestObject()->toXMLString();
		$soapBody .= '</soapenv:Body>';
		
		return $soapEnvelope . $soapHeader . $soapBody . '</soapenv:Envelope>';
	}
	
	public function toObject($string, $options=array()) {
		throw new BadMethodCallException("Unimplemented");
	}	
}
