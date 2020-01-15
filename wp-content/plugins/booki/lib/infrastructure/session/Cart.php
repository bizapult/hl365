<?php
class Booki_Cart{
	private $bookings;
	public function __construct(){
		if(!BOOKIAPP()->session->get('Booki_Bookings')){
			BOOKIAPP()->session->set('Booki_Bookings', new Booki_Bookings(Booki_TimeHelper::getDefaultTimezone()));
		}
		$this->bookings = BOOKIAPP()->session->get('Booki_Bookings');
	}
	
	public function setCoupon($coupon){
		$this->bookings->coupon = $coupon;
		$this->rebuildSession($this->bookings);
	}
	
	public function getCoupon(){
		return $this->bookings->coupon;
	}
	
	public function removeCoupon(){
		return $this->setCoupon(null);
	}
	
	public function addBooking(Booki_Booking $booking){
		$this->bookings->add($booking);
	}
	
	public function getBookings(){
		return $this->bookings;
	}
	
	public function getBooking($id){
		foreach($this->bookings as $booking){
			if($booking->id === $id){
				return $booking;
			}
		}
	}
	
	public function remove($id){
		foreach($this->bookings as $booking){
			if($booking->id === $id){
				$this->bookings->remove_item($booking);
				break;
			}
		}
		$this->rebuildSession($this->bookings);
	}
	public function count(){
		return $this->bookings->count();
	}
	public function rebuildSession($bookings){
		BOOKIAPP()->session->set('Booki_Bookings', $bookings);
		$this->bookings = BOOKIAPP()->session->get('Booki_Bookings');
	}
	public function clear(){
		$this->removeCoupon();
		$this->bookings->clear();
		BOOKIAPP()->session->delete('Booki_Bookings');
	}
	
	public function getTotalAmount(){
		$additionalCosts = 0;
		$totalCost = 0;
		$flag = false;
		
		$calendarRepository =  new Booki_CalendarRepository();
		$calendarDayRepository = new Booki_CalendarDayRepository();
		
		$bookings = $this->getBookings();
		$projectId = null;
		$calendar = null;
		$calendarDays = null;
		foreach($bookings as $booking){
			if($booking->projectId !== $projectId){ 
				$projectId = $booking->projectId;
				$calendar = $calendarRepository->readByProject($projectId);
				$calendarDays = $calendarDayRepository->readAll($calendar->id);
			}

			foreach($calendarDays as $calendarDay){
				$d = Booki_DateHelper::parseFormattedDateString($booking->date);
				if($calendarDay->day === $d->format('Y-m-d')){
					$totalCost += $calendarDay->cost;
					$flag = true;
					break;
				}
			}
			
			if(!$flag){
				$totalCost += $calendar->cost;
			}

			foreach($booking->optionals as $optional){
				$additionalCosts += $optional->cost;
			}
			
			foreach($booking->cascadingItems as $cascadingItem){
				$additionalCosts += $cascadingItem->cost;
			}
		}
		
		$totalAmount = $totalCost + $additionalCosts;
		
		if($this->bookings->coupon){
			return $this->bookings->coupon->deduct($totalAmount);
		}
		return $totalAmount;
	}
}
?>