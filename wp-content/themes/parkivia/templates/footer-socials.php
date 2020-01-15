<?php
/**
 * The template to display the socials in the footer
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.10
 */


// Socials
if ( parkivia_is_on(parkivia_get_theme_option('socials_in_footer')) && ($parkivia_output = parkivia_get_socials_links()) != '') {
	?>
	<div class="footer_socials_wrap socials_wrap">
		<div class="footer_socials_inner">
			<?php parkivia_show_layout($parkivia_output); ?>
		</div>
	</div>
	<?php
}
?>