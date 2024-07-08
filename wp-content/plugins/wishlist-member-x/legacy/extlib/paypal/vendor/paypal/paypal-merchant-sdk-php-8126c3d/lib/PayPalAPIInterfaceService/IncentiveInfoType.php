<?php 
/**
 * Details of incentive application on individual bucket. 
 */
class IncentiveInfoType  
   extends PPXmlMessage{

	/**
	 * Incentive redemption code. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $IncentiveCode;

	/**
	 * Defines which bucket or item that the incentive should be
	 * applied to. 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var IncentiveApplyIndicationType 	 
	 */ 
	public $ApplyIndication;


  
 
}
