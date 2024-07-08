<?php 
/**
 * 
 */
class GetBillingAgreementCustomerDetailsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetBillingAgreementCustomerDetailsRequestType 	 
	 */ 
	public $GetBillingAgreementCustomerDetailsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetBillingAgreementCustomerDetailsReq>';
			if(NULL != $this->GetBillingAgreementCustomerDetailsRequest)
			{
		   		$str .= '<ns:GetBillingAgreementCustomerDetailsRequest>';
				$str .= $this->GetBillingAgreementCustomerDetailsRequest->toXMLString();
				$str .= '</ns:GetBillingAgreementCustomerDetailsRequest>';
			}
			$str .= '</ns:GetBillingAgreementCustomerDetailsReq>';
			return $str;
	}
  
 
}
