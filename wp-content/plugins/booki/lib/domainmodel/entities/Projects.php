<?php
class Booki_Projects extends Booki_CollectionBase
{
	public $total = 0;
    public function add($value) {
		if (! ($value instanceOf Booki_Project) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Project class.');
		}
		parent::add($value);
    }
	public function getProjectById($id){
		$items = $this->get_items();
		foreach($items as $key=>$value) {
			if($value->id === $id){
				return $value;
			}
		}
		return false;
	}
}
?>