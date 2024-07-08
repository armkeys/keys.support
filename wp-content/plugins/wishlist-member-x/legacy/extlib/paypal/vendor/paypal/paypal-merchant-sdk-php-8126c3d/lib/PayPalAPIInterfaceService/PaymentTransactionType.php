<?php 
/**
 * PaymentTransactionType Information about a PayPal payment
 * from the seller side 
 */
class PaymentTransactionType  
   extends PPXmlMessage{

	/**
	 * Information about the recipient of the payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ReceiverInfoType 	 
	 */ 
	public $ReceiverInfo;

	/**
	 * Information about the payer 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PayerInfoType 	 
	 */ 
	public $PayerInfo;

	/**
	 * This field is for holding ReferenceId for shippment sent
	 * from Merchant to the 3rd Party  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TPLReferenceID;

	/**
	 * Information about the transaction 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentInfoType 	 
	 */ 
	public $PaymentInfo;

	/**
	 * Information about an individual item in the transaction
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentItemInfoType 	 
	 */ 
	public $PaymentItemInfo;

	/**
	 * Information about an individual Offer and Coupon information
	 * in the transaction
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var OfferCouponInfoType 	 
	 */ 
	public $OfferCouponInfo;

	/**
	 * Information about Secondary Address
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AddressType 	 
	 */ 
	public $SecondaryAddress;

	/**
	 * Information about the user selected options.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var UserSelectedOptionType 	 
	 */ 
	public $UserSelectedOptions;

	/**
	 * Information about the Gift message.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $GiftMessage;

	/**
	 * Information about the Gift receipt.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $GiftReceipt;

	/**
	 * Information about the Gift Wrap name.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $GiftWrapName;

	/**
	 * Information about the Gift Wrap amount.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $GiftWrapAmount;

	/**
	 * Information about the Buyer email.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BuyerEmailOptIn;

	/**
	 * Information about the survey question.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SurveyQuestion;

	/**
	 * Information about the survey choice selected by the user.  
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SurveyChoiceSelected;


}
