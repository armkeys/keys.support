<?php 
/**
 * PaymentItemType Information about a Payment Item. 
 */
class PaymentItemType  
   extends PPXmlMessage{

	/**
	 * eBay Auction Transaction ID of the Item Optional Character
	 * length and limitations: 255 single-byte characters 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $EbayItemTxnId;

	/**
	 * Item name set by you or entered by the customer. Character
	 * length and limitations: 127 single-byte alphanumeric
	 * characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Name;

	/**
	 * Item number set by you. Character length and limitations:
	 * 127 single-byte alphanumeric characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Number;

	/**
	 * Quantity set by you or entered by the customer. Character
	 * length and limitations: no limit
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Quantity;

	/**
	 * Amount of tax charged on payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SalesTax;

	/**
	 * Amount of shipping charged on payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ShippingAmount;

	/**
	 * Amount of handling charged on payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $HandlingAmount;

	/**
	 * Invoice item details 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var InvoiceItemType 	 
	 */ 
	public $InvoiceItemDetails;

	/**
	 * Coupon ID Number 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CouponID;

	/**
	 * Amount Value of The Coupon 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CouponAmount;

	/**
	 * Currency of the Coupon Amount 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CouponAmountCurrency;

	/**
	 * Amount of Discount on this Loyalty Card
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $LoyaltyCardDiscountAmount;

	/**
	 * Currency of the Discount
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $LoyaltyCardDiscountCurrency;

	/**
	 * Cost of item 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $Amount;

	/**
	 * Item options selected in PayPal shopping cart 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var OptionType 	 
	 */ 
	public $Options;


}
