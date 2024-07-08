<?php 
/**
 * Msg Sub Id that was used for the orginal operation. 
 */
class DoCancelRequestType  extends AbstractRequestType  
  {

	/**
	 * Msg Sub Id that was used for the orginal operation. 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CancelMsgSubID;

	/**
	 * Original API's type
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $APIType;

	/**
	 * Unique id for each API request to prevent duplicate
	 * payments. Optional Character length and limits: 38
	 * single-byte characters maximum. 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;

	/**
	 * Constructor with arguments
	 */
	public function __construct($CancelMsgSubID = NULL, $APIType = NULL) {
		$this->CancelMsgSubID = $CancelMsgSubID;
		$this->APIType = $APIType;
	}


  
 
}
