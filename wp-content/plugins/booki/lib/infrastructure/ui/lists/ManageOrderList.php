<?php
class Booki_ManageOrderList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_manageorder_';
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
		$name = '';
		$result = array();
		if($item['notRegUserFirstname']){
			$name = $item['notRegUserFirstname'];
		}else if($item['firstname']){
			$name = $item['firstname'];
		}
		if($item['notRegUserLastname']){
			$name .= ' ' . $item['notRegUserLastname'];
		}else if($item['lastname']){
			$name .= ' ' . $item['lastname'];
		}
		if($name){
			array_push($result, $name);
		}
		if($item['notRegUserEmail']){
			array_push($result, $item['notRegUserEmail']);
		} else if($item['email']){
			array_push($result, $item['email']);
		}
		return implode(', ', $result);
	}
	
	function column_orderDate($item){
		return $item['orderDate']->format($this->shorthandDateFormat);
	}
	
	function column_paymentDate($item){
		return $item['paymentDate'] ? $item['paymentDate']->format($this->shorthandDateFormat) : '--';
	}
	
	function column_discount($item){
		return $item['discount'] . '%';
	}
	
	function column_tax($item){
		return $item['tax'] . '%';
	}
	
	function column_totalAmount($item){
		$result = sprintf('<span title="%s">%s</span>'
			, __('Total amount paid, after discount.', 'booki')
			, Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney($item['totalAmount']), true)
		);
		if($item['refundAmount']){
			$result .= sprintf('<span title="%s"> - %s</span>'
				, __('Refunded amount.', 'booki')
				, Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney($item['refundAmount']), true)
			);
		}
		return $result;
	}
	
	function column_userIsRegistered($item){
		$label = '<span class="label label-%s">%s</span>';
		return $item['userIsRegistered'] ? sprintf($label, 'success', __('Member', 'booki')) : sprintf($label, 'danger', __('Not member', 'booki'));
	}
	
	function column_approvalStatus($item){
		return sprintf('<span class="label label-%s">%s</span>', $item['approvalStatusLabel'], $item['approvalStatus']);
	}
	
    function column_status($item){
		$span = '<span class="label %1$s" title="%2$s">%3$s</span>';
		$result = '';
		if($item['status'] == Booki_PaymentStatus::PAID){
			$result = sprintf(
				$span
				, 'label-success'
				, __('Order has been paid. Send a confirmation.', 'booki')
				, __('Paid','booki')
			);
		}else if ($item['status'] == Booki_PaymentStatus::REFUNDED){
			$result = sprintf(
				$span
				, 'label-warning'
				, __('Payment has been refunded.', 'booki')
				, __('Refunded','booki')
			);
		}else if ($item['status'] == Booki_PaymentStatus::PARTIALLY_REFUNDED){
			$result = sprintf(
				$span
				, 'label-warning'
				, __('Payment has been partially refunded. You can refund the remainder by clicking refund.', 'booki')
				, __('Partially Refunded','booki')
			);
		}else{
			//unpaid
			$result = sprintf(
				$span
				, 'label-info'
				, __('Not yet paid. Try sending an invoice.', 'booki')
				, __('Pending','booki')
			);
		}
		
		if($item['invoiceNotification']){
			if($result){
				$result .= '<br/>';
			}
			$result .= sprintf(
				$span
				, 'label-success'
				, __('Number of invoices emailed.', 'booki')
				, __('Invoice', 'booki') . ' [' . $item['invoiceNotification'] . ']'
			);
		}
		
		if($item['refundNotification']){
			if($result){
				$result .= '<br/>';
			}
			$result .= sprintf(
				$span
				, 'label-success'
				, __('Number of refund notifications emailed.', 'booki')
				, __('Refund', 'booki') . ' [' . $item['refundNotification'] . ']'
			);
		}
        return $result;
    }
    
	function column_token($item){
		$fields = array();
		$buttonGroups = array();
		$selectUrl = esc_url(add_query_arg(array('orderid'=>$item['id'], 'timezone'=>false, 'booki_manageorder_paged'=>$this->get_pagenum()), remove_query_arg('command')));
		$canEdit = Booki_PermissionHelper::isProjectsEditor($item['projectIdList']);
		array_push($buttonGroups, sprintf(
			'<a class="manage-order-item btn btn-default" href="%s">
				%s
			</a>'
			, $selectUrl
			, __('View details', 'booki')
		));
		
		array_push($buttonGroups, 
			'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu" role="menu">'
		);
		
		if($item['totalAmount'] > 0 && ($item['status'] == Booki_PaymentStatus::PAID || $item['status'] == Booki_PaymentStatus::PARTIALLY_REFUNDED)){
			/*$today = new Booki_DateTime();
			$daysLeft = $item['paymentDate'] ? 60 - Booki_Helper::date_diff($item['paymentDate']->format('Y-m-d'), $today->format('Y-m-d')) : 0;
			$refundTitleText = $daysLeft . ' ' . __('days left before refund opportunity expires.', 'booki');*/
			$refundTitleText = __('Mark item as refunded.', 'booki');
			if(/*$daysLeft > 0 &&*/ $canEdit){
				array_push($fields, sprintf('<input type="hidden" name="amount" value="%s"/>', $item['totalAmount'] - $item['refundAmount']));
				array_push($fields, sprintf('<input type="hidden" name="currency" value="%s"/>', $item['currency']));
				array_push($fields, sprintf('<input type="hidden" name="refundType" value="%s"/>', 
					$item['status'] == Booki_PaymentStatus::PARTIALLY_REFUNDED ? 
					'Partial' : 'Full'
				));
				array_push($fields, sprintf('<input type="hidden" name="orderId" value="%s"/>', $item['id']));
				array_push($buttonGroups, sprintf(
					'<li>
						<button class="booki-btnlink btn btn-default" name="refund" value="%s" title="%s">
							<i class="glyphicon glyphicon-minus-sign"></i> 
							%s
						</button>
					</li>'
					, $item['transactionId']
					, $refundTitleText
					, __('Refund', 'booki') 
				));
			}
		}else if ($item['status'] == Booki_PaymentStatus::UNPAID && $canEdit){
			array_push($buttonGroups, sprintf(
				'<li>
					<button class="booki-btnlink btn btn-default" name="invoiceNotification" value="%s" title="%s">
						<i class="glyphicon glyphicon-envelope"></i> 
						%s
					</button>
				</li>'
				, $item['id']
				, __('Sends an invoice to the client with payment instructions.', 'booki')
				, __('Invoice', 'booki')
			));
		}else if ($item['status'] == Booki_PaymentStatus::REFUNDED && $canEdit){
			array_push($fields, sprintf('<input type="hidden" name="refundAmount" value="%s"/>', $item['refundAmount']));
			
			array_push($buttonGroups, sprintf(
				'<li>
					<button class="booki-btnlink btn btn-default" name="refundNotification" value="%s" title="%s">
						<i class="glyphicon glyphicon-envelope"></i> 
						%s
					</button>
				</li>'
				, $item['id']
				, __('Sends a refund confirmation email to user.', 'booki')
				, __('Refunded', 'booki')
			));
		}
		
		if($canEdit){
			if($item['status'] == Booki_PaymentStatus::UNPAID){
				array_push($buttonGroups, sprintf(
					'<li>
						<button class="booki-btnlink btn btn-default" name="markPaid" value="%s" title="%s">
							<i class="glyphicon glyphicon-ok-sign"></i> 
							%s
						</button>
					</li>'
					, $item['id']
					, __('Marks the order as paid', 'booki')
					, __('Paid', 'booki')
				));
			}
		}
		
		if(($canEdit && $item['hasPendingApproval']) || $item['hasPendingCancellation']){
			array_push($buttonGroups, sprintf(
				'<li>
					<input type="hidden" name="userIsRegistered" value="%d" />
					<button class="booki-btnlink btn btn-default" name="approveAll" value="%s" title="%s">
						<i class="glyphicon glyphicon-thumbs-up"></i> 
						%s
					</button>
				</li>'
				, $item['userIsRegistered']
				, $item['id']
				, __('Approve all items in this order', 'booki')
				, __('Approve', 'booki')
			));
		}
		
		if($canEdit){
			array_push($buttonGroups, sprintf(
				'<li>
					<a href="%s" class="booki-btnlink btn btn-default" title="%s">
						<i class="glyphicon glyphicon-trash"></i>
						%s
					</a>
				</li>'
				, $selectUrl .= '&command=delete'
				, __('Cancels this order', 'booki')
				, __('Cancel', 'booki')
			));

			if(!$item['userIsRegistered']){
				if($item['notRegUserEmail']){
					array_push($buttonGroups, sprintf(
						'<li>
							<input type="hidden" name="userFirstname" value="%s" />
							<input type="hidden" name="userLastname" value="%s" />
							<input type="hidden" name="userEmail" value="%s" />
							<button class="booki-btnlink btn btn-default" name="registerUser" value="%d" title="%s">
								<i class="glyphicon glyphicon-thumbs-up"></i> 
								%s
							</button>
						</li>'
						, $item['notRegUserFirstname']
						, $item['notRegUserLastname']
						, $item['notRegUserEmail']
						, $item['id']
						, __('Create user account for this user and register with system.', 'booki')
						, __('Register User', 'booki')
					));
				}else{
					array_push($buttonGroups, sprintf(
						'<li>
							<a href="%s" class="booki-btnlink btn btn-default" title="%s">
								<i class="glyphicon glyphicon-trash"></i>
								%s
							</a>
						</li>'
						, esc_url(add_query_arg(array('orderid'=>$item['id'], 'command'=>'adduser')))
						, __('No user associated with booking. Create one ?', 'booki')
						, __('Add User', 'booki')
					));
				}
			}
		}
		array_push($buttonGroups, '</ul>');
        return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="booki_managebookings" />
				%s
				<div class="form-group">
					<div class="grid-btn-group">
						<div class="btn-group">
							%s
						</div>
					</div>
				</div>
			</form>'
			, $_SERVER['REQUEST_URI']
			, join( "\n", $fields)
			, join("\n", $buttonGroups)
        );
	}
	
    function get_columns(){
        $columns = array(
              'id'=>__('#', 'booki')
			, 'email'=>__('User Info', 'booki')
			, 'orderDate'=>__('Order Date', 'booki')
			, 'paymentDate'=>__('Payment Date', 'booki')
			, 'totalAmount'=>__('Amount', 'booki')
			, 'discount'=>__('Discount', 'booki')
			, 'tax'=>__('Tax', 'booki')
			, 'status'=>__('Payment', 'booki')
			, 'userIsRegistered'=>__('Registration', 'booki')
			, 'approvalStatus'=>__('Approval status', 'booki')
			, 'token'=>__('Task', 'booki')
        );
		$globalSettings = BOOKIAPP()->globalSettings;
		if(!$globalSettings->enablePayments){
			unset($columns['paymentDate']);
			unset($columns['status']);
		}
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false)
            , 'orderDate'=> array('orderDate', true)
			, 'totalAmount'=>array('totalAmount', false)
			, 'discount'=>array('discount', false)
			, 'tax'=>array('tax', false)
			, 'status'=>array('status', false)
			, 'userIsRegistered'=>array('isRegistered', false)
        );
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
		
		$orderId = null;
		$external = isset($_REQUEST['external']) ? $_REQUEST['external'] : null;
		if($external){
			$orderId = isset($_REQUEST['orderid']) ? $_REQUEST['orderid'] : null;
		}
        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 
		$fromDate = new Booki_DateTime(date('Y-m-01'));
		$toDate = new Booki_DateTime(date('Y-m-t'));
		$toDate->modify('+ 2 months');
		$userId = null;
		$status = null;
		
		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_managebookings'){
			if (array_key_exists('from', $_GET) && array_key_exists('to', $_GET)){
				$fromDate = new Booki_DateTime($_GET['from']);
				$toDate = new Booki_DateTime($_GET['to']);
			}
			if (array_key_exists('userid', $_GET)){
				$userId = (int)$_GET['userid'];
			}
			if (array_key_exists('status', $_GET)){
				$status = (int)$_GET['status'];
			}
		}
		if(!$this->hasFullControl){
			$user = wp_get_current_user();
			$userId = $user->ID;
		}
		$orderRepository = new Booki_OrderRepository();
        $result = $orderRepository->readAll($this->currentPage, $this->perPage, $this->orderBy, $this->order, $fromDate, $toDate, $userId, $status, $orderId, $this->hasFullControl);
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