<?php
/**
 * The template to display the logo or the site name and the slogan in the Header
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

$parkivia_args = get_query_var('parkivia_logo_args');

// Site logo
$parkivia_logo_type   = isset($parkivia_args['type']) ? $parkivia_args['type'] : '';
$parkivia_logo_image  = parkivia_get_logo_image($parkivia_logo_type);
$parkivia_logo_text   = parkivia_is_on(parkivia_get_theme_option('logo_text')) ? get_bloginfo( 'name' ) : '';
$parkivia_logo_slogan = get_bloginfo( 'description', 'display' );
if (!empty($parkivia_logo_image) || !empty($parkivia_logo_text)) {
	?><a class="sc_layouts_logo" href="<?php echo esc_url(home_url('/')); ?>"><?php
		if (!empty($parkivia_logo_image)) {
			if (empty($parkivia_logo_type) && function_exists('the_custom_logo') && (int) $parkivia_logo_image > 0) {
				the_custom_logo();
			} else {
				$parkivia_attr = parkivia_getimagesize($parkivia_logo_image);
				echo '<img src="'.esc_url($parkivia_logo_image).'" alt="'.esc_attr($parkivia_logo_text).'"'.(!empty($parkivia_attr[3]) ? ' '.wp_kses_data($parkivia_attr[3]) : '').'>';
			}
		} else {
			parkivia_show_layout(parkivia_prepare_macros($parkivia_logo_text), '<span class="logo_text">', '</span>');
			parkivia_show_layout(parkivia_prepare_macros($parkivia_logo_slogan), '<span class="logo_slogan">', '</span>');
		}
	?></a><?php
}
?>