<?php 
/**
 * If Checkout session was initialized successfully, the
 * corresponding token is returned in this element. 
 */
class SetDataResponseType  
   extends PPXmlMessage{

	/**
	 * If Checkout session was initialized successfully, the
	 * corresponding token is returned in this element. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Token;

	/**
	 * 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AddressType 	 
	 */ 
	public $ShippingAddresses;

	/**
	 * 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ErrorType 	 
	 */ 
	public $SetDataError;


}
