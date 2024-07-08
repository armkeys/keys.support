<?php 
/**
 * Type of button. One of the following: BUYNOW, CART,
 * GIFTCERTIFICATE. SUBSCRIBE, PAYMENTPLAN, AUTOBILLING,
 * DONATE, VIEWCART or UNSUBSCRIBE 
 */
class BMGetButtonDetailsResponseType  extends AbstractResponseType  
  {

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Website;

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Email;

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Mobile;

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $HostedButtonID;

	/**
	 * Type of button. One of the following: BUYNOW, CART,
	 * GIFTCERTIFICATE. SUBSCRIBE, PAYMENTPLAN, AUTOBILLING,
	 * DONATE, VIEWCART or UNSUBSCRIBE 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonType;

	/**
	 * Type of button code. One of the following: hosted, encrypted
	 * or cleartext 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonCode;

	/**
	 * Button sub type. optional for button types buynow and cart
	 * only Either PRODUCTS or SERVICES 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonSubType;

	/**
	 * Button Variable information Character length and
	 * limitations: 63 single-byte alphanumeric characters 
     * @array
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonVar;

	/**
	 * 
     * @array
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var OptionDetailsType 	 
	 */ 
	public $OptionDetails;

	/**
	 * Text field 
     * @array
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TextBox;

	/**
	 * Button image to use. One of: REG, SML, or CC 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonImage;

	/**
	 * Button URL for custom button image. 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonImageURL;

	/**
	 * Text to use on Buy Now Button. Either BUYNOW or PAYNOW 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BuyNowText;

	/**
	 * Text to use on Subscribe button. Must be either BUYNOW or
	 * SUBSCRIBE 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SubscribeText;

	/**
	 * Button Country. Valid ISO country code or 'International' 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonCountry;

	/**
	 * Button language code. Character length and limitations: 3
	 * single-byte alphanumeric characters 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonLanguage;


}
