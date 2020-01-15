<?php
class Booki_CalendarJSONProvider extends Booki_ProviderBase{
	private static $instance;
	protected function __construct()
	{
	}
	public static function repository()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Booki_CalendarRepository();
		}
		return self::$instance;
	}
	
	public static function readByProject(){
		$model = self::json_decode_request($_POST['model']);
		$result = null;
		$calendarItem = self::repository()->readByProject($model->projectId);
		if($calendarItem){
			$result = $calendarItem->toArray();
		 }
		return self::json_encode_response($result);
	}
	
	public static function read(){
		$model = self::json_decode_request($_POST['model']);
		$result = null;
		$calendarItem = self::repository()->read($model->id);
		if($calendarItem){
			$result = $calendarItem->toArray();
		 }
		return self::json_encode_response($result);
	}
	
	public static function insert(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->insert(new Booki_Calendar((array)$model));
		return self::json_encode_response($result, 'id');
	}
	
	public static function update(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->update(new Booki_Calendar((array)$model));
		return self::json_encode_response($result, 'affectedRecords');
	}
	
	public static function delete(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->delete($model->id);
		return self::json_encode_response( $result, 'affectedRecords');
	}
}
?>