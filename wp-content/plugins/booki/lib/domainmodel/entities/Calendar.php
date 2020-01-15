<?php
class Booki_Calendar extends Booki_EntityBase{
	public $id;
	public $projectId;
	public $startDate;
	public $endDate;
	public $hours;
	public $minutes;
	public $cost;
	public $calendarDays;
	public $daysExcluded;
	public $timeExcluded;
	public $weekDaysExcluded;
	public $period = Booki_CalendarPeriod::BY_DAY;
	public $hourStartInterval;
	public $minuteStartInterval;
	public $seatMode;
	public $bookingLimit;
	public $bookedDaysCount;
	public $displayCounter;
	public $minNumDaysDeposit;
	public $bookingStartLapse;
	public $bookingStartLapseMinutes;
	public $bookingStartLapseMode;
	public $reminder;
	public $reminderMode;
	public $enableSingleHourMinuteFormat;
	public $seats;
	public $quantityElementMode;
	public $quantityElements;
	public $availabilityByQuantityElement;
	public $includePriceInQuantityElement;
	public $deposit;
	public $lat;
	public $lng;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('startDate', $args)){
			$this->startDate = $args['startDate'] instanceOf Booki_DateTime ? $args['startDate'] : new Booki_DateTime($args['startDate']);
		}
		if(array_key_exists('endDate', $args)){
			$this->endDate = $args['endDate'] instanceOf Booki_DateTime ? $args['endDate'] : new Booki_DateTime($args['endDate']);
		}
		if(array_key_exists('daysExcluded', $args)){
			if(is_array($args['daysExcluded'])){
				$this->daysExcluded = $args['daysExcluded'];
			}else{
				$this->daysExcluded = (string)$args['daysExcluded'] === '' ? array() : explode(',', $args['daysExcluded']);
			}
		}
		if(array_key_exists('timeExcluded', $args)){
			if(is_array($args['timeExcluded'])){
				$this->timeExcluded = $args['timeExcluded'];
			}else{
				$this->timeExcluded = ((string)$args['timeExcluded'] === '') ? array() : explode(',', $args['timeExcluded']);
			}
		}
		if(array_key_exists('weekDaysExcluded', $args)){
			if(is_array($args['weekDaysExcluded'])){
				$this->weekDaysExcluded = $args['weekDaysExcluded'];
			}else{
				$this->weekDaysExcluded = (string)$args['weekDaysExcluded'] === '' ? array() : Booki_Helper::convertToIntArray(explode(',', $args['weekDaysExcluded']));
			}
		}
		if(array_key_exists('hours', $args)){
			$this->hours = (int)$args['hours'];
		}
		if(array_key_exists('minutes', $args)){
			$this->minutes = (int)$args['minutes'];
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('period', $args)){
			$this->period = (int)$args['period'];
		}
		if(array_key_exists('hourStartInterval', $args)){
			$this->hourStartInterval = (int)$args['hourStartInterval'];
		}
		if(array_key_exists('minuteStartInterval', $args)){
			$this->minuteStartInterval = (int)$args['minuteStartInterval'];
		}
		if(array_key_exists('seatMode', $args)){
			$this->seatMode = (int)$args['seatMode'];
		}
		if(array_key_exists('bookingLimit', $args)){
			$this->bookingLimit = (int)$args['bookingLimit'];
		}
		if(array_key_exists('displayCounter', $args)){
			$this->displayCounter = (bool)$args['displayCounter'];
		}
		if(array_key_exists('minNumDaysDeposit', $args)){
			$this->minNumDaysDeposit = (int)$args['minNumDaysDeposit'];
		}
		if(array_key_exists('deposit', $args)){
			$this->deposit = (double)$args['deposit'];
		}
		if(array_key_exists('bookingStartLapse', $args)){
			$this->bookingStartLapse = (int)$args['bookingStartLapse'];
		}
		if(array_key_exists('bookingStartLapseMinutes', $args)){
			$this->bookingStartLapseMinutes = (int)$args['bookingStartLapseMinutes'];
		}
		if(array_key_exists('bookingStartLapseMode', $args)){
			$this->bookingStartLapseMode = (int)$args['bookingStartLapseMode'];
		}
		if(array_key_exists('reminder', $args)){
			$this->reminder = (int)$args['reminder'];
		}
		if(array_key_exists('reminderMode', $args)){
			$this->reminderMode = (int)$args['reminderMode'];
		}
		if(array_key_exists('enableSingleHourMinuteFormat', $args)){
			$this->enableSingleHourMinuteFormat = (bool)$args['enableSingleHourMinuteFormat'];
		}
		if(array_key_exists('bookedDaysCount', $args)){
			$this->bookedDaysCount = (int)$args['bookedDaysCount'];
		}
		if(array_key_exists('quantityElementMode', $args)){
			$this->quantityElementMode = (int)$args['quantityElementMode'];
		}
		if(array_key_exists('availabilityByQuantityElement', $args)){
			$this->availabilityByQuantityElement = (int)$args['availabilityByQuantityElement'];
		}
		if(array_key_exists('includePriceInQuantityElement', $args)){
			$this->includePriceInQuantityElement = (int)$args['includePriceInQuantityElement'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->calendarDays = new Booki_CalendarDays();
		$this->seats = new Booki_Seats();
		$this->quantityElements = new Booki_QuantityElements();
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'startDate'=>$this->startDate->format('Y-m-d')
			, 'endDate'=>$this->endDate->format('Y-m-d')
			, 'daysExcluded'=>$this->daysExcluded
			, 'timeExcluded'=>$this->timeExcluded
			, 'weekDaysExcluded'=>$this->weekDaysExcluded
			, 'hours'=>$this->hours
			, 'minutes'=>$this->minutes
			, 'cost'=>$this->cost
			, 'calendarDays'=>$this->calendarDays->get_items()
			, 'timeSlots'=>$this->createTimeSlots($this->hours, $this->minutes, $this->hourStartInterval, $this->minuteStartInterval, $this->enableSingleHourMinuteFormat)
			, 'period'=>$this->period
			, 'hourStartInterval'=>$this->hourStartInterval
			, 'minuteStartInterval'=>$this->minuteStartInterval
			, 'seatMode'=>$this->seatMode
			, 'bookingLimit'=>$this->bookingLimit
			, 'bookedDaysCount'=>$this->bookedDaysCount
			, 'displayCounter'=>$this->displayCounter
			, 'minNumDaysDeposit'=>$this->minNumDaysDeposit
			, 'deposit'=>$this->deposit
			, 'bookingStartLapse'=>$this->bookingStartLapse
			, 'bookingStartLapseMinutes'=>$this->bookingStartLapseMinutes
			, 'bookingStartLapseMode'=>$this->bookingStartLapseMode
			, 'reminder'=>$this->reminder
			, 'reminderMode'=>$this->reminderMode
			, 'enableSingleHourMinuteFormat'=>$this->enableSingleHourMinuteFormat
			, 'seats'=>$this->seats->toArray()
			, 'quantityElementMode'=>$this->quantityElementMode
			, 'availabilityByQuantityElement'=>$this->availabilityByQuantityElement
			, 'includePriceInQuantityElement'=>$this->includePriceInQuantityElement
		);
	}
	
	public function exhausted(){
		if($this->bookingLimit > 0 && $this->seatMode === Booki_SeatMode::PER_ENTIRE_BOOKING_PERIOD){
			return $this->bookingLimit <= $this->bookedDaysCount;
		}
		return false;
	}
	
	public function timeSlots($hours, $minutes){
		return $this->createTimeSlots($hours, $minutes, $this->hourStartInterval, $this->minuteStartInterval, $this->enableSingleHourMinuteFormat);
	}
}
?>