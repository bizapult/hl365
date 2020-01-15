<?php
/**
 * The template to display menu in the footer
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.10
 */

// Footer menu
$parkivia_menu_footer = parkivia_get_nav_menu(array(
											'location' => 'menu_footer',
											'class' => 'sc_layouts_menu sc_layouts_menu_default'
											));
if (!empty($parkivia_menu_footer)) {
	?>
	<div class="footer_menu_wrap">
		<div class="footer_menu_inner">
			<?php parkivia_show_layout($parkivia_menu_footer); ?>
		</div>
	</div>
	<?php
}
?>