<?php
class Booki_ManageGCal{
	public $gcalList;
	public $id;
	public $pageIndex;
	public $totalPages;
	public $hasFullControl;
	public $onDelete;
	public $onAddUserToGCal;
	public $newUserCreated = null;
	public $createError = null;
	public $gcalUpdated = null;
	public $selectedProjectId = -1;
	public $item = null;
	public $hasProfile = null;
	public $redirectURI = null;
	public $javascriptOrigins = null;
	public $calendarSyncResult = array();
	public $calendarDeletedResult = array();
	function __construct( ){
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->id = isset($_GET['id']) ? (int)$_GET['id'] : null;
		$this->onDelete = (isset($_GET['command']) && $_GET['command'] === 'delete');
		$this->onAddUserToGCal = (isset($_GET['command']) && $_GET['command'] === 'create');
		new Booki_ManageGCalController(
			array($this, 'created')
			, array($this, 'updated')
			, array($this, 'deleted')
			, array($this, 'calendarSync')
			, array($this, 'calendarDeleted')
		);
		$user = Booki_Helper::getUserInfo();
		$this->item = new Booki_GCal(array('email'=>$user['email']));
		if(!$this->hasFullControl && $this->id === null){
			$repo = new Booki_GCalRepository();
			$profile = $repo->readByUser($user['userId']);
			if($profile){
				$this->hasProfile = true;
				$this->item = $profile;
			}
		}
		$this->gcalList = new Booki_ManageGCalList();
		$this->gcalList->bind();
		
		$this->pageIndex = $this->gcalList->currentPage;
		$this->totalPages = $this->gcalList->totalPages;
		if($this->id !== null){
			$repo = new Booki_GCalRepository();
			$this->item = $repo->read($this->id);
		}
		add_filter( 'booki_is_backend', array($this, 'isBackEnd'));
		
		$this->redirectURI = get_site_url();
		$parse = parse_url($this->redirectURI);
		$port = isset($parse['port']) ? ':' . $parse['port'] : '';
		$this->javascriptOrigins = $parse['scheme'] . '://' . $parse['host'] . $port;
	}
	
	public function isBackEnd(){
		return true;
	}
	
