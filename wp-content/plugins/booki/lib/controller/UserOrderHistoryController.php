<?php
class Booki_UserOrderHistoryController extends Booki_BaseController{
	private $hasFullControl;
	private $canEdit;
	private $globalSettings;
	
	public function __construct($cancelAllCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_userorderhistory')){
			return;
		}
		
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		if (array_key_exists('cancelAll', $_POST)){
			$this->cancelAll($cancelAllCallback);
		}
	}
	
	public function cancelAll($callback){
		$orderId = (int)$_POST['cancelAll'];
		Booki_BookingProvider::cancelOrderAndNotifyAdmin($orderId);
		$this->executeCallback($callback, array($orderId));
	}
	
}
?>