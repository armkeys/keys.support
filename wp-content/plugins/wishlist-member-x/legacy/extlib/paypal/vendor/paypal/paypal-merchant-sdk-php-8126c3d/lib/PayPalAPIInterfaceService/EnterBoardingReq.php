<?php 
/**
 * 
 */
class EnterBoardingReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var EnterBoardingRequestType 	 
	 */ 
	public $EnterBoardingRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:EnterBoardingReq>';
			if(NULL != $this->EnterBoardingRequest)
			{
		   		$str .= '<ns:EnterBoardingRequest>';
				$str .= $this->EnterBoardingRequest->toXMLString();
				$str .= '</ns:EnterBoardingRequest>';
			}
			$str .= '</ns:EnterBoardingReq>';
			return $str;
	}
  
 
}
