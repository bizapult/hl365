<?php
class Booki_ExpiredBookingsJob{
	const HOOK = 'Booki_ExpiredBookingsJobEventHook';
	public function __construct()
	{
		add_filter('cron_schedules', array($this, 'customInterval'));
		if (!wp_next_scheduled('Booki_ExpiredBookingsJobEventHook'))
		{
			wp_schedule_event( time(), 'booki_seconds', 'Booki_ExpiredBookingsJobEventHook' );
		}
	}
	public function customInterval($schedules){
		$globalSettings = BOOKIAPP()->globalSettings;
		$interval = self::getOrderExpiry($globalSettings->unpaidOrderExpiry, $globalSettings->orderExpiryMode);
		$schedules['booki_seconds'] = array(
		   'interval' => $interval,
		   'display'=> 'Seconds interval'
		);
		return $schedules;
	}
	public static function getOrderExpiry($unpaidOrderExpiry, $orderExpiryMode){
		$result = 0;
		switch($orderExpiryMode){
			case 0://day
			$result = (($unpaidOrderExpiry * 24) * 60) * 60; 
			break;
			case 1://week
			$result = ((($unpaidOrderExpiry * 7) * 24) * 60) * 60;
			break;
			case 2://hour
			$result = ($unpaidOrderExpiry * 60) * 60;
			break;
			case 3://minute
			$result = ($unpaidOrderExpiry * 60);
			break;
			case 4://second
			$result = $unpaidOrderExpiry;
			break;
		}
		return $result;
	}
	public static function init(){
		$globalSettings = BOOKIAPP()->globalSettings;
		if($globalSettings->unpaidOrderExpiry > 0){
			$interval = self::getOrderExpiry($globalSettings->unpaidOrderExpiry, $globalSettings->orderExpiryMode);
			$repo = new Booki_OrderRepository();
			$result = $repo->deleteExpired($interval);
		}
	}
}
?>