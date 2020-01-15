<?php
class Booki_BookedOptional extends Booki_EntityBookingElementBase{
	public $id = -1;
	public $projectId;
	public $orderId;
	public $name;
	public $name_loc;
	public $cost;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $handlerUserId;
	public $notifyUserEmailList;
	public $projectName;
	public $deposit;
	//count > 0 = cost * count
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
	
	public function getCalculatedCost(){
		if($this->count > 0){
			return $this->cost * $this->count;
		}
		return $this->cost;
	}
	public function getName(){
		if($this->count > 0){
			return $this->name_loc . ' x ' . $this->count;
		}
		return $this->name_loc;
	}
	
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('optional_item_' . $this->name . '_name_project' . $this->projectId, $this->name);
	}
}
?>