<?php
class Booki_BookingForm{
	const calendar = '{
					"startDate": "%s"
					, "endDate": "%s"
					, "daysExcluded": [%s]
					, "timeExcluded": [%s]
					, "weekDaysExcluded": [%s]
					, "hours": %d
					, "minutes": %d
					, "cost": "%01.2f"
					, "hourStartInterval": %d
					, "minuteStartInterval": %d
					, "minNumDaysDeposit": %d
					, "deposit":"%01.2f"
					, "bookingStartLapse": %d
				}';
	
	const calendarDays = '{
					"day": "%s"
					, "timeExcluded": [%s]
					, "hours": %d
					, "minutes": %d
					, "cost": "%01.2f"
					, "hourStartInterval": %d
					, "minuteStartInterval": %d
					, "minNumDaysDeposit": %d
					, "deposit":"%01.2f"
				}';
	
	
	public $firstDay;
	public $defaultDateSelected;
	public $showCalendarButtonPanel;
	public $dateFormat;
	public $altFormat;
	public $calendar;
	public $calendarDays = array();
	public $currency;
	public $locale;
	public $decialPoint;
	public $thousandsSep;
	public $currencySymbol;
	public $bookingDaysMinimum;
	public $bookingDaysLimit;
	public $hideSelectedDays;
	public $calendarMode;
	public $calendarPeriod;
	public $minDate;
	public $startDate;
	public $endDate;
	public $formattedStartDate;
	public $formattedEndDate;
	public $projectId;
	public $timezoneInfo;
	public $timezoneString;
	public $timeFormat;
	public $timeSlots = array();
	public $availableDaysLabel;
	public $selectedDaysLabel;
	public $bookingTimeLabel;
	public $proceedToLoginLabel;
	public $makeBookingLabel;
	public $fromLabel;
	public $toLabel;
	public $bookingLimitLabel;
	public $calendarStyles = array();
	public $bookingMode;
	public $autoTimezoneDetection;
	public $usedSlots = array();
	public $timeSelector;
	public $discount;
	public $bookingMinimumDiscount;
	public $hasDiscount;
	public $globalSettings;
	public $bookedItemsCount;
	public $displayBookedTimeSlots;
	public $optionalsBookingMode;
	public $optionalsListingMode;
	public $highlightSelectedOptionals;
	public $bookingLimit;
	public $bookingsExhausted = false;
	public $deposit;
	public $enableItemHeading;
	public $projectName;
	public $resx;
	public $userIsBanned = false;
	public $singleDayEvent = false;
	private $hours;
	private $minutes;
	private $localeInfo;
	private $quantityElements;
	private $quantityElementsReserved = array();
	private $quantityElementsFromCart = array();
	private $quantityElementRepository;
	private $bookings;
	public function __construct($calendar, $calendarDays, $bookedDays, $project, $bookings){
		$this->bookings = $bookings;
		$this->localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $this->localeInfo['currency'];
		$this->currencySymbol = $this->localeInfo['currencySymbol'];
		$this->locale = $this->localeInfo['locale'];
		
		$this->resx = BOOKIAPP()->resx;
		$this->bookedItemsCount = $bookings->count();
		$timezone = $bookings->timezone;
		$this->projectId = $project->id;
		$this->bookingDaysLimit = $project->bookingDaysLimit;
		$this->defaultDateSelected = $project->defaultDateSelected;
		$this->hideSelectedDays = $project->hideSelectedDays;
		$this->calendarMode = $project->calendarMode;
		$this->availableDaysLabel = $project->availableDaysLabel_loc;
		$this->selectedDaysLabel = $project->selectedDaysLabel_loc;
		$this->bookingTimeLabel = $project->bookingTimeLabel_loc;
		$this->optionalsBookingMode = $project->optionalsBookingMode;
		$this->optionalsListingMode = $project->optionalsListingMode;
		$this->fromLabel = $project->fromLabel_loc;
		$this->toLabel = $project->toLabel_loc;
		$this->proceedToLoginLabel = $project->proceedToLoginLabel_loc;
		$this->makeBookingLabel = $project->makeBookingLabel_loc;
		$this->bookingLimitLabel = $project->bookingLimitLabel_loc;
		$this->bookingDaysMinimum = $project->bookingDaysMinimum;
		$this->projectName = $project->name_loc;
		$this->calendarPeriod = $calendar->period;
		$this->bookingLimit = $calendar->bookingLimit;
		$this->bookedDaysCount = $calendar->bookingLimit;
		
		$this->bookingsExhausted = $calendar->exhausted();
		$this->displayCurrentBookingsCount = $calendar->displayCounter && $this->bookingLimit > 0;
		$this->deposit = $calendar->deposit;
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->bookingMode = $project->bookingMode;
		$this->autoTimezoneDetection = $this->globalSettings->autoTimezoneDetection;
		$this->timeSelector = $this->globalSettings->timeSelector;
		$this->calendarFirstDay = $this->globalSettings->calendarFirstDay;
		$this->showCalendarButtonPanel = $this->globalSettings->showCalendarButtonPanel;
		$this->discount = $this->globalSettings->discount;
		$this->bookingMinimumDiscount = $this->globalSettings->bookingMinimumDiscount;
		$this->displayBookedTimeSlots = $this->globalSettings->displayBookedTimeSlots;
		$this->highlightSelectedOptionals = $this->globalSettings->highlightSelectedOptionals;
		$this->hasDiscount = ($this->discount > 0 && ($this->bookingMinimumDiscount == 0 || $bookings->count() >= $this->bookingMinimumDiscount));
		$this->enableItemHeading = isset($_GET['enableitemheading']) ?  filter_var($_GET['enableitemheading'], FILTER_VALIDATE_BOOLEAN) : false;
		
		$this->userIsBanned = Booki_Helper::userInBanList($project->banList);
		
		if(!$this->autoTimezoneDetection){
			$timezone = null;
		}
		array_push($this->calendarStyles, 'booki-datepicker');
		if($this->globalSettings->calendarFlatStyle){
			array_push($this->calendarStyles, 'booki-flat');
		}
		if($this->globalSettings->calendarBorderlessStyle){
			array_push($this->calendarStyles, 'booki-borderless');
		}
		
		$this->dateFormat = $this->globalSettings->shorthandDateFormat;
		$this->altFormat = Booki_DateHelper::getJQueryCalendarFormat($this->dateFormat);
		
		$this->timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
		
		$this->timezoneString = $this->timezoneInfo['timezone'];
		
		if(!$this->bookingDaysLimit){
			$this->bookingDaysLimit = 1;
		}
		if(!$this->bookingDaysLimit || $this->calendarPeriod === Booki_CalendarPeriod::BY_TIME){
			if($this->calendarMode !== 0/*popup*/ && $this->calendarMode !== 1/*inline*/){
				$this->calendarMode = Booki_CalendarMode::POPUP;
			}
		}
		
		$this->decimalPoint = $this->locale['decimal_point'];
		$this->thousandsSep = $this->locale['thousands_sep'];
		$this->updateSeats($bookings, $calendar, $calendarDays);
		$this->quantityElementRepository = new Booki_QuantityElementRepository();
		$this->quantityElements = $this->quantityElementRepository->readAllByProjectId($this->projectId);

		$this->init($calendar, $calendarDays, $bookedDays);
	}
	
	protected function init($calendar, $calendarDays, $bookedDays){
		if(!$calendar){
			return '';
		}
		$today = new Booki_DateTime();
		$today->setTime(0, 0, 0);
		$this->startDate = Booki_DateHelper::formatString($calendar->startDate);
		$this->formattedStartDate = Booki_DateHelper::localizedWPDateFormat($calendar->startDate);
		$calendar->startDate->setTime(0, 0, 0);
		$calendar->endDate->setTime(0, 0, 0);
		$this->singleDayEvent = $calendar->startDate == $calendar->endDate;
		if($calendar->startDate < $today && $calendar->endDate >= $today){
			$this->startDate = Booki_DateHelper::formatString($today);
		} 
		$this->endDate = Booki_DateHelper::formatString($calendar->endDate);
		$this->formattedEndDate = Booki_DateHelper::localizedWPDateFormat($calendar->endDate);
		Booki_DateHelper::fillBookings($calendar, $calendarDays, $bookedDays);
		if($this->bookingMode === Booki_BookingMode::APPOINTMENT){
			$result = Booki_DateHelper::availabilityInRange($calendar, $calendarDays, $bookedDays);
			foreach($result['usedDays'] as $usedDay){
				$timeSlots = array(
					'day'=>$usedDay['day']
					, 'slotsExhausted'=>$usedDay['slotsExhausted']
				);
				array_push($this->usedSlots, $timeSlots);
			}
		}

		$this->calendar = array(
			'startDate'=>$this->startDate
			, 'endDate'=>$this->endDate
			, 'daysExcluded'=>$this->formatExcludedDays($calendar->daysExcluded)
			, 'timeExcluded'=>$calendar->timeExcluded
			, 'weekDaysExcluded'=>$calendar->weekDaysExcluded
			, 'hours'=>$calendar->hours
			, 'minutes'=>$calendar->minutes
			, 'cost'=>$calendar->cost
			, 'hourStartInterval'=>$calendar->hourStartInterval
			, 'minuteStartInterval'=>$calendar->minuteStartInterval
			, 'minNumDaysDeposit'=>$calendar->minNumDaysDeposit
			, 'deposit'=>$calendar->deposit
			, 'bookingStartLapse'=>$calendar->bookingStartLapseMinutes
			, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
			, 'seatMode'=>$calendar->seatMode
			, 'seats'=>$calendar->seats->toArray()
			, 'bookingLimit'=>$calendar->bookingLimit
			, 'period'=>$calendar->period
			, 'quantityElementMode'=>$calendar->quantityElementMode
			, 'availabilityByQuantityElement'=>$calendar->availabilityByQuantityElement
			, 'includePriceInQuantityElement'=>$calendar->includePriceInQuantityElement
		);
		
		if($calendar->deposit > 0){
			$this->hasDiscount = false;
			$this->discount = 0;
			$this->bookingMinimumDiscount = 0;
		}
		
		foreach($calendarDays as $calendarDay){
			if($calendarDay->deposit > 0){
				$this->hasDiscount = false;
				$this->discount = 0;
				$this->bookingMinimumDiscount = 0;
			}
			$dayStringFormat = Booki_DateHelper::formatString($calendarDay->day);
			$exhausted = false;
			foreach($this->usedSlots as $usedSlot){
				if($dayStringFormat === $usedSlot['day'] && $usedSlot['slotsExhausted']){
					$exhausted = true;
					break;
				}
			}
			
			if($exhausted){
				continue;
			}
			array_push($this->calendarDays, array(
				'day'=>$dayStringFormat
				, 'timeExcluded'=>$calendarDay->timeExcluded
				, 'hours'=>$calendarDay->hours
				, 'minutes'=>$calendarDay->minutes
				, 'cost'=>$calendarDay->cost
				, 'hourStartInterval'=>$calendarDay->hourStartInterval
				, 'minuteStartInterval'=>$calendarDay->minuteStartInterval
				, 'minNumDaysDeposit'=>$calendarDay->minNumDaysDeposit
				, 'deposit'=>$calendarDay->deposit
				, 'seatMode'=>$calendarDay->seatMode
				, 'bookingLimit'=>$calendarDay->bookingLimit
				, 'id'=>$calendarDay->id
			));
		}
		
		$quantityElementsReserved = $this->quantityElementRepository->readAllBookedQuantitiesByProjectId($this->projectId);
		foreach($quantityElementsReserved as $quantityElementReserved){
			array_push($this->quantityElementsReserved, array(
				'id'=>$quantityElementReserved->id
				, 'name'=>$quantityElementReserved->name_loc
				, 'quantity'=>$quantityElementReserved->getQuantity()
				, 'displayMode'=>$quantityElementReserved->displayMode
				, 'bookingMode'=>$quantityElementReserved->bookingMode
				, 'isRequired'=>$quantityElementReserved->isRequired
				, 'quantityCount'=>$quantityElementReserved->getBookedQuantityCount()
				, 'bookingDate'=>$quantityElementReserved->bookingDate ? Booki_DateHelper::formatString($quantityElementReserved->bookingDate)  : null
				, 'hourStart'=>$quantityElementReserved->hourStart
				, 'minuteStart'=>$quantityElementReserved->minuteStart
				, 'hourEnd'=>$quantityElementReserved->hourEnd
				, 'minuteEnd'=>$quantityElementReserved->minuteEnd
				, 'calendarDayId'=>$quantityElementReserved->calendarDayId
				, 'calendarDayIdList'=>$quantityElementReserved->calendarDayIdList
				, 'cost'=>$quantityElementReserved->cost
			));
		}
		foreach($this->bookings as $booking){
			foreach($booking->quantityElements as $quantityElement){
				array_push($this->quantityElementsFromCart, array(
					'id'=>$quantityElement->id
					, 'quantity'=>$quantityElement->getQuantity()
					, 'displayMode'=>$quantityElement->displayMode
					, 'bookingMode'=>$quantityElement->bookingMode
					, 'isRequired'=>$quantityElement->isRequired
					, 'quantityCount'=>$quantityElement->getBookedQuantityCount()
					, 'bookingDate'=>$quantityElement->bookingDate ? Booki_DateHelper::formatString($quantityElement->bookingDate)  : null
					, 'hourStart'=>$quantityElement->hourStart
					, 'minuteStart'=>$quantityElement->minuteStart
					, 'hourEnd'=>$quantityElement->hourEnd
					, 'minuteEnd'=>$quantityElement->minuteEnd
					, 'cost'=>$quantityElement->cost
				));
			}
		}
		$this->cost = $calendar->cost;
		$this->formattedCost = Booki_Helper::toMoney($calendar->cost);
		if(($this->calendarMode !== Booki_CalendarMode::POPUP && $this->calendarMode !== Booki_CalendarMode::INLINE) || 
			($this->globalSettings->timeSelector !== Booki_TimeSelector::DROPDOWNLIST && $this->calendarPeriod === Booki_CalendarPeriod::BY_TIME)){
			//we do not allow day based quantity elements in this case
			//so remove any elements that are day based.
			$quantityElements = new Booki_QuantityElements();
			foreach($this->quantityElements as $quantityElement){
				if(!$quantityElement->isDayBased()){
					$quantityElements->add($quantityElement);
				}
			}
			$this->quantityElements = $quantityElements;
		}
	}
	
	public function toJson(){
		$result = array(
			'elem'=>'#booki_' . $this->projectId . '_form'
			, 'projectId'=>$this->projectId
			, 'defaultDateSelected'=>$this->defaultDateSelected
			, 'calendar'=>$this->calendar
			, 'calendarDays'=>$this->calendarDays
			, 'minDate'=>$this->startDate
			, 'maxDate'=>$this->endDate
			, 'bookingDaysMinimum'=>$this->bookingDaysMinimum
			, 'bookingDaysLimit'=>$this->bookingDaysLimit
			, 'hideSelectedDays'=>$this->hideSelectedDays
			, 'altFormat'=>$this->altFormat
			, 'dateFormat'=>$this->dateFormat
			, 'decimalPoint'=>$this->decimalPoint
			, 'thousandsSep'=>$this->thousandsSep
			, 'currencySymbol'=>$this->currencySymbol
			, 'currency'=>$this->currency
			, 'precision'=>Booki_Helper::getCurrencyPrecision()
			, 'currencySymbolPosition'=>Booki_Helper::getCurrencySymbolPosition()
			, 'ajaxurl'=>admin_url('admin-ajax.php') 
			, 'timezone'=>$this->timezoneString 
			, 'timeSelector'=>$this->timeSelector
			, 'discount'=>(double)Booki_Helper::toMoney($this->discount)
			, 'bookingMinimumDiscount'=>$this->bookingMinimumDiscount
			, 'bookedItemsCount'=>$this->bookedItemsCount
			, 'bookingMode'=>$this->bookingMode
			, 'includeBookingPrice'=>$this->globalSettings->includeBookingPrice
			, 'calendarMode'=>$this->calendarMode
			, 'usedSlots'=>$this->usedSlots
			, 'autoTimezoneDetection'=>$this->autoTimezoneDetection
			, 'calendarFirstDay'=>(int)$this->calendarFirstDay
			, 'showCalendarButtonPanel'=>$this->showCalendarButtonPanel
			, 'displayBookedTimeSlots'=>$this->displayBookedTimeSlots
			, 'calendarCssClasses'=>implode(' ', $this->calendarStyles)
			, 'optionalsBookingMode'=>$this->optionalsBookingMode
			, 'optionalsListingMode'=>$this->optionalsListingMode
			, 'highlightSelectedOptionals'=>$this->highlightSelectedOptionals
			, 'defaultCascadingListSelectionLabel'=>__('Select an item', 'booki')
			, 'paymentOnArrivalLabel'=>__('Payment due on arrival','booki')
			, 'bookingLimitLabel'=>$this->bookingLimitLabel
			, 'quantityElements'=>$this->quantityElements->toArray()
			, 'quantityElementsReserved'=>$this->quantityElementsReserved
			, 'quantityElementsFromCart'=>$this->quantityElementsFromCart
			, 'quantityElementExhaustedAlertMessage'=>__('There are no more %s left for this day.', 'booki')
		);
		return json_encode($result, JSON_PRETTY_PRINT);
	}
	
	protected function formatExcludedDays($daysExcluded){
		$result = array();
		foreach($daysExcluded as $day){
			array_push($result, Booki_DateHelper::fromDefaultToAdminSelectedFormat($day));
		}
		return $result;
	}
	
	protected function updateSeats($bookings, $calendar, $calendarDays){
		$seatMode = null;
		$bookingLimit = null;
		$flag = false;
		foreach($bookings as $booking){
			$currentDate = Booki_DateHelper::parseFormattedDateString($booking->date);
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
				continue;
			}

			if($seatMode === Booki_SeatMode::PER_ENTIRE_BOOKING_PERIOD && $calendar->bookingLimit > 0){
				if(!$flag && $booking->projectId === $calendar->projectId){
					++$calendar->bookedDaysCount;
					$flag = true;
				}
				continue;
			}
			
			$flag = false;
			foreach($calendar->seats as $seat){
				$daysAreEqual = Booki_DateHelper::daysAreEqual($seat->bookingDate, $currentDate);
				if($seatMode === Booki_SeatMode::PER_DAY){
					if($daysAreEqual){
						++$seat->bookedDaysCount;
						$flag = true;
						break;
					}
				}else if($seatMode === Booki_SeatMode::PER_INDIVIDUAL_TIMESLOT){
					if($daysAreEqual && (($seat->hourStart == $booking->hourStart && $seat->minuteStart == $booking->minuteStart)
													&& $seat->hourEnd == $booking->hourEnd && $seat->minuteEnd == $booking->minuteEnd)){
						++$seat->timeslotsCount;
						$flag = true;
						break;
					}
				}
			}
			if(!$flag){
				$calendar->seats->add(new Booki_Seat(array(
					'bookingDate'=>$currentDate
					, 'bookedDaysCount'=>1
					, 'timeslotsCount'=>1
					, 'hourStart'=>$booking->hourStart
					, 'minuteStart'=>$booking->minuteStart
					, 'hourEnd'=>$booking->hourEnd
					, 'minuteEnd'=>$booking->minuteEnd
				)));
			}
		}
		if($calendar->seatMode === Booki_SeatMode::PER_ENTIRE_BOOKING_PERIOD){
			$this->bookedDaysCount = $calendar->bookingLimit - $calendar->bookedDaysCount;
		}
	}
}
?>