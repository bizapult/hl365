<?php
/**
 * The template to display default site footer
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.10
 */

$parkivia_footer_id = str_replace('footer-custom-', '', parkivia_get_theme_option("footer_style"));
if ((int) $parkivia_footer_id == 0) {
	$parkivia_footer_id = parkivia_get_post_id(array(
												'name' => $parkivia_footer_id,
												'post_type' => defined('TRX_ADDONS_CPT_LAYOUTS_PT') ? TRX_ADDONS_CPT_LAYOUTS_PT : 'cpt_layouts'
												)
											);
} else {
	$parkivia_footer_id = apply_filters('parkivia_filter_get_translated_layout', $parkivia_footer_id);
}
$parkivia_footer_meta = get_post_meta($parkivia_footer_id, 'trx_addons_options', true);
if (!empty($parkivia_footer_meta['margin']) != '') 
	parkivia_add_inline_css(sprintf('.page_content_wrap{padding-bottom:%s}', esc_attr(parkivia_prepare_css_value($parkivia_footer_meta['margin']))));
?>
<footer class="footer_wrap footer_custom footer_custom_<?php echo esc_attr($parkivia_footer_id); 
						?> footer_custom_<?php echo esc_attr(sanitize_title(get_the_title($parkivia_footer_id))); 
						if (!parkivia_is_inherit(parkivia_get_theme_option('footer_scheme')))
							echo ' scheme_' . esc_attr(parkivia_get_theme_option('footer_scheme'));
						?>">
	<?php
    // Custom footer's layout
    do_action('parkivia_action_show_layout', $parkivia_footer_id);
	?>
</footer><!-- /.footer_wrap -->
