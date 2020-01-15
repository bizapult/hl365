<?php
class Booki_BillSettlementController extends Booki_BaseController{
	
	public $orderId;
	public function __construct(){
		$couponCallback = null;
		$checkoutCallback = null;
		$numArgs = func_num_args();
		if($numArgs > 0){
			$couponCallback = func_get_arg(0);
		}
		if($numArgs > 1){
			$checkoutCallback = func_get_arg(1);
		}
		if(isset($_GET['orderid'])){
			$this->orderId = (int)$_GET['orderid'];
		}
		if(!$this->orderId){
			return;
		}
		if (array_key_exists('booki_checkout', $_POST)){
			$this->checkout($checkoutCallback);
		}else if(array_key_exists('booki_redeem_coupon', $_POST)){
			$this->redeemCoupon($couponCallback);
		}else if(array_key_exists('booki_cancel_coupon', $_POST)){
			$this->cancelCoupon($couponCallback);
		}
	}
	
	public function redeemCoupon($callback){
		$couponCode = trim($this->getPostValue('booki_couponcode'));
		if($couponCode){
			$repo = new Booki_CouponRepository();
			$coupon = $repo->find($couponCode);
			$message = null;
			if($coupon){
				$bs = new Booki_BillSettlement(array('orderId'=>$this->orderId, 'coupon'=>$coupon));
				$totalAmount = $bs->data->totalAmount;
				if($totalAmount < $coupon->orderMinimum){
					$message = sprintf(__('Coupon is valid only on bookings that exceed %s.', 'booki'),  Booki_Helper::getCurrencySymbol() . Booki_Helper::toMoney($coupon->orderMinimum));
				}else if(!$coupon->isValid()){
					$message = __('Coupon has expired and is not valid anymore. No discount applied.', 'booki');
				}
			}else{
				$message = __('Coupon is not valid. No discount applied.', 'booki');
			}
			$this->executeCallback($callback, array($coupon, $message, $coupon));
		}
	}
	
	public function cancelCoupon($callback){
		$this->executeCallback($callback, array(null, null));
	}
	
	public function checkOut($failureCallback){
		$couponCode = trim($this->getPostValue('booki_couponcode'));
		$coupon = null;
		$globalSettings = BOOKIAPP()->globalSettings;
		$discount = $globalSettings->discount;
		$bookingMinimumDiscount = $globalSettings->bookingMinimumDiscount;
		
		$hasDiscount = false;
		if($discount > 0 && $bookingMinimumDiscount > 0){
			$bookedDaysRepo = new Booki_BookedDaysRepository();
			$bookedDays = $bookedDaysRepo->readByOrder($this->orderId);
			if($bookedDays->count() >= $bookingMinimumDiscount){
				$hasDiscount = true;
			}
		}
		
		$order = Booki_BookingProvider::read($this->orderId);
		$now = new Booki_DateTime();
		if($order->discount > 0){
			$coupon = new Booki_Coupon(array('discount'=>$order->discount, 'orderMinimum'=>1, 'expirationDate'=>$now->format(BOOKI_DATEFORMAT)));
		}
		else if($hasDiscount){
			$coupon = new Booki_Coupon(array('discount'=>$discount, 'orderMinimum'=>$bookingMinimumDiscount, 'expirationDate'=>$now->format(BOOKI_DATEFORMAT)));
		}else if($couponCode){
			$repo = new Booki_CouponRepository();
			$coupon = $repo->find($couponCode);
			if(!$coupon || !$coupon->isValid()){
				$message = __('Coupon code is invalid and has been removed. Provide a valid coupon for discount or proceed without.', 'booki');
				$this->executeCallback($failureCallback, array(null, $message));
				return;
			}
		}

		if($globalSettings->enablePayments || $globalSettings->enablePayPalBilling){
			$paypalGateway = new Booki_PPGateway($order, $coupon);
			$paypalGateway->checkout();
			return;
		}
		
		$message = __('Payments are currently disabled. Contact us for further assistance.', 'booki');
		$this->executeCallback($failureCallback, array($coupon, $message));
	}
}
?>