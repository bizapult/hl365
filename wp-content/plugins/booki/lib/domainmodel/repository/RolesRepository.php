<?php
class Booki_RolesRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $roles_table_name;
	private $user_table_name;
	private $project_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->roles_table_name = $wpdb->prefix . 'booki_roles';
		$this->user_table_name =  $wpdb->users;
		$this->project_table_name = $wpdb->prefix . 'booki_project';
	}
	protected function getTotal($projectId, $userId){
		$where = array();
		$query = "SELECT count(r.id) as total
				  FROM $this->roles_table_name AS r";
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'r.projectId = %1$d');
		}
		if($userId !== null){
			array_push($where, 'r.userId = %2$d');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( sprintf($query, $projectId, $userId));
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'desc', $projectId = null, $userId = null){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		
		$query = "SELECT r.id,
				   r.userId,
				   r.projectId,
				   p.name AS projectName,
				   r.role,
				   u.user_login AS username,
				   u.user_email AS email
			FROM $this->roles_table_name AS r
			INNER JOIN $this->project_table_name AS p ON p.id = r.projectId
			INNER JOIN $this->user_table_name AS u ON r.userId = u.ID";
		$where = array();
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'r.projectId = %1$d');
		}
		if($userId !== null){
			array_push($where, 'r.userId = %2$d');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY r.' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		if(count($where) > 0){
			$query = $this->wpdb->prepare($query, $projectId, $userId);
		}
		$result = $this->wpdb->get_results($query);
		if ( is_array($result) ){
			$roles = new Booki_Roles();
			$total = $this->getTotal($projectId, $userId);
			foreach($result as $r){
				$roles->total = $total;
				$roles->add( new Booki_Role((array)$r));
			}
			return $roles;
		}
		return false;
	}
	public function read($id){
		$sql = "SELECT id, userId, projectId, role FROM $this->roles_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_Role((array)$r);
		}
		return false;
	}
	public function readByUser($id){
		$sql = "SELECT id, userId, projectId, role FROM $this->roles_table_name WHERE userId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if ( is_array($result) ){
			$roles = new Booki_Roles();
			foreach($result as $r){
				$roles->add( new Booki_Role((array)$r));
			}
			return $roles;
		}
		return false;
	}
	public function readByProject($projectId){
		$sql = "SELECT id, userId, projectId, role FROM $this->roles_table_name WHERE projectId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if ( is_array($result) ){
			$roles = new Booki_Roles();
			foreach($result as $r){
				$roles->add( new Booki_Role((array)$r));
			}
			return $roles;
		}
		return false;
	}
	public function insert($role){
		 $result = $this->wpdb->insert($this->roles_table_name,  array(
			'userId'=>$role->userId
			, 'projectId'=>$role->projectId
			, 'role'=>$role->role
		  ), array('%d', '%d', '%d'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	public function update($role){
		$result = $this->wpdb->update($this->roles_table_name,  array(
			'role'=>$role->role
			, 'projectId'=>$role->projectId
		), array('id'=>$role->id), array('%d', '%d'));
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->roles_table_name WHERE id = %d", $id));
	}
	public function deleteByProject($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->roles_table_name WHERE projectId = %d", $id));
	}
}
?>