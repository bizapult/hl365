<?php
	class Booki_Admin{
		const scriptFolder = 'assets/admin/scripts/';
		const frontEndScriptFolder = 'assets/scripts/';
		const cssFolder = 'assets/admin/css/';
		const frontEndCssFolder = 'assets/css/';
		const metaKeyName = 'booki_page_type';
		private $registerScripts;
		private $globalSettings;
		public function __construct(){
			new Booki_SettingsGlobalController();
			$this->globalSettings = BOOKIAPP()->globalSettings;
			$this->registerScripts = isset($_GET['page']) ? strpos($_GET['page'], 'booki/') === 0 : false;
			add_action('admin_init', array($this, 'adminInit'));
			add_action('init', array($this, 'init'));
			add_action('wp_head', array($this, 'metaData'));
			add_action('admin_menu', array($this, 'menu'));
			if($this->registerScripts){
				add_action('admin_enqueue_scripts', array($this, 'cssIncludes'));
				add_action('admin_enqueue_scripts', array($this, 'jsIncludes'));
			}
			add_action('wp_ajax_mediaLibraryPaging', array($this, 'mediaLibraryPagingCallback'));
			add_action('load-profile.php', array($this, 'disableUserProfile'));
			add_action( Booki_EmailReminderJob::HOOK, array('Booki_EmailReminderJob', 'init'), 10, 1);
		}
		public function adminInit() {
			global $wp_version;

			// all admin functions are disabled in old versions
			if ( version_compare( $wp_version, '3.0', '<' ) ) {
				add_action('admin_notices', array($this, 'wpVersionWarning' ) );
			}
			$handlers = array('orderscsvgen', 'bookingscsvgen', 'userscsvgen', 'couponscsvgen');
			$pageIdentifier = isset($_GET['booki_handler']) ? $_GET['booki_handler'] : '';
			if(in_array($pageIdentifier, $handlers)){
				require_once dirname(__FILE__) . '/../../gen/' . $pageIdentifier . '.php';
				exit();
			}
			if(WP_DEBUG){
				remove_action( 'shutdown', 'wp_ob_end_flush_all', 1);
			}
			if (defined('DOING_AJAX') && DOING_AJAX) {
				new Booki_CalendarBridge();
				new Booki_CalendarDayBridge();
				new Booki_CalendarDaysBridge();
				new Booki_FormElementBridge();
				new Booki_OptionalBridge();
				new Booki_ProjectBridge();
				new Booki_TimeBuilderBridge();
				new Booki_UserInfoBridge();
				new Booki_TimezoneControlBridge();
				new Booki_CreateTimeSlotsBridge();
				new Booki_CascadingListBridge();
				new Booki_CascadingItemBridge();
				new Booki_QuantityElementBridge();
				new Booki_SeatsBridge();
			}
		}
		
		public function wpVersionWarning() {
			echo '
			<div class="update-nag"><p><strong> ' .__('Booki has been tested to work with WordPress 3.0 or higher. We recommend you upgrade.') . '</strong> ' . sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version.'), 'http://codex.wordpress.org/Upgrading_WordPress') . '</p></div>
			';
		}
		function disableUserProfile() {
			if(BOOKI_RESTRICTED_MODE){
				$url = admin_url() . 'admin.php?page=booki/index.php';
				wp_redirect($url);
			}
		}
		
		public function menu() {
			if ( function_exists('add_menu_page') ){
				$suffixList = array();
				
				$isAdmin = Booki_PermissionHelper::isAdmin();
				
				$projectRepo = new Booki_ProjectRepository();
				$orderRepo = new Booki_OrderRepository();
				$couponRepo = new Booki_CouponRepository();
				$userRepo = new Booki_UserRepository();
				$eventsLogRepo = new Booki_EventsLogRepository();
				
				$countLabel = '&nbsp;&nbsp;<span class="update-plugins count-%1$s"><span class="plugin-count">%1$s</span></span>';
				
				$projectsCount = sprintf($countLabel, $isAdmin ? $projectRepo->count() : 0);
				$bookingsCount = sprintf($countLabel, $isAdmin ? $orderRepo->count() : 0);
				$couponsCount = sprintf($countLabel, $isAdmin ? $couponRepo->count() : 0);
				$usersCount = sprintf($countLabel, $isAdmin ? $userRepo->count() : 0);
				$eventsLogCount = sprintf($countLabel, $isAdmin ? $eventsLogRepo->count() : 0);
				
				$superAdminCapability = 'administrator';
				$adminCapability = 'administrator';
				$manageBookingsCapability = 'administrator';
				$userHistoryCapability =  'administrator';
				
				if(BOOKI_RESTRICTED_MODE){
					$superAdminCapability = 'subscriber';
					$adminCapability = 'subscriber';
					$manageBookingsCapability = 'subscriber';
					$userHistoryCapability = 'subscriber';
					remove_menu_page('profile.php');        
				}else{
					$role = Booki_PermissionHelper::wpUserRole();
					if(!$role){
						$role = array('subscriber');
					}
					$userHistoryCapability = $role[0];
					if (!$isAdmin && Booki_PermissionHelper::hasEditorPrivileges()){
						$adminCapability = $role[0];
						$manageBookingsCapability = $role[0];
					}
				}
				
				array_push($suffixList,
					add_menu_page(
						__('Booki.io - The Booking Application for WordPress', 'booki'), 
						'Booki'. $bookingsCount, 
						$adminCapability, 
						'booki/index.php', 
						array($this, 'registerCreateProjectsPage'),   
						BOOKI_PLUGINDIR . 'assets/admin/images/icon16x16.png'
				));
				
				
				//duplicate the main menu
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Create/Edit new or existing booking projects', 'booki'), 
						__('Projects', 'booki') . $projectsCount, 
						$adminCapability, 
						'booki/index.php', 
						array($this, 'registerCreateProjectsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('New Bookings made recently', 'booki'), 
						__('Bookings', 'booki') . $bookingsCount, 
						$adminCapability, 
						'booki/viewbookings.php', 
						array($this, 'registerViewBookingsPage')
				));
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage bookings by order made', 'booki'), 
						__('Orders', 'booki') . $bookingsCount, 
						$adminCapability, 
						'booki/managebookings.php', 
						array($this,  'registerManageBookingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/managebookings.php', 
						__('Create new booking manually', 'booki'), 
						__('New bookings', 'booki'), 
						$adminCapability, 
						'booki/createbookings.php', 
						array($this, 'registerCreateBookingsPage')
				));
				//toDO: adapt coupons to editors
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage discount coupons', 'booki'), 
						__('Coupons', 'booki') . $couponsCount, 
						$adminCapability, 
						'booki/coupons.php', 
						array($this,  'registerCouponsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage users', 'booki'), 
						__('Users', 'booki') . $usersCount, 
						$superAdminCapability, 
						'booki/users.php', 
						array($this,  'registerUsersPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						'Paypal', 
						'Paypal', 
						$superAdminCapability, 
						'booki/paypal.php', 
						array($this, 'registerPaypalGatewayPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Email Settings', 'booki'), 
						__('Email Settings', 'booki'), 
						$superAdminCapability, 
						'booki/emailsettings.php', 
						array($this, 'registerEmailSettingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Invoice Settings', 'booki'), 
						__('Invoice Settings', 'booki'), 
						$superAdminCapability, 
						'booki/invoicesettings.php', 
						array($this, 'registerInvoiceSettingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Edit string resources found in the booking form', 'booki'), 
						__('String Resources', 'booki'), 
						$superAdminCapability, 
						'booki/stringresources.php', 
						array($this, 'registerResourcesPage')
				));
				
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage service providers', 'booki'), 
						__('Service providers', 'booki'),
						$superAdminCapability, 
						'booki/manageroles.php', 
						array($this,  'registerRolesPage')
				));
				
				//toDO: adapt coupons to editors
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage Google calendar profiles', 'booki'), 
						__('GCal profiles', 'booki'), 
						$adminCapability, 
						'booki/managegcal.php', 
						array($this,  'registerGCalProfilesPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('General Settings', 'booki'), 
						__('General Settings', 'booki'), 
						$superAdminCapability, 
						'booki/generalsettings.php', 
						array($this, 'registerGeneralSettingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('User history --past bookings etc', 'booki'), 
						__('Booking history', 'booki'), 
						$userHistoryCapability, 
						'booki/userhistory.php', 
						array($this, 'registerUserHistoryPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('View or undo a cancelled booking', 'booki'), 
						__('Cancel history', 'booki'), 
						$adminCapability, 
						'booki/cancelledbookings.php', 
						array($this, 'registerCancelledBookingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Record of email reminders sent out by system.', 'booki'), 
						__('Email reminders', 'booki'), 
						$adminCapability, 
						'booki/reminders.php', 
						array($this, 'registerRemindersPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Logs errors returned by Paypal, Mailchimp and Email failures', 'booki'), 
						__('Event log', 'booki') . $eventsLogCount, 
						$superAdminCapability, 
						'booki/eventslog.php', 
						array($this, 'registerEventsLogPage')
				));
				
				if($this->globalSettings->debugMode){
					array_push($suffixList, 
						add_submenu_page( 
							'booki/index.php', 
							__('Allows you to check data in the database', 'booki'), 
							__('Diagnostics', 'booki'), 
							$superAdminCapability, 
							'booki/dataviewer.php', 
							array($this, 'registerDataViewerPage')
					));
				}
				//duplicate the main menu
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('General statistics', 'booki'), 
						__('Stats', 'booki'), 
						$manageBookingsCapability, 
						'booki/stats.php', 
						array($this, 'registerCreateStatsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Uninstall', 'booki'), 
						__('Uninstall', 'booki'), 
						$superAdminCapability, 
						'booki/uninstall.php', 
						array($this, 'registerUninstallPage')
				));
			}
			global $submenu;
			unset($submenu['edit.php?post_type=booki'][10]);
		}
		
		public function registerCreateProjectsPage(){
			require_once  dirname(__FILE__) . '/../../views/CreateProjects.php';
		}
		
		public function registerManageBookingsPage(){
			require_once  dirname(__FILE__) . '/../../views/ManageBookings.php';
		}
		
		public function registerViewBookingsPage(){
			require_once  dirname(__FILE__) . '/../../views/ViewBookings.php';
		}
		
		public function registerCancelledBookingsPage(){
			require_once  dirname(__FILE__) . '/../../views/CancelledBookings.php';
		}
		
		public function registerCreateStatsPage(){
			require_once  dirname(__FILE__) . '/../../views/Stats.php';
		}
		
		public function registerCreateBookingsPage(){
			require_once  dirname(__FILE__) . '/../../views/CreateBookings.php';
		}
		
		public function registerCouponsPage(){
			require_once  dirname(__FILE__) . '/../../views/ManageCoupons.php';
		}
		
		public function registerUsersPage(){
			require_once  dirname(__FILE__) . '/../../views/ManageUsers.php';
		}
		public function registerRolesPage(){
			require_once  dirname(__FILE__) . '/../../views/ManageRoles.php';
		}
		public function registerPayPalGatewayPage(){
			require_once  dirname(__FILE__) . '/../../views/Paypal.php';
		}
		public function registerResourcesPage(){
			require_once  dirname(__FILE__) . '/../../views/StringResources.php';
		}
		public function registerGCalProfilesPage(){
			require_once  dirname(__FILE__) . '/../../views/ManageGCal.php';
		}
		public function registerGeneralSettingsPage(){
			require_once  dirname(__FILE__) . '/../../views/GeneralSettings.php';
		}
		
		public function registerInvoiceSettingsPage(){
			require_once  dirname(__FILE__) . '/../../views/InvoiceSettings.php';
		}
		
		public function registerEmailSettingsPage(){
			require_once  dirname(__FILE__) . '/../../views/EmailSettings.php';
		}
		
		public function registerUninstallPage(){
			require_once  dirname(__FILE__) . '/../../views/Uninstall.php';
		}
		
		public function registerUserHistoryPage(){
			require_once  dirname(__FILE__) . '/../../views/UserHistory.php';
		}
		
		public function registerRemindersPage(){
			require_once  dirname(__FILE__) . '/../../views/Reminders.php';
		}
		public function registerEventsLogPage(){
			require_once  dirname(__FILE__) . '/../../views/EventsLogView.php';
		}
		
		public function registerDataViewerPage(){
			require_once  dirname(__FILE__) . '/../../views/DataViewer.php';
		}
		
		public function init() {
			self::registerResources();
		}
		public function cssIncludes(){
			$pathFrontEnd = BOOKI_PLUGINDIR . self::frontEndCssFolder;
			$pathBackEnd = BOOKI_PLUGINDIR . self::cssFolder;
			
			wp_enqueue_style('booki-bootstrap', $pathBackEnd . 'bootstrap.debug.css');
			wp_enqueue_style('jquery-ui-smoothness', $pathBackEnd . 'jquery-ui/smoothness/jquery-ui-1.10.3.custom.css');
			wp_enqueue_style('jquery.minicolors', $pathBackEnd . 'minicolors/jquery.minicolors.css');
			wp_enqueue_style('booki-fullcalendar', $pathBackEnd . 'fullcalendar.min.css');
			wp_enqueue_style('booki-fullcalendar-print', $pathBackEnd . 'fullcalendar.print.css', array(), false, 'print');
			wp_enqueue_style('jquery-qtip', $pathBackEnd . 'jquery.qtip.css', array(), false);
			
			if($this->globalSettings->debugMode){
				wp_enqueue_style('booki-frontend', $pathFrontEnd . 'booki.debug.css');
				wp_enqueue_style('booki-backend', $pathBackEnd . 'booki.admin.debug.css');
			}else{
				wp_enqueue_style('booki-frontend', $pathFrontEnd . 'booki.min.css');
				wp_enqueue_style('booki-backend', $pathBackEnd . 'booki.admin.min.css');
			}
		}
		
		public function jsIncludes(){
			$pathFrontEnd = BOOKI_PLUGINDIR . self::frontEndScriptFolder;
			$pathBackEnd = BOOKI_PLUGINDIR . self::scriptFolder;

			wp_enqueue_script('jquery', array(), '', true);
			wp_enqueue_script('json2', array(), '', true);
			wp_enqueue_script('jquery-ui-datepicker', array(), '', true);
			wp_enqueue_script('underscore', array(), '', true);
			wp_enqueue_script('backbone', array(), true);
			wp_enqueue_script('moment', $pathBackEnd . 'moment.min.js', array(), '', true);
			wp_enqueue_script('bootstrap', $pathBackEnd . 'bootstrap/bootstrap.min.js', array(), '', true);
			wp_enqueue_script('jquery.minicolors', $pathBackEnd . 'jquery.minicolors.js', array(), '', true);
			wp_enqueue_script('parsley', $pathBackEnd . 'parsley.min.js', array(), '', true);
			wp_enqueue_script('accounting', $pathBackEnd . 'accounting.min.js', array(), '', true);
			wp_enqueue_script('jsTimezoneDetect', $pathBackEnd . 'jstz.min.js', array(), '', true);
			wp_enqueue_script('booki-fullcalendar', $pathBackEnd . 'fullcalendar.min.js', array(), '', true);
			wp_enqueue_script('jquery-qtip', $pathBackEnd . 'jquery.qtip.min.js', array(), '', true);
			
			if($this->globalSettings->debugMode){
				wp_enqueue_script('booki-frontend', $pathFrontEnd . 'booki.debug.js', array(), '', true);
				wp_enqueue_script('booki-backend', $pathBackEnd . 'booki.admin.debug.js', array(), '', true);
			}else{
				wp_enqueue_script('booki-frontend', $pathFrontEnd . 'booki.1.0.min.js', array(), '', true);
				wp_enqueue_script('booki-backend', $pathBackEnd . 'booki.admin.1.0.min.js', array(), '', true);
			}
			Booki_ScriptHelper::enqueueDatePickerLocale();
			Booki_ScriptHelper::enqueueParsleyLocale();
		}
		
		private static function readText($path){
			$content = '';
			if ($handle = fopen($path, 'rb')) {
				$len = filesize($path);
				if ($len > 0){
					$content = fread($handle, $len);
				}
				fclose($handle);
			}
			return trim($content);
		}
		
		private static function getSqlScript($file_name){
			$path = BOOKI_ROOT  . '/assets/sql/' . $file_name;
			return Booki_Helper::readText($path);
		}

		public static function install(){
			global $wpdb;
			$charset_collate = '';
			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}
			
			$dbVersion = floatval(get_option('booki_db_version'));
			$scriptFileName = 'create_booki.sql';
			$sql = null;
			$newVersion = floatval(self::getSqlScript('version.txt'));
			try{
				if($dbVersion !== $newVersion){
					//clear out pages, they will get freshly regenerated.
					self::registerCustomPages();
					self::clearSessions();
					$sql = self::getSqlScript($scriptFileName);
					if($sql){
						$sql = str_replace('prefix_', $wpdb->prefix, $sql);
						$sql = str_replace('charset_collate_placeholder', $charset_collate, $sql);
						require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
						dbDelta($sql);
						update_option('booki_db_version', $newVersion);
					}
				}
				
			} catch (Exception $e) {
				update_option('booki_db_error', $e->getMessage());
			}
		}
		
		
		public static function uninstall(){
			global $wpdb;
			$sql = self::getSqlScript('drop_booki.sql');
			
			if(!$sql){
				return;
			}

			$sql = str_replace('prefix_', $wpdb->prefix, $sql);
			$statements = explode(';', $sql);
			$length = count($statements) - 1;

			try{
				self::deleteResources();
				for($i = 0; $i < $length; $i++){
					$stmt = $statements[$i];
					$wpdb->query($stmt);
				}	
				delete_option('booki_db_version');
				delete_option('booki_db_error');
				self::clearSessions();
			
				include_once( ABSPATH . 'wp-admin/includes/plugin.php');
				if(is_plugin_active(BOOKI_PLUGIN_NAME)) {
					deactivate_plugins(BOOKI_PLUGIN_NAME);
					wp_redirect(admin_url('plugins.php?deactivate=true&plugin_status=all&paged=1'));
					exit();
				}
			} catch (Exception $e) {
				 add_option('booki_db_error', $e->getMessage());
			}
		}
		
		public static function registerCustomPages(){
			$registered = (bool)get_option('booki_custom_pages_registered');
			if($registered){
				return false;
			}
			$created = true;
			//clear out any previous custom pages created.
			self::deleteAllCustomPages();
			try{
				$pages = new WP_Query(array( 
					'meta_key'=>self::metaKeyName
					, 'post_type'=>'page'
				));
				$pageAttributes = array(
					array('pageType'=>Booki_PageNames::CART
							, 'title'=>'Booki - Cart'
							, 'content'=>'[booki-cart]'
						)
						, array('pageType'=>Booki_PageNames::PAYPAL_HANDLER
							, 'title'=>'Booki - Billing'
							, 'content'=>'[booki-bill]'
						)
						, array('pageType'=>Booki_PageNames::PAYPAL_CONFIRMATION_HANDLER
							, 'title'=>'Booki - Paypal payment confirmation'
							, 'content'=>'[booki-ppconfirmation]'
						)
						, array('pageType'=>Booki_PageNames::PAYPAL_CANCEL_HANDLER
							, 'title'=>'Booki - Paypal payment cancel'
							, 'content'=>'[booki-ppcancel]'
						)
						, array('pageType'=>Booki_PageNames::BOOKING_VIEW
							, 'title'=>'Booki - List item'
							, 'content'=>'[booki-itemdetails]'
						)
						, array('pageType'=>Booki_PageNames::HISTORY_PAGE
							, 'title'=>'Booki - History'
							, 'content'=>'[booki-history]'
						)
						, array('pageType'=>Booki_PageNames::STATS_PAGE
							, 'title'=>'Booki - Stats'
							, 'content'=>'[booki-stats]'
						)
				);
				
				foreach($pageAttributes as $attrs){
					if(!self::hasPage($pages, $attrs['pageType'])){
						$id = wp_insert_post(array(
							'post_title'=>$attrs['title']
							, 'post_content'=>$attrs['content']
							, 'post_type'=>'page'
							, 'post_status'=>'publish'
							, 'show_ui'=>false
							, 'show_in_menu' =>false
							, 'show_in_admin_bar'=>false
							, 'comment_status'=> 'closed'
							, 'ping_status'=>'closed'
						));
						add_post_meta($id, self::metaKeyName, $attrs['pageType']);
					}
				}
				update_option('booki_custom_pages_registered', true);
			}catch(Exception $e){
				Booki_EventsLogProvider::insert($e);
				$created = false;
			}
			return $created;
		}
		
		protected static function hasPage($pages, $pageId){
			foreach($pages->posts as $page){
				$result = get_post_meta($page->ID, self::metaKeyName, true);
				if($result != '' && (int)$result == $pageId){
					return true;
				}
			}
			return false;
		}
		
		public static function deleteAllCustomPages(){
			$args = array( 
				'meta_key'=>self::metaKeyName
				, 'post_type'=>'page'
			);
			try{
				$pages = new WP_Query($args);
				foreach($pages->posts as $page){
					wp_delete_post( $page->ID, true);
				}
				update_option('booki_custom_pages_registered', false);
			}catch(Exception $e){
				Booki_EventsLogProvider::insert($e);
			}
		}
		public static function deactivate(){
			wp_clear_scheduled_hook('Booki_ResetAppJobEventHook');
			wp_clear_scheduled_hook('Booki_ExpiredBookingsJobEventHook');
			wp_clear_scheduled_hook('Booki_ExpiredEventsLogJobEventHook');
			Booki_EmailReminderJob::cancelAllSchedules();
			self::clearSessions();
		}
		
		public static function registerResources(){
			$resx = BOOKIAPP()->resx;
			$resx->updateResources();
			Booki_WPMLHelper::registerEmailResource();
		}
		
		public static function deleteResources(){
			//delete wpml resources
			$resx = BOOKIAPP()->resx;
			$resx->deleteResources();
			
			$projectRepo = new Booki_ProjectRepository();
			$projects = $projectRepo->readAll();
			foreach($projects as $project){
				$project->deleteResources();
			}
			
			$templateNames = Booki_Helper::systemEmails();
			foreach($templateNames as $templateName){
				Booki_WPMLHelper::unregister('email_template_' . str_replace(' ', '_', $templateName));
			}
		}
		
		public static function clearDatabase(){
			global $wpdb;
			$sql = self::getSqlScript('clear_booki.sql');
			self::clearSessions();
			
			if(!$sql){
				return;
			}
			
			$sql = str_replace('prefix_', $wpdb->prefix, $sql);
			$statements = explode(';', $sql);

			$length = count($statements) - 1;
			try{
				for($i = 0; $i < $length; $i++){
					$stmt = $statements[$i];
					$wpdb->query($stmt);
				}	
			} catch (Exception $e) {
				 add_option('booki_db_error', $e->getMessage());
			}
		}
		
		public static function myISAMReady(){
			global $wpdb;
			$sql = self::getSqlScript('myisam_booki.sql');
			
			if(!$sql){
				return;
			}
			
			$sql = str_replace('prefix_', $wpdb->prefix, $sql);
			$statements = explode(';', $sql);

			$length = count($statements) - 1;
			try{
				for($i = 0; $i < $length; $i++){
					$stmt = $statements[$i];
					$wpdb->query($stmt);
				}	
			} catch (Exception $e) {
				 add_option('booki_db_error', $e->getMessage());
			}
		}
		
		public static function clearSessions(){
			BOOKIAPP()->session->delete('Booki_Bookings');
			BOOKIAPP()->session->delete('Booki_MailChimpList');
		}
		
		public function mediaLibraryPagingCallback(){
			//required workaround for ajax
			$GLOBALS['hook_suffix'] = '';
			set_current_screen();
			//now get your image listing.
			$imageList = new Booki_ImageList();
			$imageList->prepare_items();
			$imageList->display();
			die();
		}
		
		public function metaData(){
			echo '<meta name="plugins" content="POWERED BY BOOKI ' . BOOKI_VERSION . '. A BOOKING PLUGIN FOR WORDPRESS." />';
		}
	}
?>
