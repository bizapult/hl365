<?php
class Booki_FormElementRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $form_element_table_name;
	private $order_form_elements_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->form_element_table_name = $wpdb->prefix . 'booki_form_element';
		$this->order_form_elements_table_name = $wpdb->prefix . 'booki_order_form_elements';
	}
	
	public function readAll($projectId){
		$sql = "SELECT   id, 
						 projectId, 
						 label, 
						 elementType, 
						 CAST(lineSeparator AS UNSIGNED INTEGER) AS lineSeparator, 
						 rowIndex, 
						 colIndex, 
						 className, 
						 value, 
						 bindingData, 
						 CAST(once AS UNSIGNED INTEGER) AS once, 
						 capability, 
						 validation 
				FROM     $this->form_element_table_name 
				WHERE    projectId = %d 
				ORDER BY rowIndex ASC";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if( is_array( $result )){
			$subResult = $this->readAllColsAndRows($projectId);
			$formElements = new Booki_FormElements();
			foreach($result as $r){
				$formElements->add(new Booki_FormElement((array)$r));
			}
			foreach($subResult as $sr){
				$formElements->cols = $sr->cols;
				$formElements->rows = $sr->rows;
			}
			return $formElements;
		}
		return false;
	}
	
	public function readAllColsAndRows($projectId){
		$sql = "SELECT MAX(rowIndex) AS rows, 
					   MAX(colIndex) AS cols 
				FROM   $this->form_element_table_name 
				WHERE  projectId = %d 
				ORDER  BY colIndex, 
						  rowIndex ASC";
		return $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
	}
	
	public function read($id){
		$sql = "SELECT id, 
					   projectId, 
					   label, 
					   elementType, 
					   CAST(lineSeparator AS UNSIGNED INTEGER) AS lineSeparator, 
					   rowIndex, 
					   colIndex, 
					   className, 
					   value, 
					   bindingData, 
					   CAST(once AS UNSIGNED INTEGER) AS once, 
					   capability, 
					   validation 
				FROM   $this->form_element_table_name 
				WHERE  id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if ($result ){
			$r = $result[0];
			return new Booki_FormElement((array)$r);
		}
		return false;
	}
	
	public function insert($formElement){
		 $result = $this->wpdb->insert($this->form_element_table_name,  array(
			'projectId'=>$formElement->projectId
			, 'label'=>$this->encode($formElement->label)
			, 'elementType'=>$formElement->elementType
			, 'lineSeparator'=>$formElement->lineSeparator
			, 'rowIndex'=>$formElement->rowIndex
			, 'colIndex'=>$formElement->colIndex
			, 'className'=>$this->encode($formElement->className)
			, 'value'=>$this->encode($formElement->value)
			, 'bindingData'=>implode(',', $formElement->bindingData)
			, 'once'=>$formElement->once
			, 'capability'=>$formElement->capability
			, 'validation'=>http_build_query($formElement->validation)
		 ), array(
			'%d'/*projectId*/
			, '%s'/*label*/
			, '%d'/*elementType*/
			, '%d'/*lineSeparator*/
			, '%d'/*rowIndex*/
			, '%d'/*colIndex*/
			, '%s'/*className*/
			, '%s'/*value*/
			, '%s'/*bindingData*/
			, '%d'/*once*/
			, '%d'/*capability*/
			, '%s'/*validation*/
		 ));
		 if($result !== false){
			$formElement->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($formElement){
		$result = $this->wpdb->update($this->form_element_table_name,  array(
			'label'=>$this->encode($formElement->label)
			, 'elementType'=>$formElement->elementType 
			, 'lineSeparator'=>$formElement->lineSeparator
			, 'rowIndex'=>$formElement->rowIndex
			, 'colIndex'=>$formElement->colIndex
			, 'className'=>$this->encode($formElement->className)
			, 'value'=>$this->encode($formElement->value)
			, 'bindingData'=>implode(',', $formElement->bindingData)
			, 'once'=>$formElement->once
			, 'capability'=>$formElement->capability
			, 'validation'=>http_build_query($formElement->validation)
		), 
		array('id'=>$formElement->id),
		array(
			'%s'/*label*/
			, '%d'/*elementType*/
			, '%d'/*lineSeparator*/
			, '%d'/*rowIndex*/
			, '%d'/*colIndex*/
			, '%s'/*className*/
			, '%s'/*value*/
			, '%s'/*bindingData*/
			, '%d'/*once*/
			, '%d'/*capability*/
			, '%s'/*validation*/
			, '%d'/*id*/
		));
		$formElement->updateResources();
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources($id);
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_table_name WHERE id = %d", $id) );
	}
	
	public function deleteResources($id){
		$formElement = $this->read($id);
		if($formElement){
			$formElement->deleteResources();
		}
	}
}
?>