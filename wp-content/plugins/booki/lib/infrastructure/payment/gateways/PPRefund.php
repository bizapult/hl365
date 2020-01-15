<?php
/**
	@description Refunding through Paypal API
*/
class Booki_PPRefund{
	private $orderId;
	private $refundSource;
	private $amount = '';
	private $refundType = 'FULL';
	private $memo = '';
	private $retryUntil = '';
	private $bookedDayId = null;
	private $bookedOptionalId = null;
	private $bookedCascadingItemId = null;
	private $bookedQuantityElementId = null;
	private $orderRepository;
	private $paypalSettings;
	private $globalSettings;
	public function __construct($args)
	{	
		if(isset($args['orderId'])){
			$this->orderId = $args['orderId'];
		}
		if(isset($args['refundSource'])){
			$this->refundSource = $args['refundSource'];
		}
		if(isset($args['amount'])){
			$this->amount = $args['amount'];
		}
		if(isset($args['refundType'])){
			$this->refundType = $args['refundType'];
		}
		if(isset($args['memo'])){
			$this->memo = $args['memo'];
		}
		if(isset($args['retryUntil'])){
			$this->retryUntil = $args['retryUntil'];
		}
		if(isset($args['bookedDayId'])){
			$this->bookedDayId = $args['bookedDayId'];
		}
		if(isset($args['bookedOptionalId'])){
			$this->bookedOptionalId = $args['bookedOptionalId'];
		}
		if(isset($args['bookedCascadingItemId'])){
			$this->bookedCascadingItemId = $args['bookedCascadingItemId'];
		}
		if(isset($args['bookedQuantityElementId'])){
			$this->bookedQuantityElementId = $args['bookedQuantityElementId'];
		}
		$paypalSettingRepo = new Booki_PaypalSettingRepository();
		$this->paypalSettings = $paypalSettingRepo->read();
		$this->globalSettings = BOOKIAPP()->globalSettings;
		
		$this->orderRepository = new Booki_OrderRepository();
	}
	
	public function refundTransaction()
	{
		$refundReqest = new RefundTransactionRequestType();
		$order = $this->orderRepository->read($this->orderId);
		
		if(!$order){
			return false;
		}
		if($this->amount != '' && strtoupper($this->refundType) != 'FULL') {
			$refundReqest->Amount = new BasicAmountType($order->currency, $this->amount);
		}

		$refundReqest->RefundType = $this->refundType;
		$refundReqest->TransactionID = $order->transactionId;
		$refundReqest->RefundSource = $this->refundSource;
		$refundReqest->Memo = $this->memo;
		$refundReqest->RetryUntil = $this->retryUntil;
		
		$refundReq = new RefundTransactionReq();
		$refundReq->RefundTransactionRequest = $refundReqest;

		$paypalService = new PayPalAPIInterfaceServiceService();
		try {
			$refundResponse = @$paypalService->RefundTransaction($refundReq, new PPSignatureCredential(
				$this->paypalSettings->username
				, $this->paypalSettings->password
				, $this->paypalSettings->signature
			));
		} catch (Exception $ex) {
			Booki_EventsLogProvider::insert($ex);
			return false;
		}

		if(isset($refundResponse)) {
			if($refundResponse->Ack === 'Success'){
				$order->status = Booki_PaymentStatus::REFUNDED;
				$order->refundAmount += $this->amount;
				$notify = $this->globalSettings->autoRefundNotification;
				
				$bookedDaysRepo = new Booki_BookedDaysRepository();
				$bookedOptionalsRepo = new Booki_BookedOptionalsRepository();
				$bookedCascadingItemsRepo = new Booki_BookedCascadingItemsRepository();
				$bookedQuantityElementRepo = new Booki_BookedQuantityElementRepository();
				
				$emailType = Booki_EmailType::REFUNDED;
				if($this->bookedDayId !== null){
					$emailType = Booki_EmailType::BOOKING_DAY_REFUNDED;
					$bookedDaysRepo->updateStatus($this->bookedDayId, Booki_BookingStatus::REFUNDED);
				}else if ($this->bookedOptionalId !== null){
					$emailType = Booki_EmailType::BOOKING_OPTIONAL_ITEM_REFUNDED;
					$bookedOptionalsRepo->updateStatus($this->bookedOptionalId, Booki_BookingStatus::REFUNDED);
				}else if ($this->bookedCascadingItemId !== null){
					$emailType = Booki_EmailType::BOOKING_OPTIONAL_ITEM_REFUNDED;
					$bookedCascadingItemsRepo->updateStatus($this->bookedCascadingItemId, Booki_BookingStatus::REFUNDED);
				}else if ($this->bookedQuantityElementId !== null){
					$emailType = Booki_EmailType::BOOKING_QUANTITY_ELEMENT_REFUNDED;
					$bookedCascadingItemsRepo->updateStatus($this->bookedQuantityElementId, Booki_BookingStatus::REFUNDED);
				}else{
					$bookedDaysRepo->updateStatusByOrderId($this->orderId, Booki_BookingStatus::REFUNDED);
					$bookedOptionalsRepo->updateStatusByOrderId($this->orderId, Booki_BookingStatus::REFUNDED);
					$bookedCascadingItemsRepo->updateStatusByOrderId($this->orderId, Booki_BookingStatus::REFUNDED);
				}
				
				if($emailType !== Booki_EmailType::REFUNDED){
					$order->status = Booki_PaymentStatus::PARTIALLY_REFUNDED;
					//partial refunds always show notification, even if notification is turned off globally.
					//we currently have no means of manually sending notifications in case of partial refunds.
					//might add this in the future.
					$notify = true;
				}
				
				if($notify){
					$notificationEmailer = new Booki_NotificationEmailer(array(
						'emailType'=>$emailType
						, 'orderId'=>$this->orderId
						, 'bookedDayId'=>$this->bookedDayId
						, 'bookedOptionalId'=>$this->bookedOptionalId
						, 'bookedCascadingItemId'=>$this->bookedCascadingItemId
						, 'refundAmount'=>$this->amount
					));
					$result = $notificationEmailer->send();
					if($result){
						++$order->refundNotification;
					}
				}
				$this->orderRepository->update($order);
			} else{
				Booki_EventsLogProvider::insert($refundResponse);
			}
			return $refundResponse;
		}
		return false;
	}
	
}
?>