<?php 
/**
 * 
 */
class BMManageButtonStatusReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BMManageButtonStatusRequestType 	 
	 */ 
	public $BMManageButtonStatusRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BMManageButtonStatusReq>';
			if(NULL != $this->BMManageButtonStatusRequest)
			{
		   		$str .= '<ns:BMManageButtonStatusRequest>';
				$str .= $this->BMManageButtonStatusRequest->toXMLString();
				$str .= '</ns:BMManageButtonStatusRequest>';
			}
			$str .= '</ns:BMManageButtonStatusReq>';
			return $str;
	}
  
 
}
