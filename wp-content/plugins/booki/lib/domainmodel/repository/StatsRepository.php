<?php
class Booki_StatsRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $order_days_table_name;
	private $order_optionals_table_name;
	private $roles_table_name;
	private $dateFormat;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->order_optionals_table_name = $wpdb->prefix . 'booki_order_optionals';
		$this->roles_table_name = $wpdb->prefix . 'booki_roles';
		$this->dateFormat = get_option('date_format');
	}
	protected function getTotal($userId = null, $period = null){
		$query = "SELECT COUNT(o.id) as total FROM $this->order_table_name as o";
		$where = array();
		if($userId !== null){
			if($hasFullControl){
				array_push($where, 'o.userId = %1$d');
			}else{
				array_push($where, 'od.projectId IN (SELECT r.projectId FROM ' . $this->roles_table_name . ' as r WHERE r.userId = %1$d )');
			}
		}
		if($period !== null){
			array_push($where, 'o.orderDate > DATE_SUB(NOW(), INTERVAL %2$d MONTH)');
		}
		if(count($where) > 0){
			$query .= " INNER JOIN $this->order_days_table_name AS od ON od.orderId = o.id";
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' GROUP BY o.orderDate';
		$result = $this->wpdb->get_results( sprintf($query, $userId, $period));
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function readOrdersMadeAggregate($userId, $pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', $period = 3){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'orderDate';
		}
		if($order === null){
			$order = 'desc';
		}
		$query = "SELECT COUNT(*) as count, o.orderDate
				FROM $this->order_table_name as o
				LEFT OUTER JOIN $this->order_days_table_name as od
				ON o.id = od.orderId
				LEFT OUTER JOIN $this->order_optionals_table_name as op
				on o.id = op.orderId
				WHERE o.orderDate > DATE_SUB(NOW(), INTERVAL %d MONTH)";
		if($userId){
			$query .= ' AND od.projectId IN (SELECT projectId FROM ' . $this->roles_table_name . " WHERE userId = $userId )";
		}
		$query .= ' GROUP BY o.orderDate ORDER BY o.' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($query, $period ) ); 
		$total = $this->getTotal($userId, $period);
		return array('result'=>$result, 'total'=>$total);
	}
	
	public function readOrdersTotalAmountAggregate($userId, $pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', $period = 3){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'orderDate';
		}
		if($order === null){
			$order = 'desc';
		}
		$query = "SELECT SUM(COALESCE(od.cost, 0) + Greatest(COALESCE(op.cost, 0), COALESCE(op.cost, 0) * op.count )) - SUM(o.refundAmount) as totalAmount, SUM(o.discount/100*od.cost + Greatest(COALESCE(op.cost, 0), COALESCE(op.cost, 0) * op.count )) as discount, o.orderDate
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					LEFT OUTER JOIN $this->order_optionals_table_name as op
					on o.id = op.orderId
					WHERE orderDate > DATE_SUB(NOW(), INTERVAL %d MONTH)";			
		if($userId){
			$query .= ' AND od.projectId IN (SELECT projectId FROM ' . $this->roles_table_name . " WHERE userId = $userId )";
		}
		$query .= ' GROUP BY o.orderDate ORDER BY o.' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($query, $period ) ); 
		$total = $this->getTotal($userId, $period);
		return array('result'=>$result, 'total'=>$total);
	}
	
	public function readOrdersRefundAmountAggregate($userId, $pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', $period = 3){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'orderDate';
		}
		if($order === null){
			$order = 'desc';
		}
		$query = "SELECT COUNT(*) as count, SUM(o.refundAmount) as refundTotal, o.orderDate
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					WHERE o.orderDate > DATE_SUB(NOW(), INTERVAL %d MONTH)
					AND o.refundAmount > 0";
		if($userId){
			$query .= ' AND od.projectId IN (SELECT projectId FROM ' . $this->roles_table_name . " WHERE userId = $userId )";
		}
		$query .= ' GROUP BY o.orderDate ORDER BY o.' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results( $this->wpdb->prepare($query, $period ) ); 
		$total = $this->getTotal($userId, $period);
		return array('result'=>$result, 'total'=>$total);
	}
	
	public function readOrdersByStatus($userId){
		$query = "SELECT COUNT(*) as count, o.status
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId";
		
		if($userId){
			$query .= ' WHERE od.projectId IN (SELECT r.projectId FROM ' . $this->roles_table_name . " as r WHERE r.userId = $userId )";
		}
		$query .= " GROUP BY o.status";
		return $this->wpdb->get_results( $query );
	}
	
	public function summary($userId){
		$query = "SELECT COUNT(o.id) as count,
					SUM(o.discount/100 * o.totalAmount) as discount
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId";
		
		if($userId){
			$query .= ' WHERE od.projectId IN (SELECT r.projectId FROM ' . $this->roles_table_name . " as r WHERE r.userId = $userId )";
		}

		$result = $this->wpdb->get_results( $query );
		if( $result ){
			return $result[0];
		}
		return $result;
	}
	
	public function readTotalAmountEarned($userId){
		$query = "SELECT SUM(o.totalAmount) - SUM(o.refundAmount) as totalAmount
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					WHERE o.status = 1";
		
		if($userId){
			$query .= ' AND od.projectId IN (SELECT r.projectId FROM ' . $this->roles_table_name . " as r WHERE r.userId = $userId )";
		}
		$result = $this->wpdb->get_results( $query );
		if( $result ){
			return (int)$result[0]->totalAmount;
		}
		return $result;
	}
}
?>