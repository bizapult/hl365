<?php
class Booki_RefundController extends Booki_BaseController{
	public function __construct($refundCallback = null, $cancelCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_refund')){
			return;
		}
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		if (array_key_exists('refund', $_POST)){
			$this->refund($refundCallback);
		}else if(array_key_exists('cancel', $_POST)){
			$this->cancel($cancelCallback);
		}
	}
	
	public function refund($callback){
		$orderId = (int)$this->getPostValue('orderId');
		$bookedDayId = isset($_POST['bookedDayId']) ? (int)$_POST['bookedDayId'] : null;
		$bookedOptionalId = isset($_POST['bookedOptionalId']) ? (int)$_POST['bookedOptionalId'] : null;
		$bookedCascadingItemId = isset($_POST['bookedCascadingItemId']) ? (int)$_POST['bookedCascadingItemId'] : null;
		$bookedQuantityElementId = isset($_POST['bookedQuantityElementId']) ? (int)$_POST['bookedQuantityElementId'] : null;
		$refundSource = $this->getPostValue('refundSource');
		$amount = $this->getPostValue('amount');
		$refundType = $this->getPostValue('refundType');
		$memo = $this->getPostValue('memo');
		$retryUntil = null;
		$ppRefund = new Booki_PPRefund(array(
			'orderId'=>$orderId
			, 'refundSource'=>$refundSource
			, 'amount'=>$amount
			, 'refundType'=>$refundType
			, 'memo'=>$memo
			, 'retryUntil'=>$retryUntil
			, 'bookedDayId'=>$bookedDayId
			, 'bookedOptionalId'=>$bookedOptionalId
			, 'bookedCascadingItemId'=>$bookedCascadingItemId
			, 'bookedQuantityElementId'=>$bookedQuantityElementId
		));
		$result = $ppRefund->refundTransaction();
		
		$this->executeCallback($callback, array($result));
	}
	
	public function cancel($callback){
		$this->executeCallback($callback/*, array($param)*/);
	}
}
?>