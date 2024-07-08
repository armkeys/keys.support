<?php 
/**
 * An authorization identification number. Character length and
 * limits: 19 single-byte characters 
 */
class DoAuthorizationResponseType  extends AbstractResponseType  
  {

	/**
	 * An authorization identification number. Character length and
	 * limits: 19 single-byte characters
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionID;

	/**
	 * The amount and currency you specified in the request. 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $Amount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AuthorizationInfoType 	 
	 */ 
	public $AuthorizationInfo;

	/**
	 * Return msgsubid back to merchant
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;


}
