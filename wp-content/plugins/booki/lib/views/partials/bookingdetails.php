<?php
	require_once  dirname(__FILE__) . '/../../infrastructure/utils/PermissionHelper.php';
	require_once  dirname(__FILE__) . '/../../infrastructure/utils/BookingHelper.php';
	class Booki_BookingDetails{
		public $data;
		public $order;
		public $hasFullControl;
		public $canEdit;
		public $canCancel;
		public $refundableDays;
		public $refundableOptionals;
		public $displayTimezone;
		public function __construct(){
			$globalSettings = BOOKIAPP()->globalSettings;
			$this->hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
			$orderDetails = apply_filters( 'booki_single_order_details', null);
			$this->data = $orderDetails->data;
			$this->order = $orderDetails->order;
			if(!$this->data || !$orderDetails->order){
				return;
			}
			$this->displayTimezone = $globalSettings->displayTimezone();
			$this->canEdit = Booki_PermissionHelper::hasEditorPermission($this->order->projectIdList);
			$this->canCancel = $this->canEdit || $globalSettings->enableUserCancelBooking;
			$this->refundableOptionals = ($this->order->status === Booki_PaymentStatus::PAID || 
									$this->order->status === Booki_PaymentStatus::PARTIALLY_REFUNDED) && $this->hasFullControl;
			$this->refundableDays = $this->refundableOptionals && $this->order->bookedDays->count() > 1;
		}
		public function timezoneControlHeaderCollapsed(){
			return false;
		}
		public function timezoneControlCollapsed(){
			return true;
		}
	}
	$_Booki_BookingDetails = new Booki_BookingDetails();
	if(!$_Booki_BookingDetails->data || !$_Booki_BookingDetails->order){
		return;
	}
