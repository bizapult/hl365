<?php
class Booki_BookedQuantityElementRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $order_quantity_element_table_name;
	private $project_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_quantity_element_table_name = $wpdb->prefix . 'booki_order_quantity_element';
		$this->project_table_name = $wpdb->prefix . 'booki_project';
	}

	public function read($id){
		$sql = "SELECT oqe.id, 
					   oqe.orderId, 
					   oqe.orderDayId, 
					   oqe.projectId, 
					   oqe.elementId, 
					   oqe.name, 
					   oqe.cost, 
					   oqe.deposit, 
					   oqe.quantity, 
					   oqe.status, 
					   oqe.handlerUserId, 
					   p.notifyUserEmailList, 
					   p.name AS projectName 
				FROM   $this->order_quantity_element_table_name oqe 
					   INNER JOIN $this->project_table_name AS p 
							   ON oqe.projectId = p.id 
				WHERE  oqe.id = %d 
				ORDER  BY p.id";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $id ) );
		if( $result ){
			$r = $result[0];
			return new Booki_BookedQuantityElement((array)$r);
		}
		return false;
	}
	
	public function readByOrder($orderId){
		$sql = "SELECT oqe.id, 
					   oqe.orderId, 
					   oqe.orderDayId, 
					   oqe.projectId, 
					   oqe.elementId, 
					   oqe.name, 
					   oqe.cost, 
					   oqe.deposit, 
					   oqe.quantity, 
					   oqe.status, 
					   oqe.handlerUserId, 
					   p.notifyUserEmailList, 
					   p.name AS projectName 
				FROM   $this->order_quantity_element_table_name AS oqe 
					   INNER JOIN $this->project_table_name AS p 
							   ON oqe.projectId = p.id 
				WHERE  oqe.orderId = %d 
				ORDER  BY p.id";
		
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql,  $orderId ));
		if ( is_array( $result) ){
			$bookedQuantityElement = new Booki_BookedQuantityElements();
			foreach($result as $r){
				$bookedQuantityElement->add(new Booki_BookedQuantityElement((array)$r));
			}
			return $bookedQuantityElement;
		}
		return false;
	}
	
	public function insert($orderId, $orderDayId, $bookedQuantityElement){
		$fields = array(
			'projectId'=>$bookedQuantityElement->projectId
			, 'elementId'=>$bookedQuantityElement->elementId
			, 'name'=>$this->encode($bookedQuantityElement->name)
			, 'cost'=>$bookedQuantityElement->cost
			, 'status'=>$bookedQuantityElement->status
			, 'orderId'=>$orderId
			, 'orderDayId'=>$orderDayId
			, 'handlerUserId'=>$bookedQuantityElement->handlerUserId
			, 'deposit'=>$bookedQuantityElement->deposit
			, 'quantity'=>$bookedQuantityElement->quantity
		);
		$formatStrings = array('%d', '%d', '%s','%f', '%d', '%d', '%d', '%d', '%f', '%d');
		if($bookedQuantityElement->id !== -1){
			$fields['id'] = $bookedQuantityElement->id;
			array_push($formatStrings, '%d');
		}
		$result = $this->wpdb->insert($this->order_quantity_element_table_name,  $fields, $formatStrings);
		if($result !== false){
			if($bookedQuantityElement->id !== -1){
				return $bookedQuantityElement->id;
			}
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	
	public function update($bookedQuantityElement){
		 $result = $this->wpdb->update($this->order_quantity_element_table_name,  array(
			'name'=>$this->encode($bookedQuantityElement->name)
			, 'cost'=>$bookedQuantityElement->cost
			, 'status'=>$bookedQuantityElement->status
			, 'handlerUserId'=>$bookedQuantityElement->handlerUserId
			, 'deposit'=>$bookedQuantityElement->deposit
			, 'quantity'=>$bookedQuantityElement->quantity
		  ), array('id'=>$bookedQuantityElement->id), array('%s','%f', '%d', '%d', '%f', '%d'));

		 return $result;
	}
	
	public function updateStatus($id, $status){
		 $result = $this->wpdb->update($this->order_quantity_element_table_name,  array(
			'status'=>$status
		  ), array('id'=>$id), array('%d'));

		 return $result;
	}
	
	public function updateStatusByOrderId($orderId, $status){
		 $result = $this->wpdb->update($this->order_quantity_element_table_name,  array(
			'status'=>$status
		  ), array('orderId'=>$orderId), array('%d'));
		 return $result;
	}
	
	public function setOwner($id, $userId){
		$result = $this->wpdb->update($this->order_quantity_element_table_name,  array(
			'handlerUserId'=>$userId
		), array('id'=>$id), array('%d'));
		return $result;
	}
	
	public function deleteByOrderId($orderId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_quantity_element_table_name WHERE orderId = %d", $orderId) );
	}
	
	public function delete($id){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_quantity_element_table_name WHERE id = %d", $id) );
	}
	
	public function deleteByUserId($userId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE oqe.* FROM $this->order_quantity_element_table_name as oqe
				LEFT JOIN $this->order_table_name as o
				ON o.id = oqe.orderId WHERE o.userId = %d", $userId));
	}
}
?>