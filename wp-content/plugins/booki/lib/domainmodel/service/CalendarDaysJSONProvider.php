<?php
class Booki_CalendarDaysJSONProvider extends Booki_ProviderBase{
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
		
		public static function readAll(){
			$model = self::json_decode_request($_POST['model']);
			$calendarDays = self::repository()->readAll($model->calendarId);
			$result = $calendarDays->toArray();
			return $result;
			//return self::json_encode_response($result);
		}
		public static function readAllSeasons(){
			$model = self::json_decode_request($_POST['model']);
			$seasons = self::repository()->readAllSeasons($model->calendarId);
			$calendarDays = self::readAll();
			$result = array('seasons'=>$seasons, 'calendarDays'=>$calendarDays);
			return self::json_encode_response($result);
		}
		
		public static function readAllBySeason(){
			$model = self::json_decode_request($_POST['model']);
			$calendarDays = self::repository()->readAllBySeason($model->calendarId, $model->seasonName);
			$result = $calendarDays->toArray();
			return self::json_encode_response($result);
		}
		
		public static function insert(){
			$models = self::json_decode_request($_POST['model']);
			foreach($models as $model){
				$result = self::repository()->insert( new Booki_CalendarDay((array)$model));
			}
			$calendarDays = self::repository()->readAllBySeason($models[0]->calendarId, $models[0]->seasonName);
			$result = $calendarDays->toArray();
			return self::json_encode_response($result);
		}
		
		public static function update(){
			$models = self::json_decode_request($_POST['model']);
			$calendarId = $models[0]->calendarId;
			foreach($models as $model){
				$result = self::repository()->update( new Booki_CalendarDay((array)$model));
			}
			$calendarDays = self::repository()->readAllBySeason($models[0]->calendarId, $models[0]->seasonName);
			$result = $calendarDays->toArray();
			return self::json_encode_response($result);
		}

		public static function delete(){
			$model = self::json_decode_request($_POST['model']);
			$result = array();
			$quantityElementRepo = new Booki_QuantityElementRepository();
			array_push($result, $quantityElementRepo->deleteBySeason($model->seasonName));
			
			array_push($result, self::repository()->deleteBySeason($model->seasonName));
			return self::json_encode_response($result, 'affectedRecords');
		}
		
		public static function deleteNamelessDays(){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->deleteNamelessDays($model->calendarId);
			return self::json_encode_response($result, 'affectedRecords');
		}
}
?>