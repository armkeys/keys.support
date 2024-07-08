<?php 
/**
 * 
 */
class TransactionSearchReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var TransactionSearchRequestType 	 
	 */ 
	public $TransactionSearchRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:TransactionSearchReq>';
			if(NULL != $this->TransactionSearchRequest)
			{
		   		$str .= '<ns:TransactionSearchRequest>';
				$str .= $this->TransactionSearchRequest->toXMLString();
				$str .= '</ns:TransactionSearchRequest>';
			}
			$str .= '</ns:TransactionSearchReq>';
			return $str;
	}
  
 
}
