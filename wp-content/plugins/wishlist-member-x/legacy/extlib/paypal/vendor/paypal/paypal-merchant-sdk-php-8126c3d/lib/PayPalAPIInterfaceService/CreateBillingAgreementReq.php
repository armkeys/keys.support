<?php 
/**
 * 
 */
class CreateBillingAgreementReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var CreateBillingAgreementRequestType 	 
	 */ 
	public $CreateBillingAgreementRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:CreateBillingAgreementReq>';
			if(NULL != $this->CreateBillingAgreementRequest)
			{
		   		$str .= '<ns:CreateBillingAgreementRequest>';
				$str .= $this->CreateBillingAgreementRequest->toXMLString();
				$str .= '</ns:CreateBillingAgreementRequest>';
			}
			$str .= '</ns:CreateBillingAgreementReq>';
			return $str;
	}
  
 
}
