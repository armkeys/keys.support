<?php 
/**
 * 
 */
class DoAuthorizationReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoAuthorizationRequestType 	 
	 */ 
	public $DoAuthorizationRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoAuthorizationReq>';
			if(NULL != $this->DoAuthorizationRequest)
			{
		   		$str .= '<ns:DoAuthorizationRequest>';
				$str .= $this->DoAuthorizationRequest->toXMLString();
				$str .= '</ns:DoAuthorizationRequest>';
			}
			$str .= '</ns:DoAuthorizationReq>';
			return $str;
	}
  
 
}
