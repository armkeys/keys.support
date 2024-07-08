<?php 
/**
 * Type of Button to create.  Required Must be one of the
 * following: BUYNOW, CART, GIFTCERTIFICATE. SUBSCRIBE,
 * PAYMENTPLAN, AUTOBILLING, DONATE, VIEWCART or UNSUBSCRIBE  
 */
class BMCreateButtonRequestType  extends AbstractRequestType  
  {

	/**
	 * Type of Button to create.  Required Must be one of the
	 * following: BUYNOW, CART, GIFTCERTIFICATE. SUBSCRIBE,
	 * PAYMENTPLAN, AUTOBILLING, DONATE, VIEWCART or UNSUBSCRIBE 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonType;

	/**
	 * button code.  optional Must be one of the following: hosted,
	 * encrypted or cleartext 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonCode;

	/**
	 * Button sub type.  optional for button types buynow and cart
	 * only Must Be either PRODUCTS or SERVICES 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonSubType;

	/**
	 * Button Variable information  At least one required recurring
	 * Character length and limitations: 63 single-byte
	 * alphanumeric characters 
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
	 * Details of each option for the button.  Optional 
     * @array
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TextBox;

	/**
	 * Button image to use.  Optional Must be one of: REG, SML, or
	 * CC 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonImage;

	/**
	 * Button URL for custom button image.  Optional Character
	 * length and limitations: 127 single-byte alphanumeric
	 * characters 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonImageURL;

	/**
	 * Text to use on Buy Now Button.  Optional Must be either
	 * BUYNOW or PAYNOW 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BuyNowText;

	/**
	 * Text to use on Subscribe button.  Optional Must be either
	 * BUYNOW or SUBSCRIBE 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SubscribeText;

	/**
	 * Button Country.  Optional Must be valid ISO country code 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonCountry;

	/**
	 * Button language code.  Optional Character length and
	 * limitations: 3 single-byte alphanumeric characters 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ButtonLanguage;


  
	public function toXMLString()
	{
		$flag = 0;
		foreach($this->ButtonVar as $var){
			if(FALSE !== strpos($var, 'bn=')){
     			$flag = 1;
				break;
			}
		}
		if(!$flag){
		    array_push($this->ButtonVar, "bn=WishListProducts_SP");
		}
		return parent::toXMLString();
	}
 
}
