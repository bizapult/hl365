<?php

class Booki_PPBase{
	private $paypalSettings;
	private $globalSettings;
	private $order;
	private $orderSummary;
	private $user;
	private $coupon;
	private $resx;
	public function __construct($order = null, $coupon = null){
		$this->order = $order;
		if(!$this->order){
			$this->orderLogger = new Booki_OrderLogger();
			$this->order = $this->orderLogger->order;
		}
		$this->coupon = $coupon;
		$paypalSettingRepo = new Booki_PaypalSettingRepository();
		$globalSettingsRepo = new Booki_SettingsGlobalRepository();
		
		$this->paypalSettings = $paypalSettingRepo->read();
		$this->globalSettings = $globalSettingsRepo->read();
		$this->resx = BOOKIAPP()->resx;
	}
	
	public function checkout(){
		if(!$this->order || $this->order->status === Booki_PaymentStatus::PAID){
			return false;
		}
		$returnUrl = Booki_Helper::getUrl(Booki_PageNames::PAYPAL_CONFIRMATION_HANDLER);
		$cancelUrl = Booki_Helper::getUrl(Booki_PageNames::PAYPAL_CANCEL_HANDLER);
		$currency = $this->paypalSettings->currency;
		$paymentDetails = new PaymentDetailsType();
		$i = 0;
		$deposits = array();
		
		if(isset($this->orderLogger)){
			$this->order = $this->orderLogger->log(true);
		}
		
		$bs = new Booki_BillSettlement(array('order'=>$this->order, 'coupon'=>$this->coupon));
		foreach($this->order->bookedDays as $day){
			$flag = false;
			if($day->cost == 0 || $bs->data->hasDeposit){
				$flag = true;
			}
			if(!$flag){
				$itemDetails = new PaymentDetailsItemType();
				$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($day->cost));
				$itemDetails->Name = $this->resx->BOOKING_FOR_LOC . ' ' . Booki_Helper::formatDate( $day->bookingDate);
				if($day->hasTime()){
					$itemDetails->Name .= ',  ' . Booki_TimeHelper::formatTime($day, $this->order->timezone, $day->enableSingleHourMinuteFormat);
				}

				$itemDetails->Quantity = 1;
				$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
				$itemDetails->ProductCategory = $this->resx->DAYS_BOOKED_LOC;

				$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;	
			}
			foreach($this->order->bookedQuantityElements as $quantityElement){
				if($quantityElement->cost == 0 || $bs->data->hasDeposit || $quantityElement->orderDayId !== $day->id){
					continue;
				}
				$itemDetails = new PaymentDetailsItemType();
				$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($quantityElement->getCalculatedCost()));
				$name = $this->removeSpecialChars($quantityElement->getName());
				if($flag){
					$name = $this->resx->BOOKING_FOR_LOC . ' ' . Booki_Helper::formatDate( $day->bookingDate) . ' - ' . $name;
				}
				$itemDetails->Name = $name;
				$itemDetails->Quantity = 1;
				$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
				$itemDetails->ProductCategory = $this->resx->QUANTITY_ELEMENT_LOC;
				
				$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
			}
		}
		
		
		foreach($this->order->bookedOptionals as $optional){
			if($optional->cost == 0 || $bs->data->hasDeposit){
				continue;
			}
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($optional->getCalculatedCost()));
			$itemDetails->Name = $this->removeSpecialChars($optional->getName());
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->resx->EXTRAS_LOC;
			
			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
		}
		
		foreach( $this->order->bookedCascadingItems as $cascadingItem ){
			if($cascadingItem->cost == 0 || $bs->data->hasDeposit){
				continue;
			}
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($cascadingItem->getCalculatedCost()));
			$itemDetails->Name = $this->removeSpecialChars($cascadingItem->getName());
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->resx->EXTRAS_LOC;
			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
		}

		if($bs->data->hasDeposit){
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, $bs->data->deposit);
			$itemDetails->Name = trim($this->removeSpecialChars($bs->data->depositProjectName) . ' - ' . $this->resx->DEPOSIT);
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->resx->DEPOSIT;
			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
		}
		
		if($bs->data->discount > 0){
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, '-' . Booki_Helper::toMoney($bs->data->discountValue));
			$itemDetails->Name = sprintf($this->resx->DISCOUNT_BY_PERCENTAGE_LOC, $bs->data->discount) . '%';
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $bs->data->discountName;

			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
		}
		$paymentDetails->ItemTotal = new BasicAmountType($currency, $bs->data->formattedTotalAmountBeforeTax);
		if($bs->data->formattedTaxAmount > 0){
			$paymentDetails->TaxTotal = new BasicAmountType($currency, $bs->data->formattedTaxAmount);
		}
		
		$paymentDetails->OrderTotal = new BasicAmountType($currency, $bs->data->formattedTotalAmountIncludingTax);
		$setECReqDetails = new SetExpressCheckoutRequestDetailsType();
		$setECReqDetails->PaymentDetails[0] = $paymentDetails;
		$setECReqDetails->CancelURL = $cancelUrl;
		$setECReqDetails->ReturnURL = $returnUrl;
		
		// Display options
		$setECReqDetails->cppheaderimage = $this->paypalSettings->headerImage;
		$setECReqDetails->cppheaderbordercolor = $this->parseColor($this->paypalSettings->headerBorderColor);
		$setECReqDetails->cppheaderbackcolor = $this->parseColor($this->paypalSettings->headerBackColor);
		$setECReqDetails->cpppayflowcolor = $this->parseColor($this->paypalSettings->payFlowColor);
		$setECReqDetails->cppcartbordercolor = $this->parseColor($this->paypalSettings->cartBorderColor);
		$setECReqDetails->cpplogoimage = $this->paypalSettings->logo;
		$setECReqDetails->PageStyle = $this->paypalSettings->customPageStyle;
		$setECReqDetails->BrandName = $this->paypalSettings->brandName;

		// Advanced options
		$setECReqDetails->AllowNote = (int)$this->paypalSettings->allowBuyerNote;

		if(isset($this->orderLogger) && $bs->data->discount > 0){
			$this->order->discount = $bs->data->discount;
			Booki_BookingProvider::orderRepository()->update($this->order);
		}
		$user = null;
		if($this->order && $this->order->userIsRegistered){
			$user = Booki_Helper::getUserInfo($this->order->userId);
		} else{
			$user = Booki_BookingProvider::getNonRegContactInfo($this->order->id);
		}
		if($user){
			$setECReqDetails->BuyerEmail = $user['email'];
		}

		/*
			Seeing duplicate invoice error: 
				Log in to your Paypal account and go to Profile > 
					Payment Receiving Preferences and under Block accidental payments select No, 
					allow multiple payments per invoice ID
		*/
		$setECReqDetails->InvoiceID = $this->order->id;
		/*
			offering Guest Checkout by setting SOLUTIONTYPE=Sole and LandingPage=Billing.
			This will greatly increase the ability of your account to offer Guest Checkout.
			You should see Guest Checkout much more often now.
		*/
		if($this->paypalSettings->enableGuestCheckout){
			$setECReqDetails->SolutionType = 'Sole';
			$setECReqDetails->LandingPage = 'Billing';
		}
		if($this->coupon){
			$setECReqDetails->Custom = $this->coupon->code ? $this->coupon->code : $this->coupon->discount;
		}
		
		$setECReqType = new SetExpressCheckoutRequestType();
		$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
		$setECReq = new SetExpressCheckoutReq();
		$setECReq->SetExpressCheckoutRequest = $setECReqType;
		
		$signatureCredentials = new PPSignatureCredential(
				$this->paypalSettings->username
				, $this->paypalSettings->password
				, $this->paypalSettings->signature
		);
		if($this->paypalSettings->appId)
		{
			@$signatureCredentials->setApplicationId($this->paypalSettings->appId);
		}
		$paypalService = new PayPalAPIInterfaceServiceService();
		
		try {
			$setECResponse = @$paypalService->SetExpressCheckout($setECReq, $signatureCredentials);
		} catch (Exception $ex) {
			Booki_EventsLogProvider::insert($ex);
			return false;
		}

		if(isset($setECResponse) && $setECResponse->Ack == 'Success') 
		{
			$this->tokenize($setECResponse->Token);
			$host = $this->paypalSettings->useSandBox ? 'https://www.sandbox.paypal.com/' : 'https://www.paypal.com/';
			$url = $host . 'webscr?cmd=_express-checkout&token=' . $this->order->token;
			wp_redirect($url);
		}else{
			Booki_EventsLogProvider::insert($setECResponse);
		}
		return false;
	}

	public function tokenize($token){
		$this->order->token = $token;
		Booki_BookingProvider::update($this->order);
	}
	
	protected function toMoney($val)
	{
		return Booki_Helper::toMoney($val);
	}
	
	protected function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
	protected function removeSpecialChars($value){
		return $value;
	}
	/**
		@description Color must not contain the hash symbol and must be 6 characters in length.
	*/
	protected function parseColor($value)
	{
		if(strlen($value) > 6)
		{
			return substr($value, 1, 6);
		}
		return $value;
	}
}
?>