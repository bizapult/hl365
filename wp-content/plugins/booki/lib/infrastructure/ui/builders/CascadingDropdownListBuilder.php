<?php
class Booki_CascadingDropdownListBuilder{
	public $projectId;
	public $result;
	public function __construct($projectId){
		$this->projectId = $projectId;
		$cascadingListRepository = new Booki_CascadingListRepository();
		$cascadingList = $cascadingListRepository->readAllTopLevel($projectId);
		$cascadingLists = $cascadingListRepository->readItemsByLists($cascadingList);
		$projectRepository = new Booki_ProjectRepository();
		$project = $projectRepository->read($projectId);
		$localeInfo = Booki_Helper::getLocaleInfo();
		
		$currency = $localeInfo['currency'];
		$currencySymbol = $localeInfo['currencySymbol'];
		
		$this->result = new Booki_CascadingDropdownList($cascadingLists, $project, $currency, $currencySymbol);
	}
}
?>