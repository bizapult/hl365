<?php
class Booki_GCalRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $gcal_table_name;
	private $user_table_name;
	private $project_table_name;
	private $roles_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->wpdb->query('SET SESSION group_concat_max_len = 1000000');
		$this->gcal_table_name = $wpdb->prefix . 'booki_gcal';
		$this->user_table_name =  $wpdb->users;
		$this->project_table_name = $wpdb->prefix . 'booki_project';
		$this->roles_table_name = $wpdb->prefix . 'booki_roles';
	}
	protected function getTotal($userId){
		$where = array();
		$query = "SELECT count(g.id) as total
			FROM $this->gcal_table_name AS g";
		if(isset($userId)){
			array_push($where, 'g.userId = ' . $userId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( sprintf($query, $userId));
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'desc', $userId = null){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		
		$query = "SELECT g.id,
				   g.userId,
				   g.applicationName,
				   g.clientId,
				   g.clientSecret,
				   g.accessToken,
				   u.user_login AS username,
				   u.user_email AS email,
				   (SELECT GROUP_CONCAT(DISTINCT p.name SEPARATOR ',')
					FROM $this->roles_table_name AS r
					INNER JOIN $this->project_table_name AS p ON p.id = r.projectId
					WHERE r.userId = g.userId) as projectNames
			FROM $this->gcal_table_name AS g
			INNER JOIN $this->user_table_name AS u ON g.userId = u.ID";
		$where = array();
		if(isset($userId)){
			array_push($where, 'g.userId = ' . $userId);
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY g.' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results($query);
		if ( is_array($result) ){
			$gcals = new Booki_GCals();
			$total = $this->getTotal($userId);
			foreach($result as $r){
				$gcals->total = $total;
				$gcals->add( new Booki_GCal((array)$r));
			}
			return $gcals;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT g.id,
					   g.userId,
					   g.applicationName,
					   g.clientId,
					   g.clientSecret,
					   g.accessToken,
					   u.user_login AS username,
					   u.user_email AS email,
					   (SELECT GROUP_CONCAT(DISTINCT r.projectId SEPARATOR ',')
						FROM $this->roles_table_name AS r
						WHERE r.userId = g.userId) as projectIdList
				FROM $this->gcal_table_name AS g
				INNER JOIN $this->user_table_name AS u ON g.userId = u.ID
				WHERE g.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_GCal((array)$r);
		}
		return false;
	}
	public function readByUser($id){
		$sql = "SELECT g.id,
					   g.userId,
					   g.applicationName,
					   g.clientId,
					   g.clientSecret,
					   g.accessToken,
					   u.user_login AS username,
					   u.user_email AS email,
					   (SELECT GROUP_CONCAT(DISTINCT r.id SEPARATOR ',')
						FROM $this->roles_table_name AS r
						WHERE r.userId = g.userId) as projectIdList
				FROM $this->gcal_table_name AS g
				INNER JOIN $this->user_table_name AS u ON g.userId = u.ID
				WHERE g.userId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_GCal((array)$r);
		}
		return false;
	}
	public function insert($gcal){
		 $result = $this->wpdb->insert($this->gcal_table_name,  array(
			'userId'=>$gcal->userId
			, 'applicationName'=>$gcal->applicationName
			, 'clientId'=>$gcal->clientId
			, 'clientSecret'=>$gcal->clientSecret
			, 'accessToken'=>$gcal->accessToken
		  ), array('%d', '%s', '%s', '%s', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	public function update($gcal){
		$params = array();
		$placeHolders = array();
		if(isset($gcal->userId)){
			$params['userId'] = $gcal->userId;
			array_push($placeHolders, '%d');
		}
		if(isset($gcal->applicationName)){
			$params['applicationName'] = $gcal->applicationName;
			array_push($placeHolders, '%s');
		}
		if(isset($gcal->clientId)){
			$params['clientId'] = $gcal->clientId;
			array_push($placeHolders, '%s');
		}
		if(isset($gcal->clientSecret)){
			$params['clientSecret'] = $gcal->clientSecret;
			array_push($placeHolders, '%s');
		}
		if(isset($gcal->accessToken)){
			$params['accessToken'] = $gcal->accessToken;
			array_push($placeHolders, '%s');
		}
		$result = $this->wpdb->update($this->gcal_table_name,  $params, array('id'=>$gcal->id), $placeHolders);
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_table_name WHERE id = %d", $id));
	}
	public function deleteByUser($userId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->gcal_table_name WHERE userId = %d", $userId));
	}
}
?>