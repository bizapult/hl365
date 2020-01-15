<?php
class Booki_BookedDayController extends Booki_BaseController{
	private $bookedDaysRepo;
	private $bookedDay;
	private $refundAmount;
	private $refundCurrency;
	private $refundOrderId;
	private $hasFullControl;
	private $canEdit;
	public function __construct($approveCallback = null, $cancelCallback = null, $refundCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookedday')){
			return;
		}

		$this->bookedDaysRepo = new Booki_BookedDaysRepository();
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		
		if(array_key_exists('approve', $_POST)){
			$this->approve($approveCallback);
		}else if(array_key_exists('cancel', $_POST)){
			$this->cancel($cancelCallback);
		}else if (array_key_exists('refund', $_POST)){
			$this->refund($refundCallback);
		}
	}
	
	public function approve($callback){
		$id = (int)$this->getPostValue('approve');
		$orderId = (int)$this->getPostValue('orderid');
		$bookedDay = $this->bookedDaysRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($bookedDay->projectId);
		if(!$canEdit){
			return;
		}
		$bookedDay->status = Booki_BookingStatus::APPROVED;
		$result = $this->bookedDaysRepo->update($bookedDay);
		if($result){
			//editor approving a day becomes the owner
			$userId = get_current_user_id();
			$this->bookedDaysRepo->setOwner($bookedDay->id, $userId);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_DAY_CONFIRMED
				, 'orderId'=>$bookedDay->orderId
				, 'bookedDayId'=>$bookedDay->id
			));
			$notificationEmailer->send();
			Booki_GCalHelper::updateByBookedDay($bookedDay->id, $bookedDay->projectId);
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function cancel($callback){
		$id = (int)$this->getPostValue('cancel');
		$orderId = (int)$this->getPostValue('orderid');
		$orderRepository = new Booki_OrderRepository();
		$bookedDay = $this->bookedDaysRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($bookedDay->projectId);
		if($canEdit){
			Booki_GCalHelper::deleteByBookedDay($id, $bookedDay->projectId);
			Booki_BookingHelper::deleteBookedDay($orderId, $bookedDay);
		}else{
			$this->bookedDaysRepo->updateStatus($id, Booki_BookingStatus::USER_REQUEST_CANCEL);
			$notificationEmailer = new Booki_OrderCancelNotificationEmailer(array(
				'orderId'=>$orderId
				, 'bookedDayId'=>$id
			));
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array(true));
	}
	
	public function refund($callback){
		$id = (int)$this->getPostValue('refund');
		$this->refundOrderId = (int)$this->getPostValue('orderid');
		$this->refundCurrency = (string)$this->getPostValue('currency');
		$this->bookedDay = $this->bookedDaysRepo->read($id);
		$this->refundAmount = $this->bookedDay->cost;
		$canEdit = Booki_PermissionHelper::hasEditorPermission($this->bookedDay->projectId);
		if(!$canEdit){
			return;
		}
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_booked_day', array($this, 'getBookedDay'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array());
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	
	public function getBookedDay(){
		return $this->bookedDay;
	}
	public function getRefundAmount(){
		return $this->refundAmount;
	}
	
	public function getRefundCurrency(){
		return $this->refundCurrency;
	}
	
	public function getRefundType(){
		return 'Partial';
	}
}
?>