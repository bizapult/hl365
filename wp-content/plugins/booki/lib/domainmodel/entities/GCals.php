<?php
class Booki_GCals extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_GCal) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_GCal class.');
		}
        parent::add($value);
    }
}
?>