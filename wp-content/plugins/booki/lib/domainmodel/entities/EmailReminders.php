<?php
class Booki_EmailReminders extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_EmailReminder) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_EmailReminder class.');
		}
        parent::add($value);
    }
}
?>