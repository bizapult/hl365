<?php
class Booki_ListTmpl{
	public $projectList;
	public $heading;
	public $fromDate;
	public $toDate;
	public $fromLabel;
	public $toLabel;
	public $enableSearch;
	public $isWidget;
	public $uniqueKey;
	public $dateFormat;
	public $altFormat;
	public $calendarCssClasses;
	public $calendarFirstDay;
	public $showCalendarButtonPanel;
	public $enableItemHeading;
	public $dispalyAllResultsByDefault;
	public $tags;
	public $headingLength;
	public $descriptionLength;
	public $fullPager;
	public $perPage;
	public $projectId;
	public function __construct(){
		$listArgs = apply_filters('booki_list', null);
		$this->tags = $listArgs['tags'];
		$this->headingLength = isset($listArgs['headingLength']) ? intval($listArgs['headingLength']) : 0;
		$this->descriptionLength = isset($listArgs['descriptionLength']) ? intval($listArgs['descriptionLength']) : 0;
		$this->perPage = intval($listArgs['perPage']);
		$this->fullPager = $listArgs['fullPager'];
		$this->dispalyAllResultsByDefault = $listArgs['dispalyAllResultsByDefault'];
		$this->enableItemHeading = $listArgs['enableItemHeading'];
		$this->heading = $listArgs['heading'];
		$this->fromLabel = $listArgs['fromLabel'];
		$this->toLabel = $listArgs['toLabel'];
		$this->enableSearch = $listArgs['enableSearch'];
		$this->isWidget = isset($listArgs['widget']);
		$this->projectId = isset($listArgs['projectId']) ? (int)$listArgs['projectId'] : -1;
		$this->uniqueKey = uniqid();
		$globalSettings = BOOKIAPP()->globalSettings;
		$this->dateFormat = $globalSettings->shorthandDateFormat;
		$this->altFormat = Booki_DateHelper::getJQueryCalendarFormat($this->dateFormat);
		$calendarStyles = array();
		if($globalSettings->calendarFlatStyle){
			array_push($calendarStyles, 'booki-flat');
		}
		if($globalSettings->calendarBorderlessStyle){
			array_push($calendarStyles, 'booki-borderless');
		}
		$this->calendarCssClasses = implode(' ', $calendarStyles);
		$this->calendarFirstDay = $globalSettings->calendarFirstDay;
		$this->showCalendarButtonPanel = $globalSettings->showCalendarButtonPanel ? 'true' : 'false';
		new Booki_SearchControlController($listArgs, array($this, 'search'));
		if (!(array_key_exists('controller', $_GET) 
			&& $_GET['controller'] == 'booki_searchcontrol')){
			return;
		}
		$this->fromDate = $_GET['fromDate'];
		$this->toDate = $_GET['toDate'];
	}
	public function search($result){
		$this->projectList = $result;
	}
}
?>