<?php 
/**
 * This holds single key-value pair. 
 */
class TupleType  
   extends PPXmlMessage{

	/**
	 * Key Name.Optional
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Key;

	/**
	 * Value for the above key.Optional
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Value;

	/**
	 * Constructor with arguments
	 */
	public function __construct($Key = NULL, $Value = NULL) {
		$this->Key = $Key;
		$this->Value = $Value;
	}


  
 
}
