<?php
if(file_exists(ABSPATH . 'wp-admin/includes/class-wp-screen.php') && !class_exists('WP_Screen')){
	require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
}
require_once ABSPATH . 'wp-admin/includes/screen.php';
class Booki_BookingWizardTmpl{
	public $projectId;
	public $data;
	public $project;
	public $isBackEnd;
	public $steps = array();
	public $goToCartUrl;
	public $orderHistoryUrl;
	public $cartEmpty;
	public $globalSettings;
	public $checkoutSuccessMessage;
	public $orderId = null;
	public $errors = array();
	public $resx;
	public $attendeeList;
	public function __construct($hasCustomFormFields){
		$this->projectId = apply_filters( 'booki_shortcode_id', null);
		if($this->projectId === null || $this->projectId === -1){
			$this->projectId = apply_filters( 'booki_project_id', null);
		}
		$this->isBackEnd = apply_filters( 'booki_is_backend', null);
		$repo = new Booki_ProjectRepository();
		$this->project = $repo->read($this->projectId);
		
		
		$defaultStep = $this->project->defaultStep;
		
		$bookingTabStep = array(
				'id'=>'bookingtab' . $this->projectId
				, 'step'=>0
				, 'defaultStep'=>$defaultStep
				, 'name'=>$this->project->bookingTabLabel_loc
		);
		if($hasCustomFormFields){
			array_push($this->steps, 
			array(
					'id'=>'detailstab' . $this->projectId
					, 'step'=>1
					, 'defaultStep'=>$defaultStep
					, 'name'=>$this->project->customFormTabLabel_loc
			));
		}
		
		if($this->project->displayAttendees){
			if(!isset($GLOBALS['hook_suffix'])){
				$GLOBALS['hook_suffix'] = '';
				set_current_screen();
			}
			$this->attendeeList = new Booki_AttendeeList($this->projectId);
			$this->attendeeList->bind();
			array_push($this->steps, 
			array(
					'id'=>'attendeetab' . $this->projectId
					, 'step'=>2
					, 'defaultStep'=>$defaultStep
					, 'name'=>$this->project->attendeeTabLabel_loc
			));
		}
		
		if(count($this->steps) > 0){
			array_push($this->steps, $bookingTabStep);
			usort($this->steps, array($this, 'sortByDefaultStep'));
		}
	
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$this->resx = BOOKIAPP()->resx;
		
		$postProjectId = isset($_POST['projectid']) ? (int)$_POST['projectid'] : -1;
		if($this->projectId === $postProjectId){
			new Booki_WizardController(array($this, 'addToCartCallback'), array($this, 'checkoutCallback'));
		}
		$orderBuilder = new Booki_OrderSummaryBuilder();
		$this->data = $orderBuilder->result;
		
		$cart = new Booki_Cart();
		$bookings = $cart->getBookings();
		$this->cartEmpty = $bookings->count() === 0;
		
		if($this->globalSettings->useDashboardHistoryPage){
			$this->orderHistoryUrl = admin_url() . 'admin.php?page=booki/userhistory.php';
		}else{
			$this->orderHistoryUrl = Booki_Helper::getUrl(Booki_PageNames::HISTORY_PAGE);
		}
		$this->goToCartUrl = Booki_Helper::appendReferrer(Booki_Helper::getUrl(Booki_PageNames::CART));
		$this->errors = apply_filters( 'booki_custom_form_errors', null);
		//Booki_Helper::noCache();
	}
	
	public function sortByDefaultStep($a, $b){
		if($a['step'] == $a['defaultStep']){
			return 0;
		}
		if($b['step'] == $a['defaultStep']){
			return 1;
		}
		return ($a['step'] < $b['step']) ? -1 : 1;
	}
	
	public function addToCartCallback($cart, $projectId, $errors){}
	
	public function checkoutCallback($errorMessage, $orderId = null){
		$this->checkoutSuccessMessage = $errorMessage;
		$this->orderId = $orderId;
	}
}

?>