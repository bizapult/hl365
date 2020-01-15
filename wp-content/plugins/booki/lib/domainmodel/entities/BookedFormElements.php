<?php
class Booki_BookedFormElements extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_BookedFormElement) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_BookedFormElement class.');
		}
        parent::add($value);
    }
}
?>