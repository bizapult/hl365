<?php
/**
 * The Portfolio template to display the content
 *
 * Used for index/archive/search.
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

$parkivia_blog_style = explode('_', parkivia_get_theme_option('blog_style'));
$parkivia_columns = empty($parkivia_blog_style[1]) ? 2 : max(2, $parkivia_blog_style[1]);
$parkivia_post_format = get_post_format();
$parkivia_post_format = empty($parkivia_post_format) ? 'standard' : str_replace('post-format-', '', $parkivia_post_format);
$parkivia_animation = parkivia_get_theme_option('blog_animation');

?><article id="post-<?php the_ID(); ?>" 
	<?php post_class( 'post_item post_layout_portfolio post_layout_portfolio_'.esc_attr($parkivia_columns).' post_format_'.esc_attr($parkivia_post_format).(is_sticky() && !is_paged() ? ' sticky' : '') ); ?>
	<?php echo (!parkivia_is_off($parkivia_animation) ? ' data-animation="'.esc_attr(parkivia_get_animation_classes($parkivia_animation)).'"' : ''); ?>>
	<?php

	// Sticky label
	if ( is_sticky() && !is_paged() ) {
		?><span class="post_label label_sticky"></span><?php
	}

	$parkivia_image_hover = parkivia_get_theme_option('image_hover');
	// Featured image
	parkivia_show_post_featured(array(
		'thumb_size' => parkivia_get_thumb_size(strpos(parkivia_get_theme_option('body_style'), 'full')!==false || $parkivia_columns < 3 
								? 'masonry-big' 
								: 'masonry'),
		'show_no_image' => true,
		'class' => $parkivia_image_hover == 'dots' ? 'hover_with_info' : '',
		'post_info' => $parkivia_image_hover == 'dots' ? '<div class="post_info">'.esc_html(get_the_title()).'</div>' : ''
	));
	?>
</article>