<?php 
/**
 * 
 */
class GetBoardingDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetBoardingDetailsRequestType 	 
	 */ 
	public $GetBoardingDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetBoardingDetailsReq>';
			if(NULL != $this->GetBoardingDetailsRequest)
			{
		   		$str .= '<ns:GetBoardingDetailsRequest>';
				$str .= $this->GetBoardingDetailsRequest->toXMLString();
				$str .= '</ns:GetBoardingDetailsRequest>';
			}
			$str .= '</ns:GetBoardingDetailsReq>';
			return $str;
	}
  
 
}
