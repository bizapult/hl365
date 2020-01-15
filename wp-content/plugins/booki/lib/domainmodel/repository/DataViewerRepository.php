<?php
class Booki_DataViewerRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->table_name = $wpdb->prefix . 'booki_';
	}
	protected function getTotal($tablename){
		$query = "SELECT count(*) as total
			FROM ";
		$query .=  ($this->table_name . $tablename);
		$result = $this->wpdb->get_results($query);
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function readAll($tablename, $pageIndex = -1, $limit = 50){
		$query = "SELECT *
			FROM ";
		$query .=  ($this->table_name . $tablename);
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results($query);
		
		$data = array();
		$columns = array();
		$total = 0;
		if( is_array($result) ){
			$total = $this->getTotal($tablename);
			foreach($result as $r){
				$row = array();
				foreach($r as $key=>$value){
					if(!in_array($key, $columns)){
						array_push($columns, $key);
					}
					$row[$key] = $value;
				}
				array_push($data, $row);
			}
		}
		return array('result'=>$data, 'total'=>$total);
	}
}
?>