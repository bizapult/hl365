<?php
class Booki_GCal extends Booki_EntityBase{
	public $userId;
	public $applicationName;
	public $clientId;
	public $clientSecret;
	public $email;
	public $username;
	public $accessToken;
	public $projectNames;
	public $projectIdList;
	public $id;
	protected $args;
	public function __construct($args){
		$this->args = $args;
		if(array_key_exists('userId', $args)){
			$this->userId = (int)$args['userId'];
		}
		if(array_key_exists('applicationName', $args)){
			$this->applicationName = (string)$args['applicationName'];
		}
		if(array_key_exists('clientId', $args)){
			$this->clientId = (string)$args['clientId'];
		}
		if(array_key_exists('clientSecret', $args)){
			$this->clientSecret = (string)$args['clientSecret'];
		}
		if(array_key_exists('username', $args)){
			$this->username = (string)$args['username'];
		}
		if(array_key_exists('email', $args)){
			$this->email = (string)$args['email'];
		}
		if(array_key_exists('accessToken', $args)){
			$this->accessToken = (string)$args['accessToken'];
		}
		if(array_key_exists('projectNames', $args)){
			$this->projectNames = (string)$args['projectNames'];
		}
		if(array_key_exists('projectIdList', $args)){
			if(!is_array($args['projectIdList'])){
				$this->projectIdList =  isset($args['projectIdList']) ? array_map('intval', explode(',', $args['projectIdList'])) : array();
			}else{
				$this->projectIdList = (array)$args['projectIdList'];
			}
		}
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'userId'=>$this->userId
			, 'applicationName'=>$this->applicationName
			, 'clientId'=>$this->clientId
			, 'clientSecret'=>$this->clientSecret
			, 'email'=>$this->email
			, 'username'=>$this->username
			, 'accessToken'=>$this->accessToken
			, 'projectNames'=>$this->projectNames
			, 'projectIdList'=>$this->projectIdList
		);
	}
}
?>