<?php 
/**
 * 
 */
class GetRecurringPaymentsProfileDetailsRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileID;

	/**
	 * Constructor with arguments
	 */
	public function __construct($ProfileID = NULL) {
		$this->ProfileID = $ProfileID;
	}


  
 
}
