<?php 
/**
 * Defines relationship between buckets 
 */
class CoupledBucketsType  
   extends PPXmlMessage{

	/**
	 * Relationship Type - LifeTime (default) 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CoupleType;

	/**
	 * Identifier for this relation 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CoupledPaymentRequestID;

	/**
	 * 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentRequestID;

	/**
	 * Constructor with arguments
	 */
	public function __construct($PaymentRequestID = NULL) {
		$this->PaymentRequestID = $PaymentRequestID;
	}


  
 
}
