<?php
/**
 * The template to display custom header from the ThemeREX Addons Layouts
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.06
 */

$parkivia_header_css = '';
$parkivia_header_image = get_header_image();
$parkivia_header_video = parkivia_get_header_video();
if (!empty($parkivia_header_image) && parkivia_trx_addons_featured_image_override(is_singular() || parkivia_storage_isset('blog_archive') || is_category())) {
	$parkivia_header_image = parkivia_get_current_mode_image($parkivia_header_image);
}

$parkivia_header_id = str_replace('header-custom-', '', parkivia_get_theme_option("header_style"));
if ((int) $parkivia_header_id == 0) {
	$parkivia_header_id = parkivia_get_post_id(array(
												'name' => $parkivia_header_id,
												'post_type' => defined('TRX_ADDONS_CPT_LAYOUTS_PT') ? TRX_ADDONS_CPT_LAYOUTS_PT : 'cpt_layouts'
												)
											);
} else {
	$parkivia_header_id = apply_filters('parkivia_filter_get_translated_layout', $parkivia_header_id);
}
$parkivia_header_meta = get_post_meta($parkivia_header_id, 'trx_addons_options', true);
if (!empty($parkivia_header_meta['margin']) != '') 
	parkivia_add_inline_css(sprintf('.page_content_wrap{padding-top:%s}', esc_attr(parkivia_prepare_css_value($parkivia_header_meta['margin']))));

?><header class="top_panel top_panel_custom top_panel_custom_<?php echo esc_attr($parkivia_header_id); 
				?> top_panel_custom_<?php echo esc_attr(sanitize_title(get_the_title($parkivia_header_id)));
				echo !empty($parkivia_header_image) || !empty($parkivia_header_video) 
					? ' with_bg_image' 
					: ' without_bg_image';
				if ($parkivia_header_video!='') 
					echo ' with_bg_video';
				if ($parkivia_header_image!='') 
					echo ' '.esc_attr(parkivia_add_inline_css_class('background-image: url('.esc_url($parkivia_header_image).');'));
				if (is_single() && has_post_thumbnail()) 
					echo ' with_featured_image';
				if (parkivia_is_on(parkivia_get_theme_option('header_fullheight'))) 
					echo ' header_fullheight parkivia-full-height';
				if (!parkivia_is_inherit(parkivia_get_theme_option('header_scheme')))
					echo ' scheme_' . esc_attr(parkivia_get_theme_option('header_scheme'));
				?>"><?php

	// Background video
	if (!empty($parkivia_header_video)) {
		get_template_part( 'templates/header-video' );
	}
		
	// Custom header's layout
	do_action('parkivia_action_show_layout', $parkivia_header_id);

	// Header widgets area
	get_template_part( 'templates/header-widgets' );
		
?></header>