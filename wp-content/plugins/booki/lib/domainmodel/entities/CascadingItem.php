<?php
class Booki_CascadingItem extends Booki_EntityBookingElementBase {
	public $id = -1;
	public $listId = -1;
	public $parentId = -1;
	public $value;
	public $value_loc;
	public $cost = 0;
	public $lat = 0;
	public $lng = 0;
	public $status = null;
	public $trails = array();
	public $trail;
	//count is a helper for BookedCascadingItem
	public $count;
	public $isRequired;
	public function __construct($args){
		if(array_key_exists('value', $args)){
			$this->value = $this->decode((string)$args['value']);
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('lat', $args)){
			$this->lat = (double)$args['lat'];
		}
		if(array_key_exists('lng', $args)){
			$this->lng = (double)$args['lng'];
		}
		if(array_key_exists('listId', $args)){
			$this->listId = (int)$args['listId'];
		}
		if(array_key_exists('parentId', $args) && isset($args['parentId'])){
			$this->parentId = (int)$args['parentId'];
		}
		if(array_key_exists('isRequired', $args)){
			$this->isRequired = (bool)$args['isRequired'];
		}
		if(array_key_exists('trails', $args)){
			$this->trails = $args['trails'];
			if(!is_array($args['trails'])){
				$this->trails = explode(',', (string)$args['trails']);
			}
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
		$this->value_loc = Booki_WPMLHelper::t('cascading_item_' . $this->value . '_value', $this->value);
		$this->trail = $this->getTrail();
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('cascading_item_' . $this->value . '_value', $this->value);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('cascading_item_' . $this->value . '_value');
	}
	
	public function getValuePlusFormattedCost(){
		if($this->cost > 0 && $this->parentId === -1){
			return $this->value_loc . '&nbsp;&nbsp;' . Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney($this->cost));
		}
		return $this->value_loc;
	}
	
	public function toArray(){
		return array(
			'value'=>$this->value
			,'cost'=>$this->cost
			, 'listId'=>$this->listId
			, 'parentId'=>$this->parentId
			, 'lat'=>$this->lat
			, 'lng'=>$this->lng
			, 'id'=>$this->id
		);
	}
	public function getTrail(){
		$result = array();
		if($this->trails){
			for($i = 0; $i < count($this->trails); $i++){
				$trail = $this->trails[$i];
				if($i === 0){
					array_push($result, Booki_WPMLHelper::t('cascading_list_' . $trail . '_label', $trail));
				}else{
					array_push($result, Booki_WPMLHelper::t('cascading_item_' . $trail . '_value', $trail));
				}
			}
		}
		return implode(' - ', $result);
	}
}
?>