<?php
class Booki_PermissionHelper{
	private static $userRoles;
	public static function userRoles()
	{
		if (!is_admin()) {
			//used only in admin dashboard so bail out
			return false;
		}
		$user = wp_get_current_user();
		if (empty($user)){
			return false;
		}
		if(!isset(self::$userRoles))
		{
			$repo = new Booki_RolesRepository();
			self::$userRoles = $repo->readByUser($user->ID);
		}
		return self::$userRoles;
	}
	public static function hasProjectPermission($projectId, $permission){
		if(self::isAdmin()){
			return true;
		}
		$roles = self::userRoles();
		if(!$roles){
			return false;
		}
		foreach($roles as $role){
			if($projectId === $role->projectId && in_array($role->role, $permission)){
				return true;
			}
		}
		return false;
	}
	public static function hasProjectsPermission($projectIdList, $permission){
		if(self::isAdmin()){
			return true;
		}
		$roles = self::userRoles();
		if(!$roles){
			return false;
		}
		$result = false;
		//if permission denied on any one project, user does not have permission at all.
		foreach($projectIdList as $projectId){
			foreach($roles as $role){
				if($role->projectId === $role->projectId){
					 if(in_array($role->role, $permission)){
						$result = true;
					 }else{
						$result = false;
						break 2;
					 }
				}
			}
		}
		return $result;
	}
	public static function hasPermission($permission){
		if(self::isAdmin()){
			return true;
		}
		$roles = self::userRoles();
		if(!$roles){
			return false;
		}
		foreach($roles as $role){
			if($permission === $role->role){
				return true;
			}
		}
		return false;
	}
	public static function currentUserPermissions(){
		$globalSettings = BOOKIAPP()->globalSettings;
		$result = array();
		
		if(self::userHasRole('administrator')){
			array_push($result, 'administrator');
		}
		if($globalSettings->enableEditors && self::userHasRole('editor')){
			array_push($result, 'editor');
		}
		return $result;
	}
	public static function hasAdministratorPermission(){
		if(BOOKI_RESTRICTED_MODE){
			return true;
		}
		return is_super_admin();
	}
	public static function hasEditorPermission($projectId){
		$permission = array(Booki_RoleType::APPROVE_DELETE_VIEW, Booki_RoleType::CREATE_EDIT_APPROVE_DELETE_VIEW);
		return self::hasProjectPermission($projectId, $permission);
	}
	public static function isAdmin(){
		return is_super_admin();
	}
	public static function isProjectsEditor($projectIdList){
		$permission = array(Booki_RoleType::APPROVE_DELETE_VIEW, Booki_RoleType::CREATE_EDIT_APPROVE_DELETE_VIEW);
		return self::hasProjectsPermission($projectIdList, $permission);
	}
	public static function hasEditorPrivileges(){
		if(self::isAdmin()){
			return true;
		}
		$roles = self::userRoles();
		if(!$roles){
			return false;
		}
		$permission = array(Booki_RoleType::APPROVE_DELETE_VIEW, Booki_RoleType::CREATE_EDIT_APPROVE_DELETE_VIEW);
		foreach($roles as $role){
			if(in_array($role->role, $permission)){
				return true;
			}
		}
		return false;
	}
	public static function userHasRole( $role, $user_id = null ) {
		$user = null;
		if ( is_numeric($user_id)){
			$user = get_userdata($user_id);
		}else{
			$user = wp_get_current_user();
		}
		if (empty($user)){
			return false;
		}
		return in_array( $role, (array) $user->roles );
	}
	public static function wpUserRole( $user_id = null ) {
		$user = null;
		if ( is_numeric($user_id)){
			$user = get_userdata($user_id);
		}else{
			$user = wp_get_current_user();
		}
		if (empty($user)){
			return false;
		}
		return (array)$user->roles;
	}
	public static function hasOrderPermission($order){
		foreach($order->bookedDays as $bookedDay){
			$result = self::hasEditorPermission($bookedDay->projectId);
			if(!$result){
				return false;
			}
		}
		return true;
	}
}
?>