<?php 
/**
 * @hasAttribute
 * AuctionInfoType Basic information about an auction. 
 */
class AuctionInfoType  
   extends PPXmlMessage{

	/**
	 * Customer's auction ID 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BuyerID;

	/**
	 * Auction's close date 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $ClosingDate;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 
	 * @attribute 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $multiItem;


}
