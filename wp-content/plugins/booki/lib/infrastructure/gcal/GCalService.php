<?php
class Booki_GCalService{
	public $client;
	public $clientData;
	public $redirectUri;
	public $globalSettings;
	protected $service = null;
	protected $repo;
	protected $code;
	protected $dateFormat = 'Y-m-d';
	public function __construct($userId, $code = null){
		$this->code = $code;
		$this->repo = new Booki_GCalRepository();
		$this->clientData = $this->repo->readByUser($userId);
		if(!$this->clientData){
			return;
		}
		$this->redirectUri = get_site_url();
		$this->state = Booki_Helper::base64UrlEncode('{ "booki_action" : "gcal" , "booki_userid" : ' . $userId . '}');
		$this->globalSettings = BOOKIAPP()->globalSettings;
		ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes
		try {
			$this->init();
		} catch (Exception $e) {
			Booki_EventsLogProvider::insert($e);
		}
	}
	
	public function init(){
		$this->client = new Google_Client();
		$this->client->setClientId($this->clientData->clientId);
		$this->client->setClientSecret($this->clientData->clientSecret);
		$this->client->setRedirectUri($this->redirectUri);
		$this->client->setState($this->state);
		$this->client->setApprovalPrompt('force');
		$this->client->setAccessType('offline');
		//request read/write access
		$this->client->addScope('https://www.googleapis.com/auth/calendar');
		if (!$this->clientData->accessToken && isset($this->code)) {
			//first time, client has granted us permission
			$this->client->authenticate($this->code);  
			$this->clientData->accessToken = $this->client->getAccessToken();
			$this->repo->update($this->clientData);
		} else if ($this->clientData->accessToken){
			$this->client->setAccessToken($this->clientData->accessToken);
			if($this->client->isAccessTokenExpired()){
				$accessToken = json_decode($this->clientData->accessToken);
				$this->client->refreshToken($accessToken->refresh_token);
				$this->clientData->accessToken = $this->client->getAccessToken();
				$this->repo->update($this->clientData);
			}
		}
		$this->service = new Google_Service_Calendar($this->client);
	}
	public function getAuthorizationURL(){
		return $this->client->createAuthUrl();
	}
	public function authenticate() {
		if(!$this->serviceActive()){
			return;
		}
		//dead code, not being used.
		if (isset($this->clientData->accessToken)) {
		  //already have the token.
		  $this->apiClient->setAccessToken($this->clientData->accessToken);
		} else {
			//assumes client already granted permission previously
			$this->client->setAccessToken($this->client->authenticate());
			$this->clientData->accessToken = $this->client->getAccessToken();
			$this->repo->update($this->clientData);
		}
	}
	//toDO: create companion methods for syncing/deleting single order and event
	public function sync(){
		$result = $this->syncAllCalendars();
		$eventsResult = $this->syncEvents();
		array_push($result, sprintf(__('%d new events created and %d events synced.', 'booki'), $eventsResult['created'], $eventsResult['updated']));
		return $result;
	}
	public function syncAllCalendars(){
		$result = array();
		if(!$this->serviceActive()){
			return $result;
		}
		$projectRepo = new Booki_ProjectRepository();
		$projects = $projectRepo->readAll();
		
		$gcalProjectsRepo = new Booki_GCalProjectsRepository();
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		$orderRepo = new Booki_OrderRepository();
		$rolesRepo = new Booki_RolesRepository();
		$roles = $rolesRepo->readByUser($this->clientData->userId);
		$projectsToSync = $roles->getProjectIdList();

		$calendarList = $this->service->calendarList->listCalendarList();
		
		while(true) {
			foreach ($calendarList->getItems() as $calendarListEntry) {
				$id = $calendarListEntry->getId();
				$gcalProject = $gcalProjectsRepo->readByCalendar($id);
				if($gcalProject){
					if(($key = array_search((int)$gcalProject->projectId, $projectsToSync)) !== false &&
						(int)$gcalProject->userId === $this->clientData->userId) {
						unset($projectsToSync[$key]);
					}
				}
			}
			$pageToken = $calendarList->getNextPageToken();
			if ($pageToken) {
				$optParams = array('pageToken' => $pageToken);
				$calendarList = $this->service->calendarList->listCalendarList($optParams);
			} else {
				break;
			}
		}
		foreach($projectsToSync as $projectId){
			$project = $projects->getProjectById($projectId);
			$result = array_merge($result, $this->createCalendar($projectId, $project));
		}
		return $result;
	}
	
