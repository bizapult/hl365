<div class="booki">
<?php if(!is_user_logged_in()):?>
	<div class="booki col-lg-12">
		<h1><?php echo __('History', 'booki') ?></h1>
		<p><?php __('You need to login to view your history.', 'booki') ?> </p>
	</div>
<?php else: ?>
	<?php 
		$_Booki_HistoryTmpl = new Booki_HistoryTmpl();
	?>
	<div class="col-lg-12">
		<div class="form-group">
			<div class="booki-section-heading">
				<h1><?php echo __('User history', 'booki') ?></h1>
				<p><?php echo sprintf(__('Past bookings made by current user. YOU (%s)', 'booki'), $_Booki_HistoryTmpl->userName) ?> </p>
			</div>
		</div>
	</div>
	<div class="col-lg-12">
		<?php if($_Booki_HistoryTmpl->hasFullControl): ?>
			<?php require dirname(__FILE__) . '/../partials/refundtransaction.php' ?>
		<?php endif; ?>
		<?php if($_Booki_HistoryTmpl->orderId): ?>
			<?php require dirname(__FILE__) . '/../partials/bookingdetails.php' ?>
		<?php endif;?>
		<div class="booki-content-box">
			<div class="table-responsive">
				<?php $_Booki_HistoryTmpl->orderList->display() ?>
			</div>
		</div>
	</div>
<?php endif; ?>
</div>