<?php 
/**
 * 
 */
class DoDirectPaymentReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoDirectPaymentRequestType 	 
	 */ 
	public $DoDirectPaymentRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoDirectPaymentReq>';
			if(NULL != $this->DoDirectPaymentRequest)
			{
		   		$str .= '<ns:DoDirectPaymentRequest>';
				$str .= $this->DoDirectPaymentRequest->toXMLString();
				$str .= '</ns:DoDirectPaymentRequest>';
			}
			$str .= '</ns:DoDirectPaymentReq>';
			return $str;
	}
  
 
}
