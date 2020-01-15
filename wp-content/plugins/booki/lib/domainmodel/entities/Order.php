<?php
class Booki_Order extends Booki_EntityBase{
	public $id = -1;
	public $orderDate;
	public $paymentDate;
	public $userId = null;
	public $status = Booki_PaymentStatus::UNPAID;
	public $token = null;
	public $transactionId = null;
	public $refundAmount = 0;
	public $currency;
	/**
		@description A special note a user can leave when checking out through paypal. 
		Value is retrieved from paypal after user payment.
	*/
	public $note = null;
	public $totalAmount = 0;
	/**
		@description Number of times an invoice was sent
	*/
	public $invoiceNotification = 0;
	public $refundNotification = 0;
	public $bookedOptionals;
	public $bookedCascadingItems;
	public $bookedFormElements;
	public $bookedQuantityElements;
	public $bookedDays;

	public $timezone;
	public $discount = 0;
	public $tax = 0;
	public $userIsRegistered = false;
	public $hasDaysPendingApproval = false;
	public $hasOptionalsPendingApproval = false;
	public $hasCascadingItemsPendingApproval = false;
	public $hasDaysPendingCancellation = false;
	public $hasOptionalsPendingCancellation = false;
	public $hasCascadingItemsPendingCancellation = false;
	public $hasQuantityElementsPendingCancellation = false;
	public $hasQuantityElementsPendingApproval = false;
	
