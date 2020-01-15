<?php
class Booki_TrashRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $trashed_table_name;
	private $trashed_project_table_name;
	private $roles_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->trashed_table_name = $wpdb->prefix . 'booki_trashed';
		$this->trashed_project_table_name = $wpdb->prefix . 'booki_trashed_project';
		$this->roles_table_name = $wpdb->prefix . 'booki_roles';
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->trashed_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	protected function getTotal($fromDate = null, $toDate = null, $userId = null){
		$where = array();
		$query = "SELECT count(t.id) as total
				FROM $this->trashed_table_name as t";
		if($fromDate !== null && $toDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			$toDate = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 't.deletionDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 't.deletionDate = CONVERT( \'%1$s\', DATETIME)');
		}
		
		if($userId !== null){
			array_push($where, 't.id IN (SELECT tp.trashId FROM ' . $this->trashed_project_table_name . ' as tp 
											INNER JOIN ' . $this->roles_table_name . ' as r
											ON r.projectId = tp.projectId
											WHERE tp.trashId = t.id AND r.userId = %3$d
										)');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( sprintf($query, $fromDate, $toDate, $userId));
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function read($id){
		$sql = "SELECT id, orderId, deletionDate, data
				FROM $this->trashed_table_name WHERE id = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $id) );
		if($result){
			$r = $result[0];
			return new Booki_Trash(array(
				'data'=>$this->unserializeData($r->data)
				, 'deletionDate'=>new Booki_DateTime((string)$r->deletionDate)
				, 'orderId'=>(int)$r->orderId
				, 'id'=>(int)$r->id
			));
		}
		return false;
	}
	
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc', $fromDate = null, $toDate = null, $userId = null){
		if($pageIndex === null){
			$pageIndex = -1;
		}else{
			$pageIndex = intval($pageIndex);
		}
		if($limit === null){
			$limit = 5;
		}
		else{
			$limit = intval($limit);
		}
		$fromDateString = null;
		$toDateString = null;
		if($orderBy === null || (strtolower($orderBy) != 'deletionDate' && strtolower($orderBy) != 'id')){
			$orderBy = 'id';
		}
		if($order === null || (strtolower($order) != 'asc' && strtolower($order) != 'desc')){
			$order = 'asc';
		}
		$query = "SELECT t.id, t.orderId, t.deletionDate, t.data
				FROM $this->trashed_table_name as t";
		$where = array();
		if($fromDate !== null && $toDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			$toDateString = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 't.deletionDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 't.deletionDate = CONVERT( \'%1$s\', DATETIME)');
		}
		if($userId !== null){
			array_push($where, 't.id IN (SELECT tp.trashId FROM ' . $this->trashed_project_table_name . ' as tp 
											INNER JOIN ' . $this->roles_table_name . ' as r
											ON r.projectId = tp.projectId
											WHERE tp.trashId = t.id AND r.userId = %3$d
										)');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results( sprintf($query, $fromDateString, $toDateString, $userId));
		$total = 0;
		if( is_array($result) ){
			$trashItems = new Booki_TrashItems();
			$total = $this->getTotal($fromDate, $toDate, $userId);
			foreach($result as $r){
				$trashItems->total = $total;
				$trashItems->add(new Booki_Trash(array(
					'data'=>$this->unserializeData($r->data)
					, 'deletionDate'=>new Booki_DateTime((string)$r->deletionDate)
					, 'orderId'=>(int)$r->orderId
					, 'id'=> (int)$r->id
				)));
			}
			return $trashItems;
		}
		return false;
	}
	
	public function insert($trash){
		 $result = $this->wpdb->insert($this->trashed_table_name,  array(
			'data'=>$this->serializeData($trash->data)
			, 'orderId'=>$trash->orderId
			, 'deletionDate'=>$trash->deletionDate->format(BOOKI_DATEFORMAT)
		  ), array('%s', '%d', '%s'));

		 if($result !== false){
			$newId = $this->wpdb->insert_id;
			foreach($trash->data->projectIdList as $projectId){
				$result = $this->wpdb->insert($this->trashed_project_table_name,  array(
					'trashId'=>$newId
					, 'projectId'=>$projectId
				), array('%d', '%d'));
			}
			return $newId;
		 }
		 return $result;
	}
	
	public function update($trash){
		$result = $this->wpdb->update($this->trashed_table_name,  array(
			'data'=>$this->serializeData($trash->data)
		), array('id'=>$trash->id), array('%s'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->trashed_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->trashed_project_table_name WHERE trashId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	
	public function deleteAll(){
		$sql = "DELETE FROM $this->trashed_table_name";
		$rows_affected = $this->wpdb->query( $sql );
		
		$sql = "DELETE FROM $this->trashed_project_table_name";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		return $rows_affected;
	}
}
?>