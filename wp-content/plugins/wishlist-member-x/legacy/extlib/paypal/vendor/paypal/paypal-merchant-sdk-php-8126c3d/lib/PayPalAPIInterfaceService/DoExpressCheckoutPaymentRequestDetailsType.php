<?php 
/**
 * How you want to obtain payment. Required Authorization
 * indicates that this payment is a basic authorization subject
 * to settlement with PayPal Authorization and Capture. Order
 * indicates that this payment is is an order authorization
 * subject to settlement with PayPal Authorization and Capture.
 * Sale indicates that this is a final sale for which you are
 * requesting payment. IMPORTANT: You cannot set PaymentAction
 * to Sale on SetExpressCheckoutRequest and then change
 * PaymentAction to Authorization on the final Express Checkout
 * API, DoExpressCheckoutPaymentRequest. Character length and
 * limit: Up to 13 single-byte alphabetic characters 
 */
class DoExpressCheckoutPaymentRequestDetailsType  
   extends PPXmlMessage{

	/**
	 * How you want to obtain payment. Required Authorization
	 * indicates that this payment is a basic authorization subject
	 * to settlement with PayPal Authorization and Capture. Order
	 * indicates that this payment is is an order authorization
	 * subject to settlement with PayPal Authorization and Capture.
	 * Sale indicates that this is a final sale for which you are
	 * requesting payment. IMPORTANT: You cannot set PaymentAction
	 * to Sale on SetExpressCheckoutRequest and then change
	 * PaymentAction to Authorization on the final Express Checkout
	 * API, DoExpressCheckoutPaymentRequest. Character length and
	 * limit: Up to 13 single-byte alphabetic characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentAction;

	/**
	 * The timestamped token value that was returned by
	 * SetExpressCheckoutResponse and passed on
	 * GetExpressCheckoutDetailsRequest. Required Character length
	 * and limitations: 20 single-byte characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Token;

	/**
	 * Encrypted PayPal customer account identification number as
	 * returned by GetExpressCheckoutDetailsResponse. Required
	 * Character length and limitations: 127 single-byte
	 * characters.
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PayerID;

	/**
	 * URL on Merchant site pertaining to this invoice. Optional 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OrderURL;

	/**
	 * Information about the payment Required 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentDetailsType 	 
	 */ 
	public $PaymentDetails;

	/**
	 * Flag to indicate if previously set promoCode shall be
	 * overriden. Value 1 indicates overriding.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PromoOverrideFlag;

	/**
	 * Promotional financing code for item. Overrides any previous
	 * PromoCode setting. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PromoCode;

	/**
	 * Contains data for enhanced data like Airline Itinerary Data.
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var EnhancedDataType 	 
	 */ 
	public $EnhancedData;

	/**
	 * Soft Descriptor supported for Sale and Auth in DEC only. For
	 * Order this will be ignored. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SoftDescriptor;

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
	 * Information about the Gift receipt enable. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $GiftReceiptEnable;

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
	 * Information about the Buyer marketing email. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BuyerMarketingEmail;

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

	/**
	 * An identification code for use by third-party applications
	 * to identify transactions. Optional Character length and
	 * limitations: 32 single-byte alphanumeric characters 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonSource = "WishListProducts_SP";

	/**
	 * Merchant specified flag which indicates whether to create
	 * billing agreement as part of DoEC or not. Optional 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var boolean 	 
	 */ 
	public $SkipBACreation;

	/**
	 * Merchant specified flag which indicates to use payment
	 * details from session if available. Optional 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $UseSessionPaymentDetails;

	/**
	 * Optional element that defines relationship between buckets 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var CoupledBucketsType 	 
	 */ 
	public $CoupledBuckets;


  
 
}
