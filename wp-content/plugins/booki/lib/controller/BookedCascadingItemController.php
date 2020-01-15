<?php
class Booki_BookedCascadingItemController extends Booki_BaseController{
	private $bookedOptionalsRepo;
	private $bookedCascadingItem;
	private $refundAmount;
	private $refundCurrency;
	private $refundOrderId;
	
	public function __construct($approveCallback = null, $cancelCallback = null, $refundCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookedcascadingitems')){
			return;
		}
		
		$this->bookedCascadingItemsRepo = new Booki_BookedCascadingItemsRepository();
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
		$bookedCascadingItem = $this->bookedCascadingItemsRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($bookedCascadingItem->projectId);
		if(!$canEdit){
			return;
		}
		$bookedCascadingItem->status = Booki_BookingStatus::APPROVED;
		$result = $this->bookedCascadingItemsRepo->update($bookedCascadingItem);
		if($result){
		
			$userId = get_current_user_id();
			$this->bookedCascadingItemsRepo->setOwner($bookedCascadingItem->id, $userId);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_OPTIONAL_ITEM_CONFIRMED
				, 'orderId'=>$bookedCascadingItem->orderId
				, 'bookedCascadingItemId'=>$bookedCascadingItem->id
			));
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function cancel($callback){
		$id = (int)$this->getPostValue('cancel');
		$orderId = (int)$this->getPostValue('orderid');
		$bookedCascadingItem = $this->bookedCascadingItemsRepo->read($id);
		$canEdit = Booki_PermissionHelper::hasEditorPermission($bookedCascadingItem->projectId);
		if($canEdit){
			if($bookedCascadingItem->cost > 0){
				$orderRepository = new Booki_OrderRepository();
				$order = $orderRepository->read($orderId);
				$cost = Booki_Helper::calcDeposit($bookedCascadingItem->deposit, $bookedCascadingItem->cost);
				if($order->discount > 0){
					$cost = Booki_Helper::calcDiscount($order->discount, $cost);
				}
				if($order->tax > 0){
					$cost += Booki_Helper::percentage($order->tax, $cost);
				}
				$order->totalAmount -= $cost;
				$orderRepository->update($order);
			}
			$this->bookedCascadingItemsRepo->delete($id);
			Booki_GCalHelper::updateByOrder($orderId);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_OPTIONAL_ITEM_CANCELLED
				, 'orderId'=>$orderId
				, 'bookedCascadingItemId'=>$id
			));
			$notificationEmailer->send();
		}else{
			$this->bookedCascadingItemsRepo->updateStatus($id, Booki_BookingStatus::USER_REQUEST_CANCEL);
			$notificationEmailer = new Booki_OrderCancelNotificationEmailer(array(
				'orderId'=>$orderId
				, 'bookedCascadingItemId'=>$id
			));
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array(true));
	}
	
	public function refund($callback){
		$id = (int)$this->getPostValue('refund');
		$this->refundOrderId = (int)$this->getPostValue('orderid');
		$this->refundCurrency = (string)$this->getPostValue('currency');
		$this->bookedCascadingItem = $this->bookedCascadingItemsRepo->read($id);
		$this->refundAmount = $this->bookedCascadingItem->getCalculatedCost();
		$canEdit = Booki_PermissionHelper::hasEditorPermission($this->bookedCascadingItem->projectId);
		if(!$canEdit){
			return;
		}
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_booked_cascading_item', array($this, 'getBookedCascadingItem'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array());
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	public function getBookedCascadingItem(){
		return $this->bookedCascadingItem;
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