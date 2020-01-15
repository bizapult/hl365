<?php
class Booki_ManageRoles{
	public $rolesList;
	public $roleId;
	public $role;
	public $email;
	public $pageIndex;
	public $totalPages;
	public $projects;
	public $hasFullControl;
	public $onDelete;
	public $onAddUserToRole;
	public $newUserCreated = null;
	public $roleUpdated = null;
	public $selectedProjectId = -1;
	function __construct( ){
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->roleId = isset($_GET['roleid']) ? (int)$_GET['roleid'] : null;
		$this->role = isset($_GET['role']) ? (int)$_GET['role'] : 0;
		$this->email = isset($_GET['email']) ? (string)$_GET['email'] : '';
		$this->projectId = isset($_GET['projectid']) && trim($_GET['projectid']) ? (int)$_GET['projectid'] : -1;
		$this->selectedProjectId = isset($_GET['projectfilter']) && trim($_GET['projectfilter']) ? (int)$_GET['projectfilter'] : -1;
		$this->onDelete = (isset($_GET['command']) && $_GET['command'] === 'delete');
		$this->onAddUserToRole = (isset($_GET['command']) && $_GET['command'] === 'addusertorole');
		
		new Booki_ManageRolesController(
			array($this, 'createRole')
			, array($this, 'updateRole')
			, array($this, 'deleted')
		);
		
		$this->rolesList = new Booki_ManageRolesList();
		$this->rolesList->bind();
		
		$this->pageIndex = $this->rolesList->currentPage;
		$this->totalPages = $this->rolesList->totalPages;
		
		add_filter( 'booki_is_backend', array($this, 'isBackEnd'));
		
		$projectRepo = new Booki_ProjectRepository();
		$this->projects = $projectRepo->readAll();
	}
	
	public function isBackEnd(){
		return true;
	}
	