	public function syncEvents(){
		$result = array('created'=>0, 'updated'=>0);
		if(!$this->serviceActive()){
			return $result;
		}
		$orderRepo = new Booki_OrderRepository();
		$rolesRepo = new Booki_RolesRepository();
		$roles = $rolesRepo->readByUser($this->clientData->userId);

		$projectsToSync = $roles->getProjectIdList();
		foreach($projectsToSync as $projectId){
			$orderList = $orderRepo->readByProject($projectId);
			foreach($orderList as $ol){
				$syncResult = $this->updateEventByOrder((int)$ol->id, true);
				$result['created'] += $syncResult['created'];
				$result['updated'] += $syncResult['updated'];
			}
		}
		return $result;
	}
	
	public function createEvent($orderId, $gcalProject = null){
		$created = false;
		if(!$this->serviceActive()){
			return $created;
		}
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		$hasEvents = $gcalEventsRepo->orderHasEvents($orderId);
		if($hasEvents){
			//already synced
			return false;
		}
		$order = Booki_BookingProvider::read($orderId);
		$result = Booki_BookingHelper::getBookings($order);
		$projectId = null;
		foreach($result['bookings'] as $booking){
			if(Booki_DateHelper::todayMoreThan($booking->date)){
				//only sync future bookings.
				continue;
			}
			if(!$gcalProject || $projectId !== $booking->projectId){
				$gcalProject = $this->getCalendarProject($booking->projectId);
			}
			$projectId = $booking->projectId;
			$event = new Google_Service_Calendar_Event();
			$event = $this->setEventAttributes($event, $booking, $order, $booking->date);
			$created = $this->insertEvent($event, $gcalProject->calendarId, $orderId, $booking->id);
		}
		return $created;
	}
	public function updateEventByOrder($orderId, $isSync = false){
		$result = array('created'=>0,'updated'=>0);
		if(!$this->serviceActive()){
			return $result;
		}
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		$order = Booki_BookingProvider::read($orderId);
		$r = Booki_BookingHelper::getBookings($order);
		$projectId = null;
		$gcalProject = null;
		foreach($r['bookings'] as $booking){
			if($isSync && Booki_DateHelper::todayMoreThan($booking->date)){
				//only sync future bookings.
				continue;
			}
			if($projectId !== $booking->projectId){
				$gcalProject = $this->getCalendarProject($booking->projectId);
			}
			$projectId = $booking->projectId;
			$gcalEvent = $gcalEventsRepo->readByBookedDay($booking->id);
			if(!$gcalEvent){
				//new? add it
				$event = new Google_Service_Calendar_Event();
				$event = $this->setEventAttributes($event, $booking, $order, $booking->date);
				$this->insertEvent($event, $gcalProject->calendarId, $orderId, $booking->id);
				++$result['created'];
				continue;
			}
			$event = $this->service->events->get($gcalEvent->calendarId, $gcalEvent->eventId);
			$event = $this->setEventAttributes($event, $booking, $order, $booking->date);
			try {
				$this->service->events->update($gcalProject->calendarId, $gcalEvent->eventId, $event);
				++$result['updated'];
			} catch (Exception $e) {
				Booki_EventsLogProvider::insert($e);
			}
		}
		return $result;
	}
	public function updateSingleEvent($eventId){
		$result = false;
		if(!$this->serviceActive()){
			return $result;
		}
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		$gcalEvent = $gcalEventsRepo->read($eventId);
		if(!$gcalEvent){
			return false;
		}
		$bookedDayRepo = new Booki_BookedDaysRepository();
		$bookedDay = $bookedDayRepo->read($gcalEvent->bookedDayId);
		$gcalProject = $this->getCalendarProject($bookedDay->projectId);
		$event = $this->service->events->get($gcalEvent->calendarId, $gcalEvent->eventId);
		$event = $this->setEventAttributes($event, $bookedDay, $order, $bookedDay->bookingDate);
		try {
			$this->service->events->update($gcalProject->calendarId, $gcalEvent->eventId, $event);
			$result = true;
		} catch (Exception $e) {
			Booki_EventsLogProvider::insert($e);
			$result = false;
		}
		return $result;
	}
	public function updateEventByBookedDay($bookedDayId){
		$result = false;
		if(!$this->serviceActive()){
			return $result;
		}
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		$gcalEvent = $gcalEventsRepo->readByBookedDay($eventId);
		if(!$gcalEvent){
			return false;
		}
		$bookedDayRepo = new Booki_BookedDaysRepository();
		$bookedDay = $bookedDayRepo->read($gcalEvent->bookedDayId);
		$gcalProject = $this->getCalendarProject($bookedDay->projectId);
		$event = $this->service->events->get($gcalEvent->calendarId, $gcalEvent->eventId);
		$event = $this->setEventAttributes($event, $bookedDay, $order, $bookedDay->bookingDate);
		try {
			$this->service->events->update($gcalProject->calendarId, $gcalEvent->eventId, $event);
			$result = true;
		} catch (Exception $e) {
			Booki_EventsLogProvider::insert($e);
			$result = false;
		}
		return $result;
	}
	public function deleteAllCalendars($projectsToSync = null){
		$result = array();
		if(!$this->serviceActive()){
			return $result;
		}
		$gcalProjectsRepo = new Booki_GCalProjectsRepository();
		if($projectsToSync === null){
			$rolesRepo = new Booki_RolesRepository();
			$roles = $rolesRepo->readByUser($this->clientData->userId);
			$projectsToSync = $roles->getProjectIdList();
		}
		$calendarList = $this->service->calendarList->listCalendarList();
		while(true) {
			foreach ($calendarList->getItems() as $calendarListEntry) {
				$id = $calendarListEntry->getId();
				$gcalProject = $gcalProjectsRepo->readByCalendar($id);
				if($gcalProject){
					if(array_search((int)$gcalProject->projectId, $projectsToSync) !== false &&
						(int)$gcalProject->userId === $this->clientData->userId) {
						try{
							$this->service->calendarList->delete($id);
							$gcalProjectsRepo->deleteByCalendar($id);
							array_push($result, $id);
						} catch (Exception $e) {
							Booki_EventsLogProvider::insert($e);
						}
					}
				}
			}
			$pageToken = $calendarList->getNextPageToken();
			if ($pageToken) {
				$optParams = array('pageToken' => $pageToken);
				$calendarList = $this->service->calendarList->listCalendarList($optParams);
			} else {
				break;
			}
		}
		return $result;
	}
	public function deleteEventByOrder($orderId){
		if(!$this->serviceActive()){
			return;
		}
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		$result = $gcalEventsRepo->readByOrder($orderId);
		if($result === false){
			return;
		}
		foreach($result as $gcalEvent){
			try{
				$this->service->events->delete($gcalEvent->calendarId, $gcalEvent->eventId);
			}catch(Exception $e){
				Booki_EventsLogProvider::insert($e);
			}
		}
	}
	public function deleteEventByBookedDay($bookedDayId){
		if(!$this->serviceActive()){
			return;
		}
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		$gcalEvent = $gcalEventsRepo->readByBookedDay($bookedDayId);
		if($gcalEvent === false){
			return;
		}
		try{
			$this->service->events->delete($gcalEvent->calendarId, $gcalEvent->eventId);
		}catch(Exception $e){
			Booki_EventsLogProvider::insert($e);
		}
	}
	
