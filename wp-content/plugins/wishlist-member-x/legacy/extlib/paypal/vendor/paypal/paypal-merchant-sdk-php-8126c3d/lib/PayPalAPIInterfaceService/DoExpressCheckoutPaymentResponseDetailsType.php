<?php 
/**
 * The timestamped token value that was returned by
 * SetExpressCheckoutResponse and passed on
 * GetExpressCheckoutDetailsRequest. Character length and
 * limitations:20 single-byte characters 
 */
class DoExpressCheckoutPaymentResponseDetailsType  
   extends PPXmlMessage{

	/**
	 * The timestamped token value that was returned by
	 * SetExpressCheckoutResponse and passed on
	 * GetExpressCheckoutDetailsRequest. Character length and
	 * limitations:20 single-byte characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Token;

	/**
	 * Information about the transaction 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentInfoType 	 
	 */ 
	public $PaymentInfo;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingAgreementID;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $RedirectRequired;

	/**
	 * Memo entered by sender in PayPal Review Page note field.
	 * Optional Character length and limitations: 255 single-byte
	 * alphanumeric characters 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Note;

	/**
	 * Redirect back to PayPal, PayPal can host the success page. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SuccessPageRedirectRequested;

	/**
	 * Information about the user selected options. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var UserSelectedOptionType 	 
	 */ 
	public $UserSelectedOptions;

	/**
	 * Information about Coupled Payment transactions. 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var CoupledPaymentInfoType 	 
	 */ 
	public $CoupledPaymentInfo;


}
