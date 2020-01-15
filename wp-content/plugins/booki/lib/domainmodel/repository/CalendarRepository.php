<?php
class Booki_CalendarRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $calendar_table_name;
	private $calendarDay_table_name;
	private $order_days_table_name;
	private $order_form_elements_table_name;
	private $order_table_name;
	private $usermeta_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->calendar_table_name = $wpdb->prefix . 'booki_calendar';
		$this->calendarDay_table_name = $wpdb->prefix . 'booki_calendar_day';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_form_elements_table_name = $wpdb->prefix . 'booki_order_form_elements';
		$this->usermeta_table_name =  $wpdb->usermeta;
	}
	public function readSeatsBookedForEntireBookingPeriod($projectId){
		$sql = "SELECT COUNT(DISTINCT orderId) AS bookedDaysCount
				FROM   $this->order_days_table_name
				WHERE  projectId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $projectId ));
		if( $result ){
			$r = $result[0];
			return (array)$r;
		}
		return false;
	}
	public function readSeatsBookedByTimeslotPerDay($projectId){
		$sql = "SELECT COUNT(id) AS timeslotsCount,
					   bookingDate,
					   projectId,
					   hourStart,
					   minuteStart,
					   hourEnd,
					   minuteEnd
				FROM   $this->order_days_table_name
				WHERE  projectId = %d
					   AND hourStart IS NOT NULL
				GROUP  BY bookingDate,
						  hourStart,
						  minuteStart,
						  hourEnd,
						  minuteEnd";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $projectId ));
		if( is_array( $result )){
			$seats = new Booki_Seats();
			foreach($result as $r){
				$seats->add(new Booki_Seat((array)$r));
			}
			return $seats;
		}
		return false;
	}
	public function readSeatsBookedByDay($projectId){
		$sql = "SELECT COUNT(id) AS bookedDaysCount,
					   bookingDate,
					   projectId
				FROM   $this->order_days_table_name
				WHERE  projectId = %d
				GROUP  BY bookingDate";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $projectId ));
		if( is_array( $result )){
			$seats = new Booki_Seats();
			foreach($result as $r){
				$seats->add(new Booki_Seat((array)$r));
			}
			return $seats;
		}
		return false;
	}
	public function readByProject($projectId){
		$sql = "SELECT c.id,
					   c.projectId,
					   c.startDate,
					   c.endDate,
					   c.daysExcluded,
					   c.timeExcluded,
					   c.weekDaysExcluded,
					   c.hours,
					   c.minutes,
					   c.cost,
					   c.period,
					   c.hourStartInterval,
					   c.minuteStartInterval,
					   c.seatMode,
					   c.bookingLimit,
					   c.displayCounter,
					   c.minNumDaysDeposit,
					   c.deposit,
					   c.bookingStartLapseMode,
					   c.bookingStartLapse,
					   ( 
						   SELECT 
								  CASE c.bookingStartLapseMode 
										 WHEN 0 THEN ((c.bookingStartLapse * 24) * 60) 
										 WHEN 1 THEN (((c.bookingStartLapse * 7) * 24) * 60) 
										 WHEN 2 THEN c.bookingStartLapse * 60 
										 WHEN 3 THEN c.bookingStartLapse 
								  END
						) AS bookingStartLapseMinutes, 
					   c.reminder,
					   c.reminderMode,
					   c.enableSingleHourMinuteFormat,
					   c.quantityElementMode,
					   c.availabilityByQuantityElement,
					   c.includePriceInQuantityElement,
					   (SELECT COUNT(DISTINCT orderId)
						FROM   $this->order_days_table_name
						WHERE  projectId = c.projectId) AS bookedDaysCount
				FROM   $this->calendar_table_name AS c
				WHERE  c.projectId = %d";

		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $projectId ));
		if( $result ){
			$r = $result[0];
			$calendar = new Booki_Calendar((array)$r);
			$calendar->seats = $this->getSeats((int)$r->seatMode, (int)$r->projectId);
			return $calendar;
		}
		return false;
	}
	public function getSeats($seatMode, $projectId){
		if($seatMode === Booki_SeatMode::PER_ENTIRE_BOOKING_PERIOD){
			$result = $this->readSeatsBookedForEntireBookingPeriod($projectId);
			$seats = new Booki_Seats();
			$seats->add(new Booki_Seat($result));
			return $seats;
		}else if($seatMode === Booki_SeatMode::PER_DAY){
			return $this->readSeatsBookedByDay($projectId);
		} else  if($seatMode === Booki_SeatMode::PER_INDIVIDUAL_TIMESLOT){
			return $this->readSeatsBookedByTimeslotPerDay($projectId);
		}
		return false;
	}
	public function read($id){
		//bookingStartLapseMode value is in minutes
		$sql = "SELECT c.id,
					   c.projectId,
					   c.startDate,
					   c.endDate,
					   c.daysExcluded,
					   c.timeExcluded,
					   c.weekDaysExcluded,
					   c.hours,
					   c.minutes,
					   c.cost,
					   c.period,
					   c.hourStartInterval,
					   c.minuteStartInterval,
					   c.seatMode,
					   c.bookingLimit,
					   c.displayCounter,
					   c.minNumDaysDeposit,
					   c.deposit,
					   c.bookingStartLapseMode,
					   c.bookingStartLapse,
					   ( 
						   SELECT 
								  CASE c.bookingStartLapseMode 
										 WHEN 0 THEN ((c.bookingStartLapse * 24) * 60) 
										 WHEN 1 THEN (((c.bookingStartLapse * 7) * 24) * 60) 
										 WHEN 2 THEN c.bookingStartLapse * 60 
										 WHEN 3 THEN c.bookingStartLapse 
								  END
						) AS bookingStartLapseMinutes, 
					   c.reminder,
					   c.reminderMode,
					   c.enableSingleHourMinuteFormat,
					   c.quantityElementMode,
					   c.availabilityByQuantityElement,
					   c.includePriceInQuantityElement,
					   (SELECT COUNT(id)
						FROM   $this->order_days_table_name
						WHERE  projectId = c.projectId) AS bookedDaysCount
				FROM   $this->calendar_table_name AS c
				WHERE  c.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			$calendar = new Booki_Calendar((array)$r);
			$calendar->seats = $this->getSeats((int)$r->seatMode, (int)$r->projectId);
			return $calendar;
		}
		return false;
	}
	public function readAllReminders(){
		$query = "SELECT DISTINCT od.id, od.orderId, od.bookingDate, od.hourStart, od.minuteStart,
               ( 
					   SELECT 
							  CASE c.reminderMode 
									 WHEN 0 THEN ((c.reminder * 24) * 60) 
									 WHEN 1 THEN (((c.bookingStartLapse * 7) * 24) * 60)  
									 WHEN 2 THEN c.reminder * 60 
									 WHEN 3 THEN c.reminder 
							  END) AS reminderMinutes, 
				( 
					   SELECT
							ADDTIME(od.bookingDate, CONCAT(LPAD(COALESCE(od.hourStart, HOUR(NOW())), 2, '0'), ':'
											, LPAD(COALESCE(od.minuteStart, MINUTE(NOW())), 2, '0'), ':00'))  + INTERVAL reminderMinutes MINUTE) AS scheduleDate
				FROM            $this->calendar_table_name   AS c 
				INNER JOIN      $this->order_days_table_name AS od 
				ON              c.projectId = od.projectId 
				WHERE           c.reminder > 0
				LIMIT 1";
		$result = $this->wpdb->get_results($query);
		if( is_array($result) ){
			return $result;
		}
		return false;
	}
	public function readReminderByOrder($orderId){
		$query = "SELECT DISTINCT od.id, od.orderId, od.bookingDate, od.hourStart, od.minuteStart,
                ( 
					   SELECT 
							  CASE c.reminderMode 
									 WHEN 0 THEN ((c.reminder * 24) * 60) 
									 WHEN 1 THEN (((c.bookingStartLapse * 7) * 24) * 60) 
									 WHEN 2 THEN c.reminder * 60 
									 WHEN 3 THEN c.reminder 
							  END) AS reminderMinutes, 
				( 
					SELECT
							ADDTIME(od.bookingDate, CONCAT(LPAD(COALESCE(od.hourStart, HOUR(NOW())), 2, '0'), ':'
											, LPAD(COALESCE(od.minuteStart, MINUTE(NOW())), 2, '0'), ':00'))  + INTERVAL reminderMinutes MINUTE) AS scheduleDate
				FROM            $this->calendar_table_name   AS c 
				INNER JOIN      $this->order_days_table_name AS od 
				ON              c.projectId = od.projectId 
				WHERE           c.reminder > 0
				AND				od.orderId = %d
				LIMIT 1";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($query, $orderId));
		if($result){
			return $result[0];
		}
		return false;
	}
	public function insert($calendar){
		 $result = $this->wpdb->insert($this->calendar_table_name,  array(
			'projectId'=>$calendar->projectId
			, 'startDate'=>$calendar->startDate->format(BOOKI_DATEFORMAT)
			, 'endDate'=>$calendar->endDate->format(BOOKI_DATEFORMAT)
			, 'daysExcluded'=>implode(',', $calendar->daysExcluded)
			, 'timeExcluded'=>implode(',', $calendar->timeExcluded)
			, 'weekDaysExcluded'=>implode(',', $calendar->weekDaysExcluded)
			, 'hours'=>$calendar->hours
			, 'minutes'=>$calendar->minutes
			, 'cost'=>$calendar->cost
			, 'period'=>$calendar->period
			, 'hourStartInterval'=>$calendar->hourStartInterval
			, 'minuteStartInterval'=>$calendar->minuteStartInterval
			, 'seatMode'=>$calendar->seatMode
			, 'bookingLimit'=>$calendar->bookingLimit
			, 'displayCounter'=>$calendar->displayCounter
			, 'minNumDaysDeposit'=>$calendar->minNumDaysDeposit
			, 'bookingStartLapse'=>$calendar->bookingStartLapse
			, 'bookingStartLapseMode'=>$calendar->bookingStartLapseMode
			, 'reminder'=>$calendar->reminder
			, 'reminderMode'=>$calendar->reminderMode
			, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
			, 'deposit'=>$calendar->deposit
			, 'quantityElementMode'=>$calendar->quantityElementMode
			, 'availabilityByQuantityElement'=>$calendar->availabilityByQuantityElement
			, 'includePriceInQuantityElement'=>$calendar->includePriceInQuantityElement
		 ), array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%d', '%d', '%d'));
		 
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($calendar){
		if($calendar->period === Booki_CalendarPeriod::BY_DAY){
			/**
				@description when switching from BY_TIME period setting on the calendar to a BY_DAY period
				then ensure all days do not have time settings.
			*/
			$result = $this->wpdb->update($this->calendarDay_table_name,  array(
				'timeExcluded'=>''
				, 'hours'=>23
				, 'minutes'=>60
			), array('calendarId'=>$calendar->id), array('%s', '%d', '%d'));
		}
		
		$result = $this->wpdb->update($this->calendar_table_name,  array(
			'startDate'=>$calendar->startDate->format(BOOKI_DATEFORMAT)
			, 'endDate'=>$calendar->endDate->format(BOOKI_DATEFORMAT)
			, 'daysExcluded'=>implode(',', $calendar->daysExcluded)
			, 'timeExcluded'=>implode(',', $calendar->timeExcluded)
			, 'weekDaysExcluded'=>implode(',', $calendar->weekDaysExcluded)
			, 'hours'=>$calendar->hours
			, 'minutes'=>$calendar->minutes
			, 'cost'=>$calendar->cost
			, 'period'=>$calendar->period
			, 'hourStartInterval'=>$calendar->hourStartInterval
			, 'minuteStartInterval'=>$calendar->minuteStartInterval
			, 'seatMode'=>$calendar->seatMode
			, 'bookingLimit'=>$calendar->bookingLimit
			, 'displayCounter'=>$calendar->displayCounter
			, 'minNumDaysDeposit'=>$calendar->minNumDaysDeposit
			, 'bookingStartLapse'=>$calendar->bookingStartLapse
			, 'bookingStartLapseMode'=>$calendar->bookingStartLapseMode
			, 'reminder'=>$calendar->reminder
			, 'reminderMode'=>$calendar->reminderMode
			, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
			, 'deposit'=>$calendar->deposit
			, 'quantityElementMode'=>$calendar->quantityElementMode
			, 'availabilityByQuantityElement'=>$calendar->availabilityByQuantityElement
			, 'includePriceInQuantityElement'=>$calendar->includePriceInQuantityElement
		), array('id'=>$calendar->id), array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%d', '%d', '%d'));
		
		return $result;
	}
	
	public function delete($id){
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->calendarDay_table_name WHERE calendarId = %d", $id));
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->calendar_table_name WHERE id = %d", $id));
	}
}
?>