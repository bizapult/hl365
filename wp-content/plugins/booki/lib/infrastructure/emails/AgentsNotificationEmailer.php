<?php
class Booki_AgentsNotificationEmailer extends Booki_NotificationEmailer{
	public function __construct($args)
	{
		//array('emailType'=>$emailType, 'orderId'=>$orderId)
		parent::__construct($args);
	}
	
	public function send($projectId = null, $to = null){
		$projectList = Booki_BookingProvider::bookedDaysRepository()->readAgentToNotifyByOrderId($this->orderId);
		if($projectList && count($projectList) > 0){
			foreach($projectList as $project){
				if($project['notifyUserEmailList']){
					$result = parent::send($project['id'], $project['notifyUserEmailList']);
				}
			}
		}
	}
}
?>