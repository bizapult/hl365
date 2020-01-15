<?php
class Booki_ProjectRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $project_table_name;
	private $calendar_table_name;
	private $calendarDay_table_name;
	private $order_table_name;
	private $order_days_table_name;
	private $form_element_table_name;
	private $optional_table_name;
	private $order_form_elements_table_name;
	private $order_optionals_table_name;
	private $cascading_list_table_name;
	private $cascading_item_table_name;
	private $order_cascading_item_table_name;
	private $quantity_element_table_name;
	private $quantity_element_item_table_name;
	private $quantity_element_calendar_table_name;
	private $quantity_element_calendarday_table_name;
	private $trashed_table_name;
	private $trashed_project_table_name;
	private $reminder_table_name;
	private $roles_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->project_table_name = $wpdb->prefix . 'booki_project';
		$this->calendar_table_name = $wpdb->prefix . 'booki_calendar';
		$this->calendarDay_table_name = $wpdb->prefix . 'booki_calendar_day';
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->form_element_table_name = $wpdb->prefix . 'booki_form_element';
		$this->optional_table_name = $wpdb->prefix . 'booki_optional';
		$this->order_form_elements_table_name = $wpdb->prefix . 'booki_order_form_elements';
		$this->order_optionals_table_name = $wpdb->prefix . 'booki_order_optionals';
		$this->cascading_list_table_name = $wpdb->prefix . 'booki_cascading_list';
		$this->cascading_item_table_name = $wpdb->prefix . 'booki_cascading_item';
		$this->order_cascading_item_table_name = $wpdb->prefix . 'booki_order_cascading_item';
		$this->quantity_element_table_name = $wpdb->prefix . 'booki_quantity_element';
		$this->quantity_element_item_table_name = $wpdb->prefix . 'booki_quantity_element_item';
		$this->quantity_element_calendar_table_name = $wpdb->prefix . 'booki_quantity_element_calendar';
		$this->quantity_element_calendarday_table_name = $wpdb->prefix . 'booki_quantity_element_calendarday';
		$this->trashed_table_name = $wpdb->prefix . 'booki_trashed';
		$this->trashed_project_table_name = $wpdb->prefix . 'booki_trashed_project';
		$this->reminder_table_name = $wpdb->prefix . 'booki_reminders';
		$this->roles_table_name = $wpdb->prefix . 'booki_roles';
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->project_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	protected function getTotal($tags, $startDate, $endDate, $projectId){
		$where = array();
		$query = "SELECT COUNT(p.id) AS total 
					FROM   $this->project_table_name AS p 
						   INNER JOIN $this->calendar_table_name AS c 
								   ON p.id = c.projectId";
		if($tags){
			array_push($where, "p.tag IN ('" . implode("','", array_map('trim', explode(',', $tags))) . "')");
		}
		if($startDate && $endDate){
			array_push($where, 'c.startDate <= CONVERT( \'%1$s\', DATETIME) AND c.endDate >= CONVERT( \'%2$s\', DATETIME)');
		}
		if($projectId !== -1){
			array_push($where, 'p.id <> %3$d');
		}
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results( sprintf($query, $startDate, $endDate, $projectId));
		if($result){
			return (int)$result[0]->total;
		}
		return 0;
	}
	public function readAll($userId = null){
		$where = array();
		$sql = "SELECT p.id,
			   CAST(p.status AS unsigned integer) AS status,
			   p.name,
			   p.bookingDaysMinimum,
			   p.bookingDaysLimit,
			   p.calendarMode,
			   p.bookingMode,
			   p.description,
			   p.previewUrl,
			   p.tag,
			   p.defaultStep,
			   p.bookingTabLabel,
			   p.customFormTabLabel,
			   p.attendeeTabLabel,
			   p.availableDaysLabel,
			   p.selectedDaysLabel,
			   p.nextLabel,
			   p.prevLabel,
			   p.addToCartLabel,
			   p.optionalItemsLabel,
			   p.bookingTimeLabel,
			   p.fromLabel,
			   p.toLabel,
			   p.proceedToLoginLabel,
			   p.makeBookingLabel,
			   p.bookingLimitLabel,
			   p.notifyUserEmailList,
			   p.optionalsBookingMode,
			   p.optionalsListingMode,
			   p.optionalsMinimumSelection,
			   p.contentTop,
			   p.contentBottom,
			   p.bookingWizardMode,
			   p.hideSelectedDays,
			   p.displayAttendees,
			   p.banList,
			   p.defaultDateSelected
		FROM $this->project_table_name as p";
		if($userId !== null){
			$sql .= ' INNER JOIN '  . $this->roles_table_name . ' as r ON p.id = r.projectId ';
			array_push($where, 'r.userId = %1$d');
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY p.tag';
		$result = $this->wpdb->get_results( sprintf($sql, $userId));
		if( is_array( $result )){
			$projects = new Booki_Projects();
			foreach($result as $r){
				$projects->add(new Booki_Project((array)$r));
			}
			return $projects;
		}
		return false;
	}
	
	public function readAllTags($userId = null){
		//tags aren't normalized, perhaps it's better this way. less joins.
		$where = array();
		$sql = "SELECT DISTINCT p.tag
				FROM $this->project_table_name as p";
		if($userId !== null){
			$sql .= " INNER JOIN $this->roles_table_name as r ON p.id = r.projectId ";
			array_push($where, 'r.userId = %1$d');
		}
		array_push($where, 'p.tag IS NOT NULL');
		array_push($where, "p.tag <> ''");
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$result = $this->wpdb->get_results(sprintf($sql, $userId));
		$tags = array();
		if( is_array( $result )){
			foreach($result as $r){
				array_push($tags, array('name'=>$r->tag));
			}
		}
		return $tags;
	}
	
	public function readByTag($tags, $startDate = null, $endDate = null, $projectId = -1, $pageIndex = -1, $limit = 5, $orderBy = 'name', $order = 'asc'){
		if($projectId === null){
			$projectId = -1;
		}
		if($pageIndex === null){
			$pageIndex = -1;
		}

		if($limit === null || $limit <= 0){
			$limit = 5;
		}
		
		if($orderBy === null){
			$orderBy = 'name';
		}
		
		if($order === null){
			$order = 'asc';
		}
		
		if($startDate){
			$startDate = $startDate->format(BOOKI_DATEFORMAT);
		}
		if($endDate){
			$endDate = $endDate->format(BOOKI_DATEFORMAT);
		}
		$sql = "SELECT p.id,
					   CAST(p.status AS unsigned integer) AS status,
					   p.name,
					   p.calendarMode ,
					   p.description,
					   p.previewUrl,
					   p.tag ,
					   c.startDate,
					   c.endDate
				FROM $this->project_table_name AS p
				INNER JOIN $this->calendar_table_name AS c ON p.id = c.projectId";
		$where = array();
		if($tags){
			array_push($where, "p.tag IN ('" . implode("','", array_map('trim', explode(',', $tags))) . "')");
		}
		if($startDate && $endDate){
			array_push($where, 'c.startDate <= CONVERT( \'%1$s\', DATETIME) AND c.endDate >= CONVERT( \'%2$s\', DATETIME)');
		}
		if($projectId !== -1){
			array_push($where, 'p.id <> %3$d');
		}
		if(count($where) > 0){
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY ' . $orderBy . ' ' . $order;
		if($pageIndex > -1){
			$sql .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $startDate, $endDate, $projectId));
		if( is_array( $result )){
			$projects = array();
			$total = $this->getTotal($tags, $startDate, $endDate, $projectId);
			foreach($result as $r){
				array_push($projects, array(
					'id'=>(int)$r->id
					, 'status'=>(int)$r->status
					, 'calendarMode'=>(int)$r->calendarMode
					, 'name'=>$this->decode((string)$r->name)
					, 'description'=>$this->decode((string)$r->description)
					, 'previewUrl'=>(string)$r->previewUrl
					, 'tag'=>$this->decode((string)$r->tag)
					, 'startDate'=>(string)$r->startDate
					, 'endDate'=>(string)$r->endDate
				));
			}
			return array('total'=>$total, 'projects'=>$projects);
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT id,
			   CAST(status AS unsigned integer) AS status,
			   name,
			   bookingDaysMinimum,
			   bookingDaysLimit,
			   calendarMode,
			   bookingMode,
			   description,
			   previewUrl,
			   tag,
			   defaultStep,
			   bookingTabLabel,
			   customFormTabLabel,
			   attendeeTabLabel,
			   availableDaysLabel,
			   selectedDaysLabel,
			   nextLabel,
			   prevLabel,
			   addToCartLabel,
			   optionalItemsLabel,
			   bookingTimeLabel,
			   fromLabel,
			   toLabel,
			   proceedToLoginLabel,
			   makeBookingLabel,
			   bookingLimitLabel,
			   notifyUserEmailList,
			   optionalsBookingMode,
			   optionalsListingMode,
			   optionalsMinimumSelection,
			   contentTop,
			   contentBottom,
			   bookingWizardMode,
			   hideSelectedDays,
			   displayAttendees,
			   banList,
			   defaultDateSelected
		FROM $this->project_table_name
		WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result){
			$r = $result[0];
			return new Booki_Project((array)$r);
		}
		return false;
	}
	
	public function insert($project){
		$result = $this->wpdb->insert($this->project_table_name, array( 
			'status'=>$project->status
			, 'name'=>$this->encode($project->name)
			, 'bookingDaysMinimum'=>$project->bookingDaysMinimum
			, 'bookingDaysLimit'=>$project->bookingDaysLimit
			, 'calendarMode'=>$project->calendarMode
			, 'bookingMode'=>$project->bookingMode
			, 'description'=>$this->encode($project->description)
			, 'previewUrl'=>$project->previewUrl
			, 'tag'=>$this->encode($project->tag)
			, 'defaultStep'=>$this->encode($project->defaultStep)
			, 'bookingTabLabel'=>$this->encode($project->bookingTabLabel)
			, 'customFormTabLabel'=>$this->encode($project->customFormTabLabel)
			, 'attendeeTabLabel'=>$this->encode($project->attendeeTabLabel)
			, 'availableDaysLabel'=>$this->encode($project->availableDaysLabel)
			, 'selectedDaysLabel'=>$this->encode($project->selectedDaysLabel)
			, 'bookingTimeLabel'=>$this->encode($project->bookingTimeLabel)
			, 'optionalItemsLabel'=>$this->encode($project->optionalItemsLabel)
			, 'nextLabel'=>$this->encode($project->nextLabel)
			, 'prevLabel'=>$this->encode($project->prevLabel)
			, 'addToCartLabel'=>$this->encode($project->addToCartLabel)
			, 'fromLabel'=>$this->encode($project->fromLabel)
			, 'toLabel'=>$this->encode($project->toLabel)
			, 'proceedToLoginLabel'=>$this->encode($project->proceedToLoginLabel)
			, 'makeBookingLabel'=>$this->encode($project->makeBookingLabel)
			, 'bookingLimitLabel'=>$this->encode($project->bookingLimitLabel)
			, 'notifyUserEmailList'=>trim($this->encode($project->notifyUserEmailList))
			, 'optionalsBookingMode'=>$project->optionalsBookingMode
			, 'optionalsListingMode'=>$project->optionalsListingMode
			, 'optionalsMinimumSelection'=>$project->optionalsMinimumSelection
			, 'contentTop'=>$project->contentTop
			, 'contentBottom'=>$project->contentBottom
			, 'bookingWizardMode'=>$project->bookingWizardMode
			, 'hideSelectedDays'=>$project->hideSelectedDays
			, 'displayAttendees'=>$project->displayAttendees
			, 'banList'=>$project->banList
			, 'defaultDateSelected'=>$project->defaultDateSelected
		), array('%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%d'));
		 if($result !== false){
			$project->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($project){
		$result = $this->wpdb->update($this->project_table_name, array( 
			'status'=>$project->status
			, 'name'=>$this->encode($project->name)
			, 'bookingDaysMinimum'=>$project->bookingDaysMinimum
			, 'bookingDaysLimit'=>$project->bookingDaysLimit
			, 'calendarMode'=>$project->calendarMode
			, 'bookingMode'=>$project->bookingMode
			, 'description'=>$this->encode($project->description)
			, 'previewUrl'=>$project->previewUrl
			, 'tag'=>$this->encode($project->tag)
			, 'defaultStep'=>$this->encode($project->defaultStep)
			, 'bookingTabLabel'=>$this->encode($project->bookingTabLabel)
			, 'customFormTabLabel'=>$this->encode($project->customFormTabLabel)
			, 'attendeeTabLabel'=>$this->encode($project->attendeeTabLabel)
			, 'availableDaysLabel'=>$this->encode($project->availableDaysLabel)
			, 'selectedDaysLabel'=>$this->encode($project->selectedDaysLabel)
			, 'bookingTimeLabel'=>$this->encode($project->bookingTimeLabel)
			, 'optionalItemsLabel'=>$this->encode($project->optionalItemsLabel)
			, 'nextLabel'=>$this->encode($project->nextLabel)
			, 'prevLabel'=>$this->encode($project->prevLabel)
			, 'addToCartLabel'=>$this->encode($project->addToCartLabel)
			, 'fromLabel'=>$this->encode($project->fromLabel)
			, 'toLabel'=>$this->encode($project->toLabel)
			, 'proceedToLoginLabel'=>$this->encode($project->proceedToLoginLabel)
			, 'makeBookingLabel'=>$this->encode($project->makeBookingLabel)
			, 'bookingLimitLabel'=>$this->encode($project->bookingLimitLabel)
			, 'notifyUserEmailList'=>trim($this->encode($project->notifyUserEmailList))
			, 'optionalsBookingMode'=>$project->optionalsBookingMode
			, 'optionalsListingMode'=>$project->optionalsListingMode
			, 'optionalsMinimumSelection'=>$project->optionalsMinimumSelection
			, 'contentTop'=>$project->contentTop
			, 'contentBottom'=>$project->contentBottom
			, 'bookingWizardMode'=>$project->bookingWizardMode
			, 'hideSelectedDays'=>$project->hideSelectedDays
			, 'displayAttendees'=>$project->displayAttendees
			, 'banList'=>$project->banList
			, 'defaultDateSelected'=>$project->defaultDateSelected
		), array('id'=>$project->id), array('%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%d'));
		
		$project->updateResources();
		return $result;
	}
	
	public function delete($id){
		//the orders table has no references to a project. 
		//so don't leave orphaned when deleting a project.
		//MyISAM does not support on delete cascades.
		Booki_GCalHelper::deleteCalendarByProject($id);
		$sql = "DELETE t.* FROM $this->trashed_table_name as t
				INNER JOIN $this->order_days_table_name as ods
				ON t.orderId = ods.orderId
				WHERE ods.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE rm.* FROM $this->reminder_table_name as rm
				INNER JOIN $this->order_days_table_name as ods
				ON rm.orderId = ods.orderId
				WHERE ods.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE o.* FROM $this->order_table_name as o
				INNER JOIN $this->order_days_table_name as ods
				ON o.id = ods.orderId
				WHERE ods.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		//myisam has no delete cascades. manual labor of love.
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_days_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_form_elements_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_optionals_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->optional_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_cascading_item_table_name WHERE projectId = %d", $id) );
		$sql = "DELETE cd.* FROM $this->calendarDay_table_name as cd
				INNER JOIN $this->calendar_table_name as c
				ON c.id = cd.calendarId
				WHERE c.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->calendar_table_name WHERE projectId = %d", $id));

		$sql = "DELETE ci.* FROM $this->cascading_item_table_name as ci
			INNER JOIN $this->cascading_list_table_name as cl
			ON cl.id = ci.listId
			WHERE cl.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->cascading_list_table_name WHERE projectId = %d", $id));
		
		$this->wpdb->query($this->wpdb->prepare("DELETE qec.* FROM $this->quantity_element_calendar_table_name as qec INNER JOIN 
													$this->quantity_element_table_name as qe 
													ON qe.id = qec.quantityElementId 
													WHERE qe.projectId = %d", $id));
		$this->wpdb->query($this->wpdb->prepare("DELETE qecd.* FROM $this->quantity_element_calendarday_table_name as qecd INNER JOIN 
													$this->quantity_element_table_name as qe 
													ON qe.id = qecd.quantityElementId 
													WHERE qe.projectId = %d", $id));
		$this->wpdb->query($this->wpdb->prepare("DELETE qei.* FROM $this->quantity_element_item_table_name as qei INNER JOIN 
													$this->quantity_element_table_name as qe 
													ON qe.id = qei.elementId 
													WHERE qe.projectId = %d", $id));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->quantity_element_table_name WHERE projectId = %d", $id));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->roles_table_name WHERE projectId = %d", $id));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->trashed_project_table_name WHERE projectId = %d", $id));
		
		$project = new Booki_Project(array('id'=>$id));
		$project->deleteResources();
		
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->project_table_name WHERE id = %d", $id));
	}
}
?>