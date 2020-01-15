<?php
class Booki_BookingInfos extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_BookingInfo) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_BookingInfo class.');
		}
        parent::add($value);
    }
}
?>