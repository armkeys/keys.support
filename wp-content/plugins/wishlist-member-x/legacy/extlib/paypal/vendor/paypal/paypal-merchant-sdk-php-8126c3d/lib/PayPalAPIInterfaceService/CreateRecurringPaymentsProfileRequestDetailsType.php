<?php 
/**
 * Billing Agreement token (required if Express Checkout) 
 */
class CreateRecurringPaymentsProfileRequestDetailsType  
   extends PPXmlMessage{

	/**
	 * Billing Agreement token (required if Express Checkout) 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Token;

	/**
	 * Information about the credit card to be charged (required if
	 * Direct Payment) 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var CreditCardDetailsType 	 
	 */ 
	public $CreditCard;

	/**
	 * Customer Information for this Recurring Payments 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var RecurringPaymentsProfileDetailsType 	 
	 */ 
	public $RecurringPaymentsProfileDetails;

	/**
	 * Schedule Information for this Recurring Payments 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ScheduleDetailsType 	 
	 */ 
	public $ScheduleDetails;

	/**
	 * Information about the Item Details. 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentDetailsItemType 	 
	 */ 
	public $PaymentDetailsItem;

	/**
	 * Constructor with arguments
	 */
	public function __construct($RecurringPaymentsProfileDetails = NULL, $ScheduleDetails = NULL) {
		$this->RecurringPaymentsProfileDetails = $RecurringPaymentsProfileDetails;
		$this->ScheduleDetails = $ScheduleDetails;
	}


  
 
}
