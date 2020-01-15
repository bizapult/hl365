<?php
if(!class_exists('TCPDF')){
	require_once  BOOKI_TCPDF . 'tcpdf.php';
}

class Booki_NotificationEmailer extends Booki_Emailer{
	protected $invoiceSettings;
	protected $orderId;
	protected $bookedDay = null;
	protected $bookedOptional = null;
	protected $bookedCascadingItem = null;
	protected $bookedQuantityElement = null;
	protected $refundAmount;
	protected $currency;
	protected $currencySymbol;
	protected $dateFormat;
	protected $userInfo;
	protected $order;
	public function __construct($args)
	{	
		$emailType = null;
		if(array_key_exists('emailType', $args)){
			$emailType = $args['emailType'];
		}
		if(array_key_exists('orderId', $args)){
			$this->orderId = $args['orderId'];
		}
		if(array_key_exists('bookedDayId', $args)){
			$bookedDaysRepo = new Booki_BookedDaysRepository();
			$this->bookedDay = $bookedDaysRepo->read($args['bookedDayId']);
		}
		if(array_key_exists('bookedOptionalId', $args)){
			$bookedOptionalRepo = new Booki_BookedOptionalsRepository();
			$this->bookedOptional = $bookedOptionalRepo->read($args['bookedOptionalId']);
		}
		if(array_key_exists('bookedCascadingId', $args)){
			$bookedCascadingItemRepo = new Booki_BookedCascadingItemsRepository();
			$this->bookedCascadingItem = $bookedCascadingItemRepo->read($args['bookedCascadingId']);
		}
		if(array_key_exists('bookedQuantityElementId', $args)){
			$bookedQuantityElementRepo = new Booki_BookedQuantityElementRepository();
			$this->bookedQuantityElement = $bookedQuantityElementRepo->read($args['bookedQuantityElementId']);
		}
		if(array_key_exists('refundAmount', $args)){
			$this->refundAmount = $args['refundAmount'];
		}
		if(array_key_exists('userInfo', $args)){
			$this->userInfo = $args['userInfo'];
		}
		
		$localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol =	$localeInfo['currencySymbol'];
		$this->dateFormat = get_option('date_format');
		
		$invoiceSettingRepository = new Booki_InvoiceSettingRepository();
		$this->invoiceSettings = $invoiceSettingRepository->read();
		
		parent::__construct($emailType);
	}
	
	public function getUserInfo(){

		if($this->userInfo){
			return $this->userInfo;
		}
		
		if($this->order && $this->order->userIsRegistered){
			return Booki_Helper::getUserInfo($this->order->userId);
		}
		
		return Booki_BookingProvider::getNonRegContactInfo($this->orderId);
	}
	
	public function getCustomerInfo(){
		if($this->order && $this->order->userIsRegistered){
			return Booki_Helper::getUserInfo($this->order->userId);
		}
		
		return Booki_BookingProvider::getNonRegContactInfo($this->orderId);
	}
	
	public function send($projectId = null, $to = null){
		$emailSettings = $this->emailSettings;
		if($this->orderId && ($emailSettings && $emailSettings->enable)){
			$bs = new Booki_BillSettlement(array('orderId'=>$this->orderId, 'projectId'=>$projectId));
			$this->order = $bs->order;
			$userInfo = $this->getUserInfo();
			$customerName = '';
			if($userInfo){
				$customerName = $userInfo['name'];
			}
			if($to === null){
				$to = $userInfo['email'];
			}
			$content = $this->getEmailBody($bs->data);
			
			$subject = $emailSettings->subject;
			if(!$subject){
				$subject = __($this->emailType, 'booki');
			}
			add_filter('wp_mail_content_type', array($this, 'setHtmlContentType'));
			add_filter('wp_mail_from', array($this, 'getSenderAddress'));
			add_filter('wp_mail_from_name',array($this, 'getSenderName'));
			
			$content = $this->setLayout($content);
			$result = wp_mail( $to, $subject, $content);

			remove_filter('wp_mail_from', array($this, 'getSenderAddress'));
			remove_filter('wp_mail_from_name',array($this, 'getSenderName'));
			remove_filter( 'wp_mail_content_type', array($this, 'setHtmlContentType'));
			$this->logErrorIfAny($result);
			return $result;
		}
		return false;
	}
	public function getSenderAddress(){
		return $this->emailSettings->senderEmail;
	}
	
	public function getSenderName(){
		return  $this->emailSettings->senderName;
	}
	public function generateInvoice(){
		if(!$this->orderId)
		{
			return;
		}
		$bs = new Booki_BillSettlement(array('orderId'=>$this->orderId));
		$this->order = $bs->order;
		$userInfo = $this->getUserInfo();
		$content = $this->invoice($bs->data, $userInfo);
		
		return $this->generatePDF($content);
	}
	
