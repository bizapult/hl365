<?php
class Booki_GCalEventsRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $gcal_events_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->gcal_events_table_name = $wpdb->prefix . 'booki_gcal_events';
	}

	public function readAll($calendarId = null){
		$query = "SELECT ge.calendarId,
						ge.eventId,
						ge.orderId,
						ge.bookedDayId
			FROM $this->gcal_events_table_name AS ge";
		$where = array();
		if(isset($calendarId)){
			array_push($where, 'ge.calendarId = ' . $calendarId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results($query);
		if ( is_array($result) ){
			return $result;
		}
		return false;
	}
	public function readByEvent($eventId){
		$sql = "SELECT ge.calendarId,
						ge.eventId,
						ge.orderId,
						ge.bookedDayId
				FROM $this->gcal_events_table_name AS ge
				WHERE ge.eventId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $eventId) );
		if( $result ){
			$r = $result[0];
			return $r;
		}
		return false;
	}
	public function orderHasEvents($orderId){
		$sql = "SELECT count(*) as count
				FROM $this->gcal_events_table_name AS ge
				WHERE ge.orderId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if( $result ){
			$r = $result[0];
			return (int)$r->count > 0;
		}
		return false;
	}
	public function readByOrder($orderId){
		$sql = "SELECT ge.calendarId,
						ge.eventId,
						ge.orderId,
						ge.bookedDayId
				FROM $this->gcal_events_table_name AS ge
				WHERE ge.orderId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if ( is_array($result) ){
			return $result;
		}
		return false;
	}
	public function readByBookedDay($bookedDayId){
		$sql = "SELECT ge.calendarId,
						ge.eventId,
						ge.orderId,
						ge.bookedDayId
				FROM $this->gcal_events_table_name AS ge
				WHERE ge.bookedDayId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $bookedDayId) );
		if( $result ){
			$r = $result[0];
			return $r;
		}
		return false;
	}
	public function insert($calendarId, $eventId, $orderId, $bookedDayId){
		 $result = $this->wpdb->insert($this->gcal_events_table_name,  array(
			'calendarId'=>$calendarId
			, 'eventId'=>$eventId
			, 'orderId'=>$orderId
			, 'bookedDayId'=>$bookedDayId
		  ), array('%s', '%s', '%d', '%d'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	public function deleteByCalendar($calendarId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_events_table_name WHERE calendarId = %s", $calendarId));
	}
	public function deleteByEvent($eventId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_events_table_name WHERE eventId = %s", $eventId));
	}
	public function deleteByBookedDay($bookedDayId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_events_table_name WHERE bookedDayId = %d", $bookedDayId));
	}
	public function deleteByOrder($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_events_table_name WHERE orderId = %d", $orderId));
	}
}
?>