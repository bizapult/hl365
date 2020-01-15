<?php
class Booki_Coupon extends Booki_EntityBase{
	public $code;
	public $emailedTo;
	
	public $id = -1;
	public $discount = 0;
	public $orderMinimum = 0;
	public $expirationDate;
	public $projectId = -1;
	public $projectName;
	public $couponType = Booki_CouponType::REGULAR;
	public function __construct($args){
		if(array_key_exists('discount', $args)){
			$this->discount = (double)$args['discount'];
		};
		if(array_key_exists('orderMinimum', $args)){
			$this->orderMinimum = (double)$args['orderMinimum'];
		};
		if(array_key_exists('expirationDate', $args)){
			$this->expirationDate = new Booki_DateTime($args['expirationDate']);
		};
		if(array_key_exists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		};
		if(array_key_exists('projectName', $args)){
			$this->projectName = (int)$args['projectName'];
		};
		if(array_key_exists('code', $args)){
			$this->code = (string)$args['code'];
		};
		if(array_key_exists('emailedTo', $args)){
			$this->emailedTo = (string)$args['emailedTo'];
		};
		if(array_key_exists('couponType', $args)){
			$this->couponType = (int)$args['couponType'];
		};
		if(array_key_exists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'discount'=>$this->discount
			, 'orderMinimum'=>$this->orderMinimum
			, 'expirationDate'=>$this->expirationDate
			, 'code'=>$this->code
			, 'emailedTo'=>$this->emailedTo
			, 'projectId'=>$this->projectId
			, 'projectName'=>$this->projectName
			, 'couponType'=>$this->couponType
		);
	}
	
	public function isValid(){
		if(!$this->expirationDate){
			return false;
		}
		$today = new Booki_DateTime();
		try{
			$result = strtotime($today->format('Y-m-d')) <= strtotime($this->expirationDate->format('Y-m-d'));
		}catch(Exception $ex){
			$result = false;
		}
		return $result;
	}
	

	public function deduct($totalAmount){
		if($this->isValid() && $this->discount > 0){
			$totalAmount = Booki_Helper::calcDiscount($this->discount, $totalAmount);
		}
		return $totalAmount;
	}
	
	public function expire(){
		if($this->isValid()){
			$expirationDate = new Booki_DateTime();
			$expirationDate->modify('-1 day');
			$this->expirationDate = $expirationDate;
		}
	}
}
?>