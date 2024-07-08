<?php 
/**
 * 
 */
class BMButtonSearchReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BMButtonSearchRequestType 	 
	 */ 
	public $BMButtonSearchRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BMButtonSearchReq>';
			if(NULL != $this->BMButtonSearchRequest)
			{
		   		$str .= '<ns:BMButtonSearchRequest>';
				$str .= $this->BMButtonSearchRequest->toXMLString();
				$str .= '</ns:BMButtonSearchRequest>';
			}
			$str .= '</ns:BMButtonSearchReq>';
			return $str;
	}
  
 
}
