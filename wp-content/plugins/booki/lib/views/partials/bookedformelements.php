<?php
	$_Booki_BookedFormElements = apply_filters( 'booki_booked_form_elements', null);
?>
	<p><i><?php echo __('This is additional information collected during the booking process.', 'booki') ?></i></p>
	<div>
		<?php 
			$list = array();
			foreach( $_Booki_BookedFormElements as $item ) : ?>
				<?php array_push($list, ($item->elementType === 4 || $item->elementType === 5) ? esc_html($item->value) : $item->label . ': ' . esc_html($item->value));?>
			<?php endforeach; ?>
		<span><?php echo join(', ', $list);?></span>
	</div>