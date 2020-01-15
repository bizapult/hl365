<?php
class Booki_FullCalendarDataController extends Booki_BaseController{
	private $repo;
	private $hasFullControl;
	private $bookingsInfo;
	public function __construct($viewCallback, $bookingsInfo = null){
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->bookingsInfo = $bookingsInfo;
		$this->view($viewCallback);
	}
	
	//toDO: fix filter buttons and export ?
	public function view($callback){
		$dateFormat = 'Y-m-d';
		$fromDate = null;
		$toDate = null;
		$userId = null;
		$result = array();
		$today = new Booki_DateTime();
		$calendarStartDate = null;
		$color = array('#ffffff', '#ffffff');
		$textColor = array('#000000', '#000000');
		$borderColor = array('#ffffff', '#ffffff');
		$order = 0;
		$row = 0;
		if(!$this->bookingsInfo){
			$today->format($dateFormat);
		}
		foreach($this->bookingsInfo as $bookingInfo){
			$startDate;
			$rowColor = $color[$row % 2];
			$rowTextColor = $textColor[$row % 2];
			$rowBorderColor = $borderColor[$row % 2];
			foreach($bookingInfo->bookedDates as $bookedDate){
				$fullname = trim($bookingInfo->firstname . ' ' . $bookingInfo->lastname);
				$email = $bookingInfo->email;
				$title = $bookingInfo->projectNames;
				$startDate = new Booki_DateTime($bookedDate);
				$startDate->setTime(0, 0, ++$order);
				$url = esc_url_raw(add_query_arg(array('orderid'=>$bookingInfo->orderId, 'external'=>true), 'admin.php?page=booki/managebookings.php'));
				if(!$calendarStartDate){
					$calendarStartDate = $startDate->format($dateFormat);
				}
				array_push($result, array(
					'title'=>$bookingInfo->projectNames
					, 'start'=>$startDate->format('c')
					, 'color'=>'#3366cc'
					, 'textColor'=>'#ffffff'
					, 'headingfield'=>true
					, 'url'=>$url
				));
				if($fullname){
					$startDate->setTime(0, 0, ++$order);
					array_push($result, array(
						'title'=>$fullname
						, 'start'=>$startDate->format('c')
						, 'color'=>$rowColor
						, 'textColor'=>$rowTextColor
						, 'borderColor'=>$rowBorderColor
						, 'namefield'=>true
					));
				}
				$statusTitle = 'Approved';
				$statusColor = 'green';
				if($bookingInfo->hasPendingApproval){
					$statusTitle = 'Pending approval';
					$statusColor = 'orange';
				}
				$startDate->setTime(0, 0, ++$order);
				array_push($result, array(
					'title'=>$statusTitle
					, 'start'=>$startDate->format('c')
					, 'color'=>$rowColor
					, 'textColor'=>$rowTextColor
					, 'borderColor'=>$rowBorderColor
					, 'statusfield'=>true
					, 'pendingApproval'=>$bookingInfo->hasPendingApproval
				));
			}
			$timeslotsCount = count($bookingInfo->bookedTimeslots);
			for($i = 0; $i < $timeslotsCount; $i++){
				$bookedTimeslot = (object)$bookingInfo->bookedTimeslots[$i];
				$startDateTime = $startDate->setTime(0, 0, ++$order);
				$endDateTime = $startDate->setTime(0, 0, $order);
				$title = sprintf(
					'%s:%s - %s:%s'
					, sprintf('%02d', $bookedTimeslot->startHour)
					, sprintf('%02d', $bookedTimeslot->startMinute)
					, sprintf('%02d', $bookedTimeslot->endHour)
					, sprintf('%02d', $bookedTimeslot->endMinute
				));
				array_push($result, array(
					'title'=>$title
					, 'start'=>$startDateTime->format('c')
					, 'end'=>$endDateTime->format('c')
					, 'color'=>$rowColor
					, 'textColor'=>$rowTextColor
					, 'borderColor'=>$rowBorderColor
					, 'timefield'=>true
				));
			}
			if($bookingInfo->formElements){
				$title = __('Has form elements', 'booki');
				$startDateTime = $startDate->setTime(0, 0, ++$order);
				array_push($result, array(
					'title'=>$title
					, 'description'=>$bookingInfo->formElements
					, 'start'=>$startDateTime->format('c')
					, 'color'=>$rowColor
					, 'textColor'=>$rowTextColor
					, 'borderColor'=>$rowBorderColor
					, 'subfield'=>true
				));
			}
			if($bookingInfo->optionals){
				$title = __('Has optionals', 'booki');
				$startDateTime = $startDate->setTime(0, 0, ++$order);
				array_push($result, array(
					'title'=>$title
					, 'description'=>$bookingInfo->optionals
					, 'start'=>$startDateTime->format('c')
					, 'color'=>$rowColor
					, 'textColor'=>$rowTextColor
					, 'borderColor'=>$rowBorderColor
					, 'subfield'=>true
				));
			}
			if($bookingInfo->quantityElements){
				$title = __('Has quantity elements', 'booki');
				$startDateTime = $startDate->setTime(0, 0, ++$order);
				array_push($result, array(
					'title'=>$title
					, 'description'=>$bookingInfo->quantityElements
					, 'start'=>$startDateTime->format('c')
					, 'color'=>$rowColor
					, 'textColor'=>$rowTextColor
					, 'borderColor'=>$rowBorderColor
					, 'subfield'=>true
				));
			}
			if($bookingInfo->cascadingItems){
				$title = __('Has cascading items', 'booki');
				$startDateTime = $startDate->setTime(0, 0, ++$order);
				array_push($result, array(
					'title'=>$title
					, 'description'=>$bookingInfo->cascadingItems
					, 'start'=>$startDateTime->format('c')
					, 'color'=>$rowColor
					, 'textColor'=>$rowTextColor
					, 'borderColor'=>$rowBorderColor
					, 'subfield'=>true
				));
			}
			$row++;
		}
		if(!$calendarStartDate){
			$calendarStartDate = $today->format($dateFormat);
		}
		$this->executeCallback($callback, array($result, $calendarStartDate));
	}
}
?>