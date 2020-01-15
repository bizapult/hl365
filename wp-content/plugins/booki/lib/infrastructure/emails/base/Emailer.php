<?php
class Booki_Emailer{
	private $emailSettingRepository;
	protected $emailSettings;
	protected $emailType;
	protected $globalSettings;
	protected $displayTimezone;
	public function __construct($emailType){
		$this->emailType = $emailType;
		$this->emailSettingRepository = new Booki_EmailSettingRepository($emailType);
		$this->emailSettings = $this->emailSettingRepository->read();
		$this->globalSettings = BOOKIAPP()->globalSettings;
		if(!$this->emailSettings){
			$this->emailSettings = new Booki_EmailSetting();
			$this->emailSettings->content = Booki_Helper::readEmailTemplate($emailType);
		}
		$this->emailSettings->templateName = str_replace(' ', '_', $emailType);
		$this->emailSettings->init();
		$this->displayTimezone = $this->globalSettings->displayTimezone();
	}
	
	protected function logErrorIfAny($result){
		if (!$result) {
			global $ts_mail_errors;
			global $phpmailer;
			if (!isset($ts_mail_errors)){
				$ts_mail_errors = array();
			}
			if (isset($phpmailer)) {
				array_push($ts_mail_errors, $phpmailer->ErrorInfo);
			}
			
			Booki_EventsLogProvider::insert($ts_mail_errors);
		}
	}
	
	public function setLayout($content){
		$siteName = get_bloginfo('name');
		$siteDescription = get_bloginfo('description');
		$siteUrl = site_url();
		$year = date("Y");
		$emailSettingRepository = new Booki_EmailSettingRepository(Booki_EmailType::MASTER_TEMPLATE);
		$emailSetting = $emailSettingRepository->read();
		if(!$emailSetting){
			$emailSetting = new Booki_EmailSetting();
		}
		$masterTemplateContent = $emailSetting->content;
		if(!trim($masterTemplateContent)){
			$masterTemplateContent = Booki_Helper::readEmailTemplate(Booki_EmailType::MASTER_TEMPLATE);
		}
		$masterTemplateContent = str_ireplace("%siteName%", $siteName, $masterTemplateContent);
		$masterTemplateContent = str_ireplace("%siteDescription%", $siteDescription, $masterTemplateContent);
		$masterTemplateContent = str_ireplace("%siteUrl%", $siteUrl, $masterTemplateContent);
		$masterTemplateContent = str_ireplace("%year%", $year, $masterTemplateContent);
		$masterTemplateContent = str_ireplace("%content%", $content, $masterTemplateContent);
		return $masterTemplateContent;
	}
}
?>