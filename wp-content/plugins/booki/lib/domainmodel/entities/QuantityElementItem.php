<?php
class Booki_QuantityElementItem extends Booki_EntityBookingElementBase {
	public $id;
	public $elementId;
	public $quantityIndex;
	public $cost;
	public function __construct($args){
		if(array_key_exists('elementId', $args)){
			$this->elementId = (int)$args['elementId'];
		}
		if(array_key_exists('quantityIndex', $args)){
			$this->quantityIndex = (int)$args['quantityIndex'];
		}
		if(array_key_exists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'elementId'=>$this->elementId
			, 'quantityIndex'=>$this->quantityIndex
			, 'cost'=>$this->cost
			, 'id'=>$this->id
		);
	}
}
?>