<?php 
/**
 * 
 */
class BMUpdateButtonReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BMUpdateButtonRequestType 	 
	 */ 
	public $BMUpdateButtonRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BMUpdateButtonReq>';
			if(NULL != $this->BMUpdateButtonRequest)
			{
		   		$str .= '<ns:BMUpdateButtonRequest>';
				$str .= $this->BMUpdateButtonRequest->toXMLString();
				$str .= '</ns:BMUpdateButtonRequest>';
			}
			$str .= '</ns:BMUpdateButtonReq>';
			return $str;
	}
  
 
}
