<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

if (parkivia_sidebar_present()) {
	ob_start();
	$parkivia_sidebar_name = parkivia_get_theme_option('sidebar_widgets');
	parkivia_storage_set('current_sidebar', 'sidebar');
	if ( is_active_sidebar($parkivia_sidebar_name) ) {
		dynamic_sidebar($parkivia_sidebar_name);
	}
	$parkivia_out = trim(ob_get_contents());
	ob_end_clean();
	if (!empty($parkivia_out)) {
		$parkivia_sidebar_position = parkivia_get_theme_option('sidebar_position');
		?>
		<div class="sidebar <?php echo esc_attr($parkivia_sidebar_position); ?> widget_area<?php if (!parkivia_is_inherit(parkivia_get_theme_option('sidebar_scheme'))) echo ' scheme_'.esc_attr(parkivia_get_theme_option('sidebar_scheme')); ?>" role="complementary">
			<div class="sidebar_inner">
				<?php
				do_action( 'parkivia_action_before_sidebar' );
				parkivia_show_layout(preg_replace("/<\/aside>[\r\n\s]*<aside/", "</aside><aside", $parkivia_out));
				do_action( 'parkivia_action_after_sidebar' );
				?>
			</div><!-- /.sidebar_inner -->
		</div><!-- /.sidebar -->
		<?php
	}
}
?>