	protected function createCalendar($projectId, $project = null){
		$result = array();
		if(!$this->serviceActive()){
			return $result;
		}
		$gcalProjectsRepo = new Booki_GCalProjectsRepository();
		$projectRepo = new Booki_ProjectRepository();
		if(!$project){
			$project = $projectRepo->read($projectId);
		}
		if($project){
			$calendar = new Google_Service_Calendar_Calendar();
			$calendar->setSummary($project->name);
			$calendar->setTimeZone($this->globalSettings->timezone);
			try {
				$createdCalendar = $this->service->calendars->insert($calendar);
				$newId = $createdCalendar->getId();
				$gcalProjectsRepo->insert($newId, $project->id, $this->clientData->userId);
				array_push($result, $newId);
			} catch (Exception $e) {
				Booki_EventsLogProvider::insert($e);
			}
		}
		return $result;
	}
	protected function getCalendarProject($projectId){
		$gcalProjectsRepo = new Booki_GCalProjectsRepository();
		$result = $gcalProjectsRepo->readByProject($projectId, $this->clientData->userId);
		if(!$result){
			$this->createCalendar($projectId);
			$result = $gcalProjectsRepo->readByProject($projectId, $this->clientData->userId);
		}
		return $result;
	}
	protected function getBookingSummary($booking){
		$summary = array();
		if($booking->firstname){
			array_push($summary, sprintf(__('Booked by %s', 'booki'), trim($booking->firstname . ' ' . $booking->lastname)));
		}
		if($booking->email){
			$label = '%s';
			if(count($summary) === 0){
				$label = __('Booked by %s', 'booki');
			}
			array_push($summary, sprintf($label, $booking->email));
		}
		return join(', ', $summary);
	}
	protected function getBookingDescription($booking){
		$description = array();
		foreach($booking->quantityElements as $quantityElement){
			$value = $quantityElement->name . ' x ' . $quantityElement->getSelectedQuantity();
			array_push($description, $value);
		}
		foreach($booking->cascadingItems as $cascadingItem){
			$value = $cascadingItem->value;
			$trail = $cascadingItem->getTrail();
			if($cascadingItem->count > 0){
				$value .= ' x ' . $cascadingItem->count;
			}
			if($trail){
				$value = $trail;
			}
			array_push($description, $value);
		}
		foreach($booking->optionals as $optional){
			$value = $optional->name;
			if($optional->count > 0){
				$value .= ' x ' . $optional->count;
			}
			array_push($description, $value);
		}
		foreach($booking->formElements as $formElement){
			$capabilities = array(
				Booki_FormElementCapability::EMAIL_NOTIFICATION_AUTOREG
				, Booki_FormElementCapability::EMAIL_NOTIFICATION
				, Booki_FormElementCapability::FIRST_NAME
				, Booki_FormElementCapability::LAST_NAME
			);
			if(!in_array($formElement->capability, $capabilities)){
				$value = $formElement->label . ': ' . $formElement->value;
				array_push($description, $value);
			}
		}
		//need more data in case we are not using custom form elements to capture user info
		//get registered user data and add it
		
		return join(', ', $description);
	}
	protected function setEventAttributes($event, $booking, $order, $date){
		$event->setSummary($this->getBookingSummary($booking));
		$event->setDescription($this->getBookingDescription($booking));
		//recreating the date with correct timezone, otherwise PHP DateTime misbehaves
		$bookedDate = new Booki_DateTime($date->format('Y-m-d'), new DateTimeZone($this->globalSettings->timezone));
		//Note: cancelled bookings are just deleted so no cancelled status.
		if($booking->status === Booki_BookingStatus::APPROVED){
			$event->setStatus('confirmed');
		}else if($booking->status === Booki_BookingStatus::PENDING_APPROVAL){
			$event->setStatus('tentative');
		}else if($booking->status === Booki_BookingStatus::CANCELLED){
			$event->setStatus('cancelled');
		}
		//$event->setLocation('');
		$start = new Google_Service_Calendar_EventDateTime();
		//$start->setTimezone($order->timezone);
		$start->setTimeZone($this->globalSettings->timezone);
		if($booking->hasTime()){
			$bookedDate->setTime($booking->hourStart, $booking->minuteStart);
			$start->setDateTime($bookedDate->format(DateTime::RFC3339));
			$event->setStart($start);
			$hourEnd = $booking->hourStart;
			$minuteEnd = $booking->minuteStart;
			if($booking->hasEndTime()){
				$hourEnd = $booking->hourEnd;
				$minuteEnd = $booking->minuteEnd;
			}
			$end = new Google_Service_Calendar_EventDateTime();
			//$end->setTimezone($order->timezone);
			$end->setTimeZone($this->globalSettings->timezone);
			$bookedDate->setTime($hourEnd, $minuteEnd);
			$end->setDateTime($bookedDate->format(DateTime::RFC3339));
			$event->setEnd($end);
		}else{
			$start->setDate($bookedDate->format($this->dateFormat));
			$event->setStart($start);
			
			$end = new Google_Service_Calendar_EventDateTime();
			$end->setDate($bookedDate->format($this->dateFormat));
			$event->setEnd($end);
		}
		
		$attendee = new Google_Service_Calendar_EventAttendee();
		$email = $order->user->email;
		$fullName = trim($order->user->firstname . ' ' . $order->user->lastname);
		
		if(!$order->userIsRegistered && $order->notRegUserEmail){
			$email = $order->notRegUserEmail;
			$fullName = trim($order->notRegUserFirstname . ' ' . $order->notRegUserLastname);
		}
		$attendee->setEmail($email);
		$attendee->setDisplayName($fullName);
		$event->attendees = array($attendee);
		return $event;
	}
	protected function syncEventLocally($event, $booking, $order){
		$bookedDayRepo = new Booki_BookedDaysRepository();
		$bookedDay = $bookedDayRepo->read($booking->id);
		//also check the order. if all days have a status of approved, then approve order as well.
		$gcalStatus = $event->getStatus();
		$bookedDay->status = Booki_BookingStatus::APPROVED;
		if($gcalStatus === 'tentative'){
			$bookedDay->status = Booki_BookingStatus::PENDING_APPROVAL;
		}else if($gcalStatus === 'cancelled'){
			$bookedDay->status = Booki_BookingStatus::CANCELLED;
		}
		Booki_BookingHelper::setBookedDayStatus($orderId, $bookedDay);
	}
	protected function insertEvent($event, $calendarId, $orderId, $bookingId){
		$created = false;
		$gcalEventsRepo = new Booki_GCalEventsRepository();
		try {
			$createdEvent = $this->service->events->insert($calendarId, $event);
			$eventId = $createdEvent->getId();
			$gcalEventsRepo->insert($calendarId, $eventId, $orderId, $bookingId);
			$created = true;
		} catch (Exception $e) {
			Booki_EventsLogProvider::insert($e);
			$created = false;
		}
		return $created;
	}
	protected function serviceActive(){
		return $this->service !== null;
	}
}
?>