<?php
class Booki_QuantityElementJSONProvider extends Booki_ProviderBase{
	private static $instance;
	protected function __construct()
	{
	}
	public static function repository()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Booki_QuantityElementRepository();
		}
		return self::$instance;
	}
	
	public static function readAllByCalendarId(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->readAllByCalendarId($model->calendarId);
		return self::json_encode_response($result);
	}
	
	public static function readAllByCalendarDayIdList(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->readAllByCalendarDayId($model->calendarDayIdList);
		return self::json_encode_response($result);
	}
	
	public static function readQuantityElement(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->readQuantityElement($model->id);
		return self::json_encode_response($result);
	}
	
	public static function readQuantityElementItem(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->readQuantityElementItem($model->id);
		return self::json_encode_response($result);
	}
	
	public static function insertQuantityElement(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->insertQuantityElement(new Booki_QuantityElement((array)$model));
		return self::json_encode_response($result, 'id');
	}
	
	public static function insertQuantityElementItem(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->insertQuantityElementItem(new Booki_QuantityElementItem((array)$model));
		return self::json_encode_response($result, 'id');
	}
	
	public static function updateQuantityElement(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->updateQuantityElement(new Booki_QuantityElement((array)$model));
		return self::json_encode_response($result, 'affectedRecords');
	}

	public static function updateQuantityElementItem(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->updateQuantityElementItem(new Booki_QuantityElementItem((array)$model));
		return self::json_encode_response($result, 'affectedRecords');
	}
	
	public static function deleteQuantityElement(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->deleteQuantityElement($model->id);
		return self::json_encode_response($result, 'affectedRecords');
	}
	
	public static function deleteQuantityElementItem(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->deleteQuantityElementItem($model->id);
		return self::json_encode_response($result, 'affectedRecords');
	}
	public static function deleteQuantityElementItems(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->deleteQuantityElementItems($model->id);
		return self::json_encode_response($result, 'affectedRecords');
	}
}
?>