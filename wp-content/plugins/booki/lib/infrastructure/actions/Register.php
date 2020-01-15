<?php
class Booki_Register{
    
    const scriptFolder = 'assets/scripts/';
    const cssFolder = 'assets/css/';
	private $pages;
	private $globalSettings;
	public function __construct(){
		$this->globalSettings = BOOKIAPP()->globalSettings;
		$args = array( 
			'meta_key'=>'booki_page_type'
			, 'hierarchical' => 0
		);
		$this->pages = get_pages($args);

		add_action('init', array($this, 'init'));
		add_action('wp_head', array($this, 'metaData'));
		add_action('wp_enqueue_scripts', array($this, 'cssIncludes'), 99);
		add_action('wp_enqueue_scripts', array($this, 'jsIncludes'));
		//[booki id="2"]
		add_shortcode( 'booki-booking', array($this, 'processShortCodeBooking'));
		add_shortcode( 'booki-list', array($this, 'processShortCodeList'));
		add_shortcode( 'booki-basket', array($this, 'processShortCodeBasket'));

		add_shortcode( 'booki-cart', array($this, 'processShortCodeCart'));
		add_shortcode( 'booki-bill', array($this, 'processShortCodeBillSettlement'));
		add_shortcode( 'booki-ppconfirmation', array($this, 'processShortCodePayPalConfirmation'));
		add_shortcode( 'booki-ppcancel', array($this, 'processShortCodePayPalCancel'));
		add_shortcode( 'booki-itemdetails', array($this, 'processShortCodeItemDetails'));
		add_shortcode( 'booki-history', array($this, 'processShortCodeHistory'));
		add_shortcode( 'booki-stats', array($this, 'processShortCodeStats'));

		add_filter('wp_get_nav_menu_items', array($this, 'excludeFromRegisteredMenu'), 10, 3);
		add_filter('wp_page_menu_args', array($this, 'excludeFromDefaultMenu'), 10, 1);
		//add_action('user_register', array($this, 'registrationComplete'), 10, 1);
		//add_action('wp_login', array($this, 'loginComplete'), 10, 2);
		
		add_filter('register', array($this, 'customRegisterUrl'));
		add_filter('registration_redirect', array($this, 'customRegistrationRedirect'));
		add_action( Booki_EmailReminderJob::HOOK, array('Booki_EmailReminderJob', 'init'), 10, 1);
		
		if (!(defined('DOING_AJAX') && DOING_AJAX)) {
			add_action( Booki_ExpiredBookingsJob::HOOK, array('Booki_ExpiredBookingsJob', 'init'), 10, 1);
			add_action( Booki_ExpiredEventsLogJob::HOOK, array('Booki_ExpiredEventsLogJob', 'init'), 10, 1);
			new Booki_ExpiredBookingsJob();
			new Booki_ExpiredEventsLogJob();
		}
		new Booki_TimeBuilderBridge();
		new Booki_TimezoneControlBridge();
		new Booki_SeatsBridge();
	}
	public function customRegisterUrl( $registrationUrl ) {
		$redirectTo = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '';
		$flag = isset($_GET['booki']) ? $_GET['booki'] : '';
		if($redirectTo && $flag) {
			// change query name values to prevent default behaviour (redirect_to to uct_redirect)
			$registrationUrl = sprintf( '<a href="%s&%s">%s</a>', esc_url( wp_registration_url() ), 'uct_redirect=' . urlencode($redirectTo),  __('Register'));
		}
		
		return $registrationUrl;
	}

	public function customRegistrationRedirect($registrationRedirect) {
		$redirectTo = isset($_GET['uct_redirect']) ? $_GET['uct_redirect'] : '';
		$flag = isset($_GET['booki']) ? $_GET['booki'] : '';
		if($redirectTo && $flag) {
			$registrationRedirect = wp_login_url( $redirectTo );
		}

		return $registrationRedirect;
	}
	

	function loginComplete($userLogin, $user){}

