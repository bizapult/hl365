<?php
class Booki_Booking{
	public $formElements;
	public $optionals;
	public $cascadingItems;
	public $quantityElements;
	public $date;
	public $hourStart = null;
	public $minuteStart = null;
	public $hourEnd = null;
	public $minuteEnd = null;
	public $deposit = null;
	public $id;
	public $projectId;
	public $projectName;
	public $status = null;
	public $firstname = null;
	public $lastname = null;
	public $email = null;
	private $args;
	public function __construct($args){
		$this->args = $args;
		if(array_key_exists('id', $args)){
			$this->id = $args['id'];
		}
		if(array_key_exists('projectId', $args)){
			$this->projectId = $args['projectId'];
		}
		if(array_key_exists('projectName', $args)){
			$this->projectName = $args['projectName'];
		}
		if(array_key_exists('date', $args)){
			$this->date = $args['date'];
		}
		if(array_key_exists('time', $args)){
			$this->parseTime($args['time']);
		}
		if(array_key_exists('deposit', $args)){
			$this->deposit = $args['deposit'];
		}
		if(array_key_exists('status', $args)){
			//helper to bridge BookedDay
			$this->status = $args['status'];
		}
		if(array_key_exists('hourStart', $args)){
			$this->hourStart = $args['hourStart'] ;
		}
		if(array_key_exists('minuteStart', $args)){
			$this->minuteStart = $args['minuteStart'];
		}
		if(array_key_exists('hourEnd', $args)){
			$this->hourEnd = $args['hourEnd'];
		}
		if(array_key_exists('minuteEnd', $args)){
			$this->minuteEnd = $args['minuteEnd'];
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
		$this->formElements = new Booki_FormElements();
		$this->optionals = new Booki_Optionals();
		$this->cascadingItems = new Booki_CascadingItems();
		$this->quantityElements = new Booki_QuantityElements();
	}
	
	public function hasTime(){
		return $this->hourStart !== null || $this->minuteStart !== null;
	}
	public function hasEndTime(){
		if($this->hourEnd !== null || $this->minuteEnd !== null){
			return true;
		}
		return false;
	}
	protected function parseTime($time){
		if($time){
			$t = explode(',', $time);
			$start = explode(':', $t[0]);
			$this->hourStart = intval($start[0]);
			$this->minuteStart = intval($start[1]);
			if(count($t) > 1){
				$end = explode(':', $t[1]);
				$this->hourEnd = intval($end[0]);
				$this->minuteEnd = intval($end[1]);
			}
		}
	}
	public function toArray(){
		$this->args['hourStart'] = $this->hourStart;
		$this->args['minuteStart'] = $this->minuteStart;
		$this->args['hourEnd'] = $this->hourEnd;
		$this->args['minuteEnd'] = $this->minuteEnd;
		$this->args['formElements'] = $this->formElements->toArray();
		$this->args['optionals'] = $this->optionals->toArray();
		$this->args['cascadingItems'] = $this->cascadingItems->toArray();
		$this->args['quantityElements'] = $this->quantityElements->toArray();
		return $this->args;
	}
}
?>