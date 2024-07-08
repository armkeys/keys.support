<?php 
/**
 * The authorization identification number you specified in the
 * request. Character length and limits: 19 single-byte
 * characters 
 */
class DoVoidResponseType  extends AbstractResponseType  
  {

	/**
	 * The authorization identification number you specified in the
	 * request. Character length and limits: 19 single-byte
	 * characters
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AuthorizationID;

	/**
	 * Return msgsubid back to merchant
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;


}
