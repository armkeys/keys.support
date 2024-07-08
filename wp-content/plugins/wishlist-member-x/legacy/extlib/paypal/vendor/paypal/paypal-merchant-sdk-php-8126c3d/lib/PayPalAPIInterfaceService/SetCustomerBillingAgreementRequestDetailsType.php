<?php 
/**
 * 
 */
class SetCustomerBillingAgreementRequestDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingAgreementDetailsType 	 
	 */ 
	public $BillingAgreementDetails;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ReturnURL;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CancelURL;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $LocaleCode;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PageStyle;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 
	 * @name cpp-header-image
	 	 	 	 
	 * @var string 	 
	 */ 
	public $cppheaderimage;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 
	 * @name cpp-header-border-color
	 	 	 	 
	 * @var string 	 
	 */ 
	public $cppheaderbordercolor;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 
	 * @name cpp-header-back-color
	 	 	 	 
	 * @var string 	 
	 */ 
	public $cppheaderbackcolor;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 
	 * @name cpp-payflow-color
	 	 	 	 
	 * @var string 	 
	 */ 
	public $cpppayflowcolor;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BuyerEmail;

	/**
	 * The value 1 indicates that you require that the customer's
	 * billing address on file. Setting this element overrides the
	 * setting you have specified in Admin. Optional Character
	 * length and limitations: One single-byte numeric character.
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ReqBillingAddress;

	/**
	 * Constructor with arguments
	 */
	public function __construct($BillingAgreementDetails = NULL, $ReturnURL = NULL, $CancelURL = NULL) {
		$this->BillingAgreementDetails = $BillingAgreementDetails;
		$this->ReturnURL = $ReturnURL;
		$this->CancelURL = $CancelURL;
	}


  
 
}
