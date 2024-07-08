<?php 
/**
 * 
 */
class ManagePendingTransactionStatusReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var ManagePendingTransactionStatusRequestType 	 
	 */ 
	public $ManagePendingTransactionStatusRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:ManagePendingTransactionStatusReq>';
			if(NULL != $this->ManagePendingTransactionStatusRequest)
			{
		   		$str .= '<ns:ManagePendingTransactionStatusRequest>';
				$str .= $this->ManagePendingTransactionStatusRequest->toXMLString();
				$str .= '</ns:ManagePendingTransactionStatusRequest>';
			}
			$str .= '</ns:ManagePendingTransactionStatusReq>';
			return $str;
	}
  
 
}
