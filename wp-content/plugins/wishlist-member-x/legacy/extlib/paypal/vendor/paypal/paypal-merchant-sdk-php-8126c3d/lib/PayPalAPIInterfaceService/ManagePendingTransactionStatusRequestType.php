<?php 
/**
 * 
 */
class ManagePendingTransactionStatusRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionID;

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Action;

	/**
	 * Constructor with arguments
	 */
	public function __construct($TransactionID = NULL, $Action = NULL) {
		$this->TransactionID = $TransactionID;
		$this->Action = $Action;
	}


  
 
}
