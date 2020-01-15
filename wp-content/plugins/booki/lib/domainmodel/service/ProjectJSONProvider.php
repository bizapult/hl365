<?php
class Booki_ProjectJSONProvider extends Booki_ProviderBase{
	private static $instance;
	protected function __construct()
	{
	}
	public static function repository()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Booki_ProjectRepository();
		}
		return self::$instance;
	}
	
	public static function duplicateProject(){
		$model = self::json_decode_request($_POST['model']);
		$project = self::repository()->read($model->projectId);
		$project->name = $model->projectName;
		$newProjectId = self::repository()->insert($project);
		
		$formElementsRepo = new Booki_FormElementRepository();
		$formElements = $formElementsRepo->readAll($model->projectId);

		if($formElements && $formElements->count() > 0){
			foreach($formElements as $formElement){
				$formElement->projectId = $newProjectId;
				$formElementsRepo->insert($formElement);
			}
		}
		
		$calendarRepo = new Booki_CalendarRepository();
		$calendar = $calendarRepo->readByProject($model->projectId);

		if($calendar){
			$calendar->projectId = $newProjectId;
			$newCalendarId = $calendarRepo->insert($calendar);

			$calendarDayRepo = new Booki_CalendarDayRepository();
			$calendarDays = $calendarDayRepo->readAll($calendar->id);

			if($calendarDays && $calendarDays->count() > 0){
				foreach($calendarDays as $calendarDay){
					$calendarDay->calendarId = $newCalendarId;
					$newCalendarDaysId = $calendarDayRepo->insert($calendarDay);
				}
			}
		}
		
		$optionalsRepo = new Booki_OptionalRepository();
		$optionals = $optionalsRepo->readAll($model->projectId);
		if($optionals && $optionals->count() > 0){
			foreach($optionals as $optional){
				$optional->projectId = $newProjectId;
				$optionalsRepo->insert($optional);
			}
		}
		
		$cascadingListRepo = new Booki_CascadingListRepository();
		$cascadingLists = $cascadingListRepo->readAll($model->projectId);
		$cascadingLists = $cascadingListRepo->readItemsByLists($cascadingLists);
		if($cascadingLists && $cascadingLists->count() > 0){
			$i = $cascadingLists->count();

			while($i) {
				$cascadingList = $cascadingLists->item(--$i);
				$cascadingList->projectId = $newProjectId;
				$newListId = $cascadingListRepo->insertList($cascadingList);
				$j = $cascadingList->cascadingItems->count();
				foreach($cascadingList->cascadingItems as $cascadingItem){
					$cascadingItem->listId = $newListId;
					$cascadingItem->parentId = -1;
					$cascadingListRepo->insertItem($cascadingItem);
				}
			}
		}
		
		return self::json_encode_response($newProjectId, 'id');
	}
	public static function readAll($includeTags = false){
		$hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$userId = null;
		if(!$hasFullControl){
			$user = wp_get_current_user();
			$userId = $user->ID;
		}
		$projects = self::repository()->readAll($userId);
		$result = null;
		if($projects){
			$result = $projects;
		}
		if($result && $includeTags){
			$tags = self::repository()->readAllTags($userId);
			$result = array('projects'=>$result->toArray(), 'tags'=>$tags, 'hasFullControl'=>$hasFullControl);
		}
		return self::json_encode_response($result);
	}
	
	public static function read(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->read($model->id);
		return self::json_encode_response($result);
	}
	
	public static function readAllTags(){
		$hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$userId = null;
		if(!$hasFullControl){
			$user = wp_get_current_user();
			$userId = $user->ID;
		}
		$result = self::repository()->readAllTags($userId);
		return self::json_encode_response($result);
	}
	
	public static function insert(){
		$hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$result = null;
		if($hasFullControl){
			$model = self::json_decode_request($_POST['model']);
			if (! is_null ($model) && strlen($model->name) > 0){
				$result = self::repository()->insert(new Booki_Project((array)$model));
			}
		}
		return self::json_encode_response($result, 'id');
	}
	
	public static function update(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->update(new Booki_Project((array)$model));
		return self::json_encode_response($result, 'affectedRecords');
	}
	
	public static function delete(){
		$hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$result = null;
		if($hasFullControl){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->delete($model->id);
		}
		return self::json_encode_response($result, 'affectedRecords');
	}
}
?>