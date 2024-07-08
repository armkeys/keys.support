<?php 
/**
 * 
 */
class ExecuteCheckoutOperationsReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var ExecuteCheckoutOperationsRequestType 	 
	 */ 
	public $ExecuteCheckoutOperationsRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:ExecuteCheckoutOperationsReq>';
			if(NULL != $this->ExecuteCheckoutOperationsRequest)
			{
		   		$str .= '<ns:ExecuteCheckoutOperationsRequest>';
				$str .= $this->ExecuteCheckoutOperationsRequest->toXMLString();
				$str .= '</ns:ExecuteCheckoutOperationsRequest>';
			}
			$str .= '</ns:ExecuteCheckoutOperationsReq>';
			return $str;
	}
  
 
}
