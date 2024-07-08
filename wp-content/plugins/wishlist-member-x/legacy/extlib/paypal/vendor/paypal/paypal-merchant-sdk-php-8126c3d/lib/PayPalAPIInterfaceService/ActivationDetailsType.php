<?php 
/**
 * 
 */
class ActivationDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $InitialAmount;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $FailedInitialAmountAction;

	/**
	 * Constructor with arguments
	 */
	public function __construct($InitialAmount = NULL) {
		$this->InitialAmount = $InitialAmount;
	}


  
 
}
