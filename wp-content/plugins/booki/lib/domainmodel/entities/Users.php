<?php
class Booki_Users extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_User) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_User class.');
		}
        parent::add($value);
    }
}
?>