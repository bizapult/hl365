<?php
class Booki_OrdersTotalAmountAggregateList extends Booki_List {
	public $perPage;
	public $orderBy;
	public $order;
  function __construct($userId = null){
		$this->uniqueNamespace = 'booki_orders_total_amount_aggregate_';
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
    
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
    
	function column_totalAmount($item){
		$result = sprintf('<p><span>%s</span></p>'
			, Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney($item['totalAmount']), true)
		);
		return $result;
	}
	
	function column_discount($item){
		$result = sprintf('<p><span>%s</span></p>'
			, Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney($item['discount']), true)
		);
		return $result;
	}
	
	function column_orderDate($item){
		$orderDate = new Booki_DateTime($item['orderDate']);
		$result = sprintf('<p><span>%s</span></p>'
			, Booki_DateHelper::localizedWPDateFormat($orderDate)
		);
		return $result;
	}
	
    function get_columns(){
        $columns = array(
			'totalAmount'=>__('Total Amount', 'booki')
			, 'discount'=>__('Total Discounts', 'booki')
			, 'orderDate'=>__('Order Date', 'booki')
			
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'orderDate'=> array('orderDate', true)
        );
        return $sortable_columns;
    }
    
    /**
		@description binds to data
	*/
    function bind() {
		$this->items = array();
        $per_page = 10;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $current_page = $this->get_pagenum() - 1;
		if($current_page){
			$current_page = $current_page * $per_page;
		}

        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 
		$period =  (!empty($_REQUEST[$this->uniqueNamespace . 'period'])) ? intval($_REQUEST[$this->uniqueNamespace . 'period']) : 3; 
		$userId = null;
		$statsRepository = new Booki_StatsRepository();
		if(!$this->hasFullControl){
			$userId = get_current_user_id();
		}
        $result = $statsRepository->readOrdersTotalAmountAggregate($userId, $current_page, $per_page, $this->orderBy, $this->order, $period);
		if(!$result['result']){
			return;
		}
        $total_pages = ceil((int)$result['total'] / $per_page);
        $total_items = (int)$result['total'];
        foreach($result['result'] as $r){
			array_push($this->items, array(
				'totalAmount'=>(double)$r->totalAmount
				, 'discount'=>(double)$r->discount
				, 'orderDate'=>$r->orderDate
			));
		}
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ) );
    }
	
	function get_table_classes() {
		return array( 'booki', 'booki-grid', 'table', 'table-bordered');
	}
}
?>