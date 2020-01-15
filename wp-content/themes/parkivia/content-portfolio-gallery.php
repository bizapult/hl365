<?php
/**
 * The Gallery template to display posts
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
$parkivia_image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' );

?><article id="post-<?php the_ID(); ?>" 
	<?php post_class( 'post_item post_layout_portfolio post_layout_gallery post_layout_gallery_'.esc_attr($parkivia_columns).' post_format_'.esc_attr($parkivia_post_format) ); ?>
	<?php echo (!parkivia_is_off($parkivia_animation) ? ' data-animation="'.esc_attr(parkivia_get_animation_classes($parkivia_animation)).'"' : ''); ?>
	data-size="<?php if (!empty($parkivia_image[1]) && !empty($parkivia_image[2])) echo intval($parkivia_image[1]) .'x' . intval($parkivia_image[2]); ?>"
	data-src="<?php if (!empty($parkivia_image[0])) echo esc_url($parkivia_image[0]); ?>"
	>

	<?php

	// Sticky label
	if ( is_sticky() && !is_paged() ) {
		?><span class="post_label label_sticky"></span><?php
	}

	// Featured image
	$parkivia_image_hover = 'icon';
	if (in_array($parkivia_image_hover, array('icons', 'zoom'))) $parkivia_image_hover = 'dots';
	$parkivia_components = parkivia_array_get_keys_by_value(parkivia_get_theme_option('meta_parts'));
	$parkivia_counters = parkivia_array_get_keys_by_value(parkivia_get_theme_option('counters'));
	parkivia_show_post_featured(array(
		'hover' => $parkivia_image_hover,
		'thumb_size' => parkivia_get_thumb_size( strpos(parkivia_get_theme_option('body_style'), 'full')!==false || $parkivia_columns < 3 ? 'masonry-big' : 'masonry' ),
		'thumb_only' => true,
		'show_no_image' => true,
		'post_info' => '<div class="post_details">'
							. '<h2 class="post_title"><a href="'.esc_url(get_permalink()).'">'. esc_html(get_the_title()) . '</a></h2>'
							. '<div class="post_description">'
								. (!empty($parkivia_components)
										? parkivia_show_post_meta(apply_filters('parkivia_filter_post_meta_args', array(
											'components' => $parkivia_components,
											'counters' => $parkivia_counters,
											'seo' => false,
											'echo' => false
											), $parkivia_blog_style[0], $parkivia_columns))
										: '')
								. '<div class="post_description_content">'
									. get_the_excerpt()
								. '</div>'
								. '<a href="'.esc_url(get_permalink()).'" class="theme_button post_readmore"><span class="post_readmore_label">' . esc_html__('Learn more', 'parkivia') . '</span></a>'
							. '</div>'
						. '</div>'
	));
	?>
</article>