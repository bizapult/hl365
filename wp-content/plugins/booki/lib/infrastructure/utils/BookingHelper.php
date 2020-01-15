<?php
class Booki_BookingHelper{
	public static function getBookings($order, $projectId = null){
		$refundTotal = 0;
		$bookings = new Booki_Bookings($order->timezone);
		if(!$order){
			return array('bookings'=>$bookings, 'refundTotal'=>$refundTotal);
		}
		$length = $order->bookedDays->count();
		$lastIndex = $length - 1;
		$projectId = null;
		for($i = 0; $i < $length; $i++){
			$day = $order->bookedDays->item($i);
			if($projectId !== null && $day->projectId !== $projectId){
				continue;
			}

			if($order->status === Booki_PaymentStatus::REFUNDED || $day->status === Booki_BookingStatus::REFUNDED){
				$refundTotal += self::calcDeposit($day->deposit, $day->cost);
			}
			$booking = new Booki_Booking(array(
				'projectId'=>$day->projectId
				, 'deposit'=>$day->deposit
				, 'projectName'=>$day->projectName
				, 'date'=>$day->bookingDate
				, 'hourStart'=>$day->hourStart
				, 'minuteStart'=>$day->minuteStart
				, 'hourEnd'=>$day->hourEnd
				, 'minuteEnd'=>$day->minuteEnd
				, 'status'=>$day->status
				, 'id'=>$day->id
				, 'firstname'=>$order->getFirstname()
				, 'lastname'=>$order->getLastname()
				, 'email'=>$order->getEmail()
			));
			$bookings->add($booking);
			//we add optionals and cascades into the last day of each project always.
			$nextIndex = $i + 1;
			if($i === $lastIndex || $nextIndex < $length){
				$flag = true;
				if($i !== $lastIndex){
					$nextDay = $order->bookedDays->item($nextIndex);
					$flag = false;
					if($nextDay->projectId !== $day->projectId){
						$flag = true;
					}
				}
				
				foreach($order->bookedQuantityElements as $quantityElement){
					if($day->id === $quantityElement->orderDayId){
						if($day->projectId === $quantityElement->projectId){
							if($order->status === Booki_PaymentStatus::REFUNDED || $quantityElement->status === Booki_BookingStatus::REFUNDED){
								$refundTotal += self::calcDeposit($quantityElement->deposit, $quantityElement->cost);
							}
							$booking->quantityElements->add(new Booki_QuantityElement(array(
								'projectId'=>$quantityElement->projectId
								, 'name'=>$quantityElement->name
								, 'cost'=>$quantityElement->cost
								, 'status'=>$quantityElement->status
								, 'quantity'=>$quantityElement->quantity
								, 'selectedQuantity'=>$quantityElement->getSelectedQuantity()
								, 'bookingId'=>$quantityElement->orderDayId
								, 'id'=>$quantityElement->id
							)));
						}
					}
				}
					
				if($flag){
					foreach($order->bookedOptionals as $optional){
						if($day->projectId === $optional->projectId){
							if($order->status === Booki_PaymentStatus::REFUNDED || $optional->status === Booki_BookingStatus::REFUNDED){
								$refundTotal += self::calcDeposit($optional->deposit, $optional->getCalculatedCost());
							}
							$booking->optionals->add(new Booki_Optional(array(
								'projectId'=>$optional->projectId
								, 'name'=>$optional->name
								, 'cost'=>$optional->cost
								, 'status'=>$optional->status
								, 'count'=>$optional->count
								, 'id'=>$optional->id
							)));
						}
					}
					foreach($order->bookedCascadingItems as $cascadingItem){
						if($day->projectId === $cascadingItem->projectId){
							if($order->status === Booki_PaymentStatus::REFUNDED || $cascadingItem->status === Booki_BookingStatus::REFUNDED){
								$refundTotal += self::calcDeposit($cascadingItem->deposit, $cascadingItem->getCalculatedCost());
							}
							$booking->cascadingItems->add(new Booki_CascadingItem(array(
								'projectId'=>$cascadingItem->projectId
								, 'value'=>$cascadingItem->value
								, 'trails'=>$cascadingItem->trails
								, 'cost'=>$cascadingItem->cost
								, 'status'=>$cascadingItem->status
								, 'count'=>$cascadingItem->count
								, 'id'=>$cascadingItem->id
							)));
						}
					}
					foreach($order->bookedFormElements as $formElement){
						if($day->projectId === $formElement->projectId){
							$booking->formElements->add(new Booki_FormElement(array(
								'projectId'=>$formElement->projectId
								, 'label'=>$formElement->label
								, 'value'=>$formElement->value
								, 'elementType'=>$formElement->elementType
								, 'capability'=>$formElement->capability
								, 'id'=>$formElement->id
							)));
						}
					}
				}
			}
		}
		return array('bookings'=>$bookings, 'refundTotal'=>$refundTotal);
	}
	
