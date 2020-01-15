<?php
class Booki_PaypalCancelPaymentTmpl{
	public $success;
	public $globalSettings;
	public function __construct(){
		$this->globalSettings = BOOKIAPP()->globalSettings;
		new Booki_PaypalCancelPaymentController(array($this, 'onCancel'));
	}

	public function onCancel($result){
		$this->success = true;
	}
}
?>