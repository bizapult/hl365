<?php
class Booki_BookedCascadingItems extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_BookedCascadingItem) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_BookedCascadingItem class.');
		}
        parent::add($value);
    }
}
?>