	public $hasPendingApproval;
	public $hasPendingCancellation;
	public $user = null;
	public $notRegUserFirstname = null;
	public $notRegUserLastname = null;
	public $notRegUserEmail = null;
	public $enableSingleHourMinuteFormat = false;
	public $projectIdList = array();
	public $projectNames;
	public $csvFields = array();
	public $bookingInfo = null;
	public function __construct($args){
		if(array_key_exists('orderDate', $args)){
			$this->orderDate = new Booki_DateTime($args['orderDate']);
		}
		if(array_key_exists('userId', $args)){
			$this->userId = (int)$args['userId'];
		}
		if(array_key_exists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if(array_key_exists('token', $args)){
			$this->token = (string)$args['token'];
		}
		if(array_key_exists('transactionId', $args)){
			$this->transactionId = (string)$args['transactionId'];
		}
		if(array_key_exists('note', $args)){
			$this->note = (string)$args['note'];
		}
		if(array_key_exists('totalAmount', $args)){
			$this->totalAmount = (double)$args['totalAmount'];
		}
		if(array_key_exists('currency', $args)){
			$this->currency = (string)$args['currency'];
		}
		if(array_key_exists('discount', $args)){
			$this->discount = (double)$args['discount'];
		}
		if(array_key_exists('tax', $args)){
			$this->tax = (double)$args['tax'];
		}
		if(array_key_exists('invoiceNotification', $args)){
			$this->invoiceNotification = (int)$args['invoiceNotification'];
		}
		if(array_key_exists('refundNotification', $args)){
			$this->refundNotification = (int)$args['refundNotification'];
		}
		if(array_key_exists('refundAmount', $args)){
			$this->refundAmount = (double)$args['refundAmount'];
		}
		if(array_key_exists('paymentDate', $args)){
			$this->paymentDate = new Booki_DateTime($args['paymentDate']);
		}
		if(array_key_exists('timezone', $args)){
			$this->timezone = (string)$args['timezone'];
		}
		if(array_key_exists('isRegistered', $args)){
			$this->userIsRegistered = (bool)$args['isRegistered'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		if(array_key_exists('notRegUserFirstname', $args)){
			$this->notRegUserFirstname = (string)$args['notRegUserFirstname'];
		}
		if(array_key_exists('notRegUserLastname', $args)){
			$this->notRegUserLastname = (string)$args['notRegUserLastname'];
		}
		if(array_key_exists('notRegUserEmail', $args)){
			$this->notRegUserEmail = (string)$args['notRegUserEmail'];
		}
		if(array_key_exists('projectNames', $args)){
			$this->csvFields['projectNames'] = (string)$args['projectNames'];
			$this->projectNames = $this->csvFields['projectNames'];
		}
		if(array_key_exists('projectIdList', $args)){
			if(!is_array($args['projectIdList'])){
				$this->projectIdList =  isset($args['projectIdList']) ? array_map('intval', explode(',', $args['projectIdList'])) : array();
			}else{
				$this->projectIdList = (array)$args['projectIdList'];
			}
		}
		if(array_key_exists('enableSingleHourMinuteFormat', $args)){
			$this->enableSingleHourMinuteFormat = (bool)$args['enableSingleHourMinuteFormat'];
		}
		if(array_key_exists('bookedDates', $args)){
			$this->csvFields['bookedDates'] = explode(',', (string)$args['bookedDates']);
		}
		if(array_key_exists('bookedTimeslots', $args)){
			$bookedTimeslots = explode(',', (string)$args['bookedTimeslots']);
			$this->csvFields['bookedTimeslots'] = array();
			foreach($bookedTimeslots as $bts){
				if($bts){
					$s = explode('-', $bts);
					$start = explode(':', $s[0]);
					$end = explode(':', $s[1]);
					$timeslot = array('startHour'=>(int)$start[0], 'startMinute'=>(int)$start[1], 'endHour'=>$end[0], 'endMinute'=>$end[1]);
					array_push($this->csvFields['bookedTimeslots'], $timeslot);
				}
			}
		}
		if(array_key_exists('formElements', $args)){
			$this->csvFields['formElements'] = (string)$args['formElements'];
		}
		if(array_key_exists('optionals', $args)){
			$this->csvFields['optionals'] = (string)$args['optionals'];
		}
		if(array_key_exists('quantityElements', $args)){
			$this->csvFields['quantityElements'] = (string)$args['quantityElements'];
		}
		if(array_key_exists('cascadingItems', $args)){
			$this->csvFields['cascadingItems'] = (string)$args['cascadingItems'];
		}
		if(array_key_exists('hasDaysPendingApproval', $args)){
			$this->hasDaysPendingApproval = (int)$args['hasDaysPendingApproval'] > 0;
		}
		if(array_key_exists('hasOptionalsPendingApproval', $args)){
			$this->hasOptionalsPendingApproval = (int)$args['hasOptionalsPendingApproval'] > 0;
		}
		if(array_key_exists('hasCascadingItemsPendingApproval', $args)){
			$this->hasCascadingItemsPendingApproval = (int)$args['hasCascadingItemsPendingApproval'] > 0;
		}
		if(array_key_exists('hasDaysPendingCancellation', $args)){
			$this->hasDaysPendingCancellation = (int)$args['hasDaysPendingCancellation'] > 0;
		}
		if(array_key_exists('hasOptionalsPendingCancellation', $args)){
			$this->hasOptionalsPendingCancellation = (int)$args['hasOptionalsPendingCancellation'] > 0;
		}
		if(array_key_exists('hasCascadingItemsPendingCancellation', $args)){
			$this->hasCascadingItemsPendingCancellation = (int)$args['hasCascadingItemsPendingCancellation'] > 0;
		}
		if(array_key_exists('hasQuantityElementsPendingApproval', $args)){
			$this->hasQuantityElementsPendingApproval = (int)$args['hasQuantityElementsPendingApproval'] > 0;
		}
		if(array_key_exists('hasQuantityElementsPendingCancellation', $args)){
			$this->hasQuantityElementsPendingCancellation = (int)$args['hasQuantityElementsPendingCancellation'] > 0;
		}
		$globalSettings = BOOKIAPP()->globalSettings;
		
		if(!$this->currency){
			$localeInfo = Booki_Helper::getLocaleInfo();
			$this->currency = $localeInfo['currency'];
		}
		if(!($this->tax > 0) && $globalSettings->tax > 0){
			$this->tax = $globalSettings->tax;
		}
		$this->bookedOptionals = new Booki_BookedOptionals();
		$this->bookedFormElements = new Booki_BookedFormElements();
		$this->bookedDays = new Booki_BookedDays();
		$this->bookedCascadingItems = new Booki_BookedCascadingItems();
		$this->bookedQuantityElements = new Booki_BookedQuantityElements();
		$this->hasPendingApproval = $this->hasDaysPendingApproval || $this->hasOptionalsPendingApproval || $this->hasCascadingItemsPendingApproval || $this->hasQuantityElementsPendingApproval;
		$this->hasPendingCancellation = $this->hasDaysPendingCancellation || $this->hasOptionalsPendingCancellation || $this->hasCascadingItemsPendingCancellation || $this->hasQuantityElementsPendingCancellation;
	}
	
	public function getFirstName(){
		$result = '';
		if($this->userIsRegistered && $this->user){
			$result = $this->user->firstname;
		}else if(!$this->userIsRegistered && $this->notRegUserFirstname){
			$result = $this->notRegUserFirstname;
		}
		return $result;
	}
	public function getLastName(){
		$result = '';
		if($this->userIsRegistered && $this->user){
			$result = $this->user->lastname;
		}else if(!$this->userIsRegistered && $this->notRegUserLastname){
			$result = $this->notRegUserLastname;
		}
		return $result;
	}
	public function getEmail(){
		$result = '';
		if($this->userIsRegistered && $this->user){
			$result = $this->user->email;
		}else if(!$this->userIsRegistered && $this->notRegUserEmail){
			$result = $this->notRegUserEmail;
		}
		return $result;
	}
	public function toArray(){
		$result = array(
			'id'=>$this->id
			, 'orderDate'=>$this->orderDate
			, 'userId'=>$this->userId
			, 'status'=>$this->status
			, 'token'=>$this->token
			, 'transactionId'=>$this->transactionId
			, 'note'=>$this->note
			, 'totalAmount'=>$this->totalAmount
			, 'currency'=>$this->currency
			, 'discount'=>$this->discount
			, 'tax'=>$this->tax
			, 'invoiceNotification'=>$this->invoiceNotification
			, 'refundNotification'=>$this->refundNotification
			, 'refundAmount'=>$this->refundAmount
			, 'paymentDate'=>$this->paymentDate
			, 'timezone'=>$this->timezone
			, 'userIsRegistered'=>$this->userIsRegistered
			, 'hasDaysPendingApproval'=>$this->hasDaysPendingApproval
			, 'hasOptionalsPendingApproval'=>$this->hasOptionalsPendingApproval
			, 'hasCascadingItemsPendingApproval'=>$this->hasCascadingItemsPendingApproval
			, 'hasDaysPendingCancellation'=>$this->hasDaysPendingCancellation
			, 'hasOptionalsPendingCancellation'=>$this->hasOptionalsPendingCancellation
			, 'hasCascadingItemsPendingCancellation'=>$this->hasCascadingItemsPendingCancellation
			, 'hasQuantityElementsPendingCancellation'=>$this->hasQuantityElementsPendingCancellation
			, 'hasQuantityElementsPendingApproval'=>$this->hasQuantityElementsPendingApproval
			, 'hasPendingApproval'=>$this->hasPendingApproval
			, 'hasPendingCancellation'=>$this->hasPendingCancellation
			, 'approvalStatus'=>$this->getApprovalStatus()
			, 'approvalStatusLabel'=>$this->getApprovalStatusLabel()
			, 'notRegUserFirstname'=>$this->notRegUserFirstname
			, 'notRegUserLastname'=>$this->notRegUserLastname
			, 'notRegUserEmail'=>$this->notRegUserEmail
			, 'projectIdList'=>$this->projectIdList
		);
		if($this->user){
			$user = $this->user->toArray();
			unset($user['id']);
			$result = array_merge($result, $user);
		}
		return $result;
	}
	
	public function afterDiscount(){
		$totalAmount = $this->totalAmount;
		if($this->discount > 0){
			$totalAmount = Booki_Helper::calcDiscount($this->discount, $this->totalAmount);
		}
		return $totalAmount;
	}
	
	protected function getApprovalStatus(){
		if ($this->hasPendingApproval){
			return __('Pending Approval', 'booki');
		}else if ($this->hasPendingCancellation){
			return __('Pending User Cancel Request', 'booki');
		}else if ($this->status === Booki_BookingStatus::REFUNDED){
			return __('Refunded', 'booki');
		}else{
			return __('Approved', 'booki');
		}
	}
	
	protected function getApprovalStatusLabel(){
		if ($this->hasPendingApproval){
			return 'info';
		}else if ($this->hasPendingCancellation){
			return 'danger';
		}else if ($this->status === Booki_BookingStatus::REFUNDED){
			return 'warning';
		}
		return 'success';
	}
}
?>