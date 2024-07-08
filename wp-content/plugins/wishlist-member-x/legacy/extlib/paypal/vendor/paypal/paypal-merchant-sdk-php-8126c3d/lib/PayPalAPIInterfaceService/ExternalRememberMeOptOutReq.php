<?php 
/**
 * 
 */
class ExternalRememberMeOptOutReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var ExternalRememberMeOptOutRequestType 	 
	 */ 
	public $ExternalRememberMeOptOutRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:ExternalRememberMeOptOutReq>';
			if(NULL != $this->ExternalRememberMeOptOutRequest)
			{
		   		$str .= '<ns:ExternalRememberMeOptOutRequest>';
				$str .= $this->ExternalRememberMeOptOutRequest->toXMLString();
				$str .= '</ns:ExternalRememberMeOptOutRequest>';
			}
			$str .= '</ns:ExternalRememberMeOptOutReq>';
			return $str;
	}
  
 
}
