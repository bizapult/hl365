<?php
class Booki_BookingFormParser{
	protected function __construct() {
	}
	public static function populateBookingFromPostData($projectId, Booki_Bookings $bookings){
		if($projectId === -1 || $projectId === null){
			return $bookings;
		}
		$errors = array();
		$selectedDate = isset($_POST['selected_date']) ? $_POST['selected_date'] : '';
		$selectedTime = isset($_POST['time']) ? $_POST['time'] : null;
		$timezone = isset($_POST['timezone']) ? $_POST['timezone'] : null;
		$deposit = isset($_POST['deposit_field']) ? (double)$_POST['deposit_field'] : null;
		$dates = explode(',', $selectedDate);
		
		foreach($dates as $d){
			if(!Booki_DateHelper::dateIsValidFormat($d)){
				$errors['Date'] = __('Booking date is required or is in an invalid format.', 'booki');
				return array('bookings'=>$bookings, 'errors'=>$errors);
			}
		}
		
		$projectRepository = new Booki_ProjectRepository();
		$project = $projectRepository->read($projectId);
		
		$calendarRepository = new Booki_CalendarRepository();
		$calendar = $calendarRepository->readByProject($projectId);
		
		$formElementRepository = new Booki_FormElementRepository();	
		$formElements = $formElementRepository->readAll($projectId);
		
		$optionalRepository =  new Booki_OptionalRepository();
		$optionals = $optionalRepository->readAll($projectId);
		
		$cascadingListRepository = new Booki_CascadingListRepository();
		$cascadingLists = $cascadingListRepository->readAllTopLevel($projectId);
		$cascadingLists = $cascadingListRepository->readItemsByLists($cascadingLists);
		$quantityElementRepository = new Booki_QuantityElementRepository();
		$quantityElements = $quantityElementRepository->readAllByProjectId($projectId);
		if($timezone){
			$bookings->setTimezone($timezone);
		}

		$args = array(
			'projectId'=>$projectId
			, 'deposit'=>$deposit
			, 'projectName'=>$project->name
		);
		
		$booking = null;
		$currentBookings = array();
		if(is_array($selectedTime)){
			for($i = 0; $i < count($selectedTime); $i++){
				$time = $selectedTime[$i];
				$id = $bookings->count() - 1;
				$args = array_merge($args, array(
					'date'=>$selectedDate
					, 'time'=>$time
					, 'id'=>$id
				));
				$b = new Booki_Booking($args);
				if($i == count($selectedTime) - 1){
					$booking = $b;
				}
				$bookings->add($b);
				array_push($currentBookings, $b);
			}
		}else{
			$length = count($dates);
			for($i = 0; $i < $length; $i++){
				$date = $dates[$i];
				$id = $bookings->count() - 1;
				$args = array_merge($args, array(
					'date'=>$date
					, 'time'=>$selectedTime
					, 'id'=>$id
				));
				$b = new Booki_Booking($args);
				if($i == $length - 1){
					$booking = $b;
				}
				$bookings->add($b);
				array_push($currentBookings, $b);
			}
		}

		$bookingsTotalCount = 0;
		if($project->optionalsBookingMode === Booki_OptionalsBookingMode::EACH_DAY){
			if(count($dates) > 1){
				$bookingsTotalCount = count($dates);
			}else if(is_array($selectedTime) && count($selectedTime) > 1){
				$bookingsTotalCount = count($selectedTime);
			}
		}
			
		foreach($formElements as $elem){
			$name = 'booki_form_element_' . $elem->id;
			if($elem->elementType === Booki_ElementType::RADIOBUTTON){
				$name = 'booki_form_element_' . (strlen($elem->value) > 0 ? $elem->value : $elem->id);
			}
			if( isset($_POST[$name])){
				$value = $_POST[$name];
				if($elem->elementType === Booki_ElementType::RADIOBUTTON && $elem->label !== $value)
				{
					continue;
				}
				
				$validator = new Booki_Validator($elem->validation, $elem->label, $value);
				if($validator->isValid()){
					if($value && count($errors) === 0){
						$elem->value = $value;
						$booking->formElements->add($elem);
					}
				} else{
					$errors[$name] = join(',', $validator->errors);
				}
			}
		}
		
		$optionalName = 'booki_optional_group_' . $projectId;
		if($project->optionalsMinimumSelection > 0){
			if(isset($_POST[$optionalName]) && count($_POST[$optionalName]) < $project->optionalsMinimumSelection){
				$errors[$optionalName] = sprintf(__('You must make atleast %d selections', 'booki'), $project->optionalsMinimumSelection);
			}
		}
		
		if($calendar->period === Booki_CalendarPeriod::BY_DAY){
			if($project->bookingDaysMinimum && count($dates) < $project->bookingDaysMinimum){
				$errors['Dates'] = sprintf(__('A minimum of %d days required.', 'booki'), $project->bookingDaysMinimum);
			}
			if($project->bookingDaysLimit > 1 && count($dates) > $project->bookingDaysLimit){
				$errors['Dates'] = sprintf(__('You cannot select more than %d days.', 'booki'), $project->bookingDaysLimit);
			}
		}else{
			if(!$selectedTime){
				$errors['Timeslots'] = sprintf(__('A minimum of 1 time slots required. If there are no time slots available, try a different date.', 'booki'), 1);
			}else{
				if($project->bookingDaysMinimum && (is_array($selectedTime) && count($selectedTime) < $project->bookingDaysMinimum)){
					$errors['Timeslots'] = sprintf(__('A minimum of %d time slots required.', 'booki'), $project->bookingDaysMinimum);
				}
				if($project->bookingDaysLimit > 1 && (is_array($selectedTime) && count($selectedTime) > $project->bookingDaysLimit)){
					$errors['Timeslots'] = sprintf(__('You cannot select more than %d time slots.', 'booki'), $project->bookingDaysLimit);
				}
			}
		}
		
		if(count($errors) === 0){
			foreach($cascadingLists as $cl){
				$errors = self::fillCascadingListFromHttpPost(array(
					'booking'=>$booking
					, 'cascadingListRepository'=>$cascadingListRepository
					, 'bookingsTotalCount'=>$bookingsTotalCount
					, 'cascadingItems'=>$cl->cascadingItems
					, 'id'=>$cl->id
					, 'isRequired'=>$cl->isRequired
					, 'trails'=>array($cl->label)
				));
				if(count($errors) > 0){
					break;
				}
			}
		}
		
		if(count($errors) === 0){
			foreach($optionals as $optional){
				if(isset($_POST[$optionalName])){
					$optional->count = $bookingsTotalCount;
					$value = $_POST[$optionalName];
					if(is_array($value)){
						foreach($value as $id){
							if(intval($id) === $optional->id){
								$booking->optionals->add($optional);
								break;
							}
						}
					}else if($value){
						$booking->optionals->add($optional);
					}
				}
			}
		}
		
		if(count($errors) === 0){
			foreach($currentBookings as $cb){
				foreach($quantityElements as $qe){
					$quantityElement = new Booki_QuantityElement($qe->params());
					$quantityElement->quantityElementItems = $qe->quantityElementItems;
					$name = 'booki_quantity_element_' . $quantityElement->id;
					$quantity = $quantityElement->quantity;
					if(isset($_POST[$name]) && $_POST[$name] !== ''){
						$value = intval($_POST[$name]);
						$temp = $value;
						if($quantityElement->displayMode === Booki_QuantityElementDisplayMode::DROPDOWNLIST){
							$temp += 1;
						}else{
							$value -= 1;
						}
						$validator = new Booki_Validator(array('min'=>1,'max'=>$quantity, 'required'=>$quantityElement->isRequired ? true : null), $quantityElement->name, $temp);
						if($validator->isValid()){
							if(count($errors) === 0){
								$quantityElement->bookingId = $cb->id;
								$quantityElement->selectedQuantity = $value;
								$quantityElement->bookingDate = Booki_DateHelper::parseFormattedDateString($cb->date);
								$quantityElement->hourStart = $cb->hourStart;
								$quantityElement->minuteStart = $cb->minuteStart;
								$quantityElement->hourEnd = $cb->hourEnd;
								$quantityElement->minuteEnd = $cb->minuteEnd;
								$quantityElement->bookedQuantityCount = $temp;
								$cb->quantityElements->add($quantityElement);
							}
						} else{
							$errors[$name] = join(',', $validator->errors);
						}
					}
				}
			}
		}
		return array('bookings'=>$bookings, 'errors'=>$errors);
	}

