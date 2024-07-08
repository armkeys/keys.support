<?php 
/**
 * Details of incentive application on individual bucket/item. 
 */
class IncentiveAppliedDetailsType  
   extends PPXmlMessage{

	/**
	 * PaymentRequestID uniquely identifies a bucket. It is the
	 * "bucket id" in the world of EC API. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentRequestID;

	/**
	 * The item id passed through by the merchant. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ItemId;

	/**
	 * The item transaction id passed through by the merchant. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ExternalTxnId;

	/**
	 * Discount offerred for this bucket or item. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $DiscountAmount;

	/**
	 * SubType for coupon. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SubType;


}
