<?php
$_Booki_TimezoneControlTmpl = new Booki_TimezoneControlTmpl();
?>
<div class="booki booki-timezone-control">
	<div class="form-group">
		<label class="col-lg-4 control-label" for="region">
				<?php echo __('Current Timezone', 'booki') ?>
		</label>
		<div class="col-lg-8">
			<a class="btn btn-default booki-timezone-heading" data-toggle="collapse" href=".collapseTimezone">
				<i class="glyphicon glyphicon-globe"></i>
				<span class="timezone-header"><?php echo __('Change Timezone', 'booki') ?></span>
			</a>
		</div>
	</div>
	<div class="collapseTimezone accordion-body collapse">
		<div class="form-group">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="bg-warning booki-bg-box">
					<strong><?php echo __('Timezone!', 'booki') ?></strong> <?php echo __('Booking time will adapt to the timezone you select below.', 'booki')?>
				</div>
			</div>
		</div>
		<div class="form-group region">
			<label class="col-lg-4 control-label" for="region">
				<?php echo __('Region', 'booki') ?>
			</label>
			<div class="col-lg-8">
				<select name="region" class="form-control">
				<option value="-1"><?php echo __('Select a region', 'booki') ?></option>
				<?php echo $_Booki_TimezoneControlTmpl->regions; ?>
				</select>
			</div>
		</div>
		<div class="form-group timezone hide">
			<label class="col-lg-4 control-label" for="timezone">
				<?php echo __('Timezone', 'booki') ?>
			</label>
			<div class="col-lg-8">
				<select name="timezone" class="form-control"></select>
			</div>
		</div>
		<div class="form-group autodetect">
			<div class="col-lg-8 col-md-offset-4">
				<div class="checkbox">
				   <label>
						<input name="autodetect" type="checkbox" title="<?php echo __('We will try to guess your timezone. For accuracy, we recommend you to manually select your timezone.', 'booki') ?>" /> 
						<?php echo __('Guess my timezone', 'booki')?>
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-8 col-md-offset-4">
				<div class="progress progress-striped active hide">
					<div class="progress-bar"  role="progressbar" aria-valuenow="100" 
						aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	(function() {
		if (window.addEventListener){
		  window.addEventListener('load', onload, false); 
		} else if (window.attachEvent){
		  window.attachEvent('onload', onload);
		}

		function onload(e){
			new Booki.TimezoneControl({
				"elem": ".booki-timezone-control"
				, "headerCaption": ".timezone-header"
				, "region": 'select[name="region"]'
				, "timezone": ".timezone"
				, "loadOnStart": 'input[name="booki_timezonecontrol_loadonstart"]'
				, "timezoneManualSelection": 'input[name="booki_timezone_selection"]'
				, "ajaxurl": "<?php echo admin_url('admin-ajax.php') ?>"
			});
		}
	})();

</script>