?>
<div class="booki booki-content-box">
	<div class="booki-section-heading">
		<h4><?php echo __('Booking details', 'booki') ?></h4>
	</div>
	<?php $projectId = null; ?>
	<?php foreach($_Booki_BookingDetails->data->bookings as $booking): ?>
		<form class="form-horizontal" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
			<input type="hidden" name="controller" value="booki_managebookedday" />
			<input type="hidden" name="orderid" value="<?php echo $_GET['orderid']?>" />
			<input type="hidden" name="currency" value="<?php echo $_Booki_BookingDetails->data->currency?>" />
			<?php if($projectId != $booking->projectId):?>
				<div class="booki-projectname <?php echo $projectId !== null ? 'booki-projectname-sep' : '' ?> col-lg-12">
					<h4><?php echo $booking->projectName?></h4>
				</div>
				<?php $projectId = $booking->projectId; ?>
			<?php endif; ?>
			<?php foreach( $booking->dates as $item ) : ?>
			<div class="col-lg-12 booki-bg-box">
				<div class="col-lg-6">
					<div>
					<?php echo $item['formattedDate'] ?>
					<?php if($item['formattedTime']):?>
						<br>
						<?php echo $item['formattedTime'] ?>
						<?php if($_Booki_BookingDetails->displayTimezone):?>
						(<small><strong><?php echo __('in user selected timezone', 'booki')?>:</strong>
							<?php echo $_Booki_BookingDetails->data->timezoneInfo['timezone']  ?>
						</small>)
						<br>
						<?php echo $item['adminFormattedTime'] ?>
						(<small><strong><?php echo __('in admin timezone', 'booki')?>:</strong>
							<?php echo $_Booki_BookingDetails->data->adminTimezoneInfo['timezone']  ?>
						</small>)
						<?php endif; ?>
					<?php endif; ?>
					</div>
					<?php 
						$result = Booki_BookingHelper::fillContextMenu($_Booki_BookingDetails->canEdit, $_Booki_BookingDetails->canCancel, $_Booki_BookingDetails->refundableDays, $item['status']);
						$currentStatus = $result['currentStatus'];
						$contextButtons = $result['contextButtons'];
					?>
					<?php if($currentStatus):?>
						<div class="badge"><?php echo $currentStatus ?></div>
					<?php elseif (count($contextButtons) > 0): ?>
					<div class="btn-group">
						<button type="button" class="btn btn-<?php echo Booki_BookingHelper::getStatusLabel($item['status'])?> btn-sm dropdown-toggle" data-toggle="dropdown">
							<?php echo __(Booki_BookingHelper::getStatusText($item['status']), 'booki') ?>
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<?php foreach($contextButtons as $key=>$value): ?>
							<li>
								<button 
									<?php if (strtolower($key) !== 'cancel'):?>
									name="<?php echo strtolower($key) ?>"
									type="submit"
									value="<?php echo $item['id'] ?>"
									<?php else: ?>
									data-booki-id="<?php echo  $item['id'] ?>"
									type="button"
									data-toggle="modal" 
									data-target="#cancelDayModal"
									<?php endif;?>
									 class="booki-btnlink btn btn-default">
									<i class="glyphicon <?php echo $value['icon']?>"></i> 
									<?php echo __($value['label'], 'booki')?>
								</button>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>
				<div class="col-lg-6 booki-price-column-align">
					<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCost'], true); ?>
				</div>
			</div>
			<?php if(count($booking->quantityElements) > 0):?>
				<?php foreach( $booking->quantityElements as $quantityElement ) : ?>
				<div class="clearfix"></div>
				<div class="col-lg-12 bg-success booki-bg-box">
					<div class="col-lg-6">
						<div>
							<?php echo $quantityElement['name'] . ' x ' . $quantityElement['quantity'] ?>
						</div>
						<?php 
							$result = Booki_BookingHelper::fillContextMenu($_Booki_BookingDetails->canEdit, $_Booki_BookingDetails->canCancel, $_Booki_BookingDetails->refundableOptionals, $quantityElement['status']);
							$currentStatus = $result['currentStatus'];
							$contextButtons = $result['contextButtons'];
						?>
						<?php if($currentStatus):?>
							<div class="badge"><?php echo $currentStatus ?></div>
						<?php elseif (count($contextButtons) > 0): ?>
						<div class="btn-group">
							<button type="button" class="btn btn-<?php echo Booki_BookingHelper::getStatusLabel($quantityElement['status'])?> btn-sm dropdown-toggle" data-toggle="dropdown">
								<?php echo __(Booki_BookingHelper::getStatusText($quantityElement['status']), 'booki') ?>
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<?php foreach($contextButtons as $key=>$value): ?>
								<li>
									<button 
										<?php if (strtolower($key) !== 'cancel'):?>
										name="<?php echo strtolower($key) . '_quantity' ?>"
										type="submit"
										value="<?php echo  $quantityElement['id'] ?>"
										<?php else: ?>
										data-booki-id="<?php echo  $quantityElement['id'] ?>"
										type="button"
										data-toggle="modal" 
										data-target="#cancelQuantityElementModal"
										<?php endif;?>
										 class="booki-btnlink btn btn-default">
										<i class="glyphicon <?php echo $value['icon']?>"></i> 
										<?php echo __($value['label'], 'booki')?>
									</button>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php endif; ?>
					</div>
					<div class="col-lg-6 booki-price-column-align">
						<?php echo Booki_Helper::formatCurrencySymbol($quantityElement['formattedCost'], true); ?>
					</div>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php endforeach; ?>
			<div class="modal fade" id="cancelDayModal" tabindex="-1" role="dialog" aria-labelledby="cancelDayModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="cancelDayModalLabel"><?php echo __('Cancel booked date confirmation', 'booki') ?></h4>
						</div>
						<div class="modal-body">
							<?php echo __('When you cancel a booked date, the cost is deducted from the order total and the booked date is removed from the system. If there is only one date then the order is deleted.', 'booki') ?>
							<strong><?php echo __('Booki does not keep records of cancelled bookings. Proceed ?', 'booki') ?></strong>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
							<button
								name="cancel"
								class="btn btn-danger booki-confirm">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo __('Cancel', 'booki') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal fade" id="cancelQuantityElementModal" tabindex="-1" role="dialog" aria-labelledby="cancelQuantityElementModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="cancelQuantityElementModalLabel"><?php echo __('Cancel quantity element item confirmation', 'booki') ?></h4>
						</div>
						<div class="modal-body">
							<?php echo __('When you cancel a quantity element item, the cost is deducted from the order total and the quantity element item is removed from the system.', 'booki') ?>
							<strong><?php echo __('Booki does not keep records of cancelled quantity element item selections. Proceed ?', 'booki') ?></strong>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
							<button
								name="cancel_quantity"
								class="btn btn-danger booki-confirm">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo __('Cancel', 'booki') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php if(count($booking->optionals) > 0):?>
		<div class="clearfix"></div>
		<hr/>
		<form class="form-horizontal" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
			<input type="hidden" name="controller" value="booki_managebookedoptionals" />
			<input type="hidden" name="orderid" value="<?php echo $_GET['orderid']?>" />
			<input type="hidden" name="currency" value="<?php echo $_Booki_BookingDetails->data->currency?>" />
			<?php foreach( $booking->optionals as $item ) : ?>
			<div class="col-lg-12 bg-info booki-bg-box">
				<div class="col-lg-6">
					<div>
						<?php echo $item['calculatedName'] ?>
					</div>
					<?php 
						$result = Booki_BookingHelper::fillContextMenu($_Booki_BookingDetails->canEdit, $_Booki_BookingDetails->canCancel, $_Booki_BookingDetails->refundableOptionals, $item['status']);
						$currentStatus = $result['currentStatus'];
						$contextButtons = $result['contextButtons'];
					?>
					<?php if($currentStatus):?>
						<div class="badge"><?php echo $currentStatus ?></div>
					<?php elseif (count($contextButtons) > 0): ?>
					<div class="btn-group">
						<button type="button" class="btn btn-<?php echo Booki_BookingHelper::getStatusLabel($item['status'])?> btn-sm dropdown-toggle" data-toggle="dropdown">
							<?php echo __(Booki_BookingHelper::getStatusText($item['status']), 'booki') ?>
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<?php foreach($contextButtons as $key=>$value): ?>
							<li>
								<button 
									<?php if (strtolower($key) !== 'cancel'):?>
									name="<?php echo strtolower($key) ?>"
									type="submit"
									value="<?php echo  $item['id'] ?>"
									<?php else: ?>
									data-booki-id="<?php echo  $item['id'] ?>"
									type="button"
									data-toggle="modal" 
									data-target="#cancelOptionalModal"
									<?php endif;?>
									 class="booki-btnlink btn btn-default">
									<i class="glyphicon <?php echo $value['icon']?>"></i> 
									<?php echo __($value['label'], 'booki')?>
								</button>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>
				<div class="col-lg-6 booki-price-column-align">
					<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true); ?>
				</div>
			</div>
			<?php endforeach; ?>
			<div class="modal fade" id="cancelOptionalModal" tabindex="-1" role="dialog" aria-labelledby="cancelOptionalModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="cancelOptionalModalLabel"><?php echo __('Cancel optional item confirmation', 'booki') ?></h4>
						</div>
						<div class="modal-body">
							<?php echo __('When you cancel an optional item, the cost is deducted from the order total and the optional item is removed from the system.', 'booki') ?>
							<strong><?php echo __('Booki does not keep records of cancelled optional item selections. Proceed ?', 'booki') ?></strong>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
							<button
								name="cancel"
								class="btn btn-danger booki-confirm">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo __('Cancel', 'booki') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	<?php endif; ?>
	<?php if(count($booking->cascadingItems) > 0):?>
		<form class="form-horizontal" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
			<input type="hidden" name="controller" value="booki_managebookedcascadingitems" />
			<input type="hidden" name="orderid" value="<?php echo $_GET['orderid']?>" />
			<input type="hidden" name="currency" value="<?php echo $_Booki_BookingDetails->data->currency?>" />
			<?php foreach( $booking->cascadingItems as $item ) : ?>
			<div class="col-lg-12 bg-warning booki-bg-box">
				<div class="col-lg-6">
					<div><?php echo $item['trail'] ?></div>
					<?php 
						$result = Booki_BookingHelper::fillContextMenu($_Booki_BookingDetails->canEdit, $_Booki_BookingDetails->canCancel, $_Booki_BookingDetails->refundableOptionals, $item['status']);
						$currentStatus = $result['currentStatus'];
						$contextButtons = $result['contextButtons'];
					?>
					<?php if($currentStatus):?>
						<div class="badge"><?php echo $currentStatus ?></div>
					<?php elseif (count($contextButtons) > 0): ?>
					<div class="btn-group">
						<button type="button" class="btn btn-<?php echo Booki_BookingHelper::getStatusLabel($item['status'])?> btn-sm dropdown-toggle" data-toggle="dropdown">
							<?php echo __(Booki_BookingHelper::getStatusText($item['status']), 'booki') ?>
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<?php foreach($contextButtons as $key=>$value): ?>
							<li>
								<button 
									<?php if (strtolower($key) !== 'cancel'):?>
									name="<?php echo strtolower($key) ?>"
									type="submit"
									value="<?php echo  $item['id'] ?>"
									<?php else: ?>
									data-booki-id="<?php echo  $item['id'] ?>"
									type="button"
									data-toggle="modal" 
									data-target="#cancelCascadingModal"
									<?php endif;?>
									 class="booki-btnlink btn btn-default">
									<i class="glyphicon <?php echo $value['icon']?>"></i> 
									<?php echo __($value['label'], 'booki')?>
								</button>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>
				<div class="col-lg-6 booki-price-column-align">
					<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true);?>
				</div>
			</div>
			<?php endforeach; ?>
			<div class="modal fade" id="cancelCascadingModal" tabindex="-1" role="dialog" aria-labelledby="cancelCascadingModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="cancelCascadingModalLabel"><?php echo __('Cancel cascading item confirmation', 'booki') ?></h4>
						</div>
						<div class="modal-body">
							<?php echo __('When you cancel an cascading item, the cost is deducted from the order total and the cascading item is removed from the system.', 'booki') ?>
							<strong><?php echo __('Booki does not keep records of cancelled cascading item selections. Proceed ?', 'booki') ?></strong>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
							<button
								name="cancel"
								class="btn btn-danger booki-confirm">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo __('Cancel', 'booki') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	<?php endif; ?>
