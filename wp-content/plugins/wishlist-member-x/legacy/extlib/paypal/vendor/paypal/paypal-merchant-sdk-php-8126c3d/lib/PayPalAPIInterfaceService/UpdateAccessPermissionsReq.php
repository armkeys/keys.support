<?php 
/**
 * 
 */
class UpdateAccessPermissionsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var UpdateAccessPermissionsRequestType 	 
	 */ 
	public $UpdateAccessPermissionsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:UpdateAccessPermissionsReq>';
			if(NULL != $this->UpdateAccessPermissionsRequest)
			{
		   		$str .= '<ns:UpdateAccessPermissionsRequest>';
				$str .= $this->UpdateAccessPermissionsRequest->toXMLString();
				$str .= '</ns:UpdateAccessPermissionsRequest>';
			}
			$str .= '</ns:UpdateAccessPermissionsReq>';
			return $str;
	}
  
 
}
