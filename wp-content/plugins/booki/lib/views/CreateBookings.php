<?php
class Booki_CreateBookings{
	public $projectId;
	public $errors = array();
	public $projects;
	public $bookingPeriodValid = false;
	public $hasAvailableBookings = false;
	public $projectCreatedSuccess;
	public $hasFullControl;
	public $hasEditorPermission;
	public $globalSettings;
	function __construct( ){
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->projectId = isset($_GET['projectid']) ? (int)$_GET['projectid'] : -1;
		new Booki_CreateBookingController(array($this, 'onCreated'));
		if(!$this->hasFullControl && $this->projectId !== -1){
			$this->hasEditorPermission = Booki_PermissionHelper::hasEditorPermission($this->projectId);
			if(!$this->hasEditorPermission){
				$this->projectId = -1;
			}
		}

		if($this->projectId !== -1){
			add_filter( 'booki_is_backend', array($this, 'isBackEnd'));
			add_filter( 'booki_shortcode_id', array($this, 'shortCodeId'));
			
			$calendarRepository =  new Booki_CalendarRepository();
			$calendar = $calendarRepository->readByProject($this->projectId);
			if(!$calendar){
				return;
			}
			$this->bookingPeriodValid = Booki_DateHelper::todayLessThanOrEqualTo($calendar->endDate);
			if($this->bookingPeriodValid){
				$this->hasAvailableBookings = Booki_BookingProvider::hasAvailability($this->projectId);
			}
		}
	}
	public function isBackEnd(){
		return true;
	}
	public function shortCodeId(){
		return $this->projectId;
	}
	
	public function onCreated($projectId, $errors){
		$this->projectId = $projectId;
		$this->errors = $errors;
		if(count($errors) === 0){
			$this->projectCreatedSuccess = true;
		}
	}
}
$_Booki_CreateBookings = new Booki_CreateBookings();
?>
<div class="booki">
	<div class="booki-vertical-gap-xs"></div>
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Create bookings', 'booki')?></h1>
		<p><?php echo __('Create bookings manually and email them to user.', 'booki') ?> </p>
	</div>
	<div class="booki col-lg-12">
	<?php if($_Booki_CreateBookings->projectId > -1) : ?>
		<?php if($_Booki_CreateBookings->projectCreatedSuccess):?>
			<div class="bg-success booki-bg-box">
				<?php echo __('The booking was created successfully and an email was sent.', 'booki') ?>
			</div>
		<?php endif; ?>
		<?php if($_Booki_CreateBookings->errors && count($_Booki_CreateBookings->errors) > 0):?>
			<div class="bg-danger booki-bg-box">
				<?php foreach($_Booki_CreateBookings->errors as $key=>$value):?>
					<div><strong><?php echo $key?></strong>: <?php echo $value ?></div>
				<?php endforeach;?>
			</div>
		<?php endif; ?>
		</div>
		<div class="clearfix"></div>
		<form class="booki form-horizontal booki-form-elements" id="booki_<?php echo $_Booki_CreateBookings->projectId ?>_form"
					name="booki_<?php echo $_Booki_CreateBookings->projectId ?>_form"
					action="<?php echo admin_url() . "admin.php?page=booki/createbookings.php" ?>" data-parsley-validate method="post">
			<input type="hidden" name="projectid" value="<?php echo $_Booki_CreateBookings->projectId ?>" />
			<div class="booki col-lg-12">
				<?php if($_Booki_CreateBookings->globalSettings->membershipRequired && ($_Booki_CreateBookings->projectId > -1 && $_Booki_CreateBookings->hasAvailableBookings)): ?>
				<div class="booki-content-box">
					<div class="form-group">
						<div class="col-lg-8 col-md-offset-4">
							<div class="booki-callout booki-callout-info">
								<p>
								<?php echo __('The booking will be emailed to the user email provided below. If payments are enabled then an invoice is emailed along with payment instructions.
								If user is not already registered, then a new user is also created and login credentials along with invoice are emailed. After entering the email below, click [Create booking] to proceed.', 'booki')?>
								</p>
							</div>
						</div>
						<div id="userinfo">
							<?php require dirname(__FILE__) . '/partials/userinfo.php'?>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php if($_Booki_CreateBookings->projectId > -1): ?>
				<div class="booki-content-box">
					<div class="form-group">
						<div class="col-lg-12">
						<?php if($_Booki_CreateBookings->hasAvailableBookings): ?>
								<input type="hidden" name="booki_add_new_booking" />
								<?php include_once('templates/bookingwizard.php') ?>
						<?php else: ?>
							<div class="bg-warning booki-bg-box">
								<?php echo __('Whoops! No more bookings available for selected project.', 'booki') ?>
							</div>
						<?php endif; ?>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			<?php endif; ?>
			</div>
		</form>
		<script type="text/javascript">
			(function($) {
				$(document).ready(function(){
					new Booki.UserInfo({
						"elem": "#userinfo"
						, "ajaxUrl": "<?php echo admin_url('admin-ajax.php') ?>"
						, "userNotFoundMessage": "<?php echo __('Email not found. A new user will be created and the booking emailed along with the user account credentials.', 'booki') ?>"
						, "userFoundMessage": "<?php echo __('User email found. Username is', 'booki') ?>"
					});
				});
			})(jQuery);
		</script>
	<?php endif; ?>
</div>