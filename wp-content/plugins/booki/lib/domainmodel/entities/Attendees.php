<?php
class Booki_Attendees extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_Attendee) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Attendee class.');
		}
        parent::add($value);
    }
}
?>