	function registrationComplete($userId) {
		$cart = new Booki_Cart();
		$bookings = $cart->getBookings();
		$cartEmpty = $bookings->count() === 0;
		
		if($this->globalSettings->autoLoginAfterRegistration){
			wp_set_auth_cookie( $userId, false, is_ssl() );
			if($cartEmpty){
				wp_safe_redirect(home_url('/'));
				exit;
			}
		}
		
		if($this->globalSettings->autoLoginAfterRegistration && ($this->globalSettings->useCartSystem && !$cartEmpty)){
			Booki_Helper::redirect(Booki_PageNames::CART);
			exit;
		}
	}

	public function excludeFromDefaultMenu($args){
		$excludes = array();
		foreach($this->pages as $page){
			array_push($excludes, $page->ID);
		}
		$args['exclude'] = implode(',', $excludes);
		return $args;
	}

	public function excludeFromRegisteredMenu( $items, $menu, $args ) {
		// Iterate over the items to search and destroy
		foreach ( $items as $key => $item ) {
			foreach($this->pages as $page){
				if ( $item->object_id === $page->ID ) {
					unset( $items[$key] );
				}
			}
		}
		return $items;
	}

	public function init(){
		ob_start();
		$handlers = array('invoicegen');
		$pageIdentifier = isset($_GET['booki_handler']) ? $_GET['booki_handler'] : '';
		if(in_array($pageIdentifier, $handlers)){
			require_once dirname(__FILE__) . '/../../gen/' . $pageIdentifier . '.php';
			exit();
		}
		$this->gcal();
	}
	
	function gcal(){
		$val = isset($_GET['code']) && isset($_GET['state']) ? $_GET['state'] : null;
		if($val){
			$state = json_decode(Booki_Helper::base64UrlDecode($val), true);
			if(isset($state['booki_action']) && $state['booki_action'] === 'gcal'){
				new Booki_GCalService((int)$state['booki_userid'], $_GET['code']);
				$redirectUrl = get_admin_url() . 'admin.php?page=booki/managegcal.php';
				wp_redirect($redirectUrl);
				exit();
			}
		}
	}
	
	function processShortCodeBooking( $atts, $content = null ) {
		extract( shortcode_atts( array( 'id' => '-1' ), $atts ) );
		$id = intval($id);
		
		$render = new Booki_Render();
		return $render->booking($id);
	}
	
	function processShortCodeList( $atts, $content = null ) {
		$listArgs = array(
			'tags'=>isset($atts['tags']) ? $atts['tags'] : ''
			, 'dispalyAllResultsByDefault'=>isset($atts['displayallresultsbydefault']) ? filter_var($atts['displayallresultsbydefault'], FILTER_VALIDATE_BOOLEAN) : true
			, 'heading'=>isset($atts['heading']) ? $atts['heading'] : __('Find a booking', 'booki')
			, 'fromLabel'=>isset($atts['fromlabel']) ? $atts['fromlabel'] : __('Check-in', 'booki')
			, 'toLabel'=>isset($atts['tolabel']) ? $atts['tolabel'] : __('Check-out', 'booki')
			, 'perPage'=>isset($atts['perpage']) ? intval($atts['perpage']) : 5
			, 'fullPager'=>isset($atts['fullpager']) ? filter_var($atts['fullpager'], FILTER_VALIDATE_BOOLEAN) : true
			, 'enableSearch'=>isset($atts['enablesearch']) ? filter_var($atts['enablesearch'], FILTER_VALIDATE_BOOLEAN) : true
			, 'enableItemHeading'=>isset($atts['enableitemheading']) ? filter_var($atts['enableitemheading'], FILTER_VALIDATE_BOOLEAN) : false
		);
		
		$render = new Booki_Render();
		return $render->bookingList($listArgs);
	}
	
	function processShortCodeBasket($atts, $content = null){
		$render = new Booki_Render();
		return $render->basket();
	}
	
	public function processShortCodeCart(){
		$render = new Booki_Render();
		return $render->cart();
	}

	public function processShortCodeBillSettlement(){
		$render = new Booki_Render();
		return $render->payPalBillSettlement();
	}

	public function processShortCodePayPalConfirmation(){
		$render = new Booki_Render();
		return $render->payPalPaymentConfirmation();
	}

	public function processShortCodePayPalCancel(){
		$render = new Booki_Render();
		return $render->payPalPaymentCancel();
	}

	public function processShortCodeItemDetails(){
		$render = new Booki_Render();
		return $render->bookingItemDetails();
	}
	
