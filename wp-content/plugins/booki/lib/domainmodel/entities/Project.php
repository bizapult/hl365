<?php
class Booki_Project extends Booki_EntityBase{
	public $id;
	public $status = Booki_ProjectStatus::RUNNING;
	public $name;
	public $bookingDaysLimit = 1;
	public $calendarMode = Booki_CalendarMode::POPUP;
	public $bookingMode = Booki_BookingMode::RESERVATION;
	public $description;
	public $previewUrl;
	public $tag;
	public $defaultStep = Booki_ProjectStep::BOOKING_FORM;
	public $bookingTabLabel; 
	public $customFormTabLabel;
	public $attendeeTabLabel;
	public $availableDaysLabel;
	public $selectedDaysLabel;
	public $bookingTimeLabel;
	public $optionalItemsLabel;
	public $nextLabel;
	public $prevLabel;
	public $addToCartLabel;
	public $fromLabel;
	public $toLabel;
	public $proceedToLoginLabel;
	public $makeBookingLabel;
	public $bookingLimitLabel;
	public $notifyUserEmailList;
	public $optionalsBookingMode;
	public $optionalsListingMode;
	public $optionalsMinimumSelection;
	public $contentTop;
	public $contentBottom;
	public $bookingWizardMode;
	public $hideSelectedDays;
	public $displayAttendees;
	public $bookingDaysMinimum;
	//localized
	public $name_loc;
	public $description_loc;
	public $bookingTabLabel_loc;
	public $customFormTabLabel_loc;
	public $attendeeTabLabel_loc;
	public $selectedDaysLabel_loc;
	public $availableDaysLabel_loc;
	public $prevLabel_loc;
	public $nextLabel_loc;
	public $addToCartLabel_loc;
	public $bookingTimeLabel_loc;
	public $optionalItemsLabel_loc;
	public $fromLabel_loc;
	public $toLabel_loc;
	public $proceedToLoginLabel_loc;
	public $makeBookingLabel_loc;
	public $bookingLimitLabel_loc;
	public $banList;
	public $defaultDateSelected = true;
	public function  __construct($args){
		if(array_key_exists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('name', $args)){
			$this->name = $this->decode((string)$args['name']);
		}
		if(array_key_exists('bookingDaysMinimum', $args)){
			$this->bookingDaysMinimum = (int)$args['bookingDaysMinimum'];
		}
		if(array_key_exists('bookingDaysLimit', $args)){
			$this->bookingDaysLimit = (int)$args['bookingDaysLimit'];
		}
		if(array_key_exists('calendarMode', $args)){
			$this->calendarMode = (int)$args['calendarMode'];
		}
		if(array_key_exists('bookingMode', $args)){
			$this->bookingMode = (int)$args['bookingMode'];
		}
		if(array_key_exists('description', $args)){
			$this->description = $this->decode((string)$args['description']);
		}
		if(array_key_exists('previewUrl', $args)){
			$this->previewUrl = (string)$args['previewUrl'];
		}
		if(array_key_exists('tag', $args)){
			$this->tag = $this->decode((string)$args['tag']);
		}
		if(array_key_exists('defaultStep', $args)){
			$this->defaultStep = (int)$args['defaultStep'];
		}
		if(array_key_exists('bookingTabLabel', $args)){
			$this->bookingTabLabel = $this->decode((string)$args['bookingTabLabel']);
		}
		if(array_key_exists('customFormTabLabel', $args)){
			$this->customFormTabLabel = $this->decode((string)$args['customFormTabLabel']);
		}
		if(array_key_exists('attendeeTabLabel', $args)){
			$this->attendeeTabLabel = $this->decode((string)$args['attendeeTabLabel']);
		}
		if(array_key_exists('availableDaysLabel', $args)){
			$this->availableDaysLabel = $this->decode((string)$args['availableDaysLabel']);
		}
		if(array_key_exists('selectedDaysLabel', $args)){
			$this->selectedDaysLabel = $this->decode((string)$args['selectedDaysLabel']);
		}
		if(array_key_exists('bookingTimeLabel', $args)){
			$this->bookingTimeLabel = $this->decode((string)$args['bookingTimeLabel']);
		}
		if(array_key_exists('optionalItemsLabel', $args)){
			$this->optionalItemsLabel = $this->decode((string)$args['optionalItemsLabel']);
		}
		if(array_key_exists('nextLabel', $args)){
			$this->nextLabel = $this->decode((string)$args['nextLabel']);
		}
		if(array_key_exists('prevLabel', $args)){
			$this->prevLabel = $this->decode((string)$args['prevLabel']);
		}
		if(array_key_exists('addToCartLabel', $args)){
			$this->addToCartLabel = $this->decode((string)$args['addToCartLabel']);
		}
		if(array_key_exists('fromLabel', $args)){
			$this->fromLabel = $this->decode((string)$args['fromLabel']);
		}
		if(array_key_exists('toLabel', $args)){
			$this->toLabel = $this->decode((string)$args['toLabel']);
		}
		if(array_key_exists('proceedToLoginLabel', $args)){
			$this->proceedToLoginLabel = $this->decode((string)$args['proceedToLoginLabel']);
		}
		if(array_key_exists('makeBookingLabel', $args)){
			$this->makeBookingLabel = $this->decode((string)$args['makeBookingLabel']);
		}
		if(array_key_exists('bookingLimitLabel', $args)){
			$this->bookingLimitLabel = $this->decode((string)$args['bookingLimitLabel']);
		}
		if(array_key_exists('notifyUserEmailList', $args)){
			$this->notifyUserEmailList = trim($this->decode((string)$args['notifyUserEmailList']));
		}
		if(array_key_exists('optionalsBookingMode', $args)){
			$this->optionalsBookingMode = (int)$args['optionalsBookingMode'];
		}
		if(array_key_exists('optionalsListingMode', $args)){
			$this->optionalsListingMode = (int)$args['optionalsListingMode'];
		}
		if(array_key_exists('optionalsMinimumSelection', $args)){
			$this->optionalsMinimumSelection = (int)$args['optionalsMinimumSelection'];
		}
		if(array_key_exists('contentTop', $args)){
			$this->contentTop = $this->decode((string)$args['contentTop']);
		}
		if(array_key_exists('contentBottom', $args)){
			$this->contentBottom = $this->decode((string)$args['contentBottom']);
		}
		if(array_key_exists('bookingWizardMode', $args)){
			$this->bookingWizardMode = (int)$args['bookingWizardMode'];
		}
		if(array_key_exists('hideSelectedDays', $args)){
			$this->hideSelectedDays = (bool)$args['hideSelectedDays'];
		}
		if(array_key_exists('displayAttendees', $args)){
			$this->displayAttendees = (bool)$args['displayAttendees'];
		}
		if(array_key_exists('banList', $args)){
			$this->banList = $this->decode((string)$args['banList']);
		}
		if(array_key_exists('defaultDateSelected', $args)){
			if(!is_null($args['defaultDateSelected'])){
				$this->defaultDateSelected = (bool)$args['defaultDateSelected'];
			}
		}
		
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		
		//defaults
		if(!$this->bookingTabLabel){
			$this->bookingTabLabel = __('Booking', 'booki');
		}
		if(!$this->customFormTabLabel){
			$this->customFormTabLabel = __('Details', 'booki');
		}
		if(!$this->attendeeTabLabel){
			$this->attendeeTabLabel = __('Attendees', 'booki');
		}
		if(!$this->selectedDaysLabel){
			$this->selectedDaysLabel = __('Selected days', 'booki');
		}
		if(!$this->availableDaysLabel){
			$this->availableDaysLabel = __('Available days', 'booki');
		}
		if(!$this->prevLabel){
			$this->prevLabel =  __('Back', 'booki');
		}
		if(!$this->nextLabel){
			$this->nextLabel =  __('Next', 'booki');
		}
		if(!$this->addToCartLabel){
			$this->addToCartLabel =  __('Add to cart', 'booki');
		}
		if(!$this->bookingTimeLabel){
			$this->bookingTimeLabel = __('Booking time', 'booki');
		}
		if(!$this->optionalItemsLabel){
			$this->optionalItemsLabel = __('Optional extras', 'booki');
		}
		if(!$this->fromLabel){
			$this->fromLabel = __('From', 'booki');
		}
		if(!$this->toLabel){
			$this->toLabel = __('To', 'booki');
		}
		if(!$this->proceedToLoginLabel){
			$this->proceedToLoginLabel = __('Proceed', 'booki');
		}
		if(!$this->makeBookingLabel){
			$this->makeBookingLabel = __('Make booking', 'booki');
		}
		if(!$this->bookingLimitLabel){
			$this->bookingLimitLabel = __('%d seats left.', 'booki');
		}
		$this->updateResources();
		$this->init();
	}
	
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('name_project' . $this->id, $this->name);
		$this->bookingTabLabel_loc = Booki_WPMLHelper::t('bookingTabLabel_project' . $this->id, $this->bookingTabLabel);
		$this->customFormTabLabel_loc = Booki_WPMLHelper::t('customFormTabLabel_project' . $this->id, $this->customFormTabLabel);
		$this->attendeeTabLabel_loc = Booki_WPMLHelper::t('attendeeTabLabel_project' . $this->id, $this->attendeeTabLabel);
		$this->selectedDaysLabel_loc = Booki_WPMLHelper::t('selectedDaysLabel_project' . $this->id, $this->selectedDaysLabel);
		$this->availableDaysLabel_loc = Booki_WPMLHelper::t('availableDaysLabel_project' . $this->id, $this->availableDaysLabel);
		$this->description_loc = Booki_WPMLHelper::t('description_project' . $this->id, $this->description);
		$this->prevLabel_loc = Booki_WPMLHelper::t('prev_label_project' . $this->id, $this->prevLabel);
		$this->nextLabel_loc = Booki_WPMLHelper::t('next_label_project' . $this->id, $this->nextLabel);
		$this->addToCartLabel_loc = Booki_WPMLHelper::t('add_to_cart_label_project' . $this->id, $this->addToCartLabel);
		$this->bookingTimeLabel_loc = Booki_WPMLHelper::t('booking_time_label_project' . $this->id, $this->bookingTimeLabel);
		$this->optionalItemsLabel_loc = Booki_WPMLHelper::t('optional_items_label_project' . $this->id, $this->optionalItemsLabel);
		$this->fromLabel_loc = Booki_WPMLHelper::t('from_label_project' . $this->id, $this->fromLabel);
		$this->toLabel_loc = Booki_WPMLHelper::t('to_label_project' . $this->id, $this->toLabel);
		$this->proceedToLoginLabel_loc = Booki_WPMLHelper::t('proceed_to_login_label_project' . $this->id, $this->proceedToLoginLabel);
		$this->makeBookingLabel_loc = Booki_WPMLHelper::t('make_booking_label_project' . $this->id, $this->makeBookingLabel);
		$this->bookingLimitLabel_loc = Booki_WPMLHelper::t('booking_limit_label_project' . $this->id, $this->bookingLimitLabel);
	}
	public function updateResources(){
		$this->registerWPML();
	}
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('name_project' . $this->id, $this->name);
		Booki_WPMLHelper::register('bookingTabLabel_project' . $this->id, $this->bookingTabLabel);
		Booki_WPMLHelper::register('customFormTabLabel_project' . $this->id, $this->customFormTabLabel);
		Booki_WPMLHelper::register('attendeeTabLabel_project' . $this->id, $this->attendeeTabLabel);
		Booki_WPMLHelper::register('selectedDaysLabel_project' . $this->id, $this->selectedDaysLabel);
		Booki_WPMLHelper::register('availableDaysLabel_project' . $this->id, $this->availableDaysLabel);
		Booki_WPMLHelper::register('description_project' . $this->id, $this->description);
		Booki_WPMLHelper::register('prev_label_project' . $this->id, $this->prevLabel);
		Booki_WPMLHelper::register('next_label_project' . $this->id, $this->nextLabel);
		Booki_WPMLHelper::register('add_to_cart_label_project' . $this->id, $this->addToCartLabel);
		Booki_WPMLHelper::register('booking_time_label_project' . $this->id, $this->bookingTimeLabel);
		Booki_WPMLHelper::register('optional_items_label_project' . $this->id, $this->optionalItemsLabel);
		Booki_WPMLHelper::register('from_label_project' . $this->id, $this->fromLabel);
		Booki_WPMLHelper::register('to_label_project' . $this->id, $this->toLabel);
		Booki_WPMLHelper::register('proceed_to_login_label_project' . $this->id, $this->proceedToLoginLabel);
		Booki_WPMLHelper::register('make_booking_label_project' . $this->id, $this->makeBookingLabel);
		Booki_WPMLHelper::register('booking_limit_label_project' . $this->id, $this->bookingLimitLabel);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('name_project' . $this->id);
		Booki_WPMLHelper::unregister('bookingTabLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('customFormTabLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('attendeeTabLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('selectedDaysLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('availableDaysLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('description_project' . $this->id);
		Booki_WPMLHelper::unregister('prev_label_project' . $this->id);
		Booki_WPMLHelper::unregister('next_label_project' . $this->id);
		Booki_WPMLHelper::unregister('add_to_cart_label_project' . $this->id);
		Booki_WPMLHelper::unregister('booking_time_label_project' . $this->id);
		Booki_WPMLHelper::unregister('optional_items_label_project' . $this->id);
		Booki_WPMLHelper::unregister('from_label_project' . $this->id);
		Booki_WPMLHelper::unregister('to_label_project' . $this->id);
		Booki_WPMLHelper::unregister('proceed_to_login_label_project' . $this->id);
		Booki_WPMLHelper::unregister('make_booking_label_project' . $this->id);
		Booki_WPMLHelper::unregister('booking_limit_label_project' . $this->id);
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'status'=>$this->status
			, 'name'=>$this->name
			, 'name_loc'=>$this->name_loc
			, 'bookingDaysMinimum'=>$this->bookingDaysMinimum
			, 'bookingDaysLimit'=>$this->bookingDaysLimit
			, 'calendarMode'=>$this->calendarMode
			, 'bookingMode'=>$this->bookingMode
			, 'description'=>$this->description
			, 'description_loc'=>$this->description_loc
			, 'previewUrl'=>$this->previewUrl
			, 'tag'=>$this->tag
			, 'defaultStep'=>$this->defaultStep
			, 'bookingTabLabel'=>$this->bookingTabLabel
			, 'customFormTabLabel'=>$this->customFormTabLabel
			, 'attendeeTabLabel'=>$this->attendeeTabLabel
			, 'availableDaysLabel'=>$this->availableDaysLabel
			, 'selectedDaysLabel'=>$this->selectedDaysLabel
			, 'bookingTimeLabel'=>$this->bookingTimeLabel
			, 'optionalItemsLabel'=>$this->optionalItemsLabel
			, 'nextLabel'=>$this->nextLabel
			, 'prevLabel'=>$this->prevLabel
			, 'addToCartLabel'=>$this->addToCartLabel
			, 'fromLabel'=>$this->fromLabel
			, 'toLabel'=>$this->toLabel
			, 'proceedToLoginLabel'=>$this->proceedToLoginLabel
			, 'makeBookingLabel'=>$this->makeBookingLabel
			, 'bookingLimitLabel'=>$this->bookingLimitLabel
			, 'notifyUserEmailList'=>$this->notifyUserEmailList
			, 'optionalsBookingMode'=>$this->optionalsBookingMode
			, 'optionalsListingMode'=>$this->optionalsListingMode
			, 'optionalsMinimumSelection'=>$this->optionalsMinimumSelection
			, 'contentTop'=>$this->contentTop
			, 'contentBottom'=>$this->contentBottom
			, 'bookingWizardMode'=>$this->bookingWizardMode
			, 'hideSelectedDays'=>$this->hideSelectedDays
			, 'displayAttendees'=>$this->displayAttendees
			, 'banList'=>$this->banList
			, 'defaultDateSelected'=>$this->defaultDateSelected
		);
	}
}
?>