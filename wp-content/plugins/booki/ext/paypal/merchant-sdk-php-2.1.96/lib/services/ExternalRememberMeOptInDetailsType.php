<?php
class ExternalRememberMeOptInDetailsType  
   extends PPXmlMessage{

	/**
	 * 1 = opt in to external remember me. 0 or omitted = no opt-in
	 * Other values are invalid 
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ExternalRememberMeOptIn;

	/**
	 * E-mail address or secure merchant account ID of merchant to
	 * associate with new external remember-me. Currently, the
	 * owner must be either the API actor or omitted/none. In the
	 * future, we may allow the owner to be a 3rd party merchant
	 * account. 
	 * @access public
	 
	 * @namespace ebl
	 
	 	 	 	 
	 * @var ExternalRememberMeOwnerDetailsType 	 
	 */ 
	public $ExternalRememberMeOwnerDetails;


   
}