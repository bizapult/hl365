<?php
class Booki_BillSettlementTmpl{
	public $data;
	public $globalSettings;
	public $couponErrorMessage;
	public $checkoutSuccessMessage;
	public $enableCoupons;
	public $editable = false;
	public $enablePayPalBilling;
	public $showFooter = true;
	public $coupon = null;
	public $billSettlement = true;
	public function __construct(){
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->enablePayPalBilling = $this->globalSettings->enablePayPalBilling;
		$this->enableCoupons = $this->globalSettings->enableCoupons;
		
		new Booki_BillSettlementController(array($this, 'couponCallback'), array($this, 'checkoutCallback'));
		
		$orderId = null;
		if(isset($_GET['orderid'])){
			$orderId = (int)$_GET['orderid'];
		}

		$bs = new Booki_BillSettlement(array('orderId'=>$orderId, 'coupon'=>$this->coupon));
		$this->data = $bs->data;
		if(!$this->data->hasBookings){
			$this->showFooter = false;
		}
		add_filter( 'booki_cart_items', array($this, 'getData'));
	}
	
	public function getData(){
		return $this;
	}
	
	public function couponCallback($coupon, $errorMessage){
		$this->coupon = $coupon;
		$this->couponErrorMessage = $errorMessage;
	}
	
	public function checkoutCallback($coupon, $errorMessage){
		$this->coupon = $coupon;
		$this->checkoutSuccessMessage = $errorMessage;
	}
}
?>