<?php 
/**
 * Auth Authorization Code. 
 */
class DoUATPAuthorizationResponseType  extends DoAuthorizationResponseType  
  {

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var UATPDetailsType 	 
	 */ 
	public $UATPDetails;

	/**
	 * Auth Authorization Code. 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AuthorizationCode;

	/**
	 * Invoice ID. A pass through. 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $InvoiceID;

	/**
	 * Unique id for each API request to prevent duplicate
	 * payments. Optional Character length and limits: 38
	 * single-byte characters maximum. 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;


}
