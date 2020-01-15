<?php
class Booki_FullCalendarList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	function __construct( ){
		$this->perPage = 25;
		$this->uniqueNamespace = 'booki_viewbookings_';
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	public function print_column_headers( $with_id = true ) {}
	function single_row( $item ) {}
		public function display_tablenav( $which ) {
				if ( 'top' == $which ){
					wp_nonce_field( 'bulk-' . $this->_args['plural'] );
				}else{
					return;
				}
		?>
			<div class="<?php echo esc_attr( $which ); ?>">

				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
		<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
		?>

				<br class="clear" />
			</div>
		<?php
		}
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
	
    function get_columns(){
        $columns = array();
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
        $this->currentPage = -1;
		$fromDate = new Booki_DateTime(date('Y-m-01'));
		$toDate = new Booki_DateTime(date('Y-m-t'));
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
		$this->items = $result;
    }
	
	function get_table_classes() {
		return array( 'booki', 'booki-grid', 'table', 'table-bordered');
	}
}
?>