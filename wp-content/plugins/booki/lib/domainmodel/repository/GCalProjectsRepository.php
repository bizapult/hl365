<?php
class Booki_GCalProjectsRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $gcal_projects_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->gcal_projects_table_name = $wpdb->prefix . 'booki_gcal_projects';
	}

	public function readAll(){
		$query = "SELECT gp.calendarId,
				   gp.projectId,
				   gp.userId
			FROM $this->gcal_projects_table_name AS gp";
		$result = $this->wpdb->get_results($query);
		if ( is_array($result) ){
			return $result;
		}
		return false;
	}
	public function readByCalendar($calendarId){
		$sql = "SELECT gp.calendarId,
				   gp.projectId,
				   gp.userId
				FROM $this->gcal_projects_table_name AS gp
				WHERE gp.calendarId = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $calendarId) );
		if( $result ){
			$r = $result[0];
			return $r;
		}
		return false;
	}
	public function readByProject($projectId, $userId){
		$sql = "SELECT gp.calendarId,
				   gp.projectId,
				   gp.userId
				FROM $this->gcal_projects_table_name AS gp
				WHERE gp.projectId = %d AND gp.userId = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId, $userId) );
		if( $result ){
			$r = $result[0];
			return $r;
		}
		return false;
	}
	public function insert($calendarId, $projectId, $userId){
		 $result = $this->wpdb->insert($this->gcal_projects_table_name,  array(
			'calendarId'=>$calendarId
			, 'projectId'=>$projectId
			, 'userId'=>$userId
		  ), array('%s', '%d', '%d'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	public function deleteByCalendar($calendarId){
		$repo = new Booki_GCalEventsRepository();
		$repo->deleteByCalendar($calendarId);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_projects_table_name WHERE calendarId = %s", $calendarId));
	}
	public function deleteByProject($projectId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_projects_table_name WHERE projectId = %d", $projectId));
	}
	public function deleteByUser($userId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_projects_table_name WHERE userId = %d", $userId));
	}
}
?>