<?php
class Booki_QuantityElementRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $quantity_element_table_name;
	private $quantity_order_element_table_name;
	private $quantity_element_item_table_name;
	private $quantity_element_calendar_table_name;
	private $quantity_element_calendarday_table_name;
	private $order_days_table_name;
	private $calendarday_table_name;
	private $calendar_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->wpdb->query('SET SESSION group_concat_max_len = 1000000');
		$this->quantity_element_table_name = $wpdb->prefix . 'booki_quantity_element';
		$this->quantity_order_element_table_name = $wpdb->prefix . 'booki_order_quantity_element';
		$this->quantity_element_item_table_name = $wpdb->prefix . 'booki_quantity_element_item';
		$this->quantity_element_calendar_table_name = $wpdb->prefix . 'booki_quantity_element_calendar';
		$this->quantity_element_calendarday_table_name = $wpdb->prefix . 'booki_quantity_element_calendarday';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->calendarday_table_name = $wpdb->prefix . 'booki_calendar_day';
		$this->calendar_table_name = $wpdb->prefix . 'booki_calendar';
	}
	
	public function readAllByProjectId($id){
		$sql = "SELECT qe.id,
						qe.projectId,  
						qe.quantity, 
						qe.name, 
						qe.cost, 
						qe.displayMode, 
						qe.bookingMode, 
						qe.isRequired, 
						qec.calendarId, 
						qecd.calendarDayId,
						(SELECT GROUP_CONCAT(DISTINCT calendarDayId SEPARATOR ',')  FROM $this->quantity_element_calendarday_table_name WHERE quantityElementId = qe.id) as calendarDayIdList
		FROM            $this->quantity_element_table_name AS qe
		LEFT OUTER JOIN $this->quantity_element_calendar_table_name AS qec
		ON              qe.id = qec.quantityElementId
		LEFT OUTER JOIN $this->quantity_element_calendarday_table_name AS qecd
		ON              qe.id = qecd.quantityElementId
		WHERE           qe.projectId = %d
		GROUP BY 		qe.id
		ORDER BY        qe.id DESC";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		$quantityElements = new Booki_QuantityElements();
		if ( is_array($result) ){
			foreach($result as $r){
				$quantityElement = new Booki_QuantityElement((array)$r);
				$quantityElement->quantityElementItems = $this->readItems($quantityElement->id);
				$quantityElements->add($quantityElement);
			}
		}
		return $quantityElements;
	}
	
	public function readAllBookedQuantitiesByProjectId($id){
		$sql = "SELECT DISTINCT qe.id, 
						qe.projectId, 
						od.bookingDate, 
						od.hourStart, 
						od.minuteStart, 
						od.hourEnd, 
						od.minuteEnd, 
						qe.quantity,
						qoe.quantity AS selectedQuantity,
						qe.name, 
						qe.cost, 
						qe.displayMode, 
						qe.bookingMode, 
						qe.isRequired, 
						qec.calendarId, 
						qecd.calendarDayId,
						(SELECT GROUP_CONCAT(DISTINCT calendarDayId SEPARATOR ',')  FROM $this->quantity_element_calendarday_table_name WHERE quantityElementId = qe.id) as calendarDayIdList
		FROM            $this->quantity_order_element_table_name AS qoe 
		INNER JOIN      $this->order_days_table_name AS od 
		ON              qoe.orderDayId = od.id
		INNER JOIN      $this->quantity_element_table_name AS qe 
		ON              qoe.elementid = qe.id 
		LEFT OUTER JOIN $this->quantity_element_calendar_table_name AS qec 
		ON              qe.id = qec.quantityElementId 
		LEFT OUTER JOIN $this->quantity_element_calendarday_table_name AS qecd 
		ON              qe.id = qecd.quantityElementId 
		WHERE           od.projectId = %d
		ORDER BY        qoe.orderDayId DESC";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		$quantityElements = new Booki_QuantityElements();
		if ( is_array($result) ){
			foreach($result as $r){
				$quantityElement = new Booki_QuantityElement((array)$r);
				$quantityElement = $this->appendBookedQuantityTimeslotCount($quantityElement);
				$quantityElements->add($quantityElement);
			}
		}
		return $quantityElements;
	}
	protected function appendBookedQuantityTimeslotCount($quantityElement){
		if($quantityElement->hasTime()){
			$sql = "SELECT Sum(qoe.quantity) AS bookedQuantityCount
					FROM   $this->quantity_order_element_table_name AS qoe 
						   INNER JOIN $this->order_days_table_name AS od 
								   ON qoe.orderDayId = od.id 
					WHERE  qoe.elementid = %d 
						   AND od.bookingdate = %s 
						   AND od.hourstart = %d 
						   AND od.minutestart = %d 
						   AND od.hourend = %d 
						   AND od.minuteend = %d";
			if($quantityElement->isDayBased()){
				$sql = "SELECT     Sum(qoe.quantity) AS bookedQuantityCount
						FROM       $this->quantity_order_element_table_name AS qoe 
						INNER JOIN $this->quantity_element_calendarday_table_name AS qecd 
						ON         qecd.calendardayid = qecd.calendardayid 
						INNER JOIN $this->order_days_table_name AS od 
						ON         qoe.orderDayId = od.id 
						WHERE      qoe.elementid = %d 
						AND        od.bookingdate = %s 
						AND        od.hourstart = %d 
						AND        od.minutestart = %d 
						AND        od.hourend = %d 
						AND        od.minuteend = %d";
			}
			$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $quantityElement->id, $quantityElement->bookingDate, 
					$quantityElement->hourStart, $quantityElement->minuteStart, $quantityElement->hourEnd, $quantityElement->minuteEnd
			));
		}else{
			$result = null;
			if($quantityElement->bookingMode === Booki_QuantityElementBookingMode::PER_ENTIRE_BOOKING_PERIOD){
				$sql = "SELECT SUM(qoe.quantity)  AS bookedQuantityCount 
						FROM   $this->quantity_order_element_table_name AS qoe 
						WHERE qoe.elementId = %d";
				$result = $this->wpdb->get_results($this->wpdb->prepare( $sql, $quantityElement->id));
			}else if($quantityElement->bookingMode === Booki_QuantityElementBookingMode::PER_DAY){
				$sql = "SELECT SUM(qoe.quantity) AS bookedQuantityCount 
						FROM   $this->quantity_order_element_table_name AS qoe 
							   INNER JOIN $this->order_days_table_name AS od 
									   ON qoe.orderDayId = od.id 
						WHERE  qoe.elementId = %d 
							   AND od.bookingdate = %s";
				$result = $this->wpdb->get_results($this->wpdb->prepare( $sql, $quantityElement->id, $quantityElement->bookingDate));
			}
		}
		
		if($result){
			$r = $result[0];
			$quantityElement->bookedQuantityCount = (int)$r->bookedQuantityCount;
		}
		return $quantityElement;
	}
	public function readAllByCalendarId($id){
		$sql = "SELECT qe.id,
			   qe.projectId,
			   qe.quantity,
			   qe.name,
			   qe.cost,
			   qe.displayMode,
			   qe.bookingMode,
			   qe.isRequired,
			   qec.calendarId,
			   (SELECT GROUP_CONCAT(DISTINCT calendarDayId SEPARATOR ',')  FROM $this->quantity_element_calendarday_table_name WHERE quantityElementId = qe.id) as calendarDayIdList
		FROM   $this->quantity_element_table_name AS qe
			   INNER JOIN $this->quantity_element_calendar_table_name AS qec
					   ON qe.id = qec.quantityElementId
		WHERE  qec.calendarid = %d
		ORDER  BY qe.id desc";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if ( is_array($result) ){
			$quantityElements = new Booki_QuantityElements();
			foreach($result as $r){
				$quantityElement = new Booki_QuantityElement((array)$r);
				$quantityElement->quantityElementItems = $this->readItems($quantityElement->id);
				$quantityElements->add($quantityElement);
			}
			return $quantityElements;
		}
		return false;
	}
	
	public function readAllByCalendarDays($calendardaysIdList){
		$list = implode(',', $calendarDaysIdList);
		$sql = "SELECT DISTINCT qe.id,
                qe.projectId,
                qe.quantity,
                qe.name,
                qe.cost,
                qe.displayMode,
                qe.bookingMode,
                qe.isRequired,
                qec.calendarDayId,
				(SELECT GROUP_CONCAT(DISTINCT calendarDayId SEPARATOR ',')  FROM $this->quantity_element_calendarday_table_name WHERE quantityElementId = qe.id) as calendarDayIdList
		FROM   $this->quantity_element_table_name AS qe
			   INNER JOIN $this->quantity_element_calendarday_table_name AS qec
					   ON qe.id = qec.quantityElementId
		WHERE  qec.calendardayid IN ( $list )
		ORDER  BY qe.id desc";
		$result = $this->wpdb->get_results($sql);
		if ( is_array($result) ){
			$quantityElements = new Booki_QuantityElements();
			foreach($result as $r){
				$quantityElement = new Booki_QuantityElement((array)$r);
				$quantityElement->quantityElementItems = $this->readItems($quantityElement->id);
				$quantityElements->add($quantityElement);
			}
			return $quantityElements;
		}
		return false;
	}
	
	public function readAllByCalendarDayId($id){
		$sql = "SELECT qe.id,
			   qe.projectId,
			   qe.quantity,
			   qe.name,
			   qe.cost,
			   qe.displayMode,
			   qe.bookingMode,
			   qe.isRequired,
			   qec.calendarDayId,
			   (SELECT GROUP_CONCAT(DISTINCT calendarDayId SEPARATOR ',')  FROM $this->quantity_element_calendarday_table_name WHERE quantityElementId = qe.id) as calendarDayIdList
		FROM   $this->quantity_element_table_name AS qe
			   INNER JOIN $this->quantity_element_calendarday_table_name AS qec
					   ON qe.id = qec.quantityElementId
		WHERE  qec.calendarDayId = %d
		ORDER  BY qe.id desc";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if ( is_array($result) ){
			$quantityElements = new Booki_QuantityElements();
			foreach($result as $r){
				$quantityElement = new Booki_QuantityElement((array)$r);
				$quantityElement->quantityElementItems = $this->readItems($quantityElement->id);
				$quantityElements->add($quantityElement);
			}
			return $quantityElements;
		}
		return false;
	}
	
	public function readItems($id){
		$sql = "SELECT id,
			   cost,
			   elementId,
			   quantityIndex
		FROM   $this->quantity_element_item_table_name
		WHERE  elementId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );

		if ( is_array($result) ){
			$quantityElementItems = new Booki_QuantityElementItems();
			foreach($result as $r){
				$quantityElementItems->add(new Booki_QuantityElementItem((array)$r));
			}
			return $quantityElementItems;
		}
		return new Booki_QuantityElementItems();
	}
	
	public function readQuantityElement($id){
		$sql = "SELECT qe.id,
			   qe.projectId,
			   qe.quantity,
			   qe.name,
			   qe.cost,
			   qe.displayMode,
			   qe.bookingMode,
			   qe.isRequired,
			   qec.calendarId,
			   qecd.calendarDayId,
			   (SELECT GROUP_CONCAT(DISTINCT calendarDayId SEPARATOR ',')  FROM $this->quantity_element_calendarday_table_name WHERE quantityElementId = qe.id) as calendarDayIdList
		FROM   $this->quantity_element_table_name AS qe
			   LEFT OUTER JOIN $this->quantity_element_calendar_table_name AS qec
							ON qe.id = qec.quantityElementId
			   LEFT OUTER JOIN $this->quantity_element_calendarday_table_name AS qecd
							ON qe.id = qecd.quantityElementId
		WHERE  qe.id = %d ";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			$r = $result[0];
			$quantityElement = new Booki_QuantityElement((array)$r);
			$quantityElement->quantityElementItems = $this->readItems($quantityElement->id);
			return $quantityElement;
		}
		return false;
	}
	
	public function readQuantityElementItem($id){
		$sql = "SELECT id,
			   cost,
			   elementId,
			   quantityIndex
		FROM   $this->quantity_element_item_table_name
		WHERE  elementId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if($result){
			$r = $result[0];
			return new Booki_QuantityElementItem((array)$r);
		}
		return new Booki_QuantityElementItems();
	}
	
	public function insertQuantityElement($quantityElement){
		$result = $this->wpdb->insert($this->quantity_element_table_name,  array(
			'projectId'=>$quantityElement->projectId 
			, 'name'=>$this->encode($quantityElement->name)
			, 'quantity'=>$quantityElement->quantity
			, 'cost'=>$quantityElement->cost
			, 'displayMode'=>$quantityElement->displayMode
			, 'bookingMode'=>$quantityElement->bookingMode
			, 'isRequired'=>$quantityElement->isRequired
		), array('%d', '%s', '%d', '%f', '%d', '%d', '%d'));
		  
		if($result !== false){
			$quantityElement->updateResources();
			$newId = $this->wpdb->insert_id;
			if($quantityElement->calendarId != null){
				$this->wpdb->insert($this->quantity_element_calendar_table_name,  array(
					'quantityElementId'=>$newId
					, 'calendarId'=>$quantityElement->calendarId
				), array('%d', '%d'));
			}else if(is_array($quantityElement->calendarDayIdList)){
				foreach((array)$quantityElement->calendarDayIdList as $calendarDayId){
					$this->wpdb->insert($this->quantity_element_calendarday_table_name,  array(
						'quantityElementId'=>$newId
						, 'calendarDayId'=>$calendarDayId
					), array('%d', '%d'));
				}
			}
			return $newId;
		}
		return $result;
	}
	
	public function updateQuantityElement($quantityElement){
		$result = $this->wpdb->update($this->quantity_element_table_name,  array(
			'projectId'=>$quantityElement->projectId 
			, 'name'=>$this->encode($quantityElement->name)
			, 'quantity'=>$quantityElement->quantity
			, 'cost'=>$quantityElement->cost
			, 'displayMode'=>$quantityElement->displayMode
			, 'bookingMode'=>$quantityElement->bookingMode
			, 'isRequired'=>$quantityElement->isRequired
		), array('id'=>$quantityElement->id), array('%d', '%s', '%d', '%f', '%d', '%d', '%d'));
		$quantityElement->updateResources();
		return $result;
	}
	
	public function insertQuantityElementItem($quantityElementItem){
		$result = $this->wpdb->insert($this->quantity_element_item_table_name,   array(
			'elementId'=>$quantityElementItem->elementId 
			, 'quantityIndex'=>$quantityElementItem->quantityIndex
			, 'cost'=>$quantityElementItem->cost
		), array('%d', '%d', '%f'));
		  
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	
	public function updateQuantityElementItem($quantityElementItem){
		$result = $this->wpdb->update($this->quantity_element_item_table_name,  array('cost'=>$quantityElementItem->cost), array('id'=>$quantityElementItem->id), array('%f'));
		return $result;
	}
	
	public function deleteQuantityElement($elementId){
		$this->deleteItemResources($elementId);
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_table_name WHERE id = %d", $elementId));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_item_table_name WHERE elementId = %d", $elementId));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_calendar_table_name WHERE quantityElementId = %d", $elementId));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_calendarday_table_name WHERE quantityElementId = %d", $elementId));
	}
	
	public function deleteQuantityElementItem($id){
		//deletes by element item id i.e. just a single element item
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_item_table_name WHERE id = %d", $id));
	}
	public function deleteQuantityElementItems($id){
		//deletes by element id
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_item_table_name WHERE elementId = %d", $id));
	}
	//toDO: ensure we're calling this from projects ?
	//toDO: ensure we are cloning quantity elements when duplicating a project?
	public function deleteByProject($projectId){
		$this->wpdb->query($this->wpdb->prepare("DELETE qec.* FROM $this->quantity_element_calendar_table_name as qec INNER JOIN 
													$this->quantity_element_table_name as qe 
													ON qe.id = qec.quantityElementId 
													WHERE qe.projectId = %d", $projectId));
		$this->wpdb->query($this->wpdb->prepare("DELETE qecd.* FROM $this->quantity_element_calendarday_table_name as qecd INNER JOIN 
													$this->quantity_element_table_name as qe 
													ON qe.id = qecd.quantityElementId 
													WHERE qe.projectId = %d", $projectId));
		$this->wpdb->query($this->wpdb->prepare("DELETE qei.* FROM $this->quantity_element_item_table_name as qei INNER JOIN 
													$this->quantity_element_table_name as qe 
													ON qe.id = qei.elementId 
													WHERE where qe.projectId = %d", $projectId));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_table_name WHERE projectId = %d", $projectId));
	}
	
	public function deleteBySeason($seasonName){
			$result = array();
			array_push($result, $this->wpdb->query($this->wpdb->prepare("DELETE qei.* FROM $this->quantity_element_item_table_name as qei
														INNER JOIN $this->quantity_element_calendarday_table_name as qecd 
														ON qei.elementId = qecd.quantityElementId
														INNER JOIN $this->calendarday_table_name as cd
														ON cd.id = qecd.calendarDayId
														WHERE cd.seasonName = %s", $seasonName)));
			array_push($result, $this->wpdb->query($this->wpdb->prepare("DELETE qe.* FROM $this->quantity_element_table_name as qe
														INNER JOIN $this->quantity_element_calendarday_table_name as qecd 
														ON qe.id = qecd.quantityElementId
														INNER JOIN $this->calendarday_table_name as cd
														ON cd.id = qecd.calendarDayId
														WHERE cd.seasonName = %s", $seasonName)));
			array_push($result, $this->wpdb->query($this->wpdb->prepare("DELETE qecd.* FROM $this->quantity_element_calendarday_table_name as qecd 
														INNER JOIN $this->calendarday_table_name as cd
														ON cd.id = qecd.calendarDayId
														WHERE cd.seasonName = %s", $seasonName)));
		return $result;
	}
	public function deleteItemResources($id){
		$quantityElement = $this->readQuantityElement($id);
		if($quantityElement){
			$quantityElement->deleteResources();
		}
	}
}
?>