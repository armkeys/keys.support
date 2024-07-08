<?php 
/**
 * 
 */
class GetRecurringPaymentsProfileDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetRecurringPaymentsProfileDetailsRequestType 	 
	 */ 
	public $GetRecurringPaymentsProfileDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetRecurringPaymentsProfileDetailsReq>';
			if(NULL != $this->GetRecurringPaymentsProfileDetailsRequest)
			{
		   		$str .= '<ns:GetRecurringPaymentsProfileDetailsRequest>';
				$str .= $this->GetRecurringPaymentsProfileDetailsRequest->toXMLString();
				$str .= '</ns:GetRecurringPaymentsProfileDetailsRequest>';
			}
			$str .= '</ns:GetRecurringPaymentsProfileDetailsReq>';
			return $str;
	}
  
 
}