	public function processShortCodeHistory(){
		$render = new Booki_Render();
		return $render->historyPage();
	}
	
	public function processShortCodeStats(){
		$render = new Booki_Render();
		return $render->statsPage();
	}
	
    public function cssIncludes(){
		$bootstrapRoot =  BOOKI_PLUGINDIR;
		$bookiFrontEndImport = null;
		$calendarStyleSheet = sprintf('jquery-ui/%s/jquery-ui-1.10.3.custom.css', $this->globalSettings->calendarTheme);
		$calendarThemeFile = BOOKI_PLUGINDIR . self::cssFolder . $calendarStyleSheet;
		if($this->globalSettings->theme !== '-1'){
			$dirUri = get_stylesheet_directory_uri() . '/booki/' . $this->globalSettings->theme . '/' . self::cssFolder;
			$dir = get_stylesheet_directory() . '/booki/' . $this->globalSettings->theme . '/' . self::cssFolder;
			if(file_exists($dir . 'bootstrap.min.css')){
				$bootstrapRoot = $dirUri;
			}
			if(file_exists($dir . 'booki.import.css')){
				$bookiFrontEndImport = $dirUri;
			}
			//calendar is in the booki root directory i.e. /booki/assets/css and not /booki/themename/assets/css
			if(file_exists($dir . $calendarStyleSheet)){
				$calendarThemeFile = $dirUri . $calendarStyleSheet;
			}
		}
		if($this->globalSettings->refBootstrapStyleSheet){
			wp_enqueue_style( 'booki-bootstrap', $bootstrapRoot . self::cssFolder . 'bootstrap.min.css');
		}
		if($this->globalSettings->calendarTheme){
			wp_enqueue_style( 'jquery-ui-' . $this->globalSettings->calendarTheme, $calendarThemeFile);
		}
		if($this->globalSettings->debugMode){
			wp_enqueue_style( 'booki-frontend', BOOKI_PLUGINDIR . self::cssFolder . 'booki.debug.css' . '?booki=' . BOOKI_VERSION);
		} else{
			wp_enqueue_style( 'booki-frontend', BOOKI_PLUGINDIR . self::cssFolder . 'booki.min.css' . '?booki=' . BOOKI_VERSION);
		}
		if($bookiFrontEndImport !== null){
			wp_enqueue_style( 'booki-frontend-import', $bookiFrontEndImport . 'booki.import.css');
		}
    }
    
    public function jsIncludes(){
		wp_enqueue_script('jquery' );
		wp_enqueue_script('jquery-ui-datepicker');
		Booki_ScriptHelper::enqueueDatePickerLocale();
		if($this->globalSettings->refMomentJS){
			wp_enqueue_script('booki-moment', BOOKI_PLUGINDIR . self::scriptFolder . 'moment.min.js', array(), false, true);
		}
		if($this->globalSettings->refBootstrapJS){
			wp_enqueue_script('booki-bootstrap', BOOKI_PLUGINDIR . self::scriptFolder . 'bootstrap/bootstrap.min.js', array(), false, true);
		}
		if($this->globalSettings->refParsleyJS){
			wp_enqueue_script('parsely', BOOKI_PLUGINDIR . self::scriptFolder . 'parsley.min.js', array(), false, true);
			Booki_ScriptHelper::enqueueParsleyLocale();
		}
		wp_enqueue_script('accounting', BOOKI_PLUGINDIR . self::scriptFolder . 'accounting.min.js', array(), false, true);
		wp_enqueue_script('jsTimezoneDetect', BOOKI_PLUGINDIR . self::scriptFolder . 'jstz.min.js', array(), false, true);
		if($this->globalSettings->debugMode){
			wp_enqueue_script('booki-frontend', BOOKI_PLUGINDIR . self::scriptFolder . 'booki.debug.js' . '?booki=' . BOOKI_VERSION, array(), false, true);
		} else{
			wp_enqueue_script('booki-frontend', BOOKI_PLUGINDIR . self::scriptFolder . 'booki.1.0.min.js' . '?booki=' . BOOKI_VERSION, array(), false, true);
		}
    }
	
	public function metaData(){
		if($this->globalSettings->enableMobileInitialScale){
			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		}
	}
}
?>