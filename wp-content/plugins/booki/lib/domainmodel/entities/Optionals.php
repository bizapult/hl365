<?php
class Booki_Optionals extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_Optional) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Optional class.');
		}
        parent::add($value);
    }
}
?>