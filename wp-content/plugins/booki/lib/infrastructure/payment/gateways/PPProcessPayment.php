<?php
class Booki_PPProcessPayment{
	private $paypalSettings;
	private $globalSettings;
	private $token;
	private $payerId;
	private $orderRepository;
	private $couponRepository;
	public function __construct($token = null, $payerId = null)
	{
		$paypalSettingRepo = new Booki_PaypalSettingRepository();
		$this->paypalSettings = $paypalSettingRepo->read();
		
		$this->globalSettings = BOOKIAPP()->globalSettings;
		
		$this->token = isset($_GET['token']) ? $_GET['token'] : $token;
		$this->payerId = isset($_GET['PayerID']) ? $_GET['PayerID'] : $payerId;
		$this->orderRepository = new Booki_OrderRepository();
		$this->couponRepository = new Booki_CouponRepository();
	}
	
	public function expressCheckout()
	{
		$getExpressCheckoutDetails = new Booki_PPGetExpressCheckoutDetails();
		$result = $getExpressCheckoutDetails->getDetails();
		
		if($result){
			return $this->doExpressCheckout((int)$result['orderId'], $result['couponCode'], $result['payerEmail'], $result['firstName'], $result['lastName']);
		}
		
		return false;
	}
	
	protected function doExpressCheckout($orderId, $couponCode, $payerEmail, $firstName, $lastName){
		$order = $this->orderRepository->read($orderId);
		if(!$order || $order->status === Booki_PaymentStatus::PAID){
			return false;
		}
		
		$bs = new Booki_BillSettlement(array('orderId'=>$orderId));
		$totalAmount = $bs->data->formattedTotalAmountIncludingTax;

		$paymentDetails = new PaymentDetailsType();
		$paymentDetails->OrderTotal = new BasicAmountType($this->paypalSettings->currency, $totalAmount);
		
		$doECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
		$doECRequestDetails->PayerID = $this->payerId;
		$doECRequestDetails->Token = $this->token;
		$doECRequestDetails->PaymentAction = 'Sale';
		$doECRequestDetails->PaymentDetails[0] = $paymentDetails;

		$doECRequest = new DoExpressCheckoutPaymentRequestType();
		$doECRequest->DoExpressCheckoutPaymentRequestDetails = $doECRequestDetails;


		$doECReq = new DoExpressCheckoutPaymentReq();
		$doECReq->DoExpressCheckoutPaymentRequest = $doECRequest;
		
		$paypalService = new PayPalAPIInterfaceServiceService();
		try {
			$doECResponse = @$paypalService->DoExpressCheckoutPayment($doECReq, new PPSignatureCredential(
				$this->paypalSettings->username
				, $this->paypalSettings->password
				, $this->paypalSettings->signature
			));
		} catch (Exception $ex) {
			Booki_EventsLogProvider::insert($ex);
			return false;
		}

		if(isset($doECResponse) && $doECResponse->Ack == 'Success') {
			if(isset($doECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo)) {
				if(!$order->userIsRegistered && $this->globalSettings->registerUserAfterPayment){
					$createUserResult = Booki_Helper::createUserIfNotExists($payerEmail, $firstName, $lastName);
					$order->userId = $createUserResult['userId'];
					$order->userIsRegistered = true;
				}
				$order->transactionId = $doECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
				$order->note = $doECResponse->DoExpressCheckoutPaymentResponseDetails->Note;
				$order->status = Booki_PaymentStatus::PAID;
				$order->totalAmount = $totalAmount;
				
				$order->paymentDate = new Booki_DateTime();
				
				if(isset($couponCode)){
					//invalidate coupon.
					$coupon = $this->couponRepository->find($couponCode);
					if($coupon){
						if($coupon->couponType === Booki_CouponType::REGULAR){
							$coupon->expire();
							$this->couponRepository->update($coupon);
						}
						//update discount field on order
						$order->discount = $coupon->discount;
					} else if(is_numeric($couponCode) && (double)$couponCode < 100){
						$order->discount = (double)$couponCode;
					}

				}
				$this->orderRepository->update($order);
				try{
					$notificationEmailer = new Booki_NotificationEmailer(array('emailType'=>Booki_EmailType::PAYMENT_RECEIVED, 'orderId'=>$order->id));
					$result = $notificationEmailer->send();
					
					if($this->globalSettings->autoApproveBooking || (!$this->globalSettings->autoApproveBooking && $this->globalSettings->autoApproveAfterBillingSettlement)){
						Booki_BookingProvider::approveOrderAndNotifyUser($order->id);
					} 
					if($this->globalSettings->autoNotifyAdminNewBooking){
						$notificationToUserInfo = Booki_Helper::getUserInfoByEmail($this->globalSettings->notificationEmailTo);
						$notificationEmailer = new Booki_NotificationEmailer(array(
							'emailType'=>Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_ADMIN
							, 'orderId'=>$order->id
							, 'userInfo'=>$notificationToUserInfo
						));
						$notificationEmailer->send();
						
						//notifies also agents if projects in booking have agents
						$notificationEmailer = new Booki_AgentsNotificationEmailer(array(
							'emailType'=>Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_AGENTS
							, 'orderId'=>$order->id
						));
						$notificationEmailer->send();
					}
						
				}catch(Exception $ex){
					Booki_EventsLogProvider::insert($ex);
				}
				return true;
			}
		}else{
			Booki_EventsLogProvider::insert($doECResponse);
		}
		return $doECResponse->Ack;
		//else serialize and log doECResponse
	}
}
?>