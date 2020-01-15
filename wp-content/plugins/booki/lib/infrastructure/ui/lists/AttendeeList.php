<?php
class Booki_AttendeeList extends Booki_ULList {
	public $perPage;
	public $orderBy;
	public $order;
	public $bookingDate;
	public $projectId;
	public $hourStart;
	public $minuteStart;
	public $hourEnd;
	public $minuteEnd;
  function __construct($projectId, $bookingDate = null, $hourStart = null, $minuteStart = null, $hourEnd = null, $minuteEnd = null){
		$this->bookingDate = $bookingDate;
		$this->projectId = $projectId;
		$this->hourStart = $hourStart;
		$this->minuteStart = $minuteStart;
		$this->hourEnd = $hourEnd;
		$this->minuteEnd = $minuteEnd;
		$this->uniqueNamespace = 'booki_attendee_';
        parent::__construct( array(
            'singular'  => 'attendee', 
            'plural'    => 'attendees', 
            'ajax'      =>  false    
        ), true );
    }
	public function no_items() {
		_e( 'No attendees found.', 'booki' );
	}
    function print_column_headers( $with_id = true ) {
		return false;
	}

    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
	
    function column_attendee($item){
		$content = array();
		$row = array();
		$name = trim($item['firstname'] . ' ' . $item['lastname']);
		$name = $name ? $name : '--';
		array_push($content, '<div>%s</div>');
		array_push($row, $name);
		array_push($row, Booki_DateHelper::localizedWPDateFormat($item['bookingDate']));
		if($item['hasTime']){
			$bookingTime = sprintf('%02d:%02d:00', $item['hourStart'], $item['minuteStart']);
			if(!$item['enableSingleHourMinuteFormat']){
				$bookingTime .= sprintf(' - %02d:%02d:00', $item['hourEnd'], $item['minuteEnd']);
			}
			array_push($row, $bookingTime);
		}
		array_push($row, $item['status']);
		return sprintf(join('', $content), join(', ', $row));
	}
	
    function get_columns(){
        return array( 'attendee'=>'');
    }

    /**
		@description binds to data
	*/
    function bind() {
        $per_page = 10;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $current_page = $this->get_pagenum() - 1;
		if($current_page){
			$current_page = $current_page * $per_page;
		}

		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_attendee'){
			if (array_key_exists('bookingdate', $_GET)){
				$this->bookingDate = new Booki_DateTime($_GET['bookingdate']);
			}
			if (array_key_exists('projectid', $_GET)){
				$this->projectId = (int)$_GET['projectid'];
			}
			if (array_key_exists('hourstart', $_GET)){
				$this->hourStart = (int)$_GET['hourstart'];
			}
			if (array_key_exists('minutestart', $_GET)){
				$this->minuteStart = (int)$_GET['minutestart'];
			}
			if (array_key_exists('hourend', $_GET)){
				$this->hourEnd = (int)$_GET['hourend'];
			}
			if (array_key_exists('minuteend', $_GET)){
				$this->minuteEnd = (int)$_GET['minuteend'];
			}
		}
		$this->items = array();
		$total_items = 0;
		if(is_int($this->projectId)){
			$orderRepository = new Booki_OrderRepository();
			$result = $orderRepository->readAttendees($current_page, $per_page, $this->projectId, $this->bookingDate, $this->hourStart, $this->minuteStart, $this->hourEnd, $this->minuteEnd);
			$total_pages = ceil($result->total / $per_page);
			$total_items = $result->total;
			$this->items = $result->toArray();
		}
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ) );
    }

	public function localizedWPDateFormat($date){
		$dateFormat = get_option('date_format');
		return date_i18n($dateFormat, strtotime($date->format(DateTime::ISO8601)));
	}
}
?>