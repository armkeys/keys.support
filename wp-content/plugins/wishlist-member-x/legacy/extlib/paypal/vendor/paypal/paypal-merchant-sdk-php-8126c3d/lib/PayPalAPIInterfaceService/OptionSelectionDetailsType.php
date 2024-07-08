<?php 
/**
 * Option Selection. Required Character length and limitations:
 * 12 single-byte alphanumeric characters 
 */
class OptionSelectionDetailsType  
   extends PPXmlMessage{

	/**
	 * Option Selection. Required Character length and limitations:
	 * 12 single-byte alphanumeric characters 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OptionSelection;

	/**
	 * Option Price. Optional 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Price;

	/**
	 * Option Type Optional 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $OptionType;

	/**
	 * 
     * @array
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var InstallmentDetailsType 	 
	 */ 
	public $PaymentPeriod;

	/**
	 * Constructor with arguments
	 */
	public function __construct($OptionSelection = NULL) {
		$this->OptionSelection = $OptionSelection;
	}


  
 
}
