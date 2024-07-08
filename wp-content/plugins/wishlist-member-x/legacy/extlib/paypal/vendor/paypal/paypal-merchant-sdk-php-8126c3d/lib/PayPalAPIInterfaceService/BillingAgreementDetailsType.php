<?php 
/**
 * 
 */
class BillingAgreementDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingType;

	/**
	 * Only needed for AutoBill billinng type. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingAgreementDescription;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentType;

	/**
	 * Custom annotation field for your exclusive use. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingAgreementCustom;

	/**
	 * Constructor with arguments
	 */
	public function __construct($BillingType = NULL) {
		$this->BillingType = $BillingType;
	}


  
 
}