<?php endforeach; ?>
	<?php if($_Booki_BookingDetails->order && $_Booki_BookingDetails->order->bookedFormElements->count()):?>
		<div class="clearfix"></div>
		<hr/>
		<?php require 'bookedformelements.php' ?>
	<?php endif; ?>
	<div class="clearfix"></div>
	<div class="col-lg-12 booki-bg-box">
		<div class="clearfix"></div>
		<hr/>
		<?php if($_Booki_BookingDetails->data->totalAmount > 0):?>
		<div class="col-lg-12 booki-cart-totals-row">
			<?php if($_Booki_BookingDetails->data->deposit > 0):?>
			<div>
				<strong><?php echo __('Amount due on arrival', 'booki') ?></strong>
				<?php echo  Booki_Helper::formatCurrencySymbol($_Booki_BookingDetails->data->formattedArrivalAmount); ?>
			</div>
			<div>
					<strong><?php echo __('Advance deposit', 'booki') ?></strong>
					<?php echo Booki_Helper::formatCurrencySymbol($_Booki_BookingDetails->data->deposit); ?>
			</div>
			<?php endif;?>
			<div>
				<strong><?php echo __('Subtotal', 'booki') ?></strong>
				<span>
					<?php echo Booki_Helper::formatCurrencySymbol($_Booki_BookingDetails->data->formattedTotalAmount); ?>
				</span>
			</div>
			
			<?php if($_Booki_BookingDetails->data->tax > 0):?>
			<div>
				<strong><span class="booki-tax-label"><?php echo __('Tax', 'booki') ?></span></strong>
				<?php echo $_Booki_BookingDetails->data->tax ?>%
			</div>
			<?php endif;?>
			<?php if($_Booki_BookingDetails->data->hasDiscount): ?>
			<div>
				<strong>
					<span class="booki-coupon-label"><?php echo __('Discount', 'booki') ?></span>
				</strong>
				<span>
					-<?php echo $_Booki_BookingDetails->data->discount ?>%
				</span>
			</div>
			<?php endif; ?>
			<?php if($_Booki_BookingDetails->data->refundTotal > 0):?>
			<div>
				<strong><span class="booki-refund-label"><?php echo __('Refunded', 'booki') ?></span></strong>
				<?php echo Booki_Helper::formatCurrencySymbol($_Booki_BookingDetails->data->refundTotal); ?>
			</div>
			<?php endif;?>
			<div>
				<strong><span class="booki-total-label"><?php echo __('Total', 'booki') ?></span></strong>
				<?php echo Booki_Helper::formatCurrencySymbol($_Booki_BookingDetails->data->formattedTotalAmountIncludingTax); ?>
			</div>
		</div>
	<?php endif;?>
	</div>
	<div class="clearfix"></div>
</div>
<script type="text/javascript">
	(function($) {
		$(document).ready(function(){
			new Booki.ModalPopup({"elem": "#cancelDayModal"});
			new Booki.ModalPopup({"elem": "#cancelQuantityElementModal"});
			new Booki.ModalPopup({"elem": "#cancelOptionalModal"});
			new Booki.ModalPopup({"elem": "#cancelCascadingModal"});
		});
	})(jQuery);
</script>