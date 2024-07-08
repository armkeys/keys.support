<?php 
/**
 * 
 */
class BillAgreementUpdateReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var BAUpdateRequestType 	 
	 */ 
	public $BAUpdateRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:BillAgreementUpdateReq>';
			if(NULL != $this->BAUpdateRequest)
			{
		   		$str .= '<ns:BAUpdateRequest>';
				$str .= $this->BAUpdateRequest->toXMLString();
				$str .= '</ns:BAUpdateRequest>';
			}
			$str .= '</ns:BillAgreementUpdateReq>';
			return $str;
	}
  
 
}
