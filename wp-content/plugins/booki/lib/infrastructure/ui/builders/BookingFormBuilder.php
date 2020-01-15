<?php
class Booki_BookingFormBuilder{
	public $projectId;
	public $result;
	public function __construct($projectId){
		$this->projectId = $projectId;
		$this->init();
	}
	
	protected function init(){
		$cart = new Booki_Cart();
		$bookings = $cart->getBookings();
		$result = Booki_BookingProvider::getBookingPeriod($this->projectId, $bookings);
		$this->result = new Booki_BookingForm($result->calendar, $result->calendarDays, $result->bookedDays, $result->project, $bookings);
	}
}
?>