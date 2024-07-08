<?php 
/**
 * PaymentTransactionSearchResultType Results from a
 * PaymentTransaction search 
 */
class PaymentTransactionSearchResultType  
   extends PPXmlMessage{

	/**
	 * The date and time (in UTC/GMT format) the transaction
	 * occurred
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $Timestamp;

	/**
	 * The time zone of the transaction 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Timezone;

	/**
	 * The type of the transaction
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Type;

	/**
	 * The email address of the payer
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Payer;

	/**
	 * Display name of the payer
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PayerDisplayName;

	/**
	 * The transaction ID of the seller
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionID;

	/**
	 * The status of the transaction
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Status;

	/**
	 * The total gross amount charged, including any profile
	 * shipping cost and taxes
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $GrossAmount;

	/**
	 * The fee that PayPal charged for the transaction 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $FeeAmount;

	/**
	 * The net amount of the transaction 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $NetAmount;


}
