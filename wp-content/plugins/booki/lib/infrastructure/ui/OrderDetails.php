<?php
class Booki_OrderDetails{
	public $order;
	public $data;
	public function __construct($orderId){
		if($orderId === null){
			return;
		}
		
		$bs = new Booki_BillSettlement(array('orderId'=>$orderId));
		$this->data = $bs->data;
		$this->order = $bs->order;
	}
}
?>