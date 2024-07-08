<?php 
/**
 * 
 */
class DoUATPExpressCheckoutPaymentReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoUATPExpressCheckoutPaymentRequestType 	 
	 */ 
	public $DoUATPExpressCheckoutPaymentRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoUATPExpressCheckoutPaymentReq>';
			if(NULL != $this->DoUATPExpressCheckoutPaymentRequest)
			{
		   		$str .= '<ns:DoUATPExpressCheckoutPaymentRequest>';
				$str .= $this->DoUATPExpressCheckoutPaymentRequest->toXMLString();
				$str .= '</ns:DoUATPExpressCheckoutPaymentRequest>';
			}
			$str .= '</ns:DoUATPExpressCheckoutPaymentReq>';
			return $str;
	}
  
 
}
