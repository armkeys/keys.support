<?php 
/**
 * Identity Access token from merchant 
 */
class IdentityTokenInfoType  
   extends PPXmlMessage{

	/**
	 * Identity Access token from merchant
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AccessToken;

	/**
	 * Constructor with arguments
	 */
	public function __construct($AccessToken = NULL) {
		$this->AccessToken = $AccessToken;
	}


  
 
}
