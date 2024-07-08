<?php 
/**
 * 
 */
class GetAuthDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetAuthDetailsRequestType 	 
	 */ 
	public $GetAuthDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetAuthDetailsReq>';
			if(NULL != $this->GetAuthDetailsRequest)
			{
		   		$str .= '<ns:GetAuthDetailsRequest>';
				$str .= $this->GetAuthDetailsRequest->toXMLString();
				$str .= '</ns:GetAuthDetailsRequest>';
			}
			$str .= '</ns:GetAuthDetailsReq>';
			return $str;
	}
  
 
}
