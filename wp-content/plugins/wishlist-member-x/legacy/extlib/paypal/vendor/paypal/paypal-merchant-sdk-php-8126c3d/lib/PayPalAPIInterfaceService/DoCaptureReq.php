<?php 
/**
 * 
 */
class DoCaptureReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoCaptureRequestType 	 
	 */ 
	public $DoCaptureRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoCaptureReq>';
			if(NULL != $this->DoCaptureRequest)
			{
		   		$str .= '<ns:DoCaptureRequest>';
				$str .= $this->DoCaptureRequest->toXMLString();
				$str .= '</ns:DoCaptureRequest>';
			}
			$str .= '</ns:DoCaptureReq>';
			return $str;
	}
  
 
}
