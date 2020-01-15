<?php 
	class Booki_BookingsCSVHandler extends Booki_CSVBaseHandler
	{
		public function __construct(){
			$hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
			$pageIndex = isset($_GET['pageindex']) && trim($_GET['pageindex']) ? (int)$_GET['pageindex'] : -1;
			$perPage = isset($_GET['perpage']) && trim($_GET['perpage']) ? (int)$_GET['perpage'] : null;
			$fromDate = isset($_GET['from']) && trim($_GET['from']) ? new Booki_DateTime($_GET['from']) : null;
			$toDate = isset($_GET['to']) && trim($_GET['to']) ? new Booki_DateTime($_GET['to']) : null;
			$userId = isset($_GET['userid']) && trim($_GET['userid']) ? (int)$_GET['userid'] : null;
			$projectId = isset($_GET['projectid']) && trim($_GET['projectid']) ? (int)$_GET['projectid'] : -1;
			$globalSettings = BOOKIAPP()->globalSettings;
			$shorthandDateFormat = $globalSettings->getServerFormatShorthandDate();
			$timeFormat = get_option('time_format');
			$orderRepository = new Booki_OrderRepository();
			if(!$hasFullControl){
				$user = wp_get_current_user();
				$userId = $user->ID;
			}
			$result = $orderRepository->readAllBookings($pageIndex, $perPage, $fromDate, $toDate, $userId, null, $projectId, $hasFullControl);
			$columnNames = array(
				'orderId'
				, 'firstname'
				, 'lastname'
				, 'email'
				, 'projectNames'
				, 'bookedDates'
				, 'bookedTimeslots'
				, 'formElements'
				, 'optionals'
				, 'cascadingItems'
			);
			
			$records = array();
			foreach($result as $bookingInfo){
				$dates = array();
				$timeslots = array();
				foreach($bookingInfo->bookedDates as $d){
					$date = new Booki_DateTime($d);
					array_push($dates, sprintf('%s', $date->format($shorthandDateFormat)));
				}
				if(count($bookingInfo->bookedTimeslots) > 0){
					$timezone = null;
					if($globalSettings->autoTimezoneDetection){
						$timezone = $bookingInfo->timezone;
					}
					$timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
					$userTimezoneString = $timezoneInfo['timezone'];
					
					$adminTimezoneInfo = Booki_TimeHelper::timezoneInfo();
					$adminTimezoneString = $adminTimezoneInfo['timezone'];
					
					foreach($bookingInfo->bookedTimeslots as $timeslot){
						$t = new stdClass();
						$t->hourStart = $timeslot['startHour'];
						$t->minuteStart = $timeslot['startMinute'];
						$t->hourEnd = $timeslot['endHour'];
						$t->minuteEnd = $timeslot['endMinute'];
						//$formattedTime = Booki_TimeHelper::formatTime($t, $userTimezoneString, $bookingInfo['enableSingleHourMinuteFormat'], $timeFormat);
						/*array_push($timeslot, sprintf('<div>%1$s</div><div>(<small><strong>%2$s: </strong><span>%3$s</span></small>)</div>'
											, $formattedTime, __('in user selected timezone', 'booki'), $userTimezoneString));*/
						//dont want to cram in too much, so putting time in admin timezone only.					
						$adminFormattedTime = Booki_TimeHelper::formatTime($t, $adminTimezoneString, $bookingInfo->enableSingleHourMinuteFormat, $timeFormat);
						array_push($timeslots, sprintf('%1$s %2$s', 
											$adminFormattedTime, $adminTimezoneString));
					}
				}
				array_push($records, implode(",", array(
					$this->encode($bookingInfo->orderId)
					, $this->encode($bookingInfo->firstname)
					, $this->encode($bookingInfo->lastname)
					, $this->encode($bookingInfo->email)
					, $this->encode($bookingInfo->projectNames)
					, $this->encode(implode(',', $dates))
					, $this->encode(implode(',', $timeslots))
					, $this->encode($bookingInfo->formElements)
					, $this->encode($bookingInfo->optionals)
					, $this->encode($bookingInfo->cascadingItems)
				)));
			}
			
			echo implode(",", $columnNames);
			echo "\n";
			echo implode("\n", $records);
			parent::__construct();
		}
	}
?>
