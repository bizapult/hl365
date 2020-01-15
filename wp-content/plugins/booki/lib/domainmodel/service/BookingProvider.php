<?php
class Booki_BookingProvider
{
	private static $orderRepo;
	private static $bookedDaysRepo;
	private static $bookedCascadingItemsRepo;
	private static $bookedFormElementsRepo;
	private static $bookedOptionalsRepo;
	private static $bookedQuantityElementRepo;
	protected function __construct()
	{
	}
	public static function orderRepository()
	{
		if (!isset(self::$orderRepo)) 
		{
			self::$orderRepo = new Booki_OrderRepository();
		}
		return self::$orderRepo;
	}
	
	public static function bookedDaysRepository()
	{
		if (!isset(self::$bookedDaysRepo)) 
		{
			self::$bookedDaysRepo = new Booki_BookedDaysRepository();
		}
		return self::$bookedDaysRepo;
	}
	
	public static function bookedFormElementsRepository()
	{
		if (!isset(self::$bookedFormElementsRepo)) 
		{
			self::$bookedFormElementsRepo = new Booki_BookedFormElementsRepository();
		}
		return self::$bookedFormElementsRepo;
	}
	
	public static function bookedCascadingItemsRepository()
	{
		if (!isset(self::$bookedCascadingItemsRepo)) 
		{
			self::$bookedCascadingItemsRepo = new Booki_BookedCascadingItemsRepository();
		}
		return self::$bookedCascadingItemsRepo;
	}
	
	public static function bookedOptionalsRepository()
	{
		if (!isset(self::$bookedOptionalsRepo)) 
		{
			self::$bookedOptionalsRepo = new Booki_BookedOptionalsRepository();
		}
		return self::$bookedOptionalsRepo;
	}
	
	public static function bookedQuantityElementRepository()
	{
		if (!isset(self::$bookedQuantityElementRepo)) 
		{
			self::$bookedQuantityElementRepo = new Booki_BookedQuantityElementRepository();
		}
		return self::$bookedQuantityElementRepo;
	}
	protected static function filter($formElements, $capability){
		if($formElements){
			foreach($formElements as $formElement){
				if($formElement->capability === $capability){
					return $formElement;
				}
			}
		}
		return null;
	}
	
	public static function insert($order)
	{
		$formElements = null;
		
		$orderId = self::orderRepository()->insert($order);
		$insertedBookedDays = array();
		if($order->bookedDays)
		{
			foreach($order->bookedDays as $bookedDay)
			{
				$insertedId = self::bookedDaysRepository()->insert($orderId, $bookedDay);
				if($insertedId !== false){
					$bookedDay->id = $insertedId;
				}
			}
		}
		
		if($order->bookedQuantityElements)
		{
			foreach($order->bookedQuantityElements as $quantityElement)
			{
				$bookedDay = null;
				foreach($order->bookedDays as $bd)
				{
					if($bd->bookingId === $quantityElement->bookingId){
						$bookedDay = $bd;
						break;
					}
				}
				if($bookedDay){
					self::bookedQuantityElementRepository()->insert($orderId, $bookedDay->id, $quantityElement);
				}
			}
		}
		
		if($order->bookedOptionals)
		{
			foreach($order->bookedOptionals as $optional)
			{
				self::bookedOptionalsRepository()->insert($orderId, $optional);
			}
		}
		
		if($order->bookedCascadingItems){
			foreach($order->bookedCascadingItems as $cascadingItem){
				self::bookedCascadingItemsRepository()->insert($orderId, $cascadingItem);
			}
		}
		
		if($order->bookedFormElements)
		{
			$projects = array();
			foreach($order->bookedFormElements as $formElement)
			{
				if(!in_array($formElement->projectId, $projects) && $formElement->elementType === Booki_ElementType::TEXTBOX){
					array_push($projects, $formElement->projectId);
				}
				self::bookedFormElementsRepository()->insert($orderId, $formElement);
			}
		}
		
		if(!$order->userIsRegistered && $order->bookedFormElements)
		{
			$contactInfo = self::getNonRegContactInfo($orderId);
			if($contactInfo){
				$firstName = $contactInfo['firstname'];
				$lastName = $contactInfo['lastname'];
				$email = $contactInfo['email'];
				if($contactInfo['hasAutoRegEmail']){
					$result = Booki_Helper::createUserIfNotExists($email, $firstName, $lastName);
					$order->userId = $result['userId'];
					$order->userIsRegistered = true;
					$order->id = $orderId;
					self::update($order);
				}
			}
		}

		return $orderId;
	}
	
	public static function getNonRegContactInfo($orderId){
		$capabilities = implode(',', array(
			Booki_FormElementCapability::EMAIL_NOTIFICATION_AUTOREG
			, Booki_FormElementCapability::EMAIL_NOTIFICATION
			, Booki_FormElementCapability::FIRST_NAME
			, Booki_FormElementCapability::LAST_NAME
		));
		$formElements = self::bookedFormElementsRepository()->readOrderByCapability($orderId, Booki_ElementType::TEXTBOX, $capabilities);
		$formElementEmailNotification = self::filter($formElements, Booki_FormElementCapability::EMAIL_NOTIFICATION);
		$formElementEmailAutoReg = self::filter($formElements, Booki_FormElementCapability::EMAIL_NOTIFICATION_AUTOREG);
		$formElementFirstName = self::filter($formElements, Booki_FormElementCapability::FIRST_NAME);
		$formElementLastName = self::filter($formElements, Booki_FormElementCapability::LAST_NAME);
		$firstName = $formElementFirstName ? $formElementFirstName->value : null;
		$lastName = $formElementLastName ? $formElementLastName->value : null;
		$email = null;
		$hasAutoRegEmail = false;
		
		if($formElementEmailAutoReg){
			$email = $formElementEmailAutoReg->value;
			$hasAutoRegEmail = true;
		}else if($formElementEmailNotification){
			$email = $formElementEmailNotification->value;
		}
		
		if(!$email){
			return null;
		}
		
		if($firstName && !$lastName){
			$lastName = '';
			$parts = explode(' ', $firstName);
			if(count($parts) > 1){
				$firstName = $parts[0];
				$lastName = $parts[1];
			}
		}
		$fullName = $firstName . ' ' . $lastName;
		return array('email'=>$email, 'firstname'=>$firstName, 'lastname'=>$lastName, 'hasAutoRegEmail'=>$hasAutoRegEmail, 'name'=>$fullName);
	}
	