	protected static function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
	
	public static function getStatusText($status){
		if($status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('Pending Approval', 'booki');
		}else if($status === Booki_BookingStatus::APPROVED){
			return __('Approved', 'booki');
		}else if($status === Booki_BookingStatus::CANCELLED){
			return __('Cancelled', 'booki');
		}else if($status === Booki_BookingStatus::REFUNDED){
			return __('Refunded', 'booki');
		} else if ($status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('Pending User Cancel Request', 'booki');
		}
	}
	
	public static function getStatusLabel($status){
		if($status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('info', 'booki');
		}else if($status === Booki_BookingStatus::APPROVED){
			return __('success', 'booki');
		}else if($status === Booki_BookingStatus::CANCELLED){
			return __('danger', 'booki');
		}else if($status === Booki_BookingStatus::REFUNDED || $status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('warning', 'booki');
		}
	}
	
	public static function fillContextMenu($canEdit, $canCancel, $refundable, $status){
		$contextButtons = array();
		$currentStatus = '';
		if($status === Booki_BookingStatus::REFUNDED){
			$currentStatus = __('Refunded', 'booki');
		}else if($status === Booki_BookingStatus::USER_REQUEST_CANCEL && !$canEdit){
			$currentStatus =  __('Pending Cancel Request', 'booki');
		}else if($status === Booki_BookingStatus::CANCELLED){
			$currentStatus = __('Cancelled', 'booki');
		}
		
		if($currentStatus){
			return array('currentStatus'=>$currentStatus, 'contextButtons'=>$contextButtons);
		}

		if($canEdit && ($status !== Booki_BookingStatus::APPROVED)){
			$contextButtons['Approve'] = array('icon'=>'glyphicon-thumbs-up', 'label'=>'Approve');
		}
		if($canCancel && 
			($status === Booki_BookingStatus::PENDING_APPROVAL || 
			$status === Booki_BookingStatus::APPROVED ||
			$status === Booki_BookingStatus::USER_REQUEST_CANCEL)){
			$contextButtons['Cancel'] = array('icon'=>'glyphicon-thumbs-down', 'label'=>'Cancel');
		}
		if($refundable && $status !== Booki_BookingStatus::REFUNDED){
			$contextButtons['Refund'] = array('icon'=>'glyphicon-arrow-left', 'label'=>'Refund');
		}
		return array('currentStatus'=>$currentStatus, 'contextButtons'=>$contextButtons);
	}
	
	public static function deleteBookedDay($orderId, $bookedDay){
		$orderRepo = new Booki_OrderRepository();
		$bookedDaysRepo = new Booki_BookedDaysRepository();
		$bookedDays = $bookedDaysRepo->readByOrder($orderId);
		$deleteOrder = false;
		if($bookedDays->count() === 1){
			$deleteOrder = true;
		}else{
			if($bookedDay->cost > 0){
				$order = $orderRepo->read($orderId);
				$cost = Booki_Helper::calcDeposit($bookedDay->deposit, $bookedDay->cost);
				if($order->discount > 0){
					$cost = Booki_Helper::calcDiscount($order->discount, $cost);
				}
				if($order->tax > 0){
					$cost += Booki_Helper::percentage($order->tax, $cost);
				}
				$order->totalAmount -= $cost;
				$orderRepo->update($order);
			}
			$bookedDaysRepo->delete($bookedDay->id);
		}
		$notificationEmailer = new Booki_NotificationEmailer(array(
			'emailType'=>Booki_EmailType::BOOKING_DAY_CANCELLED
			, 'orderId'=>$orderId
			, 'bookedDayId'=>$bookedDay->id
		));
		$notificationEmailer->send();
		if($deleteOrder){
			$orderRepo->delete($orderId);
		}
	}
	public static function setBookedDayStatus($orderId, $bookedDay){
		if($bookedDay->status === Booki_BookingStatus::CANCELLED){
			self::deleteBookedDay($orderId, $bookedDay);
			return;
		}
		$orderRepo = new Booki_OrderRepository();
		$bookedDaysRepo = new Booki_BookedDaysRepository();
		$bookedDays = $bookedDaysRepo->readByOrder($orderId);
		foreach($bookedDays as $bd){
			if($bd->id !== $bookedDay->id && $bd->status !== $bookedDay->status){
				$flag = false;
			}
		}
		if($bookedDay->status === Booki_BookingStatus::APPROVED){
			$notificationEmailer = new Booki_NotificationEmailer(array(
				'emailType'=>Booki_EmailType::BOOKING_DAY_CONFIRMED
				, 'orderId'=>$bookedDay->orderId
				, 'bookedDayId'=>$bookedDay->id
			));
			$notificationEmailer->send();
		}
		if($flag){
			$order = $orderRepo->read($orderId);
			$order->status = $bookedDay->status;
			$orderRepository->update($order);
		}
		$bookedDaysRepo->update($bookedDay);
	}
}
?>