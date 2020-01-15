<?php
class Booki_OrderSummary
{
	
	public $currency;
	public $currencySymbol;
	public $totalAmount;
	public $bookings;
	public $tax;
	public $formattedTaxAmount;
	public $formattedTotalAmountBeforeTax;
	public $formattedTotalAmountIncludingTax;
	public $hasBookings;
	public $dateFormat;
	public $globalSettings;
	public $hasBookedElements = false;
	public $timezoneInfo;
	public $timezoneString;
	public $adminTimezoneInfo;
	public $adminTimezoneString;
	public $bookingsCount = 0;
	public $hasDiscount = false;
	public $hasDeposit = false;
	public $deposit = 0;
	public $formattedArrivalAmount = 0;
	public $discount;
	public $discounted = false;
	public $bookingMinimumDiscount;
	public $enableBookingWithAndWithoutPayment;
	public $refundTotal = null;
	public $discountValue = 0;
	public $discountName;
	public $depositProjectName;
	public $orderId;
	public $seatRequired = false;
	public $quantityElementRequired = false;
	private $items;
	private $timeFormat;
	private $dateTimeHelper;
	private $validate;
	private $coupon;
	private $resx;
	
	public function __construct($args)
	{
		$items = null;
		$validate = true;
		$discount = null;
		if(!array_key_exists('bookings', $args)){
			return;
		}
		$items = $args['bookings'];
		if(array_key_exists('validate', $args)){
			$validate = $args['validate'];
		}
		if(array_key_exists('discount', $args)){
			$discount = $args['discount'];
		}
		if(array_key_exists('coupon', $args)){
			$this->coupon = $args['coupon'];
		}
		if(array_key_exists('refundTotal', $args)){
			$this->refundTotal = $args['refundTotal'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = $args['orderId'];
		}
		$this->dateFormat = get_option('date_format');
		$this->timeFormat = get_option('time_format');
		
		$this->resx = BOOKIAPP()->resx;
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol = $localeInfo['currencySymbol'];
		$timezone = $items->timezone;
		if(!$this->globalSettings->autoTimezoneDetection){
			$timezone = null;
		}
		$this->timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
		$this->timezoneString = $this->timezoneInfo['timezone'];
		
		$this->adminTimezoneInfo = Booki_TimeHelper::timezoneInfo();
		$this->adminTimezoneString = $this->adminTimezoneInfo['timezone'];
		
		$this->items = $items;
		$this->validate = $validate;
		$this->discount = $this->globalSettings->discount;
		if($discount){
			$this->discount = $discount;
		}
		$this->totalAmount = 0;
		$this->bookings = array();
		
		$this->tax = $this->globalSettings->tax;
		$this->enableCartItemHeader = $this->globalSettings->enableCartItemHeader;
		$this->bookingMinimumDiscount = $this->globalSettings->bookingMinimumDiscount;
		$this->enableBookingWithAndWithoutPayment = $this->globalSettings->enableBookingWithAndWithoutPayment;
		$this->hasDiscount = ($this->discount > 0 && ($this->bookingMinimumDiscount == 0 || $this->items->count() >= $this->bookingMinimumDiscount));
		$this->discountName = $this->resx->PROMOTIONS_LOC;
		
		$this->createSummary();
		$this->enablePayments = $this->globalSettings->enablePayments && $this->totalAmount > 0;
		if($this->items->count() === 0){
			$this->disableDiscounts();
		}
	}
	
	protected function createSummary()
	{	
		$projectRepository = new Booki_ProjectRepository();
		$calendarRepository =  new Booki_CalendarRepository();
		$calendarDayRepository = new Booki_CalendarDayRepository();
		$bookedDaysRepository = new Booki_BookedDaysRepository();
		$quantityElementRepository = new Booki_QuantityElementRepository();
		
		$projectId = null;
		$project = null;
		$calendar = null;
		$calendarDays = null;
		$bookedQuantityElements = null;
		foreach($this->items as $booking){
			if($booking->projectId !== $projectId){ 
				$projectId = $booking->projectId;
				$project = $projectRepository->read($projectId);
				if(!$project){
					continue;
				}
				$calendar = $calendarRepository->readByProject($projectId);
				$calendarDays = $calendarDayRepository->readAll($calendar->id);
				$quantityElements = $quantityElementRepository->readAllByProjectId($projectId);
				if($booking->quantityElements->count() > 0){
					$bookedQuantityElements = $quantityElementRepository->readAllBookedQuantitiesByProjectId($projectId);
				}
				if($this->validate){
					$userIsBanned = Booki_Helper::userInBanList($project->banList);
					if($userIsBanned){
						$this->items->remove_item($booking);
						continue;
					}
				}
			}
			
			if($calendar->deposit > 0){
				$this->disableDiscounts();
				$this->hasDeposit = true;
			}
			
			if($projectId === null || !$project){
				continue;
			}
			
			$item = new stdClass();
			$item->dates = array();
			$item->optionals = array();
			$item->cascadingItems = array();
			$item->quantityElements = array();
			$item->projectId = $projectId;
			$item->projectName = $project->name_loc;
			$item->calendarId = $calendar->id;
			$item->formElements = $booking->formElements;
			$item->hasBookingLimit = $calendar->bookingLimit > 0;
			$item->bookingExhausted = false;
			$item->calendarMode = $project->calendarMode;
			$item->deposit = 0;
			$item->subTotal = 0;
			$item->total = 0;
			$item->rangeDates = null;
			$singleBookingTotal = 0;
			$bookedDays = null;
			$reserved = false;
			$formattedTime = '';
			$adminFormattedTime = '';
			$slotCost;
			$currentDate = Booki_DateHelper::parseFormattedDateString($booking->date);

			if($this->validate && $project->bookingMode === Booki_BookingMode::APPOINTMENT){
				$bookedDays = $bookedDaysRepository->readByProject($projectId);
			}

			if($bookedDays){
				foreach($bookedDays as $bookedDay){
					if($calendar->exhausted()){
						$item->bookingExhausted = true;
						$reserved = true;
					}
					if(!$reserved && Booki_DateHelper::daysAreEqual($bookedDay->bookingDate, $currentDate)){
						if($calendar->period === Booki_CalendarPeriod::BY_TIME && 
							($booking->hasTime() && !$bookedDay->compareTime($booking))){
							continue;
						}
						$reserved = true;
					}
					if($reserved){
						$this->items->remove_item($booking);
						$this->hasBookedElements = true;
						break;
					}
				}
			}
			
			if($this->validate){
				$seatsCount = $this->getRemainingSeats($this->items, $booking, $calendar, $calendarDays, $currentDate);
				$this->seatRequired = $seatsCount !== false && $seatsCount < 0;
				$this->quantityElementRequired = ($booking->quantityElements->count() === 0 && $calendar->availabilityByQuantityElement) &&
													($quantityElements && $quantityElements->count() > 0);
				if($this->seatRequired){
					$reserved = true;
					$this->items->remove_item($booking);
				}else if($this->quantityElementRequired){
					$reserved = true;
					$this->items->remove_item($booking);
				}
			}

			foreach($calendarDays as $calendarDay){
				if($calendarDay->day->format(BOOKI_DATEFORMAT) === $currentDate->format(BOOKI_DATEFORMAT)){
					$slotCost = $calendarDay->cost;
					break;
				}
			}
			
			if(!isset($slotCost)){
				$slotCost = $calendar->cost;
			}
			if(!$reserved){
				$item->subTotal = $slotCost;
			}
			if($calendar->period === Booki_CalendarPeriod::BY_TIME){
				$formattedTime = Booki_TimeHelper::formatTime($booking, $this->timezoneString, $calendar->enableSingleHourMinuteFormat, $this->timeFormat);
				$adminFormattedTime = Booki_TimeHelper::formatTime($booking, $this->adminTimezoneString, $calendar->enableSingleHourMinuteFormat, $this->timeFormat);
			}

			array_push($item->dates, array(
				'rawDate'=>$booking->date
				, 'date'=>$currentDate
				, 'formattedDate'=>Booki_DateHelper::localizedWPDateFormat($currentDate)
				, 'bookingId'=>$booking->id
				, 'cost'=>$slotCost
				, 'deposit'=>$booking->deposit
				, 'formattedCost'=>Booki_Helper::toMoney($slotCost)
				, 'hourStart'=>$booking->hourStart
				, 'minuteStart'=>$booking->minuteStart
				, 'hourEnd'=>$booking->hourEnd
				, 'minuteEnd'=>$booking->minuteEnd
				, 'formattedTime'=>$formattedTime
				, 'adminFormattedTime'=>$adminFormattedTime
				, 'projectName'=>$item->projectName
				, 'status'=>$booking->status
				, 'reserved'=>$reserved
				, 'isRequired'=>$project->bookingDaysMinimum > 0 && $this->items->count() <= $project->bookingDaysMinimum
				, 'notifyUserEmailList'=>$project->notifyUserEmailList
				, 'id'=>$booking->id
			));
			unset($slotCost);
			if(!$reserved){
				++$this->bookingsCount;
			}
			//if all days in booking reserved, remove all sub items eg: optionals and cascades.
			$fullyReserved = count($item->dates) === 0 && $reserved;
			
			$lengthOptionals = 0;
			foreach($booking->optionals as $optional){
				++$lengthOptionals;
				$calculatedCost = $optional->cost;
				$calculatedName = $optional->name_loc;
				if($optional->count > 0){
					$calculatedCost =  $optional->cost * $optional->count;
					$calculatedName .= ' x ' . $optional->count;
				}
				array_push($item->optionals, array(
					'name'=>$optional->name_loc
					, 'id'=>$optional->id
					, 'bookingId'=>$booking->id
					, 'cost'=>$optional->cost
					, 'deposit'=>$booking->deposit
					, 'formattedCost'=>Booki_Helper::toMoney($optional->cost)
					, 'reserved'=>$fullyReserved
					, 'count'=>$optional->count
					, 'calculatedCost'=>$calculatedCost
					, 'formattedCalculatedCost'=>Booki_Helper::toMoney($calculatedCost)
					, 'calculatedName'=>$calculatedName
					, 'projectName'=>$item->projectName
					, 'status'=>$optional->status
					, 'isRequired'=>$project->optionalsMinimumSelection > 0 && $lengthOptionals <= $project->optionalsMinimumSelection
					, 'notifyUserEmailList'=>$project->notifyUserEmailList
				));
				if(!$fullyReserved){
					$item->subTotal += $calculatedCost;
				}
			}

			foreach($booking->cascadingItems as $cascadingItem){
				$calculatedCost = $cascadingItem->cost;
				$calculatedName = $cascadingItem->value_loc;
				$trail = $cascadingItem->getTrail();
				if($cascadingItem->count > 0){
					$calculatedCost =  $cascadingItem->cost * $cascadingItem->count;
					$calculatedName .= ' x ' . $cascadingItem->count;
					$trail .= ' x ' . $cascadingItem->count;
				}
				array_push($item->cascadingItems, array(
					'value'=>$cascadingItem->value_loc
					, 'id'=>$cascadingItem->id
					, 'bookingId'=>$booking->id
					, 'cost'=>$cascadingItem->cost
					, 'deposit'=>$booking->deposit
					, 'formattedCost'=>Booki_Helper::toMoney($cascadingItem->cost)
					, 'reserved'=>$fullyReserved
					, 'count'=>$cascadingItem->count
					, 'calculatedCost'=>$calculatedCost
					, 'formattedCalculatedCost'=>Booki_Helper::toMoney($calculatedCost)
					, 'calculatedName'=>$calculatedName
					, 'trail'=>$trail
					, 'trails'=>$cascadingItem->trails
					, 'projectName'=>$item->projectName
					, 'status'=>$cascadingItem->status
					, 'isRequired'=>$cascadingItem->isRequired
					, 'notifyUserEmailList'=>$project->notifyUserEmailList
				));
				if(!$fullyReserved){
					$item->subTotal += $calculatedCost;
				}
			}
			foreach($booking->quantityElements as $quantityElement){
				$hasQuantity = true;
				if($this->validate){
					$hasQuantity = Booki_QuantityElementHelper::hasQuantity($this->items, $bookedQuantityElements, $quantityElement);
					if(!$hasQuantity && $quantityElement->isRequired){
						$fullyReserved = true;
					}
				}
				if($hasQuantity){
					$quantityElementCost = $quantityElement->getCost();
					array_push($item->quantityElements, array(
						'id'=>$quantityElement->id
						, 'bookingId'=>$quantityElement->bookingId
						, 'bookingDate'=>$quantityElement->bookingDate
						, 'name'=>$quantityElement->name_loc
						, 'quantity'=>$quantityElement->getSelectedQuantity()
						, 'cost'=>$quantityElementCost
						, 'formattedCost'=>Booki_Helper::toMoney($quantityElementCost)
						, 'notifyUserEmailList'=>$project->notifyUserEmailList
						, 'deposit'=>$booking->deposit
						, 'reserved'=>$fullyReserved
						, 'status'=>$quantityElement->status
						, 'isRequired'=>$quantityElement->isRequired
					));
					
					if(!$fullyReserved){
						$item->subTotal += $quantityElementCost;
					}
				}
			}
			
			if($fullyReserved){
				$this->items->remove_item($booking);
			}
			
			if($booking->deposit > 0){
				$item->deposit = $this->calcDeposit($booking->deposit, $item->subTotal);
				$item->total = $item->subTotal - $item->deposit;
				//only one project is allowed when using deposits, so keep the name.
				$this->depositProjectName = $item->projectName;
			}else{
				$item->total = $item->subTotal;
			}
			array_push($this->bookings, $item);
		}

		$this->hasBookings = count($this->bookings) > 0;
		$totalAmount = 0;
		$arrivalAmount = 0;
		foreach($this->bookings as $item){
			if($item->deposit > 0){
				$totalAmount += $item->deposit;
				$arrivalAmount += $item->total;
			}else{
				$totalAmount += $item->total;
			}
		}
		$this->totalAmount = $totalAmount;
		$this->formattedTotalAmount = Booki_Helper::toMoney($totalAmount);
		$this->formattedArrivalAmount = Booki_Helper::toMoney($arrivalAmount);
		if($this->hasDeposit){
			$this->deposit = $this->formattedTotalAmount;
		}
		
		if(!$this->hasDiscount){
			$this->discount = 0;
		}
		$coupon = $this->coupon ? $this->coupon : $this->items->coupon;
		if($coupon){
			$this->discount = $coupon->discount;
			$this->discountName = $coupon->id !== -1 ? $this->resx->COUPON_LOC : $this->resx->PROMOTIONS_LOC;
		}
		if($this->discount > 0){
			$totalAmount = Booki_Helper::calcDiscount($this->discount, $totalAmount);
			$this->discountValue = Booki_Helper::percentage($this->discount, $this->totalAmount);
		}
		$this->formattedTaxAmount = Booki_Helper::toMoney(Booki_Helper::percentage($this->tax, $totalAmount));
		if($this->refundTotal > 0){
			$totalAmount -= $this->refundTotal;
		}
		$this->formattedTotalAmountBeforeTax = Booki_Helper::toMoney($totalAmount);
		$this->formattedTotalAmountIncludingTax = Booki_Helper::toMoney($this->formattedTaxAmount + $totalAmount);
	}

	protected function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
	
	protected function disableDiscounts(){
		$this->hasDiscount = false;
		$this->discount = 0;
		$this->bookingMinimumDiscount = 0;
	}
	
	protected function getRemainingSeats($bookings, $booking, $calendar, $calendarDays, $currentDate){
		$seatMode = null;
		$bookingLimit = null;
		$seatCount = null;
		$flag = false;
		$i = 0;
		foreach($calendarDays as $calendarDay){
			if(Booki_DateHelper::daysAreEqual($calendarDay->day, $currentDate)){
				$seatMode = $calendarDay->seatMode;
				$bookingLimit = $calendarDay->bookingLimit;
				break;
			}
		}

		if($seatMode === null){
			$seatMode = $calendar->seatMode;
			$bookingLimit = $calendar->bookingLimit;
		}
		
		if($bookingLimit === 0){
			return false;
		}
		
		foreach($bookings as $b){
			if($seatMode === Booki_SeatMode::PER_ENTIRE_BOOKING_PERIOD && $calendar->bookingLimit > 0){
				if(!$flag && $booking->projectId === $calendar->projectId){
					++$i;
					$flag = true;
				}
			}
			else{
				$d = Booki_DateHelper::parseFormattedDateString($b->date);
				$dateFlag = Booki_DateHelper::daysAreEqual($currentDate, $d);
				$timeFlag = (($booking->hourStart == $b->hourStart && $booking->minuteStart == $b->minuteStart)
														&& $booking->hourEnd == $b->hourEnd && $booking->minuteEnd == $b->minuteEnd);
				if($seatMode === Booki_SeatMode::PER_DAY){
					if($dateFlag){
						++$i;
					}
				}else if($seatMode === Booki_SeatMode::PER_INDIVIDUAL_TIMESLOT){
					if($dateFlag && $timeFlag){
						++$i;
					}
				}
				if($seatCount === null){
					foreach($calendar->seats as $seat){
						$daysAreEqual = Booki_DateHelper::daysAreEqual($seat->bookingDate, $currentDate);
						if($seatMode === Booki_SeatMode::PER_DAY){
							if($daysAreEqual){
								$seatCount = $bookingLimit - $seat->bookedDaysCount;
								break;
							}
						}else if($seatMode === Booki_SeatMode::PER_INDIVIDUAL_TIMESLOT){
							$timeslotsAreEqual = (($seat->hourStart == $booking->hourStart && $seat->minuteStart == $booking->minuteStart)
															&& $seat->hourEnd == $booking->hourEnd && $seat->minuteEnd == $booking->minuteEnd);
							if($daysAreEqual && $timeslotsAreEqual){
								$seatCount = $bookingLimit - $seat->timeslotsCount;
								break;
							}
						}
					}
				}
			}
		}
		if($seatCount === null){
			$seatCount = $bookingLimit;
		}
		return $seatCount - $i;
	}
}
?>