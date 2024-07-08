<?php 
/**
 * 
 */
class DoReferenceTransactionResponseDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingAgreementID;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentInfoType 	 
	 */ 
	public $PaymentInfo;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $Amount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AVSCode;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CVV2Code;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionID;

	/**
	 * Response code from the processor when a recurring
	 * transaction is declined 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentAdviceCode;

	/**
	 * Return msgsubid back to merchant 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;


}
