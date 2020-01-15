<?php
/**
 * The template to display the widgets area in the header
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

// Header sidebar
$parkivia_header_name = parkivia_get_theme_option('header_widgets');
$parkivia_header_present = !parkivia_is_off($parkivia_header_name) && is_active_sidebar($parkivia_header_name);
if ($parkivia_header_present) { 
	parkivia_storage_set('current_sidebar', 'header');
	$parkivia_header_wide = parkivia_get_theme_option('header_wide');
	ob_start();
	if ( is_active_sidebar($parkivia_header_name) ) {
		dynamic_sidebar($parkivia_header_name);
	}
	$parkivia_widgets_output = ob_get_contents();
	ob_end_clean();
	if (!empty($parkivia_widgets_output)) {
		$parkivia_widgets_output = preg_replace("/<\/aside>[\r\n\s]*<aside/", "</aside><aside", $parkivia_widgets_output);
		$parkivia_need_columns = strpos($parkivia_widgets_output, 'columns_wrap')===false;
		if ($parkivia_need_columns) {
			$parkivia_columns = max(0, (int) parkivia_get_theme_option('header_columns'));
			if ($parkivia_columns == 0) $parkivia_columns = min(6, max(1, substr_count($parkivia_widgets_output, '<aside ')));
			if ($parkivia_columns > 1)
				$parkivia_widgets_output = preg_replace("/<aside([^>]*)class=\"widget/", "<aside$1class=\"column-1_".esc_attr($parkivia_columns).' widget', $parkivia_widgets_output);
			else
				$parkivia_need_columns = false;
		}
		?>
		<div class="header_widgets_wrap widget_area<?php echo !empty($parkivia_header_wide) ? ' header_fullwidth' : ' header_boxed'; ?>">
			<div class="header_widgets_inner widget_area_inner">
				<?php 
				if (!$parkivia_header_wide) { 
					?><div class="content_wrap"><?php
				}
				if ($parkivia_need_columns) {
					?><div class="columns_wrap"><?php
				}
				do_action( 'parkivia_action_before_sidebar' );
				parkivia_show_layout($parkivia_widgets_output);
				do_action( 'parkivia_action_after_sidebar' );
				if ($parkivia_need_columns) {
					?></div>	<!-- /.columns_wrap --><?php
				}
				if (!$parkivia_header_wide) {
					?></div>	<!-- /.content_wrap --><?php
				}
				?>
			</div>	<!-- /.header_widgets_inner -->
		</div>	<!-- /.header_widgets_wrap -->
		<?php
	}
}
?>