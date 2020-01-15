<?php
class Booki_HistoryTmpl{
	public $orderList;
	public $currency;
	public $currencySymbol;
	public $orderId;
	public $singleOrderDetails;
	public $userName;
	public $hasFullControl;
	public $canEdit;
	public $refundResult;
	public function __construct(){
		if (!is_user_logged_in())
		{
			wp_safe_redirect(home_url('/'));
		}
		if(!isset($GLOBALS['hook_suffix'])){
			$GLOBALS['hook_suffix'] = '';
		}
		new Booki_RefundController(
			array($this, 'refunded')
		);
		new Booki_BookedDayController();
		new Booki_BookedOptionalController();
		new Booki_BookedCascadingItemController();
		new Booki_BookedQuantityElementController();
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		new Booki_UserOrderHistoryController(array($this, 'cancelAll'));
		
		$this->orderList = new Booki_UserOrderList();
		$this->orderId = isset($_GET['orderid']) ? $_GET['orderid'] : null;
		$this->singleOrderDetails = new Booki_OrderDetails($this->orderId);
		
		add_filter( 'booki_refund_result', array($this, 'getRefundResult'));
		add_filter( 'booki_single_order_details', array($this, 'getSingleOrderDetails'));
		add_filter( 'booki_booked_form_elements', array($this, 'getBookedFormElements'));
		
		$this->orderList->bind();
		
		$userInfo = Booki_Helper::getUserInfo();
		if($userInfo){
			$this->userName = $userInfo['name'];
			if(!$this->userName){
				$this->userName = $userInfo['email'];
			}
		}
	}
	
	function cancelAll($result){}
	function getSingleOrderDetails(){
		return $this->singleOrderDetails;
	}
	
	function getBookedFormElements(){
		return $this->singleOrderDetails->order->bookedFormElements;
	}
	
	function refunded($refundResult){
		$this->refundResult = $refundResult;
	}
	
	function getRefundResult(){
		return $this->refundResult;
	}
}
?>