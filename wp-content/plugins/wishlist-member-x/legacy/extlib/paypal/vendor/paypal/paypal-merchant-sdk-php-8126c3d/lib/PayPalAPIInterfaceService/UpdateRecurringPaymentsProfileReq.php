<?php 
/**
 * 
 */
class UpdateRecurringPaymentsProfileReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var UpdateRecurringPaymentsProfileRequestType 	 
	 */ 
	public $UpdateRecurringPaymentsProfileRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:UpdateRecurringPaymentsProfileReq>';
			if(NULL != $this->UpdateRecurringPaymentsProfileRequest)
			{
		   		$str .= '<ns:UpdateRecurringPaymentsProfileRequest>';
				$str .= $this->UpdateRecurringPaymentsProfileRequest->toXMLString();
				$str .= '</ns:UpdateRecurringPaymentsProfileRequest>';
			}
			$str .= '</ns:UpdateRecurringPaymentsProfileReq>';
			return $str;
	}
  
 
}
