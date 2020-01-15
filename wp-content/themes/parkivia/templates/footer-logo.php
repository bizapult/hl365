<?php
/**
 * The template to display the site logo in the footer
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.10
 */

// Logo
if (parkivia_is_on(parkivia_get_theme_option('logo_in_footer'))) {
	$parkivia_logo_image = '';
	if (parkivia_is_on(parkivia_get_theme_option('logo_retina_enabled')) && parkivia_get_retina_multiplier() > 1)
		$parkivia_logo_image = parkivia_get_theme_option( 'logo_footer_retina' );
	if (empty($parkivia_logo_image)) 
		$parkivia_logo_image = parkivia_get_theme_option( 'logo_footer' );
	$parkivia_logo_text   = get_bloginfo( 'name' );
	if (!empty($parkivia_logo_image) || !empty($parkivia_logo_text)) {
		?>
		<div class="footer_logo_wrap">
			<div class="footer_logo_inner">
				<?php
				if (!empty($parkivia_logo_image)) {
					$parkivia_attr = parkivia_getimagesize($parkivia_logo_image);
					echo '<a href="'.esc_url(home_url('/')).'"><img src="'.esc_url($parkivia_logo_image).'" class="logo_footer_image" alt="' . esc_html__('Image', 'parkivia') . '"'.(!empty($parkivia_attr[3]) ? ' ' . wp_kses_data($parkivia_attr[3]) : '').'></a>' ;
				} else if (!empty($parkivia_logo_text)) {
					echo '<h1 class="logo_footer_text"><a href="'.esc_url(home_url('/')).'">' . esc_html($parkivia_logo_text) . '</a></h1>';
				}
				?>
			</div>
		</div>
		<?php
	}
}
?>