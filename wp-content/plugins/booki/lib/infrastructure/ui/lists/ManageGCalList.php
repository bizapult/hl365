<?php
class Booki_ManageGCalList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_managegcal_';
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$orderId = isset($_GET['roleid']) ? (int)$_GET['roleid'] : null;
		if($item['id'] == $orderId){
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
		return sprintf('<p><span>%s</span></p>', $item['id']);
	}
	function column_email($item){
		return sprintf('<p><span>%s</span></p>', $item['email']);
	}
	function column_applicationName($item){
		return sprintf('<p><span>%s</span></p>', $item['applicationName']);
	}
	function column_projectNames($item){
		return sprintf('<p><span>%s</span></p>', $item['projectNames'] ? $item['projectNames'] : '--');
	}
	function column_clientId($item){
		return sprintf('<p><span>%s</span></p>',  $item['clientId'] ? __('Saved', 'booki') : '--');
	}
	function column_clientSecret($item){
		return sprintf('<p><span>%s</span></p>',  $item['clientSecret'] ? __('Saved', 'booki') : '--');
	}
	function column_accessToken($item){
		return sprintf('<p><span>%s</span></p>', $item['accessToken'] ? __('Authorized', 'booki') : 'Authorization required' );
	}

	function column_action($item){
		$buttonGroups = array();
		$selectUrl = esc_url(add_query_arg(array('id'=>$item['id'], 'booki_managegcal_paged'=>$this->get_pagenum()), remove_query_arg('command')));
		$authorizationUrl = null;
		if(!$item['accessToken']){
			$service = new Booki_GCalService($item['userId']);
			$authorizationUrl = $service->getAuthorizationUrl();
		}
		array_push($buttonGroups, sprintf(
			'<a class="manage-order-item btn btn-default" href="%s">
				%s
			</a>'
			, $selectUrl
			, __('Edit', 'booki')
		));
		
		array_push($buttonGroups, 
			'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu" role="menu">'
		);

		if($authorizationUrl){
			array_push($buttonGroups, sprintf(
				'<li>
					<a href="%s" class="booki-btnlink btn btn-default" title="%s" target="_blank">
						<i class="glyphicon glyphicon-ok-sign"></i>
						%s
					</a>
				</li>'
				, $authorizationUrl
				, __('Grant permission so that Booki can access this calendar', 'booki')
				, __('Authorize', 'booki')
			));
		}else{
			array_push($buttonGroups, sprintf(
				'<li>
					<button class="booki-btnlink btn btn-default" name="calendarSync" value="%d" title="%s">
						<i class="glyphicon glyphicon-refresh"></i> 
						%s
					</button>
				</li>'
				, $item['userId']
				, __('Sync bookings with calendar', 'booki')
				, __('Sync', 'booki') 
			));
			array_push($buttonGroups, sprintf(
				'<li>
					<button class="booki-btnlink btn btn-default" name="calendarDelete" value="%d" title="%s">
						<i class="glyphicon glyphicon-remove"></i> 
						%s
					</button>
				</li>'
				, $item['userId']
				, __('Delete all synced data from your google calendar', 'booki')
				, __('Delete synced data', 'booki') 
			));
		}
		array_push($buttonGroups, sprintf(
			'<li>
				<a href="%s" class="booki-btnlink btn btn-default" title="%s">
					<i class="glyphicon glyphicon-trash"></i>
					%s
				</a>
			</li>'
			, $selectUrl .= '&command=delete'
			, __('Deletes this profile', 'booki')
			, __('Delete profile', 'booki')
		));
		array_push($buttonGroups, '</ul>');
        return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="booki_managegcal" />
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
            'id'=>__('#', 'booki')
			, 'email'=>__('Email', 'booki')
			, 'applicationName'=>__('Application Name', 'booki')
			, 'projectNames'=>__('Projects synced', 'booki')
			, 'clientId'=>__('Client Id', 'booki')
			, 'clientSecret'=>__('Client Secret', 'booki')
			, 'accessToken'=>__('Authorization', 'booki')
			, 'action'=>__('Actions', 'booki')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false) );
        return $sortable_columns;
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
			$user = Booki_Helper::getUserInfo();
			$userId = $user['userId'];
		}
		$gcalRepository = new Booki_GCalRepository();
        $result = $gcalRepository->readAll($this->currentPage, $this->perPage, $this->orderBy, $this->order, $userId);
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