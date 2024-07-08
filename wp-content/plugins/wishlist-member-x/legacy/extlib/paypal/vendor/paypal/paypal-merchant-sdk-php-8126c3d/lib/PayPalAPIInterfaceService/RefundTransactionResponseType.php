<?php 
/**
 * Unique transaction ID of the refund. Character length and
 * limitations:17 single-byte characters 
 */
class RefundTransactionResponseType  extends AbstractResponseType  
  {

	/**
	 * Unique transaction ID of the refund. Character length and
	 * limitations:17 single-byte characters
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $RefundTransactionID;

	/**
	 * Amount subtracted from PayPal balance of original recipient
	 * of payment to make this refund 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $NetRefundAmount;

	/**
	 * Transaction fee refunded to original recipient of payment 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $FeeRefundAmount;

	/**
	 * Amount of money refunded to original payer 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $GrossRefundAmount;

	/**
	 * Total of all previous refunds
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $TotalRefundedAmount;

	/**
	 * Contains Refund Payment status information.
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var RefundInfoType 	 
	 */ 
	public $RefundInfo;

	/**
	 * Any general information like offer details that is
	 * reinstated or any other marketing data
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ReceiptData;

	/**
	 * Return msgsubid back to merchant
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;


}