	/**
		%customerName% : Name of customer, if it exists in their profile.
		%bookedDateTime%: The date or datetime of the day booked and confirmed/cancelled or refunded.
		%bookedDateTimeCost%: The cost of the booked date.
		%optionalItemName%: The optional item name.
		%optionalItemCost%: The optional item cost.
		%orderId% : The order ID.
		%orderDate% : The date the order was made.
		%orderDetails% : Order details.
		%orderAddtionalInfo% : Order additional info.
		%invoicePaymentLink% : A link is generated that redirects the user to paypal for payment of the invoice.
		%adminName%: The admin name, normally found in emails send out to the admin user when a new booking is made.
		%refundAmount%: The amount refunded.
		%orderUrl%: The url to the order for admin use.
	*/
	protected function getEmailBody($data){
		$customerInfo = $this->getCustomerInfo();
		$customerName = '';
		if($customerInfo){
			$customerName = $customerInfo['name'];
		}
		
		$emailSettings = $this->emailSettings;
		$content = $emailSettings->content;
		$attachment = null;
		if(!$content){
			$content = Booki_Helper::readEmailTemplate($this->emailType);
		}
		if(!$content){
			return;
		}
		
		$orderDetails = $this->orderDetails($data);
		$orderAddtionalInfo = $this->orderAddtionalInfo($data);
		$orderDate = $data && $this->order ? Booki_Helper::formatDate( $this->order->orderDate) : '';
		$bookedDateTime = '';
		$bookedDateTimeCost = '';
		$optionalItemName = '';
		$optionalItemCost = '';
		$quantityElementName = '';
		$quantityElementCost = '';
		$invoicePaymentLink = '';
		
		if($this->bookedDay){
			$bookedDateTime = Booki_DateHelper::localizedWPDateFormat($this->bookedDay->bookingDate) . ' ' . Booki_TimeHelper::formatTime($this->bookedDay, $data->timezoneInfo['timezone'], $this->bookedDay->enableSingleHourMinuteFormat);
			$bookedDateTimeCost = Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney($this->bookedDay->cost));
		}
		
		if($this->bookedOptional){
			$optionalItemName = $this->bookedOptional->getName();
			$optionalItemCost = Booki_Helper::formatCurrencySymbol($this->bookedOptional->getCalculatedCost());
		}
		else if($this->bookedCascadingItem){
			$optionalItemName = $this->bookedCascadingItem->getName();
			$optionalItemCost = Booki_Helper::formatCurrencySymbol($this->bookedCascadingItem->getCalculatedCost());
		}else if($this->bookedQuantityElement){
			$quantityElementName = $this->bookedQuantityElement->name_loc;
			$quantityElementCost = Booki_Helper::formatCurrencySymbol($this->bookedQuantityElement->cost);
		}

		$invoicePaymentLink = Booki_Helper::getUrl(Booki_PageNames::PAYPAL_HANDLER);
		$delimiter = Booki_Helper::getUrlDelimiter($invoicePaymentLink);
		$invoicePaymentLink = sprintf('<a href="%1$s">%1$s</a>', $invoicePaymentLink . $delimiter . "orderid=$this->orderId");
		
		
		$adminName = '';
		$adminUserId = $this->globalSettings->adminUserId;
		if($adminUserId){
			$adminUserInfo = Booki_Helper::getUserInfo($adminUserId);
			$adminName = $adminUserInfo['name'];
		}
		$content = str_ireplace("%orderId%", $this->orderId, $content);
		if($this->refundAmount){
			$content = str_ireplace("%refundAmount%", Booki_Helper::formatCurrencySymbol($this->refundAmount), $content);
		}
		
		$invoiceDownloadLink = BOOKIAPP()->handlerUrls->invoiceHandlerUrl;
		$delimiter = Booki_Helper::getUrlDelimiter($invoiceDownloadLink);
		$invoiceDownloadLink = sprintf('<a href="%1$s">%1$s</a>', $invoiceDownloadLink . $delimiter . "orderid=$this->orderId");
		$orderUrl = sprintf('<a href="%1$s">%1$s</a>', admin_url() . "admin.php?page=booki/managebookings.php&amp;orderid=$this->orderId");
		
