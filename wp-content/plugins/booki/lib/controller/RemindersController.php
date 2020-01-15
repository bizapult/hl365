<?php
class Booki_RemindersController extends Booki_BaseController{
	private $repo;
	public function __construct($deleteCallback, $deleteAllCallback, $clearSchedulesCallback, $resendCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_reminders')){
			return;
		}
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$this->repo = new Booki_RemindersRepository();

		if(array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if(array_key_exists('deleteall', $_POST)){
			$this->deleteAll($deleteAllCallback);
		}else if(array_key_exists('clearschedules', $_POST)){
			$this->clearSchedules($clearSchedulesCallback);
		}else if(array_key_exists('resend', $_POST)){
			$this->resend($resendCallback);
		}
	}
	
	public function delete($callback){
		$id = isset($_POST['delete']) ? intval($_POST['delete']) : null;
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
	
	public function deleteAll($callback){
		$result = $this->repo->deleteAll();
		$this->executeCallback($callback, array($result));
	}
	
	public function clearSchedules($callback){
		Booki_EmailReminderJob::cancelAllSchedules();
		$this->executeCallback($callback, array(null));
	}
	
	public function resend($callback){
		$orderId = isset($_POST['resend']) ? intval($_POST['resend']) : null;
		Booki_EmailReminderJob::init($orderId);
		$this->executeCallback($callback, array(null));
	}
}
?>