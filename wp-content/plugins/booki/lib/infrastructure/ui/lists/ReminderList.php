<?php
class Booki_ReminderList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	public $totalItemsCount = 0;
	function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_reminders_';
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$id = isset($_POST['reminderid']) ? (int)$_POST['reminderid'] : null;
		if($item['id'] == $id){
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
		return $item['orderId'];
	}
	function column_sentDate($item){
		return $item['sentDate']->format($this->shorthandDateFormat);
	}
	function column_name($item){
		$fullname = trim($item['firstname'] . ' ' . $item['lastname']);
		return  $fullname ? $fullname : '--';
	}
	function column_email($item){
		return $item['email'];
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
				<button class="booki-btnlink btn btn-default" name="resend" value="%d" title="%s">
					<i class="glyphicon glyphicon-send"></i> 
					%s
				</button>
			</li>'
			, $item['orderId']
			, __('Send reminder again.', 'booki')
			, __('Send another reminder', 'booki') 
		));
		array_push($buttonGroups, sprintf(
			'<li>
				<button class="booki-btnlink btn btn-default" name="delete" value="%d" title="%s">
					<i class="glyphicon glyphicon-remove"></i> 
					%s
				</button>
			</li>'
			, $item['id']
			, __('Delete this reminder.', 'booki')
			, __('Delete', 'booki') 
		));
		array_push($buttonGroups, '</ul>');
        return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="booki_reminders" />
				<input type="hidden" name="reminderid" value="%d" />
				<div class="form-group">
					<div class="grid-btn-group">
						<div class="btn-group">
							%s
						</div>
					</div>
				</div>
			</form>'
			, $_SERVER['REQUEST_URI']
			, $item['id']
			, join("\n", $buttonGroups)
        );
	}
	
    function get_columns(){
        $columns = array(
			'id'=>__('ID', 'booki')
			, 'orderId'=>__('ORDER ID', 'booki')
			, 'sentDate'=>__('SENT DATE', 'booki')
            , 'name'=>__('NAME', 'booki')
			, 'email'=>__('EMAIL', 'booki')
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
		$userId = null;
		
		if(!$this->hasFullControl){
			$user = wp_get_current_user();
			$userId = $user->ID;
		}
		$remindersRepository = new Booki_RemindersRepository();
        $result = $remindersRepository->readAll($this->currentPage, $this->perPage, $this->orderBy, $this->order, $userId);

        $this->totalPages = ceil($result->total / $this->perPage);
        $this->totalItemsCount = $result->total;
        $this->items = $result->toArray();
        $this->set_pagination_args( array(
            'total_items' => $this->totalItemsCount,
            'per_page'    => $this->perPage,
            'total_pages' => $this->totalPages
        ) );
    }
	
	function get_table_classes() {
		return array( 'booki', 'booki-grid', 'table', 'table-bordered');
	}
}
?>