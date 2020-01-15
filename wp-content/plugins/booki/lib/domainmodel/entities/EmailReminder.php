<?php
class Booki_EmailReminder extends Booki_EntityBase{
	public $id;
	public $orderId;
	public $sentDate;
	public $firstname;
	public $lastname;
	public $email;
	public function __construct($args){
		if(array_key_exists('firstname', $args)){
			$this->firstname = $args['firstname'];
		}
		if(array_key_exists('lastname', $args)){
			$this->lastname = $args['lastname'];
		}
		if(array_key_exists('email', $args)){
			$this->email = $args['email'];
		}
		if(array_key_exists('sentDate', $args)){
			$this->sentDate = $args['sentDate'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'orderId'=>$this->orderId
			, 'firstname'=>$this->firstname
			, 'lastname'=>$this->lastname
			, 'email'=>$this->email
			, 'sentDate'=>$this->sentDate
			, 'id'=>$this->id
		);
	}
}
?>