<?php 
/**
 * 
 */
class CreateMobilePaymentReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var CreateMobilePaymentRequestType 	 
	 */ 
	public $CreateMobilePaymentRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:CreateMobilePaymentReq>';
			if(NULL != $this->CreateMobilePaymentRequest)
			{
		   		$str .= '<ns:CreateMobilePaymentRequest>';
				$str .= $this->CreateMobilePaymentRequest->toXMLString();
				$str .= '</ns:CreateMobilePaymentRequest>';
			}
			$str .= '</ns:CreateMobilePaymentReq>';
			return $str;
	}
  
 
}
