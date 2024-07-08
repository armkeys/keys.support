<?php 
/**
 * Information about Coupled Payment transactions. 
 */
class CoupledPaymentInfoType  
   extends PPXmlMessage{

	/**
	 * ID received in the Coupled Payment Request
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CoupledPaymentRequestID;

	/**
	 * ID that uniquely identifies this CoupledPayment. Generated
	 * by PP in Response
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CoupledPaymentID;


}
