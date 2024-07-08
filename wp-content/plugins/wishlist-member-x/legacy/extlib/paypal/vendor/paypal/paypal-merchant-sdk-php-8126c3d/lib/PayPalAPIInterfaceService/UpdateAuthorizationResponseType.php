<?php 
/**
 * An authorization identification number. Character length and
 * limits: 19 single-byte characters 
 */
class UpdateAuthorizationResponseType  extends AbstractResponseType  
  {

	/**
	 * An authorization identification number. Character length and
	 * limits: 19 single-byte characters 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionID;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AuthorizationInfoType 	 
	 */ 
	public $AuthorizationInfo;


}
