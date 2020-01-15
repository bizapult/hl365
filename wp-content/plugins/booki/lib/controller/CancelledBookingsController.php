<?php
class Booki_CancelledBookingsController extends Booki_BaseController{
	private $repo;
	public function __construct($deleteCallback, $deleteAllCallback, $undoCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_cancelledbookings')){
			return;
		}
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$this->repo = new Booki_TrashRepository();

		if(array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if(array_key_exists('deleteall', $_POST)){
			$this->deleteAll($deleteAllCallback);
		}else if(array_key_exists('undo', $_POST)){
			$this->undo($undoCallback);
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
	
	public function undo($callback){
		$id = isset($_POST['undo']) ? intval($_POST['undo']) : null;
		$result = $this->repo->read($id);
		$order = $result->data;
		Booki_BookingProvider::insert($order);
		Booki_GCalHelper::updateByOrder($order->id);
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
}
?>