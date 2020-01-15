<?php
class Booki_FormElement extends Booki_EntityBase{
	public $id;
	public $projectId;
	public $label;
	public $label_loc;
	public $elementType;
	public $lineSeparator;
	public $rowIndex;
	public $colIndex;
	public $className;
	public $value;
	public $bindingData;
	public $once;
	public $validation = array();
	public $attributes;
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
		if(array_key_exists('lineSeparator', $args)){
			$this->lineSeparator = (bool)$args['lineSeparator'];
		}
		if(array_key_exists('rowIndex', $args)){
			$this->rowIndex= (int)$args['rowIndex'];
		}
		if(array_key_exists('colIndex', $args)){
			$this->colIndex = (int)$args['colIndex'];
		}
		if(array_key_exists('className', $args)){
			$this->className = $this->decode((string)$args['className']);
		}
		if(array_key_exists('value', $args)){
			$this->value = strip_tags($this->decode((string)$args['value']));
		}
		if(array_key_exists('bindingData', $args)){
			$this->bindingData =  is_array($args['bindingData']) ? $args['bindingData'] : explode(',', (string)$args['bindingData']);
		}
		if(array_key_exists('once', $args)){
			$this->once = (bool)$args['once'];
		}
		if(array_key_exists('capability', $args)){
			$this->capability = (int)$args['capability'];
		}
		if(array_key_exists('validation', $args)){
			if(is_string($args['validation'])){
				parse_str($args['validation'], $output);
				$v = new Booki_Validation($output);
				$this->validation = $v->getAllConstraints();
			}else{
				$this->validation = (array)$args['validation'];
			}
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->attributes = $this->getValidationAttributes();
		$this->updateResources();
		$this->init();
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'label'=>$this->label
			, 'elementType'=>$this->elementType
			, 'lineSeparator'=>$this->lineSeparator
			, 'rowIndex'=>$this->rowIndex
			, 'colIndex'=>$this->colIndex
			, 'className'=>$this->className
			, 'value'=>$this->value
			, 'bindingData'=>$this->bindingData
			, 'once'=>$this->once
			, 'capability'=>$this->capability
			, 'validation'=>$this->validation
		);
	}
	
	protected function getValidationAttributes(){
		$result = array();
		if(!$this->validation){
			return $result;
		}
		foreach($this->validation as $key=>$val){
			if($val !== null){
				switch($key){
					case 'required':
						if($val){
							array_push($result, 'data-parsley-required="true"');
						}
					break;
					case 'minLength':
						if($val){
							array_push($result, sprintf('data-parsley-minlength="%s"', $val));
						}
					break;
					case 'maxLength':
						if($val){
							array_push($result, sprintf('data-parsley-maxlength="%s"', $val));
						}
					break;
					case 'min':
						if($val){
							array_push($result, sprintf('data-parsley-min="%s"', $val));
						}
					break;
					case 'max':
						if($val){
							array_push($result, sprintf('data-parsley-max="%s"', $val));
						}
					break;
					case 'regex':
						if($val){
							array_push($result, sprintf('data-parsley-pattern="%s"', $val));
						}
					break;
					case 'email':
						if($val){
							array_push($result, 'data-parsley-type="email"');
						}
					break;
					case 'url':
						if($val){
							array_push($result, 'data-parsley-type="url"');
						}
					break;
					case 'digits':
						if($val){
							array_push($result, 'data-parsley-type="digits"');
						}
					break;
					case 'number':
						if($val){
							array_push($result, 'data-parsley-type="number"');
						}
					break;
					case 'alphanum':
						if($val){
							array_push($result, 'data-parsley-type="alphanum"');
						}
					break;
					case 'dateIso':
						if($val){
							array_push($result, 'data-parsley-type="dateIso"');
						}
					break;
				}
			}
		}
		if(count($result) > 0){
			array_push($result, 'data-parsley-trigger="change"');
		}
		return implode(' ', $result);
	}
	
	protected function init(){
		$this->label_loc = Booki_WPMLHelper::t('form_field_' . $this->label . '_label_project' . $this->projectId, $this->label);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('form_field_' . $this->label . '_label_project' . $this->projectId, $this->label);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('form_field_' . $this->label . '_label_project' . $this->projectId);
	}
}
?>