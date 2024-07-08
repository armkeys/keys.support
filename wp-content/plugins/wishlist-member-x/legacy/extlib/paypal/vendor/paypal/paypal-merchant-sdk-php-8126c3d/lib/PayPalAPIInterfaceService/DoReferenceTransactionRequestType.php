<?php 
/**
 * This flag indicates that the response should include
 * FMFDetails 
 */
class DoReferenceTransactionRequestType  extends AbstractRequestType  
  {

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var DoReferenceTransactionRequestDetailsType 	 
	 */ 
	public $DoReferenceTransactionRequestDetails;

	/**
	 * This flag indicates that the response should include
	 * FMFDetails
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $ReturnFMFDetails;

	/**
	 * Constructor with arguments
	 */
	public function __construct($DoReferenceTransactionRequestDetails = NULL) {
		$this->DoReferenceTransactionRequestDetails = $DoReferenceTransactionRequestDetails;
	}


  
 
}
