<?php 
/**
 * @hasAttribute
 * SubscriptionTermsType Terms of a PayPal subscription. 
 */
class SubscriptionTermsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $Amount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 
	 * @attribute 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $period;


}