	protected static function fillCascadingListFromHttpPost($args){
		$errors = array();
		$cascadingItemName = 'booki_cascadingdropdown_' . $args['id'];
		foreach($args['cascadingItems'] as $ci){
			if(isset($_POST[$cascadingItemName])){
				$args['hasSelection'] = true;
				if((int)$_POST[$cascadingItemName] === $ci->id){
					array_push($args['trails'], $ci->value_loc);
					$ci->count = $args['bookingsTotalCount'];
					if($ci->parentId === -1){
						$ci->trails = $args['trails'];
						$args['booking']->cascadingItems->add($ci);
					}
				}
			}
			if($ci->parentId !== -1){
				$args['cascadingItems'] = $args['cascadingListRepository']->readItemsByListId($ci->parentId);
				$args['id'] = $ci->parentId;
				self::fillCascadingListFromHttpPost($args);
			}
		}
		if(!$args['hasSelection'] && $args['isRequired']){
			$errors[$cascadingItemName] = __('You must select atleast one item from the dropdown list', 'booki');
		}
		return $errors;
	}
	
	protected function getCostByQuantity($quantityElementItems, $quantity){
		foreach($quantityElementItems as $quantityElementItem){
			if($quantityElementItem->quantity === $quantity){
				return $quantityElementItem->cost;
			}
		}
		return null;
	}
}
?>