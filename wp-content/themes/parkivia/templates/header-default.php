<?php
/**
 * The template to display default site header
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

$parkivia_header_css = '';
$parkivia_header_image = get_header_image();
$parkivia_header_video = parkivia_get_header_video();
if (!empty($parkivia_header_image) && parkivia_trx_addons_featured_image_override(is_singular() || parkivia_storage_isset('blog_archive') || is_category())) {
	$parkivia_header_image = parkivia_get_current_mode_image($parkivia_header_image);
}

?><header class="top_panel top_panel_default<?php
					echo !empty($parkivia_header_image) || !empty($parkivia_header_video) ? ' with_bg_image' : ' without_bg_image';
					if ($parkivia_header_video!='') echo ' with_bg_video';
					if ($parkivia_header_image!='') echo ' '.esc_attr(parkivia_add_inline_css_class('background-image: url('.esc_url($parkivia_header_image).');'));
					if (is_single() && has_post_thumbnail()) echo ' with_featured_image';
					if (parkivia_is_on(parkivia_get_theme_option('header_fullheight'))) echo ' header_fullheight parkivia-full-height';
					if (!parkivia_is_inherit(parkivia_get_theme_option('header_scheme')))
						echo ' scheme_' . esc_attr(parkivia_get_theme_option('header_scheme'));
					?>"><?php

	// Background video
	if (!empty($parkivia_header_video)) {
		get_template_part( 'templates/header-video' );
	}
	
	// Main menu
	if (parkivia_get_theme_option("menu_style") == 'top') {
		get_template_part( 'templates/header-navi' );
	}

	// Mobile header
	if (parkivia_is_on(parkivia_get_theme_option("header_mobile_enabled"))) {
		get_template_part( 'templates/header-mobile' );
	}
	
	// Page title and breadcrumbs area
	get_template_part( 'templates/header-title');

	// Header widgets area
	get_template_part( 'templates/header-widgets' );

	// Display featured image in the header on the single posts
	// Comment next line to prevent show featured image in the header area
	// and display it in the post's content
	get_template_part( 'templates/header-single' );

?></header>