<?php
class Booki_BillSettlement
{
	public $data;
	public $order;
	public function __construct($args)
	{
		$projectId = null;
		$discount = null;
		$coupon = null;
		$orderId = null;
		if(isset($args['orderId'])){
			$orderId = $args['orderId'];
			$this->order = Booki_BookingProvider::read($orderId);
		}else if(isset($args['order'])){
			$this->order = $args['order'];
			$orderId = $this->order->id;
		}
		if(isset($args['discount'])){
			$discount = $args['discount'];
		}
		if(isset($args['coupon'])){
			$coupon = $args['coupon'];
		}
		if(isset($args['projectId'])){
			$projectId = $args['projectId'];
		}
		if(!$this->order){
			return;
		}
		$result = Booki_BookingHelper::getBookings($this->order, $projectId);
		$this->data = new Booki_OrderSummary(array(
			'bookings'=>$result['bookings']
			, 'refundTotal'=>$result['refundTotal']
			, 'discount'=>$discount ? $discount : $this->order->discount
			, 'validate'=>false
			, 'coupon'=>$coupon
			, 'orderId'=>$orderId
		));
	}
}
?>