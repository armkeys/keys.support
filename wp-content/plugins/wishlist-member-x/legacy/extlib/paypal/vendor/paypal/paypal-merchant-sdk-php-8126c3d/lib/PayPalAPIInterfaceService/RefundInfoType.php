<?php 
/**
 * Holds refunds payment status information 
 */
class RefundInfoType  
   extends PPXmlMessage{

	/**
	 * Refund status whether it is Instant or Delayed. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $RefundStatus;

	/**
	 * Tells us the reason when refund payment status is Delayed. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PendingReason;


}
