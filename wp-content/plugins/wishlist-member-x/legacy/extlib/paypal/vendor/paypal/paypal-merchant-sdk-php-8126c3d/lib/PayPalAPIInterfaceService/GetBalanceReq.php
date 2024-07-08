<?php 
/**
 * 
 */
class GetBalanceReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetBalanceRequestType 	 
	 */ 
	public $GetBalanceRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetBalanceReq>';
			if(NULL != $this->GetBalanceRequest)
			{
		   		$str .= '<ns:GetBalanceRequest>';
				$str .= $this->GetBalanceRequest->toXMLString();
				$str .= '</ns:GetBalanceRequest>';
			}
			$str .= '</ns:GetBalanceReq>';
			return $str;
	}
  
 
}
