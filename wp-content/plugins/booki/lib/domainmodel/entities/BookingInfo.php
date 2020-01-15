<?php
class Booki_BookingInfo extends Booki_EntityBase{
	public $orderId;
	public $firstname;
	public $lastname;
	public $email;
	public $bookedDates;
	public $bookedTimeslots;
	public $enableSingleHourMinuteFormat;
	public $projectNames;
	public $formElements;
	public $optionals;
	public $quantityElements;
	public $cascadingItems;
	public $user;
	public $timezone;
	public $status;
	
	public $hasDaysPendingApproval = false;
	public $hasOptionalsPendingApproval = false;
	public $hasCascadingItemsPendingApproval = false;
	public $hasDaysPendingCancellation = false;
	public $hasOptionalsPendingCancellation = false;
	public $hasCascadingItemsPendingCancellation = false;
	public $hasQuantityElementsPendingCancellation = false;
	public $hasQuantityElementsPendingApproval = false;
	
	public $hasPendingApproval;
	public $hasPendingCancellation;
	public function __construct($args){
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('firstname', $args)){
			$this->firstname = (string)$args['firstname'];
		}
		if(array_key_exists('lastname', $args)){
			$this->lastname = (string)$args['lastname'];
		}
		if(array_key_exists('email', $args)){
			$this->email = (string)$args['email'];
		}
		if(array_key_exists('status', $args)){
			$this->status = (string)$args['status'];
		}
		if(array_key_exists('projectNames', $args)){
			$this->projectNames = (string)$args['projectNames'];
		}
		if(array_key_exists('timezone', $args)){
			$this->timezone = (string)$args['timezone'];
		}
		if(array_key_exists('bookedDates', $args)){
			$this->bookedDates = explode(',', (string)$args['bookedDates']);
		}
		if(array_key_exists('bookedTimeslots', $args)){
			$bookedTimeslots = explode(',', (string)$args['bookedTimeslots']);
			$this->bookedTimeslots = array();
			foreach($bookedTimeslots as $bts){
				if($bts){
					$s = explode('-', $bts);
					$start = explode(':', $s[0]);
					$end = explode(':', $s[1]);
					array_push($this->bookedTimeslots, array('startHour'=>(int)$start[0], 'startMinute'=>(int)$start[1], 'endHour'=>(int)$end[0], 'endMinute'=>(int)$end[1]));
				}
			}
		}
		if(array_key_exists('enableSingleHourMinuteFormat', $args)){
			$this->enableSingleHourMinuteFormat = (bool)$args['enableSingleHourMinuteFormat'];
		}
		
		if(array_key_exists('formElements', $args)){
			$this->formElements = (string)$args['formElements'];
		}
		if(array_key_exists('optionals', $args)){
			$this->optionals = (string)$args['optionals'];
		}
		if(array_key_exists('quantityElements', $args)){
			$this->quantityElements = (string)$args['quantityElements'];
		}
		if(array_key_exists('cascadingItems', $args)){
			$this->cascadingItems = (string)$args['cascadingItems'];
		}
		if(array_key_exists('hasDaysPendingApproval', $args)){
			$this->hasDaysPendingApproval = (int)$args['hasDaysPendingApproval'] > 0;
		}
		if(array_key_exists('hasOptionalsPendingApproval', $args)){
			$this->hasOptionalsPendingApproval = (int)$args['hasOptionalsPendingApproval'] > 0;
		}
		if(array_key_exists('hasCascadingItemsPendingApproval', $args)){
			$this->hasCascadingItemsPendingApproval = (int)$args['hasCascadingItemsPendingApproval'] > 0;
		}
		if(array_key_exists('hasDaysPendingCancellation', $args)){
			$this->hasDaysPendingCancellation = (int)$args['hasDaysPendingCancellation'] > 0;
		}
		if(array_key_exists('hasOptionalsPendingCancellation', $args)){
			$this->hasOptionalsPendingCancellation = (int)$args['hasOptionalsPendingCancellation'] > 0;
		}
		if(array_key_exists('hasCascadingItemsPendingCancellation', $args)){
			$this->hasCascadingItemsPendingCancellation = (int)$args['hasCascadingItemsPendingCancellation'] > 0;
		}
		if(array_key_exists('hasQuantityElementsPendingApproval', $args)){
			$this->hasQuantityElementsPendingApproval = (int)$args['hasQuantityElementsPendingApproval'] > 0;
		}
		if(array_key_exists('hasQuantityElementsPendingCancellation', $args)){
			$this->hasQuantityElementsPendingCancellation = (int)$args['hasQuantityElementsPendingCancellation'] > 0;
		}
		$this->hasPendingApproval = $this->hasDaysPendingApproval || $this->hasOptionalsPendingApproval || $this->hasCascadingItemsPendingApproval || $this->hasQuantityElementsPendingApproval;
		$this->hasPendingCancellation = $this->hasDaysPendingCancellation || $this->hasOptionalsPendingCancellation || $this->hasCascadingItemsPendingCancellation || $this->hasQuantityElementsPendingCancellation;
	}
	public function toArray(){
		return array(
			'orderId'=>$this->orderId
			, 'firstname'=>$this->firstname
			, 'lastname'=>$this->lastname
			, 'email'=>$this->email
			, 'projectNames'=>$this->projectNames
			, 'bookedDates'=>$this->bookedDates
			, 'bookedTimeslots'=>$this->bookedTimeslots
			, 'formElements'=>$this->formElements
			, 'optionals'=>$this->optionals
			, 'cascadingItems'=>$this->cascadingItems
			, 'quantityElements'=>$this->quantityElements
			, 'enableSingleHourMinuteFormat'=>$this->enableSingleHourMinuteFormat
			, 'timezone'=>$this->timezone
			, 'status'=>$this->status
		);
	}
}
?>