		$content = str_ireplace("%orderDate%", $orderDate, $content);
		$content = str_ireplace("%bookedDateTime%", $bookedDateTime, $content);
		$content = str_ireplace("%bookedDateTimeCost%", $bookedDateTimeCost, $content);
		$content = str_ireplace("%optionalItemName%", $optionalItemName, $content);
		$content = str_ireplace("%optionalItemCost%", $optionalItemCost, $content);
		$content = str_ireplace("%quantityElementName%", $quantityElementName, $content);
		$content = str_ireplace("%quantityElementCost%", $quantityElementCost, $content);
		$content = str_ireplace("%customerName%", $customerName, $content);
		$content = str_ireplace("%orderDetails%", $orderDetails, $content);
		$content = str_ireplace("%orderAddtionalInfo%", $orderAddtionalInfo, $content);
		$content = str_ireplace("%invoicePaymentLink%", $invoicePaymentLink, $content);
		$content = str_ireplace("%invoiceDownloadLink%", $invoiceDownloadLink, $content);
		$content = str_ireplace("%adminName%", $adminName, $content);
		$content = str_ireplace("%orderUrl%", $orderUrl, $content);
		
		return $content;
	}

	protected function invoice($data, $userInfo){
		ob_start();
		if(!($data && $this->order)){
			return '';
		}
		$orderDetails = $this->orderDetails($data);
		
	?>
		<table width="100%" cellpadding="2">
			<thead>
			<tr bgcolor="#f1f1f1">
				<th align="left">
					<?php if(isset($this->invoiceSettings->companyName)):?>
					<?php echo $this->invoiceSettings->companyName ?>
					<?php endif;?>
				</th>
				<th align="right">
					<strong><?php echo __('INVOICE', 'booki') ?></strong>
				</th>
			</tr>
			<tr>
				<td>
					<?php if(isset($this->invoiceSettings->companyNumber)):?>
					<?php echo $this->invoiceSettings->companyNumber ?>
					<br>
					<?php endif;?>
					<?php if(isset($this->invoiceSettings->address)): ?>
					<strong><?php echo __('Address', 'booki') ?></strong>: <?php echo $this->invoiceSettings->address ?>
					<br>
					<?php endif; ?>
					<?php if(isset($this->invoiceSettings->telephone)): ?>
					<strong><?php echo __('Tel', 'booki') ?></strong>: <?php echo $this->invoiceSettings->telephone ?>
					<br>
					<?php endif; ?>
					<?php if(isset($this->invoiceSettings->email)): ?>
					<strong><?php echo __('Email', 'booki') ?></strong>: <?php echo $this->invoiceSettings->email ?>
					<br>
					<?php endif; ?>
					<br>
					<br>
					<br>
					<?php if($this->order->status === Booki_PaymentStatus::PAID): ?>
					<strong><?php echo __('STATUS: PAID', 'booki')?></strong>
					<?php else:?>
					<strong><?php echo __('STATUS: UNPAID', 'booki')?></strong>
					<?php endif; ?>
					<br>
					<br>
					<br>
					<?php if(isset($userInfo['name'])): ?>
					<strong><?php echo __('SOLD TO:', 'booki')?></strong>
					<br>
					<?php echo $userInfo['name'] ?>
					<br>
					<?php endif; ?>
					<strong><?php echo __('EMAIL', 'booki')?>:</strong> <?php echo $userInfo['email'] ?>
					<?php if($data->discount > 0):?>
					<br>
					<?php echo __('Coupon discount:', 'booki')?> <strong><?php echo -$data->discount ?>%</strong>
					<?php endif; ?>
					<?php if($this->globalSettings->tax > 0): ?>
					<br>
					<?php echo __('SALES TAX RATE:', 'booki')?> <strong><?php echo $this->globalSettings->tax ?>%</strong>
					<?php endif; ?>
				</td>
				<td align="right" valign="top">
					<?php echo __('ORDER NUMBER') ?>: #<?php echo $data->orderId ?>
					<br>
					<?php echo __('CUSTOMER NUMBER') ?>: #<?php echo $this->order->userId ?>
					<br>
					<?php echo __('ORDER DATE') ?>: <?php echo Booki_Helper::formatDate( $this->order->orderDate) ?>
				</td>
			</tr>
		</table>
		<br>
		<br>
		<?php echo $orderDetails ?>
		<br>
		<?php if(isset($this->invoiceSettings->additionalNote)){
			 echo $this->invoiceSettings->additionalNote . '<br>';
		}?>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	protected function orderDetails($data){
		ob_start();
		if(!$data){
			return '';
		}
	?>
	<table width="100%" border="1" cellpadding="2">
		<thead>
			<tr bgcolor="#f1f1f1">
				<th align="left">
					<?php echo __('BOOKINGS', 'booki') ?>
				</th>
				<?php if($data->totalAmount > 0):?>
				<th align="left">
					<?php echo __('COST', 'booki') ?>
				</th>
				<?php endif; ?>
			</tr>
		</thead>
			<?php $projectId = null; ?>
			<?php foreach($data->bookings as $booking): ?>
				<?php foreach( $booking->dates as $item ) : ?>
					<?php if($projectId != $booking->projectId):?>
					<tr>
						<td colspan="2"><strong><?php echo $item['projectName']?></strong></td>
					</tr>
					<?php $projectId = $booking->projectId; ?>
					<?php endif; ?>
					<tr>
						<td>
							<?php echo $item['formattedDate'] ?>
							<?php if($item['formattedTime']):?>
								<br>
								<?php echo $item['formattedTime'] ?>
								<?php if($this->displayTimezone):?>
								(<small><strong><?php echo __('Timezone', 'booki')?>:</strong>
									<?php echo $data->timezoneInfo['timezone']  ?>
								</small>)
								<?php endif; ?>
							<?php endif; ?>
						</td>
						<?php if($data->totalAmount > 0):?>
						<td>
							<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCost'], true); ?>
						</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			<?php if(count($booking->quantityElements) > 0):?>
				<?php foreach( $booking->quantityElements as $item ) : ?>
				<tr>
					<td><?php echo $item['name'] . ' x ' . $item['quantity']?></td>
					<td>
						<?php if($data->totalAmount > 0):?>
						<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCost'], true); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if(count($booking->optionals) > 0):?>
				<?php foreach( $booking->optionals as $item ) : ?>
				<tr>
					<td><?php echo $item['calculatedName'] ?></td>
					<td>
						<?php if($data->totalAmount > 0):?>
						<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if(count($booking->cascadingItems) > 0):?>
				<?php foreach( $booking->cascadingItems as $item ) : ?>
				<tr>
					<td><?php echo $item['trail'] ?></td>
					<td>
						<?php if($data->totalAmount > 0):?>
						<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if($booking->deposit > 0):?>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Payment due upon arrival', 'booki') ?></strong>
					<?php echo Booki_Helper::formatCurrencySymbol($data->formattedArrivalAmount); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Advance deposit', 'booki') ?></strong>
					<?php echo Booki_Helper::formatCurrencySymbol($data->deposit); ?>
				</td>
			</tr>
		<?php endif;?>
		<?php if($data->totalAmount > 0):?>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Sub total', 'booki') ?></strong>
					<?php echo Booki_Helper::formatCurrencySymbol($data->formattedTotalAmount); ?>
				</td>
			</tr>
			<?php if($data->discount > 0 && $data->hasBookings):?>
			<tr>
				<td colspan="2" align="right">
						<strong><?php echo __('Discount', 'booki') ?></strong>
						-<?php echo $data->discount . '%' ?>
				</td>
			</tr>
			<?php endif;?>
			<?php if($data->tax > 0 && $data->hasBookings):?>
			<tr>
				<td colspan="2" align="right">
						<strong><?php echo __('Tax', 'booki') ?></strong>
						<?php echo $data->tax ?>%
				</td>
			</tr>
			<?php endif;?>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Total', 'booki') ?></strong>
					<?php echo Booki_Helper::formatCurrencySymbol($data->formattedTotalAmountIncludingTax); ?>
				</td>
			</tr>
		<?php endif; ?>
	</table>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	protected function orderAddtionalInfo($data)
	{
		if((!($data && $this->order)) || $this->order->bookedFormElements->count() === 0){
			return '';
		}
		ob_start();
	?>
	<br>
	<table width="100%" border="1" cellpadding="2">
		<thead>
			<tr bgcolor="#f1f1f1">
				<th align="left">
					<i><?php echo __('ADDITIONAL INFORMATION', 'booki') ?></i>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td align="left">
				<?php 
					$list = array();
					foreach( $this->order->bookedFormElements as $item ) : ?>
						<?php array_push($list, ($item->elementType === 4 || $item->elementType === 5) ? esc_html($item->value) : $item->label . ': ' . esc_html($item->value));?>
					<?php endforeach; ?>
				<span><?php echo join(', ', $list);?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<br>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	protected function generatePDF($buffer, $orientation = 'P', $unit = 'mm', $format = 'A4'){
		$fileName = $this->orderId . '-invoice.pdf';
		$pdf = new TCPDF($orientation, $unit, $format); 
		$pdf->AddPage(); 
		@$pdf->WriteHTML($buffer); 
		$pdf->Output($fileName, 'D');
	}
	
	
	public function setHtmlContentType() {
		return 'text/html';
	}
}
?>