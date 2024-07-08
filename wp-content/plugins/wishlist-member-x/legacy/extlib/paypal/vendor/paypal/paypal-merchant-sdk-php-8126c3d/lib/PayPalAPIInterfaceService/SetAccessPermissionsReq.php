<?php 
/**
 * 
 */
class SetAccessPermissionsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var SetAccessPermissionsRequestType 	 
	 */ 
	public $SetAccessPermissionsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:SetAccessPermissionsReq>';
			if(NULL != $this->SetAccessPermissionsRequest)
			{
		   		$str .= '<ns:SetAccessPermissionsRequest>';
				$str .= $this->SetAccessPermissionsRequest->toXMLString();
				$str .= '</ns:SetAccessPermissionsRequest>';
			}
			$str .= '</ns:SetAccessPermissionsReq>';
			return $str;
	}
  
 
}
