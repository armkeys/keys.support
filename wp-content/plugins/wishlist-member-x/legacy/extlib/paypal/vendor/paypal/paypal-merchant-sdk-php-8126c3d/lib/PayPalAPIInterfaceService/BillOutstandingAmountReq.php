<?php 
/**
 * 
 */
class BillOutstandingAmountReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BillOutstandingAmountRequestType 	 
	 */ 
	public $BillOutstandingAmountRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BillOutstandingAmountReq>';
			if(NULL != $this->BillOutstandingAmountRequest)
			{
		   		$str .= '<ns:BillOutstandingAmountRequest>';
				$str .= $this->BillOutstandingAmountRequest->toXMLString();
				$str .= '</ns:BillOutstandingAmountRequest>';
			}
			$str .= '</ns:BillOutstandingAmountReq>';
			return $str;
	}
  
 
}
