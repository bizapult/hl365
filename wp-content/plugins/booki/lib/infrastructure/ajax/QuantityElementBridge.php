<?php
class Booki_QuantityElementBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readAllQuantityElementsByCalendarId', array($this, 'readAllByCalendarIdCallback')); 
		add_action('wp_ajax_booki_readAllQuantityElementsByCalendarDayIdList', array($this, 'readAllByCalendarDayIdListCallback')); 
		add_action('wp_ajax_booki_readQuantityElement', array($this, 'readCallback')); 
		add_action('wp_ajax_booki_insertQuantityElement', array($this, 'insertQuantityElementCallback'));
		add_action('wp_ajax_booki_insertQuantityElementItem', array($this, 'insertQuantityElementItemCallback'));
		add_action('wp_ajax_booki_updateQuantityElement', array($this, 'updateQuantityElementCallback'));
		add_action('wp_ajax_booki_updateQuantityElementItem', array($this, 'updateQuantityElementItemCallback')); 
		add_action('wp_ajax_booki_deleteQuantityElement', array($this, 'deleteQuantityElementCallback'));
		add_action('wp_ajax_booki_deleteQuantityElementItem', array($this, 'deleteQuantityElementItemCallback'));
		add_action('wp_ajax_booki_deleteQuantityElementItems', array($this, 'deleteQuantityElementItemsCallback'));
	}
	
	public function readAllByCalendarIdCallback(){

		echo Booki_QuantityElementJSONProvider::readAllByCalendarId();
		
		die();
	}
	
	public function readAllByCalendarDayIdListCallback(){

		echo Booki_QuantityElementJSONProvider::readAllByCalendarDayIdList();
		
		die();
	}
	
	
	public function readCallback(){

		echo Booki_QuantityElementJSONProvider::read();
		
		die();
	}
	
	public function insertQuantityElementCallback(){

		echo Booki_QuantityElementJSONProvider::insertQuantityElement();
		
		die();
	}
	
	public function insertQuantityElementByDaysCallback(){
		
		echo Booki_QuantityElementJSONProvider::insertQuantityElementByDays();
		
		die();
	}
	
	public function insertQuantityElementItemCallback(){

		echo Booki_QuantityElementJSONProvider::insertQuantityElementItem();
		
		die();
	}
	
	public function updateQuantityElementCallback(){

		echo Booki_QuantityElementJSONProvider::updateQuantityElement();
		
		die();
	}
	
	public function updateQuantityElementByDaysCallback(){
		
		echo Booki_QuantityElementJSONProvider::updateQuantityElementByDays();
		
		die();
	}
	
	public function updateQuantityElementItemCallback(){

		echo Booki_QuantityElementJSONProvider::updateQuantityElementItem();
		
		die();
	}
	
	public function deleteQuantityElementCallback(){

		echo Booki_QuantityElementJSONProvider::deleteQuantityElement();
		
		die();
	}
	
	public function deleteQuantityElementItemCallback(){

		echo Booki_QuantityElementJSONProvider::deleteQuantityElementItem();
		
		die();
	}
	
	public function deleteQuantityElementItemsCallback(){

		echo Booki_QuantityElementJSONProvider::deleteQuantityElementItems();
		
		die();
	}
}
?>
