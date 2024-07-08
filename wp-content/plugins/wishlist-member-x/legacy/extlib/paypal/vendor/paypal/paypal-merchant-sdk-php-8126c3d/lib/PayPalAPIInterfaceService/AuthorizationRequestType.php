<?php 
/**
 * 
 */
class AuthorizationRequestType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var boolean 	 
	 */ 
	public $IsRequested;

	/**
	 * Constructor with arguments
	 */
	public function __construct($IsRequested = NULL) {
		$this->IsRequested = $IsRequested;
	}


  
 
}
