<?php
class Booki_Orders extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_Order) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Order class.');
		}
        parent::add($value);
    }
}
?>