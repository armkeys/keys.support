<?php 
/**
 * 
 */
class BAUpdateRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ReferenceID;

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingAgreementDescription;

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingAgreementStatus;

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BillingAgreementCustom;

	/**
	 * Constructor with arguments
	 */
	public function __construct($ReferenceID = NULL) {
		$this->ReferenceID = $ReferenceID;
	}


  
 
}
