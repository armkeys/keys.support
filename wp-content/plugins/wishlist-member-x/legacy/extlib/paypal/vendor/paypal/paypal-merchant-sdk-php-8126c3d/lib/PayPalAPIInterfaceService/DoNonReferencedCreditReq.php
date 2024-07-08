<?php 
/**
 * 
 */
class DoNonReferencedCreditReq  
   extends PPXmlMessage{

	/**
	 * 
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var DoNonReferencedCreditRequestType 	 
	 */ 
	public $DoNonReferencedCreditRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:DoNonReferencedCreditReq>';
			if(NULL != $this->DoNonReferencedCreditRequest)
			{
		   		$str .= '<ns:DoNonReferencedCreditRequest>';
				$str .= $this->DoNonReferencedCreditRequest->toXMLString();
				$str .= '</ns:DoNonReferencedCreditRequest>';
			}
			$str .= '</ns:DoNonReferencedCreditReq>';
			return $str;
	}
  
 
}
