<?php
class Booki_StatsTmpl{
	public $ordersMadeAggregateList;
	public $ordersRefundAmountAggregateList;
	public $ordersTotalAmountAggregateList;
	public $singleOrderDetails;
	public $donut = array('0'=>0, '1'=>0, '2'=>0);
	public $summary;
	public $totalAmountEarned;
	public $localInfo;
	public $orderId;
	public $orderList = null;
	public $hasFullControl;
	public function __construct(){
		if(!isset($GLOBALS['hook_suffix'])){
			$GLOBALS['hook_suffix'] = '';
		}
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->localInfo = Booki_Helper::getLocaleInfo();
		$this->ordersMadeAggregateList = new Booki_OrdersMadeAggregateList();
		$this->ordersRefundAmountAggregateList = new Booki_OrdersRefundAmountAggregateList();
		$this->ordersTotalAmountAggregateList = new Booki_OrdersTotalAmountAggregateList();
		$this->orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
		$this->ordersMadeAggregateList->bind();
		$this->ordersRefundAmountAggregateList->bind();
		$this->ordersTotalAmountAggregateList->bind();
		$userId = null;
		if(!$this->hasFullControl){
			$userId = get_current_user_id();
		}
		$this->orderList = new Booki_EditorApprovedOrderList();
		new Booki_BookedDayController();
		new Booki_BookedOptionalController();
		new Booki_ManageBookingsController(
			null
			, null
			, null
			, null
			, array($this, 'invoiceNotification')
			, array($this, 'refundNotification')
			, null
			, null
			, null
			, $this->orderList->perPage
		);
		$this->orderList->bind();
		$this->singleOrderDetails = new Booki_OrderDetails($this->orderId);
		add_filter( 'booki_single_order_details', array($this, 'getSingleOrderDetails'));
		add_filter( 'booki_booked_form_elements', array($this, 'getBookedFormElements'));
		
		$repo = new Booki_StatsRepository();
		$donut = $repo->readOrdersByStatus($userId);
		
		foreach($donut as $d){
			$this->donut["$d->status"] = $d->count;
		}

		$this->summary = $repo->summary($userId);
		$this->totalAmountEarned = $repo->readTotalAmountEarned($userId);
	}
	public function invoiceNotification(){}
	public function refundNotification(){}
	function getSingleOrderDetails(){
		return $this->singleOrderDetails;
	}
	
	function getBookedFormElements(){
		$this->singleOrderDetails->order->bookedFormElements;
	}
}
?>