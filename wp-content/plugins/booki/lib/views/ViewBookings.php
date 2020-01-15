<?php
class Booki_ViewBookings{
	public $bookingsList;
	public $fromDate;
	public $toDate;
	public $userId;
	public $pageIndex;
	public $totalPages;
	public $csvUrl;
	public $hasFullControl;
	public $selectedProjectId = -1;
	public $projects;
	public $calendarViewStartDate;
	public $calendarViewData = array();
	public $listViewUrl;
	public $calendarViewUrl;
	public $selectedView;
	public $hasRecords;
	function __construct( ){
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->userId = isset($_GET['userid']) && trim($_GET['userid']) ? (int)$_GET['userid'] : null;
		$this->fromDate = isset($_GET['from']) && trim($_GET['from']) ? new Booki_DateTime($_GET['from']) : new Booki_DateTime(date('Y-m-01'));
		$this->toDate = isset($_GET['to']) && trim($_GET['to']) ? new Booki_DateTime($_GET['to']) : null;
		$this->selectedView = isset($_GET['view']) ? (int)$_GET['view'] : 0;
		if(!$this->toDate){
			$this->toDate = new Booki_DateTime(date('Y-m-t'));
			$this->toDate->modify('+ 2 months');
		}
		$this->bookingsList = new Booki_BookingsList();
		if($this->selectedView === 0){
			$this->bookingsList->bind();
			$this->hasRecords = count($this->bookingsList->items) > 0;
		}
		$this->pageIndex = $this->bookingsList->currentPage;
		$this->totalPages = $this->bookingsList->totalPages;
		$csvUrlHandlers = BOOKIAPP()->handlerUrls;
		$this->csvUrl = $csvUrlHandlers->bookingsCsvHandlerUrl;
		$delimiter = Booki_Helper::getUrlDelimiter($this->csvUrl);
		
		$this->csvUrl .= $delimiter . 'perpage=' . $this->bookingsList->perPage;
		$this->csvUrl .= '&orderby=' . $this->bookingsList->orderBy;
		$this->csvUrl .= '&order=' . $this->bookingsList->order;
		$this->csvUrl .= '&from=' . $this->fromDate->format('Y-m-d'); 
		$this->csvUrl .= '&to=' . $this->toDate->format('Y-m-d');
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
		
		if($this->selectedView === 1){
			$this->fullCalendarList = new Booki_FullCalendarList();
			$this->fullCalendarList->bind();
			$this->hasRecords = count($this->fullCalendarList->items) > 0;
			new Booki_FullCalendarDataController(array($this, 'calendarView'), $this->fullCalendarList->items);
		}
		$this->listViewUrl = esc_url(add_query_arg(array('view'=>0), 'admin.php?page=booki/viewbookings.php'));
		$this->calendarViewUrl = esc_url(add_query_arg(array('view'=>1), 'admin.php?page=booki/viewbookings.php'));
	}
	public function calendarView($data, $startDate){
		$this->calendarViewData = $data;
		$this->calendarViewStartDate = $startDate;
	}
	public function isBackEnd(){
		return true;
	}
}
$_Booki_ViewBookings = new Booki_ViewBookings();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Bookings', 'booki')?></h1>
		<p><?php echo __('View all bookings made', 'booki') ?> </p>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Bookings', 'booki') ?></h4>
				<p><?php echo __('Listing of all bookings made', 'booki') ?></p>
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
						<a class="btn btn-default filter-by-bookingdate" href="#">
							<i class="glyphicon glyphicon-filter"></i>
							<?php echo __('Filter', 'booki') ?>
						</a>
					</div>
				</div>
			</div>
			<?php if($_Booki_ViewBookings->hasFullControl): ?>
			<hr>
			<div class="booki-bg-box"><?php echo __('Search by registered users email', 'booki')?></div>
			<div>
				<form id="userinfo" class="form-inline" action="<?php echo admin_url() . "admin.php?page=booki/viewbookings.php" ?>" method="post" data-parsley-validate>
					<input type="hidden" name="controller" value="booki_viewbookings" />
					<input type="hidden" name="userid" />
					<?php require dirname(__FILE__) . '/partials/userinfoinline.php'?>
				</form>
			</div>
			<?php endif; ?>
			<hr>
			<div class="booki-vertical-gap">
				<div class="form-inline">
					<div class="form-group">
						<div class="radio">
							<label>
							  <input type="radio" name="pageindex" value="-1" checked>
							  <?php echo __('Export all bookings', 'booki') ?>
							</label>
						</div>
					</div>
					<?php if($_Booki_ViewBookings->selectedView === 0): ?>
					<div class="form-group">
						<div class="radio">
							<label>
							  <input type="radio" name="pageindex" value="<?php echo $_Booki_ViewBookings->pageIndex ?>">
							   <?php echo __('Export the current page only', 'booki') ?>
							</label>
						</div>
					</div>
					<?php endif; ?>
					<div class="form-group">
						<div>
							  <select name="projectid"  class="form-control projectfilter">
							   <option value="-1"><?php echo __('All projects', 'booki') ?></option>
							   <?php foreach($_Booki_ViewBookings->projects as $project):?>
							   <option value="<?php echo $project->id?>" 
									<?php echo $_Booki_ViewBookings->selectedProjectId === $project->id ? "selected" : ""?>><?php echo $project->name ?></option>
							   <?php endforeach;?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<button type="button" class="btn btn-default export-csv">
							<i class="glyphicon glyphicon-file" 
							data-toggle="tooltip" 
							data-placement="right" 
							data-original-title="<?php echo __('Export to CSV file', 'booki')?>"></i>
							<?php echo __('CSV', 'booki') ?>
						</button>
					</div>
				</div>
			</div>
			<?php if(!$_Booki_ViewBookings->hasRecords):?>
				<div class="bg-warning booki-bg-box"><?php echo sprintf(__('There are currently no bookings found for selected period %s - %s.', 'booki'), $_Booki_ViewBookings->fromDate->format('m/d/Y/'), $_Booki_ViewBookings->toDate->format('m/d/Y/'))?></div>
				<div class="booki-vertical-gap"></div>
			<?php endif; ?>
			<ul class="nav nav-tabs">
			  <li class="<?php echo $_Booki_ViewBookings->selectedView === 0 ? 'active' : '' ?>"><a href="<?php echo $_Booki_ViewBookings->listViewUrl . '#listview'?>"><i class="glyphicon glyphicon-th-list"></i> List view</a></li>
			  <li class="<?php echo $_Booki_ViewBookings->selectedView === 1 ? 'active' : '' ?>"><a href="<?php echo $_Booki_ViewBookings->calendarViewUrl . '#calendarview'?>"><i class="glyphicon glyphicon-calendar"></i> Calendar view</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="listview">
				<div class="booki-vertical-gap"></div>
				<?php if($_Booki_ViewBookings->selectedView === 0):?>
					<div class="table-responsive">
						<?php $_Booki_ViewBookings->bookingsList->display();?>
					</div>
				<?php endif;?>
				</div>
				<div class="tab-pane active" id="calendarview">
					<?php if($_Booki_ViewBookings->fullCalendarList):?>
						<div class="booki-vertical-gap"></div>
						<?php $_Booki_ViewBookings->fullCalendarList->display();?>
					<?php endif; ?>
					<div id="calendar" class="booki-admin-fullcalendar"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var $fromDate = $('#fromdate')
			, $toDate = $('#todate')
			, $findUserButton = $('.find-user')
			, $userIdField = $('[name="userid"]')
			, $tabs = $('.nav.nav-tabs')
			, dateFormatString = 'MM/DD/YYYY'
			, datepickerFormat = 'mm/dd/yy'
			, from = moment('<?php echo $_Booki_ViewBookings->fromDate->format('Y-m-d') ?>').format(dateFormatString)
			, to = moment('<?php echo $_Booki_ViewBookings->toDate->format('Y-m-d') ?>').format(dateFormatString)
			, url = "<?php echo admin_url() . "admin.php?page=booki/viewbookings.php&controller=booki_viewbookings&view=$_Booki_ViewBookings->selectedView" ?>"
			, $selectedView = $tabs.find('a[href="#<?php echo $_Booki_ViewBookings->selectedView === 0 ? 'listview' : 'calendarview' ?>"]');
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
		
		$('.filter-by-bookingdate').click(function(){
			var redirectUrl = url + '&from=' + encodeURIComponent($fromDate.val()) + '&to=' + encodeURIComponent($toDate.val());
			window.location.href = redirectUrl;
			return false;
		});
		
		$('.export-csv').click(function(){
			var pageIndex = $('[name="pageindex"]:checked').val()
				, projectId = parseInt($('select[name="projectid"].projectfilter').val(), 10)
				, redirectUrl = "<?php echo $_Booki_ViewBookings->csvUrl ?>" + pageIndex + '&projectid=' + projectId;
			window.location.href = redirectUrl;
			return false;
		});
		
		new Booki.UserInfo({
			"elem": "#userinfo"
			, "ajaxUrl": '<?php echo admin_url('admin-ajax.php') ?>'
			, "triggerButton": ".find-user"
			, "userIdField": "[name=\"userid\"]"
			, "userNotFoundMessage": "<?php echo __('User not found. Try again.', 'booki') ?>"
			, "userFoundMessage": "<?php echo __('User found. Username is', 'booki') ?>"
			, 'success': function(){
				var redirectUrl = url + '&userid=' + $userIdField.val();
				window.location.href = redirectUrl;
				return false;
			}
		});
		<?php if($_Booki_ViewBookings->selectedView === 1):?>
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next, today',
				center: 'title'
			},
			defaultDate: '<?php echo  $_Booki_ViewBookings->calendarViewStartDate ?>',
			editable: false,
			eventLimit: true, // allow "more" link when too many events
			events: <?php echo json_encode($_Booki_ViewBookings->calendarViewData);?>,
			eventRender: function(event, element) {
				var $element = $(element);
				if(event.description){
					element.qtip({
						content: event.description,
						// Position (optional)
						position: {
							my: 'top center',
							at: 'bottom center',
							target: $element
						}
					});
				}
				if(event.headingfield){
					$element.find('.fc-title').prepend('<i class="glyphicon glyphicon-new-window"><i>&nbsp;');
				}
				if(event.namefield){
					$element.find('.fc-title').prepend('<i class="glyphicon glyphicon-user"><i>&nbsp;');
				}
				if(event.statusfield){
					if(event.pendingApproval){
						$element.find('.fc-title').prepend('<i class="glyphicon glyphicon-flag"><i>&nbsp;');
					}else{
						$element.find('.fc-title').prepend('<i class="glyphicon glyphicon-ok"><i>&nbsp;');
					}
				}
				if(event.subfield){
					$element.find('.fc-title').prepend('<i class="glyphicon glyphicon-option-horizontal"><i>&nbsp;');
				}
				if(event.timefield){
					$element.find('.fc-title').prepend('<i class="glyphicon glyphicon-time"><i>&nbsp;');
				}
			}, 
			eventClick: function(event) {
				if (event.url) {
					window.open(event.url, '_blank');
					return false;
				}
			}
		});
		 $('.fc-today-button').on('click', function(e){
			e.preventDefault();
			var today = moment().format(dateFormatString)
				, redirectUrl = url + '&from=' + encodeURIComponent(today) + '&to=' + encodeURIComponent(today);
			window.location.href = redirectUrl;
			return false;
		});
		<?php endif; ?>
		$selectedView.tab('show');
	});
</script>