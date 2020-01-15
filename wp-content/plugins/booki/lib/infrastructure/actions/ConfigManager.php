<?php
/**
 * BKConfigManager loads the paypal merchant SDK configuration file and
 * allows reading/writing to it.
 */
 class Booki_ConfigManager {

	private $config;
	private $configFile;
	/**
	 * @var BKConfigManager
	 */
	private static $instance;

	private function __construct($configFile){
		if($configFile){
			$this->configFile = $configFile;
		}
		else{
			$this->configFile = constant('BOOKI_PAYPAL_MERCHANT_SDK') . 'config/sdk_config.ini';
		}
		$this->load($this->configFile);
	}

	// create singleton object for BKConfigManager
	public static function getInstance($configFile = null)
	{
		if ( !isset(self::$instance) || $configFile !== self::$instance->configFile) {
			self::$instance = new Booki_ConfigManager($configFile);
		}
		return self::$instance;
	}
	
	//used to load the file
	private function load($fileName) {
		//some hosts disable parse_ini_file so working around instead of reading from file.
		$this->config = strpos($fileName, 'debug') === false ? Booki_PayPalHelper::getLiveConfigParams() : Booki_PayPalHelper::getSandboxConfigParams();
	}
	
	public function getConfigs(){
		return $this->config;
	}
}