<?php 
/**
 * 
 */
class UpdateRecurringPaymentsProfileRequestDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileID;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Note;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Description;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SubscriberName;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AddressType 	 
	 */ 
	public $SubscriberShippingAddress;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileReference;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $AdditionalBillingCycles;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $Amount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $ShippingAmount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $TaxAmount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $OutstandingBalance;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AutoBillOutstandingAmount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $MaxFailedPayments;

	/**
	 * Information about the credit card to be charged (required if
	 * Direct Payment) 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var CreditCardDetailsType 	 
	 */ 
	public $CreditCard;

	/**
	 * When does this Profile begin billing? 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $BillingStartDate;

	/**
	 * Trial period of this schedule 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingPeriodDetailsType_Update 	 
	 */ 
	public $TrialPeriod;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingPeriodDetailsType_Update 	 
	 */ 
	public $PaymentPeriod;

	/**
	 * Constructor with arguments
	 */
	public function __construct($ProfileID = NULL) {
		$this->ProfileID = $ProfileID;
	}


  
 
}
