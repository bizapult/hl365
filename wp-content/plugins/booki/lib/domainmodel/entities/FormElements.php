<?php
class Booki_FormElements extends Booki_CollectionBase{
	public $cols;
	public $rows;
	public function add($value) {
		if (! ($value instanceOf Booki_FormElement) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_FormElement class.');
		}
        parent::add($value);
    }
}
?>