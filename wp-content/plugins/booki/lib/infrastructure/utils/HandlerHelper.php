<?php
class Booki_HandlerHelper{
	public $usersCsvHandlerUrl;
	public $ordersCsvHandlerUrl;
	public $bookingsCsvHandlerUrl;
	public $couponsCsvHandlerUrl;
	public $invoiceHandlerUrl;
	public function __construct(){
		$this->usersCsvHandlerUrl = admin_url() . 'admin.php?page=booki/index.php&booki_handler=userscsvgen';
		$this->ordersCsvHandlerUrl  = admin_url() . 'admin.php?page=booki/index.php&booki_handler=orderscsvgen';
		$this->bookingsCsvHandlerUrl  = admin_url() . 'admin.php?page=booki/index.php&booki_handler=bookingscsvgen';
		$this->couponsCsvHandlerUrl = admin_url() . 'admin.php?page=booki/index.php&booki_handler=couponscsvgen';
		$this->invoiceHandlerUrl = home_url('/') . '?booki_handler=invoicegen';
	}
}
?>