<?php
class Booki_QuantityElementBuilder{
	public $projectId;
	public $result;
	public function __construct($projectId){
		$this->projectId = $projectId;
		$quantityElementRepository = new Booki_QuantityElementRepository();
		$quantityElementRepository->readAllByProjectId($this->projectId);
		$this->result = $quantityElementRepository->readAllByProjectId($this->projectId);
	}
}
?>