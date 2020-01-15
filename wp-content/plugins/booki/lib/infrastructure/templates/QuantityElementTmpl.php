<?php
class Booki_QuantityElementTmpl{
	public $data;
	public $projectId;
	public function __construct(){
		$this->projectId = apply_filters( 'booki_shortcode_id', null);
		if($this->projectId === null || $this->projectId === -1){
			$this->projectId = apply_filters( 'booki_project_id', null);
		}
		$builder = new Booki_QuantityElementBuilder($this->projectId);
		$this->data = $builder->result;
	}
}
?>