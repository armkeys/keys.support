<?php 
/**
 * OffersAndCouponsInfoType Information about a Offers and
 * Coupons. 
 */
class OfferCouponInfoType  
   extends PPXmlMessage{

	/**
	 * Type of the incentive 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Type;

	/**
	 * ID of the Incentive used in transaction
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ID;

	/**
	 * Amount used on transaction
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Amount;

	/**
	 * Amount Currency
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AmountCurrency;


}
