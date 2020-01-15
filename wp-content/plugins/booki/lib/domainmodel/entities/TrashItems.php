<?php
class Booki_TrashItems extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_Trash) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Trash class.');
		}
        parent::add($value);
    }
}
?>