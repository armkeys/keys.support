<?php 
/**
 * 
 */
class DoUATPAuthorizationReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoUATPAuthorizationRequestType 	 
	 */ 
	public $DoUATPAuthorizationRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoUATPAuthorizationReq>';
			if(NULL != $this->DoUATPAuthorizationRequest)
			{
		   		$str .= '<ns:DoUATPAuthorizationRequest>';
				$str .= $this->DoUATPAuthorizationRequest->toXMLString();
				$str .= '</ns:DoUATPAuthorizationRequest>';
			}
			$str .= '</ns:DoUATPAuthorizationReq>';
			return $str;
	}
  
 
}
