<?php 
/**
 * 
 */
class BillUserReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BillUserRequestType 	 
	 */ 
	public $BillUserRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BillUserReq>';
			if(NULL != $this->BillUserRequest)
			{
		   		$str .= '<ns:BillUserRequest>';
				$str .= $this->BillUserRequest->toXMLString();
				$str .= '</ns:BillUserRequest>';
			}
			$str .= '</ns:BillUserReq>';
			return $str;
	}
  
 
}
