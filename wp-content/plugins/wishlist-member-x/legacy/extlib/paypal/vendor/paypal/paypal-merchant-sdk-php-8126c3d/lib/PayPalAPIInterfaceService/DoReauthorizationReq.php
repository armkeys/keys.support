<?php 
/**
 * 
 */
class DoReauthorizationReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoReauthorizationRequestType 	 
	 */ 
	public $DoReauthorizationRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoReauthorizationReq>';
			if(NULL != $this->DoReauthorizationRequest)
			{
		   		$str .= '<ns:DoReauthorizationRequest>';
				$str .= $this->DoReauthorizationRequest->toXMLString();
				$str .= '</ns:DoReauthorizationRequest>';
			}
			$str .= '</ns:DoReauthorizationReq>';
			return $str;
	}
  
 
}
