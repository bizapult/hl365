<?php
class Booki_RemindersRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $reminder_table_name;
	private $order_days_table_name;
	private $roles_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->reminder_table_name = $wpdb->prefix . 'booki_reminders';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->roles_table_name = $wpdb->prefix . 'booki_roles';
	}
	protected function getTotal($userId){
		$where = array();
		$query = "SELECT count(rm.id) as total
					FROM $this->reminder_table_name AS rm";
		if($userId !== null){
			array_push($where, 'rm.orderId IN (SELECT od.orderId FROM ' . $this->order_days_table_name . ' as od 
							INNER JOIN ' . $this->roles_table_name . ' as r
							ON od.projectId = r.projectId 
							WHERE r.userId = %1$d
						)');
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
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc', $userId = null){
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
		if($orderBy === null || (strtolower($orderBy) != 'sentDate' && strtolower($orderBy) != 'id')){
			$orderBy = 'id';
		}

		if($order === null || (strtolower($order) != 'asc' && strtolower($order) != 'desc')){
			$order = 'asc';
		}
		$query = "SELECT rm.id,
						rm.orderId,
						rm.firstname,
						rm.lastname,
						rm.email,
						rm.sentDate
			FROM $this->reminder_table_name AS rm";
		$where = array();
		if($userId !== null){
			array_push($where, 'rm.orderId IN (SELECT od.orderId FROM ' . $this->order_days_table_name . ' as od 
											INNER JOIN ' . $this->roles_table_name . ' as r
											ON od.projectId = r.projectId 
											WHERE r.userId = %1$d
										)');
		}

		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results( sprintf($query, $userId));
		if (is_array($result) ){
			$emailReminders = new Booki_EmailReminders();
			$total = $this->getTotal($userId);
			foreach($result as $r){
				$emailReminders->total = $total;
				$emailReminders->add(new Booki_EmailReminder(array(
					'firstname'=>$r->firstname
					, 'lastname'=>$r->lastname
					, 'email'=>$r->email
					, 'sentDate'=>new Booki_DateTime((string)$r->sentDate)
					, 'orderId'=>(int)$r->orderId
					, 'id'=> (int)$r->id
				)));
			}
			return $emailReminders;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT rm.id,
						rm.orderId,
						rm.firstname,
						rm.lastname,
						rm.email,
						rm.sentDate
				FROM $this->reminder_table_name AS rm
				WHERE rm.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_EmailReminder(array(
				'firstname'=>$r->firstname
				, 'lastname'=>$r->lastname
				, 'email'=>$r->email
				, 'sentDate'=>new Booki_DateTime((string)$r->sentDate)
				, 'orderId'=>(int)$r->orderId
				, 'id'=> (int)$r->id
			));
		}
		return false;
	}
	public function readByOrder($orderId){
		$sql = "SELECT rm.id,
						rm.orderId,
						rm.firstname,
						rm.lastname,
						rm.email,
						rm.sentDate
				FROM $this->reminder_table_name AS rm
				WHERE rm.orderId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId) );
		if(is_array($result)){
			return $result;
		}
		return false;
	}
	
	public function insert($emailReminder){
		 $result = $this->wpdb->insert($this->reminder_table_name,  array(
			'orderId'=>$emailReminder->orderId
			, 'firstname'=>$emailReminder->firstname
			, 'lastname'=>$emailReminder->lastname
			, 'email'=>$emailReminder->email
			, 'sentDate'=>$emailReminder->sentDate->format(BOOKI_DATEFORMAT)
		  ), array('%d', '%s', '%s', '%s', '%s', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}

	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->reminder_table_name WHERE id = %s", $id));
	}
	public function deleteByOrder($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->reminder_table_name WHERE orderId = %s", $orderId));
	}
	public function deleteAll(){
		return $this->wpdb->query("DELETE FROM $this->reminder_table_name");
	}
}
?>