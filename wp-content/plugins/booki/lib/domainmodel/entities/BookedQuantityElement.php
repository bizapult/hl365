<?php
class Booki_BookedQuantityElement extends Booki_EntityBookingElementBase{
	public $id = -1;
	public $projectId;
	public $bookingId;
	public $orderId;
	public $orderDayId;
	public $bookingDate;
	public $elementId;
	public $name;
	public $name_loc;
	public $cost;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $handlerUserId;
	public $notifyUserEmailList;
	public $projectName;
	public $deposit;
	public $quantity;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('orderDayId', $args)){
			$this->orderDayId = (int)$args['orderDayId'];
		}
		if(array_key_exists('elementId', $args)){
			$this->elementId = (int)$args['elementId'];
		}
		if(array_key_exists('bookingId', $args)){
			//helper
			$this->bookingId = (int)$args['bookingId'];
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
		if(array_key_exists('quantity', $args)){
			$this->quantity = (int)$args['quantity'];
		}
		if(array_key_exists('projectName', $args)){
			$this->projectName = (string)$args['projectName'];
		}
		if(array_key_exists('bookingDate', $args)){
			$this->bookingDate = $args['bookingDate'] instanceOf Booki_DateTime ? $args['bookingDate'] : new Booki_DateTime($args['bookingDate']);
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->init();
	}
	
	public function getCalculatedCost(){
		return $this->cost;
	}
	public function getName(){
		return $this->name_loc . ' x ' . $this->quantity;
	}
	public function getSelectedQuantity(){
		return $this->quantity - 1;
	}
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('optional_item_' . $this->name . '_name_project' . $this->projectId, $this->name);
	}
}
?>