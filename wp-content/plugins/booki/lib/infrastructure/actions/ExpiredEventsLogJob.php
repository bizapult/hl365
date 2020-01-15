<?php
class Booki_ExpiredEventsLogJob{
	const HOOK = 'Booki_ExpiredEventsLogJobEventHook';
	public function __construct()
	{
		if ( !wp_next_scheduled('Booki_ExpiredEventsLogJobEventHook'))
		{
			wp_schedule_event( time(), 'daily', 'Booki_ExpiredEventsLogJobEventHook' );
		}
	}
	public static function init(){
		$globalSettings = BOOKIAPP()->globalSettings;
		$eventsLogRepo = new Booki_EventsLogRepository();
		$days = $globalSettings->eventsLogExpiry;
		if($days > 0){
			$today = new Booki_DateTime();
			$expiryDate = date('Y-m-d', strtotime($today->format('Y-m-d') . " - $days days"));
			$result = $eventsLogRepo->deleteExpired($expiryDate);
		}
	}
}

?>