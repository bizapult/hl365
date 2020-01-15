<?php
class Booki_ManageGCalController extends Booki_BaseController{
	private $id;
	private $hasFullControl;
	private $canEdit;
	private $globalSettings;
	private $userId;
	private $code;
	public function __construct( $createCallback, $updateCallback, $deleteCallback, $calendarSyncCallback, $calendarDeletedCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managegcal')){
			return;
		}
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->id = isset($_GET['id']) ? $_GET['id'] : null;
		$this->userId = isset($_GET['userid']) ? $_GET['userid'] : null;
		$this->code = isset($_GET['code']) ? $_GET['code'] : null;
		if($this->code !== null && $this->userId !== null){
			new Booki_GCalService($this->userId);
			//redirect back to page to clear querystring.
		}
		if (array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if (array_key_exists('create', $_POST)){
			$this->create($createCallback);
		}else if (array_key_exists('update', $_POST)){
			$this->update($updateCallback);
		}else if (array_key_exists('calendarSync', $_POST)){
			$this->calendarSync($calendarSyncCallback);
		}else if (array_key_exists('calendarDelete', $_POST)){
			$this->calendarDelete($calendarDeletedCallback);
		}
	}
	public function calendarSync($callback){
		$userId = (int)$this->getPostValue('calendarSync');
		$service = new Booki_GCalService($userId);
		$result = $service->sync();
		$this->executeCallback($callback, array($result));
	}
	public function calendarDelete($callback){
		$userId = (int)$this->getPostValue('calendarDelete');
		$result = $this->deleteAllCalendars($userId);
		$this->executeCallback($callback, array($result));
	}
	public function deleteAllCalendars($userId){
		$service = new Booki_GCalService($userId);
		return $service->deleteAllCalendars();
	}
	public function create($callback){
		$userId = null;
		$createUserResult = array('isNew'=>false);
		$applicationName = (string)$this->getPostValue('applicationName');
		$clientId = (string)$this->getPostValue('clientId');
		$clientSecret = (string)$this->getPostValue('clientSecret');
		$email = (string)$this->getPostValue('email');
		if(!$email){
			$user = Booki_Helper::getUserInfo();
			if($user){
				$email = $user['email'];
				$userId = $user['userId'];
			}else{
				$this->executeCallback($callback, array(null, __('Unable to retrieve your user info. Contact admin to set up profile.', 'booki')));
				return;
			}
		}
		$gcalRepository = new Booki_GCalRepository();
		if($userId === null){
			$createUserResult = Booki_Helper::createUserIfNotExists($email);
			$userId = $createUserResult['userId'];
		}
		if($this->userHasProfile($userId)){
			//user already has a profile, bail out
			$this->executeCallback($callback, array(null,__('Only one profile allowed per user.', 'booki')));
			return;
		}
		$gcalRepository->insert(new Booki_GCal(array(
			'userId'=>$userId
			, 'applicationName'=>trim($applicationName)
			, 'clientId'=>trim($clientId)
			, 'clientSecret'=>trim($clientSecret)
		)));
		$this->executeCallback($callback, array($createUserResult['isNew']));
	}
	public function update($callback){
		$id = (int)$_POST['update'];
		$applicationName = (string)$this->getPostValue('applicationName');
		$clientId = (string)$this->getPostValue('clientId');
		$clientSecret = (string)$this->getPostValue('clientSecret');
		$gcalRepository = new Booki_GCalRepository();
		$result = $gcalRepository->update(new Booki_GCal(array(
			'id'=>$id
			, 'applicationName'=>$applicationName
			, 'clientId'=>$clientId
			, 'clientSecret'=>$clientSecret
		)));
		$this->executeCallback($callback, array($result));
	}
	public function delete($deleteCallback){
		$id = $_POST['delete'];
		$userId = (int)$this->getPostValue('userId');
		$gcalRepository = new Booki_GCalRepository();
		$result = $gcalRepository->delete($id);
		$this->deleteAllCalendars($userId);
		$this->executeCallback($deleteCallback, array($result));
	}
	protected function userHasProfile($userId){
		$repo = new Booki_GCalRepository();
		$profile = $repo->readByUser($userId);
		return $profile !== false;
	}
}
?>