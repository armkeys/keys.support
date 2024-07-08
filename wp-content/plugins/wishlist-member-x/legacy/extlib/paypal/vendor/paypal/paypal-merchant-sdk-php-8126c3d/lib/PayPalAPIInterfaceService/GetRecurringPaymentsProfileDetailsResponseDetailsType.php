<?php 
/**
 * Recurring Billing Profile ID 
 */
class GetRecurringPaymentsProfileDetailsResponseDetailsType  
   extends PPXmlMessage{

	/**
	 * Recurring Billing Profile ID 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileID;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileStatus;

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
	public $AutoBillOutstandingAmount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $MaxFailedPayments;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var RecurringPaymentsProfileDetailsType 	 
	 */ 
	public $RecurringPaymentsProfileDetails;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingPeriodDetailsType 	 
	 */ 
	public $CurrentRecurringPaymentsPeriod;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var RecurringPaymentsSummaryType 	 
	 */ 
	public $RecurringPaymentsSummary;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var CreditCardDetailsType 	 
	 */ 
	public $CreditCard;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingPeriodDetailsType 	 
	 */ 
	public $TrialRecurringPaymentsPeriod;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingPeriodDetailsType 	 
	 */ 
	public $RegularRecurringPaymentsPeriod;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $TrialAmountPaid;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $RegularAmountPaid;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $AggregateAmount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $AggregateOptionalAmount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $FinalPaymentDueDate;


}
