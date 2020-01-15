<?php
class Booki_BookingsList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_viewbookings_';
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
		if($item['orderId'] == $orderId){
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
    
	function column_bookedDates($item){
		$output = array();
		$dates = array();
		
		foreach($item['bookedDates'] as $d){
			$date = new Booki_DateTime($d);
			array_push($dates, sprintf('<strong> %s</strong>', $date->format($this->shorthandDateFormat)));
		}
		array_push($output, implode(',', $dates));
		if(count($item['bookedTimeslots']) > 0){
			$timeslots = array();
			$timezone = null;
			if($this->globalSettings->autoTimezoneDetection){
				$timezone = $item['timezone'];
			}
			$timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
			$userTimezoneString = $timezoneInfo['timezone'];
			
			$adminTimezoneInfo = Booki_TimeHelper::timezoneInfo();
			$adminTimezoneString = $adminTimezoneInfo['timezone'];
			
			foreach($item['bookedTimeslots'] as $timeslot){
				$t = new stdClass();
				$t->hourStart = $timeslot['startHour'];
				$t->minuteStart = $timeslot['startMinute'];
				$t->hourEnd = $timeslot['endHour'];
				$t->minuteEnd = $timeslot['endMinute'];
				//$formattedTime = Booki_TimeHelper::formatTime($t, $userTimezoneString, $item['enableSingleHourMinuteFormat'], $this->timeFormat);
				/*array_push($timeslot, sprintf('<div>%1$s</div><div>(<small><strong>%2$s: </strong><span>%3$s</span></small>)</div>'
									, $formattedTime, __('in user selected timezone', 'booki'), $userTimezoneString));*/
				//dont want to cram in too much, so putting time in admin timezone only.					
				$adminFormattedTime = Booki_TimeHelper::formatTime($t, $adminTimezoneString, $item['enableSingleHourMinuteFormat'], $this->timeFormat);
				array_push($timeslots, sprintf('<i> %1$s %2$s</i>', 
									$adminFormattedTime, $adminTimezoneString));
			}
			array_push($output, '<br>');
			array_push($output, implode(',', $timeslots));
		}
		return implode('', $output);
	}
	

	function column_projectNames($item){
		return $item['projectNames'];
	}
	
	function column_formElements($item){
		$name = $item['firstname'] . ' ' . $item['lastname'];
		$output = array();
		if(trim($name)){
			array_push($output, $name);
		}
		if($item['email']){
			array_push($output, $item['email']);
		}
		if($item['quantityElements']){
			array_push($output, $item['quantityElements']);
		}
		if($item['formElements']){
			array_push($output, $item['formElements']);
		}
		if($item['optionals'] || $item['cascadingItems']){
			if($item['optionals']){
				array_push($output, $item['optionals']);
			}
			if($item['cascadingItems']){
				array_push($output,  $item['cascadingItems']);
			}
		}
		return implode(',', $output);
	}
    
	function column_action($item){
		$selectUrl = esc_url(add_query_arg(array('orderid'=>$item['orderId'], 'external'=>true), 'admin.php?page=booki/managebookings.php'));
		return sprintf(
			'<a target="_blank" class="btn btn-default" href="%s">
				<span class="badge">#%s</span> 
				%s
			</a>'
			, $selectUrl
			, $item['orderId']
			, __('View Order', 'booki')
		);
	}
	
    function get_columns(){
        $columns = array(
            'bookedDates'=>__('DATES AND TIME', 'booki')
			, 'projectNames'=>__('PROJECT/S', 'booki')
			, 'formElements'=>__('ADDITIONAL INFO', 'booki')
			, 'action'=>__('Action', 'booki')
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
		$fromDate = new Booki_DateTime(date('Y-m-01'));
		$toDate = new Booki_DateTime(date('Y-m-t'));
		$toDate->modify('+ 2 months');
		$userId = null;
		
		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_viewbookings'){
			if (array_key_exists('from', $_GET) && array_key_exists('to', $_GET)){
				$fromDate = new Booki_DateTime($_GET['from']);
				$toDate = new Booki_DateTime($_GET['to']);
			}
			if (array_key_exists('userid', $_GET)){
				$userId = (int)$_GET['userid'];
			}
		}

		if(!$this->hasFullControl){
			$user = wp_get_current_user();
			$userId = $user->ID;
		}
		$orderRepository = new Booki_OrderRepository();
        $result = $orderRepository->readAllBookings($this->currentPage, $this->perPage, $fromDate, $toDate, $userId, null, null, $this->hasFullControl);
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