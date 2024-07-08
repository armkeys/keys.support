<?php 
/**
 * 
 */
class RefundTransactionReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var RefundTransactionRequestType 	 
	 */ 
	public $RefundTransactionRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:RefundTransactionReq>';
			if(NULL != $this->RefundTransactionRequest)
			{
		   		$str .= '<ns:RefundTransactionRequest>';
				$str .= $this->RefundTransactionRequest->toXMLString();
				$str .= '</ns:RefundTransactionRequest>';
			}
			$str .= '</ns:RefundTransactionReq>';
			return $str;
	}
  
 
}
