<?php 
/**
 * 
 */
class GetPalDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetPalDetailsRequestType 	 
	 */ 
	public $GetPalDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetPalDetailsReq>';
			if(NULL != $this->GetPalDetailsRequest)
			{
		   		$str .= '<ns:GetPalDetailsRequest>';
				$str .= $this->GetPalDetailsRequest->toXMLString();
				$str .= '</ns:GetPalDetailsRequest>';
			}
			$str .= '</ns:GetPalDetailsReq>';
			return $str;
	}
  
 
}
