<?php
class Booki_BookedDays extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_BookedDay) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_BookedDay class.');
		}
        parent::add($value);
    }
}
?>