<?php 
/**
 * 
 */
class GetBillingAgreementCustomerDetailsResponseDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PayerInfoType 	 
	 */ 
	public $PayerInfo;

	/**
	 * Customer's billing address. Optional If you have a credit
	 * card mapped in your PayPal account, PayPal returns the
	 * billing address of the credit billing address otherwise your
	 * primary address as billing address in
	 * GetBillingAgreementCustomerDetails.
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AddressType 	 
	 */ 
	public $BillingAddress;


}
