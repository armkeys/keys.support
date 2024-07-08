<?php 
/**
 * 
 */
class MassPayReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var MassPayRequestType 	 
	 */ 
	public $MassPayRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:MassPayReq>';
			if(NULL != $this->MassPayRequest)
			{
		   		$str .= '<ns:MassPayRequest>';
				$str .= $this->MassPayRequest->toXMLString();
				$str .= '</ns:MassPayRequest>';
			}
			$str .= '</ns:MassPayReq>';
			return $str;
	}
  
 
}
