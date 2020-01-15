<?php
class Booki_OrderCancelNotificationEmailer extends Booki_NotificationEmailer{
	public function __construct($args){
		$orderId = null;
		$bookedDayId = null;
		$bookedOptionalId = null;
		$bookedCascadingItemId = null;
		if(array_key_exists('orderId', $args)){
			$orderId = $args['orderId'];
		}
		if(array_key_exists('bookedDayId', $args)){
			$bookedDayId = $args['bookedDayId'];
		}
		if(array_key_exists('bookedOptionalId', $args)){
			$bookedOptionalId = $args['bookedOptionalId'];
		}
		if(array_key_exists('bookedCascadingItemId', $args)){
			$bookedCascadingItemId = $args['bookedCascadingItemId'];
		}
		parent::__construct(array(
			'emailType'=>Booki_EmailType::BOOKING_CANCEL_REQUEST
			, 'orderId'=>$orderId
			, 'bookedDayId'=>$bookedDayId
			, 'bookedOptionalId'=>$bookedOptionalId
			, 'bookedCascadingItemId'=>$bookedCascadingItemId
		));
	}
	
	public function send($projectId = null, $to = null){
		$projectList = Booki_BookingProvider::bookedDaysRepository()->readAgentToNotifyByOrderId($this->orderId);
		if($this->bookedDay){
			$projectId = $this->bookedDay->projectId;
		}else if($this->bookedOptional){
			$projectId = $this->bookedOptional->projectId;
		}else if($this->bookedCascadingItem){
			$projectId = $this->bookedCascadingItem->projectId;
		}
		
		if($projectList && count($projectList) > 0){
			foreach($projectList as $project){
				$settings = BOOKIAPP()->globalSettings;
				$recipient = $settings->notificationEmailTo;
				if($project['notifyUserEmailList']){
					$recipient = $project['notifyUserEmailList'];
				}
				
				if($projectId === $project['id'] || $projectId === null){
					$id = $projectId === null ? $project['id'] : $projectId;
					$result = parent::send($id, $recipient);
				}
			}
		}
	}
}
?>