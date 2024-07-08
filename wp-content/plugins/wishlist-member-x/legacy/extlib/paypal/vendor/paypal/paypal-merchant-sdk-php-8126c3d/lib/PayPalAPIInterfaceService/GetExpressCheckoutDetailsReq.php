<?php 
/**
 * 
 */
class GetExpressCheckoutDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetExpressCheckoutDetailsRequestType 	 
	 */ 
	public $GetExpressCheckoutDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetExpressCheckoutDetailsReq>';
			if(NULL != $this->GetExpressCheckoutDetailsRequest)
			{
		   		$str .= '<ns:GetExpressCheckoutDetailsRequest>';
				$str .= $this->GetExpressCheckoutDetailsRequest->toXMLString();
				$str .= '</ns:GetExpressCheckoutDetailsRequest>';
			}
			$str .= '</ns:GetExpressCheckoutDetailsReq>';
			return $str;
	}
  
 
}
