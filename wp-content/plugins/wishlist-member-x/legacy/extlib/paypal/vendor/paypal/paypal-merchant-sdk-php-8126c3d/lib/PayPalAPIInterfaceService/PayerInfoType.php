<?php 
/**
 * PayerInfoType Payer information 
 */
class PayerInfoType  
   extends PPXmlMessage{

	/**
	 * Email address of payer Character length and limitations: 127
	 * single-byte characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Payer;

	/**
	 * Unique customer ID Character length and limitations: 17
	 * single-byte characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PayerID;

	/**
	 * Status of payer's email address 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PayerStatus;

	/**
	 * Name of payer 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PersonNameType 	 
	 */ 
	public $PayerName;

	/**
	 * Payment sender's country of residence using standard
	 * two-character ISO 3166 country codes. Character length and
	 * limitations: Two single-byte characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PayerCountry;

	/**
	 * Payer's business name. Character length and limitations: 127
	 * single-byte characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PayerBusiness;

	/**
	 * Payer's business address
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var AddressType 	 
	 */ 
	public $Address;

	/**
	 * Business contact telephone number
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ContactPhone;

	/**
	 * Details about payer's tax info. Refer to the
	 * TaxIdDetailsType for more details. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var TaxIdDetailsType 	 
	 */ 
	public $TaxIdDetails;

	/**
	 * Holds any enhanced information about the payer
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var EnhancedPayerInfoType 	 
	 */ 
	public $EnhancedPayerInfo;


  
 
}
