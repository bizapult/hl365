<?php
class Booki_EmailReminderJob{
	const HOOK = 'Booki_EmailReminderJobEventHook';
	private $timezoneString;
	public function __construct($orderId = null)
	{
		$calendarRepo = new Booki_CalendarRepository();
		$timezoneInfo = Booki_TimeHelper::timezoneInfo();
		$this->timezoneString = $timezoneInfo['timezone'];
		if($orderId !== null){
			$reminder = $calendarRepo->readReminderByOrder($orderId);
			if($reminder){
				$this->schedule($reminder, $orderId);
			}
			return;
		}
		$reminders = $calendarRepo->readAllReminders();
		foreach($reminders as $reminder){
			$this->schedule($reminder, $reminder->orderId);
		}
	}
	public function schedule($reminder, $orderId){
		$adminTimezoneInfo = Booki_TimeHelper::timezoneInfo();
		$adminTimezoneString = $adminTimezoneInfo['timezone'];
		
		$scheduleDate = new Booki_DateTime($reminder->bookingDate );
		$now = new Booki_DateTime();
		if($reminder->hourStart === null){
			$scheduleDate->setTime((int)$now->format('H'), (int)$now->format('i'), (int)$now->format('s'));
		}else{
			$scheduleDate->setTime((int)$reminder->hourStart, (int)$reminder->minuteStart, 0);
		}
		$scheduleDate->modify("-{$reminder->reminderMinutes} minutes");
		if (!self::schedulePending($orderId))
		{
			$gmt = gmdate('Y-m-d H:i:s', strtotime($scheduleDate->format('Y-m-d H:i:s') . ' ' . $adminTimezoneString));
			$result = wp_schedule_single_event(strtotime($gmt), self::HOOK, array($orderId));
		}
	}
	public static function init($orderId){
		$notificationEmailer = new Booki_NotificationEmailer(array('emailType'=>Booki_EmailType::BOOKING_REMINDER, 'orderId'=>(int)$orderId));
		$notificationEmailer->send();
		$userInfo = $notificationEmailer->getUserInfo();
		$reminderRepo = new Booki_RemindersRepository();
		$reminderRepo->insert(new Booki_EmailReminder(array(
			'firstname'=>$userInfo['firstname']
			, 'lastname'=>$userInfo['lastname']
			, 'email'=>$userInfo['email']
			, 'sentDate'=>new Booki_DateTime()
			, 'orderId'=>(int)$orderId
		)));
	}
	public static function getSchedulesCount(){
		$crons = _get_cron_array();
		$result = 0;
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				if(strpos($hook, self::HOOK)!== false){
					++$result;
				}
			}
		}
		return $result;
	}
	public static function cancelAllSchedules(){
		$crons = _get_cron_array();
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				if(strpos($hook, self::HOOK)!== false){
					wp_clear_scheduled_hook($hook, $params[key($params)]['args']);
				}
			}
		}
	}
	public static function schedulePending($orderId){
		$result = false;
		$crons = _get_cron_array();
		foreach ($crons as $timestamp=>$hooks) { 
			foreach ((array)$hooks as $hook=>$params) {
				if(strpos($hook, self::HOOK)!== false){
					$args = $params[key($params)]['args'];
					if($args[0] == $orderId){
						$result = true;
						break 2;
					}
				}
			}
		}
		return $result;
	}
}
?>