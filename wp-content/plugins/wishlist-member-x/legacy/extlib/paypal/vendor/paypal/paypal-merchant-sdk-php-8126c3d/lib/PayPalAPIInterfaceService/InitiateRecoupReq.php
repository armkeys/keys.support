<?php 
/**
 * 
 */
class InitiateRecoupReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var InitiateRecoupRequestType 	 
	 */ 
	public $InitiateRecoupRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:InitiateRecoupReq>';
			if(NULL != $this->InitiateRecoupRequest)
			{
		   		$str .= '<ns:InitiateRecoupRequest>';
				$str .= $this->InitiateRecoupRequest->toXMLString();
				$str .= '</ns:InitiateRecoupRequest>';
			}
			$str .= '</ns:InitiateRecoupReq>';
			return $str;
	}
  
 
}
