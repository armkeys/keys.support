<?php 
/**
 * Error code can be used by a receiving application to
 * debugging a response message. These codes will need to be
 * uniquely defined for each application. 
 */
class ErrorType  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ShortMessage;

	/**
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $LongMessage;

	/**
	 * Error code can be used by a receiving application to
	 * debugging a response message. These codes will need to be
	 * uniquely defined for each application. 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ErrorCode;

	/**
	 * SeverityCode indicates whether the error is an application 
	 * level error or if it is informational error, i.e., warning. 
	 * 
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $SeverityCode;

	/**
	 * This optional element may carry additional
	 * application-specific error variables that indicate specific
	 * information about the error condition particularly in the
	 * cases where there are multiple instances of the ErrorType
	 * which require additional context.  
     * @array
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ErrorParameterType 	 
	 */ 
	public $ErrorParameters;


}
