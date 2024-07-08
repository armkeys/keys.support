<?php 
/**
 * 
 */
class ManageRecurringPaymentsProfileStatusReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var ManageRecurringPaymentsProfileStatusRequestType 	 
	 */ 
	public $ManageRecurringPaymentsProfileStatusRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:ManageRecurringPaymentsProfileStatusReq>';
			if(NULL != $this->ManageRecurringPaymentsProfileStatusRequest)
			{
		   		$str .= '<ns:ManageRecurringPaymentsProfileStatusRequest>';
				$str .= $this->ManageRecurringPaymentsProfileStatusRequest->toXMLString();
				$str .= '</ns:ManageRecurringPaymentsProfileStatusRequest>';
			}
			$str .= '</ns:ManageRecurringPaymentsProfileStatusReq>';
			return $str;
	}
  
 
}
