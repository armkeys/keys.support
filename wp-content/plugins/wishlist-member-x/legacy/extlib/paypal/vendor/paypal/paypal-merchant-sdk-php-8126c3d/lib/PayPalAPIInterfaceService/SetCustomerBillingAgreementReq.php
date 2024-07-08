<?php 
/**
 * 
 */
class SetCustomerBillingAgreementReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var SetCustomerBillingAgreementRequestType 	 
	 */ 
	public $SetCustomerBillingAgreementRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:SetCustomerBillingAgreementReq>';
			if(NULL != $this->SetCustomerBillingAgreementRequest)
			{
		   		$str .= '<ns:SetCustomerBillingAgreementRequest>';
				$str .= $this->SetCustomerBillingAgreementRequest->toXMLString();
				$str .= '</ns:SetCustomerBillingAgreementRequest>';
			}
			$str .= '</ns:SetCustomerBillingAgreementReq>';
			return $str;
	}
  
 
}
