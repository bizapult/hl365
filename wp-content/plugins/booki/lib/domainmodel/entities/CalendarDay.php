<?php
class Booki_CalendarDay extends Booki_EntityBase{
	public $id = -1;
	public $calendarId;
	public $day;
	public $daysExcluded;
	public $timeExcluded;
	public $weekDaysExcluded;
	public $hours = 23;
	public $minutes = 60;
	public $cost;
	public $hourStartInterval;
	public $minuteStartInterval;
	public $minNumDaysDeposit;
	public $enableSingleHourMinuteFormat = false;
	public $deposit;
	public $seasonName;
	public $seatMode;
	public $bookingLimit;
	public $quantityElements;
	public function  __construct($args){
		if(array_key_exists('calendarId', $args)){
			$this->calendarId = (int)$args['calendarId'];
		}
		if(array_key_exists('day', $args)){
			$this->day = $args['day'] instanceOf Booki_DateTime ? $args['day'] : new Booki_DateTime($args['day']);
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
				$this->timeExcluded = (string)$args['timeExcluded'] === '' ? array() : explode(',', $args['timeExcluded']);
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
		if(array_key_exists('hourStartInterval', $args)){
			$this->hourStartInterval = (int)$args['hourStartInterval'];
		}
		if(array_key_exists('minuteStartInterval', $args)){
			$this->minuteStartInterval = (int)$args['minuteStartInterval'];
		}
		if(array_key_exists('minNumDaysDeposit', $args)){
			$this->minNumDaysDeposit = (int)$args['minNumDaysDeposit'];
		}
		if(array_key_exists('deposit', $args)){
			$this->deposit = (double)$args['deposit'];
		}
		if(array_key_exists('seasonName', $args)){
			$this->seasonName = (string)$args['seasonName'];
		}
		if(array_key_exists('enableSingleHourMinuteFormat', $args)){
			$this->enableSingleHourMinuteFormat = (bool)$args['enableSingleHourMinuteFormat'];
		}
		if(array_key_exists('seatMode', $args)){
			$this->seatMode = (int)$args['seatMode'];
		}
		if(array_key_exists('bookingLimit', $args)){
			$this->bookingLimit = (int)$args['bookingLimit'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		if(!$this->timeExcluded){
			$this->timeExcluded = array();
		}
		$this->quantityElements = new Booki_QuantityElements();
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'calendarId'=>$this->calendarId
			, 'day'=>$this->day ? $this->day->format('Y-m-d') : null
			, 'daysExcluded'=>$this->daysExcluded
			, 'timeExcluded'=>$this->timeExcluded
			, 'weekDaysExcluded'=>$this->weekDaysExcluded
			, 'hours'=>$this->hours
			, 'minutes'=>$this->minutes
			, 'cost'=>$this->cost
			, 'timeSlots'=>$this->createTimeSlots($this->hours, $this->minutes, $this->hourStartInterval, $this->minuteStartInterval, $this->enableSingleHourMinuteFormat)
			, 'hourStartInterval'=>$this->hourStartInterval
			, 'minuteStartInterval'=>$this->minuteStartInterval
			, 'seasonName'=>$this->seasonName
			, 'minNumDaysDeposit'=>$this->minNumDaysDeposit
			, 'deposit'=>$this->deposit
			, 'enableSingleHourMinuteFormat'=>$this->enableSingleHourMinuteFormat
			, 'seatMode'=>$this->seatMode
			, 'bookingLimit'=>$this->bookingLimit
		);
	}
}
?>