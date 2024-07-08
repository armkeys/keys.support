<?php 
/**
 * 
 */
class GetMobileStatusReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetMobileStatusRequestType 	 
	 */ 
	public $GetMobileStatusRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetMobileStatusReq>';
			if(NULL != $this->GetMobileStatusRequest)
			{
		   		$str .= '<ns:GetMobileStatusRequest>';
				$str .= $this->GetMobileStatusRequest->toXMLString();
				$str .= '</ns:GetMobileStatusRequest>';
			}
			$str .= '</ns:GetMobileStatusReq>';
			return $str;
	}
  
 
}
