<?php
class Booki_QuantityElements extends Booki_CollectionBase{
	public function add($value) {
		if (!($value instanceOf Booki_QuantityElement)){
			throw new Exception('Invalid value. Expected an instance of the Booki_QuantityElement class.');
		}
        parent::add($value);
    }
}
?>