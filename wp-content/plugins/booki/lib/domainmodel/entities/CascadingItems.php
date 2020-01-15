<?php
class Booki_CascadingItems extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_CascadingItem) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_CascadingItem class.');
		}
        parent::add($value);
    }
}
?>