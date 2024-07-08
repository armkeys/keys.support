<?php 
/**
 * MerchantPullPaymentResponseType Response data from the
 * merchant pull. 
 */
class MerchantPullPaymentResponseType  
   extends PPXmlMessage{

	/**
	 * information about the customer
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PayerInfoType 	 
	 */ 
	public $PayerInfo;

	/**
	 * Information about the transaction 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentInfoType 	 
	 */ 
	public $PaymentInfo;

	/**
	 * Specific information about the preapproved payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var MerchantPullInfoType 	 
	 */ 
	public $MerchantPullInfo;


}
