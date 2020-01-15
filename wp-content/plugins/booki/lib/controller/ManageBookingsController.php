<?php
class Booki_ManageBookingsController extends Booki_BaseController{
	private $orderId;
	private $exportsPerPage;
	private $hasFullControl;
	private $canEdit;
	private $globalSettings;
	public $refundOrderId;
	public $refundAmount;
	public $refundCurrency;
	public $refundType;
	
	public function __construct($refundCallback, $deleteCallback, $addUserCallback, $registerUserCallback, $invoiceNotificationCallback, 
									$refundNotificationCallback, $approveAllCallback, $markPaidCallback, $exportCallback, $exportsPerPage ){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookings')){
			return;
		}
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->exportsPerPage = $exportsPerPage;
		$this->orderId = isset($_GET['orderid']) ? $_GET['orderid'] : null;
		if (array_key_exists('refund', $_POST)){
			$this->refund($refundCallback);
		}else if(array_key_exists('invoiceNotification', $_POST)){
			$this->invoiceNotification($invoiceNotificationCallback);
		}else if (array_key_exists('refundNotification', $_POST)){
			$this->refundNotification($refundNotificationCallback);
		}else if (array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if (array_key_exists('adduser', $_POST)){
			$this->addUser($addUserCallback);
		}else if (array_key_exists('export', $_POST)){
			$this->export($exportCallback);
		}else if (array_key_exists('approveAll', $_POST)){
			$this->approveAll($approveAllCallback);
		}else if (array_key_exists('markPaid', $_POST)){
			$this->markPaid($markPaidCallback);
		}else if (array_key_exists('registerUser', $_POST)){	
			$this->registerUser($registerUserCallback);
		}
	}
	
	public function registerUser($callback){
		$orderId = (int)$_POST['registerUser'];
		$firstName = (string)$_POST['userFirstname'];
		$lastName = (string)$_POST['userLastname'];
		$email = (string)$_POST['userEmail'];
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		$isNew = false;
		if($order){
			$createUserResult = Booki_Helper::createUserIfNotExists($email, $firstName, $lastName);
			$order->userId = $createUserResult['userId'];
			$order->userIsRegistered = true;
			$result = Booki_BookingProvider::orderRepository()->update($order);
			$isNew = $createUserResult['isNew'];
		}
		$this->executeCallback($callback, array($isNew));
	}
	
	public function approveAll($callback){
		$orderId = (int)$_POST['approveAll'];
		$userIsRegistered = (bool)$_POST['userIsRegistered'];
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		Booki_BookingProvider::approveOrderAndNotifyUser($orderId);
		Booki_GCalHelper::updateByOrder($orderId);
	}
	
	public function markPaid($callback){
		$orderId = (int)$_POST['markPaid'];
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		Booki_BookingProvider::orderRepository()->updateStatusByOrderId($orderId, Booki_PaymentStatus::PAID);
		$notificationEmailer = new Booki_NotificationEmailer(array('emailType'=>Booki_EmailType::PAYMENT_RECEIVED, 'orderId'=>$orderId));
		$result = $notificationEmailer->send();
		
		if($this->globalSettings->autoApproveBooking || (!$this->globalSettings->autoApproveBooking && $this->globalSettings->autoApproveAfterBillingSettlement)){
			Booki_BookingProvider::approveOrderAndNotifyUser($orderId);
			Booki_GCalHelper::updateByOrder($orderId);
		}
	}
	
	public function invoiceNotification($callback){
		$orderId = $_POST['invoiceNotification'];
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		$notificationEmailer = new Booki_NotificationEmailer(array('emailType'=>Booki_EmailType::INVOICE, 'orderId'=>$orderId));
		$result = $notificationEmailer->send();
		if($result){
			++$order->invoiceNotification;
			Booki_BookingProvider::orderRepository()->update($order);
		}
		$this->executeCallback($callback, array($orderId, $result));
	}
	
	
	public function refundNotification($callback){
		$orderId = $_POST['refundNotification'];
		$refundAmount = $_POST['refundAmount'];
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		$notificationEmailer = new Booki_NotificationEmailer(array(
			'emailType'=>Booki_EmailType::REFUNDED
			, 'orderId'=>$orderId
			, 'refundAmount'=>$refundAmount
		));
		$result = $notificationEmailer->send();
		if($result){
			++$order->refundNotification;
			Booki_BookingProvider::orderRepository()->update($order);
		}
		$this->executeCallback($callback, array($orderId, $result));
	}
	
	public function delete($callback){
		$orderId = (int)$_POST['delete'];
		$order = Booki_BookingProvider::read($orderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		$trashRepository = new Booki_TrashRepository();
		$trashRepository->insert(new Booki_Trash(array(
			'data'=>$order
			, 'deletionDate'=>new DateTime()
			, 'orderId'=>$orderId
		)));

		if($order->hasPendingCancellation){
			$notificationEmailer = new Booki_NotificationEmailer(array('emailType'=>Booki_EmailType::ORDER_CANCELLED, 'orderId'=>$orderId));
			$notificationEmailer->send();
		}
		Booki_GCalHelper::deleteByOrder($orderId);
		Booki_BookingProvider::delete($orderId);
		$this->executeCallback($callback, array($orderId));
	}
	
	public function addUser($callback){
		$orderId = $this->getPostValue('adduser');
		$userEmail = $this->getPostValue('adduseremail');
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		$isNew = false;
		if($order){
			$createUserResult = Booki_Helper::createUserIfNotExists($userEmail);
			$order->userId = $createUserResult['userId'];
			$order->userIsRegistered = true;
			$result = Booki_BookingProvider::orderRepository()->update($order);
			$isNew = $createUserResult['isNew'];
		}
		$this->executeCallback($callback, array($isNew));
	}
	
	public function export($callback){
		$pageIndex = (int)$_POST['pageindex'];
		$result = Booki_BookingProvider::orderRepository()->readAll($pageIndex, $this->exportsPerPage);
		$this->executeCallback($callback, array($pageIndex));
	}
	
	public function refund($callback){
		$this->refundOrderId = (int)$this->getPostValue('orderId');
		$this->refundAmount = $this->getPostValue('amount');
		$this->refundCurrency = $this->getPostValue('currency');
		$this->refundType = $this->getPostValue('refundType');
		
		$order = Booki_BookingProvider::orderRepository()->read($this->refundOrderId);
		$canEdit = Booki_PermissionHelper::hasOrderPermission($order);
		if(!$canEdit){
			return;
		}
		//uncomment if statement to enable paypal refunds.
		//if(!$order->transactionId && !$order->token){
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::REFUNDED
				, 'orderId'=>$order->id
				, 'refundAmount'=>$order->totalAmount
			));
			$result = $notificationEmailer->send();
			$order->refundAmount = $order->totalAmount;
			$order->status = Booki_PaymentStatus::REFUNDED;
			Booki_BookingProvider::orderRepository()->update($order);
			return;
		//}
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array($this->refundOrderId, $this->refundAmount, $this->refundCurrency, $this->refundType));
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	public function getRefundAmount(){
		return $this->refundAmount;
	}

	public function getRefundCurrency(){
		return $this->refundCurrency;
	}
	
	public function getRefundType(){
		return $this->refundType;
	}
}
?>