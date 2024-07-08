<?php 
/**
 * 
 */
class ManageRecurringPaymentsProfileStatusRequestDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProfileID;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Action;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Note;

	/**
	 * Constructor with arguments
	 */
	public function __construct($ProfileID = NULL, $Action = NULL) {
		$this->ProfileID = $ProfileID;
		$this->Action = $Action;
	}


  
 
}
