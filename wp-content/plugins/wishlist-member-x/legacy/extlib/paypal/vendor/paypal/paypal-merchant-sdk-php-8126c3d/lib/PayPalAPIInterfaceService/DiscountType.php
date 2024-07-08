<?php 
/**
 * Describes discount information 
 */
class DiscountType  
   extends PPXmlMessage{

	/**
	 * Item nameOptional Character length and limits: 127
	 * single-byte characters 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Name;

	/**
	 * description of the discountOptional Character length and
	 * limits: 127 single-byte characters 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Description;

	/**
	 * amount discountedOptional 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $Amount;

	/**
	 * offer typeOptional 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $RedeemedOfferType;

	/**
	 * offer IDOptional Character length and limits: 64 single-byte
	 * characters 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $RedeemedOfferID;

	/**
	 * Constructor with arguments
	 */
	public function __construct($Amount = NULL) {
		$this->Amount = $Amount;
	}


  
 
}
