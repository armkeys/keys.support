<?php 
/**
 * 
 */
class GetBillingAgreementCustomerDetailsRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Token;

	/**
	 * Constructor with arguments
	 */
	public function __construct($Token = NULL) {
		$this->Token = $Token;
	}


  
 
}
