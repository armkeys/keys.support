<?php 
/**
 * The value of the authorizationâtransaction identification
 * number returned by a PayPal product. Required Character
 * length and limits: 19 single-byte characters maximum 
 */
class UpdateAuthorizationRequestType  extends AbstractRequestType  
  {

	/**
	 * The value of the authorizationâtransaction identification
	 * number returned by a PayPal product. Required Character
	 * length and limits: 19 single-byte characters maximum 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionID;

	/**
	 * Shipping Address for this transaction. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AddressType 	 
	 */ 
	public $ShipToAddress;

	/**
	 * IP Address of the buyer 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $IPAddress;

	/**
	 * Constructor with arguments
	 */
	public function __construct($TransactionID = NULL) {
		$this->TransactionID = $TransactionID;
	}


  
 
}
