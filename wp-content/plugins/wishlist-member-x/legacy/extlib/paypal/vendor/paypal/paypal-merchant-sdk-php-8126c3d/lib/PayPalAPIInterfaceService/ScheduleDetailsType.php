<?php 
/**
 * Schedule details for the Recurring Payment 
 */
class ScheduleDetailsType  
   extends PPXmlMessage{

	/**
	 * Schedule details for the Recurring Payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Description;

	/**
	 * Trial period of this schedule 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingPeriodDetailsType 	 
	 */ 
	public $TrialPeriod;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingPeriodDetailsType 	 
	 */ 
	public $PaymentPeriod;

	/**
	 * The max number of payments the buyer can fail before this
	 * Recurring Payments profile is cancelled 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $MaxFailedPayments;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ActivationDetailsType 	 
	 */ 
	public $ActivationDetails;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AutoBillOutstandingAmount;

	/**
	 * Constructor with arguments
	 */
	public function __construct($Description = NULL, $PaymentPeriod = NULL) {
		$this->Description = $Description;
		$this->PaymentPeriod = $PaymentPeriod;
	}


  
 
}
