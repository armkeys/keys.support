<?php 
/**
 * 
 */
class BMGetInventoryReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BMGetInventoryRequestType 	 
	 */ 
	public $BMGetInventoryRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BMGetInventoryReq>';
			if(NULL != $this->BMGetInventoryRequest)
			{
		   		$str .= '<ns:BMGetInventoryRequest>';
				$str .= $this->BMGetInventoryRequest->toXMLString();
				$str .= '</ns:BMGetInventoryRequest>';
			}
			$str .= '</ns:BMGetInventoryReq>';
			return $str;
	}
  
 
}
