<?php 
/**
 * 
 */
class GetIncentiveEvaluationRequestDetailsType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ExternalBuyerId;

	/**
	 * 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $IncentiveCodes;

	/**
	 * 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var IncentiveApplyIndicationType 	 
	 */ 
	public $ApplyIndication;

	/**
	 * 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var IncentiveBucketType 	 
	 */ 
	public $Buckets;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BasicAmountType 	 
	 */ 
	public $CartTotalAmt;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var IncentiveRequestDetailsType 	 
	 */ 
	public $RequestDetails;


  
 
}
