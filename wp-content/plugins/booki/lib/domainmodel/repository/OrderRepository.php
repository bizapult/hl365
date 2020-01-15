<?php
class Booki_OrderRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $user_table_name;
	private $usermeta_table_name;
	private $order_table_name;
	private $project_table_name;
	private $order_days_table_name;
	private $order_form_elements_table_name;
	private $order_optionals_table_name;
	private $order_cascading_item_table_name;
	private $order_quantity_element_table_name;
	private $calendar_table_name;
	private $roles_table_name;
	private $dateFormat;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->wpdb->query('SET SESSION group_concat_max_len = 1000000');
		$this->user_table_name =  $wpdb->users;
		$this->usermeta_table_name =  $wpdb->usermeta;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->project_table_name = $wpdb->prefix . 'booki_project';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->order_form_elements_table_name = $wpdb->prefix . 'booki_order_form_elements';
		$this->order_optionals_table_name = $wpdb->prefix . 'booki_order_optionals';
		$this->order_cascading_item_table_name = $wpdb->prefix . 'booki_order_cascading_item';
		$this->order_quantity_element_table_name = $wpdb->prefix . 'booki_order_quantity_element';
		$this->calendar_table_name = $wpdb->prefix . 'booki_calendar';
		$this->roles_table_name = $wpdb->prefix . 'booki_roles';
		$this->dateFormat = get_option('date_format');
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->order_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	protected function getTotal($fromDate = null, $toDate = null, $userId = null, $status = null, $projectId = null, $hasFullControl = true, $orderId = null){
		$fromDateString = null;
		$toDateString = null;
		$query = "SELECT COUNT(id) as total FROM $this->order_table_name";
		$where = array();
		$innerWhere = '';
		if($userId !== null){
			if($hasFullControl){
				array_push($where, 'userId = %3$d');
			}else{
				$innerWhere = ' AND projectId IN (SELECT projectId FROM ' . $this->roles_table_name . ' WHERE userId = %3$d )';
			}
		}
		if($projectId !== null && $projectId !== -1){
			$innerWhere .= ' AND projectId = %5$d';
		}
		if($fromDate !== null && $toDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			$toDateString = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'id IN (SELECT orderId FROM ' . $this->order_days_table_name . ' WHERE bookingDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)' . $innerWhere .')');
		}
		else if($fromDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'id IN (SELECT orderId FROM ' . $this->order_days_table_name . ' WHERE bookingDate BETWEEN CONVERT( \'%1$s\', DATETIME)' . $innerWhere .')');
		}
		if($status !== null){
			array_push($where, 'status = %4$d');
		}
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'projectId = %5$d');
		}
		if($orderId !== null){
			array_push($where, 'id = %6$d');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( sprintf($query, $fromDateString, $toDateString, $userId, $status, $projectId, $orderId));
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function readAllBookings($pageIndex = -1, $limit = 5, 
							$fromDate = null, $toDate = null, $userId = null, $status = null, $projectId = null, $hasFullControl = true){
		$fromDateString = null;
		$toDateString = null;
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		$query = "SELECT * FROM (SELECT u.user_login AS username,
									COALESCE(
											   ( SELECT value
												FROM $this->order_form_elements_table_name AS ofe
												WHERE ofe.orderId = o.id
												  AND ( capability = 1
													   OR capability = 2) LIMIT 1) , u.user_email) AS email,
									COALESCE(
											   ( SELECT value
												FROM $this->order_form_elements_table_name AS ofe
												WHERE ofe.orderId = o.id
												  AND capability = 3 LIMIT 1) ,
											   ( SELECT meta_value
												FROM $this->usermeta_table_name
												WHERE meta_key = 'first_name'
												  AND user_id = o.userId LIMIT 1)) AS firstname,
									COALESCE(
											   ( SELECT value
												FROM $this->order_form_elements_table_name AS ofe
												WHERE ofe.orderId = o.id
												  AND capability = 4 LIMIT 1) ,
											   ( SELECT meta_value
												FROM $this->usermeta_table_name
												WHERE meta_key = 'last_name'
												  AND user_id = o.userId LIMIT 1)) AS lastname,

					  ( SELECT COUNT(*)
					   FROM $this->order_table_name
					   WHERE userId = u.ID) AS bookingsCount,

					  ( SELECT COUNT(id)
					   FROM $this->order_days_table_name
					   WHERE orderId = o.id
						 AND status = 0) AS hasDaysPendingApproval,

					  ( SELECT COUNT(id)
					   FROM $this->order_optionals_table_name
					   WHERE orderId = o.id
						 AND status = 0) AS hasOptionalsPendingApproval,

					  ( SELECT COUNT(id)
					   FROM $this->order_cascading_item_table_name
					   WHERE orderId = o.id
						 AND status = 0) AS hasCascadingItemsPendingApproval,

					  ( SELECT COUNT(id)
					   FROM $this->order_quantity_element_table_name
					   WHERE orderId = o.id
						 AND status = 0) AS hasQuantityElementsPendingApproval,

					  ( SELECT COUNT(id)
					   FROM $this->order_days_table_name
					   WHERE orderId = o.id
						 AND status = 4) AS hasDaysPendingCancellation,

					  ( SELECT COUNT(id)
					   FROM $this->order_optionals_table_name
					   WHERE orderId = o.id
						 AND status = 4) AS hasOptionalsPendingCancellation,

					  ( SELECT COUNT(id)
						FROM $this->order_cascading_item_table_name
						WHERE orderId = o.id
						AND status = 4) AS hasCascadingItemsPendingCancellation,
						o.id AS orderId,
						o.userId,
						o.orderDate,
						o.status,
						o.token,
						o.transactionId,
						o.note,
						o.totalAmount,
						o.currency,
						o.invoiceNotification,
						o.refundNotification,
						o.refundAmount,
						o.paymentDate,
						o.timezone,
						o.discount,
						o.tax,
						COALESCE(o.isRegistered, 1) AS isRegistered,
						od.bookingDate,
					  ( SELECT GROUP_CONCAT(DISTINCT p.id SEPARATOR ',')
					   FROM $this->order_days_table_name AS od
					   INNER JOIN $this->project_table_name AS p ON od.projectId = p.id
					   WHERE od.orderId = o.id
					   GROUP BY od.orderId) AS projectIdList,
									c.enableSingleHourMinuteFormat,

					  ( SELECT GROUP_CONCAT(DISTINCT p.name SEPARATOR ',')
					   FROM $this->order_days_table_name AS od
					   INNER JOIN $this->project_table_name AS p ON od.projectId = p.id
					   WHERE od.orderId = o.id
					   GROUP BY od.orderId) AS projectNames,

					  ( SELECT GROUP_CONCAT(bookingDate SEPARATOR ',')
					   FROM $this->order_days_table_name AS od
					   WHERE od.orderId = o.id
					   GROUP BY od.orderId) AS bookedDates,

					  ( SELECT GROUP_CONCAT(CONCAT(LPAD(od.hourStart, 2, '0'), ':', LPAD(od.minuteStart, 2, '0'), '-', LPAD(od.hourEnd, 2, '0'), ':', LPAD(od.minuteEnd, 2, '0')) SEPARATOR ',')
					   FROM $this->order_days_table_name AS od
					   WHERE od.orderId = o.id
					   GROUP BY od.orderId) AS bookedTimeslots,

					  ( SELECT GROUP_CONCAT( CASE WHEN fe.elementType = 4 THEN fe.label WHEN fe.elementType = 5 THEN fe.label ELSE CONCAT(fe.label, ': ', fe.value) END SEPARATOR ',')
					   FROM $this->order_form_elements_table_name AS fe
					   WHERE fe.orderId = o.id AND fe.capability = 0
					   GROUP BY fe.orderId) AS formElements,

					  ( SELECT GROUP_CONCAT(op.name SEPARATOR ',')
					   FROM $this->order_optionals_table_name AS op
					   WHERE op.orderId = o.id
					   GROUP BY op.orderId) AS optionals,

					  ( SELECT GROUP_CONCAT(CONCAT(oqe.name, ' x ', oqe.quantity) SEPARATOR ',')
					   FROM $this->order_quantity_element_table_name AS oqe
					   WHERE oqe.orderId = o.id
					   GROUP BY oqe.orderId) AS quantityElements,

					  ( SELECT GROUP_CONCAT(oc.value SEPARATOR ',')
					   FROM $this->order_cascading_item_table_name AS oc
					   WHERE oc.orderId = o.id
					   GROUP BY oc.orderId) AS cascadingItems
					FROM $this->order_table_name AS o
					INNER JOIN $this->order_days_table_name AS od ON od.orderId = o.id
					INNER JOIN $this->calendar_table_name AS c ON od.projectId = c.projectId
					LEFT JOIN $this->user_table_name AS u ON o.userId = u.ID";
		$where = array();
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'od.projectId = %5$d');
		}
		if($fromDate !== null && $toDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			$toDateString = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'od.bookingDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'od.bookingDate = CONVERT( \'%1$s\', DATETIME)');
		}
		
		if($userId !== null){
			if($hasFullControl){
				array_push($where, 'o.userId = %3$d');
			}else{
				array_push($where, 'od.projectId IN (SELECT projectId FROM ' . $this->roles_table_name . ' WHERE userId = %3$d )');
			}
		}
		
		if($status !== null){
			array_push($where, 'o.status = %4$d');
		}
		
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' GROUP BY o.id';
		$query .= ') as d ORDER BY d.bookingDate DESC';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit;
		}
		$result = $this->wpdb->get_results( sprintf($query, $fromDateString, $toDateString, $userId, $status, $projectId ) );
		$total = 0;
		if( is_array($result) ){
			$bookingInfos = new Booki_BookingInfos();
			$bookingInfos->total = $this->getTotal($fromDate, $toDate, $userId, $status, $projectId, $hasFullControl);
			foreach($result as $r){
				$bookingInfo = new Booki_BookingInfo((array)$r);
				$bookingInfo->user = new Booki_User(
					(string)$r->username
					, (string)$r->email
					, (string)$r->firstname
					, (string)$r->lastname
					, (int)$r->bookingsCount
					, (int)$r->userId
				);
				$bookingInfos->add($bookingInfo);
			}
			return $bookingInfos;
		}
		return false;
	}
	public function readAllCSV($pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', 
							$fromDate = null, $toDate = null, $userId = null, $status = null, $projectId = null, $hasFullControl = true){
		$fromDateString = null;
		$toDateString = null;
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
		$query = "SELECT    u.user_login AS username,
                           u.user_email AS email ,
                           (
                                  SELECT meta_value
                                  FROM   $this->usermeta_table_name
                                  WHERE  meta_key = 'first_name'
                                  AND    user_id = o.userId
                                  LIMIT  1) AS firstname ,
                           (
                                  SELECT meta_value
                                  FROM   $this->usermeta_table_name
                                  WHERE  meta_key = 'last_name'
                                  AND    user_id = o.userId
                                  LIMIT  1) AS lastname ,
                           (
                                  SELECT COUNT(*)
                                  FROM   $this->order_table_name
                                  WHERE  userId = u.ID) AS bookingsCount ,
                           o.id,
                           o.userId,
                           o.orderDate,
                           o.status,
                           o.token,
                           o.transactionId,
                           o.note,
                           o.totalAmount,
                           o.currency ,
                           o.invoiceNotification,
                           o.refundNotification,
                           o.refundAmount,
                           o.paymentDate,
                           o.timezone ,
                           o.discount,
                           o.tax,
                           COALESCE(o.isRegistered, 1) AS isRegistered,
						   	(
								  SELECT     GROUP_CONCAT(DISTINCT p.id SEPARATOR ',')
								  FROM       $this->order_days_table_name AS od
								  INNER JOIN $this->project_table_name    AS p
								  ON         od.projectId = p.id
								  WHERE      od.orderId = o.id
								  GROUP BY   od.orderId) AS projectIdList,
						   c.enableSingleHourMinuteFormat ,
                           (
                                  SELECT value
                                  FROM   $this->order_form_elements_table_name AS ofe
                                  WHERE  ofe.orderId = o.id
                                  AND    capability = 3
                                  LIMIT  1) AS notRegUserFirstname,
                           (
                                  SELECT value
                                  FROM   $this->order_form_elements_table_name AS ofe
                                  WHERE  ofe.orderId = o.id
                                  AND    capability = 4
                                  LIMIT  1) AS notRegUserLastname,
                           (
                                  SELECT value
                                  FROM   $this->order_form_elements_table_name AS ofe
                                  WHERE  ofe.orderId = o.id
                                  AND    (
                                                capability = 1
                                         OR     capability = 2)
                                  LIMIT  1) AS notRegUserEmail,
                           (
                                      SELECT     GROUP_CONCAT(DISTINCT p.name SEPARATOR ',')
                                      FROM       $this->order_days_table_name AS od
                                      INNER JOIN $this->project_table_name    AS p
                                      ON         od.projectId = p.id
                                      WHERE      od.orderId = o.id
                                      GROUP BY   od.orderId) AS projectNames,
                           (
								SELECT   GROUP_CONCAT(bookingDate SEPARATOR ',')
								FROM     $this->order_days_table_name AS od
								WHERE    od.orderId = o.id
								GROUP BY od.orderId) AS bookedDates ,
							(
								SELECT   GROUP_CONCAT(CONCAT(LPAD(od.hourStart, 2, '0'), ':' , LPAD(od.minuteStart, 2, '0'), '-' , LPAD(od.hourEnd, 2, '0'), ':' , LPAD(od.minuteEnd, 2, '0')) SEPARATOR ',')
								FROM     $this->order_days_table_name AS od
								WHERE    od.orderId = o.id
								GROUP BY od.orderId) AS bookedTimeslots ,
                           (
                                    SELECT   GROUP_CONCAT(
                                             CASE
                                                      WHEN fe.elementType = 4 THEN fe.label
                                                      WHEN fe.elementType = 5 THEN fe.label
                                                      ELSE CONCAT(fe.label, ': ', fe.value)
                                             END SEPARATOR ',')
                                    FROM     $this->order_form_elements_table_name AS fe
                                    WHERE    fe.orderId = o.id
                                    GROUP BY fe.orderId) AS formElements,
                           (
                                    SELECT   GROUP_CONCAT(op.name SEPARATOR ',')
                                    FROM     $this->order_optionals_table_name AS op
                                    WHERE    op.orderId = o.id
                                    GROUP BY op.orderId) AS optionals,
							(
									SELECT   GROUP_CONCAT(CONCAT(oqe.name, ' x ', oqe.quantity) SEPARATOR ',')
									FROM     $this->order_quantity_element_table_name AS oqe
									WHERE    oqe.orderId = o.id
									GROUP BY oqe.orderId) AS quantityElements ,
                           (
                                    SELECT   GROUP_CONCAT(oc.value SEPARATOR ',')
                                    FROM     $this->order_cascading_item_table_name AS oc
                                    WHERE    oc.orderId = o.id
                                    GROUP BY oc.orderId) AS cascadingItems
                 FROM      $this->order_table_name       AS o
				 INNER JOIN      $this->order_days_table_name  AS od
				 ON              od.orderId = o.id
				 INNER JOIN      $this->calendar_table_name AS c
				 ON              od.projectId = c.projectId
                 LEFT JOIN $this->user_table_name        AS u
                 ON        o.userId = u.ID";
			
		$where = array();
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'od.projectId = %5$d');
		}
		if($fromDate !== null && $toDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			$toDateString = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate = CONVERT( \'%1$s\', DATETIME)');
		}
		
		if($userId !== null){
			if($hasFullControl){
				array_push($where, 'o.userId = %3$d');
			}else{
				array_push($where, 'od.projectId IN (SELECT projectId FROM ' . $this->roles_table_name . ' WHERE userId = %3$d )');
			}
		}
		
		
		if($status !== null){
			array_push($where, 'o.status = %4$d');
		}
		
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' GROUP BY o.id';
		$query .= ' ORDER BY o.' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		
		$result = $this->wpdb->get_results( sprintf($query, $fromDateString, $toDateString, $userId, $status, $projectId ) );
		$total = 0;
		if( is_array($result) ){
			$orders = new Booki_Orders();
			$orders->total = $this->getTotal($fromDate, $toDate, $userId, $status, $projectId, $hasFullControl);
			foreach($result as $r){
				$order = new Booki_Order((array)$r);
				$order->user = new Booki_User(
					(string)$r->username
					, (string)$r->email
					, (string)$r->firstname
					, (string)$r->lastname
					, (int)$r->bookingsCount
					, (int)$r->userId
				);
				$orders->add($order);
			}
			return $orders;
		}
		return false;
	}
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'asc', 
							$fromDate = null, $toDate = null, $userId = null, $status = null, 
							$orderId = null, $hasFullControl = true){
		$fromDateString = null;
		$toDateString = null;
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
			$order = 'asc';
		}
		$query = "SELECT    u.user_login AS username,
				   u.user_email AS email,
				   o.id,
				   o.userId,
				   o.orderDate,
				   o.status,
				   o.token,
				   o.transactionId,
				   o.note,
				   o.totalAmount,
				   o.currency,
				   o.invoiceNotification,
				   o.refundNotification,
				   o.refundAmount,
				   o.paymentDate,
				   o.timezone,
				   o.discount,
				   o.tax,
				   Coalesce(o.isRegistered, 1) AS isRegistered,
				   (
						  SELECT     GROUP_CONCAT(DISTINCT p.id SEPARATOR ',')
						  FROM       $this->order_days_table_name AS od
						  INNER JOIN $this->project_table_name    AS p
						  ON         od.projectId = p.id
						  WHERE      od.orderId = o.id
						  GROUP BY   od.orderId) AS projectIdList,
				   (
						  SELECT meta_value
						  FROM   $this->usermeta_table_name
						  WHERE  meta_key = 'first_name'
						  AND    user_id = o.userId
						  LIMIT  1) AS firstname,
				   (
						  SELECT meta_value
						  FROM   $this->usermeta_table_name
						  WHERE  meta_key = 'last_name'
						  AND    user_id = o.userId
						  LIMIT  1) AS lastname,
				   (
						  SELECT COUNT(*)
						  FROM   $this->order_table_name
						  WHERE  userId = u.ID) AS bookingsCount,
				   (
						  SELECT COUNT(id)
						  FROM   $this->order_days_table_name
						  WHERE  orderId = o.id
						  AND    status = 0) AS hasDaysPendingApproval,
				   (
						  SELECT COUNT(id)
						  FROM   $this->order_optionals_table_name
						  WHERE  orderId = o.id
						  AND    status = 0) AS hasOptionalsPendingApproval,
				   (
						  SELECT COUNT(id)
						  FROM   $this->order_cascading_item_table_name
						  WHERE  orderId = o.id
						  AND    status = 0) AS hasCascadingItemsPendingApproval,
				   (
						  SELECT COUNT(id)
						  FROM   $this->order_quantity_element_table_name
						  WHERE  orderId = o.id
						  AND    status = 0) AS hasQuantityElementsPendingApproval,
				   (
						  SELECT COUNT(id)
						  FROM   $this->order_days_table_name
						  WHERE  orderId = o.id
						  AND    status = 4) AS hasDaysPendingCancellation,
				   (
						  SELECT COUNT(id)
						  FROM   $this->order_optionals_table_name
						  WHERE  orderId = o.id
						  AND    status = 4) AS hasOptionalsPendingCancellation,
				   (
						  SELECT COUNT(id)
						  FROM   $this->order_cascading_item_table_name
						  WHERE  orderId = o.id
						  AND    status = 4) AS hasCascadingItemsPendingCancellation,
				   (
						  SELECT ofe.value
						  FROM   $this->order_form_elements_table_name AS ofe
						  WHERE  ofe.orderId = o.id
						  AND    capability = 3
						  LIMIT  1) AS notRegUserFirstname,
				   (
						  SELECT ofe.value
						  FROM   $this->order_form_elements_table_name AS ofe
						  WHERE  ofe.orderId = o.id
						  AND    capability = 4
						  LIMIT  1) AS notRegUserLastname,
				   (
						  SELECT ofe.value
						  FROM   $this->order_form_elements_table_name AS ofe
						  WHERE  ofe.orderId = o.id
						  AND    (
										capability = 1
								 OR     capability = 2)
						  LIMIT  1)        AS notRegUserEmail
		FROM      		$this->order_table_name       AS o
		INNER JOIN      $this->order_days_table_name  AS od
		ON              od.orderId = o.id
		LEFT JOIN $this->user_table_name  AS u
		ON        o.userId = u.ID";
		$where = array();

		if($fromDate !== null && $toDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			$toDateString = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDateString = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate = CONVERT( \'%1$s\', DATETIME)');
		}
		
		if($userId !== null){
			if($hasFullControl){
				array_push($where, 'o.userId = %3$d');
			}else{
				array_push($where, 'od.projectId IN (SELECT projectId FROM ' . $this->roles_table_name . ' WHERE userId = %3$d )');
			}
		}
		
		
		if($status !== null){
			array_push($where, 'o.status = %4$d');
		}
		if($orderId !== null){
			array_push($where, 'o.id = %5$d');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' GROUP BY o.id';
		$query .= ' ORDER BY o.' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}

		$result = $this->wpdb->get_results( sprintf($query, $fromDateString, $toDateString, $userId, $status, $orderId ) );
		$total = 0;
		if( is_array($result) ){
			$orders = new Booki_Orders();
			$orders->total = $this->getTotal($fromDate, $toDate, $userId, $status, null, $hasFullControl, $orderId);
			foreach($result as $r){
				$order = new Booki_Order((array)$r);
				$order->user = new Booki_User(
					(string)$r->username
					, (string)$r->email
					, (string)$r->firstname
					, (string)$r->lastname
					, (int)$r->bookingsCount
					, (int)$r->userId
				);
				$orders->add($order);
			}
			return $orders;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT u.user_login AS username,
				   u.user_email AS email,
				   o.id,
				   o.userId,
				   o.orderDate,
				   o.status,
				   o.token,
				   o.transactionId,
				   o.note,
				   o.totalAmount,
				   o.currency,
				   o.invoiceNotification,
				   o.refundNotification,
				   o.refundAmount,
				   o.paymentDate,
				   o.timezone,
				   o.discount,
				   o.tax,
				   Coalesce(o.isRegistered, 1) AS isRegistered,
				
				( SELECT     GROUP_CONCAT(DISTINCT p.id SEPARATOR ',')
				  FROM       $this->order_days_table_name AS od
				  INNER JOIN $this->project_table_name    AS p
				  ON         od.projectId = p.id
				  WHERE      od.orderId = o.id
				  GROUP BY   od.orderId) AS projectIdList,
				 
				 ( SELECT     GROUP_CONCAT(DISTINCT p.name SEPARATOR ',')
				  FROM       $this->order_days_table_name AS od
				  INNER JOIN $this->project_table_name    AS p
				  ON         od.projectId = p.id
				  WHERE      od.orderId = o.id
				  GROUP BY   od.orderId) AS projectNames, 
				  
			  ( SELECT meta_value
			   FROM $this->usermeta_table_name
			   WHERE meta_key = 'first_name'
				 AND user_id = o.userId LIMIT 1) AS firstname,

			  ( SELECT meta_value
			   FROM $this->usermeta_table_name
			   WHERE meta_key = 'last_name'
				 AND user_id = o.userId LIMIT 1) AS lastname,

			  ( SELECT ofe.value
			   FROM $this->order_form_elements_table_name AS ofe
			   WHERE ofe.orderId = o.id
				 AND capability = 3 LIMIT 1) AS notRegUserFirstname,

			  ( SELECT ofe.value
			   FROM $this->order_form_elements_table_name AS ofe
			   WHERE ofe.orderId = o.id
				 AND capability = 4 LIMIT 1) AS notRegUserLastname,

			  ( SELECT ofe.value
			   FROM $this->order_form_elements_table_name AS ofe
			   WHERE ofe.orderId = o.id
				 AND ( capability = 1
					  OR capability = 2) LIMIT 1) AS notRegUserEmail,

			  ( SELECT COUNT(*)
			   FROM $this->order_table_name
			   WHERE userId = u.ID) AS bookingsCount,
			   
			    ( SELECT COUNT(id)
				   FROM $this->order_days_table_name
				   WHERE orderId = o.id
					 AND status = 0) AS hasDaysPendingApproval,

				  ( SELECT COUNT(id)
				   FROM $this->order_optionals_table_name
				   WHERE orderId = o.id
					 AND status = 0) AS hasOptionalsPendingApproval,

				  ( SELECT COUNT(id)
				   FROM $this->order_cascading_item_table_name
				   WHERE orderId = o.id
					 AND status = 0) AS hasCascadingItemsPendingApproval,

				  ( SELECT COUNT(id)
				   FROM $this->order_quantity_element_table_name
				   WHERE orderId = o.id
					 AND status = 0) AS hasQuantityElementsPendingApproval,

				  ( SELECT COUNT(id)
				   FROM $this->order_days_table_name
				   WHERE orderId = o.id
					 AND status = 4) AS hasDaysPendingCancellation,

				  ( SELECT COUNT(id)
				   FROM $this->order_optionals_table_name
				   WHERE orderId = o.id
					 AND status = 4) AS hasOptionalsPendingCancellation,

				  ( SELECT COUNT(id)
					FROM $this->order_cascading_item_table_name
					WHERE orderId = o.id
					AND status = 4) AS hasCascadingItemsPendingCancellation
						
			FROM $this->order_table_name AS o
			LEFT 	JOIN $this->user_table_name AS u ON o.userId = u.ID
			WHERE o.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id ) );
		if( $result ){
			$r = $result[0];
			$order = new Booki_Order((array)$r);
			$order->user = new Booki_User(
				(string)$r->username
				, (string)$r->email
				, (string)$r->firstname
				, (string)$r->lastname
				, (int)$r->bookingsCount
				, (int)$r->userId
			);
			return $order;
		}
		return false;
	}
	
	public function readByProject($projectId){
		$sql = "SELECT o.id 
				FROM   $this->order_table_name AS o 
					   INNER JOIN $this->order_days_table_name AS od 
							   ON od.orderId = o.id 
				WHERE  od.projectId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId ) );
		if ( $result !== false ){
			return $result;
		}
		return false;
	}
	
	public function readOrderProjects($orderId){
		$sql = "SELECT  p.id
					  FROM       $this->order_days_table_name AS od
					  INNER JOIN $this->project_table_name    AS p
					  ON         od.projectId = p.id
					  WHERE      od.orderId = %d
					  GROUP BY   od.orderId";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $orderId ) );
		if ( $result !== false ){
			return $result;
		}
		return false;
	}
	public function getAttendeesTotal($projectId, $bookingDate, $hourStart = null, $minuteStart = null, $hourEnd = null, $minuteEnd = null){
		$query = "SELECT COUNT(DISTINCT o.id) as total
					FROM $this->order_table_name AS o
					INNER JOIN $this->order_days_table_name AS od ON od.orderId = o.id";
		$where = array();
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'od.projectId = %1$d');
		}
		if($bookingDate !== null){
			$bookingDate = $bookingDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'od.bookingDate = CONVERT( \'%2$s\', DATETIME)');
		}
		if($hourStart !== null){
			array_push($where, 'od.hourStart = %3$d');
		}
		if($minuteStart !== null){
			array_push($where, 'od.minuteStart = %4$d');
		}
		if($hourEnd !== null){
			array_push($where, 'od.hourEnd = %5$d');
		}
		if($minuteEnd !== null){
			array_push($where, 'od.minuteEnd = %6$d');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( sprintf($query, $projectId, $bookingDate, $hourStart, $minuteStart, $hourEnd, $minuteEnd ) );
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function readAttendees($pageIndex = -1, $limit = 5, $projectId, $bookingDate, $hourStart = null, $minuteStart = null, $hourEnd = null, $minuteEnd = null){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		$query = "SELECT DISTINCT u.user_login AS username,
									COALESCE(
											   ( SELECT value
												FROM $this->order_form_elements_table_name AS ofe
												WHERE ofe.orderId = o.id
												  AND ( capability = 1
													   OR capability = 2) LIMIT 1) , u.user_email) AS email,
									COALESCE(
											   ( SELECT value
												FROM $this->order_form_elements_table_name AS ofe
												WHERE ofe.orderId = o.id
												  AND capability = 3 LIMIT 1) ,
											   ( SELECT meta_value
												FROM $this->usermeta_table_name
												WHERE meta_key = 'first_name'
												  AND user_id = o.userId LIMIT 1)) AS firstname,
									COALESCE(
											   ( SELECT value
												FROM $this->order_form_elements_table_name AS ofe
												WHERE ofe.orderId = o.id
												  AND capability = 4 LIMIT 1) ,
											   ( SELECT meta_value
												FROM $this->usermeta_table_name
												WHERE meta_key = 'last_name'
												  AND user_id = o.userId LIMIT 1)) AS lastname,
									o.id AS orderId,
									o.userId,
									od.status,
									od.bookingDate,
									od.hourStart,
									od.minuteStart,
									od.hourEnd,
									od.minuteEnd,
									c.enableSingleHourMinuteFormat
					FROM $this->order_table_name AS o
					INNER JOIN $this->order_days_table_name AS od ON od.orderId = o.id
					INNER JOIN $this->calendar_table_name AS c ON od.projectId = c.projectId
					LEFT JOIN $this->user_table_name AS u ON o.userId = u.ID";
		$where = array();
		if($projectId !== null && $projectId !== -1){
			array_push($where, 'od.projectId = %1$d');
		}
		if($bookingDate !== null){
			$bookingDate = $bookingDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'od.bookingDate = CONVERT( \'%2$s\', DATETIME)');
		}
		if($hourStart !== null){
			array_push($where, 'od.hourStart = %3$d');
		}
		if($minuteStart !== null){
			array_push($where, 'od.minuteStart = %4$d');
		}
		if($hourEnd !== null){
			array_push($where, 'od.hourEnd = %5$d');
		}
		if($minuteEnd !== null){
			array_push($where, 'od.minuteEnd = %6$d');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY o.orderDate DESC';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}

		$result = $this->wpdb->get_results( sprintf($query, $projectId, $bookingDate, $hourStart, $minuteStart, $hourEnd, $minuteEnd ) );
		$total = 0;
		if( is_array($result) ){
			$attendees = new Booki_Attendees();
			$attendees->total = $this->getAttendeesTotal($projectId, $bookingDate, $hourStart, $minuteStart, $hourEnd, $minuteEnd);
			foreach($result as $r){
				$attendees->add(new Booki_Attendee((array)$r));
			}
			return $attendees;
		}
		return false;
	}
	public function insert($order){
		$params = array(
			'orderDate'=>$order->orderDate->format(BOOKI_FULL_DATEFORMAT)
			, 'status'=>$order->status
			, 'userId'=>$order->userId
			, 'token'=>$order->token
			, 'transactionId'=>$order->transactionId
			, 'note'=>$order->note
			, 'totalAmount'=>$order->totalAmount
			, 'currency'=>$order->currency
			, 'discount'=>$order->discount
			, 'tax'=>$order->tax
			, 'invoiceNotification'=>$order->invoiceNotification
			, 'refundNotification'=>$order->refundNotification
			, 'refundAmount'=>$order->refundAmount
			, 'timezone'=>$order->timezone
			, 'isRegistered'=>$order->userIsRegistered
		);
		
		$placeHolders = array(
			'%s'/*orderDate*/
			, '%d'/*status*/
			, '%d'/*userId*/
			, '%s'/*token*/
			, '%s'/*transactionId*/
			, '%s'/*note*/
			, '%f'/*totalAmount*/
			, '%s'/*currency*/
			, '%f'/*discount*/
			, '%f'/*tax*/
			, '%d'/*invoiceNotification*/
			, '%d'/*refundNotification*/
			, '%f'/*refundAmount*/
			, '%s'/*timezone*/
			, '%d' /*isRegistered*/
		);
		if($order->id !== -1){
			$params['id'] = $order->id;
			array_push($placeHolders, '%d');
		}
		if($order->paymentDate){
			$params['paymentDate'] = $order->paymentDate->format(BOOKI_DATEFORMAT);
			array_push($placeHolders, '%s');
		}
		
		$result = $this->wpdb->insert($this->order_table_name,  $params, $placeHolders);
		
		 if($result !== false){
			 if($order->id !== -1){
				 return $order->id;
			 }
			return $this->wpdb->insert_id;
		 }
		 return false;
	}

	public function update($order){
		$params = array(
			'status'=>$order->status
			, 'userId'=>$order->userId
			, 'token'=>$order->token
			, 'transactionId'=>$order->transactionId
			, 'note'=>$order->note
			, 'totalAmount'=>$order->totalAmount
			, 'currency'=>$order->currency
			, 'discount'=>$order->discount
			, 'tax'=>$order->tax
			, 'invoiceNotification'=>$order->invoiceNotification
			, 'refundNotification'=>$order->refundNotification
			, 'refundAmount'=>$order->refundAmount
			, 'timezone'=>$order->timezone
			, 'isRegistered'=>$order->userIsRegistered
		);
		
		$placeHolders = array(
			'%d'/*status*/
			, '%d'/*userId*/
			, '%s'/*token*/
			, '%s'/*transactionId*/
			, '%s'/*note*/
			, '%f'/*totalAmount*/
			, '%s'/*currency*/
			, '%f'/*discount*/
			, '%f'/*tax*/
			, '%d'/*invoiceNotification*/
			, '%d'/*refundNotification*/
			, '%f'/*refundAmount*/
			, '%s'/*timezone*/
			, '%d'/*isRegistered*/
		);
		
		if($order->paymentDate){
			$params['paymentDate'] = $order->paymentDate->format(BOOKI_DATEFORMAT);
			array_push($placeHolders, '%s');
		}
		return $this->wpdb->update($this->order_table_name,  $params , array('id'=>$order->id) , $placeHolders);
	}
	
	public function updateStatusByOrderId($orderId, $status){
		 $result = $this->wpdb->update($this->order_table_name,  array(
			'status'=>$status
		  ), array('id'=>$orderId), array('%d'));
		 return $result;
	}
	/**
		@description fromDate has to be a formatted date string BOOKI_DATEFORMAT.
	*/
	public function deleteExpired($interval, $status = Booki_PaymentStatus::UNPAID){
		$fromDate = date(BOOKI_FULL_DATEFORMAT, time() - $interval);
		$sql = "DELETE FROM $this->order_table_name
					WHERE (orderDate < CONVERT( '$fromDate', DATETIME))
					AND status = $status;";
		
		$this->wpdb->query($sql);
		
		$this->wpdb->query("DELETE od.* FROM $this->order_days_table_name as od 
				LEFT JOIN $this->order_table_name as o
				ON o.id = od.orderId WHERE o.id IS NULL");
		$this->wpdb->query("DELETE fe.* FROM $this->order_form_elements_table_name as fe 
				LEFT JOIN $this->order_table_name as o
				ON o.id = fe.orderId WHERE o.id IS NULL");
		$this->wpdb->query("DELETE op.* FROM $this->order_optionals_table_name as op 
				LEFT JOIN $this->order_table_name as o
				ON o.id = op.orderId WHERE o.id IS NULL");
		$this->wpdb->query("DELETE oci.* FROM $this->order_cascading_item_table_name as oci 
				LEFT JOIN $this->order_table_name as o
				ON o.id = oci.orderId WHERE o.id IS NULL");
		$this->wpdb->query("DELETE oqe.* FROM $this->order_quantity_element_table_name as oqe 
				LEFT JOIN $this->order_table_name as o
				ON o.id = oqe.orderId WHERE o.id IS NULL");
	}
	
	public function delete($id){
		//myisam has no delete cascades. manual labor of love.
		$sql = "DELETE FROM $this->order_days_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_form_elements_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_optionals_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_cascading_item_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_quantity_element_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_table_name WHERE id = %d";
		return	$this->wpdb->query($this->wpdb->prepare($sql, $id));
	}
	
}
?>