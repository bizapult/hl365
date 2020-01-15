<?php
class Booki_OrderLogger{
	private $globalSettings;
	public $orderSummary;
	public $bookings;
	public $order;
	public $userId;
	public $coupon;
	private $userIsRegistered = true;
	private $payingNow = false;
	public function __construct(Booki_Bookings $bookings = null, $userId = null, $coupon = null){

		$this->globalSettings = BOOKIAPP()->globalSettings;
		
		if(!$bookings){
			$cart = new Booki_Cart();
			$bookings = $cart->getBookings();
		}
		
		$this->coupon = $coupon ? $coupon : $bookings->coupon;
		$this->orderSummary = new Booki_OrderSummary(array('bookings'=>$bookings));
		$this->bookings = $this->orderSummary->bookings;
		if($userId !== null){
			$this->userId = $userId;
		}else if($this->globalSettings->membershipRequired && is_user_logged_in()){
			$this->userId = get_current_user_id();
		} else {
			//by default admin is owner
			$this->userId = $this->globalSettings->adminUserId;
			$this->userIsRegistered = false;
		}
		
		$this->init();
	}
	
	protected function init(){

		$this->order = new Booki_Order(array(
			'orderDate'=>new Booki_DateTime()
			, 'userId'=>$this->userId
			, 'status'=>Booki_PaymentStatus::UNPAID
			, 'totalAmount'=>$this->orderSummary->totalAmount
			, 'isRegistered'=>$this->userIsRegistered
			, 'timezone'=>$this->orderSummary->timezoneString
			, 'tax'=>$this->globalSettings->tax
		));

		$status = (!$this->globalSettings->enablePayments && $this->globalSettings->autoApproveBooking)
						? Booki_BookingStatus::APPROVED : Booki_BookingStatus::PENDING_APPROVAL;
		$handlerUserId = $this->userId;
		$projectId = null;
		$calendarRepository =  new Booki_CalendarRepository();
		foreach($this->bookings as $booking){
			if($projectId !== $booking->projectId){
				$calendar = $calendarRepository->readByProject($booking->projectId);
				$projectId = $booking->projectId;
			}
			
			foreach($booking->dates as $date){
				$this->order->bookedDays->add(new Booki_BookedDay(array(
					'projectId'=>$booking->projectId
					, 'bookingId'=>$date['id']
					, 'bookingDate'=>$date['date']
					, 'hourStart'=>$date['hourStart']
					, 'minuteStart'=>$date['minuteStart']
					, 'hourEnd'=>$date['hourEnd']
					, 'minuteEnd'=>$date['minuteEnd']
					, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
					, 'cost'=>$date['cost']
					, 'deposit'=>$date['deposit']
					, 'status'=>$status
					, 'handlerUserId'=>$handlerUserId
					, 'projectName'=>$booking->projectName
				)));				
			}			
			
			foreach( $booking->optionals as $optional ){
				$this->order->bookedOptionals->add(new Booki_BookedOptional(array(
					'projectId'=>$booking->projectId
					, 'name'=> $optional['name']
					, 'cost'=>$optional['cost']
					, 'deposit'=>$optional['deposit']
					, 'status'=>$status
					, 'handlerUserId'=>$handlerUserId
					, 'projectName'=>$booking->projectName
					, 'count'=>$optional['count']
				)));
			}
			
			foreach( $booking->cascadingItems as $cascadingItem ){
				$this->order->bookedCascadingItems->add(new Booki_BookedCascadingItem(array(
					'projectId'=>$booking->projectId
					, 'value'=>$cascadingItem['value']
					, 'trails'=>$cascadingItem['trails']
					, 'cost'=>$cascadingItem['cost']
					, 'deposit'=>$cascadingItem['deposit']
					, 'status'=>$status
					, 'handlerUserId'=>$handlerUserId
					, 'projectName'=>$booking->projectName
					, 'count'=>$cascadingItem['count']
				)));
			}
			
			foreach($booking->formElements as $formElement){
				$this->order->bookedFormElements->add(new Booki_BookedFormElement(array(
					'projectId'=>$booking->projectId
					, 'label'=>$formElement->label
					, 'elementType'=>$formElement->elementType
					, 'rowIndex'=>$formElement->rowIndex
					, 'colIndex'=>$formElement->colIndex
					, 'value'=>$formElement->value
					, 'capability'=>$formElement->capability
				)));
			}
			
			foreach($booking->quantityElements as $quantityElement){
				$this->order->bookedQuantityElements->add(new Booki_BookedQuantityElement( array(
					'projectId'=>$booking->projectId
					, 'bookingDate'=>$quantityElement['bookingDate']
					, 'elementId'=>$quantityElement['id']
					, 'bookingId'=>$quantityElement['bookingId']
					, 'name'=>$quantityElement['name']
					, 'quantity'=>$quantityElement['quantity']
					, 'cost'=>$quantityElement['cost']
					, 'deposit'=>$quantityElement['deposit']
					, 'status'=>$status
					, 'handlerUserId'=>$handlerUserId
					, 'projectName'=>$booking->projectName
				)));
			}
		}
	}
	
	public function setUserId($userId){
		$this->order->userId = $userId;
	}
	
	public function log($payingNow = false){
		$this->payingNow = $payingNow;
		$this->order->id = Booki_BookingProvider::insert($this->order);
		$cart = new Booki_Cart();
		$cart->clear();
		$this->sendNotifications();
		Booki_GCalHelper::updateByOrder($this->order->id);
		$this->order = Booki_BookingProvider::read($this->order->id);
		do_action('booki_new_booking_logged', $this->order);
		return $this->order;
	}
	
	public function getOrder(){
		return $this->order;
	}
	public function applyCouponAndLog($coupon){
		if($coupon){
			if($coupon->code && $coupon->couponType === Booki_CouponType::REGULAR){
				$coupon->expire();
				$couponRepository = new Booki_CouponRepository();
				$couponRepository->update($coupon);
			}
			$this->order->discount = $coupon->discount;
		}
		return $this->log();
	}
	
	protected function sendNotifications(){
		if($this->globalSettings->notifyBookingReceivedSuccessfully){
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_RECEIVED_SUCCESSFULLY
				, 'orderId'=>$this->order->id
			));
			if($notificationEmailer){
				$notificationEmailer->send();
			}
		}

		if($this->globalSettings->autoNotifyAdminNewBooking && !$this->payingNow){
			$notificationToUserInfo = Booki_Helper::getUserInfoByEmail($this->globalSettings->notificationEmailTo);
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_ADMIN
				, 'orderId'=>$this->order->id
				, 'userInfo'=>$notificationToUserInfo
			));
			$notificationEmailer->send();
			
			//notifies also agents if projects in booking have agents
			$notificationEmailer = new Booki_AgentsNotificationEmailer(array(
				'emailType'=>Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_AGENTS
				, 'orderId'=>$this->order->id
			));
			$notificationEmailer->send();
		}
		$this->scheduleEmailReminder($this->order->id);
	}
	
	protected function scheduleEmailReminder($orderId){
		//ToDO: remove reminder when order is cancelled.
		new Booki_EmailReminderJob($orderId);
	}
}
?>