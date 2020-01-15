<?php
class Booki_QuantityElementItems extends Booki_CollectionBase{
	public function add($value) {
		if (!($value instanceOf Booki_QuantityElementItem)){
			throw new Exception('Invalid value. Expected an instance of the Booki_QuantityElementItem class.');
		}
        parent::add($value);
    }
}
?>