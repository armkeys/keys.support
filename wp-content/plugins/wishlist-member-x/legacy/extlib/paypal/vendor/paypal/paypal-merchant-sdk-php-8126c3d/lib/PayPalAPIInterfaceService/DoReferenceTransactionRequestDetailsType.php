<?php 
/**
 * 
 */
class DoReferenceTransactionRequestDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ReferenceID;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentAction;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentType;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PaymentDetailsType 	 
	 */ 
	public $PaymentDetails;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ReferenceCreditCardDetailsType 	 
	 */ 
	public $CreditCard;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $IPAddress;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MerchantSessionId;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ReqConfirmShipping;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SoftDescriptor;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var SenderDetailsType 	 
	 */ 
	public $SenderDetails;

	/**
	 * Unique id for each API request to prevent duplicate
	 * payments. Optional Character length and limits: 38
	 * single-byte characters maximum. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $MsgSubID;

	/**
	 * Constructor with arguments
	 */
	public function __construct($ReferenceID = NULL, $PaymentAction = NULL, $PaymentDetails = NULL) {
		$this->ReferenceID = $ReferenceID;
		$this->PaymentAction = $PaymentAction;
		$this->PaymentDetails = $PaymentDetails;
	}


  
 
}
