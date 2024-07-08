<?php 
/**
 * Status of merchant's onboarding process:
 * CompletedCancelledPending Character length and limitations:
 * Eight alphabetic characters 
 */
class GetBoardingDetailsResponseDetailsType  
   extends PPXmlMessage{

	/**
	 * Status of merchant's onboarding process:
	 * CompletedCancelledPending Character length and limitations:
	 * Eight alphabetic characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Status;

	/**
	 * Date the boarding process started
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $StartDate;

	/**
	 * Date the merchantâs status or progress was last updated
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $LastUpdated;

	/**
	 * Reason for merchantâs cancellation of sign-up. Character
	 * length and limitations: 1,024 alphanumeric characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Reason;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProgramName;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ProgramCode;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CampaignID;

	/**
	 * Indicates if there is a limitation on the amount of money
	 * the business can withdraw from PayPal
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $UserWithdrawalLimit;

	/**
	 * Custom information you set on the EnterBoarding API call
	 * Character length and limitations: 256 alphanumeric
	 * characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PartnerCustom;

	/**
	 * Details about the owner of the account
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var PayerInfoType 	 
	 */ 
	public $AccountOwner;

	/**
	 * Merchantâs PayPal API credentials
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var APICredentialsType 	 
	 */ 
	public $Credentials;

	/**
	 * The APIs that this merchant has granted the business partner
	 * permission to call on his behalf. For example:
	 * SetExpressCheckout,GetExpressCheckoutDetails,DoExpressCheckoutPayment
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ConfigureAPIs;

	/**
	 * Primary email verification status. Confirmed, Unconfirmed
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $EmailVerificationStatus;

	/**
	 * Gives VettingStatus - Pending, Cancelled, Approved,
	 * UnderReview Character length and limitations: 256
	 * alphanumeric characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $VettingStatus;

	/**
	 * Gives BankAccountVerificationStatus - Added, Confirmed
	 * Character length and limitations: 256 alphanumeric
	 * characters
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $BankAccountVerificationStatus;


}
