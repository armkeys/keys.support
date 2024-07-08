<?php 
/**
 * 
 */
class BMSetInventoryReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BMSetInventoryRequestType 	 
	 */ 
	public $BMSetInventoryRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BMSetInventoryReq>';
			if(NULL != $this->BMSetInventoryRequest)
			{
		   		$str .= '<ns:BMSetInventoryRequest>';
				$str .= $this->BMSetInventoryRequest->toXMLString();
				$str .= '</ns:BMSetInventoryRequest>';
			}
			$str .= '</ns:BMSetInventoryReq>';
			return $str;
	}
  
 
}
