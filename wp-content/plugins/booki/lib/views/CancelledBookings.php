<?php

class Booki_CancelledBookings{
	public $cancelledBookingsList;
	public $fromDate;
	public $toDate;
	public $userId;
	public $pageIndex;
	public $totalPages;
	public $csvUrl;
	public $hasFullControl;
	public $selectedProjectId = -1;
	public $projects;
	function __construct( ){
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->userId = isset($_GET['userid']) && trim($_GET['userid']) ? (int)$_GET['userid'] : null;
		$this->fromDate = isset($_GET['from']) && trim($_GET['from']) ? new Booki_DateTime($_GET['from']) : null;
		$this->toDate = isset($_GET['to']) && trim($_GET['to']) ? new Booki_DateTime($_GET['to']) : null;
		
		new Booki_CancelledBookingsController(
			array($this, 'delete')
			, array($this, 'deleteAll')
			, array($this, 'undo')
		);
		$this->cancelledBookingsList = new Booki_CancelledBookingsList();
		$this->cancelledBookingsList->bind();
		
		$this->pageIndex = $this->cancelledBookingsList->currentPage;
		$this->totalPages = $this->cancelledBookingsList->totalPages;
		$csvUrlHandlers = BOOKIAPP()->handlerUrls;
		$this->csvUrl = $csvUrlHandlers->bookingsCsvHandlerUrl;
		$delimiter = Booki_Helper::getUrlDelimiter($this->csvUrl);
		
		$this->csvUrl .= $delimiter . 'perpage=' . $this->cancelledBookingsList->perPage;
		$this->csvUrl .= '&orderby=' . $this->cancelledBookingsList->orderBy;
		$this->csvUrl .= '&order=' . $this->cancelledBookingsList->order;
		$this->csvUrl .= '&from=' . $this->fromDate; 
		$this->csvUrl .= '&to=' . $this->toDate;
		$this->csvUrl .= '&userid=' . $this->userId;
		$this->csvUrl .= '&pageindex=';
		
		add_filter( 'booki_is_backend', array($this, 'isBackEnd'));
		
		$projectRepo = new Booki_ProjectRepository();
		$this->projects = new Booki_Projects();
		$projects = $projectRepo->readAll();
		foreach($projects as $project){
			if($this->hasFullControl || Booki_PermissionHelper::hasEditorPermission($project->id)){
				$this->projects->add($project);
			}
		}
		
		if(!$this->fromDate){
			$this->fromDate = new Booki_DateTime();
		}
		if(!$this->toDate){
			$this->toDate = new Booki_DateTime();
		}
	}
	
	public function isBackEnd(){
		return true;
	}
	public function delete($result){
		
	}
	public function deleteAll($result){
		
	}
	public function undo($result){
		
	}
}
$_Booki_CancelledBookings = new Booki_CancelledBookings();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Cancelled bookings', 'booki')?></h1>
		<p><?php echo __('View or undo cancelled bookings', 'booki') ?> </p>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Bookings', 'booki') ?></h4>
				<p><?php echo __('Filter bookings by cancellation date', 'booki') ?></p>
			</div>
			<div class="booki-vertical-gap">
				<div class="form-inline">
					<div class="form-group">
						<label class="sr-only" for="fromdate"><?php echo __('From', 'booki') ?></label>
						<div class="input-group">
							<input type="text" id="fromdate" name="fromdate" class="booki-datepicker form-control" readonly="true">
							<label class="input-group-addon" 
									for="fromdate">
									<i class="glyphicon glyphicon-calendar"></i>
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="sr-only" for="todate"><?php echo __('To', 'booki') ?></label>
						<div class="input-group">
							<input type="text" id="todate" name="todate" class="booki-datepicker form-control" readonly="true">
							<label class="input-group-addon" 
									for="todate">
									<i class="glyphicon glyphicon-calendar"></i>
							</label>
						</div>
					</div>
					<div class="form-group">
						<a class="btn btn-default filter-by-deletiondate" href="#">
							<i class="glyphicon glyphicon-filter"></i>
							<?php echo __('Filter', 'booki') ?>
						</a>
					</div>
				</div>
			</div>
			<hr>
			<div class="table-responsive">
				<?php $_Booki_CancelledBookings->cancelledBookingsList->display();?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var $fromDate = $('#fromdate')
			, $toDate = $('#todate')
			, dateFormatString = 'MM/DD/YYYY'
			, datepickerFormat = 'mm/dd/yy'
			, from = moment('<?php echo $_Booki_CancelledBookings->fromDate->format('Y-m-d') ?>').format(dateFormatString)
			, to = moment('<?php echo $_Booki_CancelledBookings->toDate->format('Y-m-d') ?>').format(dateFormatString)
			, url = "<?php echo admin_url() . "admin.php?page=booki/cancelledbookings.php&controller=booki_cancelledbookings" ?>";
		
		$fromDate.val(from);
		$toDate.val(to);
		
		$fromDate.datepicker({
				'defaultDate': from._d
				, 'dateFormat': datepickerFormat
				, 'changeMonth': true
				, 'changeYear': true
		});
		
		$toDate.datepicker({
				'defaultDate': to._d
				, 'dateFormat': datepickerFormat
				, 'changeMonth': true
				, 'changeYear': true
		});
		
		$('.filter-by-deletiondate').click(function(){
			var redirectUrl = url + '&from=' + encodeURIComponent($fromDate.val()) + '&to=' + encodeURIComponent($toDate.val());
			window.location.href = redirectUrl;
			return false;
		});
	});
</script>