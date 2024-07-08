<?php 
/**
 * Details about the payer's tax info passed in by the merchant
 * or partner. Optional. 
 */
class TaxIdDetailsType  
   extends PPXmlMessage{

	/**
	 * The payer's Tax ID type; CNPJ/CPF for BR country. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TaxIdType;

	/**
	 * The payer's Tax ID 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TaxId;


  
 
}
