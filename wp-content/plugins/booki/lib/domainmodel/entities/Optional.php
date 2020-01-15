<?php
class Booki_Optional extends Booki_EntityBase{
	public $id;
	public $projectId;
	public $name;
	public $name_loc;
	public $cost;
	public $status = null;
	//count for BookedOptional
	public $count = 0;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('name', $args)){
			$this->name = $this->decode((string)$args['name']);
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('status', $args) && isset($args['status'])){
			//helper to bridge BookedCascadingItem
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('count', $args)){
			$this->count = (int)$args['count'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('optional_item_' . $this->name . '_name_project' . $this->projectId, $this->name);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('optional_item_' . $this->name . '_name_project' . $this->projectId, $this->name);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('optional_item_' . $this->name . '_name_project' . $this->projectId);
	}
}
?>