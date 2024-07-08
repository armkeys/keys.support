<?php 
/**
 * Lists the Payment Methods (other than PayPal) that the use
 * can pay with e.g. Money Order. Optional. 
 */
class OtherPaymentMethodDetailsType  
   extends PPXmlMessage{

	/**
	 * The identifier of the Payment Method. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OtherPaymentMethodId;

	/**
	 * Valid values are 'Method', 'SubMethod'. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OtherPaymentMethodType;

	/**
	 * The name of the Payment Method. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OtherPaymentMethodLabel;

	/**
	 * The short description of the Payment Method, goes along with
	 * the label. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OtherPaymentMethodLabelDescription;

	/**
	 * The title for the long description. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OtherPaymentMethodLongDescriptionTitle;

	/**
	 * The long description of the Payment Method. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OtherPaymentMethodLongDescription;

	/**
	 * The icon of the Payment Method. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OtherPaymentMethodIcon;

	/**
	 * If this flag is true, then OtherPaymentMethodIcon is
	 * required to have a valid value; the label will be hidden and
	 * only ICON will be shown. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var boolean 	 
	 */ 
	public $OtherPaymentMethodHideLabel;


  
 
}
