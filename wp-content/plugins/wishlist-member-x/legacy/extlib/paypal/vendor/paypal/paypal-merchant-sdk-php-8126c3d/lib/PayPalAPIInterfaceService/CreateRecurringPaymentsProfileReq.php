<?php 
/**
 * 
 */
class CreateRecurringPaymentsProfileReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var CreateRecurringPaymentsProfileRequestType 	 
	 */ 
	public $CreateRecurringPaymentsProfileRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:CreateRecurringPaymentsProfileReq>';
			if(NULL != $this->CreateRecurringPaymentsProfileRequest)
			{
		   		$str .= '<ns:CreateRecurringPaymentsProfileRequest>';
				$str .= $this->CreateRecurringPaymentsProfileRequest->toXMLString();
				$str .= '</ns:CreateRecurringPaymentsProfileRequest>';
			}
			$str .= '</ns:CreateRecurringPaymentsProfileReq>';
			return $str;
	}
  
 
}
