<?php 
	class Booki_OrdersCSVHandler extends Booki_CSVBaseHandler
	{
		public $globalSettings;
		public $shorthandDateFormat;
		public $timeFormat;
		public function __construct(){
			$hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
			$orderBy = isset($_GET['orderby']) ? $_GET['orderby'] : 'id';
			$order = isset($_GET['order']) ? $_GET['order'] : null;
			$pageIndex = isset($_GET['pageindex']) && trim($_GET['pageindex']) ? (int)$_GET['pageindex'] : -1;
			$perPage = isset($_GET['perpage']) && trim($_GET['perpage']) ? (int)$_GET['perpage'] : null;
			$fromDate = isset($_GET['from']) && trim($_GET['from']) ? new Booki_DateTime($_GET['from']) : null;
			$toDate = isset($_GET['to']) && trim($_GET['to']) ? new Booki_DateTime($_GET['to']) : null;
			$userId = isset($_GET['userid']) && trim($_GET['userid']) ? (int)$_GET['userid'] : null;
			$projectId = isset($_GET['projectid']) && trim($_GET['projectid']) ? (int)$_GET['projectid'] : -1;
			if(!$hasFullControl){
				$user = wp_get_current_user();
				$userId = $user->ID;
			}
			$result = Booki_BookingProvider::orderRepository()->readAllCSV($pageIndex, $perPage, $orderBy, $order, $fromDate, $toDate, $userId, null, $projectId, $hasFullControl);
			$this->globalSettings = BOOKIAPP()->globalSettings;
			$this->shorthandDateFormat = $this->globalSettings->getServerFormatShorthandDate();
			$this->timeFormat = get_option('time_format');
			$columnNames = array(
				'id'
				, 'orderDate'
				, 'status'
				, 'tax'
				, 'taxAmount'
				, 'totalAmount'
				, 'netTotalAmount'
				, 'discount'
				, 'discountAmount'
				, 'paymentDate'
				, 'timezone'
				, 'invoiceNotification'
				, 'refundNotification'
				, 'refundAmount'
				, 'userId'
				, 'username'
				, 'firstname'
				, 'lastname'
				, 'email'
				, 'bookingsCount'
				, 'projectNames'
				, 'bookedDates'
				, 'bookedTimeslots'
				, 'formElements'
				, 'optionals'
				, 'cascadingItems'
			);
			
			$records = array();
			foreach($result as $order){
				$netTotalAmount = $order->totalAmount;
				$discountAmount = 0;
				$tax = 0;
				if($order->status === Booki_PaymentStatus::UNPAID){
					$status = __('Unpaid', 'booki');
				}else if($order->status === Booki_PaymentStatus::PAID){
					$status = __('Paid', 'booki');
				}else{
					$status = __('Refunded', 'booki');
				}
				
				$firstname = $order->user->firstname;
				$lastname = $order->user->lastname;
				$email = $order->user->email;
				
				if($order->notRegUserFirstname){
					$firstname = $order->notRegUserFirstname;
				}
				if($order->notRegUserLastname){
					$lastname = $order->notRegUserLastname;
				}
				if($order->notRegUserEmail){
					$email = $order->notRegUserEmail;
				}
				
				if($order->tax > 0 && $order->status === Booki_PaymentStatus::PAID){
					$tax = $this->getValueByPercentage($order->tax, $order->totalAmount);
					$netTotalAmount = (float)Booki_Helper::toMoney($netTotalAmount - $tax);
				}else if($order->tax > 0){
					$totalAmount = $order->totalAmount;
					if($order->discount > 0){
						$tempDiscount = Booki_Helper::percentage($order->discount, $order->totalAmount);
						$totalAmount -= $tempDiscount;
					}
					$tax = Booki_Helper::percentage($order->tax, $totalAmount);
				}
				if($order->discount > 0 && $order->status === Booki_PaymentStatus::PAID){
					$originalAmount = (float)Booki_Helper::toMoney($netTotalAmount / ((100 - $order->discount) / 100));
					$discountAmount = $originalAmount - $netTotalAmount;
					$netTotalAmount = $originalAmount;
				}else if($order->discount > 0){
					$discountAmount = Booki_Helper::percentage($order->discount, $order->totalAmount);
				}
				array_push($records, implode(",", array(
					$this->encode($order->id)
					, $this->encode($this->formatDate($order->orderDate))
					, $this->encode($status)
					, $this->encode($order->tax . '%')
					, $this->encode(Booki_Helper::toMoney($tax))
					, $this->encode($order->totalAmount)
					, $this->encode($netTotalAmount)
					, $this->encode($order->discount . '%')
					, $this->encode(Booki_Helper::toMoney($discountAmount))
					, $this->encode($this->formatDate($order->paymentDate))
					, $this->encode($order->timezone)
					, $this->encode($order->invoiceNotification)
					, $this->encode($order->refundNotification)
					, $this->encode($order->refundAmount)
					, $this->encode($order->user->id)
					, $this->encode($order->user->username)
					, $this->encode($firstname)
					, $this->encode($lastname)
					, $this->encode($email)
					, $this->encode($order->user->bookingsCount)
					, $this->encode($order->csvFields['projectNames'])
					, $this->encode($this->formatDates($order->csvFields['bookedDates']))
					, $this->encode($this->formatTimeslots($order->csvFields['bookedTimeslots'], $order->timezone, $order->enableSingleHourMinuteFormat))
					, $this->encode($order->csvFields['formElements'])
					, $this->encode($order->csvFields['optionals'])
					, $this->encode($order->csvFields['cascadingItems'])
				)));
			}
			
			echo implode(",", $columnNames);
			echo "\n";
			echo implode("\n", $records);
			parent::__construct();
		}
		protected function formatDate($date){
			if(!$date){
				return null;
			}
			return $date->format($this->shorthandDateFormat);
		}
		protected function formatDates($bookedDates){
			$dates = array();
			foreach($bookedDates as $d){
				$date = new Booki_DateTime($d);
				array_push($dates, $date->format($this->shorthandDateFormat));
			}
			return implode(',', $dates);
		}
		protected function formatTimeslots($bookedTimeslots, $adminTimezone, $enableSingleHourMinuteFormat){
			$timeslots = array();
			if(count($bookedTimeslots) > 0){
				$timezone = null;
				if($this->globalSettings->autoTimezoneDetection){
					$timezone = $adminTimezone;
				}
				$timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
				$userTimezoneString = $timezoneInfo['timezone'];
				
				$adminTimezoneInfo = Booki_TimeHelper::timezoneInfo();
				$adminTimezoneString = $adminTimezoneInfo['timezone'];
				
				foreach($bookedTimeslots as $timeslot){
					$t = new stdClass();
					$t->hourStart = $timeslot['startHour'];
					$t->minuteStart = $timeslot['startMinute'];
					$t->hourEnd = $timeslot['endHour'];
					$t->minuteEnd = $timeslot['endMinute'];
					//$formattedTime = Booki_TimeHelper::formatTime($t, $userTimezoneString, $bookingInfo['enableSingleHourMinuteFormat'], $timeFormat);
					/*array_push($timeslot, sprintf('<div>%1$s</div><div>(<small><strong>%2$s: </strong><span>%3$s</span></small>)</div>'
										, $formattedTime, __('in user selected timezone', 'booki'), $userTimezoneString));*/
					//dont want to cram in too much, so putting time in admin timezone only.					
					$adminFormattedTime = Booki_TimeHelper::formatTime($t, $adminTimezoneString, $enableSingleHourMinuteFormat, $this->timeFormat);
					array_push($timeslots, sprintf('%1$s %2$s', 
										$adminFormattedTime, $adminTimezoneString));
				}
			}
			return implode(',', $timeslots);
		}
		public static function getValueByPercentage($percentage, $amount)
		{
			$p = $percentage / 100;
			return $p * ($amount / ($p + 1));
		}
	}
?>
