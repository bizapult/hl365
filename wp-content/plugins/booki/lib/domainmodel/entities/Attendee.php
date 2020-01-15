<?php
class Booki_Attendee extends Booki_EntityBookingElementBase {
	public $orderId = -1;
	public $projectId;
	public $bookingDate;
	public $hourStart = null;
	public $minuteStart = null;
	public $hourEnd = null;
	public $minuteEnd = null;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $firstname;
	public $lastname;
	public $email;
	public $enableSingleHourMinuteFormat;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('firstname', $args)){
			$this->firstname = $args['firstname'];
		}
		if(array_key_exists('lastname', $args)){
			$this->lastname = $args['lastname'];
		}
		if(array_key_exists('email', $args)){
			$this->email = $args['email'];
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
		if(array_key_exists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('enableSingleHourMinuteFormat', $args)){
			$this->enableSingleHourMinuteFormat = (bool)$args['enableSingleHourMinuteFormat'];
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
	public function toArray(){
		return array(
			'projectId'=>$this->projectId
			, 'orderId'=>$this->orderId
			, 'firstname'=>$this->firstname
			, 'lastname'=>$this->lastname
			, 'email'=>$this->email
			, 'bookingDate'=>$this->bookingDate
			, 'hourStart'=>$this->hourStart
			, 'minuteStart'=>$this->minuteStart
			, 'hourEnd'=>$this->hourEnd
			, 'minuteEnd'=>$this->minuteEnd
			, 'enableSingleHourMinuteFormat'=>$this->enableSingleHourMinuteFormat
			, 'status'=>$this->getStatusText($this->status)
			, 'statusColor'=>$this->getStatusLabel($this->status)
			, 'hasTime'=>$this->hasTime()
		);
	}
	public static function getStatusText($status){
		if($status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('Pending Approval', 'booki');
		}else if($status === Booki_BookingStatus::APPROVED){
			return __('Approved', 'booki');
		}else if($status === Booki_BookingStatus::CANCELLED || 
					$status === Booki_BookingStatus::REFUNDED || 
					$status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('Cancelled', 'booki');
		}
	}

	public static function getStatusLabel($status){
		if($status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('info', 'booki');
		}else if($status === Booki_BookingStatus::APPROVED){
			return __('success', 'booki');
		}else if($status === Booki_BookingStatus::CANCELLED || 
					$status === Booki_BookingStatus::REFUNDED || 
					$status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('danger', 'booki');
		}
	}
}
?>