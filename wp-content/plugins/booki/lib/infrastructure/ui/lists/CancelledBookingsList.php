<?php
class Booki_CancelledBookingsList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_cancelledbookings_';
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
		if($item['data']->id == $orderId){
			$row_class .= ' booki-selected-row';
		}
		$row_class = $row_class ? ' class="' . $row_class . '"' : '';
		echo '<tr' . $row_class . '>';
		echo $this->single_row_columns( $item );
		echo '</tr>';
	}
	
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
    
	function column_id($item){
		return $item['id'];
	}
	function column_orderId($item){
		return $item['data']->id;
	}
	function column_deletionDate($item){
		return $item['deletionDate']->format($this->shorthandDateFormat);
	}
	function column_bookedDates($item){
		$output = array();
		$dates = array();
		$timeslots = array();
		$timezone = null;
		if($this->globalSettings->autoTimezoneDetection){
			$timezone = $item['data']->timezone;
		}
		$timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
		$userTimezoneString = $timezoneInfo['timezone'];
		
		$adminTimezoneInfo = Booki_TimeHelper::timezoneInfo();
		$adminTimezoneString = $adminTimezoneInfo['timezone'];
		
		foreach($item['data']->bookedDays as $day){
			array_push($dates, sprintf('<strong> %s</strong>', $day->bookingDate->format($this->shorthandDateFormat)));
			if($day->hasTime()){
				$adminFormattedTime = Booki_TimeHelper::formatTime($day, $adminTimezoneString,  $item['data']->enableSingleHourMinuteFormat, $this->timeFormat);
				array_push($timeslots, sprintf('<i> %1$s %2$s</i>', 
									$adminFormattedTime, $adminTimezoneString));
			}
		}
		array_push($output, implode(',', $dates));
		if($timeslots){
			array_push($output, '<br>');
			array_push($output, implode(',', $timeslots));
		}
		return implode('', $output);
	}
	
	function column_projectNames($item){
		return $item['data']->projectNames;
	}
	
	function column_formElements($item){
		$name =  trim($item['data']->getFirstName() . ' ' . $item['data']->getLastName());
		$email = $item['data']->getEmail();
		$output = array();
		if($name){
			array_push($output, $name);
		}
		if($email){
			array_push($output, $email);
		}
		foreach($item['data']->bookedQuantityElements as $quantityElement){
			array_push($output, $quantityElement->getName());
		}
		foreach($item['data']->bookedFormElements as $formElement){
			$capabilities = array(
				Booki_FormElementCapability::EMAIL_NOTIFICATION_AUTOREG
				, Booki_FormElementCapability::EMAIL_NOTIFICATION
				, Booki_FormElementCapability::FIRST_NAME
				, Booki_FormElementCapability::LAST_NAME
			);
			if(!in_array($formElement->capability, $capabilities)){
				array_push($output, sprintf('%s: %s', $formElement->label, $formElement->value));
			}
		}
		foreach($item['data']->bookedOptionals as $optional){
			array_push($output, $optional->getName());
		}
		foreach($item['data']->bookedCascadingItems as $cascadingItem){
			array_push($output,  $cascadingItem->getName());
		}
		return implode(',', $output);
	}
    
	function column_action($item){
		$buttonGroups = array();
		array_push($buttonGroups, 
			'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
				<span>Action</span>
			</button>
			<ul class="dropdown-menu" role="menu">'
		);
		array_push($buttonGroups, sprintf(
			'<li>
				<button class="booki-btnlink btn btn-default" name="undo" value="%d" title="%s">
					<i class="glyphicon glyphicon-arrow-left"></i> 
					%s
				</button>
			</li>'
			, $item['id']
			, __('Put booking back to original status as was before cancellation.', 'booki')
			, __('Undo', 'booki') 
		));
		array_push($buttonGroups, sprintf(
			'<li>
				<button class="booki-btnlink btn btn-default" name="delete" value="%d" title="%s">
					<i class="glyphicon glyphicon-remove"></i> 
					%s
				</button>
			</li>'
			, $item['id']
			, __('Deletes booking permanently from system. Cannot be recovered.', 'booki')
			, __('Delete permanently', 'booki') 
		));
		array_push($buttonGroups, '</ul>');
        return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="booki_cancelledbookings" />
				<div class="form-group">
					<div class="grid-btn-group">
						<div class="btn-group">
							%s
						</div>
					</div>
				</div>
			</form>'
			, $_SERVER['REQUEST_URI']
			, join("\n", $buttonGroups)
        );
	}
	
    function get_columns(){
        $columns = array(
			'id'=>__('ID', 'booki')
			, 'orderId'=>__('ORDER ID', 'booki')
			, 'deletionDate'=>__('CANCELLATION DATE', 'booki')
            , 'bookedDates'=>__('BOOKING DATES AND TIME', 'booki')
			, 'projectNames'=>__('PROJECT/S', 'booki')
			, 'formElements'=>__('ADDITIONAL INFO', 'booki')
			, 'action'=>__('ACTION', 'booki')
        );
        return $columns;
    }
    
    /**
		@description binds to data
	*/
    function bind() {
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->currentPage = $this->get_pagenum() - 1;
		if( $this->currentPage ){
			$this->currentPage = $this->currentPage * $this->perPage;
		}

        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 
		$fromDate = null;
		$toDate = null;
		$userId = null;
		
		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_cancelledbookings'){
			if (array_key_exists('from', $_GET) && array_key_exists('to', $_GET)){
				$fromDate = new Booki_DateTime($_GET['from']);
				$toDate = new Booki_DateTime($_GET['to']);
			}
		}
		if(!$this->hasFullControl){
			$user = wp_get_current_user();
			$userId = $user->ID;
		}
		$trashRepository = new Booki_TrashRepository();
        $result = $trashRepository->readAll($this->currentPage, $this->perPage, $this->orderBy, $this->order, $fromDate, $toDate, $userId);

        $this->totalPages = ceil($result->total / $this->perPage);
        $total_items = $result->total;
        $this->items = $result->toArray();
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $this->perPage,
            'total_pages' => $this->totalPages
        ) );
    }
	
	function get_table_classes() {
		return array( 'booki', 'booki-grid', 'table', 'table-bordered');
	}
}
?>