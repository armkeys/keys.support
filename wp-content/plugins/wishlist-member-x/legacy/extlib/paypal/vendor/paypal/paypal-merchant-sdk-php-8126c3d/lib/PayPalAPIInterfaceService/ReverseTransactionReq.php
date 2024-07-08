<?php 
/**
 * 
 */
class ReverseTransactionReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var ReverseTransactionRequestType 	 
	 */ 
	public $ReverseTransactionRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:ReverseTransactionReq>';
			if(NULL != $this->ReverseTransactionRequest)
			{
		   		$str .= '<ns:ReverseTransactionRequest>';
				$str .= $this->ReverseTransactionRequest->toXMLString();
				$str .= '</ns:ReverseTransactionRequest>';
			}
			$str .= '</ns:ReverseTransactionReq>';
			return $str;
	}
  
 
}
