<?php
class Booki_DataViewerList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_dataviewer_';
        parent::__construct( array(
            'singular'  => 'dataItem', 
            'plural'    => 'dataItems', 
            'ajax'      => false    
        ) );
    }
	

    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item[$column_name],true); 
        }
    }
    function get_columns(){
		$columns = array(
            'name'=>__('Column name', 'booki')
			, 'value'=>__('value', 'booki')
        );
		if(count($this->items) > 0){
			$columns = array();
			foreach($this->items[0] as $key=>$value){
				if($key === 'total_rows'){
					continue;
				}
				$columns[$key] = $key;
			}
		}
        return $columns;
    }
    /**
		@description binds to data
	*/
    function bind() {
        
        
        $this->currentPage = $this->get_pagenum() - 1;
		if( $this->currentPage ){
			$this->currentPage = $this->currentPage * $this->perPage;
		}

        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 
		$tablename = isset($_GET['tablename']) && trim($_GET['tablename']) ? $_GET['tablename'] : null;
		if(!$tablename){
			return;
		}
		$dataViewerRepository = new Booki_DataViewerRepository();

        $result = $dataViewerRepository->readAll($tablename, $this->currentPage, $this->perPage);
		if(!$result['result'] || count($result['result']) === 0){
			return;
		}
		$total_items = $result['total'];
        $this->totalPages = ceil($total_items / $this->perPage);
        
        $this->items = $result['result'];
        
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
		
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