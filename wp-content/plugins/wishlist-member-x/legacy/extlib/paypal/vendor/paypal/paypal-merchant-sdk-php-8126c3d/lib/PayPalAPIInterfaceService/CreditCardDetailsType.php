<?php 
/**
 * CreditCardDetailsType Information about a Credit Card. 
 */
class CreditCardDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CreditCardType;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CreditCardNumber;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $ExpMonth;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $ExpYear;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PayerInfoType 	 
	 */ 
	public $CardOwner;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CVV2;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $StartMonth;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $StartYear;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $IssueNumber;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ThreeDSecureRequestType 	 
	 */ 
	public $ThreeDSecureRequest;


  
 
}
