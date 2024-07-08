<?php 
/**
 * 
 */
class SetExpressCheckoutReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var SetExpressCheckoutRequestType 	 
	 */ 
	public $SetExpressCheckoutRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:SetExpressCheckoutReq>';
			if(NULL != $this->SetExpressCheckoutRequest)
			{
		   		$str .= '<ns:SetExpressCheckoutRequest>';
				$str .= $this->SetExpressCheckoutRequest->toXMLString();
				$str .= '</ns:SetExpressCheckoutRequest>';
			}
			$str .= '</ns:SetExpressCheckoutReq>';
			return $str;
	}
  
 
}
