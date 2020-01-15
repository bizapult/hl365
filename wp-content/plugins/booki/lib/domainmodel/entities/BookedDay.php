<?php
class Booki_BookedDay extends Booki_EntityBookingElementBase {
	public $id = -1;
	public $projectId;
	public $bookingId;
	public $orderId;
	public $bookingDate;
	public $hourStart = null;
	public $minuteStart = null;
	public $hourEnd = null;
	public $minuteEnd = null;
	public $cost;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $handlerUserId;
	public $notifyUserEmailList;
	public $projectName;
	public $deposit;
	public $enableSingleHourMinuteFormat;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('bookingId', $args)){
			//helper
			$this->bookingId = (int)$args['bookingId'];
		}
		if(array_key_exists('bookingDate', $args)){
			$this->bookingDate = $args['bookingDate'] instanceOf Booki_DateTime ? $args['bookingDate'] : new Booki_DateTime($args['bookingDate']);
		}
		if(array_key_exists('hourStart', $args) && $args['hourStart'] !== null){
			$this->hourStart = (int)$args['hourStart'] ;
		}
		if(array_key_exists('minuteStart', $args) && $args['minuteStart'] !== null){
			$this->minuteStart = (int)$args['minuteStart'];
		}
		if(array_key_exists('hourEnd', $args) &&  $args['hourEnd'] !== null){
			$this->hourEnd = (int)$args['hourEnd'];
		}
		if(array_key_exists('minuteEnd', $args) && $args['minuteEnd'] !== null){
			$this->minuteEnd = (int)$args['minuteEnd'];
		}
		if(array_key_exists('enableSingleHourMinuteFormat', $args)){
			$this->enableSingleHourMinuteFormat = (bool)$args['enableSingleHourMinuteFormat'];
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('deposit', $args)){
			$this->deposit = (double)$args['deposit'];
		}
		if(array_key_exists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('handlerUserId', $args)){
			$this->handlerUserId = (int)$args['handlerUserId'];
		}
		if(array_key_exists('notifyUserEmailList', $args)){
			$this->notifyUserEmailList = trim((string)$args['notifyUserEmailList']);
		}
		if(array_key_exists('projectName', $args)){
			$this->projectName = (string)$args['projectName'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function hasTime(){
		if($this->hourStart !== null || $this->minuteStart !== null){
			return true;
		}
		return false;
	}
	
	public function hasEndTime(){
		if($this->hourEnd !== null || $this->minuteEnd !== null){
			return true;
		}
		return false;
	}
	
	public function compareTime($t2){
		return (($this->hourStart === $t2->hourStart && $this->minuteStart === $t2->minuteStart) &&
				($this->hourEnd === $t2->hourEnd && $this->minuteEnd === $t2->minuteEnd));
	}
	
	public function hasDeposit(){
		return $this->deposit > 0;
	}
}
?>