(function(window, document){
	var Booki = Booki || {};
	/**
		@description The createDelegate function is useful when setting up an event handler to point 
		to an object method that must use the this pointer within its scope.
	*/
	Booki.createDelegate = function (instance, method) {
		return function () {
			return method.apply(instance, arguments);
		};
	}
	
	/**
		@description Allows us to retain the this context and optionally pass an arbitrary list of parameters.
	*/
	Booki.createCallback = function (method, context, params) {
		return function() {
			var l = arguments.length;
			if (l > 0) {
				var args = [];
				for (var i = 0; i < l; i++) {
					args[i] = arguments[i];
				}
				args[l] = params;
				return method.apply(context || this, args);
			}
			return method.call(context || this, params);
		}
	}
	if(!window['Booki']){
		window['Booki'] = Booki;
	}
}(window, document));
/**
	 @license Copyright @ 2014 Alessandro Zifiglio. All rights reserved. http://www.booki.io
*/
(function(window, $){
	var Booki = window['Booki'] || function(){};
	Booki.Cookie = function(){};
	Booki.Cookie.create =  function(name,value,days) {
		var date
			, expires;
		if (days) {
			date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			expires = "; expires="+date.toGMTString();
		}
		else {
			expires = "";
		}
		document.cookie = name+"="+value+expires+"; path=/";
	};
	Booki.Cookie.read = function(name) {
		var nameEQ = name + "="
			, ca = document.cookie.split(';')
			, c
			, i;
		for(i=0;i < ca.length;i++) {
			c = ca[i];
			while (c.charAt(0)==' '){ 
				c = c.substring(1,c.length);
			}
			if (c.indexOf(nameEQ) == 0){ 
				return c.substring(nameEQ.length,c.length);
			}
		}
		return null;
	};
	Booki.Cookie.erase = function(name) {
		Booki.Cookie.create(name,"",-1);
	};
	Booki.Cookie.destroy = function( ) {};
	window['Booki'] = Booki;
	
})(window, window['jQuery']);
(function($, moment, accounting, Booki){
	Booki.Booking = function(options){
		this.init(options);
		this.destroyDelegate = Booki.createDelegate(this, this.destroy);
		$(window).unload(this.destroyDelegate);
	};
	Booki.Booking.SeatMode = {'perEntireBookingPeriod': 0, 'perDay': 1, 'perIndividualTimeslot': 2};
	Booki.Booking.prototype.init = function(options){
		var context = this
			, hasBookings
			, settings = $.extend({}, options)
			, elem = settings['elem'];
			
		this.$root = $(elem);
		this.projectId = settings['projectId'];
		this.defaultDateSelected = settings['defaultDateSelected'];
		this.dateFormatString = settings['dateFormat'];
		this.altFormat = settings['altFormat'];
		this.calendar = settings['calendar'];
		this.calendarDays = settings['calendarDays'];
		this.minDate = settings['minDate'];
		this.maxDate = settings['maxDate'];
		this.currencySymbol = settings['currencySymbol'];
		this.currency = settings['currency'];
		this.decimalPoint = settings['decimalPoint'];
		this.thousandsSep = settings['thousandsSep'];
		this.timezone = settings['timezone'];
		this.ajaxUrl = settings['ajaxurl'];
		this.$daysExhausted = this.$root.find('.booki-days-exhausted');
		this.$timeSlotsExhausted = this.$root.find('.booki-time-slots-exhausted');
		this.$timeSlotsBooked = this.$root.find('.booki-time-slots-booked');
		this.$optionals = this.$root.find('.booki-optional');
		this.$popupCalendar = this.$root.find('.booki-single-datepicker');
		this.$fromPopupCalendar = this.$root.find('.booki-datepicker-from');
		this.$toPopupCalendar = this.$root.find('.booki-datepicker-to');
		this.$selectionLimit = this.$root.find('.booki-booking-limit');
		this.$datePickerMinDateSelection = this.$root.find('.booki-datepicker-min-date-selection');
		this.$singleDatePickerAddon = this.$root.find('.booki-single-datepicker-group .input-group-addon');
		this.$selectedDateInput = this.$root.find('.booki-selected-date');
		this.bookingDaysMinimum = settings['bookingDaysMinimum'];
		this.bookingDaysLimit = settings['bookingDaysLimit'];
		this.$totalsLabel = this.$root.find('.booki-totals-label');
		this.$timeSlotsDropDownList = this.$root.find('select[name="time[]"]');
		this.$timezoneDropDownList = this.$root.find('select[name="timezone"]');
		this.$autoDetect = this.$root.find('input[name="autodetect"]');
		this.bookingMode = settings['bookingMode'];	
		this.calendarMode = settings['calendarMode'];
		this.usedSlots = settings['usedSlots'];
		this.autoTimezoneDetection = settings['autoTimezoneDetection'];
		this.timeSelector = settings['timeSelector'];
		this.discount = settings['discount'];
		this.bookingMinimumDiscount = settings['bookingMinimumDiscount'];
		this.bookedItemsCount = settings['bookedItemsCount'];
		this.includeBookingPrice = settings['includeBookingPrice'];
		this.calendarCssClasses = settings['calendarCssClasses'];
		this.calendarFirstDay = settings['calendarFirstDay'];
		this.showCalendarButtonPanel = settings['showCalendarButtonPanel'];
		this.displayBookedTimeSlots = settings['displayBookedTimeSlots'];
		this.$subTotalContainer = this.$root.find('.booki-sub-total');
		this.$depositContainer = this.$root.find('.booki-deposit');
		this.$depositLabel = this.$root.find('.booki-deposit-label');
		this.$subTotalLabel = this.$root.find('.booki-sub-total-label');
		this.$discountContainer = this.$root.find('.booki-discount');
		this.$discountLabel = this.$root.find('.booki-discount-label');
		this.$optionalsCount = this.$root.find('.booki_optionals_count');
		this.$progressTimeslots = this.$root.find('.progress.booki-time-progress');
		this.optionalsBookingMode = settings['optionalsBookingMode'];
		this.optionalsListingMode = settings['optionalsListingMode'];
		this.optionalsMinimumSelection = settings['optionalsMinimumSelection'];
		this.defaultCascadingListSelectionLabel = settings['defaultCascadingListSelectionLabel']
		this.highlightSelectedOptionals = settings['highlightSelectedOptionals'];
		this.$cascadingLists = this.$root.find('.booki-cascading-list');
		this.$progressCascades = this.$root.find('.progress.booki-progress-cascades');
		this.hideSelectedDays = settings['hideSelectedDays'];
		this.$minimumDaysRequired = this.$root.find('.booki-minimum-days-required');
		this.$deposit = this.$root.find('input[name="deposit_field"]');
		this.$bookNowPayLaterButton = this.$root.find('button[name="booki_checkout"][value="0"]');
		this.$payNowButton = this.$root.find('button[name="booki_checkout"][value="1"]');
		this.$addToCartButton = this.$root.find('button[name="booki_add_cart"]');
		this.$selectedDatesContainer = this.$root.find('.booki-dates');
		this.$bookingLimitCounterAlert = this.$root.find('.booki-booking-limit-counter-alert');
		this.$bookingCounterLabel = this.$root.find('.booki-booking-counter');
		this.bookingLimitLabel = settings['bookingLimitLabel'];
		this.quantityElements = settings['quantityElements'];
		this.quantityElementsReserved = settings['quantityElementsReserved'];
		this.quantityElementsFromCart = settings['quantityElementsFromCart'];
		this.$dynamicQuantityElementPlaceholder = this.$root.find('.booki-dynamic-quantity-element-placeholder');
		this.quantityElementExhaustedAlertMessage = settings['quantityElementExhaustedAlertMessage'];
		this.precision = settings['precision'];
		this.currencySymbolPosition = settings['currencySymbolPosition'];
		this.selectedQuantityElements = [];
		this.exhaustedSlots = [];
		this.timeslotsCache = [];
		this.currencyFormatString = '%s %v';
		if(this.currencySymbolPosition === 1/*right*/){
			this.currencyFormatString = '%v %s';
		}
		if(this.currencySymbol){
			this.currencySymbol = $('<div>').html(this.currencySymbol).text();
		}
		$(document).ajaxStop(function(e){
			if(context.$progressTimeslots){
				context.$progressTimeslots.hide();
			}
			if(context.$progressCascades){
				context.$progressCascades.hide();
			}
		});
		this.$autoDetect.on('click', function(){
			if($(this).is(':checked')){
				context.getTimeSlots(context.formatDate(context.$selectedDateInput.val()));
			}
		});
		if(this.defaultDateSelected){
			this.$selectedDateInput.val(this.formatDate(this.minDate));
		}
		if(this.bookingDaysLimit > 1 && this.calendar['period'] === 0/*by_day*/){
			this.$selectedDatesContainer.addClass('booki-outer-border');
		}
		
		//readonly, so disable backspace.
		this.disableBackspaceDelegate = Booki.createDelegate(this, this.disableBackspace);
		this.$popupCalendar.on('keydown', this.disableBackspaceDelegate);
		this.$fromPopupCalendar.on('keydown', this.disableBackspaceDelegate);
		this.$toPopupCalendar.on('keydown', this.disableBackspaceDelegate);
		
		this.timezoneChangedDelegate = Booki.createDelegate(this, this.timezoneChanged);
		this.$timezoneDropDownList.change(this.timezoneChangedDelegate);
		
		$(this.usedSlots).each(function(i, item){
			if(item['slotsExhausted']){
				context.exhaustedSlots.push(item['day']);
			}
		});
		
		this.timeSlotsChangedDelegate = Booki.createDelegate(this, this.timeSlotsChanged);
		this.$timeSlotsDropDownList.change(this.timeSlotsChangedDelegate);
		
		this.cascadingListItemSelectedDelegate = Booki.createDelegate(this, this.cascadingListItemSelected);
		this.$cascadingLists.on('change', this.cascadingListItemSelectedDelegate);
		
		hasBookings = this.datePicker();
		if(!hasBookings){
			this.$optionals.attr('disabled', true);
			this.$addToCartButton.attr('disabled', true);
		}else{
			this.updateTotalDelegate = Booki.createDelegate(this, this.updateTotal);
			this.$optionals.change(this.updateTotalDelegate);
		}
		this.updateTotal();
	};
	Booki.Booking.prototype.timeSlotsChanged =  function(e){
		var timeslots
			, result = this.bookingDaysLimitCheck()
			, options = this.$timeSlotsDropDownList.find('option:selected')
			, $option
			, returnValue
			, i;
		if (!result){
			for(i = 0; i < options.length; i++){
				$option = $(options[i]);
				if (i >= this.bookingDaysLimit){
					$option.removeAttr('selected');
				}
			}
		}
		for(i = 0; i < options.length; i++){
			$option = $(options[i]);
			timeslots = this.parseTimeslots($option.val());
			returnValue = this.applySeats(this.currentDate, timeslots, true);
			if(returnValue['seatSupport'] && !returnValue['hasSeats']){
				$option.removeAttr('selected');
			}
		}
		this.updateQuantityElementTimeslot();
		this.minimumDaysCheck();
		this.updateTotal();
	};
	Booki.Booking.prototype.parseTimeslots = function(slots){
		var parts
			, pair
			, start
			, end;
		parts = slots.split(',');
		start = parts[0].split(':');
		end = parts[1].split(':');
		return {
			'hourStart': parseInt(start[0], 10)
			, 'minuteStart': parseInt(start[1], 10)
			, 'hourEnd': parseInt(end[0], 10)
			, 'minuteEnd': parseInt(end[1], 10)
		}
	};
	Booki.Booking.prototype.bookingDaysLimitCheck =  function(diff){
		var dates = this.getSelectedDates()
			, result = true
			, length = this.calendar['period'] === 0/*by_day*/ ? dates.length : this.$timeSlotsDropDownList.find('option:selected').length;
		if(typeof(diff) !== 'undefined'){
			length = diff;
		}
		if(this.bookingDaysLimit > 1 && length > this.bookingDaysLimit){
			this.$selectionLimit.removeClass('hide');
			result = false;
		}else{
			this.$selectionLimit.addClass('hide');
		}
		return result;
	};
	Booki.Booking.prototype.minimumDaysCheck =  function(){
		var dates = this.getSelectedDates()
			, result = true
			, length = this.calendar['period'] === 0/*by_day*/ ? dates.length : this.$timeSlotsDropDownList.find('option:selected').length;
		if(this.bookingDaysMinimum && length < this.bookingDaysMinimum){
			result = false;
			this.$minimumDaysRequired.removeClass('hide');
			this.commandButtonStatus(true);
		}else{
			this.$minimumDaysRequired.addClass('hide');
			this.commandButtonStatus(false);
		}
		
		if(result){
			this.testAddToCartButton();
		}
		return result;
	};
	Booki.Booking.prototype.getSelectedDates =  function(){
		var val = this.$selectedDateInput.val()
		, dates = val ? val.split(',') : [];
		return dates;
	};
	Booki.Booking.prototype.addSelectedDates =  function(selectedDate){
		var cost = this.getCost(selectedDate)
			, costFormatted = accounting.formatMoney(cost, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString)
			, $removeButtons = this.$selectedDatesContainer.find('li')
			, removeButton = '<i class="glyphicon glyphicon-trash"></i>'
			, priceBadge = (this.includeBookingPrice ? '<span class="badge">' + costFormatted + '</span>' : '')
			, removeButtonContainer = '<span class="booki-remove-button-container">'
			, dates = this.getSelectedDates()
			, $item
			, exit = false
			, $li
			, $i;
		
		if(cost > 0){
			removeButtonContainer += priceBadge;
		}
		$removeButtons.each(function(i, item){
			var date = $(item).data('value');
			if(date === selectedDate){
				exit = true;
				return false;
			}
		});
		
		if (exit){
			return;
		}
		
		if(this.bookingDaysLimit === 1 || this.calendar['period'] === 1/*by_time*/){
			this.$selectedDatesContainer.empty();
		}
		this.$selectedDatesContainer.append('<li ' + 'data-value="' + selectedDate + '"><span class="pull-left">' + selectedDate + '</span>' + removeButtonContainer + removeButton + '</span></li>');
		$li = this.$selectedDatesContainer.find('[data-value="' + selectedDate + '"]');
		this.selectedDateRemoveClickDelegate = Booki.createDelegate(this, this.onSelectedDateRemoveClick);
		$li.find('i').on('click', this.selectedDateRemoveClickDelegate);
		$item = this.$selectedDatesContainer.find('[data-value="' + dates[0] + '"] span.booki-remove-button-container');
		$i = $item.find('i');
		if($i.length === 0){
			$item.append(removeButton);
			$item.find('i').on('click', this.selectedDateRemoveClickDelegate);
		}
		window.setTimeout(function(){
			$li.addClass('booki-slide-in');
		}, 50);
	};
	Booki.Booking.prototype.onSelectedDateRemoveClick =  function(e){
		var $target = $(e.currentTarget)
			, selectedDate = $target.parent().parent().data('value');
		this.selectedDateRemove(selectedDate);
		this.bookingDaysLimitCheck();
		this.updateSeatsCounter(this.calendar['period']);
		this.minimumDaysCheck();
		this.updateTotal();
	};
	Booki.Booking.prototype.selectedDateRemove =  function(selectedDate){
		var $item
			, dates = this.getSelectedDates()
			, i
			, d
			, startDate
			, endDate;
		
		for(i = 0; i < dates.length;i++){
			if(dates[i] === selectedDate){
				dates.splice(i, 1);
				break;
			}
		}
		if(!this.hideSelectedDays){
			$item = this.$root.find('[data-value="' + selectedDate + '"]');
			$item.removeClass('booki-slide-in');
			(function ($item) {
			  window.setTimeout(function(){
				$item.remove();
			  }, 1000);
			})($item);
		}
		
		this.$selectedDateInput.val(dates.join());
		if(dates.length === 0){
			this.$popupCalendar.datepicker('setDate', null);
			this.$toPopupCalendar.datepicker('setDate', null);
			this.$fromPopupCalendar.datepicker('setDate', null);
		}
		if(dates.length > 0){
			startDate = this.formatMoment(dates[0]);
			endDate = this.formatMoment(dates[dates.length - 1]);
			for(i = 0; i < dates.length;i++){
				d = this.formatMoment(dates[i]);
				if(d.isAfter(endDate)){
					endDate = d;
				}
				if(d.isBefore(startDate)){
					startDate = d;
				}
			}
			
			(function (startDate, endDate, context) {
				  window.setTimeout(function(){
					if(context.$popupCalendar.length > 0){
						context.$popupCalendar.datepicker('setDate', context.formatDate(endDate));
					}else{
						context.$fromPopupCalendar.datepicker('setDate', context.formatDate(startDate));
						context.$toPopupCalendar.datepicker('setDate', context.formatDate(endDate));
					}
				  }, 50);
			})(startDate, endDate, this);
		}
		this.clearQuantityElements(dates.length === 0);
		if(!this.calendar['period'] && dates.length > 0){
			this.updateQuantityElements(startDate);
		}
		if(!this.defaultDateSelected){
			this.$selectedDateInput.parsley().validate(true);
		}
	};
	Booki.Booking.prototype.testAddToCartButton =  function(){
		var disabled = !this.$selectedDateInput.val();
		this.commandButtonStatus(disabled);
		return !disabled;
	};
	Booki.Booking.prototype.formatDate =  function(value){
		var m = moment.isMoment(value) ? value : moment(this.stringToDate(value));
		return m.format(this.dateFormatString);
	};
	Booki.Booking.prototype.formatMoment =  function(value){
		return moment.isMoment(value) ? value : moment(this.stringToDate(value), this.dateFormatString);
	};
	Booki.Booking.prototype.stringToDate = function(_date){
		if(_date instanceof Date && !isNaN(_date.valueOf())){
			return _date;
		}
		var _format = this.dateFormatString
			, formatLowerCase = _format.toLowerCase()
			, _delimiter = this.parseDelimiter(_format)
			, formatItems = formatLowerCase.split(_delimiter)
			, dateItems = _date.split(_delimiter)
			, monthIndex = formatItems.indexOf("mm")
			, dayIndex = formatItems.indexOf("dd")
			, yearIndex = formatItems.indexOf("yyyy")
			, month = parseInt(dateItems[monthIndex], 10);
		month -= 1;
		return new Date(dateItems[yearIndex],month,dateItems[dayIndex]);
	};
	Booki.Booking.prototype.parseDelimiter = function(value){
		if(value.indexOf('/') !== -1){
			return '/';
		}else if(value.indexOf('.') !== -1){
			return '.';
		}else if(value.indexOf('-') !== -1){
			return '-';
		}
	};
	Booki.Booking.prototype.calculateSelectedDaysCost =  function(){
		var dates = this.getSelectedDates()
		, length = dates.length
		, date
		, i
		, total = 0;

		for(i = 0; i < length; i++){
			date = dates[i];
			total += this.getCost(date);
		}
		return total;
	};
	Booki.Booking.prototype.getCost =  function(dateText){
		var i
			, length = this.calendarDays.length
			, calendarDay;
		for(i = 0; i < length; i++){
			calendarDay = this.calendarDays[i];
			if(dateText === calendarDay.day){
				return this.parseDouble(calendarDay.cost);
			}
		}
		return this.parseDouble(this.calendar.cost);
	};
	Booki.Booking.prototype.updateSeatsCounter = function(calendarPeriod){
		var dates = this.getSelectedDates()
			, i
			, returnValue
			, date
			, time
			, options
			, $option
			, label
			, result = [];
		if(calendarPeriod === 0/*by_day*/){
			for(i = 0; i < dates.length; i++){
				date = dates[i];
				returnValue = this.applySeats(date);
				if(returnValue['seatSupport']){
					label = this.bookingLimitLabel.replace('%d', returnValue['count']);
					result.push('<div class="booki-booking-count-item"><strong>' + date + '</strong>: ' + '<span>' + label + '</span></div>');
				}
			}
		}else{
			options = this.$timeSlotsDropDownList.find('option:selected');
			date = this.currentDate;
			for(i = 0; i < options.length; i++){
				$option = $(options[i]);
				time = this.parseTimeslots($option.val());
				returnValue = this.applySeats(date, time);
				if(returnValue['seatSupport']){
					label = this.bookingLimitLabel.replace('%d', returnValue['count']);
					result.push('<div class="booki-booking-count-item"><strong>' + date + ', <i>' + $option.text() + '</i></strong>: ' + '<span>' + label + '</span></div>');
				}
			}
		}
		if(result.length > 0){
			this.$bookingLimitCounterAlert.removeClass('hide');
			this.$bookingCounterLabel.html(result.join(''));
		}
	};
	Booki.Booking.prototype.applySeats = function(date, time, updateCounter){
		var i
			, j
			, count = null
			, seats = this.calendar['seats']
			, seatMode = null
			, bookingLimit = null
			, seat
			, calendarDay;

		for(j = 0; j < this.calendarDays.length; j++){
			calendarDay = this.calendarDays[j];
			if(calendarDay['day'] == date){
				seatMode = calendarDay['seatMode'];
				bookingLimit = calendarDay['bookingLimit'];
				break;
			}
		}
		if(seatMode === null){
			seatMode = this.calendar['seatMode'];
			bookingLimit = this.calendar['bookingLimit'];
		}

		if(bookingLimit === 0 || seatMode === 0/*Booki.Booking.SeatMode.perEntireBookingPeriod*/){
			//we handle this serverside
			return {'hasSeats': true, 'count': bookingLimit, 'seatSupport': bookingLimit > 0};
		}
		for(i = 0; i < seats.length; i++){
			seat = seats[i];
			if(seatMode === 1/*Booki.Booking.SeatMode.perDay*/){
				if(seat['bookingDate'] == date){
					count = bookingLimit - seat['bookedDaysCount'];
					break;
				}
			}else if(this.calendar['seatMode'] === 2/*Booki.Booking.SeatMode.perIndividualTimeslot*/ && time){
				if(seat['bookingDate'] == date && ((seat['hourStart'] == time['hourStart'] && seat['minuteStart'] == time['minuteStart'])
												&& seat['hourEnd'] == time['hourEnd'] && seat['minuteEnd'] == time['minuteEnd'])){
					count = bookingLimit - seat['timeslotsCount'];
					break;
				}
			}
		}
		if(count === null){
			count = bookingLimit;
		}
		if(updateCounter){
			this.updateSeatsCounter(this.calendar['period']);
		}
		return  {'hasSeats': count > 0, 'count': count, 'seatSupport': bookingLimit > 0};
	};
	Booki.Booking.prototype.updateTotal =  function(){
		if(!this.includeBookingPrice){
			return;
		}
		var total = 0
			, slots = this.$timeSlotsDropDownList.find('option:selected').length
			, dates = this.getSelectedDates()
			, selectedDaysCost = this.calculateSelectedDaysCost()
			, hasDiscount = (this.discount > 0 && (this.bookedItemsCount >= this.bookingMinimumDiscount || this.bookingMinimumDiscount === 0))
			, count = this.timeSelector === 1/*LISTBOX*/&& slots > 1 ? slots : dates.length
			, totalFormatted
			, deposit = this.applyDeposit()
			, depositValue;
		
		if(!count){
			count = 1;
		}
		total += this.cascadingItemsTotal(count);
		total += this.optionalsTotal(count);
		total += this.getQuantityElementsTotalCost();
		if(this.timeSelector === 1/*LISTBOX*/&& slots > 1){
			selectedDaysCost = selectedDaysCost * slots;
		}
		this.ensureTimeSlotSelection();
		total += selectedDaysCost;

		if(hasDiscount || deposit > 0){
			this.$subTotalContainer.removeClass('hide');
			this.$subTotalLabel.html(accounting.formatMoney(total, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString));
		}
		else{
			this.$subTotalContainer.addClass('hide');
		}
		if(hasDiscount){
			this.$discountContainer.removeClass('hide');
			this.$discountLabel.html(-this.discount + '%');
			total -= ((this.discount / 100) * total);
		}else{
			this.$discountContainer.addClass('hide');
		}
		if(deposit > 0){
			depositValue = (total/100)*deposit;
			total -= depositValue;
			this.$depositContainer.removeClass('hide');
			this.$depositLabel.html(accounting.formatMoney(depositValue, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString));
		}else{
			this.$depositContainer.addClass('hide');
		}
		totalFormatted = accounting.formatMoney(total, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString);
		this.$totalsLabel.html(totalFormatted);
		
		if(total > 0){
			this.$payNowButton.removeAttr('disabled');
		}else{
			this.$payNowButton.attr('disabled', true);
		}
	};
	Booki.Booking.prototype.cascadingItemsTotal = function(count){
		var i
			, total = 0
			, $costContainer
			, $item
			, cost
			, parentId
			, originalValue
			, formattedCost
			, $options = this.$root.find('.booki-cascading-list option');
			
		for(i = 0; i < $options.length;i++){
				$item = $($options[i]);
				cost = this.parseDouble($item.data('bookiCost'))
				parentId = parseInt($item.data('bookiParent'), 10)
				originalValue = $item.data('bookiOriginalValue');
			if((cost !== 0 && !isNaN(cost)) && parentId === -1){
				if(this.optionalsBookingMode === 1/*apply to each day or time slot*/){
					cost = cost * count;
					formattedCost = accounting.formatMoney(cost, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString);
					$item.html(originalValue + '&nbsp;&nbsp;x ' +  count + ' = ' + formattedCost);
				}
				if($item.is(':selected')){
					total += cost;
				}
			}
		}
		return total;
	};
	Booki.Booking.prototype.optionalsTotal = function(count){
		var i
			, total = 0
			, $item 
			, cost
			, $parent
			, $costContainer
			, formattedCost;
			
		for(i = 0; i < this.$optionals.length;i++){
			$item = $(this.$optionals[i]);
			cost = this.parseDouble($item.data('cost'))
			$parent = $($item.parent())
			$costContainer = $parent.find('.booki_optionals_cost');
			if(this.optionalsBookingMode === 1/*apply to each day or time slot*/){
				cost = cost * count;
				this.$optionalsCount.html(' x ' + count);
			}
			if($item.is(':checked')){
				total += cost;
				if(this.highlightSelectedOptionals){
					$parent.addClass('active');
				}
			}else {
				if(this.highlightSelectedOptionals){
					$parent.removeClass('active');
				}
			}
			formattedCost = accounting.formatMoney(cost, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString);
			$costContainer.html(formattedCost);
		}
		return total;
	};
	Booki.Booking.prototype.allowAdvanceBooking =  function(dateText){
		var then = this.formatMoment(dateText)
			, now = moment()
			, diff;
		diff = Math.round(this.minutesDiff(now._d, then._d));
		if(this.calendar.bookingStartLapse !== 0 && diff < this.calendar.bookingStartLapse){
			return false;
		}
		return true;
	};
	Booki.Booking.prototype.bookingDateAvailable =  function(dateText){
		var current = this.formatMoment(dateText)
			, now = moment()
			, diff
			, then;
		current = current.hours(now.hours()).minutes(now.minutes());
		then = now.minutes(this.calendar.bookingStartLapse);
		if(current < then && !current.isSame(then, 'day')){
			//before minimum notice, so not available
			return false;
		}
		return true;
	};
	Booki.Booking.prototype.minutesDiff = function getMinutesBetweenDates(startDate, endDate) {
		var diff = endDate.getTime() - startDate.getTime();
		return (diff / 60000);
	};
	Booki.Booking.prototype.isValidDay = function(formattedDay){
		var result
			, returnValue = true
			, weekDay = this.formatMoment(formattedDay).weekday();
		if(!this.bookingDateAvailable(formattedDay)){
			return false;
		}
		
		result = this.applySeats(formattedDay);
		if(!result['hasSeats'] || !this.containsQuantity(this.formatMoment(formattedDay))){
			return false;
		}
		
		$(this.calendar.daysExcluded).each(function(i, item){
			if(item === formattedDay){
				returnValue = false;
				return false;
			}
		});
		
		if(this.bookingMode === 1/*1 = Appointment*/){
			$(this.usedSlots).each(function(i, item){
				if(item['day'] === formattedDay && item['slotsExhausted']){
					returnValue = false;
					return false;
				}
			});
		}
		
		$(this.calendar.weekDaysExcluded).each(function(i, item){
			if(weekDay === item){
				returnValue = false;
				return false;
			}
		});
		return returnValue;
	};
	Booki.Booking.prototype.beforeShowDay =  function(dateText) {
		var weekDay = dateText.getDay()
			, selectable = true
			, dates = this.getSelectedDates()
			, highlight = false
			, formattedDay = this.formatDate(dateText)
			, result = this.isValidDay(formattedDay);
		
		if(!result){
			return [false];
		}
	
		$(dates).each(function(i, item){
			if (formattedDay === item){
				highlight = true;
				return false;
			}
		});
		
		if(highlight){
			if(this.calendarMode === 0/*popup*/ || this.calendarMode === 1/*inline*/){
				if(!this.hideSelectedDays && this.defaultDateSelected){
					this.addSelectedDates(formattedDay);
				}
				if(this.calendar['period'] === 1/*BY_TIME*/ && this.$timeSlotsDropDownList.find('options').length > 0){
					//loading timeslots when picker is created, so only load
					//if timeslotsdropdown already has items i.e. not loading the first time.
					this.getTimeSlots(formattedDay);
				}
			}
			return [selectable, ' highlighted-day', '..' ]
		}
		
		return [true, formattedDay, ''];
	};
	Booki.Booking.prototype.dateSelected =  function(dateText){
		var dates = this.getSelectedDates()
			, length = dates.length
			, cost
			, costFormatted = '<i class="glyphicon glyphicon-calendar"></i>'
			, returnValue;
		//this.checkSeatAvailability(dateText)
		this.currentDate = dateText;
		this.selectedQuantityElements.length = 0;
		this.clearQuantityElements();
		if(this.calendar['period']){
			this.getTimeSlots(dateText);	
		}else{
			this.updateQuantityElements(this.formatMoment(dateText));
		}
		
		if($.inArray(dateText, dates) !== -1){
			this.selectedDateRemove(dateText);
			this.bookingDaysLimitCheck();
		}else{
			if(this.bookingDaysLimit <= 1 || this.calendar['period'] === 1/*by_time*/){
				this.$selectedDateInput.val(dateText);
				if(this.includeBookingPrice){
					cost = this.getCost(dateText);
					if(cost > 0){
						costFormatted = accounting.formatMoney(cost, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString);
					}
					this.$singleDatePickerAddon.html(costFormatted);
				}
			}
			else if (length < this.bookingDaysLimit){
				dates.push(dateText);
				this.$selectedDateInput.val(dates.join());
				if(!this.hideSelectedDays){
					this.addSelectedDates(dateText);
				}
			}else{
				if(length > 0){
					(function (dates, length, context) {
					  window.setTimeout(function(){
						context.$popupCalendar.datepicker('setDate', dates[length - 1])
					  }, 50);
					})(dates, length, this);
					if(length === this.bookingDaysLimit){
						this.$selectionLimit.removeClass('hide');
					}
				}
			}
		}
		
		returnValue = this.applySeats(dateText, null, true);
		if(returnValue['seatSupport']){
			this.commandButtonStatus(!returnValue['hasSeats']);
		}
		if(!returnValue['seatSupport'] || returnValue['hasSeats']){
			this.minimumDaysCheck();
		}
		this.updateTotal();
		if(!this.defaultDateSelected){
			this.$selectedDateInput.parsley().validate(true);
		}
	};
	Booki.Booking.prototype.disableBackspace =  function(e){
		if(e.keyCode === 8){
			e.preventDefault();
		}
	};
	Booki.Booking.prototype.adjustByAvailability =  function(startDate){
		var context = this
			, usedSlot
			, usedSlotDay
			, diff
			, i
			, j;
			
		this.usedSlots.sort(function(a,b){
			return context.formatMoment(a['day']) - context.formatMoment(b['day']);
		});
		
		for(j = 0; j < this.usedSlots.length;j++){
			usedSlot = this.usedSlots[j];
			usedSlotDay = this.formatMoment(usedSlot['day']);
			diff = startDate.diff(usedSlotDay, 'days');
			if((startDate.isBefore(usedSlotDay) && diff === 1) || startDate.isSame(usedSlotDay)){
				if(usedSlot['slotsExhausted']){
					startDate = startDate.add('days', 1);
				}
			}
		}
		
		return startDate;
	};
	Booki.Booking.prototype.applyDeposit =  function(){
		var context = this
			, dates = this.getSelectedDates()
			, dateText
			, i
			, j
			, cd
			, diff
			, now = moment()
			, length = this.calendarDays.length
			, calendarDay
			, deposit;
		
		dates.sort(function(a,b){
			return context.formatMoment(a) - context.formatMoment(b);
		});
		
		for(i = 0; i < dates.length; i++){
			dateText = dates[i];
			for(j = 0; j < this.calendarDays.length; j++){
				calendarDay = this.calendarDays[j];
				if(dateText === calendarDay.day){
					cd = this.formatMoment(calendarDay.day);
					diff = cd.diff(now, 'days');
					if(diff > calendarDay.minNumDaysDeposit){
						deposit = calendarDay.deposit;
						break;
					}
				}
			}
			if(deposit > 0){
				break;
			}
		}
		
		if(!deposit){
			if(dates.length > 0){
				cd = this.formatMoment(dates[0]);
				diff = cd.diff(now, 'days');
			}
			if(this.calendar.minNumDaysDeposit === 0 || diff > this.calendar.minNumDaysDeposit){
				deposit = this.calendar.deposit;
			}
		}
		
		this.$deposit.val(deposit);
		
		return deposit;
	};
	Booki.Booking.prototype.datePicker =  function(){
		var context = this
			, bookedDate
			, beginDate
			, startDate = this.formatMoment(this.minDate)
			, endDate = this.formatMoment(this.maxDate)
			, returnValue
			, cost
			, costFormatted = '<i class="glyphicon glyphicon-calendar"></i>'
			, args;
		
		this.adjustByAvailability(startDate);
		while((startDate.isBefore(endDate) || startDate.isSame(endDate) ) && this.calendar.weekDaysExcluded.indexOf(startDate.weekday() ) > -1){
			startDate.add('days', 1);
			this.adjustByAvailability(startDate);
		}
		this.calendar.daysExcluded.sort(function(a,b){
			return context.formatMoment(a) - context.formatMoment(b);
		});
		while((startDate.isBefore(endDate) || startDate.isSame(endDate) ) && this.calendar.daysExcluded.indexOf(this.formatDate(startDate)) > -1){
			startDate.add('days', 1);
			this.adjustByAvailability(startDate);
		}
		while(!this.bookingDateAvailable(this.formatDate(startDate)) && startDate.isBefore(endDate)){
			startDate.add('days', 1);
			this.adjustByAvailability(startDate);
		}
		while(!this.containsQuantity(startDate) && startDate.isBefore(endDate)){
			startDate.add('days', 1);
		}
		if(startDate.isAfter(endDate)){
			this.$selectedDateInput.val('');
			this.commandButtonStatus(true);
			this.$daysExhausted.removeClass('hide');
			this.$timeSlotsExhausted.removeClass('hide');
			this.$bookingLimitCounterAlert.addClass('hide');
			this.$popupCalendar.addClass('booki-readonly-field');
			this.$fromPopupCalendar.addClass('booki-readonly-field');
			this.$toPopupCalendar.addClass('booki-readonly-field');
			this.$timeSlotsDropDownList.attr('disabled', true);
			if(this.includeBookingPrice && this.defaultDateSelected){
				this.$singleDatePickerAddon.html(costFormatted);
			}
			return false;
		}
		beginDate = this.formatDate(startDate);
		this.currentDate = beginDate;
		if(this.defaultDateSelected || this.calendarMode === 4/*event*/ ){
			this.$selectedDateInput.val(beginDate);
		}
		this.beforeShowDayDelegate = Booki.createDelegate(this, this.beforeShowDay);
		args = {
			'dateFormat': this.altFormat
			, 'defaultDate': startDate._d
			, 'minDate': startDate._d
			, 'maxDate': endDate._d
			, 'beforeShowDay': this.beforeShowDayDelegate
			, 'hideIfNoPrevNext': true
			, 'showButtonPanel': this.showCalendarButtonPanel
		};
		if(this.calendarFirstDay < 7){
			args['firstDay'] = this.calendarFirstDay;
		}
		if(this.$popupCalendar.length > 0){
			this.dateSelectedDelegate = Booki.createDelegate(this, this.dateSelected);
			args['onSelect'] = this.dateSelectedDelegate;
			args['onClose'] = function(dateText){
				context.$popupCalendar.parsley().validate(true);
			};
			this.$popupCalendar.datepicker(args);
			if(this.includeBookingPrice && this.defaultDateSelected){
				cost = this.getCost(beginDate);
				if(cost > 0){
					costFormatted = accounting.formatMoney(cost, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatStringt)
				}
				this.$singleDatePickerAddon.html(costFormatted);
			}
			if(this.defaultDateSelected){
				this.$popupCalendar.datepicker('setDate', beginDate);
				if(this.calendar['period'] === 1/*BY_TIME*/){
					this.getTimeSlots(beginDate);
				}
			}
			if(this.calendarCssClasses){
				this.$popupCalendar.addClass(this.calendarCssClasses);
			}
		}
		if (this.$fromPopupCalendar.length > 0){
			args['onClose'] = function( dateText ) {
				var dates = context.getSelectedDates();
				if(context.$toPopupCalendar.length > 0){
					context.$toPopupCalendar.datepicker( 'option', 'minDate', dateText );
				}
				//only if we have a range, go.
				if(dates.length > 0){
					context.onRangeClose();
				}
				context.$fromPopupCalendar.parsley().validate(true);
			};
			this.$fromPopupCalendar.datepicker(args);
			if(this.defaultDateSelected){
				this.$fromPopupCalendar.datepicker('setDate', beginDate);
			}
			if(this.calendarCssClasses){
				this.$fromPopupCalendar.addClass(this.calendarCssClasses);
			}
		}
		if(this.$toPopupCalendar.length > 0){
			args['onClose'] = function(dateText) {
				if(context.$fromPopupCalendar.length > 0){
					context.$fromPopupCalendar.datepicker( 'option', 'maxDate', dateText);
				}
				context.onRangeClose();
				context.$toPopupCalendar.parsley().validate(true);
			};
			this.$toPopupCalendar.datepicker(args);
			if(this.defaultDateSelected){
				this.$toPopupCalendar.datepicker('setDate', beginDate);
			}
			if(this.calendarCssClasses){
				this.$toPopupCalendar.addClass(this.calendarCssClasses);
			}
		}
		if(this.defaultDateSelected){
			this.addDatesFromRange();
			if(this.calendar['period'] === 0){
				this.updateQuantityElements(this.formatMoment(this.currentDate));
			}
		}
		
		returnValue = this.applySeats(this.currentDate, null, this.defaultDateSelected);
		if(returnValue['seatSupport']){
			this.commandButtonStatus(!returnValue['hasSeats']);
		}
		if(!returnValue['seatSupport'] || returnValue['hasSeats']){
			this.minimumDaysCheck();
		}
			
		if(this.calendarCssClasses){
			$('#ui-datepicker-div').addClass(this.calendarCssClasses);
		}
		
		return true;
	};
	Booki.Booking.prototype.commandButtonStatus = function(hasSeats){
		if(hasSeats){
			this.$addToCartButton.attr('disabled', hasSeats);
			this.$bookNowPayLaterButton.attr('disabled', hasSeats);
			this.$payNowButton.attr('disabled', hasSeats);
		}else{
			this.$addToCartButton.removeAttr('disabled');
			this.$bookNowPayLaterButton.removeAttr('disabled');
			this.$payNowButton.removeAttr('disabled');
		}
	};
	Booki.Booking.prototype.onRangeClose =  function(){
		this.addDatesFromRange();
	};
	Booki.Booking.prototype.timezoneChanged =  function(e){
		var $target = $(e.currentTarget)
			, selectedValue = $target.find(':selected').val();
			
		this.getTimeSlots(this.currentDate, selectedValue);
		this.timezone = selectedValue;
	};
	Booki.Booking.prototype.getTimeSlots =  function(day, tz){
		var i
			, length = this.calendarDays.length
			, calendarDay
			, timeExcluded
			, result
			, hours
			, minutes
			, hourStartInterval
			, minuteStartInterval
			, oldHours
			, oldMinutes
			, oldHourStartInterval
			, oldMinuteStartInterval
			, model;
		
		this.currentDate = day;
		
		this.$timeSlotsBooked.addClass('hide');
		
		if(typeof(tz) === 'undefined'){
			result = Booki.TimezoneControlState.readState();
			tz = result['selectedZone'];
			if(!this.autoTimezoneDetection){
				tz = this.timezone;
			}
		}
		
		hours = this.calendar['hours'];
		minutes = this.calendar['minutes'];
		hourStartInterval = this.calendar['hourStartInterval'];
		minuteStartInterval = this.calendar['minuteStartInterval'];
		
		timeExcluded = this.calendar['timeExcluded'];
		for(i = 0; i < length; i++){
			calendarDay = this.calendarDays[i];
			if(calendarDay['day'] == day){
				hours = calendarDay['hours'];
				minutes = calendarDay['minutes'];
				hourStartInterval = calendarDay['hourStartInterval'];
				minuteStartInterval = calendarDay['minuteStartInterval'];
				timeExcluded = calendarDay['timeExcluded'];
				break;
			}
		}
		
		if(((hours === oldHours && minutes === oldMinutes) && 
						(hourStartInterval === oldHourStartInterval && 
									minuteStartInterval === oldMinuteStartInterval))&& 
												this.timezone === tz){
			this.renderTimeslots(timeExcluded);
			return;
		}
		
		oldHours = hours;
		oldMinutes = minutes;
		oldHourStartInterval = hourStartInterval;
		oldMinuteStartInterval = minuteStartInterval;
		
		this.timezone = tz;
		this.$progressTimeslots.removeClass('hide');
		this.$progressTimeslots.show();
		model = {
			'hours': hours
			, 'minutes': minutes
			, 'hourStartInterval': hourStartInterval
			, 'minuteStartInterval': minuteStartInterval
			, 'enableSingleHourMinuteFormat': this.calendar.enableSingleHourMinuteFormat
			, 'timezone': this.timezone
		};
		(function(timeExcluded, model,  context){
			$.post(context.ajaxUrl, {
				'model': model
				, 'action': 'booki_getTimeSlots'}
				, function(data) {
					var r = $.parseJSON(data)
						, result = r['result']
						, timezoneInfo = result ? result['timezoneInfo'] : null;
					
					context.timeslotsCache = result ? result['timeslots'] : [];
					if(context.timeslotsCache.length === 0){
						return;
					}
					context.renderTimeslots(timeExcluded);
			});
		})(timeExcluded, model, this);
	};
	Booki.Booking.prototype.renderTimeslots =  function(timeExcluded){
		var i
			, val
			, state
			, option
			, match
			, s
			, cd
			, length = this.timeslotsCache.length
			, now = moment()
			, validSlots = []
			, timeslots
			, returnValue
			, hasSeats
			, valString
			, selectedTimeslot;
		this.$timeSlotsDropDownList.empty();
		this.$timeSlotsDropDownList.prop('disabled', false);
		for(i = 0; i < length;i++){
			hasSeats = true;
			valString = this.timeslotsCache[i]['value'];
			val = valString.split(',');
			state = '';
			if(timeExcluded.indexOf(val[0]) !== -1){
				if(!this.displayBookedTimeSlots){
					continue;
				}
				state = ' disabled class="booki-option-disabled"';
			}
			validSlots.push(this.timeslotsCache[i]);
			s = this.timeslotsCache[i]['rawFrom'].split(':');
			cd = this.formatMoment(this.currentDate).hour(s[0]).minute(s[1]);

			if(cd < now){
				continue;
			}
			timeslots = this.parseTimeslots(valString);
			if(!this.containsQuantity(this.formatMoment(this.currentDate), timeslots)){
				continue;
			}
			
			returnValue = this.applySeats(this.currentDate, timeslots, true);
			if(returnValue['seatSupport'] && !returnValue['hasSeats']){
				hasSeats = false;
			}
			
			if(this.allowAdvanceBooking(cd) && hasSeats){
				option = '<option value="' + val + '"' + state +'>' + this.timeslotsCache[i]['text'] + '</option>';
				this.$timeSlotsDropDownList.append(option);
			}
		}
		if(this.timeslotsEmpty()){
			this.$timeSlotsDropDownList.prop('disabled', true);
			this.$timeSlotsBooked.removeClass('hide');
			this.$bookingLimitCounterAlert.addClass('hide');
			this.commandButtonStatus(true);
		}
		this.ensureTimeSlotSelection();
		this.updateQuantityElementTimeslot();
		this.updateSeatsCounter(this.calendar['period']);
	};
	Booki.Booking.prototype.timeslotsEmpty = function(){
		return (this.$timeSlotsDropDownList.find('option').length === 0 || this.$timeSlotsDropDownList.find('option:not(:disabled)').length === 0);
	}
	Booki.Booking.prototype.ensureTimeSlotSelection =  function(){
		var match
			, selections;
		selections = this.$timeSlotsDropDownList.find('option[selected]');
		if(selections.length > 0){
			return;
		}
		match = this.$timeSlotsDropDownList.find('option').not('[disabled]');
		if(match.length > 0){
			$(match[0]).attr('selected', true);
		}
	};
	
	Booki.Booking.prototype.updateQuantityElementTimeslot =  function(){
		var  selections
			, val;
		selections = this.$timeSlotsDropDownList.find('option:selected');
		if(selections.length > 0){
			val = $(selections[0]).val();
		}
		if(val){
			this.updateQuantityElements(this.formatMoment(this.currentDate), this.parseTimeslots(val));
		}
	};
	
	Booki.Booking.prototype.addDatesFromRange =  function(){
		var dates = []
			, date
			, fromDate = this.formatMoment(this.currentDate)
			, toDate = this.formatMoment(this.currentDate)
			, diff
			, i
			, months
			, result;
		
		if (this.calendarMode === 2/*range*/ || this.calendarMode === 3/*nextDayCheckout*/){
			if(this.$fromPopupCalendar.length > 0){
				fromDate = moment(this.$fromPopupCalendar.datepicker('getDate'));
			}
			if(this.$toPopupCalendar.length > 0){
				toDate = moment(this.$toPopupCalendar.datepicker('getDate'));
			}
		}else if (this.bookingDaysLimit > 1 && this.calendar['period'] === 0/*by_day*/){
			if(this.$toPopupCalendar.length > 0){
				toDate = moment(this.$toPopupCalendar.datepicker('getDate'));
			}
		}else{
			return;
		}
		
		if(!fromDate || !toDate){
			return;
		}
		
		if(this.calendarMode === 3/*next day checkout*/){
			diff = toDate.diff(fromDate, 'days');
		}else{
			diff = toDate.diff(fromDate, 'days') + 1;
		}
		diff = this.validateRangeDiff(fromDate, diff);
		result = this.bookingDaysLimitCheck(diff);
		if(!result){
			dates = this.getSelectedDates();
			date = dates[dates.length - 1];
			this.$toPopupCalendar.datepicker('setDate', date);
			this.$toPopupCalendar.datepicker( 'option', 'minDate', date);
			return;
		}
		
		this.$selectedDatesContainer.empty();
		
		if(diff === 0 && this.calendarMode === 3/*next day checkout*/){
			date = fromDate.format(this.dateFormatString);
			if(this.isValidDay(date)){
				dates.push(date);
			}
		}else{
			//add also the current selection
			fromDate = fromDate.subtract(1, 'day');
			for(i = 0; i < diff; i++){
				fromDate = fromDate.add(1, 'day');
				date = fromDate.format(this.dateFormatString);
				if(this.calendar.weekDaysExcluded.indexOf(fromDate.weekday()) > -1){
					continue;
				}
				if(this.calendar.daysExcluded.indexOf(date) > -1){
					continue;
				}
				if(this.exhaustedSlots.indexOf(date) > -1){
					continue;
				}
				if(this.isValidDay(date)){
					dates.push(date);
				}
			}
		}
		this.$selectedDateInput.val(dates.join());
		if(!this.hideSelectedDays){
			for(i = 0; i < dates.length; i++){
				this.addSelectedDates(dates[i]);
			}
		}
		this.updateQuantityElements(fromDate);
		this.minimumDaysCheck();
		this.updateTotal();
	};
	Booki.Booking.prototype.validateRangeDiff =  function(fromDate, diff){
		var i
			, date
			, result = 0;
		fromDate = fromDate.clone().subtract(1, 'day');
		for(i = 0; i < diff; i++){
			fromDate = fromDate.add(1, 'day');
			date = fromDate.format(this.dateFormatString);
			if(this.calendar.weekDaysExcluded.indexOf(fromDate.weekday()) > -1){
				continue;
			}
			if(this.calendar.daysExcluded.indexOf(date) > -1){
				continue;
			}
			if(this.exhaustedSlots.indexOf(date) > -1){
				continue;
			}
			++result;
		}
		return result;
	};
	Booki.Booking.prototype.cascadingListItemSelected =  function(e){
		var target = e.currentTarget
			, $optionItem = $('option:selected', target)
			, itemId = parseInt($optionItem.val(), 10)
			, parentId = $optionItem.data('bookiParent')
			, listId = $optionItem.data('bookiList')
			, $parentList = this.$root.find('booki_cascadingdropdown_' + parentId)
			, placeHolder = $(target).data('bookiPlaceholder');
		
		this.updateTotal();
		
		if(isNaN(itemId)){
			this.clearCascadingList(itemId, target);
			return;
		}
		if(typeof(parentId) !== 'number' || $parentList.length > 0){
			return;
		}
		
		if(parentId === -1){
			return;
		}
		
		this.$progressCascades.removeClass('hide');
		this.$progressCascades.show();
		
		(function(model, target, parentId, listId, itemId, placeHolder, context){
			$.post(context.ajaxUrl, {
				'model': model 
				, 'action': 'booki_readCascadingItemsByListId'}
				, function(data) {
					var result = $.parseJSON(data);
					context.createCascadingList(result, target, parentId, listId, itemId, placeHolder);
			});
		})({ 'id': parentId }, target, parentId, listId, itemId, placeHolder, this);
	};
	Booki.Booking.prototype.createCascadingList =  function(cascadingList, target, parentId, listId, itemId, placeHolder){
		var i
			, item
			, cascadingItems = cascadingList['cascadingItems']
			, name = 'booki_cascadingdropdown_' + cascadingList['id']
			, $option
			, formGroup = this.getFormGroup(name, cascadingList['label'])
			, containerId = '#' + name + '_container'
			, formattedValue
			, $list
			, $selectList = $('<select></select>').attr({
				'id': name
				, 'name': name
				, 'data-booki-list': listId
				, 'data-booki-placeholder': placeHolder
			}).addClass('form-control booki-cascading-list booki_parsley_validated');
		if(cascadingList['isRequired']){
			$selectList.attr('data-parsley-trigger', 'change');
			$selectList.attr('data-parsley-required', true);
		}
		$option = $('<option></option>').attr('value', '').html(this.defaultCascadingListSelectionLabel);
		$selectList.append($option);
		if(!this.$selectLists){
			this.$selectLists = [];
		}
		for(i = 0; i < cascadingItems.length;i++){
			item = cascadingItems[i];
			formattedValue = item['value'];
			if(item['cost'] > 0 && item['parentId'] === -1){
				formattedValue += '&nbsp;&nbsp;' + accounting.formatMoney(item['cost'], this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString)
			}
			$option = $('<option></option>').attr({
				'value': item['id']
				, 'data-booki-parent': item['parentId']
				, 'data-booki-cost': item['cost']
				, 'data-booki-original-value': item['value']
				, 'data-booki-list': listId
			}).html(formattedValue);
			$selectList.append($option);
		}
		$(placeHolder).append($('<div></div>').attr({'id': 'booki_' + parentId + '_' + listId + '_' + itemId}).append(formGroup));
		//remove previous list if from same level.
		this.clearCascadingList(itemId, target);
		//append new list
		$(containerId).append($selectList);
		
		this.cascadingListItemSelectedDelegate = Booki.createDelegate(this, this.cascadingListItemSelected);
		$selectList.on('change', this.cascadingListItemSelectedDelegate);
		this.$selectLists.push($selectList);
		this.updateTotal();
	};
	Booki.Booking.prototype.clearCascadingList = function(itemId, target){
		var i
			, $list
			, $options = $('option', target)
			, $option
			, parentId
			, listId
			, value
			, $item;
		for(i = 0; i < $options.length; i++){
			$option = $($options[i]);
			value = parseInt($option.val(), 10);
			parentId = $option.data('bookiParent');
			listId = $option.data('bookiList');
			if(value === itemId){
				continue;
			}
			$item = $('#booki_' + parentId + '_' + listId + '_' + value);
			if($item.length > 0){
				this.clearCascadingList(value, $item[0]);
				for(i = 0; i < this.$selectLists.length; i++){
					$list = this.$selectLists[i];
					if ($list.is('[id="booki_cascadingdropdown_' + parentId + '"]')) {
						this.$selectLists.splice(i, 1);
						window.console.log('removed:' + i);
						break;
					}
				}
				$item.remove();
			}
		}
	};
	Booki.Booking.prototype.attachQuantityElementDelegates = function($quantityElement, quantityElement){
		var index = this.getQuantityElementIndex(quantityElement)
			, quantityElementChangedDelegate;
		if(!this.$quantityElements){
			this.$quantityElements = [];
		}
		this.$quantityElements.push($quantityElement);
		quantityElementChangedDelegate = Booki.createDelegate(this, this.quantityElementChanged);
		$quantityElement.change(quantityElementChangedDelegate);
		if(quantityElement['isRequired'] && quantityElement['displayMode'] === 0/*DROPDOWNLIST*/ && $.trim($quantityElement.val()) !== ''){
			this.selectedQuantityElements.push(index);
		}
	};
	Booki.Booking.prototype.detachQuantityElementDelegates = function($quantityElement, quantityElement){
		var index = this.getQuantityElementIndex(quantityElement)
			, i
			, j = null;
		if(index !== -1){
			for(i = 0; i < this.selectedQuantityElements.length;i++){
				if(this.selectedQuantityElements[i] === index){
					this.selectedQuantityElements.splice(i, 1);
					break;
				}
			}
		}
		for(i = 0; i < this.$quantityElements.length; i++){
			if(this.$quantityElements[i] == $quantityElement){
				j = i;
				break;
			}
		}
		if(j !== null){
			this.$quantityElements.splice(j, 1);
		}
		$quantityElement.off();
		this.$root.find('.booki_quantity_element_exhausted_alert_' + quantityElement['id']).remove();
		this.$root.find('.form-group.booki_quantity_element_' + quantityElement['id']).remove();
	};
	Booki.Booking.prototype.getQuantityElementIndex = function(quantityElement){
		var i
			, length = this.quantityElements.length
			, qe;
		for(i = 0; i < length; i++){
			qe = this.quantityElements[i];
			if(qe['id'] === quantityElement['id']){
				return i;
			}
		}
		return -1;
	};
	Booki.Booking.prototype.calendarDayHasQuantityElement = function(quantityElement, currentDate){
		var i
			, calendarDay;
		if(!currentDate){
			currentDate = this.currentDate;
		}
		for(i = 0; i < this.calendarDays.length;i++){
			calendarDay = this.calendarDays[i];
			if(quantityElement['calendarDayIdList'].indexOf(calendarDay['id']) === -1){
				continue;
			}
			if(calendarDay['day'] == currentDate){
				return true;
			}
		}
		return false;
	};
	Booki.Booking.prototype.getQuantityElementsTotalCost = function(){
		var i
			, j
			, quantityElement
			, quantityElementItem
			, $quantityElement
			, displayMode
			, elementType
			, quantity
			, type
			, cost
			, totalCost = 0
			, dates = this.getSelectedDates()
			, $timeSelections = this.$timeSlotsDropDownList.find('option:selected');
		for(i = 0; i < this.selectedQuantityElements.length; i++){
			quantityElement = this.quantityElements[this.selectedQuantityElements[i]];
			displayMode = quantityElement['displayMode'];
			elementType = displayMode === 0 ? 'select' : 'input';
			$quantityElement = this.$root.find(elementType + '[name="booki_quantity_element_' + quantityElement['id'] + '"]');
			quantity = parseInt($.trim($quantityElement.val()), 10);
			type = $quantityElement[0].type;
			if(displayMode === 1/*textbox*/){
				if(quantity > quantityElement['quantity'] || quantity <= 0){
					continue;
				}
			}
			cost = quantityElement['cost'];
			if(type === 'text'){
				quantity -= 1;
			}
			for(j = 0; j < quantityElement['quantityElementItems'].length; j++){
				quantityElementItem = quantityElement['quantityElementItems'][j];
				if(quantityElementItem['quantityIndex'] === quantity){
					cost = quantityElementItem['cost'];
					break;
				}
			}
			totalCost += cost;
		}
		if(dates.length > 0){
			//multiple days selected. apply per day
			totalCost = totalCost * dates.length;
		}else if($timeSelections.length > 0){
			totalCost = totalCost * $timeSelections.length;
		}
		return totalCost;
	};
	Booki.Booking.prototype.quantityElementChanged = function(e){
		var $target = $(e.currentTarget)
			, value = $.trim($target.val())
			, quantity = parseInt(value, 10)
			, quantityElementId = parseInt($target.attr('data-booki-quantity-element-id'), 10)
			, type = $target[0].type
			, i;
		if(type === 'text'){
			quantity -= 1;
		}
		for(i = 0; i < this.quantityElements.length; i++){
			if(this.quantityElements[i]['id'] === quantityElementId){
				if(value === '' || isNaN(quantity)){
					this.selectedQuantityElements.splice(this.selectedQuantityElements.indexOf(i), 1);
				}
				else if(this.selectedQuantityElements.indexOf(i) === -1){
					this.selectedQuantityElements.push(i);
				}
				break;
			}
		}
		this.updateTotal();
	};
	Booki.Booking.prototype.containsQuantity = function(currentDate, currentTime){
		var i
			, quantityElement
			, result;
		if(!this.calendar['availabilityByQuantityElement'] || this.quantityElements.length === 0){
			return true;
		}
		
		quantityElement = this.getAvailableQuantityElement(true, this.quantityElementsReserved, currentDate, currentTime);
		if(!quantityElement){
			for(i = 0; i < this.quantityElements.length; i++){
				result = this.syncQuantityElementsFromCart(this.shallowCloneQuantity(this.quantityElements[i]), currentDate, currentTime);
				if(result['quantity'] <= 0){
					quantityElement = result;
					break;
				}
			}
		}
		if(quantityElement){
			return quantityElement['quantity'] > 0;
		}
		
		return true;
	};
	Booki.Booking.prototype.getAvailableQuantityElement = function(isReservedQuantity, quantityElements, currentDate, currentTime){
		var flag
			, i
			, result = null
			, quantityElement;
		for(i = 0; i < quantityElements.length; i++){
			quantityElement = quantityElements[i];
			if(quantityElement['bookingMode'] === 3/*FIXED*/){
				continue;
			}
			
			flag = this.formatMoment(quantityElement['bookingDate']).isSame(currentDate) || this.calendarDayHasQuantityElement(quantityElement, this.formatDate(currentDate));
			
			if(quantityElement['bookingMode'] !== 0/*PER_ENTIRE_BOOKING_PERIOD*/ && !flag){
				continue;
			}
			if(quantityElement['bookingMode'] === 1/*PER_DAY*/){
				result = quantityElement;
				break;
			}
			if(quantityElement['bookingMode'] === 2/*PER_TIMESLOT*/ && currentTime){
				if(!this.isSameTime(currentTime, quantityElement)){
					continue;
				}
				result = quantityElement;
				break;
			}
		}
		if(result){
			result = this.syncQuantityElementsFromCart(this.shallowCloneQuantity(result), currentDate, currentTime);
		}
		return result;
	};
	Booki.Booking.prototype.updateQuantityElements = function(currentDate, currentTime){
		var i
			, j
			, quantityElement
			, bookedDate
			, updatedElements = [];
		for(i = 0; i < this.quantityElementsReserved.length; i++){
			quantityElement = this.quantityElementsReserved[i];
			if(quantityElement['bookingMode'] === 3/*FIXED*/){
				continue;
			}
			if(quantityElement['bookingMode'] !== 0/*PER_ENTIRE_BOOKING_PERIOD*/){
				bookedDate = this.formatMoment(quantityElement['bookingDate']);
				if(!bookedDate.isSame(currentDate)){
					continue;
				}
			}
			if(quantityElement['bookingMode'] === 2){
				if(!this.isSameTime(currentTime, quantityElement)){
					continue;
				}
			}
			quantityElement = this.syncQuantityElementsFromCart(this.shallowCloneQuantity(quantityElement), currentDate, currentTime);
			this.updateQuantityElementData(quantityElement);
			updatedElements.push(quantityElement['id']);
		}
		for(i = 0; i < this.quantityElements.length; i++){
			quantityElement = this.quantityElements[i];
			if(updatedElements.indexOf(quantityElement['id']) === -1){
				quantityElement = this.syncQuantityElementsFromCart(this.shallowCloneQuantity(quantityElement), currentDate, currentTime);	
				this.updateQuantityElementData(quantityElement, i);
			}
		}
		updatedElements.length = 0;
	};
	Booki.Booking.prototype.getQuantityElementItems = function(id){
		var i
			, quantityElement;
		for(i = 0; i < this.quantityElements.length; i++){
			quantityElement = this.quantityElements[i];
			if(quantityElement['id'] === id){
				return quantityElement['quantityElementItems'];
			}
		}
		return null;
	};
	Booki.Booking.prototype.isSameTime = function(a, b){
		if(!a || !b){
			return false;
		}
		return ((a['hourStart'] == b['hourStart'] && a['minuteStart'] == b['minuteStart']) &&
					(a['hourEnd'] == b['hourEnd'] && a['minuteEnd'] == b['minuteEnd']));
	};
	Booki.Booking.prototype.clearQuantityElements = function(removeAll){
		var i
			, length = this.quantityElements.length
			, quantityElement
			, $quantityElement
			, elementType;
		if(!removeAll && this.quantityElementMode() === 0/*quantityElementMode.ADD*/){
			return;
		}
		for(i = 0; i < length; i++){
			quantityElement = this.quantityElements[i];
			elementType = quantityElement['displayMode'] === 0 ? 'select' : 'input';
			$quantityElement = this.$root.find(elementType + '[name="booki_quantity_element_' + quantityElement['id'] + '"]');
			if($quantityElement.length > 0){
				this.detachQuantityElementDelegates($quantityElement, quantityElement);
			}
		}
	};
	Booki.Booking.prototype.quantityElementMode = function(){
		var i
			, length = this.quantityElements.length
			, quantityElement
			, bookedDate;
		if(this.calendar['quantityElementMode'] === 0/*quantityElementMode.ADD*/){
			return 0/*quantityElementMode.ADD*/;
		}
		for(i = 0; i < length; i++){
			quantityElement = this.quantityElements[i];
			if(typeof(quantityElement['calendarDayId']) !== 'undefined' &&  quantityElement['calendarDayId'] !== null){
				if(this.calendarDayHasQuantityElement(quantityElement)){
					return 1/*quantityElementMode.REPLACE*/;
				}
			}
		}
		return 0/*quantityElementMode.ADD*/;
	};
	Booki.Booking.prototype.shallowCloneQuantity = function(quantityElement){
		var i
			, copy = {};
		for (i in quantityElement) {
			if (quantityElement.hasOwnProperty(i)){
				copy[i] = quantityElement[i];
			}
		}
		return copy;
	};
	Booki.Booking.prototype.syncQuantityElementsFromCart = function(quantityElement, currentDate, currentTime){
		var i
			, cartQuantityElement
			, quantityCount
			, dt1
			, dt2
			, dates = this.getSelectedDates()
			, $timeSelections = this.$timeSlotsDropDownList.find('option:selected');
			
		for(i = 0; i < this.quantityElementsFromCart.length;i++){
			cartQuantityElement = this.quantityElementsFromCart[i];
			if(quantityElement.id === cartQuantityElement.id){
				if(quantityElement['bookingMode'] === 3/*FIXED*/){
					continue;
				}
				if(quantityElement['bookingMode'] !== 0/*PER_ENTIRE_BOOKING_PERIOD*/){
					dt1 = this.formatMoment(currentDate);
					dt2 = this.formatMoment(cartQuantityElement['bookingDate']);
					if(!dt1.isSame(dt2)){
						continue;
					}
				}
				if(quantityElement['bookingMode'] === 2){
					if(!this.isSameTime(currentTime, cartQuantityElement)){
						continue;
					}
				}
				quantityCount = cartQuantityElement['quantityCount'];
				if(this.timeSelector !== 0/*DROPDOWNLIST*/ && this.calendar['period'] === 1/*BY_TIME*/){
					quantityCount = quantityCount * $timeSelections.length;
				}
				quantityElement['quantity'] -= quantityCount;
			}
		}
		return quantityElement;
	};
	Booki.Booking.prototype.updateQuantityElementData = function(quantityElement){
		var i
			, j
			, displayMode
			, elementType
			, $quantityElement
			, $quantity
			, $alert
			, quantityElementMode = this.quantityElementMode()
			, quantityElementItems = quantityElement['quantityElementItems'] ? quantityElement['quantityElementItems'] : this.getQuantityElementItems(quantityElement['id'])
			, cost
			, text;
		displayMode = quantityElement['displayMode'];
		elementType = displayMode === 0 ? 'select' : 'input';
		$quantityElement = this.$root.find(elementType + '[name="booki_quantity_element_' + quantityElement['id'] + '"]');
		if(typeof(quantityElement['calendarDayId']) !== 'undefined' &&  quantityElement['calendarDayId'] !== null){
			if($quantityElement.length === 0){
				if(this.calendarDayHasQuantityElement(quantityElement)){
					$quantityElement = this.createQuantityElement(displayMode, elementType, quantityElement);
				}else{
					return;
				}
			}else if($quantityElement.length > 0 && !this.calendarDayHasQuantityElement(quantityElement)){
				this.detachQuantityElementDelegates($quantityElement, quantityElement);
				return;
			}
		}else if(quantityElementMode === 1){
			return;
		}
		
		if($quantityElement.length === 0){
			$quantityElement = this.createQuantityElement(displayMode, elementType, quantityElement);
		}
		
		if(displayMode === 0){
			$quantityElement.empty();
			$("<option />", {
				'val': '',
				'text': '--'
			}).appendTo($quantityElement);
			for(i = 0; i < quantityElement['quantity']; i++){
				text = i + 1;
				if(this.calendar['includePriceInQuantityElement'] && quantityElement['cost'] > 0){
					cost = quantityElement['cost'];
					if(quantityElementItems && quantityElementItems.length > 0){
						for(j = 0; j < quantityElementItems.length; j++){
							if(quantityElementItems[j]['quantityIndex'] === i){
								cost = quantityElementItems[j]['cost'];
								break;
							}
						}
					}
					text += ' - (' + accounting.formatMoney(cost, this.currencySymbol, this.precision, this.thousandsSep, this.decimalPoint, this.currencyFormatString) + ')';
				}
				 $("<option />", {
					'val': i,
					'text': text
				}).appendTo($quantityElement);
			}
		}else{
			$quantity = this.$root.find('.input-group-addon.booki_quantity_element_' + quantityElement['id']);
			$quantity.html(quantityElement['quantity']);
			$quantityElement.attr('data-parsley-max', quantityElement['quantity']);
		}
		$quantityElement.removeAttr('disabled');
		if(quantityElement['isRequired'] && quantityElement['quantity'] > 0){
			if(displayMode === 1){
				$quantityElement.attr('data-parsley-min', 1);
			}
			$quantityElement.attr('data-parsley-required', true);
		}else{
			$quantityElement.removeAttr('data-parsley-required');
			$quantityElement.removeAttr('data-parsley-min');
		}
		$alert = this.$root.find('.booki_quantity_element_exhausted_alert_' + quantityElement['id']);
		if(quantityElement['quantity'] <= 0 || this.calendar['period'] === 1/*BY_TIME*/ && this.timeslotsEmpty()){
			$quantityElement.attr('disabled', true);
			$alert.removeClass('hide');
		}else{
			$alert.addClass('hide');
		}
	};
	Booki.Booking.prototype.createQuantityElement = function(displayMode, elementType, quantityElement){
		var $quantityElement;
		if(displayMode === 0){
			this.$dynamicQuantityElementPlaceholder.append(this.getQuantityElementSelectListTemplate(quantityElement));
		}else{
			this.$dynamicQuantityElementPlaceholder.append(this.getQuantityElementTextboxTemplate(quantityElement));
		}
		$quantityElement = this.$root.find(elementType + '[name="booki_quantity_element_' + quantityElement['id'] + '"]');
		this.attachQuantityElementDelegates($quantityElement, quantityElement);
		return $quantityElement;
	};
	Booki.Booking.prototype.getQuantityElementSelectListTemplate = function(quantityElement){
		var template = '<div class="form-group booki_quantity_element_exhausted_alert_' + quantityElement['id'] + ' hide">'
			+'<div class="col-lg-8 col-lg-offset-4">'
				+'<div class="bg-warning booki-bg-box">' + this.quantityElementExhaustedAlertMessage.replace('%s', quantityElement['name']) + '</div>'
			+'</div>'
		+'</div>'
		+'<div class="form-group booki_quantity_element_' + quantityElement['id'] + '">'
			+'<label class="col-lg-4 control-label" for="booki_quantity_element_' + quantityElement['id'] + '">'
				+ quantityElement['name']
			+'</label>'
			+'<div class="col-lg-8">'
				+'<select class="form-control"'
					+' name="booki_quantity_element_' + quantityElement['id'] + '"'
					+' data-booki-quantity-element-id="' + quantityElement['id'] + '">'
				+'</select>'
			+'</div>'
		+'</div>';
		return template;
	}
	Booki.Booking.prototype.getQuantityElementTextboxTemplate = function(quantityElement){
		var template = '<div class="form-group booki_quantity_element_exhausted_alert_' + quantityElement['id'] + ' hide">'
			+'<div class="col-lg-8 col-lg-offset-4">'
				+'<div class="bg-warning booki-bg-box">' + this.quantityElementExhaustedAlertMessage.replace('%s', quantityElement['name']) + '</div>'
			+'</div>'
		+'</div>'
		+'<div class="form-group booki_quantity_element_' + quantityElement['id'] + '">'
			+'<label class="col-lg-4 control-label" for="booki_quantity_element_' + quantityElement['id'] + '">'
				+ quantityElement['name']
			+'</label>'
			+'<div class="col-lg-8">'
				+'<div class="input-group">'
					+'<input type="text" class="form-control"'
						+' name="booki_quantity_element_' + quantityElement['id'] + '"'
						+' data-booki-quantity-element-id="' + quantityElement['id'] + '"'
						+' placeholder="0"'
						+' data-parsley-trigger="change"'
						+' data-parsley-errors-container=".booki-quantityelement-error-' + quantityElement['id']  + '-' + quantityElement['projectId'] + '"'
						+' data-parsley-type="digits" />'
						+'<div class="clearfix"></div>'
						+'<div class="booki-quantityelement-error-' + quantityElement['id']  + '-' + quantityElement['projectId'] + '"></div>'
				+'</div>'
			+'</div>'
		+'</div>';
		return template;
	};
	Booki.Booking.prototype.getFormGroup =  function(name, label){
		var result = '<div class="form-group">';
			result +=	'<label class="col-lg-4 control-label" for="' + name + '">';
			result +=	label;
			result +=	'</label>';
			result +=	'<div class="col-lg-8">';
			result += 		'<div id="' + name + '_container"></div>';
			result += 	'</div>';
			result += 	'</div>';
		return result;
	};
	Booki.Booking.prototype.parseDouble =  function(value){
		return parseFloat((''+value).replace(/,/g,''));
	};
	Booki.Booking.prototype.checkSeatAvailability = function(selectedDate, hourStart, minuteStart, hourEnd, minuteEnd){
		var model = {
				'bookingDate': selectedDate
				, 'projectId': this.projectId
				, 'hourStart': hourStart
				, 'minuteStart': minuteStart
				, 'hourEnd': hourEnd
				, 'minuteEnd': minuteEnd
			};
		(function(context, model){
			$.post(context.ajaxUrl, {
				'model': model
				, 'action': 'booki_seatAvailable'}
				, function(data) {
					var r = $.parseJSON(data)
						, result = r['result'];
					window.console.log(result);
			});
		})(this, model);
	};
	Booki.Booking.prototype.destroy = function(){
		var $elems, i;
		if(this.disableBackspaceDelegate){
			this.$popupCalendar.off('keydown');
			this.$fromPopupCalendar.off('keydown');
			this.$toPopupCalendar.off('keydown');
			delete this.disableBackspaceDelegate;
		}
		if(this.timezoneChangedDelegate){
			this.$timezoneDropDownList.off();
			delete this.timezoneChangedDelegate;
		}
		if(this.updateTotalDelegate){
			this.$optionals.off();
			delete this.updateTotalDelegate;
		}
		if(this.timeSlotsChangedDelegate){
			this.$timeSlotsDropDownList.off();
			delete this.timeSlotsChangedDelegate;
		}
		if(this.selectedDateRemoveClickDelegate){
			$elems = this.$selectedDatesContainer.find('li');
			$elems.each(function(i, elem){
				$(elem).off();
			});
			delete this.selectedDateRemoveClickDelegate;
		}
		if(this.beforeShowDayDelegate){
			delete this.beforeShowDayDelegate;
		}
		if(this.dateSelectedDelegate){
			delete this.dateSelectedDelegate;
		}
		if(this.cascadingListItemSelectedDelegate){
			this.$cascadingLists.off();
			if(this.$selectLists){
				for(i = 0; i < this.$selectLists.length;i++){
					this.$selectLists[i].off();
				}
				this.$selectLists.length = 0;
			}
			delete this.cascadingListItemSelectedDelegate;
		}
		if(this.$quantityElements){
			for(i = 0; i < this.$quantityElements.length;i++){
				this.$quantityElements[i].off();
			}
		}
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	}
	
})(window['jQuery'], window['moment'], window['accounting'], window['Booki']);
(function($, moment, Booki){
	Booki.BookingWizard = function(options){
		var context = this;
		this.init(options);
		window['ParsleyConfig'] = {
			excluded: 'input[type=button], input[type=submit], input[type=reset]'
			, inputs: 'input, textarea, select, input[type=hidden], :hidden'
		};
		this.destroyDelegate = Booki.createDelegate(this, this.destroy);
		$(window).unload(this.destroyDelegate);
	};
	Booki.BookingWizard.prototype.init = function(options){
		var context = this
			, settings = $.extend({}, options)
			, elem = settings['elem'];
			
		this.$root = $(elem);
		this.projectId = settings['projectId'];
		this.$bookingButton = this.$root.find(settings['bookingButton']);
		this.$nextButton = this.$root.find(settings['nextButton']);
		this.$backButton = this.$root.find(settings['backButton']);
		this.$step1 = this.$root.find(settings['step1']);
		this.$tabs = this.$root.find(settings['tabs']);
		this.$errorContainer = this.$root.find(settings['errorContainer']);
		
		if(this.$tabs.length < 2){
			if(this.$step1){
				this.$step1.tab('show');
			}
			this.$nextButton.addClass('hide');
			this.$backButton.addClass('hide');
			return;
		}
		
		this.wizardButtonClickDelegate = Booki.createDelegate(this, this.wizardButtonClick);
		this.$nextButton.on('click', this.wizardButtonClickDelegate);
		this.$backButton.on('click', this.wizardButtonClickDelegate);
		
		this.toggleButtons(0);
		
		this.tabsClickDelegate = Booki.createDelegate(this, this.tabsClick);
		this.$tabs.on('click', this.tabsClickDelegate);
	}
	Booki.BookingWizard.prototype.validate = function(){
		if(!this.isValid()){
			this.$errorContainer.removeClass('hide');
		}else{
			this.$errorContainer.addClass('hide');
		}
	}
	Booki.BookingWizard.prototype.tabsClick = function (e) {
		var $target = $(e.currentTarget);
		e.preventDefault();
		this.validate();
		this.toggleButtons($target.data('step'));
	}
	Booki.BookingWizard.prototype.toggleButtons = function (selectedStep){
		if(parseInt(selectedStep, 10) === 0){
			this.$bookingButton.addClass('hide');
			this.$nextButton.removeClass('hide');
			this.$backButton.addClass('hide');
		}else{
			this.$bookingButton.removeClass('hide');
			this.$nextButton.addClass('hide');
			this.$backButton.removeClass('hide');
		}
		$('.booki' + this.projectId + '.nav.nav-tabs a[data-step="' + selectedStep + '"]').tab('show');
	}
	Booki.BookingWizard.prototype.wizardButtonClick = function (e){
		var $target = $(e.currentTarget);
		this.validate();
		this.toggleButtons($target.data('step'));
	};
	Booki.BookingWizard.prototype.isValid = function(){
		var isValid = true
			, $validators = this.$root.find('.booki_parsley_validated');
		$validators.each(function(){
			var $elem = $(this),
				result;
				window.console.log($elem[0].name + ':' + (!$elem.is(':visible') && $elem[0].type !== 'hidden'));
			if ((!$elem.is(':visible') && $elem[0].type !== 'hidden') || $elem.is(':disabled')){
				return true;
			}
			result = $elem.parsley().validate(true);
			if(result !== null && (typeof(result) === 'object' && result.length > 0)){
				isValid = false;
			}
		});
		return isValid;
	};
	Booki.BookingWizard.prototype.destroy = function( ) {
		if(this.wizardButtonClickDelegate){
			this.$nextButton.off();
			this.$backButton.off();
			delete this.wizardButtonClickDelegate;
		}
		if(this.tabsClickDelegate){
			this.$tabs.off();
			delete this.tabsClickDelegate;
		}
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
})(window['jQuery'], window['moment'], window['Booki']);
(function($, Booki){
	Booki.ModalPopup = function(options){
		this.init(options);
		this.destroyDelegate = Booki.createDelegate(this, this.destroy);
		$(window).unload(this.destroyDelegate);
	};
	Booki.ModalPopup.prototype.init = function(options){
		var settings = $.extend({}, options)
			, elem = settings['elem'];
			this.$root = $(elem);
			this.$confirmButton = this.$root.find('.booki-confirm');
			this.onModalShowDelegate = Booki.createDelegate(this, this.onModalShow);
			this.$root.on('show.bs.modal', this.onModalShowDelegate);
	};
	Booki.ModalPopup.prototype.onModalShow = function(e){
		var $target = $(e.relatedTarget)
			, id = $target.data('bookiId');
		this.$confirmButton.attr('value', id);
	};
	Booki.ModalPopup.prototype.destroy = function(){
		if(this.onModalShowDelegate){
			this.$root.off();
			delete this.onModalShowDelegate;
		}
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
})(window['jQuery'], window['Booki']);
(function($, moment, Booki){
	Booki.SearchFilter = function(options){
		this.init(options);
		this.destroyDelegate = Booki.createDelegate(this, this.destroy);
		$(window).unload(this.destroyDelegate);
	};
	Booki.SearchFilter.prototype.init = function(options){
		var settings = $.extend({}, options)
			, elem = settings['elem']
			, argsFrom
			, argsTo;
			this.$root = $(elem);

		this.fromDefaultDate = moment(settings['fromDefaultDate']);
		this.toDefaultDate = moment(settings['toDefaultDate']);
		this.$fromDate = this.$root.find(settings['fromDateElem']);
		this.$toDate = this.$root.find(settings['toDateElem']);
		this.calendarFirstDay = settings['calendarFirstDay'];
		this.showCalendarButtonPanel = settings['showCalendarButtonPanel'];
		this.calendarCssClasses = settings['calendarCssClasses'];
		this.altFormat = settings['altFormat'];
		this.dateFormat = settings['dateFormat'];
		
		argsFrom = {
			'dateFormat': this.altFormat
			, 'changeMonth': true
			, 'changeYear': true
			, 'minDate': 0 /*0 = today*/
			, 'showButtonPanel': this.showCalendarButtonPanel
		};
		
		if(this.fromDefaultDate){
			argsFrom['defaultDate'] = this.fromDefaultDate._d;
		}
		argsTo = {
			'dateFormat': this.altFormat
			, 'changeMonth': true
			, 'changeYear': true
			, 'minDate': 0 /*0 = today*/
			, 'showButtonPanel': this.showCalendarButtonPanel
		}
		if(this.toDefaultDate){
			argsTo['defaultDate'] = this.toDefaultDate._d;
		}
		if(this.calendarFirstDay < 7){
			argsTo['firstDay'] = this.calendarFirstDay;
			argsFrom['firstDay'] = this.calendarFirstDay;
		}
		if(this.$fromDate.length > 0){
			this.$fromDate.datepicker(argsFrom);
			if(this.calendarCssClasses){
				this.$fromDate.addClass(this.calendarCssClasses);
			}
		}
		if(this.$toDate.length > 0){
			this.$toDate.datepicker(argsTo);
			if(this.calendarCssClasses){
				this.$toDate.addClass(this.calendarCssClasses);
			}
		}
		if(this.calendarCssClasses){
			$('#ui-datepicker-div').addClass(this.calendarCssClasses);
		}   
	};
	Booki.SearchFilter.prototype.destroy = function(){
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
})(window['jQuery'], window['moment'], window['Booki']);
(function($, jstz, Booki){
	Booki.TimezoneControl = function(options){
		this.init(options);
		this.destroyDelegate = Booki.createDelegate(this, this.destroy);
		$(window).unload(this.destroyDelegate);
	};
	Booki.TimezoneControl.prototype.init = function(options){
		var settings = $.extend({}, options)
			, elem = settings['elem']
			, context = this
			, result;
		this.$root = $(elem);
		this.cookieName = 'BOOKITIMEZONE';
		this.flag = false;
		this.$regionSelect = this.$root.find(settings['region']);
		this.$timezoneContainer = this.$root.find(settings['timezone']);
		this.$timezoneSelect = this.$timezoneContainer.find('select[name="timezone"]');
		this.$autoDetect = this.$root.find('input[name="autodetect"]');
		this.$headerCaption = this.$root.find(settings['headerCaption']);
		this.$loadOnStart = $(settings['loadOnStart']);
		this.$timezoneManualSelection = $(settings['timezoneManualSelection']);
		this.$collapseTimezone = this.$root.find('.collapseTimezone');
		this.$progressBar = this.$root.find('.progress');
		this.ajaxUrl = settings['ajaxurl'];
		
		this.timezoneSelectChangeDelegate = Booki.createDelegate(this, this.timezoneSelectChange);
		this.$timezoneSelect.on('change', this.timezoneSelectChangeDelegate);
		
		this.regionSelectChangeDelegate = Booki.createDelegate(this, this.regionSelectChange);
		this.$regionSelect.on('change', this.regionSelectChangeDelegate);

		if(this.$autoDetect.length > 0){
			this.autoDetectClickDelegate = Booki.createDelegate(this, this.autoDetectClick);
			this.$autoDetect.on('click', this.autoDetectClickDelegate);
		}
		result = this.getDefaultTimezone();
		this.$autoDetect.prop('checked', result['autoRun']);
		if(this.$timezoneManualSelection.length > 0){
			result['selectedZone'] = this.$timezoneManualSelection.val();
			this.$root.find('.autodetect').addClass('hide');
		}
		
		this.saveState(result['autoRun'], result['selectedZone']);
		
		if(this.$headerCaption.length > 0){
			this.$headerCaption[0].title = '';
			this.$headerCaption.html(result['selectedZone']);
		}
	
		$(document).ajaxStop(function(e){
			if(context.$progressBar){
				context.$progressBar.addClass('hide');
			}
		});
	};
	Booki.TimezoneControl.prototype.autoDetectClick = function(e){
		var $target = $(e.currentTarget)
			, isChecked = $target.is(':checked')
			, result
			, selectedZone;
		if( isChecked ) {
			selectedZone = this.guessTimezone(null, true);
		}else{
			result = this.readState();
			selectedZone = result['selectedZone'];
		}
		this.saveState(isChecked, selectedZone);
	}
	Booki.TimezoneControl.prototype.regionSelectChange = function(e){
		var $target = $(e.currentTarget)
			, value = $target.val();
		this.timezoneChoice(value, null);
	}
	Booki.TimezoneControl.prototype.timezoneSelectChange = function(e){
		var $target
			, $sel;
		if(this.flag){
			this.flag = false;
			return;
		}
		$target = $(e.currentTarget);
		$sel = $target.find(':selected');
		this.$headerCaption.html($sel.text());
		this.$autoDetect.prop('checked', false);
		this.saveState(false, $sel.val());
		this.$collapseTimezone.collapse('hide');
	};
	Booki.TimezoneControl.prototype.getDefaultTimezone = function(){
		var state = Booki.Cookie.read(this.cookieName)
			, values
			, zone;
		if(!state){
			zone = jstz['determine']()
			return {
				'autoRun': true
				, 'selectedZone': zone['name']()
			};
		}
		values = state.split(':');
		return {
			'autoRun': values[0] === 'true'
			, 'selectedZone': values[1] === 'null' ? null : values[1]
		};
	};
	Booki.TimezoneControl.prototype.parseSavedState = function(state, timeZone){
		var values;
		if(!state){
			return {
				'autoRun': true
				, 'selectedZone': null
			};
		}
		values = state.split(':');
		return {
			'autoRun': values[0] === 'true'
			, 'selectedZone': values[1] === 'null' ? null : values[1]
		};
	};
	Booki.TimezoneControl.prototype.readState = function(saveState){
		var result
			, newValue
			, selectedZone
		if(this.$timezoneManualSelection.length > 0){
			return this.parseSavedState();
		}
		saveState = typeof(saveState) === 'undefined' ? true : saveState;
		result = Booki.Cookie.read(this.cookieName);
		newValue = this.$autoDetect.is(':checked');
		if(!result && saveState){
			selectedZone = this.guessTimezone();
			result = this.saveState(newValue, selectedZone);
		}
		return this.parseSavedState(result);
	};
	Booki.TimezoneControl.prototype.saveState = function(newValue, timezoneValue){
		if(this.$timezoneManualSelection.length > 0){
			return this.parseSavedState();
		}
		var result = Booki.Cookie.read(this.cookieName)
			, value = newValue + ':' + timezoneValue;
		if(value !== result){
			Booki.Cookie.erase(this.cookieName);
			Booki.Cookie.create(this.cookieName, value, 30);
			return value;
		}
		return result;
	};
	Booki.TimezoneControl.prototype.guessTimezone = function(selectedZone, triggerChange){
		var guessedTimezone = jstz['determine']()
			, region;
		selectedZone = (typeof(selectedZone) === 'undefined' || selectedZone === null) ? guessedTimezone['name']() : selectedZone;
		if(!selectedZone){
			return;
		}
		region = selectedZone.substr(0, selectedZone.indexOf('/'));
		if(!region){
			region = selectedZone;
		}
		this.timezoneChoice(region, selectedZone, triggerChange);
		return selectedZone;
	};
	Booki.TimezoneControl.prototype.timezoneChoice = function(region, selectedZone, triggerChange){
		if(region === '-1'){
			this.$timezoneContainer.addClass('hide');
			this.$timezoneSelect.empty();
			return;
		}
		this.$progressBar.removeClass('hide');
		(function(region, selectedZone, triggerChange,  context){
			$.post(context.ajaxUrl, {
				'model': {
					'region': region
					, 'selectedZone': selectedZone
				}
				, 'action': 'booki_timezoneChoice'
			}
			, function(data) {
				var r = $.parseJSON(data)
					, result = r['result']
					, options = result ? result['options'] : null
					, $option;
				if(options){
					if(context.$regionSelect.length > 0){
						context.$regionSelect.val(region);
					}
					if(context.$timezoneContainer.length > 0){
						context.$timezoneSelect.html(options);
						context.$timezoneContainer.removeClass('hide');
						if(selectedZone){
							$option = context.$timezoneSelect.find(":selected");
							if(context.$headerCaption.length > 0){
								context.$headerCaption[0].title = $option.text();
								context.$headerCaption.html($option.val());
							}
						}
						if(context.$loadOnStart.length > 0 && triggerChange){
							window.setTimeout(function(){
								context.flag = true;
								context.$timezoneSelect.change();
							}, 500);
							return;
						}
						if(selectedZone){
							context.$collapseTimezone.collapse('hide');
						}
					}
				}
			});
		})(region, selectedZone, triggerChange, this);
	};
	Booki.TimezoneControl.prototype.destroy = function(){
		if(this.timezoneSelectChangeDelegate){
			this.$timezoneSelect.off();
			delete this.timezoneSelectChangeDelegate;
		}
		if(this.regionSelectChangeDelegate){
			this.$regionSelect.off();
			delete this.regionSelectChangeDelegate;
		}
		if(this.autoDetectClickDelegate){
			this.$autoDetect.off();
			delete this.autoDetectClickDelegate;
		}	
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
	
})(window['jQuery'], window['jstz'], window['Booki']);
(function($, jstz, Booki){
	Booki.TimezoneControlState = function(options){
		this.init(options);
		this.destroyDelegate = Booki.createDelegate(this, this.destroy);
		$(window).unload(this.destroyDelegate);
	};
	Booki.TimezoneControlState.cookieName = 'BOOKITIMEZONE';
	Booki.TimezoneControlState.prototype.init = function(options){
		var settings = $.extend({}, options)
			, elem = settings['elem']
			, context = this
			, result = Booki.TimezoneControlState.readState();
		this.$root = $(elem);
		this.$root.each(function(){
			var $a = $(this)
				, url = $a.prop('href');
			url = context.updateQueryString(url, 'timezone', result['selectedZone']);
			$a.prop('href', url);
		});
	};
	Booki.TimezoneControlState.parseSavedState = function(state){
		var values;
		if(!state){
			return {
				'autoRun': true
				, 'selectedZone': null
			};
		}
		values = state.split(':');
		return {
			'autoRun': values[0] === 'true'
			, 'selectedZone': values[1] === 'null' ? null : values[1]
		};
	};
	Booki.TimezoneControlState.readState = function(){
		var result = Booki.Cookie.read(Booki.TimezoneControlState.cookieName)
			, newValue = true
			, selectedZone;
		if(!result){
			selectedZone = Booki.TimezoneControlState.guessTimezone();
			result = Booki.TimezoneControlState.saveState(newValue, selectedZone);
		}
		return Booki.TimezoneControlState.parseSavedState(result);
	};
	Booki.TimezoneControlState.saveState = function(newValue, timezoneValue){
		var result = Booki.Cookie.read(Booki.TimezoneControlState.cookieName)
			, value = newValue + ':' + timezoneValue;
		if(value !== result){
			Booki.Cookie.erase(Booki.TimezoneControlState.cookieName);
			Booki.Cookie.create(Booki.TimezoneControlState.cookieName, value, 30);
			return value;
		}
		return result;
	};
	Booki.TimezoneControlState.guessTimezone = function(){
		var guessedTimezone = jstz['determine']();
		return guessedTimezone['name']();
	};
	Booki.TimezoneControlState.prototype.updateQueryString = function(url, param, value){
		var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))')
			, parts = url.toString().split('#')
			, hash = parts[1]
			, qstring = /\?.+$/
			, newURL;
			
		url = parts[0];
		newURL = url;
		
		if (val.test(url))
		{
			newURL = url.replace(val, '$1' + param + '=' + value);
		}
		else if (qstring.test(url))
		{
			newURL = url + '&' + param + '=' + value;
		}
		else
		{
			newURL = url + '?' + param + '=' + value;
		}
		if (hash)
		{
			newURL += '#' + hash;
		}
		return newURL;
	};
	Booki.TimezoneControlState.prototype.destroy = function(){
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
})(window['jQuery'], window['jstz'], window['Booki']);
(function($, Booki){
	Booki.UserInfo = function(options){
		this.init(options);
		this.destroyDelegate = Booki.createDelegate(this, this.destroy);
		$(window).unload(this.destroyDelegate);
	};
	Booki.UserInfo.prototype.init = function(options){
		var settings = $.extend({}, options)
			, elem = settings['elem']
			, context = this;
		this.$root = $(elem);
		this.$triggerButton = this.$root.find(settings['triggerButton']);
		this.$userIdField = this.$root.find(settings['userIdField']);
		this.userFoundMessage = settings['userFoundMessage'];
		this.userNotFoundMessage = settings['userNotFoundMessage'];
		this.successCallback = settings['success'];
		this.ajaxUrl = settings['ajaxUrl'];
		this.$userEmailInfo = this.$root.find('.useremail-info');
		this.$progressBar = this.$root.find('.progress.booki-useremail');
		this.$userEmailTextbox = this.$root.find('#useremail');
			
		$(document).ajaxStop(function(e){
			context.$progressBar.addClass('hide');
		});
		
		if(this.$triggerButton.length > 0){
			this.triggerClickDelegate = Booki.createDelegate(this, this.triggerClick);
			this.$triggerButton.click(this.triggerClickDelegate);
			return;
		}
		
		this.userEmailChangedDelegate = Booki.createDelegate(this, this.userEmailChanged);
		this.$userEmailTextbox.change(this.userEmailChangedDelegate);
	};
	Booki.UserInfo.prototype.userEmailChanged = function(e){
		var $target = $(e.currentTarget);
		this.getUserInfo($target.val());
	}
	Booki.UserInfo.prototype.triggerClick = function(){
		this.getUserInfo(this.$userEmailTextbox.val());
		return false;
	};
	Booki.UserInfo.prototype.getUserInfo = function(email){
		var result = $('#useremail').parsley().validate(true);
		if(result !== true){
			return;
		}
		this.$progressBar.removeClass('hide');
		(function(email,  context){
			$.post(context.ajaxUrl, {
				'model': {
					'email': email
				}
				, 'action': 'booki_getUserByEmail'
			}
			, function(data) {
				var r = $.parseJSON(data)
					, result = r['result']
					, userName
					, firstName
					, lastName
					, profilePageUrl
					, closeButton = '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
				if(result){
					userName = result['userName'];
					firstName = result['firstName'];
					lastName = result['lastName'];
					profilePageUrl =  result['profilePageUrl'];
					
					if(context.$userIdField){
						context.$userIdField.val(result['id']);
					}
					
					if(firstName){
						userName = firstName;
						if(lastName){
							userName += (' ' + lastName);
						}
					}
					
					context.$userEmailInfo.removeClass('hide');
					if(userName){
						context.$userEmailInfo.html(context.userFoundMessage + ': ' + userName);
						if(context.successCallback){
							context.successCallback();
						} else if(context.$triggerButton){
							context.$triggerButton.off();
							context.$triggerButton.click();
						}
					}else{
						context.$userEmailInfo.html(closeButton + context.userNotFoundMessage);
					}
				}
				context.$progressBar.addClass('hide');
			});
		})(email, this);
	};
	Booki.UserInfo.prototype.destroy = function(){
		if(this.triggerClickDelegate){
			this.$triggerButton.off();
			delete this.triggerClickDelegate;
		}
		if(this.userEmailChangedDelegate){
			this.$userEmailTextbox.off();
			delete this.userEmailChangedDelegate;
		}
		if(this.destroyDelegate){
			$(window).off('unload', this.destroyDelegate);
			delete this.destroyDelegate;
		}
	};
})(window['jQuery'], window['Booki']);
