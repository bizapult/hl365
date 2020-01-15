<?php

$_Booki_StatsTmpl = new Booki_StatsTmpl();

?>
<div class="booki col-lg-12">
	<div class="booki col-lg-12">
		<h1><?php echo __('Stats', 'booki') ?></h1>
		<p><?php echo __('A brief overview of your bookings.', 'booki') ?> </p>
	</div>
</div>
<div class="booki col-lg-12">
	<div class="col-lg-6">
		<div class="booki-content-box">
			<div class="col-lg-3 booki-stats-block booki-stats-separator">
				<h1 class="badge"><?php echo (int)$_Booki_StatsTmpl->donut[1] ?></h1>
				<h2><?php echo __('payments made', 'booki') ?></h2>
			</div>
			<div class="col-lg-5 booki-stats-block booki-stats-separator">
				<h1 class="badge"><?php echo (int)$_Booki_StatsTmpl->donut[0] ?></h1>
				<h2><?php echo __('payments pending', 'booki') ?></h2>
				<div>
					<span>
						<?php echo __('Purchasers are made, not born. --Henry Ford', 'booki') ?>
					</span>
				</div>
			</div>
			<div class="col-lg-4 booki-stats-block">
				<h1 class="badge"><?php echo (int)$_Booki_StatsTmpl->donut[2]  ?></h1>
				<h2><?php echo __('Refunds', 'booki') ?></h2>
				<div>
					<span>
						<?php echo __('Don\'t dwell on negativity.', 'booki') ?>
					</span>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="booki-content-box">
			<div class="col-lg-3 booki-stats-block booki-stats-separator">
				<h1 class="badge"><?php echo $_Booki_StatsTmpl->summary->count ?></h1>
				<h2><?php echo __('total days booked', 'booki') ?></h2>
			</div>
			<div class="col-lg-5 booki-stats-block booki-stats-separator">
				<h1><?php echo Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney((int)$_Booki_StatsTmpl->totalAmountEarned), true); ?></h1>
				<h2><?php echo __('total amount earned', 'booki') ?></h2>
				<div>
					<span>
						<?php echo __('I do not love the money. What I love is the making of it. --Philip Armour', 'booki') ?> 
					</span>
				</div>
			</div>
			<div class="col-lg-4 booki-stats-block">
				<h1><?php echo Booki_Helper::formatCurrencySymbol(Booki_Helper::toMoney((int)$_Booki_StatsTmpl->summary->discount), true);  ?></h1>
				<h2><?php echo __('total discounts given', 'booki') ?></h2>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="booki col-lg-12">
	<?php if($_Booki_StatsTmpl->orderId !== null): ?>
		<?php require dirname(__FILE__) . '/../partials/bookingdetails.php' ?>
	<?php endif;?>
	</div>
	<?php if($_Booki_StatsTmpl->orderList): ?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-section-heading">
				<h4><?php echo __('Bookings', 'booki') ?></h4>
				<p><?php echo __('Listing of all bookings approved by you', 'booki') ?></p>
			</div>
			<div class="table-responsive">
				<?php $_Booki_StatsTmpl->orderList->display();?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
<div class="booki col-lg-12">
	<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/stats.php" ?>" method="post">
		<input type="hidden" name="controller" value="booki_stats" />
		<div class="col-lg-6">
			<div class="booki-content-box">
				<div class="booki-section-heading">
					<h4><?php echo __('Bookings', 'booki')?></h4>
					<p><?php echo __('Bookings made in the last 3 months', 'booki')?></p>
				</div>
				<div class="table-responsive">
					<?php $_Booki_StatsTmpl->ordersMadeAggregateList->display();?>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="booki-content-box">
				<div class="booki-section-heading">
					<h4><?php echo __('Sales', 'booki')?></h4>
					<p><?php echo __('Sales in the last 3 months', 'booki')?></p>
				</div>
				<div>
					<?php $_Booki_StatsTmpl->ordersTotalAmountAggregateList->display();?>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="col-lg-6">
			<div class="booki-content-box">
				<div class="booki-section-heading">
					<h4><?php echo __('Refunds', 'booki')?></h4>
					<p><?php echo __('Refunds in the last 3 months', 'booki')?></p>
				</div>
				<div class="table-responsive">
					<?php $_Booki_StatsTmpl->ordersRefundAmountAggregateList->display(); ?>
				</div>
			</div>
		</div>
	</form>
</div>