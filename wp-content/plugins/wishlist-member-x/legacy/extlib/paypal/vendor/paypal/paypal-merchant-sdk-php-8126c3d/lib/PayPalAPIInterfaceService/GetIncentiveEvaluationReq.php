<?php 
/**
 * 
 */
class GetIncentiveEvaluationReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var GetIncentiveEvaluationRequestType 	 
	 */ 
	public $GetIncentiveEvaluationRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:GetIncentiveEvaluationReq>';
			if(NULL != $this->GetIncentiveEvaluationRequest)
			{
		   		$str .= '<ns:GetIncentiveEvaluationRequest>';
				$str .= $this->GetIncentiveEvaluationRequest->toXMLString();
				$str .= '</ns:GetIncentiveEvaluationRequest>';
			}
			$str .= '</ns:GetIncentiveEvaluationReq>';
			return $str;
	}
  
 
}
