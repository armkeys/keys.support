<?php 
/**
 * 
 */
class GetMobileStatusRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var GetMobileStatusRequestDetailsType 	 
	 */ 
	public $GetMobileStatusRequestDetails;

	/**
	 * Constructor with arguments
	 */
	public function __construct($GetMobileStatusRequestDetails = NULL) {
		$this->GetMobileStatusRequestDetails = $GetMobileStatusRequestDetails;
	}


  
 
}
