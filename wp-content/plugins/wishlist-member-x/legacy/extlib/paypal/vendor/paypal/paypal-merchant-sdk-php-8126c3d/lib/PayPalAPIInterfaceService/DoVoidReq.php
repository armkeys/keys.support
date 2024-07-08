<?php 
/**
 * 
 */
class DoVoidReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoVoidRequestType 	 
	 */ 
	public $DoVoidRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoVoidReq>';
			if(NULL != $this->DoVoidRequest)
			{
		   		$str .= '<ns:DoVoidRequest>';
				$str .= $this->DoVoidRequest->toXMLString();
				$str .= '</ns:DoVoidRequest>';
			}
			$str .= '</ns:DoVoidReq>';
			return $str;
	}
  
 
}
