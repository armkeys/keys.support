<?php 
/**
 * @hasAttribute
 * 
 */
class MeasureType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace cc
	 
	 
	 * @attribute 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $unit;

	/**
	 * 
	 
	 * @namespace cc
	 
	 
	 * @value
	 	 	 	 
	 * @var double 	 
	 */ 
	public $value;

	/**
	 * Constructor with arguments
	 */
	public function __construct($unit = NULL, $value = NULL) {
		$this->unit = $unit;
		$this->value = $value;
	}


  
 
}
