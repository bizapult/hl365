<?php
	/**
	* Template Name: Booki Cart Details
	*/
	$_Booki_CheckoutGridTmpl = new Booki_CheckoutGridTmpl();
?>
<form class="booki form-horizontal" data-parsley-validate action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
	<input type="hidden" name="booki_nonce" value="<?php echo Booki_NonceHelper::create('booki-checkout-grid');?>"/>
	<div class="booki col-lg-12">
		<div class="panel panel-default">
			<div class="panel-body">
				<?php if($_Booki_CheckoutGridTmpl->confirmCheckout): ?>
					<?php if($_Booki_CheckoutGridTmpl->checkoutFailure): ?>
						<div class="bg-danger booki-bg-box">
							<?php echo sprintf($_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT_LOC, $_Booki_CheckoutGridTmpl->checkoutFailure) ?>
						</div>
					<?php elseif($_Booki_CheckoutGridTmpl->paymentSuccess): ?>
						<div class="bg-success booki-bg-box">
							<p class="booki-payment-success">
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT_LOC ?>
								<?php if(is_user_logged_in()){ 
										echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY_LOC;
								} ?>
							</p>
							<p class="booki-history-reference">
								<?php echo sprintf($_Booki_CheckoutGridTmpl->resx->ORDER_ID_REF_LOC, $_Booki_CheckoutGridTmpl->data->orderId)?>
								<?php if(is_user_logged_in()){
										echo sprintf($_Booki_CheckoutGridTmpl->resx->VIEW_ORDER_HISTORY_LOC
											, sprintf('<a href="%s">%s</a>'
												, $_Booki_CheckoutGridTmpl->orderHistoryUrl
												, $_Booki_CheckoutGridTmpl->resx->HISTORY_LOC));
									}
								?>
							</p>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<?php if($_Booki_CheckoutGridTmpl->checkoutSuccessMessage): ?>
				<div class="bg-success booki-bg-box">
					<p class="booki-payment-success">
						<?php echo $_Booki_CheckoutGridTmpl->checkoutSuccessMessage ?>
					</p>
					<?php if(isset($_Booki_CheckoutGridTmpl->data->orderId)):?>
					<p class="booki-history-reference">
						<?php echo sprintf($_Booki_CheckoutGridTmpl->resx->ORDER_ID_REF_LOC, $_Booki_CheckoutGridTmpl->data->orderId)?>
						<?php if(is_user_logged_in()){
								echo sprintf($_Booki_CheckoutGridTmpl->resx->VIEW_ORDER_HISTORY_LOC
									, sprintf('<a href="%s">%s</a>'
										, $_Booki_CheckoutGridTmpl->orderHistoryUrl
										, $_Booki_CheckoutGridTmpl->resx->HISTORY_LOC)); 
							}
						?>
					</p>
					<?php endif;?>
				</div>
				<?php endif; ?>
				<?php if(isset($_Booki_CheckoutGridTmpl->data->hasBookedElements) && $_Booki_CheckoutGridTmpl->data->hasBookedElements):?>
				<div class="bg-warning booki-bg-box">
					<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE_LOC ?>
				</div>
				<?php endif; ?>
				<?php if($_Booki_CheckoutGridTmpl->data->hasBookings): ?>
				<div class="booki col-lg-12 booki-remove-horizontal-padding">
					<table class="table table-condensed booki-table-borderless">
						<thead>
							<tr>
								<th><small class="text-muted"><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_CHECKOUT_CART_HEADING_LOC ?></small></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach( $_Booki_CheckoutGridTmpl->data->bookings as $booking ) : ?>
								<?php if(isset($booking->bookingExhausted) && $booking->bookingExhausted): ?>
									<div class="bg-danger booki-bg-box">
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKINGS_EXHAUSTED_LOC ?>
									</div>
								<?php endif;?>
								<?php if($_Booki_CheckoutGridTmpl->projectId !== $booking->projectId && $_Booki_CheckoutGridTmpl->data->enableCartItemHeader):?>
								<tr>
									<td class="booki-cart-item-header">
										<?php echo $booking->projectName ?>
										<?php $_Booki_CheckoutGridTmpl->projectId = $booking->projectId; ?>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<ul class="list-group">
										<?php if(count($booking->dates) > 0):?>
											<li class="booki-list-group-item-borderless">
												<div class="col-sm-9">
													<div class="<?php echo  $booking->dates[0]['reserved'] ? 'booki-strike' : '' ?>">
														<i class="glyphicon glyphicon-calendar"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_DATE_LOC ?>"></i>
														<?php if(isset($booking->dates[0]['formattedDate'])){
																		echo $booking->dates[0]['formattedDate'];
																}
														?>
													</div>
													<?php if($booking->dates[0]['formattedTime']):?>
													<div class="<?php echo $booking->dates[0]['reserved'] ? 'booki-strike' : '' ?>">
														<i class="glyphicon glyphicon-time"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_TIME_LOC ?>"></i>
															<?php echo $booking->dates[0]['formattedTime'] ?>
													</div>
													<?php if ($_Booki_CheckoutGridTmpl->displayTimezone):?>
													<div>
														<i class="glyphicon glyphicon-globe"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo sprintf('%s : %s'
																					, $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_TIMEZONE_OFFSET_LOC
																					, $_Booki_CheckoutGridTmpl->data->timezoneInfo['abbr']) ?>"></i>
														<span>
															<small>
																<?php echo $_Booki_CheckoutGridTmpl->data->timezoneInfo['timezone'] ?> 
															</small>
														</span>
													</div>
													<?php endif; ?>
													<?php endif; ?>
													<hr class="visible-xs" />
													<div class="visible-xs">
														<?php if(($_Booki_CheckoutGridTmpl->editable && !$booking->dates[0]['reserved']) &&
																	(isset($booking->dates[0]['isRequired']) && !$booking->dates[0]['isRequired'])): ?>
														<button type="submit" name="booki_remove_order" 
																class="booki-styleless-btn pull-right" 
																value="<?php echo $booking->dates[0]['bookingId']?>">
															<i class="glyphicon glyphicon-trash"></i>
														</button>
														<?php endif; ?>
														<?php if($booking->dates[0]['cost'] > 0):?>
														<span class="<?php echo $booking->dates[0]['reserved'] ? 'booki-strike' : '' ?>"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
															<?php echo Booki_Helper::formatCurrencySymbol($booking->dates[0]['formattedCost'], true); ?>
														</span>
														<?php endif;?>
													</div>
												</div>
												<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
													<span class="visible-sm visible-md visible-lg <?php echo $booking->dates[0]['reserved'] ? 'booki-strike' : '' ?>">
														<?php if(($_Booki_CheckoutGridTmpl->editable && !$booking->dates[0]['reserved']) &&
																			(isset($booking->dates[0]['isRequired']) && !$booking->dates[0]['isRequired'])): ?>
														<button type="submit" 
																name="booki_remove_order" 
																class="btn btn-default btn-sm" 
																value="<?php echo $booking->dates[0]['bookingId']?>">
																	<span><?php if(isset($booking->dates[0]['formattedCost']) && (isset($booking->dates[0]['cost']) && $booking->dates[0]['cost'] > 0)){
																					echo Booki_Helper::formatCurrencySymbol($booking->dates[0]['formattedCost'], true);
																				}
																			?></span>
																	<i class="glyphicon glyphicon-trash"></i>
														</button>
														<?php elseif(isset($booking->dates[0]['cost']) && $booking->dates[0]['cost'] > 0): ?>
															<?php if(isset($booking->dates[0]['formattedCost'])):?>
															<span><?php echo Booki_Helper::formatCurrencySymbol($booking->dates[0]['formattedCost'], true); ?></span>
															<?php endif; ?>
														<?php endif; ?>
													</span>
												</div>
												<div class="clearfix"></div>
											</li>
										<?php endif; ?>
										</ul>
										<?php if(count($booking->quantityElements) > 0):?>
											<ul class="list-group">
												<?php foreach( $booking->quantityElements as $item ) : ?>
													<li class="list-group-item booki-list-group-item">
														<div class="col-sm-9">
															<div class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>">
																<i class="glyphicon glyphicon-plus-sign"></i>
																	<?php echo $item['name'] . ' x ' . $item['quantity'] ?>
															</div>
															<hr class="visible-xs" />
															<div class="visible-xs">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['reserved']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button type="submit" 
																		name="booki_remove_optional" 
																		class="booki-styleless-btn pull-right" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>">
																	<i class="glyphicon glyphicon-trash"></i>
																</button>
																<?php endif; ?>
																<?php if($item['cost']):?>
																<span class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>"
																	data-container="body" 
																	data-toggle="popover" 
																	data-placement="top" 
																	data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
																	<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCost'], true); ?>
																</span>
																<?php endif; ?>
															</div>
														</div>
														<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
															<span class="visible-sm visible-md visible-lg">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['reserved']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button 
																		name="booki_remove_quantityelement" 
																		class="btn btn-default btn-xs" 
																		type="submit" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>"> 
																		<?php if($item['cost']):?>
																		<span><?php echo Booki_Helper::formatCurrencySymbol($item['formattedCost'], true); ?></span>
																		<?php endif; ?>
																	<i class="glyphicon glyphicon-trash remove-option"></i>
																</button>
																<?php elseif($item['cost'] > 0): ?>
																	<span class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>">
																		<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCost'], true); ?>
																	</span>
																<?php endif; ?>
															</span>
														</div>
														<div class="clearfix"></div>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
										<?php if(count($booking->optionals) > 0):?>
											<ul class="list-group">
												<?php foreach( $booking->optionals as $item ) : ?>
													<li class="list-group-item booki-list-group-item">
														<div class="col-sm-9">
															<div class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>">
																<i class="glyphicon glyphicon-plus-sign"></i>
																	<?php echo $item['calculatedName'] ?>
															</div>
															<hr class="visible-xs" />
															<div class="visible-xs">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['reserved']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button type="submit" 
																		name="booki_remove_optional" 
																		class="booki-styleless-btn pull-right" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>">
																	<i class="glyphicon glyphicon-trash"></i>
																</button>
																<?php endif; ?>
																<?php if($item['cost']):?>
																<span class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>"
																	data-container="body" 
																	data-toggle="popover" 
																	data-placement="top" 
																	data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
																	<?php echo $item['formattedCalculatedCost'] ?>
																</span>
																<?php endif; ?>
															</div>
														</div>
														<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
															<span class="visible-sm visible-md visible-lg">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['reserved']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button 
																		name="booki_remove_optional" 
																		class="btn btn-default btn-xs" 
																		type="submit" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>"> 
																		<?php if($item['cost']):?>
																		<span><?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true); ?></span>
																		<?php endif;?>
																	<i class="glyphicon glyphicon-trash remove-option"></i>
																</button>
																<?php elseif($item['cost'] > 0): ?>
																	<span class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>">
																		<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true); ?>
																	</span>
																<?php endif; ?>
															</span>
														</div>
														<div class="clearfix"></div>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
										<?php if(count($booking->cascadingItems) > 0):?>
											<ul class="list-group">
												<?php foreach( $booking->cascadingItems as $item ) : ?>
													<li class="list-group-item booki-list-group-item">
														<div class="col-sm-9">
															<div class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>">
																<i class="glyphicon glyphicon-plus-sign"></i>
																	<?php echo $item['trail'] ?>
															</div>
															<hr class="visible-xs" />
															<div class="visible-xs">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['reserved']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button type="submit" 
																		name="booki_remove_cascadingitem" 
																		class="booki-styleless-btn pull-right" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>">
																	<i class="glyphicon glyphicon-trash"></i>
																</button>
																<?php endif; ?>
																<?php if($item['cost']):?>
																<span class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>"
																	data-container="body" 
																	data-toggle="popover" 
																	data-placement="top" 
																	data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
																	<?php echo $item['formattedCalculatedCost'] ?>
																</span>
																<?php endif; ?>
															</div>
														</div>
														<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
															<span class="visible-sm visible-md visible-lg">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['reserved']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button 
																		name="booki_remove_cascadingitem" 
																		class="btn btn-default btn-xs" 
																		type="submit" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>"> 
																		<?php if($item['cost'] > 0):?>
																		<span><?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true); ?></span>
																		<?php endif; ?>
																	<i class="glyphicon glyphicon-trash remove-option"></i>
																</button>
																<?php else: ?>
																	<span class="<?php echo $item['reserved'] ? 'booki-strike' : '' ?>">
																		<?php echo Booki_Helper::formatCurrencySymbol($item['formattedCalculatedCost'], true); ?>
																	</span>
																<?php endif; ?>
															</span>
														</div>
														<div class="clearfix"></div>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php else: ?>
					<div class="bg-warning booki-bg-box">
						<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT_LOC ?></p>
					</div>
				<?php endif; ?>
				<div class="clearfix"></div>
				<?php if($_Booki_CheckoutGridTmpl->data->totalAmount):?>
				<?php if($_Booki_CheckoutGridTmpl->data->hasDiscount && (!$_Booki_CheckoutGridTmpl->confirmCheckout && !$_Booki_CheckoutGridTmpl->checkoutSuccessMessage)): ?>
				<div class="bg-success booki-bg-box">
					<strong><?php echo $_Booki_CheckoutGridTmpl->resx->CONGRATS_LOC ?></strong> <?php echo sprintf($_Booki_CheckoutGridTmpl->resx->GOT_DISCOUNT_LOC, Booki_Helper::toMoney($_Booki_CheckoutGridTmpl->data->discount) ) ?>
				</div>
				<?php endif;?>
				<div class="booki col-md-6 booki-remove-horizontal-padding">
					<?php if($_Booki_CheckoutGridTmpl->enableCoupons && (!$_Booki_CheckoutGridTmpl->data->hasDiscount && !$_Booki_CheckoutGridTmpl->data->hasDeposit)):?>
					<div class="input-group">
					  <input type="text"
							id="booki_couponcode"
							name="booki_couponcode" 
							class="form-control" 
							value="<?php echo $_Booki_CheckoutGridTmpl->coupon ? $_Booki_CheckoutGridTmpl->coupon->code : '' ?>"
							placeholder="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_CODE_LOC ?>" />
						<div class="input-group-btn">
							<button class="btn btn-default booki-redeem-button" 
							type="submit" name="booki_redeem_coupon" title="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_REDEEM_LOC ?>">
								<i class="glyphicon glyphicon-ok-circle"></i>
							</button>
							<button type="button" class="btn btn-default" data-toggle="dropdown" tabindex="-1">
								<span class="caret"></span>
								<span class="sr-only"><?php echo __('Toggle Dropdown', 'booki') ?></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li>
									<button class="btn btn-default booki-styleless-btn dropdown-button"
										name="booki_redeem_coupon"
										type="submit">
										<i class="glyphicon glyphicon-ok-circle"></i>
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_REDEEM_LOC ?>
									</button>
								</li>
								<li>
									<button class="btn btn-default booki-styleless-btn dropdown-button" 
										type="submit" name="booki_cancel_coupon">
										<i class="glyphicon glyphicon-remove-circle"></i>
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_REMOVE_COUPON_LOC ?>
									</button>
								</li>
								<li>
									<a class="booki-coupon-help accordion-toggle btn btn-default booki-styleless-btn dropdown-button" data-toggle="collapse" href=".booki-coupon-info">
										<i class="glyphicon glyphicon-question-sign help"></i>
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COUPON_HELP_LOC ?>
									</a>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
					</div>
					<?php if($_Booki_CheckoutGridTmpl->couponErrorMessage): ?>
					<ul class="data-parsley-error-list">
						<li><?php echo $_Booki_CheckoutGridTmpl->couponErrorMessage ?></li>
					</ul>
					<?php endif; ?>
					<div id="booki_couponcode_error"></div>
					<div class="clearfix"></div>
					<div class="accordion-body">
						<div class="panel panel-info booki-coupon-info collapse">
							<div class="panel-heading">
								<a class="close accordion-toggle" data-toggle="collapse" href=".booki-coupon-info">&times;</a>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_HOW_COUPONS_WORK_LOC ?>
							</div>
						  <div class="panel-body">
							<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP_LOC ?></p>
							<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP_LOC ?></p>
							<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP_LOC ?></p>
						  </div>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<div class="booki col-md-6 booki-cart-totals-row booki-remove-horizontal-padding">
					<?php if($_Booki_CheckoutGridTmpl->data->deposit > 0):?>
					<div>
						<div class="col-lg-6">
							<strong><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL_LOC ?></strong>
						</div>
						<div class="col-lg-6">
							<?php echo Booki_Helper::formatCurrencySymbol($_Booki_CheckoutGridTmpl->data->formattedArrivalAmount); ?>
						</div>
						<div class="clearfix"></div>
					</div>
					<div>
						<div class="col-lg-6">
							<strong><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW_LOC ?></strong>
						</div>
						<div class="col-lg-6">
							<?php echo Booki_Helper::formatCurrencySymbol( $_Booki_CheckoutGridTmpl->data->deposit);?>
						</div>
						<div class="clearfix"></div>
					</div>
					<?php endif;?>
					<div>
						<div class="col-lg-6">
							<strong><?php echo $_Booki_CheckoutGridTmpl->resx->SUBTOTAL_LOC ?></strong>
						</div>
						<div class="col-lg-6">
								<?php echo Booki_Helper::formatCurrencySymbol($_Booki_CheckoutGridTmpl->data->formattedTotalAmount);?>
						</div>
						<div class="clearfix"></div>
					</div>
					<?php if($_Booki_CheckoutGridTmpl->data->tax && $_Booki_CheckoutGridTmpl->data->hasBookings):?>
					<div>
						<div class="col-lg-6">
							<strong><span class="booki-tax-label"><?php echo $_Booki_CheckoutGridTmpl->resx->TAX_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
								<?php echo $_Booki_CheckoutGridTmpl->data->tax ?>%
						</div>
						<div class="clearfix"></div>
					</div>
					<?php endif;?>
					<?php if($_Booki_CheckoutGridTmpl->data->hasDiscount): ?>
					<div>
						<div class="col-lg-6">
							<strong><span class="booki-discount-label"><?php echo $_Booki_CheckoutGridTmpl->resx->DISCOUNT_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
							-<?php echo $_Booki_CheckoutGridTmpl->data->discount ?>%
						</div>
						<div class="clearfix"></div>
					</div>
					<?php elseif($_Booki_CheckoutGridTmpl->coupon && $_Booki_CheckoutGridTmpl->coupon->isValid()): ?>
					<div>
						<div class="col-lg-6">
							<strong><span class="booki-discount-label"><?php echo $_Booki_CheckoutGridTmpl->resx->DISCOUNT_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
							-<?php echo $_Booki_CheckoutGridTmpl->coupon->discount ?>%
						</div>
						<div class="clearfix"></div>
					</div>
					<?php endif; ?>
					<div>
						<div class="col-lg-6">
						<strong><span class="booki-total-label"><?php echo $_Booki_CheckoutGridTmpl->resx->TOTAL_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
							<?php echo Booki_Helper::formatCurrencySymbol($_Booki_CheckoutGridTmpl->data->formattedTotalAmountIncludingTax) ?>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
				<?php endif; ?>
				<div class="clearfix"></div>
			</div>
			<?php if($_Booki_CheckoutGridTmpl->showFooter): ?>
			<div class="panel-footer">
				<?php if($_Booki_CheckoutGridTmpl->confirmCheckout && !$_Booki_CheckoutGridTmpl->globalSettings->autoConfirmOrderAfterPayment): ?>
					<div class="bg-info booki-bg-box">
						<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_PAYMENT_AUTHORIZED_LOC ?>
					</div>
				<?php endif; ?>
				<div class="pull-right">
					<?php if(!$_Booki_CheckoutGridTmpl->confirmCheckout): ?>
						<?php if($_Booki_CheckoutGridTmpl->data->hasBookings): ?>
							<?php if($_Booki_CheckoutGridTmpl->editable): ?>
							<button type="submit" 
									name="booki_empty_cart" 
									class="btn btn-danger booki-empty-cart">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_EMPTY_CART_LOC ?>
							</button>
							<?php endif; ?>
						<div class="visible-xs booki-vertical-gap-xs"></div>
						<?php endif;?>
						<?php if($_Booki_CheckoutGridTmpl->editable && $_Booki_CheckoutGridTmpl->globalSettings->enableBookMoreButton): ?>
						<button type="submit" 
								name="booki_continue_booking" 
								value="<?php echo $_Booki_CheckoutGridTmpl->globalSettings->continueBookingUrl ?>" 
								class="btn btn-primary booki-book-more">
							<i class="glyphicon glyphicon-plus-sign"></i>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOK_MORE_LOC ?>
						</button>
						<?php endif; ?>
						<div class="visible-xs booki-vertical-gap-xs"></div>
						<?php if($_Booki_CheckoutGridTmpl->data->hasBookings): ?>
							<?php if ( is_user_logged_in()  || !$_Booki_CheckoutGridTmpl->data->globalSettings->membershipRequired): ?> 
								<?php if(!$_Booki_CheckoutGridTmpl->billSettlement && (($_Booki_CheckoutGridTmpl->data->enableBookingWithAndWithoutPayment || !$_Booki_CheckoutGridTmpl->globalSettings->enablePayments || !$_Booki_CheckoutGridTmpl->data->totalAmount) && !$_Booki_CheckoutGridTmpl->enablePayPalBilling)):?>
									<button type="submit" 
											name="booki_checkout"
											value="0"
											class="btn btn-primary booki-make-booking">
											<?php echo ($_Booki_CheckoutGridTmpl->globalSettings->enablePayments && $_Booki_CheckoutGridTmpl->data->totalAmount > 0) ? $_Booki_CheckoutGridTmpl->resx->BOOK_NOW_PAY_LATER_LOC : $_Booki_CheckoutGridTmpl->resx->BOOK_NOW_LOC ?>
									</button>
								<?php endif; ?>
								<?php if (($_Booki_CheckoutGridTmpl->data->globalSettings->enablePayments || $_Booki_CheckoutGridTmpl->enablePayPalBilling) && $_Booki_CheckoutGridTmpl->data->totalAmount > 0): ?> 
									<button type="submit" 
											name="booki_checkout" 
											value="1"
											class="booki-cart-checkout">
											<img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left">
									</button>
								<?php endif; ?>
							<?php else: ?>
							<a class="btn btn-success booki-proceed" href="<?php echo Booki_Helper::appendReferrer($_Booki_CheckoutGridTmpl->globalSettings->loginPageUrl, 'redirect_to') ?>">
								<i class="glyphicon glyphicon-circle-arrow-right"></i>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_PROCEED_LOC ?>
							</a>
							<?php endif; ?>
						<?php endif; ?>
					<?php elseif(!$_Booki_CheckoutGridTmpl->globalSettings->autoConfirmOrderAfterPayment): ?>
						<button class="btn btn-primary pull-right" name="booki_paypal_process_payment">
							<i class="glyphicon glyphicon-circle-arrow-right"></i>
							<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_CONFIRM_AND_PAY_LOC ?>
						</button>
					<?php endif; ?>
				</div>
				<div class="clearfix"></div>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="clearfix"></div>
</form>