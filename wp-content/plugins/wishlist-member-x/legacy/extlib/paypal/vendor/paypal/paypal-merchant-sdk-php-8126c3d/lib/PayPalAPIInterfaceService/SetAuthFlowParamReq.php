<?php 
/**
 * 
 */
class SetAuthFlowParamReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var SetAuthFlowParamRequestType 	 
	 */ 
	public $SetAuthFlowParamRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:SetAuthFlowParamReq>';
			if(NULL != $this->SetAuthFlowParamRequest)
			{
		   		$str .= '<ns:SetAuthFlowParamRequest>';
				$str .= $this->SetAuthFlowParamRequest->toXMLString();
				$str .= '</ns:SetAuthFlowParamRequest>';
			}
			$str .= '</ns:SetAuthFlowParamReq>';
			return $str;
	}
  
 
}
