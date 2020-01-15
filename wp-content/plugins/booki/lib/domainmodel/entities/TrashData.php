<?php
class Booki_TrashData extends Booki_EntityBase{
	public $id;
	public $bookedDays;
	public $bookedCascadingItems;
	public $bookedQuantityElements;
	public $bookedOptionals;
	public function __construct($args){
		if(array_key_exists('bookedDays', $args)){
			$this->bookedDays = (int)$args['bookedDays'];
		}
		if(array_key_exists('bookedCascadingItems', $args)){
			$this->bookedCascadingItems = (int)$args['bookedCascadingItems'];
		}
		if(array_key_exists('bookedQuantityElements', $args)){
			$this->bookedQuantityElements = (int)$args['bookedQuantityElements'];
		}
		if(array_key_exists('bookedOptionals', $args)){
			$this->bookedOptionals = (int)$args['bookedOptionals'];
		}
	}
	
	public function toArray(){
		return array(
			'bookedDays'=>$this->bookedDays
			, 'bookedCascadingItems'=>$this->bookedCascadingItems
			, 'bookedQuantityElements'=>$this->bookedQuantityElements
			, 'bookedOptionals'=>$this->bookedOptionals
		);
	}
}
?>