<?php
class PaymentRequestInfoType  
   extends PPXmlMessage{

	/**
	 * Contains the transaction id of the bucket.  
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $TransactionId;

	/**
	 * Contains the bucket id.  
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $PaymentRequestID;

	/**
	 * Contains the error details.  
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ErrorType 	 
	 */ 
	public $PaymentError;


}