	function deleted(){}
	function createRole($isNewUser){
		$this->newUserCreated = $isNewUser;
	}
	function updateRole($result){
		$this->roleUpdated = $result;
	}
}
$_Booki_ManageRoles = new Booki_ManageRoles();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Manage service providers by project', 'booki')?></h1>
		<p><?php echo __('Users provided access to a project will make them service providers in which they will be allowed to manage the project i.e. create/edit/delete and the over all management of bookings made on projects they belong to. Remember, with great power comes great responsibility. Careful grasshopper!', 'booki') ?> </p>
	</div>
	<?php if($_Booki_ManageRoles->onDelete):?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Delete role', 'booki') ?></h4>
				<p><?php echo __('You are about to delete a users privileges. Are you sure you want to take away their powers ?', 'booki') ?></p>
			</div>
			<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/manageroles.php"?>" method="post">
			<input type="hidden" name="controller" value="booki_manageroles" />
			<div class="form-group">
				<div class="col-lg-8 col-lg-offset-4 booki-top-margin">
					<button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo __('Cancel', 'booki')?></button>
					<button class="btn btn-danger" value="<?php echo $_GET['roleid']?>" name="delete">
						<span class="badge">#<?php echo $_GET['roleid']?></span>
						<?php echo __('Delete', 'booki')?>
					</button>
				</div>
			</div>
			</form>
		</div>
	</div>
	<?php endif; ?>
	<?php if($_Booki_ManageRoles->projects->count() >0):?>
		<div class="booki col-lg-12">
			<div class="booki-content-box">
				<div class="booki-section-heading">
					<h4><?php echo __('Designate user as a service provider', 'booki')?></h4>
					<p>
						<?php echo __('Provide an email address. If user is not already registered, 
							then a new user is created and login credentials are emailed to the provided email address. The user will be allowed privileges on their respective project only.', 'booki')?>
					</p>
				</div>
				<?php if($_Booki_ManageRoles->newUserCreated !== null):?>
				<div class="form-group">
					<div class="bg-warning booki-bg-box">
						 <p>
							<?php echo $_Booki_ManageRoles->newUserCreated ? 
								__('A new user was created and the login credentials were emailed to the user.', 'booki') :
								__('User was found in system.', 'booki')
								. __('This user has now been assigned privileges on a project where they can perform some duties as project owner.', 'booki');
							?>
						</p>
					</div>
				</div>
				<?php endif; ?>
				<?php if($_Booki_ManageRoles->roleUpdated !== null):?>
				<div class="form-group">
					<div class="<?php echo $_Booki_ManageRoles->roleUpdated ? 'bg-success' : 'bg-warning' ?> booki-bg-box">
						 <p>
							<?php echo $_Booki_ManageRoles->roleUpdated ? 
								__('The service provider was updated successfully.', 'booki') :
								__('Update was not successful. Try again.', 'booki');
							?>
						</p>
					</div>
				</div>
				<?php endif; ?>
				<form action="<?php echo admin_url() . "admin.php?page=booki/manageroles.php" ?>" method="post" data-parsley-validate>
					<input type="hidden" name="controller" value="booki_manageroles" />
					<div class="panel-body">
						<div class="form-group">
							 <label class="col-lg-4 control-label" for="adduseremail">
								<?php echo __('User email', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<input type="text" 
										id="adduseremail"
										class="form-control"
										name="adduseremail" 
										value="<?php echo $_Booki_ManageRoles->email?>"
										placeholder="existing@useremail.com"
										data-parsley-required="true" 
										data-parsley-type="email" 
										data-parsley-trigger="change"
										<?php echo $_Booki_ManageRoles->roleId !== null ? 'disabled' : ''?>/>
							</div>
							 <div class="clearfix"></div>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="projectid">
								<?php echo __('Project', 'booki') ?>
							</label>
							<div class="col-lg-8 col-lg-offset-4">
								<select name="projectid" class="form-control">
									<?php foreach($_Booki_ManageRoles->projects as $project): ?>
										<option value="<?php echo $project->id ?>" 
											<?php echo $_Booki_ManageRoles->projectId ===  $project->id ? 'selected' : '' ?>>
												<?php echo $project->name ?>
										</option>
									<?php endforeach;?>
								</select>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-8 col-lg-offset-4">
							<?php if (isset($_GET['roleid'])):?>
							<button class="btn btn-primary" name="updaterole"  value="<?php echo $_GET['roleid']?>">
								<span class="badge">#<?php echo $_GET['roleid']?></span>
								<?php echo __('Update role', 'booki') ?>
							</button>
							<?php else:?>
							<button class="btn btn-primary" name="addusertorole">
								<?php echo __('Create service provider', 'booki') ?>
							</button>
							<?php endif;?>
						</div>
						<div class="clearfix"></div>
					</div>
				</form>
			</div>
		</div>
	<?php else:?>
	<div class="booki col-lg-12">
		<div class="bg-warning booki-bg-box">
			 <p>
				<?php echo __('You  have not created a project yet. A project needs to be created before user privileges are set on it.', 'booki') ?>
			</p>
		</div>
	</div>
	<?php endif;?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Service providers', 'booki') ?></h4>
				<p><?php echo __('Listing of users that are registered as service providers', 'booki') ?></p>
			</div>
			<form class="form-inline" action="<?php echo admin_url() . "admin.php?page=booki/manageroles.php" ?>" method="get">
				<input type="hidden" name="page" value="booki/manageroles.php"/>
				<input type="hidden" name="controller" value="booki_manageroles" />
				<div class="form-inline booki-vertical-gap">
					<div class="form-group">
						<select name="projectid" class="form-control">
							<option value="-1"><?php echo __('All projects', 'booki') ?></option>
							<?php foreach($_Booki_ManageRoles->projects as $project): ?>
								<option value="<?php echo $project->id ?>"
									<?php echo $_Booki_ManageRoles->selectedProjectId ===  $project->id ? 'selected' : '' ?>>
									<?php echo $project->name ?>
								</option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-default">
							<i class="glyphicon glyphicon-filter" 
							data-toggle="tooltip" 
							data-placement="right" 
							data-original-title="<?php echo __('Filter by project', 'booki')?>"></i>
							<?php echo __('Filter', 'booki') ?>
						</button>
					</div>
				</div>
			</form>
			<div class="table-responsive">
				<?php $_Booki_ManageRoles->rolesList->display();?>
			</div>
		</div>
	</div>
</div>