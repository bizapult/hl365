<?php
class ReverseTransactionReq  
   extends PPXmlMessage{

	/**
	 * 
	 * @access public
	 
	 * @namespace ns
	 
	 	 	 	 
	 * @var ReverseTransactionRequestType 	 
	 */ 
	public $ReverseTransactionRequest;


	public function toXMLString()
	{
		    $str = '';
			$str .= '<ns:ReverseTransactionReq>';
			if($this->ReverseTransactionRequest != NULL)
			{
		   		$str .= '<ns:ReverseTransactionRequest>';
				$str .= $this->ReverseTransactionRequest->toXMLString();
				$str .= '</ns:ReverseTransactionRequest>';
			}
			$str .= '</ns:ReverseTransactionReq>';
			return $str;
	}
   
}