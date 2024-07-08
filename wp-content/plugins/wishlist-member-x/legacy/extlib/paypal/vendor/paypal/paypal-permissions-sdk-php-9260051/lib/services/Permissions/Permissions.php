<?php
 /**
  * Stub objects for Permissions 
  * Auto generated code 
  * 
  */
/**
 * 
 */
if(!class_exists('ErrorData', false)) {
class ErrorData  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var integer 	 
	 */ 
	public $errorId;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $domain;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $subdomain;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $severity;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $category;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $message;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $exceptionId;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorParameter 	 
	 */ 
	public $parameter;


}
}



/**
 * @hasAttribute
 * 
 */
if(!class_exists('ErrorParameter', false)) {
class ErrorParameter  
  extends PPMessage   {

	/**
	 * 
	 
	 
	 * @attribute 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $name;

	/**
	 * 
	 
	 
	 * @value
	 	 	 	 
	 * @var string 	 
	 */ 
	public $value;


}
}



/**
 * This is the sample message 
 */
if(!class_exists('ResponseEnvelope', false)) {
class ResponseEnvelope  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $timestamp;

	/**
	 * Application level acknowledgment code. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ack;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $correlationId;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $build;


}
}



/**
 * This specifies the list of parameters with every request to
 * the service. 
 */
if(!class_exists('RequestEnvelope', false)) {
class RequestEnvelope  
  extends PPMessage   {

	/**
	 * This should be the standard RFC 3066 language identification
	 * tag, e.g., en_US. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $errorLanguage;

	/**
	 * Constructor with arguments
	 */
	public function __construct($errorLanguage = NULL) {
		$this->errorLanguage = $errorLanguage;
	}


}
}



/**
 * 
 */
if(!class_exists('FaultMessage', false)) {
class FaultMessage  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var ResponseEnvelope 	 
	 */ 
	public $responseEnvelope;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorData 	 
	 */ 
	public $error;


}
}



/**
 * Describes the request for permissions over an account.
 * Primary element is "scope", which lists the permissions
 * needed. 
 */
class RequestPermissionsRequest  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var RequestEnvelope 	 
	 */ 
	public $requestEnvelope;

	/**
	 * URI of the permissions being requested. 
     * @array
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $scope;

	/**
	 * URL on the client side that will be used to communicate
	 * completion of the user flow. The URL can include query
	 * parameters. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $callback;

	/**
	 * Constructor with arguments
	 */
	public function __construct($scope = NULL, $callback = NULL) {
		$this->scope = $scope;
		$this->callback = $callback;
	}


}



/**
 * Returns the temporary request token 
 */
class RequestPermissionsResponse  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var ResponseEnvelope 	 
	 */ 
	public $responseEnvelope;

	/**
	 * Temporary token that identifies the request for permissions.
	 * This token cannot be used to access resources on the
	 * account. It can only be used to instruct the user to
	 * authorize the permissions. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $token;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorData 	 
	 */ 
	public $error;


}



/**
 * The request use to retrieve a permanent access token. The
 * client can either send the token and verifier, or a subject.
 * 
 */
class GetAccessTokenRequest  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var RequestEnvelope 	 
	 */ 
	public $requestEnvelope;

	/**
	 * The temporary request token received from the
	 * RequestPermissions call. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $token;

	/**
	 * The verifier code returned to the client after the user
	 * authorization flow completed. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $verifier;

	/**
	 * The subject email address used to represent existing 3rd
	 * Party Permissions relationship. This field can be used in
	 * lieu of the token and verifier. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $subjectAlias;


}



/**
 * Permanent access token and token secret that can be used to
 * make requests for protected resources owned by another
 * account. 
 */
class GetAccessTokenResponse  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var ResponseEnvelope 	 
	 */ 
	public $responseEnvelope;

	/**
	 * Identifier for the permissions approved for this
	 * relationship. 
     * @array
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $scope;

	/**
	 * Permanent access token that identifies the relationship that
	 * the user authorized. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $token;

	/**
	 * The token secret/password that will need to be used when
	 * generating the signature. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $tokenSecret;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorData 	 
	 */ 
	public $error;


}



