<?php 
/**
 * 
 */
class DoMobileCheckoutPaymentReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoMobileCheckoutPaymentRequestType 	 
	 */ 
	public $DoMobileCheckoutPaymentRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoMobileCheckoutPaymentReq>';
			if(NULL != $this->DoMobileCheckoutPaymentRequest)
			{
		   		$str .= '<ns:DoMobileCheckoutPaymentRequest>';
				$str .= $this->DoMobileCheckoutPaymentRequest->toXMLString();
				$str .= '</ns:DoMobileCheckoutPaymentRequest>';
			}
			$str .= '</ns:DoMobileCheckoutPaymentReq>';
			return $str;
	}
  
 
}
