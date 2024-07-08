<?php 
/**
 * 
 */
class BMCreateButtonReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BMCreateButtonRequestType 	 
	 */ 
	public $BMCreateButtonRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BMCreateButtonReq>';
			if(NULL != $this->BMCreateButtonRequest)
			{
		   		$str .= '<ns:BMCreateButtonRequest>';
				$str .= $this->BMCreateButtonRequest->toXMLString();
				$str .= '</ns:BMCreateButtonRequest>';
			}
			$str .= '</ns:BMCreateButtonReq>';
			return $str;
	}
  
 
}
