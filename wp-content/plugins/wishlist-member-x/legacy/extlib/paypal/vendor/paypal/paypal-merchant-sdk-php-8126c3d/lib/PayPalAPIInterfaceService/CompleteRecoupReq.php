<?php 
/**
 * 
 */
class CompleteRecoupReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var CompleteRecoupRequestType 	 
	 */ 
	public $CompleteRecoupRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:CompleteRecoupReq>';
			if(NULL != $this->CompleteRecoupRequest)
			{
		   		$str .= '<ns:CompleteRecoupRequest>';
				$str .= $this->CompleteRecoupRequest->toXMLString();
				$str .= '</ns:CompleteRecoupRequest>';
			}
			$str .= '</ns:CompleteRecoupReq>';
			return $str;
	}
  
 
}
