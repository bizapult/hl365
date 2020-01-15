<?php
class Booki_CalendarDayRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $calendarDay_table_name;
	private $calendar_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->calendarDay_table_name = $wpdb->prefix . 'booki_calendar_day';
		$this->calendar_table_name = $wpdb->prefix . 'booki_calendar';
	}

	public function readAll($calendarId){
		$sql = "SELECT cd.id,
					   cd.calendarId,
					   cd.day,
					   cd.seasonName,
					   cd.timeExcluded,
					   cd.hours,
					   cd.minutes,
					   cd.cost,
					   cd.hourStartInterval,
					   cd.minuteStartInterval,
					   cd.seatMode,
					   cd.bookingLimit,
					   c.daysExcluded,
					   c.weekDaysExcluded,
					   cd.minNumDaysDeposit,
					   cd.deposit,
					   c.enableSingleHourMinuteFormat
				FROM   $this->calendarDay_table_name AS cd
					   INNER JOIN $this->calendar_table_name AS c
							   ON cd.calendarId = c.id
				WHERE  cd.calendarId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $calendarId) );
		if( is_array( $result )){
			$calendarDays = new Booki_CalendarDays();
			foreach($result as $r){
				$calendarDays->add(new Booki_CalendarDay((array)$r));
			}
			return $calendarDays;
		}
		return false;
	}
	public function readByDay($dateString, $calendarId){
		$d = Booki_DateHelper::parseFormattedDateString($dateString);
		$day = sprintf('CONVERT( \'%s\', DATETIME)', $d->format(BOOKI_DATEFORMAT));
		$sql = "SELECT cd.id,
					   cd.calendarId,
					   cd.day,
					   cd.seasonName,
					   cd.timeExcluded,
					   cd.hours,
					   cd.minutes,
					   cd.cost,
					   cd.hourStartInterval,
					   cd.minuteStartInterval,
					   cd.seatMode,
					   cd.bookingLimit,
					   c.daysExcluded,
					   c.weekDaysExcluded,
					   cd.minNumDaysDeposit,
					   cd.deposit,
					   c.enableSingleHourMinuteFormat
				FROM   $this->calendarDay_table_name AS cd
					   INNER JOIN $this->calendar_table_name AS c
							   ON cd.calendarId = c.id
				WHERE  cd.day IN ( %s ) 
						AND cd.calendarId = %d";
		$result = $this->wpdb->get_results(sprintf($sql,  $day, $calendarId));
		if($result){
			$r = $result[0];
			return new Booki_CalendarDay((array)$r);
		}
		return false;
	}
	public function readAllBySeason($calendarId, $seasonName){
		$sql = "SELECT cd.id,
					   cd.calendarId,
					   cd.day,
					   cd.seasonName,
					   cd.timeExcluded,
					   cd.hours,
					   cd.minutes,
					   cd.cost,
					   cd.hourStartInterval,
					   cd.minuteStartInterval,
					   cd.seatMode,
					   cd.bookingLimit,
					   c.daysExcluded,
					   c.weekDaysExcluded,
					   cd.minNumDaysDeposit,
					   cd.deposit,
					   c.enableSingleHourMinuteFormat
				FROM   $this->calendarDay_table_name AS cd
					   INNER JOIN $this->calendar_table_name AS c
							   ON cd.calendarId = c.id
				WHERE  cd.calendarId = %d
					   AND cd.seasonName = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $calendarId, $seasonName) );
		if( is_array( $result )){
			$calendarDays = new Booki_CalendarDays();
			foreach($result as $r){
				$calendarDays->add(new Booki_CalendarDay((array)$r));
			}
			return $calendarDays;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT c.id,
					   cd.calendarId,
					   cd.day,
					   cd.seasonName,
					   cd.timeExcluded,
					   cd.hours,
					   cd.minutes,
					   cd.cost,
					   cd.hourStartInterval,
					   cd.minuteStartInterval,
					   cd.seatMode,
					   cd.bookingLimit,
					   c.daysExcluded,
					   c.weekDaysExcluded,
					   cd.minNumDaysDeposit,
					   cd.deposit,
					   c.enableSingleHourMinuteFormat
				FROM   $this->calendarDay_table_name AS cd
					   INNER JOIN $this->calendar_table_name AS c
							   ON cd.calendarId = c.id
				WHERE  cd.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		if( $result ){
			$r = $result[0];
			return new Booki_CalendarDay((array)$r);
		}
		return false;
	}
	public function readAllSeasons($id){
		$sql = "SELECT DISTINCT cd.seasonName
				FROM   $this->calendarDay_table_name AS cd
					   INNER JOIN $this->calendar_table_name AS c
							   ON cd.calendarId = c.id
				WHERE  cd.calendarId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id));
		$seasons = array();
		if( is_array( $result )){
			foreach($result as $r){
				array_push($seasons, array('seasonName'=>$r->seasonName));
			}
		}
		return $seasons;
	}
	
	public function insert($calendarDay){
		 $result = $this->wpdb->insert($this->calendarDay_table_name,  array(
			'calendarId'=>$calendarDay->calendarId
			, 'day'=>$calendarDay->day->format(BOOKI_DATEFORMAT)
			, 'timeExcluded'=>implode(',', $calendarDay->timeExcluded)
			, 'hours'=>$calendarDay->hours
			, 'minutes'=>$calendarDay->minutes
			, 'cost'=>$calendarDay->cost
			, 'hourStartInterval'=>$calendarDay->hourStartInterval
			, 'minuteStartInterval'=>$calendarDay->minuteStartInterval
			, 'seasonName'=>$calendarDay->seasonName
			, 'minNumDaysDeposit'=>$calendarDay->minNumDaysDeposit
			, 'deposit'=>$calendarDay->deposit
			, 'seatMode'=>$calendarDay->seatMode
			, 'bookingLimit'=>$calendarDay->bookingLimit
		), array('%d', '%s', '%s', '%d', '%d', '%f', '%d', '%d', '%s', '%d', '%f', '%d', '%d'));
		
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($calendarDay){
		$result = $this->wpdb->update($this->calendarDay_table_name,  array(
			'day'=>$calendarDay->day->format(BOOKI_DATEFORMAT)
			, 'timeExcluded'=>implode(',', $calendarDay->timeExcluded)
			, 'hours'=>$calendarDay->hours
			, 'minutes'=>$calendarDay->minutes
			, 'cost'=>$calendarDay->cost
			, 'hourStartInterval'=>$calendarDay->hourStartInterval
			, 'minuteStartInterval'=>$calendarDay->minuteStartInterval
			, 'seasonName'=>$calendarDay->seasonName
			, 'minNumDaysDeposit'=>$calendarDay->minNumDaysDeposit
			, 'deposit'=>$calendarDay->deposit
			, 'seatMode'=>$calendarDay->seatMode
			, 'bookingLimit'=>$calendarDay->bookingLimit
		), array('id'=>$calendarDay->id), array('%s', '%s', '%d', '%d', '%f', '%d', '%d', '%s', '%d', '%f', '%d', '%d'));
		return $result;
	}
	/**
		@description if the selected day falls outside the date range, which can happen if the date 
		range is changed after selecting a day, in which case we want to clean up.
	*/
	public function cleanup(){
		$sql = "DELETE cd.*
				FROM       $this->calendarday_table_name AS cd
				INNER JOIN $this->calendar_table_name    AS c
				ON         cd.calendarid = c.id
				WHERE      c.startdate > cd.day
				OR         c.enddate < cd.day";
		return $this->wpdb->query( $sql);
	}
	
	public function deleteNamelessDays($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->calendarDay_table_name WHERE name IS NULL AND calendarId = %d", $id));
	}
	
	public function deleteBySeason($seasonName){
		if(!$seasonName){
			return $this->wpdb->query("DELETE FROM $this->calendarDay_table_name WHERE seasonName = '' OR seasonName IS NULL");
		}
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->calendarDay_table_name WHERE seasonName = %s", $seasonName) );
	}
	
	public function delete($id){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->calendarDay_table_name WHERE id = %d", $id) );
	}
}
?>