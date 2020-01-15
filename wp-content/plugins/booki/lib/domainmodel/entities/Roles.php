<?php
class Booki_Roles extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_Role) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Role class.');
		}
        parent::add($value);
    }
	public function getProjectIdList(){
		$list = array();
		$items = $this->get_items();
		foreach($items as $key=>$value) {
			array_push($list, $value->projectId);
		}
		return $list;
	}
}
?>