	function deleted(){}
	function created($newUserCreated, $createError = null){
		$this->newUserCreated = $newUserCreated;
		$this->createError = $createError;
	}
	function updated($result, $createError = null){
		$this->gcalUpdated = $result;
		$this->createError = $createError;
	}
	function calendarSync($result){
		$this->calendarSyncResult = $result;
	}
	function calendarDeleted($result){
		$this->calendarDeletedResult = $result;
	}
}
$_Booki_ManageGCal = new Booki_ManageGCal();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Manage Google calendar profiles', 'booki')?></h1>
		<p><?php echo __('Associate a user with a Google calendar profile. Bookings made will be synced to this profile on projects the user is designated as a service provider.', 'booki') ?> </p>
	</div>
	<?php if($_Booki_ManageGCal->onDelete):?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Delete role', 'booki') ?></h4>
				<p><?php echo __('You are about to delete a Google calendar profile. Are you sure you want to do this ?', 'booki') ?></p>
			</div>
			<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/managegcal.php"?>" method="post">
			<input type="hidden" name="controller" value="booki_managegcal" />
			<input type="hidden" name="userId" value="<?php echo $_Booki_ManageGCal->item->userId ?>" />
			<div class="form-group">
				<div class="col-lg-8 col-lg-offset-4 booki-top-margin">
					<button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo __('Cancel', 'booki')?></button>
					<button class="btn btn-danger" value="<?php echo $_Booki_ManageGCal->item->id ?>" name="delete">
						<span class="badge">#<?php echo $_GET['id']?></span>
						<?php echo __('Delete', 'booki')?>
					</button>
				</div>
			</div>
			</form>
		</div>
	</div>
	<?php endif; ?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Google calendar profile', 'booki')?></h4>
			</div>
			<div class="booki-vertical-gap"></div>
			<?php if($_Booki_ManageGCal->newUserCreated !== null):?>
			<div class="form-group">
				<div class="bg-warning booki-bg-box">
					 <p>
						<?php echo $_Booki_ManageGCal->newUserCreated ? 
							__('A new user was created and the login credentials were emailed to the user. Ensure that this user is a service provider on projects you want synced to their Google calendar profile.', 'booki') :
							__('User was found in system.', 'booki')
							. __('If this user is a service provider on projects in Booki, then those projects will be synced with their Google calendar associated to this profile.', 'booki');
						?>
					</p>
				</div>
			</div>
			<?php endif; ?>
			<?php if($_Booki_ManageGCal->calendarSyncResult):?>
			<div class="form-group">
				<div class="bg-success booki-bg-box">
					 <p>
						<?php echo  __('Result of the sync process:', 'booki') . join(',', $_Booki_ManageGCal->calendarSyncResult) ?>
					</p>
				</div>
			</div>
			<?php endif; ?>
			<?php if($_Booki_ManageGCal->calendarDeletedResult):?>
			<div class="form-group">
				<div class="bg-success booki-bg-box">
					 <p>
						<?php echo  __('Result of calendars and events deleted:', 'booki') . join(',', $_Booki_ManageGCal->calendarDeletedResult) ?>
					</p>
				</div>
			</div>
			<?php endif; ?>
			<?php if($_Booki_ManageGCal->gcalUpdated !== null):?>
			<div class="form-group">
				<div class="<?php echo $_Booki_ManageGCal->gcalUpdated ? 'bg-success' : 'bg-warning' ?> booki-bg-box">
					 <p>
						<?php echo $_Booki_ManageGCal->gcalUpdated ? 
							__('The gcal profile was updated successfully.', 'booki') :
							__('Update was not successful. Try again.', 'booki');
						?>
					</p>
				</div>
			</div>
			<?php endif; ?>
			<?php if($_Booki_ManageGCal->createError):?>
			<div class="form-group">
				<div class="bg-warning booki-bg-box">
					 <p>
						<?php echo $_Booki_ManageGCal->createError ?>
					</p>
				</div>
			</div>
			<?php endif; ?>
			<div class="booki-content-box">
				<ul class="nav nav-tabs">
				  <li><a href="#fields" data-toggle="tab">Settings</a></li>
				  <li><a href="#instructions" data-toggle="tab">Instructions</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="fields">
						<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/managegcal.php" ?>" method="post" enctype="multipart/form-data" data-parsley-validate>
							<input type="hidden" name="controller" value="booki_managegcal" />
							<div class="panel-body">
								<div class="form-group">
									 <label class="col-lg-4 control-label" for="email">
										<?php echo __('User email', 'booki') ?>
									</label>
									<div class="col-lg-8">
										<input type="text" 
												id="email"
												class="form-control"
												name="email" 
												value="<?php echo $_Booki_ManageGCal->item->email?>"
												placeholder="userwordpressemail@domain.com"
												data-parsley-required="true" 
												data-parsley-type="email" 
												data-parsley-trigger="change"
												<?php echo $_Booki_ManageGCal->hasFullControl ? '' : 'disabled'?>/>
									</div>
								</div>
								<div class="form-group">
									 <label class="col-lg-4 control-label" for="applicationName">
										<?php echo __('Application name', 'booki') ?>
									</label>
									<div class="col-lg-8">
										<input type="text" 
												id="applicationName"
												class="form-control"
												name="applicationName" 
												value="<?php echo $_Booki_ManageGCal->item->applicationName?>"
												data-parsley-required="true" 
												data-parsley-trigger="change"/>
									</div>
								</div>
								<div class="form-group">
									 <label class="col-lg-4 control-label" for="clientId">
										<?php echo __('Client Id', 'booki') ?>
									</label>
									<div class="col-lg-8">
										<input type="text" 
												id="clientId"
												class="form-control"
												name="clientId" 
												value="<?php echo $_Booki_ManageGCal->item->clientId?>"
												data-parsley-required="true" 
												data-parsley-trigger="change"/>
									</div>
								</div>
								<div class="form-group">
									 <label class="col-lg-4 control-label" for="clientSecret">
										<?php echo __('Client Secret', 'booki') ?>
									</label>
									<div class="col-lg-8">
										<input type="text" 
												id="clientSecret"
												class="form-control"
												name="clientSecret" 
												value="<?php echo $_Booki_ManageGCal->item->clientSecret?>"
												data-parsley-required="true" 
												data-parsley-trigger="change"/>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-8 col-lg-offset-4">
									<?php if (isset($_GET['id'])):?>
									<button class="btn btn-primary" name="update"  value="<?php echo $_GET['id']?>">
										<span class="badge">#<?php echo $_GET['id']?></span>
										<?php echo __('Update profile', 'booki') ?>
									</button>
									<?php else:?>
									<button class="btn btn-primary" name="create" <?php $_Booki_ManageGCal->hasProfile ? 'disabled': '' ?>>
										<?php echo __('Create profile', 'booki') ?>
									</button>
									<?php endif;?>
								</div>
								<div class="clearfix"></div>
							</div>
						</form>
					</div>
					<div class="tab-pane active" id="instructions">
						<div class="booki-vertical-gap"></div>
						<p><strong>How to set and retrieve your clientId, clientSecret and set redirectURI and Javascript Origins.</strong></p>
						<ol class="instructions-list">
							<li>
								Go to the <a href="https://console.developers.google.com//start/api?id=calendar&credential=client_key">Google Developers Console</a>.
							</li>
							  <li>
								Select a project, or create a new one. Keep a note of the project name as you will need it.
							</li>
							<li>
								In the sidebar on the left, expand <b>APIs and auth</b>. Next, click <b>APIs</b>. In the list of APIs, make sure the status is <b>ON</b> for the Google Calendar API.
							</li>
							<li>
								In the sidebar on the left, select <b>Credentials</b>.
							</li>
							<li>
								Under <i>OAuth</i> select <strong>Create new Client ID</strong>. For the <i>Application type</i> select <strong>Web Application</strong>.
							</li>
							<li>Add the following value for Javascript Origins: <strong><?php echo $_Booki_ManageGCal->javascriptOrigins ?></strong></li>
							<li>Add the following value for RedirectURI: <strong><?php echo $_Booki_ManageGCal->redirectURI?></strong></li>
							<li>It's done. Copy the generated ClientId, ClientSecret and Project name and insert these values here on Booki to create your Google calendar profile.</li>
						</ol>
					</div>
				</div>
		</div>
	</div>
	<div class="booki-content-box">
		<div class="booki-section-heading">
			<h4><?php echo __('Google calendar profiles', 'booki') ?></h4>
			<p><?php echo __('Listing of all Google calendar profiles registered in system', 'booki') ?></p>
		</div>
		<div class="table-responsive">
			<?php $_Booki_ManageGCal->gcalList->display();?>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var $tabs = $('.nav.nav-tabs');
		$tabs.find('a:first').tab('show');
		$tabs.find('a').click(function (e) {
		  e.preventDefault();
		  $(this).tab('show');
		});
	});
</script>