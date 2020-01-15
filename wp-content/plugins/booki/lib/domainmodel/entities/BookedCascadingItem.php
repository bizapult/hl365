<?php
class Booki_BookedCascadingItem extends Booki_EntityBookingElementBase {
	public $id = -1;
	public $projectId;
	public $orderId;
	public $cost;
	public $value;
	public $value_loc;
	public $trails = array();
	public $trail;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $handlerUserId;
	public $notifyUserEmailList;
	public $projectName;
	public $count = 0;
	public $deposit;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('value', $args)){
			$this->value = $this->decode((string)$args['value']);
		}
		if(array_key_exists('trails', $args)){
			$this->trails = $args['trails'];
			if(!is_array($args['trails'])){
				$this->trails = explode(',', $this->decode((string)$args['trails']));
			}
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('deposit', $args)){
			$this->deposit = (double)$args['deposit'];
		}
		if(array_key_exists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('handlerUserId', $args)){
			$this->handlerUserId = (int)$args['handlerUserId'];
		}
		if(array_key_exists('notifyUserEmailList', $args)){
			$this->notifyUserEmailList = trim((string)$args['notifyUserEmailList']);
		}
		if(array_key_exists('projectName', $args)){
			$this->projectName = (string)$args['projectName'];
		}
		if(array_key_exists('count', $args)){
			$this->count = (int)$args['count'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->init();
	}
	
	protected function init(){
		$this->value_loc = Booki_WPMLHelper::t('cascading_item_' . $this->value . '_value', $this->value);
		$this->trail = $this->getTrail();
	}
	
	public function getCalculatedCost(){
		if($this->count > 0){
			return $this->cost * $this->count;
		}
		return $this->cost;
	}
	
	public function getName(){
		if($this->count > 0){
			return $this->value_loc . ' x ' . $this->count;
		}
		return $this->value_loc;
	}
	
	public function getTrail(){
		$result = array();
		if($this->trails){
			$length = count($this->trails);
			for($i = 0; $i < $length; $i++){
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