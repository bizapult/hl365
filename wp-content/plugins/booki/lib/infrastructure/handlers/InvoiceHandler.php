<?php 
	class Booki_InvoiceHandler extends Booki_CSVBaseHandler
	{
		public function __construct(){
			$orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
			$globalSettings = BOOKIAPP()->globalSettings;
			
			$userId = get_current_user_id();
			 if(!is_user_logged_in() && $globalSettings->membershipRequired){
				auth_redirect();
			}
			if($orderId !== null){
				$repo = new Booki_OrderRepository();
				$order = $repo->read($orderId);
				
				if($order && !$globalSettings->membershipRequired || ($order && ($order->userId === null || ($userId === $order->userId || Booki_PermissionHelper::hasAdministratorPermission())))){
					$notificationEmailer = new Booki_NotificationEmailer(array('emailType'=>Booki_EmailType::INVOICE, 'orderId'=>$orderId));
					$notificationEmailer->generateInvoice();
				}
			}
			parent::__construct();
		}
	}
?>