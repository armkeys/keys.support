<?php 
/**
 * 
 */
class CancelRecoupReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var CancelRecoupRequestType 	 
	 */ 
	public $CancelRecoupRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:CancelRecoupReq>';
			if(NULL != $this->CancelRecoupRequest)
			{
		   		$str .= '<ns:CancelRecoupRequest>';
				$str .= $this->CancelRecoupRequest->toXMLString();
				$str .= '</ns:CancelRecoupRequest>';
			}
			$str .= '</ns:CancelRecoupReq>';
			return $str;
	}
  
 
}
