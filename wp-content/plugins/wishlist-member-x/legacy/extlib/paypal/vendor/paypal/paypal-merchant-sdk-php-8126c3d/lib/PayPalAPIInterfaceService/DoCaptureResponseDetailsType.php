<?php 
/**
 * The authorization identification number you specified in the
 * request. Character length and limits: 19 single-byte
 * characters maximum 
 */
class DoCaptureResponseDetailsType  
   extends PPXmlMessage{

	/**
	 * The authorization identification number you specified in the
	 * request. Character length and limits: 19 single-byte
	 * characters maximum
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $AuthorizationID;

	/**
	 * Information about the transaction 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentInfoType 	 
	 */ 
	public $PaymentInfo;

	/**
	 * Return msgsubid back to merchant 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;

	/**
	 * Partner funding source id corresponding to the FS used in
	 * authorization. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PartnerFundingSourceID;


}
