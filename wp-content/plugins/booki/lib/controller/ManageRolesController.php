<?php
class Booki_ManageRolesController extends Booki_BaseController{
	private $roleId;
	private $hasFullControl;
	private $canEdit;
	private $globalSettings;
	
	public function __construct( $createRoleCallback, $updateRoleCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_manageroles')){
			return;
		}
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		if(!$this->hasFullControl){
			return;
		}
		$this->roleId = isset($_GET['roleid']) ? $_GET['roleid'] : null;
		if (array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if (array_key_exists('addusertorole', $_POST)){
			$this->createRole($createRoleCallback);
		}else if (array_key_exists('updaterole', $_POST)){
			$this->updateRole($updateRoleCallback);
		}
	}
	
	public function createRole($callback){
		$roleId = (int)$_POST['addusertorole'];
		$projectId = (int)$_POST['projectid'];
		$role = 0;//(int)$_POST['role'];
		$email = (string)$_POST['adduseremail'];
		$rolesRepository = new Booki_RolesRepository();
		$createUserResult = Booki_Helper::createUserIfNotExists($email);
		$userId = $createUserResult['userId'];
		$rolesRepository->insert(new Booki_Role(array('userId'=>$userId, 'role'=>$role, 'projectId'=>$projectId)));
		$this->executeCallback($callback, array($createUserResult['isNew']));
	}
	public function updateRole($callback){
		$roleId = (int)$_POST['updaterole'];
		$projectId = (int)$_POST['projectid'];
		$role = 0;//(int)$_POST['role'];
		$rolesRepository = new Booki_RolesRepository();
		$result = $rolesRepository->update(new Booki_Role(array('id'=>$roleId, 'role'=>$role, 'projectId'=>$projectId)));
		$this->executeCallback($callback, array($result));
	}
	public function delete($callback){
		$roleId = $_POST['delete'];
		$rolesRepository = new Booki_RolesRepository();
		$result = $rolesRepository->delete($roleId);
		$this->executeCallback($callback, array($result));
	}
}
?>