<?php 
/**
 * 
 */
class CreateMobilePaymentRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var CreateMobilePaymentRequestDetailsType 	 
	 */ 
	public $CreateMobilePaymentRequestDetails;

	/**
	 * Constructor with arguments
	 */
	public function __construct($CreateMobilePaymentRequestDetails = NULL) {
		$this->CreateMobilePaymentRequestDetails = $CreateMobilePaymentRequestDetails;
	}


  
 
}