	public static function update($order)
	{
		return self::orderRepository()->update($order);
	}
	
	public static function read($orderId)
	{
		$order = self::orderRepository()->read($orderId);
		if($order){
			$order->bookedDays = self::bookedDaysRepository()->readByOrder($orderId);
			$order->bookedOptionals = self::bookedOptionalsRepository()->readByOrder($orderId);
			$order->bookedFormElements = self::bookedFormElementsRepository()->readByOrder($orderId);
			$order->bookedCascadingItems = self::bookedCascadingItemsRepository()->readByOrder($orderId);
			$order->bookedQuantityElements = self::bookedQuantityElementRepository()->readByOrder($orderId);
		}
		return $order;
	}
	
	public static function approveAll($orderId){
		
	}
	public static function delete($orderId){
		return self::orderRepository()->delete($orderId);
	}

	public static function getBookingPeriod($projectId, $bookings = null){
		$projectRepository = new Booki_ProjectRepository();
		$calendarRepository =  new Booki_CalendarRepository();
		$calendarDayRepository = new Booki_CalendarDayRepository();
		
		$result = new stdClass();
		$result->project = $projectRepository->read($projectId);
		$result->calendar = $calendarRepository->readByProject($projectId);
		$result->calendarDays = $calendarDayRepository->readAll($result->calendar->id);
		$result->bookedDays = new Booki_BookedDays();
		if($result->project->bookingMode === Booki_BookingMode::APPOINTMENT){
			$bookedDaysRepository = new Booki_BookedDaysRepository();
			$result->bookedDays = $bookedDaysRepository->readByProject($projectId);
			
			if($bookings){
				foreach($bookings as $booking){
					if($booking->projectId !== $projectId){
						continue;
					}
					if($result->calendar->bookingLimit > 0 && $result->calendar->seatMode === Booki_SeatMode::PER_ENTIRE_BOOKING_PERIOD){
						++$result->calendar->bookedDaysCount;
					}
					if($result->calendar->period === Booki_CalendarPeriod::BY_TIME && $booking->hasTime()){
						$time = $booking->hourStart . ':' . $booking->minuteStart;
						if(!in_array($time, $result->calendar->timeExcluded)){
							$result->calendarDays->add(new Booki_CalendarDay(array(
								'day'=>Booki_DateHelper::parseFormattedDateString($booking->date)
								, 'timeExcluded'=>$time ? array_merge($result->calendar->timeExcluded, array($time)) : array()
								, 'hours'=>$result->calendar->hours
								, 'minutes'=>$result->calendar->minutes
								, 'cost'=>$result->calendar->cost
								, 'hourStartInterval'=>$result->calendar->hourStartInterval
								, 'minuteStartInterval'=>$result->calendar->minuteStartInterval
							)));
						}
					}
					
					$result->bookedDays->add(new Booki_BookedDay(array(
						'projectId'=>$booking->projectId
						, 'bookingDate'=>Booki_DateHelper::parseFormattedDateString($booking->date)
						, 'hourStart'=>$booking->hourStart
						, 'minuteStart'=>$booking->minuteStart
						, 'hourEnd'=>$booking->hourEnd
						, 'minuteEnd'=>$booking->minuteEnd
						, 'enableSingleHourMinuteFormat'=>$result->calendar->enableSingleHourMinuteFormat
					)));
				}
			}
			
		}
		return $result;
	}
	public static function approveOrderAndNotifyUser($orderId){
		self::bookedDaysRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::APPROVED);
		self::bookedOptionalsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::APPROVED);
		self::bookedCascadingItemsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::APPROVED);
		self::bookedQuantityElementRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::APPROVED);
		$notificationEmailer = new Booki_NotificationEmailer(array('emailType'=>Booki_EmailType::ORDER_CONFIRMATION, 'orderId'=>$orderId));
		$notificationEmailer->send();
	}
	public static function cancelOrderAndNotifyAdmin($orderId){
		self::bookedDaysRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::USER_REQUEST_CANCEL);
		self::bookedOptionalsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::USER_REQUEST_CANCEL);
		self::bookedCascadingItemsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::USER_REQUEST_CANCEL);
		self::bookedQuantityElementRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::USER_REQUEST_CANCEL);
		$notificationEmailer = new Booki_OrderCancelNotificationEmailer(array('orderId'=>$orderId));
		$notificationEmailer->send();
	}
	public static function hasAvailability($projectId){
		$result = self::getBookingPeriod($projectId);
		Booki_DateHelper::fillBookings($result->calendar, $result->calendarDays, $result->bookedDays);
		$result = Booki_DateHelper::availabilityInRange($result->calendar, $result->calendarDays, $result->bookedDays);
		return count($result['availableDays']) > 0;
	}
}
?>