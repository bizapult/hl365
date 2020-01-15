<?php
class Booki_ManageRolesList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_manageroles_';
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
	function column_projectId($item){
		return sprintf('<p><span>#%s</span></p>', $item['projectId']);
	}
	function column_projectName($item){
		return sprintf('<p><span>%s</span></p>', $item['projectName']);
	}
	function column_userId($item){
		return sprintf('<p><span>%s</span></p>', $item['userId']);
	}
	function column_email($item){
		return sprintf('<p><span>%s</span></p>', $item['email']);
	}
	function column_username($item){
		return sprintf('<p><span>%s</span></p>', $item['username'] ? $item['username'] : '--' );
	}
	function column_role($item){
		return sprintf('<p><span>%s</span></p>', $item['role']);
	}
	
	function column_action($item){
		if(!$this->hasFullControl){
			return 'actions blocked';
		}
		$fields = array();
		$buttonGroups = array();
		
		$selectUrl = esc_url(add_query_arg(array('roleid'=>$item['id'], 'projectid'=>$item['projectId'], 'role'=>$item['role'], 'email'=>$item['email'], 'booki_manageroles_paged'=>$this->get_pagenum()), remove_query_arg('command')));
		
		$allowCancellation = false;
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
		
		array_push($buttonGroups, sprintf(
			'<li>
				<a href="%s" class="booki-btnlink btn btn-default" title="%s">
					<i class="glyphicon glyphicon-trash"></i>
					%s
				</a>
			</li>'
			, $selectUrl .= '&command=delete'
			, __('Deletes this role', 'booki')
			, __('Delete', 'booki')
		));

		array_push($buttonGroups, '</ul>');
        return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="booki_manageroles" />
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
			, 'projectId'=>__('Project Id', 'booki')
			, 'projectName'=>__('Project Name', 'booki')
			, 'userId'=>__('User Id', 'booki')
			, 'email'=>__('Email', 'booki')
			, 'username'=>__('Username', 'booki')
			, 'action'=>__('Action', 'booki')
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
		
		$projectId = null;
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 
		$userId = null;
		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_manageroles'){
			if (array_key_exists('projectid', $_GET)){
				$projectId = (int)$_GET['projectid'];
			}
			if (array_key_exists('userid', $_GET)){
				$userId = (int)$_GET['userid'];
			}
		}

		$rolesRepository = new Booki_RolesRepository();
        $result = $rolesRepository->readAll($this->currentPage, $this->perPage, $this->orderBy, $this->order, $projectId, $userId);
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