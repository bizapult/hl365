<?php
class Booki_BookedCascadingItemsRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $order_cascading_item_table_name;
	private $project_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_cascading_item_table_name = $wpdb->prefix . 'booki_order_cascading_item';
		$this->project_table_name = $wpdb->prefix . 'booki_project';
	}
	
	public function read($id){
		$sql = "SELECT oci.id, oci.orderId, oci.projectId, oci.value, oci.cost, oci.cost, oci.deposit,
				oci.status, oci.handlerUserId, oci.trails,
				p.notifyUserEmailList, oci.count, p.name as projectName
				FROM $this->order_cascading_item_table_name oci
				INNER JOIN $this->project_table_name as p
				ON oci.projectId = p.id
				WHERE oci.id = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $id ) );
		if( $result ){
			$r = $result[0];
			return new Booki_BookedCascadingItem((array)$r);
		}
		return false;
	}
	
	public function readByOrder($orderId){
		$sql = "SELECT oci.id, oci.orderId, oci.projectId, oci.value, oci.cost, oci.deposit,
				oci.status, oci.handlerUserId, oci.trails, 
				p.notifyUserEmailList, oci.count, p.name as projectName
				FROM $this->order_cascading_item_table_name oci
				INNER JOIN $this->project_table_name as p
				ON oci.projectId = p.id
				WHERE oci.orderId = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql,  $orderId ));
		if ( is_array( $result) ){
			$bookedCascadingItems = new Booki_BookedCascadingItems();
			foreach($result as $r){
				$bookedCascadingItems->add(new Booki_BookedCascadingItem((array)$r));
			}
			return $bookedCascadingItems;
		}
		return false;
	}
	
	public function insert($orderId, $bookedCascadingItem){
		$fields = array(
			'projectId'=>$bookedCascadingItem->projectId
			, 'value'=>$this->encode($bookedCascadingItem->value)
			, 'cost'=>$bookedCascadingItem->cost
			, 'status'=>$bookedCascadingItem->status
			, 'orderId'=>$orderId
			, 'handlerUserId'=>$bookedCascadingItem->handlerUserId
			, 'count'=>$bookedCascadingItem->count
			, 'deposit'=>$bookedCascadingItem->deposit
			, 'trails'=>$this->encode(implode(',', $bookedCascadingItem->trails))
		  );
		  $formatStrings = array('%d', '%s', '%f', '%d', '%d', '%d', '%d', '%f', '%s');
		  if($bookedCascadingItem->id !== -1){
			  $fields['id'] = $bookedCascadingItem->id;
			  array_push($formatStrings, '%d');
		  }
		 $result = $this->wpdb->insert($this->order_cascading_item_table_name,  $fields, $formatStrings);
		 if($result !== false){
			if($bookedCascadingItem->id !== -1){
				return $bookedCascadingItem->id;
			}
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($bookedCascadingItem){
		 $result = $this->wpdb->update($this->order_cascading_item_table_name,  array(
			'value'=>$this->encode($bookedCascadingItem->value)
			, 'cost'=>$bookedCascadingItem->cost
			, 'status'=>$bookedCascadingItem->status
			, 'handlerUserId'=>$bookedCascadingItem->handlerUserId
			, 'count'=>$bookedCascadingItem->count
			, 'deposit'=>$bookedCascadingItem->deposit
			, 'trails'=>$this->encode(implode(',', $bookedCascadingItem->trails))
		  ), array('id'=>$bookedCascadingItem->id), array('%s','%f', '%d', '%d', '%d', '%f', '%s'));

		 return $result;
	}
	
	public function updateStatus($id, $status){
		 $result = $this->wpdb->update($this->order_cascading_item_table_name,  array(
			'status'=>$status
		  ), array('id'=>$id), array('%d'));

		 return $result;
	}
	
	public function updateCount($id, $count){
		 $result = $this->wpdb->update($this->order_cascading_item_table_name,  array(
			'count'=>$count
		  ), array('id'=>$id), array('%d'));

		 return $result;
	}
	
	public function updateStatusByOrderId($orderId, $status){
		 $result = $this->wpdb->update($this->order_cascading_item_table_name,  array(
			'status'=>$status
		  ), array('orderId'=>$orderId), array('%d'));
		 return $result;
	}
	
	public function setOwner($id, $userId){
		$result = $this->wpdb->update($this->order_cascading_item_table_name,  array(
			'handlerUserId'=>$userId
		), array('id'=>$id), array('%d'));
		return $result;
	}
	
	public function deleteByOrderId($orderId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_cascading_item_table_name WHERE orderId = %d", $orderId) );
	}
	
	public function delete($id){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_cascading_item_table_name WHERE id = %d", $id) );
	}
	
	public function deleteByUserId($userId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE op.* FROM $this->order_cascading_item_table_name as op 
				LEFT JOIN $this->order_table_name as o
				ON o.id = op.orderId WHERE o.userId = %d", $userId));
	}
}
?>