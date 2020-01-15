<input type="hidden" name="userid" />
<div class="form-group">
	<label class="sr-only" for="useremail">
		<?php echo __('Find booking by email', 'booki') ?>
	</label>
	<input type="text" 
			id="useremail" 
			class="form-control"
			name="useremail" 
			placeholder="<?php echo __('userid@thedomain.com', 'booki') ?>"
			data-parsley-errors-container="#useremail-error-container"
			data-parsley-required="true" 
			data-parsley-type="email" 
			data-parsley-trigger="change" />
</div>
<div id="useremail-error-container" class="form-group"></div>
<div class="form-group">
	<a class="btn btn-default find-user" href="#">
		<i class="glyphicon glyphicon-search"></i>
		<?php echo __('Find', 'booki') ?>
	</a>
</div>
<div>
	<div class="progress progress-striped active booki-useremail hide">
		<div class="progress-bar"  role="progressbar" aria-valuenow="100" 
			aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
	</div>
	<div class="bg-info booki-bg-box hide useremail-info"></div>
</div>


