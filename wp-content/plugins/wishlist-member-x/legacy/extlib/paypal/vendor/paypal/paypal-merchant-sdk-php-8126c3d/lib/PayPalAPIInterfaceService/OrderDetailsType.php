<?php 
/**
 * Description of the Order. 
 */
class OrderDetailsType  
   extends PPXmlMessage{

	/**
	 * Description of the Order.
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Description;

	/**
	 * Expected maximum amount that the merchant may pull using
	 * DoReferenceTransaction
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $MaxAmount;


  
 
}
