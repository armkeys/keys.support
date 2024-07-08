<?php 
/**
 * Indicates whether the phone is activated for mobile payments
 * 
 */
class GetMobileStatusResponseType  extends AbstractResponseType  
  {

	/**
	 * Indicates whether the phone is activated for mobile payments
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $IsActivated;

	/**
	 * Indicates whether the password is enabled for particular
	 * account 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $IsPasswordSet;

	/**
	 * Indicates whether there is a payment pending from the phone 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $PaymentPending;


}
