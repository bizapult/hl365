<?php
/**
 * The template for homepage posts with "Chess" style
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

parkivia_storage_set('blog_archive', true);

get_header(); 

if (have_posts()) {

	parkivia_show_layout(get_query_var('blog_archive_start'));

	$parkivia_stickies = is_home() ? get_option( 'sticky_posts' ) : false;
	$parkivia_sticky_out = parkivia_get_theme_option('sticky_style')=='columns' 
							&& is_array($parkivia_stickies) && count($parkivia_stickies) > 0 && get_query_var( 'paged' ) < 1;
	if ($parkivia_sticky_out) {
		?><div class="sticky_wrap columns_wrap"><?php	
	}
	if (!$parkivia_sticky_out) {
		?><div class="chess_wrap posts_container"><?php
	}
	while ( have_posts() ) { the_post(); 
		if ($parkivia_sticky_out && !is_sticky()) {
			$parkivia_sticky_out = false;
			?></div><div class="chess_wrap posts_container"><?php
		}
		get_template_part( 'content', $parkivia_sticky_out && is_sticky() ? 'sticky' :'chess' );
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