/**
 * Request to retrieve the approved list of permissions
 * associated with a token. 
 */
class GetPermissionsRequest  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var RequestEnvelope 	 
	 */ 
	public $requestEnvelope;

	/**
	 * The permanent access token to ask about. 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $token;

	/**
	 * Constructor with arguments
	 */
	public function __construct($token = NULL) {
		$this->token = $token;
	}


}



/**
 * The list of permissions associated with the token. 
 */
class GetPermissionsResponse  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var ResponseEnvelope 	 
	 */ 
	public $responseEnvelope;

	/**
	 * Identifier for the permissions approved for this
	 * relationship. 
     * @array
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $scope;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorData 	 
	 */ 
	public $error;


}



/**
 * Request to invalidate an access token and revoke the
 * permissions associated with it. 
 */
class CancelPermissionsRequest  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var RequestEnvelope 	 
	 */ 
	public $requestEnvelope;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $token;

	/**
	 * Constructor with arguments
	 */
	public function __construct($token = NULL) {
		$this->token = $token;
	}


}



/**
 * 
 */
class CancelPermissionsResponse  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var ResponseEnvelope 	 
	 */ 
	public $responseEnvelope;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorData 	 
	 */ 
	public $error;


}



/**
 * List of Personal Attributes to be sent as a request. 
 */
class PersonalAttributeList  
  extends PPMessage   {

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $attribute;


}



/**
 * A property of User Identity data , represented as a
 * Name-value pair with Name being the PersonalAttribute
 * requested and value being the data. 
 */
class PersonalData  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $personalDataKey;

	/**
	 * 
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $personalDataValue;


}



/**
 * Set of personal data which forms the response of
 * GetPersonalData call. 
 */
class PersonalDataList  
  extends PPMessage   {

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var PersonalData 	 
	 */ 
	public $personalData;


}



/**
 * Request to retrieve basic personal data.Accepts
 * PersonalAttributeList as request and responds with
 * PersonalDataList. This call will accept only 'Basic'
 * attributes and ignore others. 
 */
class GetBasicPersonalDataRequest  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var RequestEnvelope 	 
	 */ 
	public $requestEnvelope;

	/**
	 * 
	 
	 	 	 	 
	 * @var PersonalAttributeList 	 
	 */ 
	public $attributeList;

	/**
	 * Constructor with arguments
	 */
	public function __construct($attributeList = NULL) {
		$this->attributeList = $attributeList;
	}


}



/**
 * Request to retrieve personal data.Accepts
 * PersonalAttributeList as request and responds with
 * PersonalDataList. This call will accept both 'Basic' and
 * Advanced attributes. 
 */
class GetAdvancedPersonalDataRequest  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var RequestEnvelope 	 
	 */ 
	public $requestEnvelope;

	/**
	 * 
	 
	 	 	 	 
	 * @var PersonalAttributeList 	 
	 */ 
	public $attributeList;

	/**
	 * Constructor with arguments
	 */
	public function __construct($attributeList = NULL) {
		$this->attributeList = $attributeList;
	}


}



/**
 * 
 */
class GetBasicPersonalDataResponse  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var ResponseEnvelope 	 
	 */ 
	public $responseEnvelope;

	/**
	 * 
	 
	 	 	 	 
	 * @var PersonalDataList 	 
	 */ 
	public $response;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorData 	 
	 */ 
	public $error;


}



/**
 * 
 */
class GetAdvancedPersonalDataResponse  
  extends PPMessage   {

	/**
	 * 
	 
	 	 	 	 
	 * @var ResponseEnvelope 	 
	 */ 
	public $responseEnvelope;

	/**
	 * 
	 
	 	 	 	 
	 * @var PersonalDataList 	 
	 */ 
	public $response;

	/**
	 * 
     * @array
	 
	 	 	 	 
	 * @var ErrorData 	 
	 */ 
	public $error;


}



