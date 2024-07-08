<?php 
/**
 * CreditCardDetailsType for DCC Reference Transaction
 * Information about a Credit Card. 
 */
class ReferenceCreditCardDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var CreditCardNumberTypeType 	 
	 */ 
	public $CreditCardNumberType;

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
	 
	 	 	 	 
	 * @var PersonNameType 	 
	 */ 
	public $CardOwnerName;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AddressType 	 
	 */ 
	public $BillingAddress;

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


  
 
}
