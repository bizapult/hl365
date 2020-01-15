<?php
class SetDataRequestType  
   extends PPXmlMessage{

	/**
	 * Details about Billing Agreements requested to be created. 
     * @array
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BillingApprovalDetailsType 	 
	 */ 
	public $BillingApprovalDetails;

	/**
	 * Only needed if Auto Authorization is requested. The
	 * authentication session token will be passed in here. 
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var BuyerDetailType 	 
	 */ 
	public $BuyerDetail;

	/**
	 * Requests for specific buyer information like Billing Address
	 * to be returned through GetExpressCheckoutDetails should be
	 * specified under this. 
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var InfoSharingDirectivesType 	 
	 */ 
	public $InfoSharingDirectives;


   
}