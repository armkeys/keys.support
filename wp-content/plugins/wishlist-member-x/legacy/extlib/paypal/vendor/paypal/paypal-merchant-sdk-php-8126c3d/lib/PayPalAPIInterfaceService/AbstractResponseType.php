<?php 
/**
 * Base type definition of a response payload that can carry
 * any type of payload content with following optional
 * elements: - timestamp of response message, - application
 * level acknowledgement, and - application-level errors and
 * warnings. 
 */
class AbstractResponseType  
   extends PPXmlMessage{

	/**
	 * This value represents the date and time (GMT) when the
	 * response was generated by a service provider (as a result of
	 * processing of a request). 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var dateTime 	 
	 */ 
	public $Timestamp;

	/**
	 * Application level acknowledgement code. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Ack;

	/**
	 * CorrelationID may be used optionally with an application
	 * level acknowledgement. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $CorrelationID;

	/**
	 * 
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ErrorType 	 
	 */ 
	public $Errors;

	/**
	 * This refers to the version of the response payload schema. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Version;

	/**
	 * This refers to the specific software build that was used in
	 * the deployment for processing the request and generating the
	 * response. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $Build;


}