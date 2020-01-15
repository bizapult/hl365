<?php
class Booki_Trash extends Booki_EntityBase{
	public $id;
	public $orderId;
	public $deletionDate;
	public $data;
	public function __construct($args){
		if(array_key_exists('data', $args)){
			$this->data = $args['data'];
		}
		if(array_key_exists('deletionDate', $args)){
			$this->deletionDate = $args['deletionDate'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'deletionDate'=>$this->deletionDate
			, 'orderId'=>$this->orderId
			, 'data'=>$this->data
		);
	}
}
?>