<?php
class Booki_GCalHelper{
	public static function updateByOrder($orderId){
		$rolesRepo = new Booki_RolesRepository();
		$orderRepo = new Booki_OrderRepository();
		$projectIdList = $orderRepo->readOrderProjects($orderId);
		foreach($projectIdList as $projectItem){
			$providers = $rolesRepo->readByProject((int)$projectItem->id);
			foreach($providers as $provider){
				$service = new Booki_GCalService($provider->userId);
				$result = $service->updateEventByOrder($orderId);
			}
		}
	}
	
	public static function updateByBookedDay($bookedDayId, $projectId){
		$rolesRepo = new Booki_RolesRepository();
		$providers = $rolesRepo->readByProject((int)$projectId);
		foreach($providers as $provider){
			$service = new Booki_GCalService($provider->userId);
			$service->updateEventByBookedDay($bookedDayId);
		}
	}
	public static function deleteByBookedDay($bookedDayId, $projectId){
		$rolesRepo = new Booki_RolesRepository();
		$providers = $rolesRepo->readByProject((int)$projectId);
		foreach($providers as $provider){
			$service = new Booki_GCalService($provider->userId);
			$service->deleteEventByBookedDay($bookedDayId);
		}
	}
	public static function deleteByOrder($orderId){
		$rolesRepo = new Booki_RolesRepository();
		$orderRepo = new Booki_OrderRepository();
		$projectIdList = $orderRepo->readOrderProjects($orderId);
		foreach($projectIdList as $projectItem){
			$providers = $rolesRepo->readByProject((int)$projectItem->id);
			foreach($providers as $provider){
				$service = new Booki_GCalService($provider->userId);
				$service->deleteEventByOrder($orderId);
			}
		}
	}
	public static function deleteCalendarByProject($projectId){
		$rolesRepo = new Booki_RolesRepository();
		$providers = $rolesRepo->readByProject($projectId);
		if(!$providers){
			return;
		}
		foreach($providers as $provider){
			$service = new Booki_GCalService($provider->userId);
			$service->deleteAllCalendars(array($projectId));
		}
	}
}
?>