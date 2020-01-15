<?php
class Booki_DataViewer{
	public $dataViewerList;
	public $hasFullControl;
	public $dataTables = array();
	public $selectedDataTable;
	function __construct( ){
		$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
		$this->selectedDataTable = isset($_GET['tablename']) && trim($_GET['tablename']) ? $_GET['tablename'] : -1;
		if(!$this->hasFullControl){
			return;
		}
		$this->dataViewerList = new Booki_DataViewerList();
		$this->dataViewerList->bind();
		
		add_filter( 'booki_is_backend', array($this, 'isBackEnd'));

		array_push($this->dataTables, 'calendar');
		array_push($this->dataTables, 'calendar_day');
		array_push($this->dataTables, 'cascading_item');
		array_push($this->dataTables, 'cascading_list');
		array_push($this->dataTables, 'coupons');
		array_push($this->dataTables, 'event_log');
		array_push($this->dataTables, 'form_element');
		array_push($this->dataTables, 'optional');
		array_push($this->dataTables, 'order');
		array_push($this->dataTables, 'order_cascading_item');
		array_push($this->dataTables, 'order_days');
		array_push($this->dataTables, 'order_form_elements');
		array_push($this->dataTables, 'order_optionals');
		array_push($this->dataTables, 'order_quantity_element');
		array_push($this->dataTables, 'project');
		array_push($this->dataTables, 'quantity_element');
		array_push($this->dataTables, 'quantity_element_calendar');
		array_push($this->dataTables, 'quantity_element_calendarday');
		array_push($this->dataTables, 'quantity_element_item');
		array_push($this->dataTables, 'settings');
		array_push($this->dataTables, 'quantity_element');
		array_push($this->dataTables, 'quantity_element_item');
		array_push($this->dataTables, 'order_quantity_element');
		array_push($this->dataTables, 'quantity_element_calendar');
		array_push($this->dataTables, 'quantity_element_calendarday');
		array_push($this->dataTables, 'roles');
		array_push($this->dataTables, 'gcal');
		array_push($this->dataTables, 'gcal_projects');
		array_push($this->dataTables, 'gcal_events');
		array_push($this->dataTables, 'trashed');
		array_push($this->dataTables, 'trashed_project');
		array_push($this->dataTables, 'reminders');
	}
	
	public function isBackEnd(){
		return true;
	}
}
$_Booki_DataViewer = new Booki_DataViewer();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Bookings', 'booki')?></h1>
		<p><?php echo __('View raw data stored in the database', 'booki') ?> </p>
	</div>
	<?php if($_Booki_DataViewer->hasFullControl): ?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Raw data viewer', 'booki') ?></h4>
				<p><?php echo __('Listing all records for every column in selected database table', 'booki') ?></p>
			</div>
			<div class="booki-vertical-gap">
				<form action="<?php echo admin_url() . "admin.php?page=booki/dataviewer.php" ?>" method="get" data-parsley-validate>
					<input type="hidden" name="page" value="booki/dataviewer.php"
					<input type="hidden" name="tablename" value="<?php echo $_Booki_DataViewer->selectedDataTable ?>" />
					<div class="form-inline">
						<div class="form-group">
							  <select name="tablename"  class="form-control">
							   <option value="-1"><?php echo __('Select a data table', 'booki') ?></option>
							   <?php foreach($_Booki_DataViewer->dataTables as $value):?>
							   <option value="<?php echo $value?>" 
									<?php echo $_Booki_DataViewer->selectedDataTable === $value ? "selected" : ""?>><?php echo $value ?></option>
							   <?php endforeach;?>
							</select>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-primary" style="margin-bottom: 10px">
								<i class="glyphicon glyphicon-file" 
								data-toggle="tooltip" 
								data-placement="right" 
								data-original-title="<?php echo __('view records', 'booki')?>"></i>
								<?php echo __('Query', 'booki') ?>
							</button>
						</div>
					</div>
				</form>
			</div>
			<hr>
			<div class="table-responsive booki-overflow booki-nowrap">
				<?php $_Booki_DataViewer->dataViewerList->display();?>
			</div>
		</div>
	</div>
	<?php endif;?>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
	});
</script>