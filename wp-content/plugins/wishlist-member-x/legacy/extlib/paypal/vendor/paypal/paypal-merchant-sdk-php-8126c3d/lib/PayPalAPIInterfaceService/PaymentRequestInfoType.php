<?php 
/**
 * Contains payment request information for each bucket in the
 * cart.  
 */
class PaymentRequestInfoType  
   extends PPXmlMessage{

	/**
	 * Contains the transaction id of the bucket.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionId;

	/**
	 * Contains the bucket id.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentRequestID;

	/**
	 * Contains the error details.  
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ErrorType 	 
	 */ 
	public $PaymentError;


}
