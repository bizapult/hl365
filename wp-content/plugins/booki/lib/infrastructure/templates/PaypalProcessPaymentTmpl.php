<?php
class Booki_PaypalProcessPaymentTmpl{
	public $data;
	public $globalSettings;
	public $editable = false;
	public $confirmCheckout = true;
	public $checkoutFailure = '';
	public $paymentSuccess = false;
	public $enableCoupons;
	public $showFooter = true;
	public $billSettlement = false;
	private $paymentInProgress = false;
	private $orderId = null;
	public function __construct(){
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->enableCoupons = $this->globalSettings->enableCoupons;
		new Booki_PaypalProcessPaymentController(
			$this->globalSettings->autoConfirmOrderAfterPayment
			, array($this, 'fillData')
			, array($this, 'processPayment')
		);
	}
	
	public function getData(){
		return $this;
	}
	
	public function fillData(){
	
		$getExpressCheckoutDetails = new Booki_PPGetExpressCheckoutDetails();
		$result = $getExpressCheckoutDetails->getDetails();
		$this->orderId = $result['orderId'];
		$couponCode = $result['couponCode'];
		
		$bs = new Booki_BillSettlement(array('orderId'=>$this->orderId));
		$this->data = $bs->data;
		
		add_filter( 'booki_cart_items', array($this, 'getData'));
	}
	
	public function getPaymentProgress(){
		return array(
			'paymentInProgress'=>$this->paymentInProgress
			, 'paymentSuccess'=>$this->paymentSuccess
			, 'checkoutFailure'=>$this->checkoutFailure
			, 'orderId'=>$this->orderId
		);
	}
	
	public function processPayment($result){
		if($result === true){
			$this->paymentSuccess = true;
		}else if ($result !== false){
			$this->checkoutFailure = $result;
		}
		$this->showFooter = false;
		$this->enableCoupons = false;
		$this->fillData();
		
		$this->paymentInProgress = true;
		add_filter('booki_payment_progress', array($this, 'getPaymentProgress'));
	}

}
?>