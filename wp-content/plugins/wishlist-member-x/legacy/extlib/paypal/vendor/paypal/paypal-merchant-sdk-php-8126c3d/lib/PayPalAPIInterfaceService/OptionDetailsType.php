<?php 
/**
 * Option Name. Optional 
 */
class OptionDetailsType  
   extends PPXmlMessage{

	/**
	 * Option Name. Optional 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OptionName;

	/**
	 * 
     * @array
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var OptionSelectionDetailsType 	 
	 */ 
	public $OptionSelectionDetails;

	/**
	 * Constructor with arguments
	 */
	public function __construct($OptionName = NULL) {
		$this->OptionName = $OptionName;
	}


  
 
}
