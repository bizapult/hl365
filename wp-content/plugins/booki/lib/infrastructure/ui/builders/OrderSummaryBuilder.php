<?php
class Booki_OrderSummaryBuilder{
	private $cart;
	private $localeInfo;
	public $result;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs === 1){
			$this->cart = func_get_arg(0);
		}else{
			$this->cart = new Booki_Cart();
		}
		$bookings = $this->cart->getBookings();
		$this->result = new Booki_OrderSummary(array('bookings'=>$bookings));
	}
}
?>