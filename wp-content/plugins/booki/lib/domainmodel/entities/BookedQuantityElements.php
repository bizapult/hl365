<?php
class Booki_BookedQuantityElements extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_BookedQuantityElement) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_BookedQuantityElement class.');
		}
        parent::add($value);
    }
}
?>