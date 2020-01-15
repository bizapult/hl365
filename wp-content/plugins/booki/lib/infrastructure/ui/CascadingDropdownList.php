<?php
class Booki_CascadingDropdownList{
	public $cascadingLists;
	public $currency;
	public $currencySymbol;
	public $optionalsBookingMode;
	public function __construct(Booki_CascadingLists $cascadingLists, $project, $currency, $currencySymbol){
		$this->cascadingLists = $cascadingLists;
		$this->currency = $currency;
		$this->currencySymbol = $currencySymbol;
		$this->optionalsBookingMode = $project->optionalsBookingMode;
	}
}
?>