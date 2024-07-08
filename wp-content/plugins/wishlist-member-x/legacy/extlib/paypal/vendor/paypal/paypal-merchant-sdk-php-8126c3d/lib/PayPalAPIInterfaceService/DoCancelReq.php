<?php 
/**
 * 
 */
class DoCancelReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoCancelRequestType 	 
	 */ 
	public $DoCancelRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoCancelReq>';
			if(NULL != $this->DoCancelRequest)
			{
		   		$str .= '<ns:DoCancelRequest>';
				$str .= $this->DoCancelRequest->toXMLString();
				$str .= '</ns:DoCancelRequest>';
			}
			$str .= '</ns:DoCancelReq>';
			return $str;
	}
  
 
}
