<?php 
/**
 * 
 */
class RecurringPaymentsSummaryType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $NextBillingDate;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $NumberCyclesCompleted;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $NumberCyclesRemaining;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $OutstandingBalance;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $FailedPaymentCount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $LastPaymentDate;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $LastPaymentAmount;


}
