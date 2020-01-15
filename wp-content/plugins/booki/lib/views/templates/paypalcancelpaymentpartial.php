<?php
	$_Booki_PaypalCancelPaymentTmpl = new Booki_PaypalCancelPaymentTmpl();
?>
<div class="booki col-lg-12">
	<div class="bg-success booki-bg-box">
		<?php if($_Booki_PaypalCancelPaymentTmpl->success): ?>
				<?php echo __('Payment cancelled successfully! The order has been deleted. Thanks for trying to book with us. Hope to see you again.', 'booki');?>
		<?php elseif($_Booki_PaypalCancelPaymentTmpl->globalSettings->membershipRequired): ?>
				<?php echo __('Payment cancelled. The order is still available in your order history. If you change your mind, you can make payment directly through your order history page. Thank you for booking with us.', 'booki'); ?>
		<?php else: ?>
				<?php echo __('Payment cancelled. Thank you for booking with us.', 'booki'); ?>
		<?php endif; ?>
	</div>
	<?php if($_Booki_PaypalCancelPaymentTmpl->globalSettings->membershipRequired): ?>
	<div class="bg-warning booki-bg-box">
		<?php echo __('Go to your order', 'booki') . ' ' ?> 
		<a href="<?php echo Booki_ThemeHelper::getHistoryPage() ?>"><?php echo __('history page', 'booki') ?>
		</a>
	</div>
	<?php endif; ?>
</div>