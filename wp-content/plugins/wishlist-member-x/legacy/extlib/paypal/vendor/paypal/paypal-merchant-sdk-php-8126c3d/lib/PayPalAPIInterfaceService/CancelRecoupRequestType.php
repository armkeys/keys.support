<?php 
/**
 * 
 */
class CancelRecoupRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ed
	 
	 	 	 	 
	 * @var EnhancedCancelRecoupRequestDetailsType 	 
	 */ 
	public $EnhancedCancelRecoupRequestDetails;

	/**
	 * Constructor with arguments
	 */
	public function __construct($EnhancedCancelRecoupRequestDetails = NULL) {
		$this->EnhancedCancelRecoupRequestDetails = $EnhancedCancelRecoupRequestDetails;
	}


  
 
}
