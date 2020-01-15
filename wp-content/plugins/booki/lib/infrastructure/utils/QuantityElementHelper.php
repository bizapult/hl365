<?php
class Booki_QuantityElementHelper{
	public static function hasQuantity($bookings, $bookedQuantityElements, $quantityElement){
		$quantity = 0;
		foreach($bookings as $booking){
			$currentDate = Booki_DateHelper::parseFormattedDateString($booking->date);
			foreach($booking->quantityElements as $bookedQuantityElement){
				if($bookedQuantityElement->id === $quantityElement->id){
					if(self::incrementQuantity($booking, $currentDate,  $quantityElement)){
						$quantity += $bookedQuantityElement->getSelectedQuantity();
					}
				}
				if($bookedQuantityElement === $quantityElement){
					break 2;
				}
			}
		}
		if($bookedQuantityElements){
			foreach($bookedQuantityElements as $bookedQuantityElement){
				if($bookedQuantityElement->id === $quantityElement->id){
					if(self::incrementQuantity($booking, $currentDate, $bookedQuantityElement)){
						$quantity += $bookedQuantityElement->selectedQuantity;
					}
				}
			}
		}
		return $quantityElement->quantity >= $quantity;
	}
	
	protected static function incrementQuantity($booking, $currentDate, $quantityElement){
		if($quantityElement->bookingMode === Booki_QuantityElementBookingMode::FIXED){
			return false;
		}
		if($quantityElement->bookingMode !== Booki_QuantityElementBookingMode::PER_ENTIRE_BOOKING_PERIOD){
			if(!Booki_DateHelper::daysAreEqual($quantityElement->bookingDate, $currentDate)){
				return false;
			}
		}
		if($quantityElement->bookingMode === Booki_QuantityElementBookingMode::PER_INDIVIDUAL_TIMESLOT){
			if(!(($booking->hourStart == $quantityElement->hourStart && $booking->minuteStart == $quantityElement->minuteStart) &&
				($booking->hourEnd == $quantityElement->hourEnd && $booking->minuteEnd == $quantityElement->minuteEnd))){
				return false;
			}
		}
		return true;
	}
}
?>