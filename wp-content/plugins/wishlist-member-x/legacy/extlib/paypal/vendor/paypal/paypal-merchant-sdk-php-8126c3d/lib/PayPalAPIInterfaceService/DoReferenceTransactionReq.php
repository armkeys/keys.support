<?php 
/**
 * 
 */
class DoReferenceTransactionReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoReferenceTransactionRequestType 	 
	 */ 
	public $DoReferenceTransactionRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoReferenceTransactionReq>';
			if(NULL != $this->DoReferenceTransactionRequest)
			{
		   		$str .= '<ns:DoReferenceTransactionRequest>';
				$str .= $this->DoReferenceTransactionRequest->toXMLString();
				$str .= '</ns:DoReferenceTransactionRequest>';
			}
			$str .= '</ns:DoReferenceTransactionReq>';
			return $str;
	}
  
 
}
