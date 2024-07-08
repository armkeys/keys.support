<?php 
/**
 * MerchantPullInfoType Information about the merchant pull. 
 */
class MerchantPullInfoType  
   extends PPXmlMessage{

	/**
	 * Current status of billing agreement 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MpStatus;

	/**
	 * Monthly maximum payment amount
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $MpMax;

	/**
	 * The value of the mp_custom variable that you specified in a
	 * FORM submission to PayPal during the creation or updating of
	 * a customer billing agreement 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MpCustom;

	/**
	 * The value of the mp_desc variable (description of goods or
	 * services) associated with the billing agreement 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Desc;

	/**
	 * Invoice value as set by BillUserRequest API call 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Invoice;

	/**
	 * Custom field as set by BillUserRequest API call 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Custom;

	/**
	 * Note: This field is no longer used and is always empty.
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentSourceID;


}
