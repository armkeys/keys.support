<?php 
/**
 * Unit of meausre for billing cycle 
 */
class BillingPeriodDetailsType  
   extends PPXmlMessage{

	/**
	 * Unit of meausre for billing cycle 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingPeriod;

	/**
	 * Number of BillingPeriod that make up one billing cycle 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $BillingFrequency;

	/**
	 * Total billing cycles in this portion of the schedule 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $TotalBillingCycles;

	/**
	 * Amount to charge 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $Amount;

	/**
	 * Additional shipping amount to charge 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $ShippingAmount;

	/**
	 * Additional tax amount to charge 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $TaxAmount;

	/**
	 * Constructor with arguments
	 */
	public function __construct($BillingPeriod = NULL, $BillingFrequency = NULL, $Amount = NULL) {
		$this->BillingPeriod = $BillingPeriod;
		$this->BillingFrequency = $BillingFrequency;
		$this->Amount = $Amount;
	}


  
 
}
