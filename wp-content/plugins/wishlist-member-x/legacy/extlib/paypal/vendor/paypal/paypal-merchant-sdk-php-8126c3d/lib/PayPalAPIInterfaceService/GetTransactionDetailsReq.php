<?php 
/**
 * 
 */
class GetTransactionDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetTransactionDetailsRequestType 	 
	 */ 
	public $GetTransactionDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetTransactionDetailsReq>';
			if(NULL != $this->GetTransactionDetailsRequest)
			{
		   		$str .= '<ns:GetTransactionDetailsRequest>';
				$str .= $this->GetTransactionDetailsRequest->toXMLString();
				$str .= '</ns:GetTransactionDetailsRequest>';
			}
			$str .= '</ns:GetTransactionDetailsReq>';
			return $str;
	}
  
 
}
