<?php 
/**
 * Recurring Billing Profile ID 
 */
class CreateRecurringPaymentsProfileResponseDetailsType  
   extends PPXmlMessage{

	/**
	 * Recurring Billing Profile ID 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileID;

	/**
	 * Recurring Billing Profile Status 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileStatus;

	/**
	 * Transaction id from DCC initial payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionID;

	/**
	 * Response from DCC initial payment 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $DCCProcessorResponse;

	/**
	 * Return code if DCC initial payment fails 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $DCCReturnCode;


}
