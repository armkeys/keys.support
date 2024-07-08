<?php 
/**
 * 
 */
class BMGetButtonDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BMGetButtonDetailsRequestType 	 
	 */ 
	public $BMGetButtonDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BMGetButtonDetailsReq>';
			if(NULL != $this->BMGetButtonDetailsRequest)
			{
		   		$str .= '<ns:BMGetButtonDetailsRequest>';
				$str .= $this->BMGetButtonDetailsRequest->toXMLString();
				$str .= '</ns:BMGetButtonDetailsRequest>';
			}
			$str .= '</ns:BMGetButtonDetailsReq>';
			return $str;
	}
  
 
}
