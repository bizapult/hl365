<?php
class Booki_QuantityElement extends Booki_EntityBookingElementBase {
	public $id;
	public $projectId;
	public $calendarId;
	public $calendarDayId;
	public $calendarDayIdList;
	public $name;
	public $name_loc;
	public $quantity;
	public $cost;
	public $quantityElementItems;
	public $displayMode;
	public $bookingMode;
	public $isRequired = false;
	public $selectedQuantity = 0;
	public $status = null;
	public $bookingDate;
	public $hourStart = null;
	public $minuteStart = null;
	public $hourEnd = null;
	public $minuteEnd = null;
	public $bookedQuantityCount = 0;
	public $bookingId;
	private $args;
	public function __construct($args){
		$this->args = $args;
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('calendarId', $args)){
			$this->calendarId = (int)$args['calendarId'];
		}
		if(array_key_exists('bookingId', $args)){
			//helper
			$this->bookingId = (int)$args['bookingId'];
		}
		if(array_key_exists('calendarDayId', $args)){
			$this->calendarDayId = $args['calendarDayId'] !== null ? (int)$args['calendarDayId'] : null;
		}
		if(array_key_exists('calendarDayIdList', $args)){
			if(!is_array($args['calendarDayIdList'])){
				$this->calendarDayIdList =  isset($args['calendarDayIdList']) ? array_map('intval', explode(',', $args['calendarDayIdList'])) : array();
			}else{
				$this->calendarDayIdList = (array)$args['calendarDayIdList'];
			}
		}
		if(array_key_exists('name', $args)){
			$this->name = $this->decode((string)$args['name']);
		}
		if(array_key_exists('quantity', $args)){
			$this->quantity = (int)$args['quantity'];
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('displayMode', $args)){
			$this->displayMode = (int)$args['displayMode'];
		}
		if(array_key_exists('bookingMode', $args)){
			$this->bookingMode = (int)$args['bookingMode'];
		}
		if(array_key_exists('isRequired', $args)){
			$this->isRequired = (bool)$args['isRequired'];
		}
		if(array_key_exists('bookedQuantityCount', $args)){
			$this->bookedQuantityCount = (int)$args['bookedQuantityCount'];
		}
		if(array_key_exists('bookingDate', $args)){
			$this->bookingDate = $args['bookingDate'] instanceOf Booki_DateTime ? $args['bookingDate'] : new Booki_DateTime($args['bookingDate']);
		}
		if(array_key_exists('hourStart', $args) && $args['hourStart'] !== null){
			$this->hourStart = (int)$args['hourStart'] ;
		}
		if(array_key_exists('minuteStart', $args) && $args['minuteStart'] !== null){
			$this->minuteStart = (int)$args['minuteStart'];
		}
		if(array_key_exists('hourEnd', $args) &&  $args['hourEnd'] !== null){
			$this->hourEnd = (int)$args['hourEnd'];
		}
		if(array_key_exists('minuteEnd', $args) && $args['minuteEnd'] !== null){
			$this->minuteEnd = (int)$args['minuteEnd'];
		}
		if(array_key_exists('selectedQuantity', $args)){
			$this->selectedQuantity = (int)$args['selectedQuantity'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		if(array_key_exists('status', $args) && isset($args['status'])){
			//helper to bridge BookedCascadingItem
			$this->status = (int)$args['status'];
		}
		
		$this->updateResources();
		$this->init();
		$this->quantityElementItems = new Booki_QuantityElementItems();
	}
	public function params(){
		return $this->args;
	}
	public function toArray(){
		return array(
			'projectId'=>$this->projectId
			, 'calendarId'=>$this->calendarId
			, 'calendarDayId'=>$this->calendarDayId
			, 'name'=>$this->name_loc
			, 'quantity'=>$this->quantity
			, 'cost'=>$this->cost
			, 'displayMode'=>$this->displayMode
			, 'bookingMode'=>$this->bookingMode
			, 'isRequired'=>$this->isRequired
			, 'quantityElementItems'=>$this->quantityElementItems->toArray()
			, 'calendarDayIdList'=>$this->calendarDayIdList
			, 'quantityCount'=>$this->getQuantity()
			, 'id'=>$this->id
		);
	}
	
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('quantity_element_' . $this->name . '_name', $this->name);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('quantity_element_' . $this->name . '_name', $this->name);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('quantity_element_' . $this->name . '_name');
	}
	
	public function hasTime(){
		if($this->hourStart !== null || $this->minuteStart !== null){
			return true;
		}
		return false;
	}
	public function compareTime($t2){
		if(!$this->hasTime() || !$t2->hasTime()){
			return false;
		}
		return (($this->hourStart === $t2->hourStart && $this->minuteStart === $t2->minuteStart) &&
				($this->hourEnd === $t2->hourEnd && $this->minuteEnd === $t2->minuteEnd));
	}
	public function isSame($quantityElement){
		if($this->id !== $quantityElement->id){
			return false;
		}

		if($this->bookingDate && $quantityElement->bookingDate){
			if(!Booki_DateHelper::daysAreEqual($this->bookingDate, $quantityElement->bookingDate)){
				return false;
			}
		}

		if($this->hasTime() && $quantityElement->hasTime()){
			if(!$this->compareTime($quantityElement)){
				return false;
			}
		}
		return true;
	}
	public function hasQuantity($selectedQuantity = null){
		$selectedQuantity =  $this->getSelectedQuantity();
		$count = $this->getBookedQuantityCount();
		return $this->quantity >= ($count + $selectedQuantity);
	}
	
	public function isDayBased(){
		return isset($this->calendarDayId) && $this->calendarDayId !== null;
	}
	
	public function getSelectedQuantity($selectedQuantity = null){
		if($selectedQuantity === null){
			$selectedQuantity = $this->selectedQuantity;
		}
		return $selectedQuantity + 1;
	}
	
	public function getBookedQuantityCount(){
		if($this->bookingMode === Booki_QuantityElementBookingMode::FIXED){
			return 0;
		}
		return $this->bookedQuantityCount;
	}

	public function setQuantityCount($value){
		$this->bookedQuantityCount = $value;
	}
	
	public function getQuantity(){
		if($this->bookingMode === Booki_QuantityElementBookingMode::FIXED){
			return $this->quantity;
		}
		return $this->quantity - $this->bookedQuantityCount;
	}
	
	public function getCost(){
		$cost = $this->cost;
		foreach($this->quantityElementItems as $quantityElementItem){
			if($quantityElementItem->elementId === $this->id && $quantityElementItem->quantityIndex === $this->selectedQuantity){
				$cost = $quantityElementItem->cost;
				break;
			}
		}
		return $cost;
	}
}
?>