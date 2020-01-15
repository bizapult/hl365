<?php
/**
 * The template for homepage posts with "Classic" style
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

parkivia_storage_set('blog_archive', true);

get_header(); 

if (have_posts()) {

	parkivia_show_layout(get_query_var('blog_archive_start'));

	$parkivia_classes = 'posts_container '
						. (substr(parkivia_get_theme_option('blog_style'), 0, 7) == 'classic' ? 'columns_wrap columns_padding_bottom' : 'masonry_wrap');
	$parkivia_stickies = is_home() ? get_option( 'sticky_posts' ) : false;
	$parkivia_sticky_out = parkivia_get_theme_option('sticky_style')=='columns' 
							&& is_array($parkivia_stickies) && count($parkivia_stickies) > 0 && get_query_var( 'paged' ) < 1;
	if ($parkivia_sticky_out) {
		?><div class="sticky_wrap columns_wrap"><?php	
	}
	if (!$parkivia_sticky_out) {
		if (parkivia_get_theme_option('first_post_large') && !is_paged() && !in_array(parkivia_get_theme_option('body_style'), array('fullwide', 'fullscreen'))) {
			the_post();
			get_template_part( 'content', 'excerpt' );
		}
		
		?><div class="<?php echo esc_attr($parkivia_classes); ?>"><?php
	}
	while ( have_posts() ) { the_post(); 
		if ($parkivia_sticky_out && !is_sticky()) {
			$parkivia_sticky_out = false;
			?></div><div class="<?php echo esc_attr($parkivia_classes); ?>"><?php
		}
		get_template_part( 'content', $parkivia_sticky_out && is_sticky() ? 'sticky' : 'classic' );
	}
	
	?></div><?php

	parkivia_show_pagination();

	parkivia_show_layout(get_query_var('blog_archive_end'));

} else {

	if ( is_search() )
		get_template_part( 'content', 'none-search' );
	else
		get_template_part( 'content', 'none-archive' );

}

get_footer();
?>