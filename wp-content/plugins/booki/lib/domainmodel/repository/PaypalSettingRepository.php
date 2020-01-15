<?php
class Booki_PaypalSettingRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $settings_table_name;
	private $settingName;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->settingName = 'Paypal';
		$this->settings_table_name = $wpdb->prefix . 'booki_settings';
	}

	public function read($projectId = null){
		$name = $projectId !== null ? $this->settingName . '_' . $projectId : $this->settingName;
		$sql = "SELECT id, 
					   name, 
					   data 
				FROM   $this->settings_table_name 
				WHERE  name = %s";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $name));
		if($result){
			$r = $result[0];
			$data = unserialize($r->data);
			if(!$data){
				$data = new stdClass();
			}
			$data->id = (int)$r->id;
			return $data;
		}
		
		$configManager = Booki_ConfigManager::getInstance();
		$config = $configManager->getConfigs();
		//default sandbox
		return new Booki_PaypalSetting(
			$config['acct1.AppId']
			, $config['acct1.UserName']
			, $config['acct1.Password']
			, $config['acct1.Signature']
			, false
			, $config['settings.Currency']
			, $config['settings.BrandName']
			, $config['settings.CustomPageStyle']
			, $config['settings.Logo']
			, $config['settings.HeaderImage']
			, $config['settings.HeaderBorderColor']
			, $config['settings.HeaderBackColor']
			, $config['settings.PayFlowColor']
			, $config['settings.CartBorderColor']
			, $config['settings.AllowBuyerNote']
			, 'Physical'
			, false
		);
	}
	
	public function insert($settings){
		 $result = $this->wpdb->insert($this->settings_table_name,  array(
			'name'=>$this->settingName
			, 'data'=>serialize($settings)
		  ), array('%s', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}

	public function update($settings){
		$result = $this->wpdb->update($this->settings_table_name,  array(
			'data'=>serialize($settings)
		), array('id'=>$settings->id), array('%s'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->settings_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id));
		return $rows_affected;
	}
	
	public function deleteByProject($projectId){
		$name = $this->settingName . '_' . $projectId;
		$sql = "DELETE FROM $this->settings_table_name WHERE name = %s";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $name));
		return $rows_affected;
	}
}
?>