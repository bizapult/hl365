<?php
class Booki_Reminders{
	public $reminderList;
	public $pageIndex;
	public $totalPages;
	public $hasFullControl;
	public $schedulesCount;
	public $totalRemindersCount;
	function __construct( ){
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		new Booki_RemindersController(
			array($this, 'delete')
			, array($this, 'deleteAll')
			, array($this, 'clearSchedules')
			, array($this, 'resend')
		);
		$this->reminderList = new Booki_ReminderList();
		$this->reminderList->bind();
		$this->totalRemindersCount = $this->reminderList->totalItemsCount;
		$this->pageIndex = $this->reminderList->currentPage;
		$this->totalPages = $this->reminderList->totalPages;
		$this->schedulesCount = Booki_EmailReminderJob::getSchedulesCount();
		add_filter( 'booki_is_backend', array($this, 'isBackEnd'));
	}
	
	public function isBackEnd(){
		return true;
	}
	public function delete($result){
		
	}
	public function deleteAll($result){
		
	}
	public function clearSchedules($result){
		
	}
	public function resend($result){
		
	}
}
$_Booki_Reminders = new Booki_Reminders();
?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Email reminders', 'booki')?></h1>
		<p><?php echo __('Email reminders sent out by system. Reminders are enabled individually on each project.', 'booki') ?> </p>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<?php if($_Booki_Reminders->hasFullControl):?>
			<div class="bg-info booki-bg-box">
				<?php echo __('Email reminders in queue:', 'booki') . ' ' . $_Booki_Reminders->schedulesCount ?>
			</div>
			<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/reminders.php"?>" method="post">
				<input type="hidden" name="controller" value="booki_reminders" />
				<div class="form-group">
					<div class="col-lg-12">
						<button class="btn btn-danger" name="clearschedules" 
							title="<?php echo __('Removes all scheduled reminders.', 'booki')?>" 
							<?php echo $_Booki_Reminders->schedulesCount ? '' : 'disabled' ?>>
							<i class="glyphicon glyphicon-remove"></i> 
							<?php echo __('Clear queue', 'booki')?>
						</button>
						<button class="btn btn-danger" name="deleteall" 
							title="<?php echo __('Deletes all the history of reminders sent out by system.', 'booki')?>" 
							<?php echo $_Booki_Reminders->totalRemindersCount ? '' : 'disabled' ?>>
							<i class="glyphicon glyphicon-remove"></i> 
							<?php echo __('Clear history', 'booki')?>
						</button>
					</div>
				</div>
			</form>
			<?php endif; ?>
			<div class="table-responsive">
				<?php $_Booki_Reminders->reminderList->display();?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {});
</script>