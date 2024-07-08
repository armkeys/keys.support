<?php 
/**
 * Defines which bucket or item that the incentive should be
 * applied to. 
 */
class IncentiveApplyIndicationType  
   extends PPXmlMessage{

	/**
	 * The Bucket ID that the incentive is applied to. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentRequestID;

	/**
	 * The item that the incentive is applied to. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ItemId;


  
 
}
