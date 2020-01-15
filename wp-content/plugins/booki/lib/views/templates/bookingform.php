<?php
	if(!isset($_Booki_BookingFormTmpl)){
		$_Booki_BookingFormTmpl = new Booki_BookingFormTmpl();
	}
?>
	<?php if($_Booki_BookingFormTmpl->data->enableItemHeading):?>
	<div class="form-group">
		<div class="col-lg-8 col-lg-offset-4">
			<label class="control-label">
				<?php echo $_Booki_BookingFormTmpl->data->projectName ?>
			</label>
		</div>
	</div>
	<?php endif;?>
	<?php if($_Booki_BookingFormTmpl->data->userIsBanned):?>
		<div class="form-group">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="bg-warning booki-bg-box">
					<?php echo __('You cannot make a booking with your current user account.', 'booki') ?>
				</div>
			</div>
		</div>
	<?php endif;?>
	<?php if($_Booki_BookingFormTmpl->data->displayCurrentBookingsCount && $_Booki_BookingFormTmpl->data->bookedDaysCount > 0): ?>
		<div class="form-group booki-booking-limit-counter-alert hide">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="bg-warning booki-bg-box">
					<span class="booki-booking-counter"></span>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::INLINE): ?>
	<div class="form-group">
		<label class="col-lg-12 control-label booki-text-align-left">
			<?php echo $_Booki_BookingFormTmpl->data->availableDaysLabel ?>
		</label>
		<div class="clearfix"></div>
		<div class="col-lg-12">
			<div class="booki-single-datepicker booki-inline-calendar"></div>
		</div>
	</div>
	<?php elseif ($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::POPUP || 
				(($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::RANGE ||  
	 $_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::NEXT_DAY_CHECKOUT) && 
					$_Booki_BookingFormTmpl->data->bookingDaysLimit <= 1)): ?>
	<input type="hidden" name="contractstartdate" />
	<div class="form-group">
		<label class="col-lg-4 control-label">
			<?php echo $_Booki_BookingFormTmpl->data->availableDaysLabel ?>
		</label>
		<div class="col-lg-8">
			<div class="input-group booki-single-datepicker-group">
				<input type="text" 
						id="<?php echo 'datepicker_' . $_Booki_BookingFormTmpl->uniqueKey ?>" 
						class="booki-single-datepicker form-control booki_parsley_validated" 
						readonly="true" 
						data-parsley-trigger="change" 
						data-parsley-errors-container="#<?php echo 'datepicker_error_container_' . $_Booki_BookingFormTmpl->uniqueKey ?>" 
						data-parsley-required="true">
				<label for="<?php echo 'datepicker_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="input-group-addon">
					<?php if(!$_Booki_BookingFormTmpl->data->defaultDateSelected || !$_Booki_BookingFormTmpl->data->globalSettings->includeBookingPrice):?>
					<i class="glyphicon glyphicon-calendar"></i>
					<?php endif;?>
				</label>
			</div>
			<div id="<?php echo 'datepicker_error_container_' . $_Booki_BookingFormTmpl->uniqueKey ?>"></div>
		</div>
	</div>
	<?php elseif ($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::RANGE ||
					$_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::NEXT_DAY_CHECKOUT): ?>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->fromLabel ?>
			</label>
			<div class="col-lg-8">
				<div class="input-group">
					<input type="text" 
							id="<?php echo 'datepicker_from_' . $_Booki_BookingFormTmpl->uniqueKey ?>" 
							class="booki-datepicker-from form-control booki_parsley_validated" 
							readonly="true" 
							data-parsley-trigger="change" 
							data-parsley-errors-container="#<?php echo 'datepicker_from_error_container_' . $_Booki_BookingFormTmpl->uniqueKey ?>" 
							data-parsley-required="true">
					<label for="<?php echo 'datepicker_from_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></label>
				</div>
				<div id="<?php echo 'datepicker_from_error_container_' . $_Booki_BookingFormTmpl->uniqueKey ?>"></div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->toLabel ?>
			</label>
			<div class="col-lg-8">
				<div class="input-group">
					<input type="text" 
							id="<?php echo 'datepicker_to_' . $_Booki_BookingFormTmpl->uniqueKey ?>" 
							class="booki-datepicker-to form-control booki_parsley_validated" 
							readonly="true" 
							data-parsley-trigger="change" 
							data-parsley-errors-container="#<?php echo 'datepicker_to_error_container_' . $_Booki_BookingFormTmpl->uniqueKey ?>" 
							data-parsley-required="true">
					<label for="<?php echo 'datepicker_to_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></label>
				</div>
				<div id="<?php echo 'datepicker_to_error_container_' . $_Booki_BookingFormTmpl->uniqueKey ?>"></div>
			</div>
		</div>
	<?php elseif ($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::EVENT): ?>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->fromLabel ?>
			</label>
			<div class="col-lg-8">
				 <p class="form-control-static booki-event-control"><?php echo $_Booki_BookingFormTmpl->data->formattedStartDate ?></p>
			</div>
		</div>
		<?php if (!$_Booki_BookingFormTmpl->data->singleDayEvent): ?>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->toLabel ?>
			</label>
			<div class="col-lg-8">
				 <p class="form-control-static booki-event-control"><?php echo $_Booki_BookingFormTmpl->data->formattedEndDate ?></p>
			</div>	
		</div>
		<?php endif; ?>
	<?php endif; ?>
	<div class="clearfix"></div>
	<?php if($_Booki_BookingFormTmpl->data->bookingDaysMinimum): ?>
		<div class="form-group booki-minimum-days-required hide">
				<div class="col-lg-8 col-lg-offset-4">
					<div class="bg-warning booki-bg-box">
					<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_DAY):?>
						<?php echo sprintf(__('A minimum of %d days required.', 'booki'), $_Booki_BookingFormTmpl->data->bookingDaysMinimum) ?>
					<?php else: ?>
						<?php echo sprintf(__('A minimum of %d time slot selections required.', 'booki'), $_Booki_BookingFormTmpl->data->bookingDaysMinimum) ?>
					<?php endif;?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_Booki_BookingFormTmpl->data->bookingDaysLimit > 1):?>
		<div class="form-group booki-booking-limit hide">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="bg-warning booki-bg-box">
					<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_DAY):?>
						<?php echo str_replace('{0}' , $_Booki_BookingFormTmpl->data->bookingDaysLimit, __('You can only book {0} days at a time. Excess days not applied.', 'booki')) ?>
					<?php else: ?>
						<?php echo str_replace('{0}' , $_Booki_BookingFormTmpl->data->bookingDaysLimit, __('You can only book {0} slots at a time. Excess time not applied.', 'booki')) ?>
					<?php endif;?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_DAY &&
			($_Booki_BookingFormTmpl->data->bookingDaysLimit > 1 && !$_Booki_BookingFormTmpl->data->hideSelectedDays)): ?>
	<div class="form-group">
		<label class="col-lg-4 control-label">
			<?php echo $_Booki_BookingFormTmpl->data->selectedDaysLabel ?>
		</label>
		<div class="col-lg-8">
			<ul class="booki-dates"></ul>
		</div>
	</div>
	<?php endif; ?>
	<div id="selected_date_container_<?php echo $_Booki_BookingFormTmpl->data->projectId?>"></div>
	<input name="selected_date" 
			class="booki-selected-date booki_parsley_validated" 
			type="hidden"
			<?php if($_Booki_BookingFormTmpl->data->calendarMode === 1/*inline*/):?>
			data-parsley-required="true" 
			data-parsley-error-message="<?php echo __('Please select a date.', 'booki')?>"
			<?php endif;?>
			/>
	<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_TIME): ?>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->bookingTimeLabel ?>
			</label>
			<div class="col-lg-8">
				<?php if($_Booki_BookingFormTmpl->data->globalSettings->timeSelector === Booki_TimeSelector::DROPDOWNLIST): ?>
				<select name="time[]" class="booki-time form-control" disabled></select>
				<?php elseif ($_Booki_BookingFormTmpl->data->globalSettings->timeSelector === Booki_TimeSelector::LISTBOX): ?>
				<select name="time[]" class="booki-time form-control" multiple="multiple" disabled></select>
				<?php endif; ?>
			</div>
		</div>
		<div class="form-group hide booki-time-slots-exhausted">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="bg-warning booki-bg-box">
					<strong><?php echo __('Exhausted!', 'booki') ?></strong> <?php echo __('All available slots have been added to cart. Check out ?', 'booki')?>
				</div>
			</div>
		</div>
		<div class="form-group hide booki-time-slots-booked">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="bg-warning booki-bg-box">
					<strong><?php echo __('Booked!', 'booki') ?></strong> <?php echo __('All available slots have been booked for this day.', 'booki')?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-8 col-md-offset-4">
				<div class="progress progress-striped active booki-time-progress hide">
					<div class="progress-bar"  role="progressbar" aria-valuenow="100" 
						aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
		<?php if($_Booki_BookingFormTmpl->data->globalSettings->enableTimezoneEdit):?>
		<div class="form-group">
			<div class="col-lg-12">
				<?php Booki_ThemeHelper::includeTemplate('timezonecontrol.php') ?>
			</div>
		</div>
		<?php endif; ?>
	<?php else: ?>
	<div class="form-group hide booki-days-exhausted">
		<div class="col-lg-8 col-lg-offset-4">
			<div class="bg-warning booki-bg-box">
				<strong><?php echo __('Exhausted!', 'booki') ?></strong> <?php echo __('All Available days have been added to cart. Checkout ?', 'booki') ?>
			</div>
		</div>
	</div>
	<?php endif;?>