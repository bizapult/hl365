<?php
class Booki_BookedOptionalController extends Booki_BaseController{
	private $bookedOptionalsRepo;
	private $bookedOptional;
	private $refundAmount;
	private $refundCurrency;
	private $refundOrderId;
	
	public function __construct($approveCallback = null, $cancelCallback = null, $refundCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookedoptionals')){
			return;
		}
		
		$this->bookedOptionalsRepo = new Booki_BookedOptionalsRepository();
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
		$bookedOptional = $this->bookedOptionalsRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($bookedOptional->projectId);
		if(!$canEdit){
			return;
		}
		$bookedOptional->status = Booki_BookingStatus::APPROVED;
		$result = $this->bookedOptionalsRepo->update($bookedOptional);
		if($result){
		
			$userId = get_current_user_id();
			$this->bookedOptionalsRepo->setOwner($bookedOptional->id, $userId);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_OPTIONAL_ITEM_CONFIRMED
				, 'orderId'=>$bookedOptional->orderId
				, 'bookedOptionalId'=>$bookedOptional->id
			));
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function cancel($callback){
		$id = (int)$this->getPostValue('cancel');
		$orderId = (int)$this->getPostValue('orderid');
		$optionalItem = $this->bookedOptionalsRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($optionalItem->projectId);
		if($canEdit){
			if($optionalItem->cost > 0){
				$orderRepository = new Booki_OrderRepository();
				$order = $orderRepository->read($orderId);
				$cost = Booki_Helper::calcDeposit($optionalItem->deposit, $optionalItem->cost);
				if($order->discount > 0){
					$cost = Booki_Helper::calcDiscount($order->discount, $cost);
				}
				if($order->tax > 0){
					$cost += Booki_Helper::percentage($order->tax, $cost);
				}
				$order->totalAmount -= $cost;
				$orderRepository->update($order);
			}
			$this->bookedOptionalsRepo->delete($id);
			Booki_GCalHelper::updateByOrder($orderId);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_OPTIONAL_ITEM_CANCELLED
				, 'orderId'=>$orderId
				, 'bookedOptionalId'=>$id
			));
			$notificationEmailer->send();
		}else{
			$this->bookedOptionalsRepo->updateStatus($id, Booki_BookingStatus::USER_REQUEST_CANCEL);
			$notificationEmailer = new Booki_OrderCancelNotificationEmailer(array('orderId'=>$orderId, 'bookedOptionalId'=>$id));
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array(true));
	}
	
	public function refund($callback){
		$id = (int)$this->getPostValue('refund');
		$this->refundOrderId = (int)$this->getPostValue('orderid');	
		$this->refundCurrency = (string)$this->getPostValue('currency');
		$this->bookedOptional = $this->bookedOptionalsRepo->read($id);
		$this->refundAmount = $this->bookedOptional->getCalculatedCost();
		$canEdit = Booki_PermissionHelper::hasEditorPermission($this->bookedOptional->projectId);
		if(!$canEdit){
			return;
		}
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_booked_optional', array($this, 'getBookedOptional'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array());
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	public function getBookedOptional(){
		return $this->bookedOptional;
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