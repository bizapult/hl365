<?php
/**
 * The template to display the widgets area in the footer
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.10
 */

// Footer sidebar
$parkivia_footer_name = parkivia_get_theme_option('footer_widgets');
$parkivia_footer_present = !parkivia_is_off($parkivia_footer_name) && is_active_sidebar($parkivia_footer_name);
if ($parkivia_footer_present) { 
	parkivia_storage_set('current_sidebar', 'footer');
	$parkivia_footer_wide = parkivia_get_theme_option('footer_wide');
	ob_start();
	if ( is_active_sidebar($parkivia_footer_name) ) {
		dynamic_sidebar($parkivia_footer_name);
	}
	$parkivia_out = trim(ob_get_contents());
	ob_end_clean();
	if (!empty($parkivia_out)) {
		$parkivia_out = preg_replace("/<\\/aside>[\r\n\s]*<aside/", "</aside><aside", $parkivia_out);
		$parkivia_need_columns = true;
		if ($parkivia_need_columns) {
			$parkivia_columns = max(0, (int) parkivia_get_theme_option('footer_columns'));
			if ($parkivia_columns == 0) $parkivia_columns = min(4, max(1, substr_count($parkivia_out, '<aside ')));
			if ($parkivia_columns > 1)
				$parkivia_out = preg_replace("/<aside([^>]*)class=\"widget/", "<aside$1class=\"column-1_".esc_attr($parkivia_columns).' widget', $parkivia_out);
			else
				$parkivia_need_columns = false;
		}
		?>
		<div class="footer_widgets_wrap widget_area<?php echo !empty($parkivia_footer_wide) ? ' footer_fullwidth' : ''; ?> sc_layouts_row sc_layouts_row_type_normal">
			<div class="footer_widgets_inner widget_area_inner">
				<?php 
				if (!$parkivia_footer_wide) { 
					?><div class="content_wrap"><?php
				}
				if ($parkivia_need_columns) {
					?><div class="columns_wrap"><?php
				}
				do_action( 'parkivia_action_before_sidebar' );
				parkivia_show_layout($parkivia_out);
				do_action( 'parkivia_action_after_sidebar' );
				if ($parkivia_need_columns) {
					?></div><!-- /.columns_wrap --><?php
				}
				if (!$parkivia_footer_wide) {
					?></div><!-- /.content_wrap --><?php
				}
				?>
			</div><!-- /.footer_widgets_inner -->
		</div><!-- /.footer_widgets_wrap -->
		<?php
	}
}
?>