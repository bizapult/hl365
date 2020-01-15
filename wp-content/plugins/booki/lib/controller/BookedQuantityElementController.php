<?php
class Booki_BookedQuantityElementController extends Booki_BaseController{
	private $bookedQuantityElementRepo;
	private $bookedQuantityElement;
	private $refundAmount;
	private $refundCurrency;
	private $refundOrderId;
	
	public function __construct($approveCallback = null, $cancelCallback = null, $refundCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookedday')){
			return;
		}
		
		$this->bookedQuantityElementRepo = new Booki_BookedQuantityElementRepository();
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		if(array_key_exists('approve_quantity', $_POST)){
			$this->approve($approveCallback);
		}else if(array_key_exists('cancel_quantity', $_POST)){
			$this->cancel($cancelCallback);
		}else if (array_key_exists('refund_quantity', $_POST)){
			$this->refund($refundCallback);
		}
	}
	
	public function approve($callback){
		$id = (int)$this->getPostValue('approve_quantity');
		$bookedQuantityElement = $this->bookedQuantityElementRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($bookedQuantityElement->projectId);
		if(!$canEdit){
			return;
		}
		$bookedQuantityElement->status = Booki_BookingStatus::APPROVED;
		$result = $this->bookedQuantityElementRepo->update($bookedQuantityElement);
		if($result){
		
			$userId = get_current_user_id();
			$this->bookedQuantityElementRepo->setOwner($bookedQuantityElement->id, $userId);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_QUANTITY_ELEMENT_CONFIRMED
				, 'orderId'=>$bookedQuantityElement->orderId
				, 'bookedQuantityElementId'=>$bookedQuantityElement->id
			));
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function cancel($callback){
		$id = (int)$this->getPostValue('cancel_quantity');
		$orderId = (int)$this->getPostValue('orderid');
		$bookedQuantityElement = $this->bookedQuantityElementRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($bookedQuantityElement->projectId);
		if($canEdit){
			if($bookedQuantityElement->cost > 0){
				$orderRepository = new Booki_OrderRepository();
				$order = $orderRepository->read($orderId);
				$cost = Booki_Helper::calcDeposit($bookedQuantityElement->deposit, $bookedQuantityElement->cost);
				if($order->discount > 0){
					$cost = Booki_Helper::calcDiscount($order->discount, $cost);
				}
				if($order->tax > 0){
					$cost += Booki_Helper::percentage($order->tax, $cost);
				}
				$order->totalAmount -= $cost;
				$orderRepository->update($order);
			}
			$this->bookedQuantityElementRepo->delete($id);
			Booki_GCalHelper::updateByOrder($orderId);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_QUANTITY_ELEMENT_CANCELLED
				, 'orderId'=>$orderId
				, 'bookedQuantityElementId'=>$id
			));
			$notificationEmailer->send();
		}else{
			$this->bookedQuantityElementRepo->updateStatus($id, Booki_BookingStatus::USER_REQUEST_CANCEL);
			$notificationEmailer = new Booki_OrderCancelNotificationEmailer(array('orderId'=>$orderId, 'bookedQuantityElementId'=>$id));
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array(true));
	}
	
	public function refund($callback){
		$id = (int)$this->getPostValue('refund_quantity');
		$this->refundOrderId = (int)$this->getPostValue('orderid');
		$this->bookedQuantityElement = $this->bookedQuantityElementRepo->read($id);
		$this->refundAmount = $this->bookedQuantityElement->getCalculatedCost();
		$this->refundCurrency = (string)$this->getPostValue('currency');
		
		$canEdit = Booki_PermissionHelper::hasEditorPermission($this->bookedQuantityElement->projectId);
		if(!$canEdit){
			return;
		}
		
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_booked_quantityElement', array($this, 'getBookedQuantityElement'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array());
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	public function getBookedQuantityElement(){
		return $this->bookedQuantityElement;
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