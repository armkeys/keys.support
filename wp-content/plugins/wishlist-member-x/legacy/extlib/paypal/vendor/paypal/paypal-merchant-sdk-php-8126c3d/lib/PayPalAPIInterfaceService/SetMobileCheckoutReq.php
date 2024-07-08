<?php 
/**
 * 
 */
class SetMobileCheckoutReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var SetMobileCheckoutRequestType 	 
	 */ 
	public $SetMobileCheckoutRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:SetMobileCheckoutReq>';
			if(NULL != $this->SetMobileCheckoutRequest)
			{
		   		$str .= '<ns:SetMobileCheckoutRequest>';
				$str .= $this->SetMobileCheckoutRequest->toXMLString();
				$str .= '</ns:SetMobileCheckoutRequest>';
			}
			$str .= '</ns:SetMobileCheckoutReq>';
			return $str;
	}
  
 
}
