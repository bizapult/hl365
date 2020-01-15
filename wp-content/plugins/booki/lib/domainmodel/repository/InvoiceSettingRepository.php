<?php
class Booki_InvoiceSettingRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $settings_table_name;
	private $settingName;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->settingName = 'Invoice setting';
		$this->settings_table_name = $wpdb->prefix . 'booki_settings';
	}

	public function read(){
		$sql = "SELECT id, 
					   name, 
					   data 
				FROM   $this->settings_table_name 
				WHERE  name = '$this->settingName'";
		$result = $this->wpdb->get_results($sql);
		if($result){
			$r = $result[0];
			$data = unserialize($r->data);
			if(!$data){
				$data = new stdClass();
			}
			$data->id = (int)$r->id;
			return $data;
		}
		return false;
	}
	
	public function insert($settings){
		 $result = $this->wpdb->insert($this->settings_table_name,  array(
			'name'=>$this->settingName
			, 'data'=>serialize($settings)
		  ), array('%s', '%s'));
		  
		 if($result !== false){
			$settings->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($settings){
		$result = $this->wpdb->update($this->settings_table_name,  array(
			'data'=>serialize($settings)
		), array('id'=>$settings->id), array('%s'));
		$settings->updateResources();
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources();
		$sql = "DELETE FROM $this->settings_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	public function deleteResources(){
		$settings = $this->read();
		if($settings){
			$settings->deleteResources();
		}
	}
}
?>