<?php
class Booki_SeatsBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_seatAvailable', array($this, 'seatAvailableCallback')); 
		add_action('wp_ajax_nopriv_booki_seatAvailable', array($this, 'seatAvailableCallback')); 
	}
	
	public  function seatAvailableCallback(){
		$model = $_POST['model'];
		$projectId = isset($model['projectId']) ? (int)$model['projectId'] : null;
		$bookingDate = isset($model['bookingDate']) ? $model['bookingDate'] : null;
		$hourStart = isset($model['hourStart']) ? $model['hourStart'] : null;
		$minuteStart = isset($model['minuteStart']) ? $model['minuteStart'] : null;
		$hourEnd = isset($model['hourEnd']) ? $model['hourEnd'] : null;
		$minuteEnd = isset($model['minuteEnd']) ? $model['minuteEnd'] : null;
		$bookedDaysRepo = new Booki_BookedDaysRepository();
		//ToDO: add hourStart, minuteStart, hourEnd, minuteEnd to read by days
		$bookedDays = $bookedDaysRepo->readByDays(array($bookingDate), $projectId);
		$calendarRepo = new Booki_CalendarRepository();
		$calendar = $calendarRepo->readByProject($projectId);
		$calendarDayRepo = new Booki_CalendarDayRepository();
		$calendarDay = $calendarDayRepo->readByDay($bookingDate, $calendar->id);
		$seatsBooked = $bookedDays->count();
		$seats = $calendar->bookingLimit;
		if($calendarDay){
			$seats = $calendarDay->bookingLimit;
		}
		//echo Booki_Helper::json_encode_response($result, 'result');
		die();
	}
}
?>
