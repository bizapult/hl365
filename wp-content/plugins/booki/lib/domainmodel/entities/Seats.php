<?php
class Booki_Seats extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_Seat) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Seat class.');
		}
        parent::add($value);
    }
}
?>