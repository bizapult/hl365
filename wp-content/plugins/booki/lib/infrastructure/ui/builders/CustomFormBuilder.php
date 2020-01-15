<?php
class Booki_CustomFormBuilder{
	private $projectId;
	public $result;
	public function __construct($projectId){
		$this->projectId = $projectId;
		
		$formElementRepository = new Booki_FormElementRepository();
		$formElements = $formElementRepository->readAll($projectId);
		
		$this->result = new Booki_CustomFormElements($projectId, $formElements);
	}
}
?>