<?php 
/**
 * 
 */
class AddressVerifyReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var AddressVerifyRequestType 	 
	 */ 
	public $AddressVerifyRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:AddressVerifyReq>';
			if(NULL != $this->AddressVerifyRequest)
			{
		   		$str .= '<ns:AddressVerifyRequest>';
				$str .= $this->AddressVerifyRequest->toXMLString();
				$str .= '</ns:AddressVerifyRequest>';
			}
			$str .= '</ns:AddressVerifyReq>';
			return $str;
	}
  
 
}
