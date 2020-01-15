<?php
class Booki_Bookings extends Booki_CollectionBase{
	public $timezone;
	public $coupon;
	public function __construct($timezone = null){
		$this->timezone = $timezone;
	}
	public function setTimezone($value){
		$this->timezone = $value;
	}
	public function add($value) {
		if (! ($value instanceOf Booki_Booking) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Booking class.');
		}
        parent::add($value);
    }
}
?>