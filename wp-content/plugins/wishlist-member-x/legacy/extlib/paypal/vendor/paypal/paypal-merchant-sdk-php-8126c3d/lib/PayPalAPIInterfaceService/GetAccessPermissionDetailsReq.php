<?php 
/**
 * 
 */
class GetAccessPermissionDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetAccessPermissionDetailsRequestType 	 
	 */ 
	public $GetAccessPermissionDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetAccessPermissionDetailsReq>';
			if(NULL != $this->GetAccessPermissionDetailsRequest)
			{
		   		$str .= '<ns:GetAccessPermissionDetailsRequest>';
				$str .= $this->GetAccessPermissionDetailsRequest->toXMLString();
				$str .= '</ns:GetAccessPermissionDetailsRequest>';
			}
			$str .= '</ns:GetAccessPermissionDetailsReq>';
			return $str;
	}
  
 
}
