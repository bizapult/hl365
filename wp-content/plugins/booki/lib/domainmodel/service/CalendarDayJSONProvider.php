<?php
class Booki_CalendarDayJSONProvider extends Booki_ProviderBase{
		private static $instance;
		protected function __construct()
		{
		}
		public static function repository()
		{
			if (!isset(self::$instance)) {
				self::$instance = new Booki_CalendarDayRepository();
			}
			return self::$instance;
		}

		public static function read(){
			$model = self::json_decode_request($_POST['model']);
			$calendarDayItem = self::repository()->read($model->id);
			$result = null;
			if($calendarDay){
				$result = $calendarDay->toArray();
			}
			return self::json_encode_response($result);
		}
		
		public static function insert(){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->insert( new Booki_CalendarDay((array)$model));
			return self::json_encode_response( $result, 'id');
		}
		
		public static function update(){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->update( new Booki_CalendarDay((array)$model));
			return self::json_encode_response($result, 'affectedRecords');
		}
		
		public static function cleanup(){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->cleanup($model->id);
			return self::json_encode_response($result, 'affectedRecords');
		}
		
		public static function delete(){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->delete($model->id);
			return self::json_encode_response($result, 'affectedRecords');
		}
}
?>