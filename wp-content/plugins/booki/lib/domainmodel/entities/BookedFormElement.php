<?php
class Booki_BookedFormElement extends Booki_EntityBase{
	public $id = -1;
	public $projectId;
	public $orderId;
	public $label;
	public $label_loc;
	public $elementType;
	public $rowIndex;
	public $colIndex;
	public $value;
	public $capability;
	public function __construct($args){
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if(array_key_exists('label', $args)){
			$this->label = $this->decode((string)$args['label']);
		}
		if(array_key_exists('elementType', $args)){
			$this->elementType = (int)$args['elementType'];
		}
		if(array_key_exists('rowIndex', $args)){
			$this->rowIndex= (int)$args['rowIndex'];
		}
		if(array_key_exists('colIndex', $args)){
			$this->colIndex = (int)$args['colIndex'];
		}
		if(array_key_exists('value', $args)){
			$this->value = $this->decode((string)$args['value']);
		}
		if(array_key_exists('capability', $args)){
			$this->capability = (int)$args['capability'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	protected function init(){
		$this->label_loc = Booki_WPMLHelper::t('form_field_' . $this->label . '_label_project' . $this->projectId, $this->label);
	}
}
?>