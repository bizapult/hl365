<?php
/**
 * The Sticky template to display the sticky posts
 *
 * Used for index/archive
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

$parkivia_columns = max(1, min(3, count(get_option( 'sticky_posts' ))));
$parkivia_post_format = get_post_format();
$parkivia_post_format = empty($parkivia_post_format) ? 'standard' : str_replace('post-format-', '', $parkivia_post_format);
$parkivia_animation = parkivia_get_theme_option('blog_animation');

?><div class="column-1_<?php echo esc_attr($parkivia_columns); ?>"><article id="post-<?php the_ID(); ?>" 
	<?php post_class( 'post_item post_layout_sticky post_format_'.esc_attr($parkivia_post_format) ); ?>
	<?php echo (!parkivia_is_off($parkivia_animation) ? ' data-animation="'.esc_attr(parkivia_get_animation_classes($parkivia_animation)).'"' : ''); ?>
	>

	<?php
	if ( is_sticky() && is_home() && !is_paged() ) {
		?><span class="post_label label_sticky"></span><?php
	}

	// Featured image
	parkivia_show_post_featured(array(
		'thumb_size' => parkivia_get_thumb_size($parkivia_columns==1 ? 'big' : ($parkivia_columns==2 ? 'med' : 'avatar'))
	));

	if ( !in_array($parkivia_post_format, array('link', 'aside', 'status', 'quote')) ) {
		?>
		<div class="post_header entry-header">
			<?php
			// Post title
			the_title( sprintf( '<h6 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h6>' );
			// Post meta
			parkivia_show_post_meta(apply_filters('parkivia_filter_post_meta_args', array(), 'sticky', $parkivia_columns));
			?>
		</div><!-- .entry-header -->
		<?php
	}
	?>
</article></div>