<?php
/**
 * @package Booki
 * @version 7.0
/*
Plugin Name: Booki
Plugin URI: http://www.booki.io
Description: A modern booking plugin for WordPress. This plugin allows you to setup appointments or reservations with time that adapts to users timezone. You can make payment via PayPal or simply book and pay later. Make sure you read the documentation, available in PDF format within the plugin.
Version: 7.0
Author: Alessandro Zifiglio
Author URI: http://www.typps.com
License: Copyright @Alessandro Zifiglio. All rights reserved. Codecanyon licensing applies.
*/
class Booki{
	private static $instance;
	public $paypalSettings;
	public $globalSettings;
	public $resx;
	public $handlerUrls;
	public $session;
	public function __construct(){
		$this->defineConstants();
		$this->autoload();
		new Booki_Bookings();
		$this->session = new Booki_SessionWrapper();
		add_action('after_setup_theme', array($this, 'init'));
		register_activation_hook( __FILE__, array('Booki_Admin', 'install'));
		register_deactivation_hook( __FILE__, array('Booki_Admin', 'deactivate'));
	}
	public static function bootstrap(){
		if (!isset(self::$instance)){
			self::$instance = new Booki();
		}
		return self::$instance;
	}
	protected function defineConstants(){
		if(!defined('BOOKI_VERSION')){
			define('BOOKI_VERSION', '7.0');
		}
		if(!defined('BOOKI_ROOT')){
			define('BOOKI_ROOT', dirname( __FILE__ ));
		}
		if(!defined('BOOKI_LANGUAGES_FOLDER')){
			define('BOOKI_LANGUAGES_FOLDER', dirname( plugin_basename( __FILE__ ) ) . '/languages/');
		}
		if(!defined('BOOKI_PLUGINDIR')){
			define('BOOKI_PLUGINDIR', content_url() . '/plugins/' . basename(dirname( __FILE__ )) . '/'    ) ;
		}
		if(!defined('BOOKI_PAYPAL_MERCHANT_SDK')){
			define('BOOKI_PAYPAL_MERCHANT_SDK', dirname( __FILE__ ) . '/ext/paypal/merchant-sdk-php-2.1.96/');
		}
		if(!defined('BOOKI_TCPDF')){
			define('BOOKI_TCPDF', dirname( __FILE__ ) . '/ext/tcpdf/');
		}
		if(!defined('BOOKI_MAILCHIMP')){
			define('BOOKI_MAILCHIMP', dirname( __FILE__ ) . '/ext/mailchimp/src/');
		}
		if(!defined('BOOKI_GCAL')){
			define('BOOKI_GCAL', dirname( __FILE__ ) . '/ext/google-api-php-client-master/');
		}
		if(!defined('BOOKI_PLUGIN_NAME')){
			define('BOOKI_PLUGIN_NAME', basename(dirname( __FILE__ )) . '/index.php');
		}
		if(!defined('BOOKI_WP_SESSION_MANAGER')){
			define('BOOKI_WP_SESSION_MANAGER', dirname( __FILE__ ) . '/ext/wp-session-manager/');
		}
		if(!defined('BOOKI_RESTRICTED_MODE')){
			define('BOOKI_RESTRICTED_MODE',  false);
		}
		if(!defined('BOOKI_DATEFORMAT')){
			define('BOOKI_DATEFORMAT', 'Y-m-d');
		}
		if(!defined('BOOKI_FULL_DATEFORMAT')){
			define('BOOKI_FULL_DATEFORMAT', 'Y-m-d H:i');
		}
		/*if(!defined('BOOKI_USE_PHP_SESSIONS')){
			define('BOOKI_USE_PHP_SESSIONS',  false);
		}*/
	}
	protected function defineGlobals(){
		$repo = new Booki_PaypalSettingRepository();
		$this->paypalSettings = $repo->read();

		$repo = new Booki_SettingsGlobalRepository();
		$this->globalSettings = $repo->read();
		
		$repo = new Booki_ResxRepository();
		$this->resx = $repo->read();
		
		$this->handlerUrls = new Booki_HandlerHelper();
		
		if($this->globalSettings->enablePayments || $this->globalSettings->enablePayPalBilling){
			require_once BOOKI_PAYPAL_MERCHANT_SDK . 'autoload.php';
		}
	}
	protected function autoload(){
		require_once dirname(__FILE__) . '/autoload.php';
		require_once BOOKI_GCAL . 'autoload.php';
		require_once BOOKI_WP_SESSION_MANAGER . 'autoload.php';
		
		if(!class_exists('MailChimp')){
			require_once  BOOKI_MAILCHIMP . 'Mailchimp.php';
		}
	}
	
	public function init(){
		$this->defineGlobals();
		$locale = apply_filters( 'plugin_locale',  get_locale(), 'booki' );
		$mofile = sprintf('booki-%s.mo', $locale);

		$langFileLocal  = BOOKI_LANGUAGES_FOLDER . $mofile;
		$langFileGlobal = WP_LANG_DIR . '/booki/' . $mofile;

		if (file_exists($langFileGlobal)){
			//the wordpress /wp-content/languages/booki directory
			load_textdomain('booki', $langFileGlobal);
		}elseif(file_exists($langFileLocal)){
			//this plugins languages directory
			load_textdomain('booki', $langFileLocal );
		}else{
			//default language file
			load_plugin_textdomain('booki', false, BOOKI_LANGUAGES_FOLDER);
		}
		
		add_action('widgets_init', array('Booki_BasketWidget', 'init'));
		add_action('widgets_init', array('Booki_BookingsListWidget', 'init'));

		if (is_admin() ) {
			new Booki_Admin();
		} else{
			new Booki_Register();
		}
		if($this->globalSettings->noCache){
			Booki_Helper::noCache();
		}
		if($this->paypalSettings->useSandBox){
			if(!defined('BOOKI_PP_CONFIG_PATH')){
				define('BOOKI_PP_CONFIG_PATH',  BOOKI_PAYPAL_MERCHANT_SDK . 'config/debug/');
			}
		}
	}
}

function BOOKIAPP(){
	return Booki::bootstrap();
}

BOOKIAPP();
?>