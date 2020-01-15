<?php
class BasicAmountType  
   extends PPXmlMessage{

	/**
	 * 
	 * @access public
	 
	 * @namespace cc
	 
	 
	 * @attribute 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $currencyID;

	/**
	 * 
	 * @access public
	 
	 * @namespace cc
	 
	 
	 * @value
	 	 	 	 
	 * @var string 	 
	 */ 
	public $value;

	/**
	 * Constructor with arguments
	 */
	public function __construct($currencyID = NULL, $value = NULL) {
		$this->currencyID = $currencyID;
		$this->value = $value;
	}


   
}